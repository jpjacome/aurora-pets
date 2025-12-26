<?php

namespace Tests\Feature;

use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class ChatbotWaterListBehaviorTest extends TestCase
{
    public function test_plants_that_need_mucha_agua_returns_names_only()
    {
        config(['services.gemini.api_key' => 'test-key']);

        $mockCandidates = [
            [ 'id' => 1, 'name' => 'Buganvilla', 'summary' => 'Needs lots of water', 'care' => [], 'confidence' => 0.85, 'source' => 'plants table' ],
            [ 'id' => 2, 'name' => 'Cala', 'summary' => 'Likes abundant moisture', 'care' => [], 'confidence' => 0.8, 'source' => 'plants table' ],
            [ 'id' => 3, 'name' => 'Helecho nativo', 'summary' => 'Native fern', 'care' => [], 'confidence' => 0.9, 'source' => 'plants table' ],
        ];

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(\Mockery::on(function($criteria){
            return isset($criteria['water']) && $criteria['water'] === 'high';
        }), \Mockery::type('int'))->andReturn($mockCandidates);

        $this->instance(PlantKnowledgeService::class, $mock);

        $resp = $this->postJson(route('chatbot.public.send'), [
            'message' => 'dame la lista de plantas que necesitan mucha agua',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $resp->assertStatus(200);
        $this->assertEquals('plant_recommend', $resp->json('insights.intent'));
        $this->assertTrue(!empty($resp->json('insights.brief_list')) || !empty($resp->json('insights.list_all')));

        $body = $resp->json('response');
        $this->assertStringContainsString('Buganvilla', $body);
        $this->assertStringNotContainsString('Needs lots of water', $body);
        $this->assertStringNotContainsString('Source: plants table', $body);
    }

    public function test_small_recommendations_include_summaries()
    {
        config(['services.gemini.api_key' => 'test-key']);

        $mockCandidates = [
            [ 'id' => 1, 'name' => 'Monstera Deliciosa', 'summary' => 'Tolerates medium light, easy care', 'care' => [], 'confidence' => 0.9, 'source' => 'plants table' ],
            [ 'id' => 2, 'name' => 'Syngonium Three Kings', 'summary' => 'Good for interiors', 'care' => [], 'confidence' => 0.85, 'source' => 'plants table' ],
        ];

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(\Mockery::on(function($criteria){
            return isset($criteria['location']) && $criteria['location'] === 'indoor';
        }), \Mockery::type('int'))->andReturn($mockCandidates);

        $this->instance(PlantKnowledgeService::class, $mock);

        $resp = $this->postJson(route('chatbot.public.send'), [
            'message' => 'recomiendame plantas para interior con poca luz',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $resp->assertStatus(200);
        $this->assertEquals('plant_recommend', $resp->json('insights.intent'));
        $body = $resp->json('response');
        $this->assertStringContainsString('Monstera Deliciosa', $body);
        $this->assertStringContainsString('Tolerates medium light', $body);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
