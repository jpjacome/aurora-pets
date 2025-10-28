<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;
use Illuminate\Support\Str;

class PlantsSeeder extends Seeder
{
    /**
     * Seed the plants table with 19 PlantScan plants.
     */
    public function run(): void
    {
        $plants = [
            ['num' => 1,  'name' => 'Pensamientos (Viola tricolor)', 'family' => 'Violaceae', 'image' => 'pensamiento.png'],
            ['num' => 3,  'name' => 'San Pedro', 'family' => 'Cactaceae', 'image' => null],
            ['num' => 4,  'name' => 'Limonero', 'family' => 'Rutaceae', 'image' => 'citrus-lemon.png'],
            ['num' => 5,  'name' => 'Schefflera', 'family' => 'Araliaceae', 'image' => null],
            ['num' => 6,  'name' => 'Monstera Deliciosa', 'family' => 'Araceae', 'image' => null],
            ['num' => 7,  'name' => 'Buganvilla', 'family' => 'Nyctaginaceae', 'image' => null],
            ['num' => 9,  'name' => 'Zamioculca', 'family' => 'Araceae', 'image' => null],
            ['num' => 10, 'name' => 'Syngonium Neon Pink', 'family' => 'Araceae', 'image' => null],
            ['num' => 12, 'name' => 'Sanseviera', 'family' => 'Asparagaceae', 'image' => null],
            ['num' => 13, 'name' => 'Cala', 'family' => 'Araceae', 'image' => null],
            ['num' => 14, 'name' => 'Syngonium Three Kings', 'family' => 'Araceae', 'image' => null],
            ['num' => 15, 'name' => 'Anturio', 'family' => 'Araceae', 'image' => null],
            ['num' => 17, 'name' => 'Calathea Triostar', 'family' => 'Marantaceae', 'image' => null],
            ['num' => 18, 'name' => 'Monstera Adansonii', 'family' => 'Araceae', 'image' => 'monstera-adasonii.png'],
            ['num' => 20, 'name' => 'Helecho nativo', 'family' => 'Polypodiaceae', 'image' => null],
            ['num' => 21, 'name' => 'Capulí', 'family' => 'Rosaceae', 'image' => null],
            ['num' => 22, 'name' => 'Jade', 'family' => 'Crassulaceae', 'image' => null],
            ['num' => 23, 'name' => 'Syngonium Confettii', 'family' => 'Araceae', 'image' => 'syngonium-confetti .png'],
            ['num' => 27, 'name' => 'Cholán', 'family' => 'Tecoma', 'image' => null],
        ];
        
        foreach ($plants as $plantData) {
            $slug = Str::slug($plantData['name']);
            
            // Use custom image filename if provided, otherwise use slug
            $imageFilename = $plantData['image'] ?? ($slug . '.png');
            
            Plant::updateOrCreate(
                ['plant_number' => $plantData['num']],
                [
                    'name' => $plantData['name'],
                    'slug' => $slug,
                    'plant_number' => $plantData['num'], // Explicitly set the plant number
                    'family' => $plantData['family'],
                    'default_photo' => '/assets/plantscan/imgs/plants/' . $imageFilename,
                    'is_active' => true,
                    // Care info to be added manually or via separate seeder later
                ]
            );
        }
        
        $this->command->info('✅ Seeded 19 PlantScan plants successfully!');
    }
}
