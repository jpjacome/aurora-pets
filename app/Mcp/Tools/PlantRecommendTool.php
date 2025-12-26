<?php

namespace App\Mcp\Tools;

use App\Services\PlantKnowledgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly(true)]
class PlantRecommendTool extends Tool
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'criteria' => $schema->object()->description('Recommendation criteria: location, light, no_flowers, keywords, list_all'),
            'max_results' => $schema->integer()->default(3)->description('Maximum number of candidates to return'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'candidates' => $schema->array()->items([
                'id' => $schema->number()->required(),
                'name' => $schema->string()->required(),
                'summary' => $schema->string(),
                'care' => $schema->object(),
                'confidence' => $schema->number(),
                'source' => $schema->string(),
            ]),
            'reason' => $schema->string()->description('Optional explanation for why these candidates were chosen'),
        ];
    }

    public function handle(Request $request, PlantKnowledgeService $plants): Response
    {
        $criteria = $request->get('criteria', []);
        $max = $request->int('max_results') ?? 3;

        // If client asked for list_all, increase cap (safe upper bound)
        if (!empty($criteria['list_all'])) {
            $max = max($max, 100);
        }

        $candidates = $plants->recommendPlants($criteria, $max);

        if (empty($candidates)) {
            return Response::error('No recommendations found for the given criteria.');
        }

        // Optionally build a short reason from criteria
        $reasonParts = [];
        if (!empty($criteria['light'])) $reasonParts[] = 'light: ' . $criteria['light'];
        if (!empty($criteria['no_flowers'])) $reasonParts[] = 'no flowers';
        if (!empty($criteria['keywords'])) $reasonParts[] = 'keywords: ' . implode(',', $criteria['keywords']);
        $reason = implode('; ', $reasonParts);

        return Response::structured(['candidates' => $candidates, 'reason' => $reason]);
    }
}
