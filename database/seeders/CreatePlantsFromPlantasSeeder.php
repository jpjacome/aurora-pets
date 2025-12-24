<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Plant;

class CreatePlantsFromPlantasSeeder extends Seeder
{
    /**
     * Create plants from `storage/app/chatbot-knowledge/plantas.txt` using Crowley mapping.
     * Default: DRY RUN (no DB writes). Set env `PLANTAS_SEEDER_DRY_RUN=false` to apply.
     * To force creating missing plants set `PLANTAS_SEEDER_CREATE_MISSING=true`.
     * To overwrite existing plant data set `PLANTAS_SEEDER_OVERWRITE=true`.
     */
    public function run(): void
    {
        $dryRun = filter_var(env('PLANTAS_SEEDER_DRY_RUN', 'true'), FILTER_VALIDATE_BOOLEAN);
        $createMissing = filter_var(env('PLANTAS_SEEDER_CREATE_MISSING', 'true'), FILTER_VALIDATE_BOOLEAN);
        $overwrite = filter_var(env('PLANTAS_SEEDER_OVERWRITE', 'false'), FILTER_VALIDATE_BOOLEAN);

        $file = storage_path('app/chatbot-knowledge/plantas.txt');
        if (!is_file($file)) {
            $this->command->error("Plantas file not found: $file");
            return;
        }

        $mappingPath = storage_path('app/chatbot-knowledge/plantscan-crowley-mapping.json');
        $mapping = [];
        if (is_file($mappingPath)) {
            $mapping = json_decode(file_get_contents($mappingPath), true) ?: [];
            // normalize keys
            $mapping = array_change_key_case($mapping, CASE_LOWER);
            $this->command->info('Loaded Crowley mapping entries: '.count($mapping));
        } else {
            $this->command->warn('No Crowley mapping JSON found: ' . $mappingPath);
        }

        $contents = file_get_contents($file);
        // Split sections by H1 (# )
        $parts = preg_split('/^#\s+/m', $contents);
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;

            // First line is the plant title
            $lines = preg_split('/\r?\n/', $part);
            $nameLine = array_shift($lines);
            $name = trim($nameLine);
            // Skip top-level headers and non-plant sections (handle the file header which contains 'consolidated'/'export')
            if ($name === '' || strtolower($name) === 'plantas' || strtolower($name) === 'plantas formulario' || strtolower($name) === 'correspondencias referenciales plantas' || stripos($name, 'consolidated') !== false || stripos($name, 'export') !== false) {
                continue;
            }

            $section = implode("\n", $lines);

            $scientific = $this->matchFirst($section, '/Especie:\s*(.+)/i');
            $difficulty = $this->matchFirst($section, '/Dificultad:\s*(.+)/i');
            $lighting = $this->matchFirst($section, '/Iluminaci[oó]n:\s*(.+)/i');
            $watering = $this->matchFirst($section, '/Riego:\s*(.+)/i');
            $type = $this->matchFirst($section, '/Tipo de planta\s*:?\s*(.+)/i');
            $substrate = $this->matchFirst($section, '/Tipo de sustrato\s*:?\s*(.+)/i');

            $description = trim($section);

            $lookup = $this->normalizeName($name);
            $plantNumber = $this->findMappingPlantNumber($mapping, $lookup);

            // Find existing plant by slug or name substring
            $slug = Str::slug($name);
            $plant = Plant::where('slug', $slug)->orWhere('name', 'like', "%{$name}%")->first();

            $data = [
                'name' => $name,
                'slug' => $slug,
                'scientific_name' => $scientific ?: null,
                'difficulty' => $difficulty ?: null,
                'lighting_info' => $lighting ?: null,
                'watering_info' => $watering ?: null,
                'substrate_info' => $substrate ?: null,
                'description' => $description ?: null,
                'plant_type' => $type ?: null,
                'is_active' => false,
            ];
            if ($plantNumber) $data['plant_number'] = $plantNumber;

            if ($plant) {
                // Update existing plant only if overwrite is enabled or plant has missing fields
                $needsUpdate = $overwrite;
                foreach (['scientific_name','difficulty','lighting_info','watering_info','substrate_info','description','plant_type','plant_number'] as $k) {
                    if (empty($plant->{$k}) && !empty($data[$k])) {
                        $needsUpdate = true;
                        break;
                    }
                    if ($k === 'plant_number' && isset($data[$k]) && $data[$k] && $plant->{$k} && $plant->{$k} != $data[$k]) {
                        // we'll update plant_number if different
                        $needsUpdate = true;
                        break;
                    }
                }

                if ($needsUpdate) {
                    $this->command->info("Will update existing plant [{$plant->id}] {$plant->name}");
                    // Respect overwrite flag for plant_number changes
                    $overwritePlantNumbers = filter_var(env('PLANTAS_SEEDER_OVERWRITE_PLANT_NUMBERS', 'false'), FILTER_VALIDATE_BOOLEAN);
                    if (!$dryRun) {
                        if (!$overwritePlantNumbers && isset($data['plant_number']) && $plant->plant_number && $plant->plant_number != $data['plant_number']) {
                            // don't overwrite plant number
                            unset($data['plant_number']);
                            $this->command->warn("Not overwriting plant_number for [{$plant->id}] {$plant->name}; set PLANTAS_SEEDER_OVERWRITE_PLANT_NUMBERS=true to force");
                        }

                        $plant->update(array_filter($data, function($v){ return $v !== null; }));
                        $updated++;
                    }
                } else {
                    $this->command->line("No changes for plant [{$plant->id}] {$plant->name}");
                    $skipped++;
                }
            } else {
                if ($createMissing) {
                    $this->command->info("Will create plant: {$name} (plant_number=".($plantNumber?:'null').")");
                    if (!$dryRun) {
                        $p = Plant::create($data);
                        $created++;
                        $this->command->info("Created plant [{$p->id}] {$p->name}");
                    }
                } else {
                    $this->command->warn("Skipping creation for: {$name} (creation disabled)");
                    $skipped++;
                }
            }
        }

        $this->command->info("Summary: created={$created}, updated={$updated}, skipped={$skipped}");
        if ($dryRun) {
            $this->command->warn('DRY RUN mode: no database writes were performed. Set PLANTAS_SEEDER_DRY_RUN=false to apply.');
        }
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

    /**
     * Try to find a mapping entry for normalized name. Exact match first, then substring, then fuzzy levenshtein.
     */
    private function findMappingPlantNumber(array $mapping, string $lookup): ?int
    {
        if (isset($mapping[$lookup]['plant_number'])) {
            return $mapping[$lookup]['plant_number'];
        }

        // substring match
        foreach ($mapping as $k => $v) {
            if ($k !== $lookup && (strpos($k, $lookup) !== false || strpos($lookup, $k) !== false)) {
                return $v['plant_number'] ?? null;
            }
        }

        // fuzzy levenshtein match (tolerance 2)
        $best = null;
        $bestScore = PHP_INT_MAX;
        foreach ($mapping as $k => $v) {
            $dist = levenshtein($k, $lookup);
            if ($dist < $bestScore && $dist <= 2) {
                $bestScore = $dist;
                $best = $v;
            }
        }
        return $best['plant_number'] ?? null;
    }
}
