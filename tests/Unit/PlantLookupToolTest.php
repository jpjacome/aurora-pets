<?php

namespace Tests\Unit;

use App\Mcp\Servers\ChatbotServer;
use App\Mcp\Tools\PlantLookupTool;
use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class PlantLookupToolTest extends TestCase
{
    public function test_tool_finds_existing_plant()
    {
        $mockResult = [
            'id' => 42,
            'name' => 'TestPlant',
            'scientific_name' => 'Planta testus',
            'summary' => 'A test plant used for unit tests',
            'care' => [
                'watering' => 'Moderate',
                'lighting' => 'Indirect light',
                'substrate' => 'General potting mix',
            ],
            'confidence' => 0.92,
            'source' => 'plants table',
            'last_reviewed_at' => '2025-01-01',
            'default_image' => null,
        ];

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('findBestMatch')->with('TestPlant', 1)->andReturn($mockResult);

        $this->instance(PlantKnowledgeService::class, $mock);

        $tool = new PlantLookupTool();
        $req = new \Laravel\Mcp\Request(['query' => 'TestPlant', 'max_results' => 1]);
        $response = $tool->handle($req, $mock);

        $this->assertEquals(200, $response->status);
        $this->assertStringContainsString('TestPlant', $response->getContent());
    }

    public function test_tool_returns_error_when_not_found()
    {
        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('findBestMatch')->with('NoSuchPlant', 1)->andReturn(null);

        $this->instance(PlantKnowledgeService::class, $mock);

        $tool = new PlantLookupTool();
        $req = new \Laravel\Mcp\Request(['query' => 'NoSuchPlant', 'max_results' => 1]);
        $response = $tool->handle($req, $mock);

        $this->assertEquals(400, $response->status);
        $this->assertStringContainsString('No plant found', $response->getContent());
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
