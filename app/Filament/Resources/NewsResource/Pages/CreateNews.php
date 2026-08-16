<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\Concerns\HasEditorialWorkflow;
use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    use HasEditorialWorkflow;

    protected static bool $canCreateAnother = false;

    protected static string $resource = NewsResource::class;

    protected static ?string $title = 'Новая новость';

    protected function getHeaderActions(): array
    {
        return $this->getEditorialHeaderActions();
    }

    protected function getFormActions(): array
    {
        return $this->getEditorialFormActions();
    }

    public function getTitle(): string
    {
        return (string) (data_get($this->data, 'title.ru') ?: 'Новая новость');
    }

    public function getPageClasses(): array
    {
        return [...parent::getPageClasses(), 'fi-editorial-page'];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return NewsResource::mutateFormDataBeforeCreate($data);
    }
}
