<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Plant;

class ChatbotPlantLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_endpoint_returns_plant()
    {
        $p = Plant::create([
            'name' => 'Lookup Plant',
            'scientific_name' => 'Lookupus plantus',
            'description' => 'Lookup description',
            'watering_info' => 'Moderate',
            'lighting_info' => 'Bright indirect',
            'is_active' => true,
        ]);

        $resp = $this->getJson('/admin/chatbot/plant-lookup?q=Lookup Plant');
        $resp->assertStatus(200)->assertJsonStructure(['result' => ['id','name','confidence','care']]);
        $this->assertEquals($p->id, $resp->json('result.id'));
    }

    public function test_lookup_endpoint_returns_null_for_missing()
    {
        $resp = $this->getJson('/admin/chatbot/plant-lookup?q=No Such Plant');
        $resp->assertStatus(200)->assertJson(['result' => null]);
    }
}
