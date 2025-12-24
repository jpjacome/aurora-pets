<?php

namespace App\Services;

use App\Models\Plant;
use Illuminate\Support\Facades\Cache;

class PlantKnowledgeService
{
    public function findBestMatch(string $query, int $limit = 3): ?array
    {
        $cacheKey = 'plant_lookup:'.md5($query);

        return Cache::remember($cacheKey, 60 * 60, function () use ($query, $limit) {
            $q = trim($query);

            // Try exact name/slug
            $plant = Plant::where('name', $q)
                ->orWhere('slug', $q)
                ->orWhere('scientific_name', $q)
                ->first();

            if ($plant) {
                return $this->formatResult($plant);
            }

            // Fuzzy search across name, scientific_name, slug and description
            $slug = \Illuminate\Support\Str::slug($q);

            $candidates = Plant::where(function ($sub) use ($q, $slug) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('scientific_name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$slug}%");
            })->where('is_active', true)
            ->limit($limit)
            ->get();

            if ($candidates->isEmpty()) {
                return null;
            }

            // Heuristic: choose candidate with most fields filled
            $best = $candidates->sortByDesc(function (Plant $p) {
                $score = 0;
                if ($p->description) $score += 2;
                if ($p->watering_info) $score += 1;
                if ($p->lighting_info) $score += 1;
                if ($p->substrate_info) $score += 1;
                return $score;
            })->first();

            return $this->formatResult($best);
        });
    }

    protected function formatResult(Plant $p): array
    {
        $confidence = $this->estimateConfidence($p);

        return [
            'id' => $p->id,
            'name' => $p->name,
            'scientific_name' => $p->scientific_name,
            'summary' => $p->description,
            'care' => [
                'watering' => $p->watering_info,
                'lighting' => $p->lighting_info,
                'substrate' => $p->substrate_info,
                'care_level' => $p->care_level ?? null,
            ],
            'images' => $p->photos ?? [],
            'default_image' => $p->default_photo ?? null,
            'last_reviewed_at' => $p->updated_at ? $p->updated_at->toDateString() : null,
            'source' => 'plants table',
            'confidence' => $confidence,
        ];
    }

    protected function estimateConfidence(Plant $p): float
    {
        $score = 0.4; // base
        if ($p->description) $score += 0.3;
        if ($p->watering_info) $score += 0.15;
        if ($p->lighting_info) $score += 0.1;
        if ($p->photos) $score += 0.05;

        return min(1.0, round($score, 2));
    }

    /**
     * Recommend plants from DB matching the given criteria. Criteria keys: 'light' (low|medium|high), 'no_flowers' (bool), 'indoor' (bool), 'keywords' (array)
     * Returns array of formatted plant results (limit default 3)
     */
    public function recommendPlants(array $criteria = [], int $limit = 3): array
    {
        $query = Plant::query()->where('is_active', true);

        // Light preferences (try to match keywords in lighting_info)
        if (!empty($criteria['light'])) {
            $light = strtolower($criteria['light']);
            if (in_array($light, ['low','baja','poca'])) {
                $query->where(function($q) {
                    $q->where('lighting_info', 'like', '%baja%')
                      ->orWhere('lighting_info', 'like', '%poca%')
                      ->orWhere('lighting_info', 'like', '%low%')
                      ->orWhere('lighting_info', 'like', '%indirect%');
                });
            } elseif (in_array($light, ['medium','media','media/alta'])) {
                $query->where(function($q) {
                    $q->where('lighting_info', 'like', '%media%')
                      ->orWhere('lighting_info', 'like', '%indirect%');
                });
            } elseif (in_array($light, ['high','alta','directa'])) {
                $query->where(function($q) {
                    $q->where('lighting_info', 'like', '%sol%')
                      ->orWhere('lighting_info', 'like', '%direct%')
                      ->orWhere('lighting_info', 'like', '%directa%');
                });
            }
        }

        // Exclude flowering plants if requested (use description or plant_type heuristics)
        if (!empty($criteria['no_flowers'])) {
            $query->where(function($q) {
                $q->whereNull('plant_type')
                  ->orWhere('plant_type', 'not like', '%flower%')
                  ->orWhere('description', 'not like', '%flor%')
                  ->orWhere('description', 'not like', '%flower%');
            });
        }

        // Keywords (e.g., 'green', 'succulent')
        if (!empty($criteria['keywords']) && is_array($criteria['keywords'])) {
            foreach ($criteria['keywords'] as $kw) {
                $k = strtolower($kw);
                $query->where(function($q) use ($k) {
                    $q->where('name', 'like', "%{$k}%")
                      ->orWhere('description', 'like', "%{$k}%")
                      ->orWhere('plant_type', 'like', "%{$k}%");
                });
            }
        }

        $results = $query->limit($limit)->get();

        return $results->map(function(Plant $p) {
            return $this->formatResult($p);
        })->toArray();
    }
}
