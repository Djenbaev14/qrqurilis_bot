<?php

namespace App\Filament\Resources\InProgressStatusResource\Pages;

use App\Filament\Resources\HasApplicationInfolist;
use App\Filament\Resources\InProgressStatusResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Telegram\Bot\Api;

class ViewInProgressStatus extends ViewRecord
{
    use HasApplicationInfolist;
    protected static string $resource = InProgressStatusResource::class;
    protected function getActions(): array
    {
        return [
            // ish yakunlandi 
            Action::make('mark_as_completed')
                ->label('Jumıstı juwmaqlaw')
                ->color('warning')
                ->icon('heroicon-o-check')
                ->modalSubmitActionLabel('Jumıstı juwmaqlaw')
                ->action(function (array $data, $record): void {
                    // Ma'lumotlarni yangilash
                    $record->update([
                        'status_id' => Status::where('status', 'completed')->value('id'), // 'Yakunlandi' statusining ID si
                    ]);
                    $record->histories()->create([
                        'status_id' => Status::where('status', 'completed')->value('id'),
                        'changed_by' => auth()->id(),
                    ]);
                    $companyName = \App\Models\Company::find($record->company_id)?->name;

                    $telegram = new Api(env('TELEGRAM_BOT_TOKEN')); // yoki bevosita 'TOKEN' yozing
                    $chatId = $record->resident->telegram_id;
                    $text = "🔔 *Múrájat statusı jańalandı*\n\n" .
                            "Siziń {$record->id}-sanlı múrájatıńız *{$companyName}* shirketi juwmaqladi..";

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
                        ->title('Múrájat tabıslı baǵdarlandı hám paydalanıwshıǵa xabar berildi')
                        ->success()
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
