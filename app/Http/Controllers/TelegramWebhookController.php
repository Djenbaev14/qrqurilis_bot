<?php

namespace App\Http\Controllers;

use App\Console\Commands\StartCommand;
use App\Jobs\FinishTelegramAlbum;
use App\Models\Application;
use App\Models\ApplicationPhoto;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    
    public function handle(Request $request)
    {
        try {
            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
            $update = $telegram->getWebhookUpdate();

            $message = $update->getMessage();
            if (!$message) {
                return response('ok', 200);
            }
            $chatId = $message->getChat()->getId();
            $text = $message->getText();
            $data= Cache::get("tg_data_$chatId", []);
            $state = Cache::get("tg_state_$chatId");

            // /start
            if ($text === '/start') {
                Cache::forget("tg_state_$chatId");
                Cache::forget("tg_data_$chatId");
                Cache::forget("tg_album_files_$chatId");
                if(Cache::has("tg_lang_$chatId")){
                    // agar residents jadvalda telegram_id bo'yicha mavjud bo'lsa, to'g'ridan-to'g'ri telefon raqamini so'raymiz
                    $resident = Resident::where('telegram_id', $chatId)->first();
                    if($resident){
                        // agar mavjud bo'lmasa ,kerakli amalni tanlasin 1.mening murojaatlarim 2.yangi murojaat
                        $keyboard = Keyboard::make()
                            ->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(false)
                            ->row([
                                Keyboard::button(['text' => $this->t($chatId, 'new_request')]),
                                Keyboard::button(['text' => $this->t($chatId, 'my_requests')]),
                            ]);
                            
                        return $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $this->t($chatId, 'select_action'),
                            'reply_markup' => $keyboard,
                        ]);
                    }
                    Cache::put("tg_state_$chatId", 'phone');
                    $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->row([
                            Keyboard::button([
                                'text' => $this->t($chatId, 'contact_button'),
                                'request_contact' => true,
                            ]),
                        ]);
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'enter_phone'),
                        'reply_markup' => $keyboard,
                    ]);
                }else{
                    Cache::put("tg_state_$chatId", 'choose_lang');
                    $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->row([
                            Keyboard::button(['text' => 'O‘zbekcha']),
                            Keyboard::button(['text' => 'Qaraqalpaqsha']),
                        ]);

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Tilni tanlang / Tildi saylań🔽",
                        'reply_markup' => $keyboard
                    ]);
                }


                return response('ok', 200);
            }
            if ($text === $this->t($chatId, 'my_requests')) {
                $resident = Resident::where('telegram_id', $chatId)->first();
                
                if ($resident) {
                    // Munosabatlarni (with) birga yuklaymiz, bazaga so'rov kamayishi uchun
                    $requests = Application::with(['status', 'region', 'photos'])
                        ->where('resident_id', $resident->id)
                        ->latest()
                        ->get();

                    if ($requests->count() > 0) {
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "<b>📋 Sizning jami murojaatlaringiz: " . $requests->count() . " ta</b>",
                            'parse_mode' => 'HTML'
                        ]);

                        foreach ($requests as $request) {
                            $caption = "🆔 <b>Murojaat №{$request->id}</b>\n"
                                . "📅 <b>Vaqti:</b> " . $request->created_at->format('d.m.Y H:i') . "\n"
                                . "📍 <b>Region:</b> {$request->region->name['qr']}\n"
                                . "🏠 <b>Manzil:</b> {$request->address}\n"
                                . "📝 <b>Xabar:</b> {$request->message}\n"
                                . "🚥 <b>Status:</b> <u>" . ($request->status->name['qr'] ?? 'Noma\'lum') . "</u>";

                            // Agar rasmlar bo'lsa
                            if ($request->photos->count() > 0) {
                                $media = [];
                                $files = [];

                                foreach ($request->photos as $index => $photo) {

                                    $filePath = storage_path("app/public/{$photo->photo_path}");

                                    if (!file_exists($filePath)) {
                                        continue;
                                    }

                                    $attachName = "photo{$index}";

                                    $mediaItem = [
                                        'type' => 'photo',
                                        'media' => "attach://{$attachName}",
                                    ];

                                    // Faqat birinchi rasmga caption
                                    if ($index === 0) {
                                        $mediaItem['caption'] = (string) $caption; // majburiy string
                                        $mediaItem['parse_mode'] = 'HTML';
                                    }

                                    $media[] = $mediaItem;

                                    $files[$attachName] = InputFile::create($filePath);
                                }

                                if (!empty($media)) {
                                    $telegram->sendMediaGroup(array_merge([
                                        'chat_id' => $chatId,
                                        'media'   => json_encode($media),
                                    ], $files));
                                }
                            } else {
                                // Rasm bo'lmasa faqat matnni yuboramiz
                                $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => $caption,
                                    'parse_mode' => 'HTML'
                                ]);
                            }
                        }
                    } else {
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "📭 Sizda hali hech qanday murojaat yo'q."
                        ]);
                    }
                } else {
                    // Ro'yxatdan o'tmagan foydalanuvchi uchun (Sizning kodingiz)
                    Cache::put("tg_state_$chatId", 'phone');
                    $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->row([
                            Keyboard::button([
                                'text' => $this->t($chatId, 'contact_button'),
                                'request_contact' => true,
                            ]),
                        ]);
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Iltimos, avval telefon raqamingizni yuboring",
                        'reply_markup' => $keyboard,
                    ]);
                }
            }
            if ($text === $this->t($chatId, 'new_request')) {
                $resident = Resident::where('telegram_id', $chatId)->first();

                if (!$resident) {
                    // Agar ro'yxatdan o'tmagan bo'lsa telefon bosqichiga qaytaramiz
                    Cache::put("tg_state_$chatId", 'phone');

                    $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->row([
                            Keyboard::button([
                                'text' => $this->t($chatId, 'contact_button'),
                                'request_contact' => true,
                            ]),
                        ]);

                    return $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'enter_phone'),
                        'reply_markup' => $keyboard,
                    ]);
                }

                // 🔥 Resident bor — murojaatni boshlaymiz
                $data = [
                    'resident_id' => $resident->id,
                    'phone' => $resident->phone,
                    'fio' => $resident->full_name,
                ];

                Cache::put("tg_data_$chatId", $data);
                Cache::put("tg_state_$chatId", 'region');

                // Regionlarni chiqaramiz
                $regions = \App\Models\Region::all();
                $keyboard = Keyboard::make()->setResizeKeyboard(true)->setOneTimeKeyboard(true);
                $userLang = Cache::get("tg_lang_$chatId", 'uz');

                $buttons = [];

                foreach ($regions as $region) {
                    $buttons[] = Keyboard::button([
                        'text' => $region->name[$userLang]
                    ]);
                }

                foreach (array_chunk($buttons, 2) as $chunk) {
                    $keyboard->row($chunk);
                }

                return $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $this->t($chatId, 'select_district'),
                    'reply_markup' => $keyboard
                ]);
            }
            
            if ($text === $this->t($chatId, 'confirm')) {
            
                    $data = Cache::get("tg_data_$chatId");
                    $album = Cache::get("tg_album_files_$chatId", []);

                    $application=Application::create([
                        'resident_id' => $data['resident_id'],
                        'region_id'   => $data['region_id'],
                        'address'     => $data['address'],
                        'message'     => $data['message'],
                        'status_id'   => 1
                    ]);
                    // application_status_histories jadvaliga kirish
                    $application->histories()->create([
                        'status_id' => 1,
                        'comment' => 'Murojaat qabul qilindi' ,   
                    ]);
                    foreach ($album as $filePath) {
                        $application->photos()->create([
                            'photo_path' => $filePath
                        ]);
                    }

                    Cache::forget("tg_data_$chatId");
                    Cache::forget("tg_state_$chatId");
                    Cache::forget("tg_album_files_$chatId");
                    $keyboard = Keyboard::make()
                            ->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(false)
                            ->row([
                                Keyboard::button(['text' => $this->t($chatId, 'new_request')]),
                                Keyboard::button(['text' => $this->t($chatId, 'my_requests')]),
                            ]);

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'request_done'),
                        'reply_markup' => $keyboard
                    ]);

                    return response('ok');
            }
            if($text === $this->t($chatId, 'cancel')){
                    Cache::forget("tg_data_$chatId");
                    Cache::forget("tg_state_$chatId");

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'request_cancel'),
                        'reply_markup' => Keyboard::remove()
                    ]);

                    return response('ok');
            }
            if($state === 'ask_media'){
                if ($text === $this->t($chatId, 'yes')) {
                        
                        Cache::put("tg_state_$chatId", 'waiting_media');
                        $keyboard=Keyboard::remove();

                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $this->t($chatId, 'photo_or_video'),
                            'reply_markup' => $keyboard 
                        ]);

                        return response('ok', 200);
                }
                else if($text === $this->t($chatId, 'no')) {
                        Cache::put("tg_state_$chatId", 'confirming');
                        $keyboard = Keyboard::make()
                            ->setResizeKeyboard(true)
                            ->row([
                                Keyboard::button(['text' => $this->t($chatId, 'confirm')]),
                                Keyboard::button(['text' => $this->t($chatId, 'cancel')]),
                            ]);

                        $textMessage = "
                            <b>📨 YANGI MUROJAAT</b>

                            👤 <b>F.I.Sh:</b> {$data['fio']}
                            📞 <b>Telefon:</b> {$data['phone']}
                            📍 <b>Manzil:</b> {$data['region_name']}, {$data['address']}

                            ───────────────

                            📝 <b>Murojaat:</b>
                            {$data['message']}
                            ";

                            $telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => $textMessage,
                                'parse_mode' => 'HTML',
                                'reply_markup' => $keyboard
                            ]);
                        return response('ok', 200);
                }
                else{
                    $keyboard = \Telegram\Bot\Keyboard\Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->row([
                            \Telegram\Bot\Keyboard\Keyboard::button(['text' => $this->t($chatId, 'yes')]),
                            \Telegram\Bot\Keyboard\Keyboard::button(['text' => $this->t($chatId, 'no')]),
                        ]);
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $this->t($chatId, 'invalid_option'),
                            'reply_markup' => $keyboard
                        ]);
                        return response('ok', 200);
                }
            }
            if ($state === 'waiting_media') {

                $albumKey = "tg_album_files_{$chatId}";
                
                if ($message->getPhoto()) {

                    $photos = $message->getPhoto();
                    $photoArray = is_array($photos) ? $photos : $photos->all();
                    $lastPhoto = end($photoArray);
                    log::info("Last Photo ID: " . ($lastPhoto ? $lastPhoto->file_id : 'none'));
                    if ($lastPhoto) {

                        $file = Telegram::getFile([
                            'file_id' => $lastPhoto->file_id
                        ]);
                        

                        $filePath = $file->getFilePath();

                        $contents = file_get_contents(
                            "https://api.telegram.org/file/bot"
                            . env('TELEGRAM_BOT_TOKEN')
                            . "/"
                            . $filePath
                        );

                        $fileName = 'uploads/images/' . Str::uuid() . '.jpg';
                        \Storage::disk('public')->put($fileName, $contents);
                        $files = Cache::get($albumKey, []);
                        $files[] = $fileName;
                        Cache::put($albumKey, $files, now()->addMinutes(10));
                        Log::info(Cache::get($albumKey));
                    }
                }

                if ($message->getVideo()) {

                    $video = $message->getVideo();

                    $file = Telegram::getFile([
                        'file_id' => $video->getFileId()
                    ]);

                    $filePath = $file->getFilePath();

                    $contents = file_get_contents(
                        "https://api.telegram.org/file/bot"
                        . env('TELEGRAM_BOT_TOKEN')
                        . "/"
                        . $filePath
                    );

                    $fileName = 'uploads/videos/' . uniqid() . '.mp4';
                    \Storage::disk('public')->put($fileName, $contents);

                    $files = Cache::get($albumKey, []);
                    $files[] = $fileName;
                    Cache::put($albumKey, $files, now()->addMinutes(10));
                }
                
                
                $keyboard = Keyboard::make()->setResizeKeyboard(true)->row([
                    Keyboard::button(['text' => $this->t($chatId, 'confirm')]),
                    Keyboard::button(['text' => $this->t($chatId, 'cancel')]),
                ]);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $this->t($chatId, 'all_files_saved'),
                    'reply_markup' => $keyboard
                ]);
                return response('ok', 200);
            }


            switch ($state) {

                case 'choose_lang':
                    if ($text === 'O‘zbekcha') {
                        Cache::put("tg_lang_$chatId", 'uz');
                    } elseif ($text === 'Qaraqalpaqsha') {
                        Cache::put("tg_lang_$chatId", 'qr');
                    } else {
                        return response('ok', 200);
                    }

                    $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->row([
                            Keyboard::button([
                                'text' => $this->t($chatId, 'contact_button'),
                                'request_contact' => true,
                            ]),
                        ]);
                    Cache::put("tg_state_$chatId", 'phone');

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'enter_phone'),
                        'reply_markup' => $keyboard,
                    ]);

                    break;

                case 'phone':

                    if (!$message->getContact()) {
                        return $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $this->t($chatId, 'wrong_contact'),
                        ]);
                    }

                    $contact = $message->getContact();

                    if ($contact->getUserId() != $message->getFrom()->getId()) {
                        return $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $this->t($chatId, 'wrong_contact'),
                        ]);
                    }

                    $phone = $contact->getPhoneNumber();

                    $data = Cache::get("tg_data_$chatId", []);
                    $data['phone'] = $phone;
                    Cache::put("tg_data_$chatId", $data);

                    Cache::put("tg_state_$chatId", 'fio');

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'enter_fio'),
                        'reply_markup' => Keyboard::remove()
                    ]);

                    break;

                case 'fio':
                    $data['fio'] = $text;
                    Cache::put("tg_data_$chatId", $data);
                    Cache::put("tg_state_$chatId", 'region');
                    $resident = Resident::updateOrCreate(
                        ['telegram_id' => $chatId],
                        [
                            'username' => $message->getFrom()->getUsername(),
                            'phone' => $data['phone'],
                            'full_name' => $data['fio'],
                        ]
                    );
                    
                    $data['resident_id'] = $resident->id;
                    Cache::put("tg_data_$chatId", $data);

                    $regions = \App\Models\Region::all(); // jadvaldan barcha regionlar
                    $buttons = [];
                    $keyboard = Keyboard::make()->setResizeKeyboard(true)->setOneTimeKeyboard(true);
                    $userLang = Cache::get("tg_lang_$chatId", 'uz');

                    foreach ($regions as $region) {
                        $buttons[] = Keyboard::button([
                            'text' => $region->name[$userLang] // yoki $region->name[$userLang] 
                        ]);
                    }
                    $chunks = array_chunk($buttons, 2);
                    foreach ($chunks as $chunk) {
                        $keyboard->row($chunk); // row() bilan qo‘shiladi
                    }
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'select_district'),
                        'reply_markup' => $keyboard
                    ]);
                    break;

                case 'region':
                    $userLang = Cache::get("tg_lang_$chatId", 'uz'); // foydalanuvchi tili
                    $selectedRegionName = $text;
                    $region = \App\Models\Region::where("name->$userLang", $selectedRegionName)->first();

                    if (!$region) {
                        // Noto‘g‘ri tanlangan bo‘lsa
                        $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $this->t($chatId, 'invalid_region')
                        ]);
                        return response('ok', 200);
                    }

                    
                    $data['region_id'] = $region->id;
                    $data['region_name'] = $selectedRegionName;
                    Cache::put("tg_data_$chatId", $data);
                    Cache::put("tg_state_$chatId", 'address');

                    $keyboard = Keyboard::remove();
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'enter_address'),
                        'reply_markup' => $keyboard
                    ]);
                    break;

                case 'address':
                    $data['address'] = $text;
                    Cache::put("tg_data_$chatId", $data);
                    Cache::put("tg_state_$chatId", 'message');

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'enter_message')
                    ]);
                    break;
                case 'message':
                    $data['message'] = $text;
                    Cache::put("tg_data_$chatId", $data);
                    Cache::put("tg_state_$chatId", 'ask_media');

                    $keyboard = \Telegram\Bot\Keyboard\Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->row([
                            \Telegram\Bot\Keyboard\Keyboard::button(['text' => $this->t($chatId, 'yes')]),
                            \Telegram\Bot\Keyboard\Keyboard::button(['text' => $this->t($chatId, 'no')]),
                        ]);

                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->t($chatId, 'add_media'),
                        'reply_markup' => $keyboard
                    ]);

                    break;
            }
            return response('ok', 200);
        } catch (\Throwable $th) {
            
            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
            $telegram->sendMessage([
                'chat_id' => env('TELEGRAM_MY_CHAT_ID'),
                'text' => $th->getMessage() . ' on line ' . $th->getLine() . ' in ' . $th->getFile()
            ]);
        }
        // Album tugaganini tekshirish
        

    }
    
    public function t($chatId, $key)
    {
        $lang = Cache::get("tg_lang_$chatId", 'uz');

        $texts = [

            'uz' => [
                'select_action'=>"Kerakli amalni tanlang:",
                'my_requests' => "📋 Mening murojaatlarim",
                'new_request' => "✍️ Yangi murojaat yo‘llash",
                'enter_phone' => "Telefon raqamingizni yuboring 👇",
                'contact_button' => "📞 Kontakt yuborish",
                'wrong_contact' => "❗️ Iltimos, \"Kontakt yuborish\" tugmasidan foydalaning. Boshqa odamning raqamini yubormang.",
                'enter_fio' => "Familiya va ismingizni kiriting 👤",
                'select_district' => "Hududingizni tanlang 🏢",
                'enter_address' => "Manzilingizni kiriting 🏢",
                'enter_message' => "Murojaatingizni kiriting ✍️",
                'add_media' => "Rasm yoki video qo‘shasizmi? 📎",
                'file_received' => "📎 Fayl qabul qilindi",
                'invalid_region'=>'❗️Noto‘g‘ri hudud tanlandi. Iltimos, ro‘yxatdan birini tanlang.',
                'photo_or_video'=>"Rasm yoki video jonating",
                'yes' => "Ha",
                'no' => "Yo‘q",
                'all_files_saved' => "📸 Barcha fayllar saqlandi.",
                'cancel' => "❌ Bekor qilish",
                'confirm' => "✅ Tasdiqlash",
                'invalid_option' => "❗️ Noto‘g‘ri tanlov. Iltimos, ko‘rsatilgan tugmalardan birini tanlang.",
                'request_done'=>'✅ Murojaatingiz qabul qilindi! Tez orada ko‘rib chiqamiz',
                'request_cancel'=>'❌ Murojaatingiz bekor qilindi',
                
            ],

            'qr' => [
                'select_action'=>"Kerekli ámeldi tańlań:",
                'my_requests' => "📋 Meniń múrájatlarım",
                'new_request' => "✍️ Múrájat jollaw",
                'enter_phone' => "Telefon nomerińizdi jiberiń 👇",
                'contact_button' => "📞 Kontakt jiberiw",
                'wrong_contact' => "❗️ Iltimas, \"Kontakt jiberiw\" túymesinen paydalanıń. Basqa adamnıń nomerin jibermeń.",
                'enter_fio' => "Familiya hám atıńızdı jazıń 👤",
                'select_district' => "Aymaǵıńızdı tańlań🏢",
                'enter_address' => "Mánzilińizdi jazıń 🏢",
                'enter_message' => "Múrájatińizdi jazıń ✍️",
                'invalid_region'=>'❗️Nadurıs aymaq tańlandı. Iltimas, dizimnen birewin tanlań',
                'add_media' => "Foto yamasa video qosasızba? 📎",
                'photo_or_video'=>"Súwret yamasa video jiberiń",
                'file_received' => "📎 Fayl qabıllandı",
                'all_files_saved' => "📸 Barlıq fayllar saqlandı.",
                'yes' => "Awa",
                'no' => "Yaq",
                'cancel' => "❌ Biykarław",
                'confirm' => "✅ Tastiyqlaw",
                'invalid_option' => "❗️ Nadurıs tańlaw. Iltimas, kórsetilgen túymelerden birin tańlań..",
                'request_done'=>'✅ Múrájatıńız qabıl etildi! Tez arada kórip shıǵamız',
                'request_cancel'=>'❌ Múrájatıńız biykar etildi'
            ],
        ];

        return $texts[$lang][$key] ?? $texts['uz'][$key];
    }

    public function finishAlbum($chatId)
    {

        Cache::put("tg_state_$chatId", 'confirming');
        
        $keyboard = Keyboard::make()->setResizeKeyboard(true)->row([
            Keyboard::button(['text' => $this->t($chatId, 'confirm')]),
            Keyboard::button(['text' => $this->t($chatId, 'cancel')]),
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->t($chatId, 'all_files_saved') ,
            'reply_markup' => $keyboard
        ]);
    }

    
}
