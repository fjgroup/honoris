<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City; // Import City model
use App\Models\Map;   // Import Map model
use Illuminate\Support\Str; // Import Str facade for slug generation
use Illuminate\Support\Facades\Storage; // Import Storage facade

class MapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::all(); // Get all cities seeded previously

        if ($cities->isEmpty()) {
            $this->command->warn('No cities found. Please run CitySeeder first or ensure cities exist.');
            return;
        }

        foreach ($cities as $city) {
            $imageName = Str::slug($city->name) . '_map.jpg'; // e.g., bridgewatch_map.jpg

            // Path to be stored in DB. Assumes files are in `public/storage/map_images/`
            // This path should be usable with asset() helper or directly as a public URL part.
            $publicUrlPath = '/storage/map_images/' . $imageName;

            // For this to work, the user must:
            // 1. Place corresponding image files in `storage/app/public/map_images/`
            //    (e.g., storage/app/public/map_images/bridgewatch_map.jpg)
            // 2. Run `php artisan storage:link` to create the symlink from `public/storage` to `storage/app/public`.

            Map::create([
                'city_id' => $city->id,
                'name' => 'Map of ' . $city->name,
                'image_path' => $publicUrlPath, // Store the expected public URL path
                'description' => 'Default map for the city of ' . $city->name,
                // 'width' and 'height' (natural dimensions) are not set here.
                // They could be set if known, or via a separate process that inspects images.
            ]);
        }
        $this->command->info(count($cities) . ' maps created, one for each city, with public URL paths for images.');
    }
}
