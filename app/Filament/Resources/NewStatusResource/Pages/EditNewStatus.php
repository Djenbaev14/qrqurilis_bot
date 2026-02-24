<?php

namespace App\Filament\Resources\NewStatusResource\Pages;

use App\Filament\Resources\NewStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewStatus extends EditRecord
{
    protected static string $resource = NewStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
