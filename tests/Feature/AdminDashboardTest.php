<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Client;
use App\Models\Test as PlantTest;
use App\Models\Pet;
use Carbon\Carbon;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_counts()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Create clients
        Client::create(['client' => 'Alice', 'email' => 'a@example.com']);
        Client::create(['client' => 'Bob', 'email' => 'b@example.com']);

        // Create tests
        PlantTest::create(['client' => 'Alice', 'email' => 'a@example.com']);
        PlantTest::create(['client' => 'Bob', 'email' => 'b@example.com']);
        PlantTest::create(['client' => 'Bob', 'email' => 'b@example.com']);

        // Create pets (one active, one deceased)
        $client = Client::first();
        Pet::create(['client_id' => $client->id, 'name' => 'Fluffy', 'deceased' => false]);
        Pet::create(['client_id' => $client->id, 'name' => 'Oldie', 'deceased' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);

        // Assert total clients and tests are shown
        $this->assertStringContainsString((string) Client::count(), $response->getContent());
        $this->assertStringContainsString((string) PlantTest::count(), $response->getContent());

        // Assert pets counts
        $this->assertStringContainsString((string) Pet::where('deceased', false)->count(), $response->getContent());
        $this->assertStringContainsString((string) Pet::where('deceased', true)->count(), $response->getContent());
    }

    public function test_days_dropdown_affects_new_counts()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // one client created today
        Client::create(['client' => 'Recent', 'email' => 'r@example.com']);

        // one client created 10 days ago
        $old = Client::create(['client' => 'Old', 'email' => 'o@example.com']);
        $old->created_at = Carbon::now()->subDays(10);
        $old->save();

        // Default (7 days) should show 1 new client
        $resp7 = $this->actingAs($admin)->get('/admin');
        $resp7->assertStatus(200);
        $this->assertStringContainsString('New (last 7 days)', $resp7->getContent());
        $this->assertStringContainsString('1', $resp7->getContent());

        // 14 days should show 2 new clients
        $resp14 = $this->actingAs($admin)->get('/admin?days=14');
        $resp14->assertStatus(200);
        $this->assertStringContainsString('New (last 14 days)', $resp14->getContent());
        $this->assertStringContainsString('2', $resp14->getContent());
    }
}
