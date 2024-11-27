<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Substation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $arrStation = [
            [
                'code' => 'b12_register',
                'name' => 'Register',
                'station' => 11,
            ],
            [
                'code' => 'b12_vitalsign',
                'name' => 'วัดความดัน',
                'station' => 5,
            ],
            [
                'code' => 'b12_lab',
                'name' => 'ห้องเจาะเลือด',
                'station' => 5,
            ],
            [
                'code' => 'b12_ekg',
                'name' => 'EKG',
                'station' => 2,
            ],
            [
                'code' => 'b12_abi',
                'name' => 'ABI',
                'station' => 1,
            ],
            [
                'code' => 'b12_echo',
                'name' => 'EST|ECHO',
                'station' => 1,
            ],
            [
                'code' => 'b12_chest',
                'name' => 'X-RAY',
                'station' => 1,
            ],
            [
                'code' => 'b12_ultrasound',
                'name' => 'ULTRASOUND',
                'station' => 5,
            ],
            [
                'code' => 'b12_mammogram',
                'name' => 'MAMMOGRAM',
                'station' => 1,
            ],
            [
                'code' => 'b12_boneden',
                'name' => 'BONE DEN',
                'station' => 1,
            ],
            [
                'code' => 'b12_gny',
                'name' => 'GYNE',
                'station' => 2,
            ],
        ];

        foreach( $arrStation as $data ) {
            $station = new Station([ 'code' => $data['code'], 'name' => $data['name']]);
            $station->save();
            for ($i=1; $i <= $data['station']; $i++) { 
                $substation = new Substation(['station_id' => $station->id, 'name' => $station->name.' ห้อง '. $i,]);
                $substation->save();
            }

        }        
    }
}
