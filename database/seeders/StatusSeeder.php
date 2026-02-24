<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses= [
            [
                'status'=>'new',
                "name"=>[
                    "uz"=>"yangi",
                    "qr"=>"jaña",
                ],
            ],
            // [
            //     'status'=>'under_review',
            //     "name"=>[
            //         "uz"=>"Vazirlik ko‘rib chiqmoqda",
            //         "qr"=>"Ministrlik kórip shıqpaqta",
            //     ],
            // ],
            [
                'status'=>'rejected_by_ministry',
                "name"=>[
                    "uz"=>"Vazirlik rad etdi",
                    "qr"=>"Ministrlik biykarladı",
                ],
            ],
            [
                'status'=>'assigned_to_company',
                "name"=>[
                    "uz"=>"Shirkatga yuborildi",
                    "qr"=>"Shirketke jiberildi",
                ],
            ],
            // [
            //     'status'=>'accepted_by_company',
            //     "name"=>[
            //         "uz"=>"Shirkat qabul qildi",
            //         "qr"=>"Shirket qabılladı",
            //     ],
            // ],
            [
                'status'=>'in_progress',
                "name"=>[
                    "uz"=>"Ish jarayonda",
                    "qr"=>"Jumıs procesinde",
                ],
            ],
            [
                'status'=>'completed',
                "name"=>[
                    "uz"=>"Ish tugallandi",
                    "qr"=>"Jumıs tamamlandı",
                ],
            ],
            [
                'status'=>'confirmed_by_citizen',
                "name"=>[
                    "uz"=>"Fuqaro tasdiqladi",
                    "qr"=>"Puqara tastıyıqladı",
                ],
            ],
            [
                'status'=>'rejected_by_company',
                "name"=>[
                    "uz"=>"Shirkat rad etdi",
                    "qr"=>"Shirket biykarladı",
                ],
            ],
        ];
        
        foreach ($statuses as $status) {
            Status::create([
                'status' => $status['status'],
                'name' => $status['name'],
            ]);
        }
    }
}
