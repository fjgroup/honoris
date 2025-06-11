<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BuildingType; // Import the BuildingType model

class BuildingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildingTypes = [
            ['name' => 'Aserradero'],
            ['name' => 'Cabaña del cazador'],
            ['name' => 'Cantera'],
            ['name' => 'Carnicería'],
            ['name' => 'Cocina'],
            ['name' => 'Curtiduría'],
            ['name' => 'Fábrica de herramientas'],
            ['name' => 'Forja del guerrero'],
            ['name' => 'Fundición'],
            ['name' => 'Guarnicionería'],
            ['name' => 'Laboratorio de alquimia'],
            ['name' => 'Molino'],
            ['name' => 'Tejeduría'],
            ['name' => 'Torre del mago'],
        ];

        foreach ($buildingTypes as $buildingType) {
            BuildingType::create($buildingType);
        }
    }
}
