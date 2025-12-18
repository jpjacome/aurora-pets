<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailMessage;
use App\Models\EmailCampaign;
use App\Models\Client;

class EmailTrackingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function open_tracking_sets_opened_at()
    {
        $campaign = EmailCampaign::create(['name' => 'Test', 'template_body' => '<p>Hi</p>']);

        $client = Client::create(['client' => 'Tester', 'email' => 'tester@example.com']);

        $message = EmailMessage::create([
            'campaign_id' => $campaign->id,
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        $this->get(route('email.track.open', $message->message_uuid))->assertStatus(200)->assertHeader('Content-Type', 'image/gif');

        $this->assertNotNull($message->fresh()->opened_at);
    }
}
