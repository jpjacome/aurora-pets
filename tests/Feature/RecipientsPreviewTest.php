<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Client;

class RecipientsPreviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_preview_recipients_count()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Client::create(['client' => 'A', 'email' => 'a@example.com']);
        Client::create(['client' => 'B', 'email' => 'b@example.com']);

        $this->actingAs($admin)
            ->postJson(route('admin.email-campaigns.recipients.preview'), ['filter' => 'all'])
            ->assertOk()
            ->assertJson(['count' => 2]);
    }
}
