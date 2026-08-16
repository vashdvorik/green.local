<?php

namespace App\Filament\Resources\OpportunityResource\Pages;

use App\Filament\Resources\Concerns\HasEditorialWorkflow;
use App\Filament\Resources\OpportunityResource;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    use HasEditorialWorkflow;

    protected static string $resource = OpportunityResource::class;

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
        return OpportunityResource::mutateFormDataBeforeSave($data);
    }
}
