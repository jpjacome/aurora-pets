<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Plant;

class ApplyCrowleyPlantSeeder extends Seeder
{
    /**
     * Apply Crowley mapping to plants: set plant_number for existing plants and optionally create missing ones.
     * By default this seeder runs in DRY RUN mode. Set env PLANTS_CROWLEY_SEEDER_DRY_RUN=false to apply.
     * To create missing plants, set PLANTS_CROWLEY_SEEDER_CREATE_MISSING=true
     */
    public function run(): void
    {
        $dryRun = filter_var(env('PLANTS_CROWLEY_SEEDER_DRY_RUN', 'true'), FILTER_VALIDATE_BOOLEAN);
        $createMissing = filter_var(env('PLANTS_CROWLEY_SEEDER_CREATE_MISSING', 'false'), FILTER_VALIDATE_BOOLEAN);

        $mappingPath = storage_path('app/chatbot-knowledge/plantscan-crowley-mapping.json');
        if (!is_file($mappingPath)) {
            $this->command->error("Mapping file not found: $mappingPath");
            return;
        }

        $mapping = json_decode(file_get_contents($mappingPath), true) ?: [];
        $this->command->info('Loaded mapping entries: ' . count($mapping));

        // Whether to overwrite existing plant_number values
        $overwritePlantNumbers = filter_var(env('PLANTS_CROWLEY_SEEDER_OVERWRITE', 'false'), FILTER_VALIDATE_BOOLEAN);

        $updated = 0;
        $created = 0;
        $skipped = 0;

        foreach ($mapping as $nameKey => $info) {
            $originalName = $nameKey;
            $targetPlantNumber = $info['plant_number'] ?? null;
            if (!$targetPlantNumber) {
                $this->command->warn("Skipping $nameKey: no plant_number defined in mapping");
                $skipped++;
                continue;
            }

            // Try to locate existing plant by slug or name
            $slug = Str::slug($originalName);
            $plant = Plant::where('slug', $slug)
                ->orWhere('name', 'like', "%{$originalName}%")
                ->first();

            if ($plant) {
                // Only set or overwrite plant_number if it is not set or overwrite flag is true
                if (empty($plant->plant_number) || $overwritePlantNumbers) {
                    if ((int)$plant->plant_number !== (int)$targetPlantNumber) {
                        $this->command->info("Will set plant_number {$targetPlantNumber} for existing plant [{$plant->id}] {$plant->name}");
                        if (!$dryRun) {
                            $plant->update(['plant_number' => $targetPlantNumber]);
                            $updated++;
                            $this->command->info("Updated plant [{$plant->id}] with plant_number={$targetPlantNumber}");
                        }
                    } else {
                        $this->command->line("No change needed for plant [{$plant->id}] {$plant->name}");
                    }
                } else {
                    $this->command->warn("Skipping plant_number change for [{$plant->id}] {$plant->name} because it already has a number (use PLANTS_CROWLEY_SEEDER_OVERWRITE=true to force)");
                }
            } else {
                $this->command->warn("No plant found for mapping name: '$originalName'");
                if ($createMissing) {
                    $data = [
                        'name' => ucwords($originalName),
                        'slug' => $slug,
                        'plant_number' => $targetPlantNumber,
                        'is_active' => false,
                    ];
                    $this->command->info("Will create plant: {$data['name']} with plant_number={$targetPlantNumber}");
                    if (!$dryRun) {
                        $plant = Plant::create($data);
                        $created++;
                        $this->command->info("Created plant [{$plant->id}] {$plant->name}");
                    }
                }
            }
        }

        $this->command->info("Summary: updated={$updated}, created={$created}, skipped={$skipped}");

        if ($dryRun) {
            $this->command->warn('DRY RUN mode: no database writes were performed. Set PLANTS_CROWLEY_SEEDER_DRY_RUN=false to apply.');
        } else {
            $this->command->info('Seeder applied.');
        }
    }
}
