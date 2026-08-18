<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\BuildsEditorialForm;
use App\Filament\Resources\OpportunityResource\Pages;
use App\Models\Opportunity;
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

class OpportunityResource extends Resource
{
    use BuildsEditorialForm;

    protected static ?string $model = Opportunity::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Тендеры';

    protected static ?string $navigationLabel = 'Все тендеры';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'тендер';

    protected static ?string $pluralModelLabel = 'Тендеры';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Все тендеры')
                ->key('opportunities.index')->group('Тендеры')->icon(Heroicon::OutlinedSparkles)->sort(1)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.opportunities.index'))
                ->url(static::getUrl('index')),
            NavigationItem::make('Добавить тендер')
                ->key('opportunities.create')->group('Тендеры')->icon(Heroicon::OutlinedPlus)->sort(2)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.opportunities.create'))
                ->url(static::getUrl('create')),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return static::editorialForm($schema, isOpportunity: true);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Заголовок')
                    ->getStateUsing(fn (Opportunity $record): string => $record->titleFor('ru'))
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('title->ru', 'like', "%{$search}%")),
                TextColumn::make('tag.name')->label('Тег')
                    ->getStateUsing(fn (Opportunity $record): string => $record->tag?->labelFor('ru') ?? '—')->badge(),
                TextColumn::make('status')->label('Статус')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Опубликовано', 'scheduled' => 'Запланировано', default => 'Черновик',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success', 'scheduled' => 'warning', default => 'gray',
                    }),
                TextColumn::make('application_deadline')->label('Подать до')->date('d.m.Y')->sortable(),
                TextColumn::make('published_at')->label('Дата публикации')->dateTime('d.m.Y H:i')->sortable(),
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
            'index' => Pages\ListOpportunities::route('/'),
            'create' => Pages\CreateOpportunity::route('/create'),
            'edit' => Pages\EditOpportunity::route('/{record}/edit'),
        ];
    }
}
