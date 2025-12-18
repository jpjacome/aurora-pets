<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\Client;
use App\Jobs\SendCampaignEmailJob;

class SendCampaignEmailJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_an_email_and_updates_status()
    {
        Mail::fake();

        $campaign = EmailCampaign::create(['name' => 'Test', 'subject' => 'Hi', 'template_body' => '<p>Hi {{name}}</p>']);

        $client = Client::create(['client' => 'Smith', 'email' => 'smith@example.com']);

        $message = EmailMessage::create([
            'campaign_id' => $campaign->id,
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        $job = new SendCampaignEmailJob($message);
        $job->handle();

        // Mail was sent
        Mail::assertSent(\App\Mail\GenericCampaignMailable::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });

        $this->assertEquals('delivered', $message->fresh()->status);
        $this->assertNotNull($message->fresh()->delivered_at);
    }
}
