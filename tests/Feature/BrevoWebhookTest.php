<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\Client;

class BrevoWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_email_message_on_brevo_webhook()
    {
        $campaign = EmailCampaign::create(['name' => 'Campaign', 'template_body' => '<p>Y</p>']);
        $client = Client::create(['client' => 'C1', 'email' => 'c1@example.com']);
        $message = EmailMessage::create(['campaign_id' => $campaign->id, 'client_id' => $client->id, 'email' => $client->email, 'provider_id' => 'msg-123']);

        $payload = ['messageId' => 'msg-123', 'event' => 'delivered'];

        $this->postJson(route('webhooks.receive', ['provider' => 'brevo']), $payload)->assertOk();

        $this->assertEquals('delivered', $message->fresh()->status);
        $this->assertNotNull($message->fresh()->delivered_at);
    }
}
