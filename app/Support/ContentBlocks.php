<?php

namespace App\Support;

use App\Services\ImageProcessor;
use App\Support\FilamentImageUpload;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

class ContentBlocks
{
    public static function schema(string $field, bool $defaultParagraph = false): Builder
    {
        return Builder::make($field)
            ->label(null)
            ->blocks([
                Block::make('paragraph')
                    ->label('Текст')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        RichEditor::make('text')
                            ->hiddenLabel()
                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'blockquote'])
                            ->placeholder('Начните писать текст материала...')
                            ->live(debounce: 1200)
                            ->columnSpanFull(),
                    ]),
                Block::make('heading')
                    ->label('Заголовок')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextInput::make('text')->hiddenLabel(),
                        Select::make('level')->label('Уровень')->options(['h2' => 'H2', 'h3' => 'H3'])->default('h2'),
                    ]),
                Block::make('quote')
                    ->label('Цитата')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->schema([
                        Textarea::make('text')->label('Цитата')->rows(3),
                    ]),
                Block::make('image')
                    ->label('Изображение')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        self::imageUpload('path', 'editorial-image-upload--article')->hiddenLabel(),
                    ]),
                self::imageTextBlock(
                    'image_text_photo_left',
                    'Изображение+текст',
                    imageFirst: true,
                ),
                self::imageTextBlock(
                    'image_text_text_left',
                    'Текст+изображение',
                    imageFirst: false,
                ),
                Block::make('gallery')
                    ->label('Галерея — до 3 фото')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->hidden()
                    ->schema(self::gallerySchema(3, 'editorial-image-upload--landscape')),
                self::galleryBlock('gallery_2', 'Изображения — 2 фото', 2, 'editorial-image-upload--landscape'),
                self::galleryBlock('gallery_3', 'Изображения — 3 фото', 3, 'editorial-image-upload--landscape'),
                self::galleryBlock('gallery_4', 'Изображения — 4 фото', 4, 'editorial-image-upload--portrait'),
                Block::make('video')
                    ->label('Видео / embed')
                    ->icon(Heroicon::OutlinedVideoCamera)
                    ->schema([
                        TextInput::make('url')
                            ->label('Ссылка на видео')
                            ->url()
                            ->helperText('YouTube, Vimeo или другой безопасный embed-URL.'),
                        TextInput::make('caption')->label('Подпись')->maxLength(191),
                    ]),
            ])
            ->addActionLabel('Добавить блок')
            ->addBetweenActionLabel('Вставить между')
            ->blockNumbers(false)
            ->collapsible()
            ->cloneable()
            ->default($defaultParagraph ? [['type' => 'paragraph', 'data' => ['text' => '']]] : [])
            ->live(debounce: 1200)
            ->extraAttributes(['class' => 'editorial-content-builder'])
            ->columnSpanFull();
    }

    protected static function galleryBlock(string $name, string $label, int $count, string $previewClass): Block
    {
        return Block::make($name)
            ->label($label)
            ->icon(Heroicon::OutlinedPhoto)
            ->schema(self::gallerySchema($count, $previewClass));
    }

    protected static function imageTextBlock(string $name, string $label, bool $imageFirst): Block
    {
        $image = self::imageUpload('path', 'editorial-image-upload--image-text')
            ->hiddenLabel()
            ->extraAttributes(['class' => 'editorial-image-text-upload'], merge: true);
        $text = self::richTextEditor('text');

        return Block::make($name)
            ->label($label)
            ->icon(Heroicon::OutlinedPhoto)
            ->schema([
                Grid::make(['default' => 1, 'md' => 2])
                    ->schema($imageFirst ? [$image, $text] : [$text, $image])
                    ->extraAttributes([
                        'class' => 'editorial-image-text-block '.($imageFirst
                            ? 'editorial-image-text-block--photo-left'
                            : 'editorial-image-text-block--text-left'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function richTextEditor(string $name): RichEditor
    {
        return RichEditor::make($name)
            ->hiddenLabel()
            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'blockquote'])
            ->placeholder('Начните писать текст материала...')
            ->live(debounce: 1200)
            ->columnSpanFull();
    }

    /**
     * @return array<int, Repeater>
     */
    protected static function gallerySchema(int $count, string $previewClass): array
    {
        return [
            Repeater::make('images')
                ->hiddenLabel()
                ->schema([
                    self::imageUpload('path', $previewClass)->hiddenLabel(),
                ])
                ->defaultItems($count)
                ->minItems($count)
                ->maxItems($count)
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->grid($count === 4 ? ['default' => 2, 'lg' => 4] : $count),
        ];
    }

    protected static function imageUpload(string $name, ?string $previewClass = null): FileUpload
    {
        $upload = FileUpload::make($name)
            ->image()
            ->imageEditor()
            ->previewable()
            ->maxSize(51200)
            ->disk('public')
            ->visibility('public');

        $upload = match ($previewClass) {
            'editorial-image-upload--landscape' => FilamentImageUpload::fixedGrid($upload, FilamentImageUpload::LANDSCAPE_RATIO),
            'editorial-image-upload--portrait' => FilamentImageUpload::fixedGrid($upload, FilamentImageUpload::PORTRAIT_RATIO),
            'editorial-image-upload--image-text' => FilamentImageUpload::fixedGrid($upload, FilamentImageUpload::LANDSCAPE_RATIO),
            default => FilamentImageUpload::fixedGrid($upload, FilamentImageUpload::ARTICLE_RATIO),
        };

        $ratio = match ($previewClass) {
            'editorial-image-upload--landscape', 'editorial-image-upload--image-text' => FilamentImageUpload::LANDSCAPE_RATIO,
            'editorial-image-upload--portrait' => FilamentImageUpload::PORTRAIT_RATIO,
            default => FilamentImageUpload::ARTICLE_RATIO,
        };

        return $upload
            ->directory('content')
            ->extraAttributes(['class' => trim('editorial-image-upload '.($previewClass ?? ''))])
            ->saveUploadedFileUsing(fn ($file): string => app(ImageProcessor::class)->store($file, 'content', $ratio));
    }
}
