<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\Client;

class UnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unsubscribe_sets_unsubscribed_at()
    {
        $campaign = EmailCampaign::create(['name' => 'Test', 'template_body' => 'Hello']);

        $client = Client::create(['client' => 'Unsub', 'email' => 'unsub@example.com']);

        $message = EmailMessage::create([
            'campaign_id' => $campaign->id,
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        $this->get(route('unsubscribe', ['client' => $client->id, 'uuid' => $message->message_uuid]))->assertStatus(200);

        $this->assertNotNull($client->fresh()->unsubscribed_at);
    }
}
