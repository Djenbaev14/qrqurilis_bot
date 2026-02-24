<?php

namespace App\Filament\Resources\AssignedToCompanyStatusResource\Pages;

use App\Filament\Resources\AssignedToCompanyStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignedToCompanyStatus extends EditRecord
{
    protected static string $resource = AssignedToCompanyStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
