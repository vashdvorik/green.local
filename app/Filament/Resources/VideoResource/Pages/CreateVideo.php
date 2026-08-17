<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideo extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = VideoResource::class;

    protected static ?string $title = 'Новое видео';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return VideoResource::mutateFormData($data);
    }
}
