<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Plant;

class UpdatePlantsFromNotionSeeder extends Seeder
{
    /**
     * Run the seeder.
     * Default behavior: APPLY changes (DB writes). Set env PLANTS_SEEDER_DRY_RUN=true to force a dry-run.
     * To auto-create missing plants set PLANTS_SEEDER_CREATE_MISSING=true in env.
     * WARNING: Take a DB backup before running on production.
     */
    public function run(): void
    {
        $dryRun = filter_var(env('PLANTS_SEEDER_DRY_RUN', 'false'), FILTER_VALIDATE_BOOLEAN);
        $createMissing = filter_var(env('PLANTS_SEEDER_CREATE_MISSING', 'false'), FILTER_VALIDATE_BOOLEAN);

        $this->command->info('Seeder mode: ' . ($dryRun ? 'DRY RUN (no writes)' : 'APPLY (will write to DB)'));

        $dir = storage_path('app/chatbot-knowledge/plantas-notion');
        if (!is_dir($dir)) {
            $this->command->error("Notion export directory not found: $dir");
            return;
        }

        $files = array_filter(scandir($dir), function ($n) use ($dir) {
            $p = $dir . DIRECTORY_SEPARATOR . $n;
            return is_file($p) && preg_match('/\.(md|txt|csv)$/i', $n);
        });

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $content = file_get_contents($path);
            if ($content === false) {
                $this->command->error("Failed to read: $path");
                continue;
            }

            // Determine canonical name: prefer first H1 in content, otherwise filename (without extension)
            $name = null;
            if (preg_match('/^#\s*(.+)$/m', $content, $m)) {
                $name = trim($m[1]);
            }
            if (!$name) {
                $name = pathinfo($file, PATHINFO_FILENAME);
            }

            $slug = Str::slug($name);

            // Basic field extraction (best-effort)
            $species = $this->matchFirst($content, '/Especie:\s*(.+)/i');
            $difficulty = $this->matchFirst($content, '/Dificultad:\s*(.+)/i');
            $lighting = $this->matchFirst($content, '/Iluminaci[oó]n:\s*(.+)/i');
            $watering = $this->matchFirst($content, '/Riego:\s*(.+)/i');
            $substrate = $this->matchFirst($content, '/Tipo de sustrato:\s*(.+)/i');

            $description = trim($content);

            // Find existing plant by slug or by name partial match
            $plant = Plant::where('slug', $slug)
                ->orWhere('name', 'like', "%{$name}%")
                ->first();

            $updates = array_filter([
                'scientific_name' => $species,
                'difficulty' => $difficulty,
                'lighting_info' => $lighting,
                'watering_info' => $watering,
                'substrate_info' => $substrate,
                'description' => $description,
            ], function ($v) { return $v !== null; });

            if ($plant) {
                if ($dryRun) {
                    $this->command->info("DRY RUN: would update plant [{$plant->id}] {$plant->name} (from file: $file)");
                    foreach ($updates as $k => $v) {
                        $old = $plant->{$k} ?? 'NULL';
                        $this->command->line(" - $k: [OLD] " . substr((string)$old, 0, 200) . " -> [NEW] " . substr((string)$v, 0, 200));
                    }
                } else {
                    DB::transaction(function () use ($plant, $updates, $file) {
                        $plant->update($updates);
                        $this->command->info("Updated plant [{$plant->id}] {$plant->name} from file: $file");
                    });
                }
            } else {
                $this->command->warn("No matching plant found for '$name' (file: $file)");
                if ($createMissing) {
                    $data = array_merge(['name' => $name, 'slug' => $slug, 'is_active' => false], $updates);
                    if ($dryRun) {
                        $this->command->info("DRY RUN: would create plant: $name");
                    } else {
                        DB::transaction(function () use ($data, $name) {
                            $plant = Plant::create($data);
                            $this->command->info("Created plant [{$plant->id}] {$name}");
                        });
                    }
                }
            }
        }

        $this->command->info('Done scanning Notion export files.');
    }

    private function matchFirst(string $content, string $regex): ?string
    {
        if (preg_match($regex, $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
