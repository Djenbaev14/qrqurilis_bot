<?php

namespace App\Filament\Resources\InProgressStatusResource\Pages;

use App\Filament\Resources\InProgressStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInProgressStatus extends EditRecord
{
    protected static string $resource = InProgressStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
