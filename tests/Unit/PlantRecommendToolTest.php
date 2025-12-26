<?php

namespace Tests\Unit;

use App\Mcp\Tools\PlantRecommendTool;
use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class PlantRecommendToolTest extends TestCase
{
    public function test_recommendation_returns_candidates()
    {
        $mockCandidates = [
            [
                'id' => 1,
                'name' => 'Fern',
                'summary' => 'Great for low light',
                'care' => ['watering' => 'Medium'],
                'confidence' => 0.85,
                'source' => 'plants table'
            ],
        ];

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(['light' => 'low'], 3)->andReturn($mockCandidates);
        $this->instance(PlantKnowledgeService::class, $mock);

        $tool = new PlantRecommendTool();
        $req = new \Laravel\Mcp\Request(['criteria' => ['light' => 'low'], 'max_results' => 3]);
        $response = $tool->handle($req, $mock);

        $this->assertEquals(200, $response->status);
        $this->assertStringContainsString('Fern', $response->getContent());
    }

    public function test_returns_error_when_no_candidates()
    {
        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(['light' => 'low'], 3)->andReturn([]);
        $this->instance(PlantKnowledgeService::class, $mock);

        $tool = new PlantRecommendTool();
        $req = new \Laravel\Mcp\Request(['criteria' => ['light' => 'low'], 'max_results' => 3]);
        $response = $tool->handle($req, $mock);

        $this->assertEquals(400, $response->status);
        $this->assertStringContainsString('No recommendations found', $response->getContent());
    }

    public function test_list_all_returns_multiple_candidates_and_respects_list_flag()
    {
        $mockCandidates = [];
        for ($i = 1; $i <= 20; $i++) {
            $mockCandidates[] = ['id' => $i, 'name' => 'Plant ' . $i, 'summary' => 'Summary', 'care' => [], 'confidence' => 0.8, 'source' => 'plants table'];
        }

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        // Expect at least a larger max (100) when list_all is requested
        $mock->shouldReceive('recommendPlants')->with(['location' => 'indoor', 'list_all' => true], \Mockery::type('int'))->andReturn($mockCandidates);
        $this->instance(PlantKnowledgeService::class, $mock);

        $tool = new PlantRecommendTool();
        $req = new \Laravel\Mcp\Request(['criteria' => ['location' => 'indoor', 'list_all' => true], 'max_results' => 100]);
        $response = $tool->handle($req, $mock);

        $this->assertEquals(200, $response->status);
        $this->assertStringContainsString('Plant 1', $response->getContent());
        $this->assertStringContainsString('Plant 20', $response->getContent());
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
