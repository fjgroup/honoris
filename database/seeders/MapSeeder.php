<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City; // Import City model
use App\Models\Map;   // Import Map model
use Illuminate\Support\Str; // Import Str facade for slug generation

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
            // Generate a simple image path based on city name
            // User will need to place actual images matching these paths in `storage/app/public/map_images/`
            // And ensure `php artisan storage:link` has been run.
            // The path saved here will be accessible via Storage::url() if stored like 'public/map_images/...'
            // For simplicity, we'll store a relative path that implies it's in the public map images directory.
            $imageName = Str::slug($city->name) . '_map.jpg'; // e.g., bridgewatch_map.jpg

            // This path assumes that when displayed, it will be prefixed by `storage/` (publicly accessible)
            // and that the actual files are in `storage/app/public/map_images/`.
            // The MapController's store method uses `Storage::url($path)` where $path is 'public/map_images/filename.jpg'.
            // So, to be consistent for display, we should store the path as it would be after Storage::url(),
            // but without the leading /storage. Or, ensure the model accessor for image_path always uses Storage::url().
            // For simplicity, let's store the path as 'map_images/filename.jpg' and assume the model accessor
            // or Vue component will prepend '/storage/' or use `Storage::url()` if the path doesn't start with http.
            // The MapController store method saves it as Storage::url($path), which gives a full URL.
            // To keep it simple and consistent with how the MapController saves it (which includes /storage/),
            // we should aim for that. However, Storage::url() is for retrieving.
            // When seeding, we just define the path *within* the public disk.
            // So, 'map_images/' . $imageName is correct for the database if the accessor prepends Storage::url's base.
            // Or, if the accessor expects a path already including /storage, then:
            // $publicPath = 'map_images/' . $imageName;
            // $imageStoragePathForDb = '/storage/' . $publicPath; // This is how it would look if Storage::url() was used on 'public/map_images/...'
            // For now, let's stick to 'map_images/city_map.jpg' and let the model/controller handle full URL generation.
            // The MapController's store method saves the full URL: `$data['image_path'] = Storage::url($path);`
            // So this seeder should also save the full URL for consistency.
            // BUT, Storage::url() is for *generating* URLs. We are *defining* the path.
            // The convention is to store paths relative to the disk's root.
            // If 'public' disk is used, 'map_images/...' is correct. The accessor should then use Storage::url().

            $imagePathInPublicDisk = 'map_images/' . $imageName;


            Map::create([
                'city_id' => $city->id,
                'name' => 'Map of ' . $city->name,
                'image_path' => $imagePathInPublicDisk, // Path relative to 'storage/app/public' or the 'public' disk root
                'description' => 'Default map for the city of ' . $city->name,
                // Assuming 'width' and 'height' (natural dimensions of the image) are nullable or have defaults.
                // Ideally, these would be known or extracted from the actual image if possible during seeding.
            ]);
        }
        $this->command->info(count($cities) . ' maps created, one for each city.');
    }
}
