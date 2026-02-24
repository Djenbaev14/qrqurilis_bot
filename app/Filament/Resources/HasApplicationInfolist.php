<?php
namespace App\Filament\Resources;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Enums\FontWeight;

trait HasApplicationInfolist
{
    public static function getSharedInfolistSchema(): array
    {
        return [
            Section::make('Múrájat maǵlıwmatları')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('resident.full_name')
                                ->label('FIO')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('resident.phone')
                                ->label('Telefon nomeri')
                                ->copyable(),
                            TextEntry::make('region.name.qr')
                                ->label('Rayon'),
                            TextEntry::make('address')
                                ->label('Address'),
                        ]),
                    
                    TextEntry::make('message')
                        ->label('Mashqala sıpatlaması')
                        ->columnSpanFull()
                        ->prose(),
                ])->columnSpan(2),

            Group::make()
                ->schema([
                    Section::make('Múrájat statusı')
                        ->schema([
                            TextEntry::make('status.name.qr')
                                ->label('Házirgi status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'jaña' => 'info',
                                    'Shirketke jiberildi' => 'warning',
                                    'Jumıs procesinde' => 'warning',
                                    'Jumıs tamamlandı' => 'success',
                                    'Puqara tastıyıqladı' => 'success',
                                    'Ministrlik biykarladı' => 'danger',
                                    'Shirket biykarladı' => 'danger',
                                    default => 'warning',
                                }),
                            TextEntry::make('created_at')
                                ->label('Jiberilgen waqıt')
                                ->dateTime(),
                        ]),
                ])->columnSpan(1),

            Section::make('Biriktirilgen fayllar (Súwret/Video)')
                ->schema([
                    RepeatableEntry::make('photos') 
                        ->label('Media fayllar')
                        ->schema([
                            ViewEntry::make('photo_path')
                                ->hiddenLabel()
                                ->view('filament.components.image-viewer'),
                        ])
                        ->grid(3)
                ])
                ->visible(fn ($record) => $record->photos->count() > 0)
                ->columnSpanFull(),
        ];
    }
}