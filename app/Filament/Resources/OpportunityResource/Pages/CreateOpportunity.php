<?php

namespace App\Filament\Resources\OpportunityResource\Pages;

use App\Filament\Resources\Concerns\HasEditorialWorkflow;
use App\Filament\Resources\OpportunityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    use HasEditorialWorkflow;

    protected static bool $canCreateAnother = false;

    protected static string $resource = OpportunityResource::class;

    protected static ?string $title = 'Новый тендер';

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
        return (string) (data_get($this->data, 'title.ru') ?: 'Новый тендер');
    }

    public function getPageClasses(): array
    {
        return [...parent::getPageClasses(), 'fi-editorial-page'];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return OpportunityResource::mutateFormDataBeforeCreate($data);
    }
}
