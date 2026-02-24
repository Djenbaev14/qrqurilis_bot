<?php

namespace App\Filament\Resources\NewStatusResource\Pages;

use App\Filament\Resources\HasApplicationInfolist;
use App\Filament\Resources\NewStatusResource;
use App\Models\Status;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Telegram\Bot\Api;

class ViewNewStatus extends ViewRecord
{
    use HasApplicationInfolist;
    protected static string $resource = NewStatusResource::class;
    protected function getActions(): array
    {
        return [
            Action::make('sendToCompany')
                ->label('Shirketke jiberiw')
                ->color('warning')
                ->icon('heroicon-m-paper-airplane')
                ->modalHeading('Sheriklikti tańlań')
                ->modalDescription('Usı múrájattı kórip shıǵıw ushın juwapker shirketti belgileń.')
                ->modalSubmitActionLabel('Jiberiw')
                // Modal ichidagi forma
                ->form([
                    Select::make('company_id')
                        ->label('Shirket atı')
                        ->options(\App\Models\Company::all()->pluck('name', 'id')) // Barcha shirkatlar ro'yxati
                        ->searchable()
                        ->required()
                        ->placeholder('Sheriklikti tańlań...'),
                ])
                ->action(function (array $data, $record): void {
                    // Ma'lumotlarni yangilash
                    $record->update([
                        'company_id' => $data['company_id'],
                        'status_id' => Status::where('status', 'assigned_to_company')->value('id'), // 'Shirkatga yuborildi' statusining ID si
                    ]);
                    $record->histories()->create([
                        'status_id' => Status::where('status', 'assigned_to_company')->value('id'),
                        'changed_by' => auth()->id(), // O'zgarishni amalga oshirgan foydalanuvchi ID si
                    ]);
                    $companyName = \App\Models\Company::find($data['company_id'])?->name;

                    $telegram = new Api(env('TELEGRAM_BOT_TOKEN')); // yoki bevosita 'TOKEN' yozing
                    $chatId = $record->resident->telegram_id;
                    $text = "🔔 *Múrájat statusı jańalandı*\n\n" .
                            "Siziń {$record->id}-sanlı múrájatıńız kórip shıǵıw ushın *{$companyName}* shirketine biriktirildi..";

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
                        ->title('Múrájat tabıslı baǵdarlandı hám paydalanıwshıǵa xabar berildi')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                // Agar allaqachon biriktirilgan bo'lsa, tugmani yashirish (ixtiyoriy)
                ->visible(fn ($record) => $record->company_id === null),
            Action::make('rejectByMinistry')
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
                        'status_id' => Status::where('status', 'rejected_by_ministry')->value('id'), // 'Vazirlik rad etdi' status ID si
                    ]);
                    $record->histories()->create([
                        'status_id' => Status::where('status', 'rejected_by_ministry')->value('id'),
                        'comment' => $data['reason'], // Rad etish sababini tarixga yozish (ixtiyoriy)
                        'changed_by' => auth()->id(),
                    ]);

                    // 2. Telegramga xabar yuborish
                    $telegram = new Api(env('TELEGRAM_BOT_TOKEN')); // yoki
                    $chatId = $record->resident->telegram_id;
                    $reason = $data['reason'];
                    
                    $text = "❌ *Múrájatıńız biykar etildi*\n\n" .
                            "Siziń {$record->id}-sanlı múrájatıńız qánigeler tárepinen kórip shıǵıldı hám biykar etildi..\n\n" .
                            "*Sebebi:* {$reason}";

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
                })
                // Agar allaqachon rad etilgan yoki bajarilgan bo'lsa tugmani ko'rsatmaslik
                ->hidden(fn ($record) => in_array($record->status_id, [4, 3])),
        ];
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(static::getSharedInfolistSchema()) // Trait ichidagi metod
            ->columns(3);
    }
}
