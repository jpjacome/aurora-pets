<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Plant;

class AddMissingPlantasApplySeeder extends Seeder
{
    /**
     * Seeder to create the non-canonical plants from `plantas.txt` using the curator-assigned Crowley numbers.
     * Default: APPLY mode (DB writes). Set env `ADD_PLANTAS_DRY_RUN=true` if you want to preview instead.
     * IMPORTANT: Back up the database before running this on production.
     * To overwrite existing plant data set `ADD_PLANTAS_OVERWRITE=true`.
     */
    public function run(): void
    {
        $dryRun = filter_var(env('ADD_PLANTAS_DRY_RUN', 'false'), FILTER_VALIDATE_BOOLEAN); // default = apply
        $overwrite = filter_var(env('ADD_PLANTAS_OVERWRITE', 'false'), FILTER_VALIDATE_BOOLEAN);

        $file = storage_path('app/chatbot-knowledge/plantas.txt');
        $mappingPath = storage_path('app/chatbot-knowledge/plantscan-crowley-mapping.json');

        if (!is_file($file)) {
            $this->command->error("Plantas file not found: $file");
            return;
        }
        if (!is_file($mappingPath)) {
            $this->command->error("Mapping JSON not found: $mappingPath");
            return;
        }

        $mapping = json_decode(file_get_contents($mappingPath), true) ?: [];
        $mapping = array_change_key_case($mapping, CASE_LOWER);

        $toAdd = [
            'aguacate',
            'anturio blanco',
            'bambu',
            'cedron',
            'higuero',
            'monstera adasonii',
            'tocte - nogal ecuatoriano',
        ];

        $contents = file_get_contents($file);
        $parts = preg_split('/^#\s+/m', $contents);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $lines = preg_split("/\r?\n/", $part);
            $nameLine = array_shift($lines);
            $name = trim($nameLine);
            if ($name === '' ) continue;

            $lookup = $this->normalizeName($name);
            if (!in_array($lookup, $toAdd, true)) continue; // only process target list

            $section = implode("\n", $lines);
            $scientific = $this->matchFirst($section, '/Especie:\s*(.+)/i');
            $lighting = $this->matchFirst($section, '/Iluminaci[oó]n:\s*(.+)/i');
            $watering = $this->matchFirst($section, '/Riego:\s*(.+)/i');
            $substrate = $this->matchFirst($section, '/Tipo de sustrato\s*:?:\s*(.+)/i');
            $type = $this->matchFirst($section, '/Tipo de planta\s*:?:\s*(.+)/i');
            $description = trim($section);

            $plantNumber = $mapping[$lookup]['plant_number'] ?? null;

            $slug = Str::slug($name);
            $plant = Plant::where('slug', $slug)->orWhere('name', 'like', "%{$name}%")->first();

            $data = [
                'name' => $name,
                'slug' => $slug,
                'scientific_name' => $scientific ?: null,
                'lighting_info' => $lighting ?: null,
                'watering_info' => $watering ?: null,
                'substrate_info' => $substrate ?: null,
                'description' => $description ?: null,
                'plant_type' => $type ?: null,
                'plant_number' => $plantNumber ?: null,
                'is_active' => false,
            ];

            if ($plant) {
                $needsUpdate = $overwrite;
                foreach (['scientific_name','lighting_info','watering_info','substrate_info','description','plant_type','plant_number'] as $k) {
                    if (empty($plant->{$k}) && !empty($data[$k])) {
                        $needsUpdate = true;
                        break;
                    }
                }

                if ($needsUpdate) {
                    $this->command->info("Will update existing plant [{$plant->id}] {$plant->name}");
                    if (!$dryRun) {
                        if (!$overwrite && isset($data['plant_number']) && $plant->plant_number && $plant->plant_number != $data['plant_number']) {
                            unset($data['plant_number']);
                            $this->command->warn("Not overwriting plant_number for [{$plant->id}] {$plant->name}; set ADD_PLANTAS_OVERWRITE=true to force");
                        }
                        $plant->update(array_filter($data, function($v){ return $v !== null; }));
                        $updated++;
                    }
                } else {
                    $this->command->line("No changes for plant [{$plant->id}] {$plant->name}");
                    $skipped++;
                }
            } else {
                // If the mapped plant_number is already used, do NOT attempt to insert duplicate
                if (!empty($data['plant_number'])) {
                    $existingNumber = Plant::where('plant_number', $data['plant_number'])->first();
                    if ($existingNumber) {
                        $this->command->warn("Plant number {$data['plant_number']} is already used by [{$existingNumber->id}] {$existingNumber->name}. Creating {$name} WITHOUT plant_number to avoid unique constraint violation.");
                        unset($data['plant_number']);
                    }
                }

                $this->command->info("Will create plant: {$name} (plant_number=".($plantNumber?:'null').")");
                if (!$dryRun) {
                    $p = Plant::create($data);
                    $created++;
                    $this->command->info("Created plant [{$p->id}] {$p->name}");
                }
            }
        }

        $this->command->info("Summary: created={$created}, updated={$updated}, skipped={$skipped}");
        if ($dryRun) $this->command->warn('DRY RUN mode: no database writes were performed. Set ADD_PLANTAS_DRY_RUN=false to apply.');
    }

    private function matchFirst(string $content, string $regex): ?string
    {
        if (preg_match($regex, $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function normalizeName(string $str): string
    {
        $s = preg_replace('/\s+/', ' ', trim($str));
        $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        return mb_strtolower(preg_replace('/[^a-z0-9 \-]/i', '', $s));
    }
}
