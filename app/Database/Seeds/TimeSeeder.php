<?php

namespace App\Database\Seeds;

use App\Models\TimeModel;
use CodeIgniter\Database\Seeder;

class TimeSeeder extends Seeder
{
    public function run()
    {
        $datas = [
            [
                'time_name' => 'Pagi 1',
                'time_start' => '08:00',
                'time_end' => '16:00',
                'color' => '#0dff00'
            ],
            [
                'time_name' => 'Siang 1',
                'time_start' => '10:00',
                'time_end' => '18:00',
                'color' => '#232132'
            ],
            [
                'time_name' => 'Malam 1',
                'time_start' => '16:00',
                'time_end' => '22:00',
                'color' => '#232211'
            ],
        ];
        foreach($datas as $data){
            model(TimeModel::class)->save($data);
        }
    }
}
