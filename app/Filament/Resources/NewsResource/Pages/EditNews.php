<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\Concerns\HasEditorialWorkflow;
use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    use HasEditorialWorkflow;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getEditorialHeaderActions();
    }

    protected function getFormActions(): array
    {
        return $this->getEditorialFormActions();
    }

    public function getPageClasses(): array
    {
        return [...parent::getPageClasses(), 'fi-editorial-page'];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return NewsResource::mutateFormDataBeforeSave($data);
    }
}
