<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Plant;
use App\Services\GroqAIService;

class PlantRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommend_with_constraints_returns_db_plants()
    {
        Plant::create([
            'name' => 'Zamioculca',
            'scientific_name' => 'Zamioculca zamiifolia',
            'description' => 'Resistente, follaje verde',
            'lighting_info' => 'poca luz',
            'is_active' => true,
        ]);

        Plant::create([
            'name' => 'Sansevieria',
            'scientific_name' => 'Sansevieria trifasciata',
            'description' => 'Vertical y resistente',
            'lighting_info' => 'poca luz',
            'is_active' => true,
        ]);

        $svc = new GroqAIService();
        $out = $svc->generateResponse('busco plantas sin flor, muy verdes y que requieran poca luz', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('Zamioculca', $out['response']);
        $this->assertStringContainsString('Sansevieria', $out['response']);
        $this->assertStringContainsString('Source: plants table', $out['response']);
        $this->assertStringNotContainsString('Aspidistra', $out['response']);
    }

    public function test_recommend_with_constraints_no_db_matches()
    {
        // No plants seeded
        $svc = new GroqAIService();
        $out = $svc->generateResponse('busco plantas sin flor, muy verdes y que requieran poca luz', []);

        $this->assertTrue($out['success']);
        $this->assertStringContainsString('No tengo sugerencias verificadas en nuestro catálogo', $out['response']);
    }
}
