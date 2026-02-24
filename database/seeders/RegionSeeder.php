<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        $regions=[
            [
            "name_uz" => "Amudaryo tumani",
            "name_qr" => "Ámiwdárya rayonı",
            ],
            [
                "name_uz" => "Beruniy tumani",
                "name_qr" => "Beruniy rayonı",
            ],
            [
                "name_uz" => "Qorao'zak tumani",
                "name_qr" => "Qaraózek rayonı",
            ],
            [
                "name_uz" => "Kegeyli tumani",
                "name_qr" => "Kegeyli rayonı",
            ],
            [
                "name_uz" => "Qo'ng'irot tumani",
                "name_qr" => "Qońırat rayonı",
            ],
            [
                "name_uz" => "Qanliko'l tumani",
                "name_qr" => "Qanlıkól rayonı",
            ],
            [
                "name_uz" => "Mo'ynoq tumani",
                "name_qr" => "Moynaq rayonı",
            ],
            [
                "name_uz" => "Nukus tumani",
                "name_qr" => "Nókis rayonı",
            ],
            [
                "name_uz" => "Taxiatosh tumani",
                "name_qr" => "Taqıyatas rayonı",
            ],
            [
                "name_uz" => "Taxtako'pir tumani",
                "name_qr" => "Taxtakópir rayonı",
            ],
            [
                "name_uz" => "To'rtko'l tumani",
                "name_qr" => "Tórtkúl rayonı",
            ],
            [
                "name_uz" => "Xo'jayli tumani",
                "name_qr" => "Xojeli rayonı",
            ],
            [
                "name_uz" => "Chimboy tumani",
                "name_qr" => "Shımbay rayonı",
            ],
            [
                "name_uz" => "Shumanay tumani",
                "name_qr" => "Shomanay rayonı",
            ],
            [
                "name_uz" => "Ellikqal'a tumani",
                "name_qr" => "Ellikqala rayonı",
            ],
            [
                "name_uz" => "Nukus",
                "name_qr" => "Nókis qalası",
            ],
        ];
        // region create
        foreach ($regions as $region) {
            \App\Models\Region::create([
                'name' => [
                    'uz' => $region['name_uz'],
                    'qr' => $region['name_qr'],
                ]
            ]);
        }
        }
}
