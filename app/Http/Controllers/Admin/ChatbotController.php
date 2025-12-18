<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function index()
    {
        // For now, just show the empty state
        // Will be populated with real data once we create the database tables
        return view('admin.chatbot.index');
    }

    public function show($id)
    {
        // Placeholder - will load actual conversation
        return view('admin.chatbot.show', [
            'conversationId' => $id
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:4096'
        ]);

        // Placeholder - will implement WhatsApp sending
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    }

    public function toggleMode(Request $request, $id)
    {
        $request->validate([
            'is_bot_mode' => 'required|boolean'
        ]);

        // Placeholder - will update conversation mode
        return response()->json([
            'success' => true,
            'is_bot_mode' => $request->is_bot_mode
        ]);
    }

    public function updateLeadScore(Request $request, $id)
    {
        $request->validate([
            'lead_score' => 'required|in:new,cold,warm,hot'
        ]);

        // Placeholder - will update conversation lead score
        return response()->json([
            'success' => true,
            'lead_score' => $request->lead_score
        ]);
    }

    public function archive($id)
    {
        // Placeholder - will archive conversation
        return response()->json([
            'success' => true,
            'message' => 'Conversation archived'
        ]);
    }

    public function export($id)
    {
        // Placeholder - will export conversation to PDF/CSV
        return response()->json([
            'success' => true,
            'message' => 'Export functionality coming soon'
        ]);
    }
}
