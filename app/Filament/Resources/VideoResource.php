<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Video;
use App\Services\ImageProcessor;
use App\Support\FilamentImageUpload;
use App\Support\YouTube;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string|\UnitEnum|null $navigationGroup = 'Медиа';

    protected static ?string $navigationLabel = 'Все видео';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'видео';

    protected static ?string $pluralModelLabel = 'Видео';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Все видео')
                ->key('videos.index')->group('Медиа')->icon(Heroicon::OutlinedVideoCamera)->sort(3)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.videos.index'))
                ->url(static::getUrl('index')),
            NavigationItem::make('Добавить видео')
                ->key('videos.create')->group('Медиа')->icon(Heroicon::OutlinedPlus)->sort(4)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.videos.create'))
                ->url(static::getUrl('create')),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Языковые версии')
                ->contained(false)
                ->activeTab(1)
                ->tabs([
                    static::localeTab('ru', 'Русский', true),
                    static::localeTab('ro', 'Română'),
                    static::localeTab('en', 'English'),
                    Tab::make('Настройки')->schema([
                        TextInput::make('youtube_url')
                            ->label('Ссылка на YouTube')
                            ->url()
                            ->required()
                            ->maxLength(2048)
                            ->helperText('Поддерживаются youtube.com и youtu.be.')
                            ->rule(static function (): Closure {
                                return static function (string $attribute, mixed $value, Closure $fail): void {
                                    if (YouTube::extractVideoId((string) $value) === null) {
                                        $fail('Укажите корректную ссылку на видео YouTube.');
                                    }
                                };
                            })
                            ->columnSpanFull(),
                        DatePicker::make('event_date')
                            ->label('Дата мероприятия')
                            ->required()
                            ->displayFormat('d.m.Y'),
                        static::coverUpload(),
                    ])->columns(2),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Обложка')
                    ->getStateUsing(fn (Video $record): string => $record->coverUrl())
                    ->imageWidth(112)
                    ->imageHeight(63),
                TextColumn::make('title')
                    ->label('Название')
                    ->getStateUsing(fn (Video $record): string => $record->titleFor('ru'))
                    ->searchable(query: fn ($query, string $search) => $query->where('title->ru', 'like', "%{$search}%")),
                TextColumn::make('event_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->reorderable('position')
            ->stackedOnMobile()
            ->defaultSort('position');
    }

    public static function mutateFormData(array $data): array
    {
        $url = trim((string) ($data['youtube_url'] ?? ''));
        $data['youtube_url'] = $url;
        $data['youtube_id'] = YouTube::extractVideoId($url);
        $data['position'] ??= 0;

        return $data;
    }

    protected static function localeTab(string $locale, string $label, bool $required = false): Tab
    {
        return Tab::make($label)->schema([
            TextInput::make("title.{$locale}")
                ->label('Название')
                ->required($required)
                ->maxLength(191)
                ->columnSpanFull(),
            Textarea::make("description.{$locale}")
                ->label('Описание')
                ->required($required)
                ->maxLength(240)
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    protected static function coverUpload(): FileUpload
    {
        $upload = FileUpload::make('cover_image')
            ->label('Собственная обложка (необязательно)')
            ->helperText('Если обложку не загрузить, будет использована обложка с YouTube.')
            ->image()
            ->imageEditor()
            ->previewable()
            ->maxSize(51200)
            ->disk('public')
            ->visibility('public');

        return FilamentImageUpload::fixedGrid($upload, FilamentImageUpload::ARTICLE_RATIO)
            ->directory('uploads/videos')
            ->extraAttributes(['class' => 'video-cover-upload'])
            ->saveUploadedFileUsing(fn ($file): string => app(ImageProcessor::class)->store(
                $file,
                'uploads/videos',
                FilamentImageUpload::ARTICLE_RATIO,
            ));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
