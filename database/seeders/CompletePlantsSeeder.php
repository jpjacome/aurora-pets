<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plant;

class CompletePlantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populates plants table with data from Notion database
     */
    public function run(): void
    {
        $this->command->info('Updating/creating plants from Notion database...');
        
        $plants = [
            // PLANTAS DE INTERIOR
            [
                'name' => 'Anturio blanco',
                'family' => 'Araceae',
                'species' => 'Anthurium andraeanum',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'substrate_info' => 'Muy aireado. Prefiere un ambiente donde el aire circule libremente alrededor de sus hojas y raíces. Se recomienda colocar la planta en un espacio abierto o utilizar un sustrato ligero que permita una buena ventilación para evitar problemas de humedad y promover un crecimiento saludable.',
                'lighting_info' => 'Luz indirecta brillante. Prefiere una iluminación intensa pero filtrada, como la que se encuentra cerca de una ventana con cortinas transparentes o en un espacio donde la luz del sol no golpea directamente las hojas. Esta luz es ideal para fomentar un crecimiento vigoroso y mantener el color vibrante de las hojas, evitando a la vez el riesgo de quemaduras.',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Bambú',
                'family' => 'Asparagaceae',
                'species' => 'Dracaena sanderiana (bambú de la suerte)',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Tierra suelta y bien drenada si se cultiva en maceta. Puede cultivarse también en agua.',
                'lighting_info' => 'Luz indirecta moderada. Se adapta bien a ubicaciones con luz filtrada o en habitaciones bien iluminadas, pero lejos de la exposición directa al sol.',
                'watering_info' => 'Moderado - Mantener el sustrato húmedo pero no encharcado',
                'is_active' => true,
            ],
            [
                'name' => 'Cala',
                'family' => 'Araceae',
                'species' => 'Zantedeschia spp.',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'substrate_info' => 'Sustrato rico en materia orgánica, bien drenado',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Abundante - Riego 3-5 veces a la semana durante la floración',
                'is_active' => true,
            ],
            [
                'name' => 'Calathea Triostar',
                'family' => 'Marantaceae',
                'species' => 'Stromanthe sanguinea "Triostar"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Alta',
                'substrate_info' => 'Suelo suelto, con turba, perlita y fibra de coco',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado a abundante - Mantener el sustrato siempre húmedo',
                'is_active' => true,
            ],
            [
                'name' => 'Jade',
                'family' => 'Crassulaceae',
                'species' => 'Crassula ovata',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato para suculentas: arenoso y con muy buen drenaje',
                'lighting_info' => 'Luz solar directa o muy brillante',
                'watering_info' => 'Escaso - Riego cada 15 días o 1 vez por semana',
                'is_active' => true,
            ],
            [
                'name' => 'Monstera Adansonii',
                'family' => 'Araceae',
                'species' => 'Monstera adansonii',
                'plant_type' => 'Foliar',
                'difficulty' => 'Media',
                'substrate_info' => 'Sustrato suelto, con perlita, turba y corteza',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Monstera Deliciosa',
                'family' => 'Araceae',
                'species' => 'Monstera deliciosa',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato suelto, con buen drenaje, mezcla de turba, perlita y compost',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Sanseviera',
                'family' => 'Asparagaceae',
                'species' => 'Sansevieria trifasciata',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato para cactus o suculentas',
                'lighting_info' => 'Luz baja a media, tolera poca luz',
                'watering_info' => 'Escaso - Riego cada 15 días',
                'is_active' => true,
            ],
            [
                'name' => 'Schefflera',
                'family' => 'Araliaceae',
                'species' => 'Schefflera arboricola',
                'plant_type' => 'Foliar',
                'difficulty' => 'Media',
                'substrate_info' => 'Sustrato universal con buen drenaje',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Syngonium Confetti',
                'family' => 'Araceae',
                'species' => 'Syngonium podophyllum "Confetti"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato suelto con turba, fibra de coco y perlita',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Syngonium Neon Pink',
                'family' => 'Araceae',
                'species' => 'Syngonium podophyllum "Neon Pink"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato aireado y con buen drenaje',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Syngonium Three Kings',
                'family' => 'Araceae',
                'species' => 'Syngonium podophyllum "Three Kings"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato con buena aireación, mezcla con perlita y turba',
                'lighting_info' => 'Luz brillante indirecta',
                'watering_info' => 'Moderado - Riego 2-3 veces a la semana',
                'is_active' => true,
            ],
            [
                'name' => 'Zamioculca',
                'family' => 'Araceae',
                'species' => 'Zamioculcas zamiifolia',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'substrate_info' => 'Sustrato para suculentas o con muy buen drenaje',
                'lighting_info' => 'Luz baja a brillante indirecta, muy adaptable',
                'watering_info' => 'Escaso - Riego cada 15-20 días',
                'is_active' => true,
            ],
        ];

        foreach ($plants as $index => $plantData) {
            $plantData['slug'] = \Illuminate\Support\Str::slug($plantData['name']);
            
            // Check if plant exists by name
            $existingPlant = Plant::where('name', $plantData['name'])->first();
            
            if ($existingPlant) {
                // Update existing plant, keep its plant_number
                $existingPlant->update($plantData);
                $this->command->info("Updated: {$plantData['name']}");
            } else {
                // Create new plant with next available plant_number
                $maxPlantNumber = Plant::max('plant_number') ?? 0;
                $plantData['plant_number'] = $maxPlantNumber + 1;
                Plant::create($plantData);
                $this->command->info("Created: {$plantData['name']}");
            }
        }

        $this->command->info('✅ ' . count($plants) . ' plants processed successfully from Notion database!');
    }
}
