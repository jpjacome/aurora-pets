<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\Client;
use App\Models\User;

class ResendSelectedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_resend_selected_messages()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $campaign = EmailCampaign::create(['name' => 'Test', 'template_body' => 'Hello']);
        $c1 = Client::create(['client' => 'R1', 'email' => 'r1@example.com']);
        $c2 = Client::create(['client' => 'R2', 'email' => 'r2@example.com']);

        $m1 = EmailMessage::create(['campaign_id' => $campaign->id, 'client_id' => $c1->id, 'email' => $c1->email]);
        $m2 = EmailMessage::create(['campaign_id' => $campaign->id, 'client_id' => $c2->id, 'email' => $c2->email]);

        $this->actingAs($admin)
            ->post(route('admin.email-campaigns.resend', $campaign), ['message_ids' => [$m1->id, $m2->id]])
            ->assertRedirect();
    }
}
