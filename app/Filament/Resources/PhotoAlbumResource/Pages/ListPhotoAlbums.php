<?php

namespace App\Filament\Resources\PhotoAlbumResource\Pages;

use App\Filament\Resources\PhotoAlbumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhotoAlbums extends ListRecords
{
    protected static string $resource = PhotoAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить фотоальбом')];
    }
}
