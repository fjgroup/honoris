<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Import the new seeders
use Database\Seeders\CitySeeder;
use Database\Seeders\BuildingTypeSeeder;
use Database\Seeders\UserSeeder; // Added UserSeeder import
use Database\Seeders\MapSeeder; // Add this

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]); // Commented out Test User factory

        $this->call([
            CitySeeder::class,
            BuildingTypeSeeder::class,
            UserSeeder::class, // Added UserSeeder to the call array
            MapSeeder::class, // Add this line
        ]);
    }
}
