<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsEditorialForm;
use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    use BuildsEditorialForm;

    protected static ?string $model = News::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Новости';

    protected static ?string $navigationLabel = 'Все новости';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'новость';

    protected static ?string $pluralModelLabel = 'Новости';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Все новости')
                ->key('news.index')->group('Новости')->icon(Heroicon::OutlinedNewspaper)->sort(1)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.news.index'))
                ->url(static::getUrl('index')),
            NavigationItem::make('Добавить новость')
                ->key('news.create')->group('Новости')->icon(Heroicon::OutlinedPlus)->sort(2)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.news.create'))
                ->url(static::getUrl('create')),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return static::editorialForm($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Заголовок')
                    ->getStateUsing(fn (News $record): string => $record->titleFor('ru'))
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('title->ru', 'like', "%{$search}%")),
                TextColumn::make('status')->label('Статус')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Опубликовано', 'scheduled' => 'Запланировано', default => 'Черновик',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success', 'scheduled' => 'warning', default => 'gray',
                    }),
                TextColumn::make('published_at')->label('Дата публикации')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('updated_at')->label('Изменено')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')
                    ->options(['draft' => 'Черновик', 'published' => 'Опубликовано', 'scheduled' => 'Запланировано']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->stackedOnMobile()
            ->defaultSort('published_at', 'desc');
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return static::normalizeSlug($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::normalizeSlug($data);
    }

    protected static function normalizeSlug(array $data): array
    {
        $slug = (string) ($data['slug'] ?? '');
        $data['slug'] = blank($slug) || Str::startsWith($slug, 'draft-')
            ? (filled(data_get($data, 'title.ru')) ? Str::slug((string) data_get($data, 'title.ru')) : 'draft-'.Str::lower(Str::random(12)))
            : Str::slug($slug);
        $data['status'] ??= 'draft';
        $data['published_at'] = match ($data['status']) {
            'published' => $data['published_at'] ?? now(),
            'scheduled' => $data['published_at'] ?? null,
            default => null,
        };

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
