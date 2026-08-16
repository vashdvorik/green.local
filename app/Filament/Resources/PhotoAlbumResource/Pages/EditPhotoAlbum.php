<?php

namespace App\Filament\Resources\PhotoAlbumResource\Pages;

use App\Filament\Resources\PhotoAlbumResource;
use Filament\Resources\Pages\EditRecord;

class EditPhotoAlbum extends EditRecord
{
    protected static string $resource = PhotoAlbumResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PhotoAlbumResource::mutateFormData($data);
    }

    public function getPageClasses(): array
    {
        return [...parent::getPageClasses(), 'fi-album-page'];
    }
}
