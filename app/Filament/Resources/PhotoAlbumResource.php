<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoAlbumResource\Pages;
use App\Models\PhotoAlbum;
use App\Services\ImageProcessor;
use App\Support\FilamentImageUpload;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PhotoAlbumResource extends Resource
{
    protected static ?string $model = PhotoAlbum::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Медиа';

    protected static ?string $navigationLabel = 'Все фотоальбомы';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'фотоальбом';

    protected static ?string $pluralModelLabel = 'Фотоальбомы';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Все фотоальбомы')
                ->key('photo-albums.index')->group('Медиа')->icon(Heroicon::OutlinedPhoto)->sort(1)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.photo-albums.index'))
                ->url(static::getUrl('index')),
            NavigationItem::make('Добавить фотоальбом')
                ->key('photo-albums.create')->group('Медиа')->icon(Heroicon::OutlinedPlus)->sort(2)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.photo-albums.create'))
                ->url(static::getUrl('create')),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Языковые версии')
                ->contained(false)
                ->activeTab(1)
                ->extraAttributes(['class' => 'album-language-tabs'])
                ->tabs([
                    static::localeTab('ru', 'Русский', true),
                    static::localeTab('ro', 'Română'),
                    static::localeTab('en', 'English'),
                    Tab::make('Настройки')->schema([
                        TextInput::make('slug')
                            ->label('Адрес страницы')
                            ->helperText('Можно оставить пустым: адрес создастся из русского названия.')
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Select::make('status')
                            ->label('Видимость')
                            ->options(['draft' => 'Черновик', 'published' => 'Опубликовано'])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->seconds(false)
                            ->displayFormat('d.m.Y H:i')
                            ->maxDate(now()),
                    ]),
                ])
                ->columnSpanFull(),
            Repeater::make('photos')
                ->label('Фотографии')
                ->relationship()
                ->orderColumn('position')
                ->reorderableWithDragAndDrop()
                ->addActionLabel('Добавить фото')
                ->defaultItems(0)
                ->grid(['default' => 2, 'lg' => 3])
                ->extraAttributes(['class' => 'album-photo-manager'])
                ->schema([
                    static::photoUpload('path', 'album-photo-upload')->hiddenLabel(),
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
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Название')
                    ->getStateUsing(fn (PhotoAlbum $record): string => $record->titleFor('ru'))
                    ->searchable(query: fn ($query, string $search) => $query->where('title->ru', 'like', "%{$search}%")),
                TextColumn::make('photos_count')->counts('photos')->label('Фото'),
                TextColumn::make('status')->label('Статус')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'published' ? 'Опубликовано' : 'Черновик')
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('published_at')->label('Дата публикации')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(['draft' => 'Черновик', 'published' => 'Опубликовано']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->stackedOnMobile()
            ->defaultSort('published_at', 'desc');
    }

    public static function mutateFormData(array $data): array
    {
        $slug = (string) ($data['slug'] ?? '');
        $data['slug'] = blank($slug)
            ? (filled(data_get($data, 'title.ru')) ? Str::slug((string) data_get($data, 'title.ru')) : 'album-'.Str::lower(Str::random(12)))
            : Str::slug($slug);
        $data['status'] ??= 'draft';
        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? now())
            : null;

        return $data;
    }

    protected static function localeTab(string $locale, string $label, bool $isRussian = false): Tab
    {
        $fields = [
            TextInput::make("title.{$locale}")
                ->label('Название альбома')
                ->maxLength(191)
                ->required($isRussian)
                ->columnSpanFull(),
            Textarea::make("excerpt.{$locale}")
                ->label('Краткое описание')
                ->rows(3)
                ->maxLength(240)
                ->columnSpanFull(),
        ];

        if ($isRussian) {
            $fields[] = static::photoUpload('cover_image', 'album-cover-upload')
                ->label('Обложка альбома')
                ->helperText('Если не загрузить обложку, на сайте будет использована первая фотография.')
                ->columnSpanFull();
        }

        return Tab::make($label)->schema($fields);
    }

    protected static function photoUpload(string $name, string $uploadClass): FileUpload
    {
        $upload = FileUpload::make($name)
            ->image()
            ->imageEditor()
            ->previewable()
            ->imagePreviewHeight($name === 'cover_image' ? '120' : '190')
            ->maxSize(51200)
            ->disk('public')
            ->visibility('public');

        $upload = FilamentImageUpload::fixedGrid($upload, FilamentImageUpload::LANDSCAPE_RATIO);

        return $upload
            ->directory('uploads/albums')
            ->extraAttributes(['class' => $uploadClass])
            ->saveUploadedFileUsing(fn ($file): string => app(ImageProcessor::class)->store(
                $file,
                'uploads/albums',
                FilamentImageUpload::LANDSCAPE_RATIO,
            ));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoAlbums::route('/'),
            'create' => Pages\CreatePhotoAlbum::route('/create'),
            'edit' => Pages\EditPhotoAlbum::route('/{record}/edit'),
        ];
    }
}
