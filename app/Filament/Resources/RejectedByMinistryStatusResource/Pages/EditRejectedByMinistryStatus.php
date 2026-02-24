<?php

namespace App\Filament\Resources\RejectedByMinistryStatusResource\Pages;

use App\Filament\Resources\RejectedByMinistryStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRejectedByMinistryStatus extends EditRecord
{
    protected static string $resource = RejectedByMinistryStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
