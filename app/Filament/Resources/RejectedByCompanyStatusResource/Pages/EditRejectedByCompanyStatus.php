<?php

namespace App\Filament\Resources\RejectedByCompanyStatusResource\Pages;

use App\Filament\Resources\RejectedByCompanyStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRejectedByCompanyStatus extends EditRecord
{
    protected static string $resource = RejectedByCompanyStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
