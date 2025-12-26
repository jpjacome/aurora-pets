<?php

namespace Tests\Feature;

use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class ChatbotListAllTest extends TestCase
{
    public function test_list_all_returns_names_only()
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
            'message' => 'quiero saber cuales son todas las plantas de interior del catalogo',
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
        // should NOT contain long summaries
        $this->assertStringNotContainsString('Historically used in love potions', $body);
        $this->assertStringNotContainsString('rituals', $body);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
