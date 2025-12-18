<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use App\Models\User;
use App\Models\Client;
use App\Models\EmailCampaign;
use App\Jobs\QueueCampaignJob;

class EmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_campaign_and_run_it()
    {
        Bus::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Make some clients
        Client::create(['client' => 'Alice', 'email' => 'alice@example.com']);
        Client::create(['client' => 'Bob', 'email' => 'bob@example.com']);
        Client::create(['client' => 'Charles', 'email' => 'charles@example.com']);

        $this->actingAs($admin)->post(route('admin.email-campaigns.store'), [
            'name' => 'Test campaign',
            'subject' => 'Hello',
            'template_body' => '<p>Hello {{name}}</p>'
        ])->assertRedirect();

        $campaign = EmailCampaign::first();

        $this->actingAs($admin)->post(route('admin.email-campaigns.run', $campaign))->assertRedirect();

        // assert that messages were created
        $this->assertDatabaseCount('email_messages', 3);

        // assert QueueCampaignJob dispatched
        Bus::assertDispatched(QueueCampaignJob::class, function ($job) use ($campaign) {
            return $job->campaign->id === $campaign->id;
        });
    }
}
