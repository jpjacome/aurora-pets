<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;

class UpdatePlantsDetailsSeeder extends Seeder
{
    /**
     * Update the 19 PlantScan plants with detailed care information.
     * This seeder is SAFE to run multiple times - it only updates, never creates.
     * 
     * Run with: php artisan db:seed --class=UpdatePlantsDetailsSeeder
     */
    public function run(): void
    {
        $plantsDetails = [
            // Plant #1 - Pensamientos (Viola tricolor)
            [
                'plant_number' => 1,
                'scientific_name' => 'Viola tricolor',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'location_type' => 'outdoor',
                'origin' => 'Europa y Asia',
                'substrate_info' => 'Sustrato bien drenado con materia orgánica. Ideal una mezcla de tierra negra, fibra de coco y un poco de perlita para mantener humedad sin encharcar.',
                'lighting_info' => 'Prefiere luz solar directa en climas templados, pero también prospera en semisombra luminosa.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Regar cuando el sustrato comience a secarse. Evitar que se seque por completo.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #3 - San Pedro
            [
                'plant_number' => 3,
                'scientific_name' => 'Echinopsis pachanoi',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'outdoor',
                'origin' => 'Andes del Perú y Ecuador',
                'substrate_info' => 'Sustrato arenoso, poroso y muy bien drenado.',
                'lighting_info' => 'Sol directo ideal. También tolera semisombra.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Regar solo cuando esté totalmente seco.',
                'water_requirement' => 'Escaso',
            ],
            
            // Plant #4 - Limonero
            [
                'plant_number' => 4,
                'scientific_name' => 'Citrus limon',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'location_type' => 'outdoor',
                'origin' => 'Asia',
                'substrate_info' => 'Fértil, suelto, con buen drenaje. Ideal tierra negra con compost.',
                'lighting_info' => 'Pleno sol por al menos 6 horas diarias.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Regular, más frecuente en floración y fructificación.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #5 - Schefflera
            [
                'plant_number' => 5,
                'scientific_name' => 'Schefflera arboricola',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'Taiwán',
                'substrate_info' => 'Sustrato universal con buen drenaje. Añadir perlita y compost.',
                'lighting_info' => 'Tolera luz indirecta alta o semisombra luminosa.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Dejar secar parte del sustrato entre riegos.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #6 - Monstera Deliciosa
            [
                'plant_number' => 6,
                'scientific_name' => 'Monstera deliciosa',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'América tropical',
                'substrate_info' => 'Sustrato aireado: fibra de coco, corteza de pino, humus y perlita.',
                'lighting_info' => 'Luz indirecta intermedia. Evitar sol directo prolongado.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Dejar secar los primeros centímetros entre riegos.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #7 - Buganvilla
            [
                'plant_number' => 7,
                'scientific_name' => 'Bougainvillea glabra',
                'plant_type' => 'Con flor',
                'difficulty' => 'Fácil',
                'location_type' => 'outdoor',
                'origin' => 'Sudamérica tropical',
                'substrate_info' => 'Sustrato arenoso o universal con excelente drenaje. Tolera suelos pobres.',
                'lighting_info' => 'Pleno sol es ideal. Necesita varias horas de luz directa.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Riego escaso. Permitir secado completo del sustrato.',
                'water_requirement' => 'Escaso',
            ],
            
            // Plant #9 - Zamioculca
            [
                'plant_number' => 9,
                'scientific_name' => 'Zamioculcas zamiifolia',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'África oriental',
                'substrate_info' => 'Sustrato suelto con arena, perlita y algo de humus.',
                'lighting_info' => 'Ideal para interiores con poca luz. Tolera sombra.',
                'light_requirement' => 'Semisombra',
                'watering_info' => 'Regar cada 2-3 semanas. Tolera sequía.',
                'water_requirement' => 'Escaso',
            ],
            
            // Plant #10 - Syngonium Neon Pink
            [
                'plant_number' => 10,
                'scientific_name' => 'Syngonium podophyllum "Neon Pink"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'América Central',
                'substrate_info' => 'Sustrato suelto con buena aireación. Ideal con turba, perlita y fibra de coco.',
                'lighting_info' => 'Prefiere luz indirecta brillante. Evitar sol directo.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Regar cuando el sustrato esté seco en los primeros 2 cm.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #12 - Sanseviera
            [
                'plant_number' => 12,
                'scientific_name' => 'Sansevieria trifasciata',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'África occidental',
                'substrate_info' => 'Sustrato arenoso o para cactus. Excelente drenaje.',
                'lighting_info' => 'Tolera poca luz pero prefiere luz indirecta moderada.',
                'light_requirement' => 'Semisombra',
                'watering_info' => 'Regar cada 2-3 semanas, permitiendo que seque por completo.',
                'water_requirement' => 'Escaso',
            ],
            
            // Plant #13 - Cala
            [
                'plant_number' => 13,
                'scientific_name' => 'Zantedeschia aethiopica',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'location_type' => 'both',
                'origin' => 'África del Sur',
                'substrate_info' => 'Sustrato húmedo, fértil y con buen drenaje. Ideal una base de compost, fibra de coco y tierra vegetal.',
                'lighting_info' => 'Requiere luz brillante indirecta o algunas horas de sol directo. Muy adaptable.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Mantener el sustrato constantemente húmedo pero sin encharcar.',
                'water_requirement' => 'Abundante',
            ],
            
            // Plant #14 - Syngonium Three Kings
            [
                'plant_number' => 14,
                'scientific_name' => 'Syngonium podophyllum "Three Kings"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'América Central',
                'substrate_info' => 'Turba, fibra de coco y perlita. Aireado y rico en materia orgánica.',
                'lighting_info' => 'Luz difusa y constante para mantener su variegación.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Evitar encharcamientos. Mantener ligeramente húmedo.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #15 - Anturio
            [
                'plant_number' => 15,
                'scientific_name' => 'Anthurium andraeanum',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'location_type' => 'indoor',
                'origin' => 'Colombia y Ecuador',
                'substrate_info' => 'Sustrato aireado con materia orgánica: fibra de coco, corteza de pino y perlita.',
                'lighting_info' => 'Luz filtrada o indirecta brillante. Evitar sol directo.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Regar cuando el sustrato esté parcialmente seco. Evitar exceso de humedad.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #17 - Calathea Triostar
            [
                'plant_number' => 17,
                'scientific_name' => 'Stromanthe sanguinea "Triostar"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Media',
                'location_type' => 'indoor',
                'origin' => 'Brasil',
                'substrate_info' => 'Sustrato aireado con fibra de coco, turba y perlita para mantener humedad y buen drenaje.',
                'lighting_info' => 'Prefiere luz indirecta brillante, no tolera sol directo.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Mantener el sustrato ligeramente húmedo sin encharcar.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #18 - Monstera Adansonii
            [
                'plant_number' => 18,
                'scientific_name' => 'Monstera adansonii',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'Centro y Sudamérica',
                'substrate_info' => 'Fibra de coco, perlita, tierra vegetal y corteza.',
                'lighting_info' => 'Ideal en interiores luminosos con luz indirecta.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Humedad constante sin exceso.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #20 - Helecho nativo
            [
                'plant_number' => 20,
                'scientific_name' => 'var. Polypodiaceae',
                'plant_type' => 'Foliar',
                'difficulty' => 'Media',
                'location_type' => 'both',
                'origin' => 'Regiones andinas',
                'substrate_info' => 'Fibra de coco, turba y compost en proporciones iguales. Mantener húmedo.',
                'lighting_info' => 'Ideal en sombra o luz filtrada. No tolera luz directa.',
                'light_requirement' => 'Semisombra',
                'watering_info' => 'Mantener humedad constante. No dejar secar.',
                'water_requirement' => 'Abundante',
            ],
            
            // Plant #21 - Capulí
            [
                'plant_number' => 21,
                'scientific_name' => 'Prunus serotina subsp. capuli',
                'plant_type' => 'Con flor',
                'difficulty' => 'Media',
                'location_type' => 'outdoor',
                'origin' => 'Andes',
                'substrate_info' => 'Profundo, fértil, bien drenado. Con buen contenido de materia orgánica.',
                'lighting_info' => 'Necesita pleno sol para dar buen fruto.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Constante en los primeros años. Luego puede tolerar sequía.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #22 - Jade
            [
                'plant_number' => 22,
                'scientific_name' => 'Crassula ovata',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'both',
                'origin' => 'Sudáfrica',
                'substrate_info' => 'Sustrato para cactus con piedra volcánica o arena gruesa.',
                'lighting_info' => 'Sol directo parcial o pleno sol.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Regar solo cuando esté completamente seco.',
                'water_requirement' => 'Escaso',
            ],
            
            // Plant #23 - Syngonium Confettii
            [
                'plant_number' => 23,
                'scientific_name' => 'Syngonium podophyllum "Confetti"',
                'plant_type' => 'Foliar',
                'difficulty' => 'Fácil',
                'location_type' => 'indoor',
                'origin' => 'América Central',
                'substrate_info' => 'Sustrato aireado con fibra de coco, turba y perlita.',
                'lighting_info' => 'Luz brillante sin sol directo para potenciar sus motas rosadas.',
                'light_requirement' => 'Indirecta',
                'watering_info' => 'Humedad constante sin saturación.',
                'water_requirement' => 'Moderado',
            ],
            
            // Plant #27 - Cholán
            [
                'plant_number' => 27,
                'scientific_name' => 'Tecoma stans',
                'plant_type' => 'Con flor',
                'difficulty' => 'Fácil',
                'location_type' => 'outdoor',
                'origin' => 'América tropical',
                'substrate_info' => 'Bien drenado, con compost o mantillo. Tolera suelos pobres.',
                'lighting_info' => 'Requiere sol directo para florecer profusamente.',
                'light_requirement' => 'Directa',
                'watering_info' => 'Riego regular, dejando secar capa superficial.',
                'water_requirement' => 'Moderado',
            ],
        ];
        
        $updated = 0;
        $notFound = 0;
        
        foreach ($plantsDetails as $details) {
            $plant = Plant::where('plant_number', $details['plant_number'])->first();
            
            if ($plant) {
                // Only update fields that are not already set, or update all
                // Remove plant_number from update data
                $updateData = $details;
                unset($updateData['plant_number']);
                
                $plant->update($updateData);
                $updated++;
                $this->command->info("✅ Updated Plant #{$details['plant_number']}: {$plant->name}");
            } else {
                $notFound++;
                $this->command->warn("⚠️  Plant #{$details['plant_number']} not found in database");
            }
        }
        
        $this->command->info("\n=================================");
        $this->command->info("✅ Updated: {$updated} plants");
        if ($notFound > 0) {
            $this->command->warn("⚠️  Not found: {$notFound} plants");
            $this->command->warn("Run PlantsSeeder first: php artisan db:seed --class=PlantsSeeder");
        }
        $this->command->info("=================================");
    }
}
