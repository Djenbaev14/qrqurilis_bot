<?php

namespace App\Filament\Resources\CompeletedStatusResource\Pages;

use App\Filament\Resources\CompeletedStatusResource;
use App\Filament\Resources\HasApplicationInfolist;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCompletedStatus extends ViewRecord
{
    use HasApplicationInfolist;
    protected static string $resource = CompeletedStatusResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(static::getSharedInfolistSchema()) // Trait ichidagi metod
            ->columns(3);
    }
}
