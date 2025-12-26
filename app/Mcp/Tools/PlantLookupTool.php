<?php

namespace App\Mcp\Tools;

use App\Services\PlantKnowledgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly(true)]
class PlantLookupTool extends Tool
{
    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Plant name or search query to look up')
                ->required(),
            'max_results' => $schema->integer()
                ->description('Maximum number of results to return')
                ->default(1),
        ];
    }

    /**
     * The tool's output schema (structured response)
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'candidates' => $schema->array()->items([
                'id' => $schema->number()->required(),
                'name' => $schema->string()->required(),
                'scientific_name' => $schema->string(),
                'summary' => $schema->string(),
                'care' => $schema->object(),
                'confidence' => $schema->number(),
                'source' => $schema->string(),
                'last_reviewed_at' => $schema->string(),
                'default_image' => $schema->string()->nullable(),
            ]),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, PlantKnowledgeService $plants): Response
    {
        $query = $request->string('query');
        $max = $request->int('max_results') ?? 1;

        // Use existing PlantKnowledgeService to return the best match
        $result = $plants->findBestMatch($query, $max);

        if (empty($result)) {
            return Response::error('No plant found for the given query.');
        }

        // Normalize into candidates array (findBestMatch returns single best match)
        $candidates = [$result];

        return Response::structured(['candidates' => $candidates]);
    }
}
