<?php

namespace App\Filament\Resources\ConfirmedByCitizenStatusResource\Pages;

use App\Filament\Resources\ConfirmedByCitizenStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConfirmedByCitizenStatus extends EditRecord
{
    protected static string $resource = ConfirmedByCitizenStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
