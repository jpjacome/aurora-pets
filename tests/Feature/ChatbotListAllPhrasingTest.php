<?php

namespace Tests\Feature;

use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class ChatbotListAllPhrasingTest extends TestCase
{
    public function test_list_request_phrasing_triggers_list_all()
    {
        config(['services.gemini.api_key' => 'test-key']);

        $mockCandidates = [
            [ 'id' => 1, 'name' => 'Pensamientos', 'summary' => 'Historically used in love potions...', 'care' => [], 'confidence' => 0.8, 'source' => 'plants table' ],
            [ 'id' => 2, 'name' => 'San Pedro', 'summary' => 'A cactus historically used in rituals...', 'care' => [], 'confidence' => 0.9, 'source' => 'plants table' ],
        ];

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(\Mockery::on(function($criteria){
            return isset($criteria['location']) && $criteria['location'] === 'indoor' && isset($criteria['list_all']) && $criteria['list_all'] === true;
        }), \Mockery::type('int'))->andReturn($mockCandidates);

        $this->instance(PlantKnowledgeService::class, $mock);

        $resp = $this->postJson(route('chatbot.public.send'), [
            'message' => 'aurora me puedes dar la lista de plantas de interior?',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $resp->assertStatus(200);
        $this->assertEquals('plant_recommend', $resp->json('insights.intent'));
        $this->assertTrue(!empty($resp->json('insights.list_all')));

        $body = $resp->json('response');
        // should contain names
        $this->assertStringContainsString('Pensamientos', $body);
        $this->assertStringContainsString('San Pedro', $body);
        // should NOT contain long summaries or 'Source: plants table'
        $this->assertStringNotContainsString('Historically used', $body);
        $this->assertStringNotContainsString('Source: plants table', $body);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
