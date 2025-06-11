<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City; // Import the City model

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Bridgewatch'],
            ['name' => 'Caerleon'],
            ['name' => 'Fort Sterling'],
            ['name' => 'Lymhurst'],
            ['name' => 'Martlock'],
            ['name' => 'Thetford'],
            ['name' => 'Brecilien'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
