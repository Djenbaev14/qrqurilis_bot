<?php

namespace App\Filament\Resources\CompeletedStatusResource\Pages;

use App\Filament\Resources\CompeletedStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompeletedStatus extends EditRecord
{
    protected static string $resource = CompeletedStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
