<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Возможности';

    protected static ?string $navigationLabel = 'Теги';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'тег';

    protected static ?string $pluralModelLabel = 'Теги';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Название тега')
                ->schema([
                    TextInput::make('name.ru')->label('Русский')->required()->maxLength(80),
                    TextInput::make('name.ro')->label('Румынский')->required()->maxLength(80),
                    TextInput::make('name.en')->label('Английский')->required()->maxLength(80),
                    Select::make('color')
                        ->label('Цвет')
                        ->options(Tag::colorOptionsWithSwatches())
                        ->allowHtml()
                        ->default('#DDF6B7')
                        ->required(),
                ])
                ->columns(['default' => 1, 'lg' => 4])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->getStateUsing(fn (Tag $record): string => $record->labelFor('ru')),
                TextColumn::make('color')
                    ->label('Цвет')
                    ->formatStateUsing(fn (string $state, Tag $record): HtmlString => new HtmlString(sprintf(
                        '<span class="tag-color-badge" style="background-color: %s; color: #111827;">%s</span>',
                        e($record->colorValue()),
                        e($record->colorLabel()),
                    )))
                    ->html(),
                TextColumn::make('opportunities_count')->counts('opportunities')->label('Возможностей'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->stackedOnMobile();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
