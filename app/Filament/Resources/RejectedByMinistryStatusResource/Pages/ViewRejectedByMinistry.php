<?php

namespace App\Filament\Resources\RejectedByMinistryStatusResource\Pages;

use App\Filament\Resources\HasApplicationInfolist;
use App\Filament\Resources\RejectedByMinistryStatusResource;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Support\Enums\FontWeight;

class ViewRejectedByMinistry extends ViewRecord
{
    use HasApplicationInfolist;
    protected static string $resource = RejectedByMinistryStatusResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(static::getSharedInfolistSchema()) // Trait ichidagi metod
            ->columns(3);
    }
}
