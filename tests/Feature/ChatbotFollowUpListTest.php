<?php

namespace Tests\Feature;

use App\Services\PlantKnowledgeService;
use Tests\TestCase;

class ChatbotFollowUpListTest extends TestCase
{
    public function test_followup_y_las_de_exterior_returns_outdoor_list()
    {
        config(['services.gemini.api_key' => 'test-key']);

        // First, simulate asking for interior list and the assistant returning it
        $mockIndoor = [[ 'id'=>1,'name'=>'Pensamientos','summary'=>'...','care'=>[], 'confidence'=>0.8,'source'=>'plants table' ]];
        $mock = \Mockery::mock(PlantKnowledgeService::class);
        $mock->shouldReceive('recommendPlants')->with(\Mockery::on(function($criteria){
            return isset($criteria['location']) && $criteria['location'] === 'indoor' && isset($criteria['list_all']) && $criteria['list_all'] === true;
        }), \Mockery::type('int'))->andReturn($mockIndoor);
        $this->instance(PlantKnowledgeService::class, $mock);

        $resp = $this->postJson(route('chatbot.public.send'), [
            'message' => 'aurora me puedes dar la lista de plantas de interior?',
            'conversation_history' => [],
            'provider' => 'gemini'
        ]);

        $resp->assertStatus(200);
        $this->assertTrue(!empty($resp->json('insights.list_all')));

        // Now user asks: 'y las de exterior?'
        // Re-mock recommendPlants to expect outdoor criteria
        $mock2 = \Mockery::mock(PlantKnowledgeService::class);
        $mock2->shouldReceive('recommendPlants')->with(\Mockery::on(function($criteria){
            return isset($criteria['location']) && $criteria['location'] === 'outdoor' && isset($criteria['list_all']) && $criteria['list_all'] === true;
        }), \Mockery::type('int'))->andReturn([[ 'id'=>2,'name'=>'Buganvilla','summary'=>'...','care'=>[], 'confidence'=>0.8,'source'=>'plants table' ]]);
        $this->instance(PlantKnowledgeService::class, $mock2);

        $resp2 = $this->postJson(route('chatbot.public.send'), [
            'message' => 'y las de exterior?',
            'conversation_history' => [
                ['role'=>'user','content'=>'aurora me puedes dar la lista de plantas de interior?'],
                ['role'=>'assistant','content'=>$resp->json('response')]
            ],
            'provider' => 'gemini'
        ]);

        $resp2->assertStatus(200);
        $this->assertTrue(!empty($resp2->json('insights.list_all')));
        $this->assertStringContainsString('Buganvilla', $resp2->json('response'));
        $this->assertStringNotContainsString('Source: plants table', $resp2->json('response'));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
