<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Tag;
use App\Services\ImageProcessor;
use App\Support\ContentBlocks;
use App\Support\ContentLimits;
use App\Support\FilamentImageUpload;
use App\Support\RichText;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

trait BuildsEditorialForm
{
    public static function editorialForm(Schema $schema, bool $isOpportunity = false): Schema
    {
        return $schema->components([
            Hidden::make('translation_meta'),
            Hidden::make('status')->default('draft'),
            static::localeTabs()
                ->columnSpanFull(),
            ...($isOpportunity ? static::opportunityFields() : []),
        ]);
    }

    protected static function localeTabs(): Tabs
    {
        return Tabs::make('Языковые версии')
            ->tabs([
                static::localeTab('ru', 'Русский', true),
                static::localeTab('ro', 'Română'),
                static::localeTab('en', 'English'),
                static::additionalSettingsTab(),
            ])
            ->extraAttributes(['class' => 'editorial-language-tabs'])
            ->contained(false)
            ->activeTab(1);
    }

    protected static function localeTab(string $locale, string $label, bool $isSource = false): Tab
    {
        $content = [
            TextInput::make("title.{$locale}")
                ->label('Заголовок')
                ->maxLength(191)
                ->live(debounce: 900)
                ->columnSpanFull(),
            static::excerptField("excerpt.{$locale}")
                ->columnSpanFull(),
        ];

        if ($isSource) {
            $content[] = static::imageUpload('cover_image', 'Фотография обложки')
                ->columnSpanFull();
        }

        if (! $isSource) {
            $content[] = Placeholder::make("translation_notice_{$locale}")
                ->label('Статус перевода')
                ->content(fn (Get $get): string => static::translationNotice($get, $locale));
            $content[] = Actions::make([
                Action::make("translate_{$locale}")
                    ->label(fn (Get $get): string => static::translationActionLabel($get, $locale))
                    ->icon('heroicon-o-language')
                    ->color('primary')
                    ->action(fn (Action $action): mixed => $action->getLivewire()?->translate($locale)),
            ]);
        }

        $content[] = ContentBlocks::schema("content.{$locale}", defaultParagraph: $isSource);

        return Tab::make($label)
            ->badge(fn (Get $get): ?string => static::translationBadge($get, $locale, $isSource))
            ->badgeColor(fn (Get $get): string => static::translationBadgeColor($get, $locale, $isSource))
            ->schema($content);
    }

    protected static function additionalSettingsTab(): Tab
    {
        return Tab::make('Дополнительные настройки')
            ->schema([
                TextInput::make('slug')
                    ->label('Адрес страницы')
                    ->helperText('Можно оставить автоматически созданный адрес.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(191),
                DateTimePicker::make('published_at')
                    ->label('Дата публикации')
                    ->helperText('Можно указать текущую или прошедшую дату. Для будущей даты используйте «Запланировать».')
                    ->seconds(false)
                    ->displayFormat('d.m.Y H:i')
                    ->maxDate(fn (Get $get) => $get('status') === 'scheduled' ? null : now()),
                TextInput::make('author')
                    ->label('Автор')
                    ->maxLength(191),
                TextInput::make('seo_title')
                    ->label('SEO-заголовок')
                    ->maxLength(191),
                Textarea::make('seo_description')
                    ->label('SEO-описание')
                    ->rows(3)
                    ->maxLength(240),
            ]);
    }

    protected static function opportunityFields(): array
    {
        return [
            DatePicker::make('application_deadline')
                ->label('Подать заявку до'),
            Select::make('tag_id')
                ->label('Тег')
                ->options(fn (): array => Tag::query()->get()->mapWithKeys(fn (Tag $tag): array => [$tag->id => $tag->labelFor('ru')])->all())
                ->searchable()
                ->preload(),
        ];
    }

    protected static function excerptField(string $name): Textarea
    {
        return Textarea::make($name)
            ->label('Краткое описание')
            ->rows(3)
            ->maxLength(ContentLimits::SHORT_DESCRIPTION_MAX)
            ->live()
            ->afterStateUpdated(function ($livewire) use ($name): void {
                $livewire->resetValidation("data.{$name}");
            })
            ->hint(fn (?string $state): string => sprintf('%d / %d символов', mb_strlen($state ?? ''), ContentLimits::SHORT_DESCRIPTION_MAX));
    }

    protected static function imageUpload(string $name, string $label): FileUpload
    {
        return FilamentImageUpload::fixedGrid(FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->previewable()
            ->maxSize(51200)
            ->disk('public')
            ->visibility('public'), FilamentImageUpload::CARD_RATIO)
            ->extraAttributes(['class' => 'editorial-cover-upload'], merge: true)
            ->directory('uploads/covers')
            ->saveUploadedFileUsing(fn ($file): string => app(ImageProcessor::class)->store($file, 'uploads/covers', FilamentImageUpload::CARD_RATIO));
    }

    protected static function translationSourceFromGetter(Get $get): array
    {
        return [
            'title' => static::stringValue($get('title.ru')),
            'excerpt' => static::stringValue($get('excerpt.ru')),
            'content' => $get('content.ru') ?? [],
        ];
    }

    public static function translationHash(array $source): string
    {
        return hash('sha256', json_encode([
            'title' => (string) ($source['title'] ?? ''),
            'excerpt' => (string) ($source['excerpt'] ?? ''),
            'content' => array_map(static function (array $block): array {
                return [
                    'type' => (string) ($block['type'] ?? ''),
                    'data' => [
                        'text' => RichText::toText(data_get($block, 'data.text', '')),
                        'level' => static::stringValue(data_get($block, 'data.level', '')),
                        'items' => array_values((array) data_get($block, 'data.items', [])),
                        'path' => static::stringValue(data_get($block, 'data.path', '')),
                        'images' => array_map(
                            static fn (mixed $image): string => static::stringValue(data_get($image, 'path', '')),
                            array_values((array) data_get($block, 'data.images', [])),
                        ),
                        'url' => static::stringValue(data_get($block, 'data.url', '')),
                        'caption' => static::stringValue(data_get($block, 'data.caption', '')),
                    ],
                ];
            }, array_values((array) ($source['content'] ?? []))),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected static function stringValue(mixed $value): string
    {
        return is_scalar($value) || $value instanceof \Stringable
            ? (string) $value
            : '';
    }

    protected static function translationHasContent(Get $get, string $locale): bool
    {
        return filled($get("title.{$locale}"))
            || filled($get("excerpt.{$locale}"))
            && collect($get("content.{$locale}") ?? [])->contains(fn ($block): bool => is_array($block) && (filled(data_get($block, 'data.text')) || filled(data_get($block, 'data.path')) || filled(data_get($block, 'data.images'))));
    }

    protected static function translationHasMissingFields(Get $get, string $locale): bool
    {
        $source = static::translationSourceFromGetter($get);
        $translated = [
            'title' => $get("title.{$locale}"),
            'excerpt' => $get("excerpt.{$locale}"),
            'content' => array_values((array) ($get("content.{$locale}") ?? [])),
        ];

        if (filled($source['title']) && blank($translated['title'])) {
            return true;
        }

        if (filled($source['excerpt']) && blank($translated['excerpt'])) {
            return true;
        }

        foreach (array_values((array) $source['content']) as $index => $sourceBlock) {
            $translatedBlock = $translated['content'][$index] ?? null;

            if (! is_array($translatedBlock) || ($translatedBlock['type'] ?? null) !== ($sourceBlock['type'] ?? null)) {
                return true;
            }

            if (filled(data_get($sourceBlock, 'data.text')) && blank(data_get($translatedBlock, 'data.text'))) {
                return true;
            }

            if (filled(data_get($sourceBlock, 'data.path')) && blank(data_get($translatedBlock, 'data.path'))) {
                return true;
            }

            $sourceImages = array_values((array) data_get($sourceBlock, 'data.images', []));
            $translatedImages = array_values((array) data_get($translatedBlock, 'data.images', []));

            if (count($sourceImages) !== count($translatedImages)) {
                return true;
            }

            foreach ($sourceImages as $imageIndex => $sourceImage) {
                if (filled(data_get($sourceImage, 'path')) && blank(data_get($translatedImages[$imageIndex] ?? null, 'path'))) {
                    return true;
                }
            }
        }

        return false;
    }

    protected static function translationComplete(Get $get, string $locale): bool
    {
        return static::translationHasContent($get, $locale)
            && ! static::translationHasMissingFields($get, $locale);
    }

    protected static function translationIsStale(Get $get, string $locale): bool
    {
        $sourceHash = static::translationHash(static::translationSourceFromGetter($get));
        $savedHash = data_get($get('translation_meta') ?? [], "{$locale}.source_hash");

        return filled($savedHash) && $savedHash !== $sourceHash;
    }

    protected static function translationBadge(Get $get, string $locale, bool $isSource): ?string
    {
        // Keep the status mutually exclusive: a tab gets either a checkmark
        // or a warning, never both.
        if ($isSource) {
            return static::translationHasContent($get, 'ru') ? "\u{2713}" : null;
        }

        if (! static::translationHasContent($get, $locale)
            || static::translationHasMissingFields($get, $locale)
            || static::translationIsStale($get, $locale)) {
            return "\u{26A0}";
        }

        return "\u{2713}";
    }

    protected static function translationBadgeColor(Get $get, string $locale, bool $isSource): string
    {
        if ($isSource) {
            return static::translationHasContent($get, 'ru') ? 'success' : 'gray';
        }

        return static::translationHasMissingFields($get, $locale) || static::translationIsStale($get, $locale)
            ? 'warning'
            : (static::translationHasContent($get, $locale) ? 'success' : 'gray');
    }

    protected static function translationNotice(Get $get, string $locale): string
    {
        if (! static::translationHasContent($get, $locale)) {
            return 'Перевод ещё не создан. Нажмите кнопку ниже, чтобы перевести русский оригинал.';
        }

        if (static::translationHasMissingFields($get, $locale)) {
            return 'В переводе не заполнена часть полей или блоков. Обновите перевод и затем проверьте текст вручную.';
        }

        if (static::translationIsStale($get, $locale)) {
            return 'Русская версия изменена после последнего перевода. Перевод можно обновить и затем проверить вручную.';
        }

        return 'Перевод актуален. Перед публикацией его можно отредактировать вручную.';
    }

    protected static function translationActionLabel(Get $get, string $locale): string
    {
        return static::translationHasContent($get, $locale) ? 'Обновить перевод' : 'Перевести с русского';
    }
}
