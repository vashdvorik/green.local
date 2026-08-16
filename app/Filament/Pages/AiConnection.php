<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\AiTranslationService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AiConnection extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки сайта';

    protected static ?string $navigationLabel = 'Подключение ИИ';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Подключение ИИ';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'api_key' => '',
            'model' => SiteSetting::getValue('ai.openrouter_model', ''),
            'base_url' => SiteSetting::getValue('ai.openrouter_base_url', 'https://openrouter.ai/api/v1'),
            'app_name' => SiteSetting::getValue('ai.app_name', 'Green Energy Hub'),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('OpenRouter')
                ->description('Ключ хранится в базе данных в зашифрованном виде и не показывается повторно.')
                ->schema([
                    TextInput::make('api_key')
                        ->label('API-ключ')
                        ->password()
                        ->revealable()
                        ->helperText('Оставьте пустым, чтобы сохранить текущий ключ.'),
                    TextInput::make('model')
                        ->label('Модель перевода')
                        ->placeholder('Укажите модель OpenRouter')
                        ->required(),
                    TextInput::make('base_url')
                        ->label('Адрес API')
                        ->url()
                        ->required(),
                    TextInput::make('app_name')
                        ->label('Название приложения')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                EmbeddedSchema::make('form'),
            ])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make($this->getFormActions()),
                ]),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить настройки')
                ->submit('save'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testConnection')
                ->label('Проверить соединение')
                ->icon(Heroicon::OutlinedSignal)
                ->action('testConnection'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (filled($data['api_key'] ?? null)) {
            SiteSetting::putEncrypted('ai.openrouter_api_key', $data['api_key']);
        }

        SiteSetting::putValue('ai.openrouter_model', $data['model']);
        SiteSetting::putValue('ai.openrouter_base_url', rtrim($data['base_url'], '/'));
        SiteSetting::putValue('ai.app_name', $data['app_name']);

        Notification::make()->success()->title('Настройки ИИ сохранены.')->send();
    }

    public function testConnection(): void
    {
        try {
            $data = $this->form->getState();
            app(AiTranslationService::class)->testConnection([
                'api_key' => filled($data['api_key'] ?? null) ? $data['api_key'] : null,
                'model' => $data['model'] ?? '',
                'base_url' => $data['base_url'] ?? '',
                'app_name' => $data['app_name'] ?? 'Green Energy Hub',
            ]);

            Notification::make()->success()->title('Соединение с OpenRouter работает.')->send();
        } catch (\Throwable $exception) {
            Notification::make()->danger()->title('Не удалось подключиться')->body($exception->getMessage())->send();
        }
    }
}
