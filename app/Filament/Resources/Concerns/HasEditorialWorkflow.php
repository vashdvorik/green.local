<?php

namespace App\Filament\Resources\Concerns;

use App\Models\News;
use App\Models\Opportunity;
use App\Services\AiTranslationService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

trait HasEditorialWorkflow
{
    public string $autosaveMessage = 'Черновик ещё не сохранён';

    protected function getEditorialHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Предпросмотр')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn (): ?string => $this->getEditorialPreviewUrl())
                ->openUrlInNewTab()
                ->disabled(fn (): bool => blank($this->getEditorialRecord()?->slug)),
            Action::make('saveDraft')
                ->label(fn (): string => $this->isEditorialDraftState() ? 'Сохранить черновик' : 'Сохранить изменения')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->action('saveDraft')
                ->keyBindings(['mod+s']),
            Action::make('schedulePublication')
                ->label('Запланировать')
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->form([
                    DateTimePicker::make('scheduled_for')
                        ->label('Дата и время публикации')
                        ->seconds(false)
                        ->default(now()->addDay())
                        ->required(),
                ])
                ->action(fn (array $data): mixed => $this->schedulePublication((string) ($data['scheduled_for'] ?? ''))),
            Action::make('publishNow')
                ->label('Опубликовать')
                ->icon('heroicon-o-paper-airplane')
                ->action('publishNow')
                ->button(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getEditorialFormActions(): array
    {
        return [];
    }

    public function autosaveDraft(): void
    {
        $state = $this->form->getRawState();

        if (($state['status'] ?? 'draft') !== 'draft' || ! $this->hasEditorialInput($state)) {
            return;
        }

        try {
            if ($this instanceof CreateRecord && $this->record === null) {
                $data = static::getResource()::mutateFormDataBeforeCreate($this->form->getState());
                $this->record = $this->handleRecordCreation($data);
                $this->form->model($this->record)->saveRelationships();
            } else {
                $this->persistEditorialRecord(shouldNotify: false);
            }

            $this->autosaveMessage = 'Черновик сохранён '.now()->format('H:i');
        } catch (Throwable) {
            $this->autosaveMessage = 'Не удалось сохранить черновик';
        }
    }

    public function saveDraft(): void
    {
        if ($this->isEditorialDraftState()) {
            $this->setEditorialPublicationState('draft');
        }

        if ($this instanceof CreateRecord && $this->record === null) {
            $this->create();
        } else {
            $this->persistEditorialRecord();
        }
    }

    public function publishNow(): void
    {
        $state = $this->form->getRawState();
        $publishedAt = filled($state['published_at'] ?? null)
            ? (string) $state['published_at']
            : now()->toDateTimeString();

        $this->setEditorialPublicationState('published', $publishedAt);

        if (! $this->validateEditorialPublication()) {
            return;
        }

        if ($this instanceof CreateRecord && $this->record === null) {
            $this->create();
        } else {
            $this->persistEditorialRecord();
        }
    }

    public function schedulePublication(string $scheduledFor): void
    {
        $this->setEditorialPublicationState('scheduled', $scheduledFor);

        if (! $this->validateEditorialPublication()) {
            return;
        }

        if ($this instanceof CreateRecord && $this->record === null) {
            $this->create();
        } else {
            $this->persistEditorialRecord();
        }
    }

    public function translate(string $locale): void
    {
        if (! in_array($locale, ['ro', 'en'], true)) {
            return;
        }

        $state = $this->form->getState();
        $source = [
            'title' => data_get($state, 'title.ru', ''),
            'excerpt' => data_get($state, 'excerpt.ru', ''),
            'content' => data_get($state, 'content.ru', []),
        ];

        if (blank($source['title']) && blank($source['excerpt']) && blank($source['content'])) {
            Notification::make()->danger()->title('Сначала заполните русский оригинал.')->send();

            return;
        }

        $translation = app(AiTranslationService::class)->translateFromRussian($source, $locale);
        data_set($state, "title.{$locale}", $translation['title']);
        data_set($state, "excerpt.{$locale}", $translation['excerpt']);
        data_set($state, "content.{$locale}", $translation['content']);
        data_set($state, "translation_meta.{$locale}", [
            'source_hash' => static::getResource()::translationHash($source),
            'translated_at' => now()->toIso8601String(),
        ]);
        $this->form->fill($state);
        $this->dispatch('editorial-translation-complete', locale: $locale);
        $this->autosaveMessage = 'Перевод подготовлен — сохраните черновик.';

        Notification::make()->success()->title('Перевод подготовлен. Проверьте его перед публикацией.')->send();
    }

    public function getEditorialPreviewUrl(): ?string
    {
        $record = $this->getEditorialRecord();

        if (! $record?->slug) {
            return null;
        }

        return $record instanceof News
            ? route('news.show', $record)
            : ($record instanceof Opportunity ? route('stories.show', $record) : null);
    }

    protected function isEditorialDraftState(): bool
    {
        $state = $this->form->getRawState();

        return ($state['status'] ?? 'draft') === 'draft';
    }

    public function getEditorialRecord(): ?Model
    {
        if ($this instanceof CreateRecord) {
            return $this->record;
        }

        return $this->getRecord();
    }

    protected function setEditorialPublicationState(string $status, ?string $publishedAt = null): void
    {
        $state = $this->form->getRawState();
        $state['status'] = $status;
        $state['published_at'] = $publishedAt;
        $this->form->fill($state);
    }

    protected function hasEditorialInput(array $state): bool
    {
        return filled(data_get($state, 'title.ru'))
            || filled(data_get($state, 'excerpt.ru'))
            || filled(data_get($state, 'cover_image'))
            || collect(data_get($state, 'content.ru', []))->contains(fn ($block): bool => is_array($block) && (filled(data_get($block, 'data.text')) || filled(data_get($block, 'data.path'))));
    }

    protected function validateEditorialPublication(): bool
    {
        $state = $this->form->getRawState();
        $errors = [];

        if (blank(data_get($state, 'title.ru'))) {
            $errors['data.title.ru'] = 'Добавьте русский заголовок.';
        }

        if (blank(data_get($state, 'excerpt.ru'))) {
            $errors['data.excerpt.ru'] = 'Добавьте краткое описание.';
        }

        if (($state['status'] ?? null) === 'published' && filled($state['published_at'] ?? null)) {
            try {
                if (Carbon::parse((string) $state['published_at'])->isFuture()) {
                    $errors['data.published_at'] = 'Дата публикации не может быть в будущем.';
                }
            } catch (Throwable) {
                $errors['data.published_at'] = 'Укажите корректную дату публикации.';
            }
        }

        $this->resetErrorBag();

        foreach ($errors as $key => $message) {
            $this->addError($key, $message);
        }

        if ($errors !== []) {
            Notification::make()->danger()->title('Не удалось опубликовать материал.')->body(implode(' ', $errors))->send();

            return false;
        }

        return true;
    }

    protected function persistEditorialRecord(bool $shouldNotify = true): void
    {
        if (method_exists($this, 'save') && ! ($this instanceof CreateRecord)) {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: $shouldNotify);

            return;
        }

        $record = $this->getEditorialRecord();

        if (! $record) {
            return;
        }

        $data = static::getResource()::mutateFormDataBeforeSave($this->form->getState());
        $record->update($data);
        $this->form->model($record)->saveRelationships();
        $this->record = $record;

        if ($shouldNotify) {
            Notification::make()
                ->success()
                ->title('Изменения сохранены')
                ->send();
        }
    }

}
