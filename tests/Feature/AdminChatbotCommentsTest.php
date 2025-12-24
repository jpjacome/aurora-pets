<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ChatbotAdminComment;

class AdminChatbotCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_comment_with_context()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $payload = [
            'comment' => 'This is a test admin comment',
            'conversation_context' => json_encode([
                ['role' => 'user', 'content' => 'hola'],
                ['role' => 'assistant', 'content' => 'Hola, soy Aurora']
            ]),
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.chatbot.comment.store'), $payload);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('chatbot_admin_comments', [
            'comment' => 'This is a test admin comment',
            'created_by' => $admin->id,
        ]);

        $comment = ChatbotAdminComment::first();
        $this->assertIsArray($comment->conversation_context);
        $this->assertCount(2, $comment->conversation_context);
    }

    public function test_non_admin_cannot_store_comment()
    {
        $user = User::factory()->create(['role' => User::ROLE_REGULAR]);

        $payload = [
            'comment' => 'This should fail',
            'conversation_context' => null,
        ];

        $response = $this->actingAs($user)->postJson(route('admin.chatbot.comment.store'), $payload);
        $response->assertStatus(403);
    }

    public function test_admin_can_fetch_comments_list_and_view()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $c1 = ChatbotAdminComment::create(['comment' => 'First comment', 'conversation_context' => [['role' => 'user','content'=>'hi']], 'created_by' => $admin->id]);
        $c2 = ChatbotAdminComment::create(['comment' => 'Second comment', 'conversation_context' => null, 'created_by' => $admin->id]);

        $listResp = $this->actingAs($admin)->getJson(route('admin.chatbot.comment.index'));
        $listResp->assertStatus(200);
        $this->assertArrayHasKey('data', $listResp->json());

        $showResp = $this->actingAs($admin)->getJson(route('admin.chatbot.comment.show', ['id' => $c1->id]));
        $showResp->assertStatus(200)->assertJsonFragment(['comment' => 'First comment']);
    }

    public function test_non_admin_cannot_fetch_comments()
    {
        $user = User::factory()->create(['role' => User::ROLE_REGULAR]);
        $resp = $this->actingAs($user)->getJson(route('admin.chatbot.comment.index'));
        $resp->assertStatus(403);
    }
}
