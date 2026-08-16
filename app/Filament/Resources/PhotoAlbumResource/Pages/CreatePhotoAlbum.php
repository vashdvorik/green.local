<?php

namespace App\Filament\Resources\PhotoAlbumResource\Pages;

use App\Filament\Resources\PhotoAlbumResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePhotoAlbum extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = PhotoAlbumResource::class;

    protected static ?string $title = 'Новый фотоальбом';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PhotoAlbumResource::mutateFormData($data);
    }

    public function getPageClasses(): array
    {
        return [...parent::getPageClasses(), 'fi-album-page'];
    }
}
