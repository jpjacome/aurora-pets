<?php

namespace Tests\Feature;

use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class ChatbotRecommendFlowTest extends TestCase
{
    public function test_clarifying_question_then_location_reply_returns_recommendations()
    {
        // Ensure Groq/Gemini API key exists so GroqAIService constructor doesn't throw
        config(['services.gemini.api_key' => 'test-key']);

        // Step 1: user asks generic recommend question -> assistant should ask clarifying
        $resp = $this->postJson(route('chatbot.public.send'), [
            'message' => 'tienes informacion sobre las plantas que ofrecen?',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $resp->assertStatus(200);
        $this->assertEquals('plant_recommend_ask', $resp->json('insights.intent'));
        $clarify = $resp->json('response');
        $this->assertStringContainsString('¿Buscas una planta para interior o exterior', $clarify);

        // Step 2: user replies 'interior' - mock PlantKnowledgeService to return recommendations
        $mockCandidates = [
            ['id' => 1, 'name' => 'Syngonium Three Kings', 'summary' => 'Great for interior', 'care' => [], 'confidence' => 0.9, 'source' => 'plants table']
        ];

        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(\Mockery::on(function($criteria){
            return isset($criteria['location']) && $criteria['location'] === 'indoor';
        }), 5)->andReturn($mockCandidates);

        $this->instance(PlantKnowledgeService::class, $mock);

        $resp2 = $this->postJson(route('chatbot.public.send'), [
            'message' => 'interior',
            'conversation_history' => [
                ['role' => 'user', 'content' => 'tienes informacion sobre las plantas que ofrecen?'],
                ['role' => 'assistant', 'content' => $clarify]
            ],
            'provider' => 'gemini'
        ]);

        $resp2->assertStatus(200);
        $this->assertEquals('plant_recommend', $resp2->json('insights.intent'));
        $this->assertStringContainsString('Syngonium Three Kings', $resp2->json('response'));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
