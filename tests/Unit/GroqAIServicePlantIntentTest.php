<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Plant;
use App\Services\GroqAIService;

class GroqAIServicePlantIntentTest extends TestCase
{
    use RefreshDatabase;

    public function test_spanish_about_phrase_triggers_db_lookup()
    {
        Plant::create([
            'name' => 'Capuli',
            'scientific_name' => 'Prunus serotina subsp. capuli',
            'description' => 'Capuli description',
            'watering_info' => 'Moderate',
            'lighting_info' => 'Full sun',
            'is_active' => true,
        ]);

        $svc = new GroqAIService();
        $out = $svc->generateResponse('sobre el capuli', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('Capuli', $out['response']);
        $this->assertStringContainsString('Source: plants table', $out['response']);
    }

    public function test_single_word_name_triggers_db_lookup()
    {
        Plant::create([
            'name' => 'Anturio',
            'scientific_name' => 'Anthurium andraeanum',
            'description' => 'Anturio info',
            'watering_info' => 'Keep soil moist',
            'lighting_info' => 'Bright indirect',
            'is_active' => true,
        ]);

        $svc = new GroqAIService();
        $out = $svc->generateResponse('anturio', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('Anturio', $out['response']);
    }

    public function test_single_word_fallback_when_no_db_entry()
    {
        $svc = new GroqAIService();
        $out = $svc->generateResponse('capuli', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('No tengo información verificada', $out['response']);
    }

    public function test_greeting_does_not_trigger_plant_lookup()
    {
        // With a plant that could match 'hola' via substring (Cholán), ensure greeting is not treated as plant intent
        Plant::create([
            'name' => 'Cholán',
            'scientific_name' => 'Tecoma stans',
            'description' => 'Cholan description',
            'watering_info' => 'Medio',
            'lighting_info' => 'Sol directo',
            'is_active' => true,
        ]);

        $svc = new GroqAIService();
        $out = $svc->generateResponse('hola', []);

        $this->assertTrue($out['success']);
        $this->assertStringNotContainsString('Source: plants table', $out['response']);
        $this->assertStringNotContainsString('¿Quieres que te dé información', $out['response']);
    }

    public function test_confirm_flow_single_word()
    {
        Plant::create([
            'name' => 'Capuli',
            'scientific_name' => 'Prunus serotina subsp. capuli',
            'description' => 'Capuli description',
            'watering_info' => 'Moderate',
            'lighting_info' => 'Full sun',
            'is_active' => true,
        ]);

        $svc = new GroqAIService();
        $out = $svc->generateResponse('capuli', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('¿Quieres que te dé información', $out['response']);

        // Simulate user saying 'sí' after assistant confirmation
        $conv = [
            ['role' => 'assistant', 'content' => $out['response']],
            ['role' => 'user', 'content' => 'sí']
        ];

        $out2 = $svc->generateResponse('sí', $conv);
        $this->assertTrue($out2['success']);
        $this->assertStringContainsString('Capuli', $out2['response']);
        $this->assertStringContainsString('Source: plants table', $out2['response']);
    }

    public function test_confirm_denied_does_not_fetch()
    {
        Plant::create([
            'name' => 'Capuli',
            'scientific_name' => 'Prunus serotina subsp. capuli',
            'description' => 'Capuli description',
            'watering_info' => 'Moderate',
            'lighting_info' => 'Full sun',
            'is_active' => true,
        ]);

        $svc = new GroqAIService();
        $out = $svc->generateResponse('capuli', []);
        $this->assertStringContainsString('¿Quieres que te dé información', $out['response']);

        $conv = [
            ['role' => 'assistant', 'content' => $out['response']],
            ['role' => 'user', 'content' => 'no']
        ];

        $out2 = $svc->generateResponse('no', $conv);
        $this->assertTrue($out2['success']);
        $this->assertStringNotContainsString('Source: plants table', $out2['response']);
    }

    public function test_recommend_broad_query_asks_clarifying()
    {
        $svc = new GroqAIService();
        $out = $svc->generateResponse('quiero saber sobre una de las plantas', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('interior o exterior', $out['response']);

        // New cases that previously returned fallback
        $out2 = $svc->generateResponse('tienes informacion sobre las plantas que me pueden ofrecer?', []);
        $this->assertTrue($out2['success']);
        $this->assertStringContainsString('interior o exterior', $out2['response']);

        $out3 = $svc->generateResponse('tienes informacion sobre plantas del catalogo?', []);
        $this->assertTrue($out3['success']);
        $this->assertStringContainsString('interior o exterior', $out3['response']);
    }
}

