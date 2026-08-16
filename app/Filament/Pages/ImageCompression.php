<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
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

class ImageCompression extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки сайта';

    protected static ?string $navigationLabel = 'Сжатие изображений';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Сжатие изображений';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'max_dimension' => SiteSetting::getValue('images.max_dimension', 2400),
            'avif_quality' => SiteSetting::getValue('images.avif_quality', 60),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Параметры обработки')
                ->description('Новые изображения уменьшаются без обрезки, затем сохраняются в формате AVIF.')
                ->schema([
                    TextInput::make('max_dimension')
                        ->label('Максимальная длинная сторона, px')
                        ->numeric()
                        ->minValue(320)
                        ->maxValue(5000)
                        ->required(),
                    TextInput::make('avif_quality')
                        ->label('Качество AVIF')
                        ->numeric()
                        ->minValue(20)
                        ->maxValue(100)
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

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::putValue('images.max_dimension', (int) $data['max_dimension']);
        SiteSetting::putValue('images.avif_quality', (int) $data['avif_quality']);

        Notification::make()->success()->title('Настройки сжатия сохранены.')->send();
    }
}
