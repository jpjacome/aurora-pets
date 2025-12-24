<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\GroqAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function index()
    {
        // Get all active (non-archived) conversations with client relationship
        $conversations = WhatsAppConversation::with('client')
            ->active()
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Calculate dashboard stats
        $stats = [
            'total_conversations' => WhatsAppConversation::active()->count(),
            'unread_count' => WhatsAppConversation::active()->unread()->count(),
            'bot_mode_active' => WhatsAppConversation::active()->where('is_bot_mode', true)->count(),
            'hot_leads' => WhatsAppConversation::active()->byLeadScore('hot')->count(),
        ];

        return view('admin.chatbot.index', [
            'conversations' => $conversations,
            'stats' => $stats,
        ]);
    }

    public function show($id)
    {
        $conversation = WhatsAppConversation::with(['client', 'messages'])
            ->findOrFail($id);

        // Mark conversation as read
        $conversation->markAsRead();

        return view('admin.chatbot.show', [
            'conversation' => $conversation,
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:4096'
        ]);

        $conversation = WhatsAppConversation::findOrFail($id);

        // Create outgoing message record
        $message = $conversation->messages()->create([
            'direction' => 'outgoing',
            'content' => $request->message,
            'sent_by_bot' => false,
            'status' => 'pending',
        ]);

        // Update conversation timestamp
        $conversation->update([
            'last_message_at' => now(),
        ]);

        // TODO: Send via WhatsApp API and update message status
        // For now, mark as sent
        $message->updateStatus('sent');

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
        ]);
    }

    public function toggleMode(Request $request, $id)
    {
        $request->validate([
            'is_bot_mode' => 'required|boolean'
        ]);

        $conversation = WhatsAppConversation::findOrFail($id);
        $conversation->update([
            'is_bot_mode' => $request->is_bot_mode,
        ]);

        return response()->json([
            'success' => true,
            'is_bot_mode' => $conversation->is_bot_mode,
        ]);
    }

    public function updateLeadScore(Request $request, $id)
    {
        $request->validate([
            'lead_score' => 'required|in:new,cold,warm,hot'
        ]);

        $conversation = WhatsAppConversation::findOrFail($id);
        $conversation->update([
            'lead_score' => $request->lead_score,
        ]);

        return response()->json([
            'success' => true,
            'lead_score' => $conversation->lead_score,
        ]);
    }

    public function archive($id)
    {
        $conversation = WhatsAppConversation::findOrFail($id);
        $conversation->update([
            'is_archived' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived',
        ]);
    }

    public function export($id)
    {
        $conversation = WhatsAppConversation::with(['client', 'messages'])
            ->findOrFail($id);

        // Prepare export data
        $exportData = [
            'phone_number' => $conversation->phone_number,
            'contact_name' => $conversation->contact_name,
            'client' => $conversation->client ? [
                'name' => $conversation->client->client,
                'pet_name' => $conversation->client->pet_name,
            ] : null,
            'lead_score' => $conversation->lead_score,
            'created_at' => $conversation->created_at->format('Y-m-d H:i:s'),
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'direction' => $message->direction,
                    'content' => $message->content,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ];

        // Return as JSON download
        return response()->json($exportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="conversation-' . $id . '.json"');
    }

    /**
     * Show test chatbot interface
     */
    public function testIndex()
    {
        return view('admin.chatbot.test');
    }

    /**
     * Process test message and return AI response with insights
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:4096',
            'conversation_history' => 'array',
            'provider' => 'string|in:groq,deepseek,gemini',
        ]);

        try {
            $startTime = microtime(true);
            $message = $request->message;
            $conversationHistory = $request->conversation_history ?? [];
            $provider = $request->provider ?? 'groq';

            // Use AI Service with selected provider
            $aiService = new GroqAIService($provider);
            $result = $aiService->generateResponse($message, $conversationHistory);

            // Calculate response time
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Add response time to insights
            $result['insights']['response_time'] = $responseTime . 'ms';

            // Include daily usage counters for the model (requests/tokens and limits)
            try {
                $usage = $aiService->getDailyUsage();
                // compute remaining if limits available
                $remaining = null;
                if (!is_null($usage['request_limit'])) {
                    $remaining = max(0, $usage['request_limit'] - $usage['requests']);
                }
                $result['insights']['usage'] = array_merge($usage, ['remaining_requests' => $remaining]);
            } catch (\Throwable $e) {
                $result['insights']['usage'] = null;
            }

            // Indicate whether the server provided an expression
            $result['insights']['expression_source'] = isset($result['insights']['expression']) ? 'server' : null;

            return response()->json([
                'success' => $result['success'],
                'response' => $result['response'],
                'insights' => $result['insights'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process message: ' . $e->getMessage(),
            ], 500);
        }
    }}