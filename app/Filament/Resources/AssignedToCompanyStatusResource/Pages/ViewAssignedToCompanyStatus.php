<?php

namespace App\Filament\Resources\AssignedToCompanyStatusResource\Pages;

use App\Filament\Resources\AssignedToCompanyStatusResource;
use App\Filament\Resources\HasApplicationInfolist;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\NewStatusResource;
use App\Models\Status;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Telegram\Bot\Api;

class ViewAssignedToCompanyStatus extends ViewRecord
{
    use HasApplicationInfolist;
    protected static string $resource = AssignedToCompanyStatusResource::class;
    protected function getActions(): array
    {
        return [
            // shirkat tomonidan korildi va ish jarayoniga tushdi statusiga otkazish
            Action::make('inProgress')
                ->label('Is procesine túsiriw')
                ->color('warning')
                ->icon('heroicon-m-arrow-trending-up')
                ->modalHeading('Murojaatni ish jarayoniga tushirish')
                ->modalDescription('Bul múrájattı jumıs procesine túsiriwdi tastıyıqlaysız ba? Bul xabar paydalanıwshıǵa jiberiledi.')
                ->modalSubmitActionLabel('Túsiriw')
                ->action(function (array $data, $record): void {
                    // Ma'lumotlarni yangilash
                    $record->update([
                        'status_id' => Status::where('status', 'in_progress')->value('id'), // 'Ish jarayoniga tushdi' statusining ID si
                    ]);
                    $record->histories()->create([
                        'status_id' => Status::where('status', 'in_progress')->value('id'),
                        'changed_by' => auth()->id(),
                    ]);
                    $companyName = \App\Models\Company::find($record->company_id)?->name;

                    $telegram = new Api(env('TELEGRAM_BOT_TOKEN')); // yoki bevosita 'TOKEN' yozing
                    $chatId = $record->resident->telegram_id;
                    $text = "✅ *Múrájatıńız jumıs procesine túsirildi*\n\n" .
                            "Siziń {$record->id}-sanlı múrájatıńız {$companyName} shirketine biriktirildi hám házirde jumıs procesinde.\n\n" .
                            "Jumıs procesi haqqında jańalıqlar bolsa sizge xabar beriledi.";

                    try {
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $text,
                            'parse_mode' => 'Markdown',
                        ]);
                    } catch (\Exception $e) {
                        // Agar xabar ketmasa, admin panelda xato haqida ogohlantirish
                        Notification::make()
                            ->title('Telegram xabar jiberiwde qátelik')
                            ->danger()
                            ->send();
                    }

                    Notification::make()
                        ->title('Múrájat is procesine túsirildi hám paydalanıwshı xabardar etildi')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Action::make('rejectedByCompany')
                ->label('Biykarlaw')
                ->color('danger') // Qizil rang
                ->icon('heroicon-m-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Múrájattı biykarlaw')
                ->modalDescription('Bul múrájattı biykarlaw sebebin kirgiziń. Bul xabar paydalanıwshıǵa jiberiledi.')
                ->form([
                    Textarea::make('reason')
                        ->label('Biykarlaw sebebi')
                        ->required()
                        ->placeholder('Mısalı: Múrájat mazmunı túsiniksiz yamasa naduris magliwmat kirgizilgen..'),
                ])
                ->action(function (array $data, $record): void {
                    // 1. Bazada statusni yangilash
                    $record->update([
                        'status_id' => Status::where('status', 'rejected_by_company')->value('id'), // 'Kompaniya tomonidan rad etildi' status ID si
                    ]);
                    $record->histories()->create([
                        'status_id' => Status::where('status', 'rejected_by_company')->value('id'),
                        'comment' => $data['reason'], // Rad etish sababini tarixga yozish (ixtiyoriy)
                        'changed_by' => auth()->id(),
                    ]);

                    // 2. Telegramga xabar yuborish
                    $telegram = new Api(env('TELEGRAM_BOT_TOKEN')); // yoki
                    $chatId = $record->resident->telegram_id;
                    $reason = $data['reason'];
                    
                    $text = "❌ *Múrájatıńız biykar etildi*\n\n" .
                            "Siziń {$record->id}-sanlı múrájatıńız shirket tomonidan rad etildi.\n\n" .
                            "*Biykarlaw sebebi: {$reason}*";

                    try {
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $text,
                            'parse_mode' => 'Markdown',
                        ]);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Telegram xabar jiberiwde qátelik')
                            ->danger()
                            ->send();
                    }

                    Notification::make()
                        ->title('Múrájat biykar etildi hám paydalanıwshı xabardar etildi')
                        ->warning()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(static::getSharedInfolistSchema()) // Trait ichidagi metod
            ->columns(3);
    }
}
