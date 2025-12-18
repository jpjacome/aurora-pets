<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class EmailCampaignPreviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_preview_campaign_template()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->postJson(route('admin.email-campaigns.preview'), [
                'subject' => 'Hello',
                'template_body' => '<p>Hello {{name}}</p>'
            ])->assertOk()->assertJsonStructure(['html']);
    }
}
