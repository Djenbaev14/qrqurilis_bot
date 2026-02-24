<?php

namespace App\Jobs;

use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;   // 👈 SHU MUHIM
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class FinishTelegramAlbum implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // 👈 SHU MUHIM

    protected $chatId;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    public function handle()
    {
        $albumKey = "tg_album_files_{$this->chatId}";
        $files = Cache::get($albumKey);

        if (!$files) {
            return;
        }
        Cache::put("tg_state_{$this->chatId}", 'confirming');
        // Cache::forget($albumKey);
        // Cache::forget("tg_state_{$this->chatId}");
        // Cache::forget("tg_data_{$this->chatId}");
        $controller = new \App\Http\Controllers\TelegramWebhookController();
        $confirm = $controller->t($this->chatId, 'confirm');
        $cancel  = $controller->t($this->chatId, 'cancel');
        $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->row([
                            Keyboard::button(['text' => $confirm]),
                            Keyboard::button(['text' => $cancel]),
                        ]);
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => $controller->t($this->chatId, 'all_files_saved'),
            'reply_markup' => $keyboard
        ]);
    }
}