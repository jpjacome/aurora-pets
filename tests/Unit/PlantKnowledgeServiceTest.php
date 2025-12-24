<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Plant;
use App\Services\PlantKnowledgeService;

class PlantKnowledgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_best_match_exact_name()
    {
        $p = Plant::create([
            'name' => 'Test Plant',
            'scientific_name' => 'Plantae testus',
            'description' => 'A lovely test plant.',
            'watering_info' => 'Water weekly',
            'lighting_info' => 'Bright indirect',
            'substrate_info' => 'Well draining soil',
            'is_active' => true,
        ]);

        $svc = new PlantKnowledgeService();
        $res = $svc->findBestMatch('Test Plant');

        $this->assertNotNull($res);
        $this->assertEquals($p->id, $res['id']);
        $this->assertEquals('Test Plant', $res['name']);
        $this->assertGreaterThanOrEqual(0.75, $res['confidence']);
    }

    public function test_find_best_match_fuzzy()
    {
        $p = Plant::create([
            'name' => 'Fuzzy Plant',
            'scientific_name' => 'Fuzzus plantus',
            'description' => 'Used for fuzzy tests.',
            'watering_info' => 'Sparingly',
            'lighting_info' => 'Low light',
            'is_active' => true,
        ]);

        $svc = new PlantKnowledgeService();
        $res = $svc->findBestMatch('fuzzy');

        $this->assertNotNull($res);
        $this->assertEquals($p->id, $res['id']);
    }

    public function test_returns_null_when_not_found()
    {
        $svc = new PlantKnowledgeService();
        $res = $svc->findBestMatch('something nonexistent');
        $this->assertNull($res);
    }
}
