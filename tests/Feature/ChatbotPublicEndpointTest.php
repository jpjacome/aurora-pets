<?php

namespace Tests\Feature;

use App\Services\GroqAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotPublicEndpointTest extends TestCase
{
    // Use RefreshDatabase only if needed; keeping DB untouched for now

    public function test_returns_500_when_ai_service_throws()
    {
        // Do not mock GroqAIService: if it throws on missing API key, controller should return 500
        $response = $this->postJson(route('chatbot.public.send'), [
            'message' => 'hola',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $response->assertStatus(500);
        $this->assertStringContainsString('Failed to process message', $response->json('error'));
    }

    public function test_successful_flow_with_mocked_ai_service()
    {
        $mock = \Mockery::mock(GroqAIService::class);
        $mock->shouldReceive('generateResponse')->andReturn([
            'response' => 'Hola, soy Aurora, te puedo ayudar.',
            'insights' => ['intent' => 'general', 'confidence' => 0.9],
            'success' => true,
        ]);

        $this->instance(GroqAIService::class, $mock);

        $response = $this->postJson(route('chatbot.public.send'), [
            'message' => 'hola',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('response', 'Hola, soy Aurora, te puedo ayudar.');
        $this->assertEquals('general', $response->json('insights.intent'));
    }
}
