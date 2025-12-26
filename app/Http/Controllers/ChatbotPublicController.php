<?php

namespace App\Http\Controllers;

use App\Services\GroqAIService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatbotPublicController extends Controller
{
    public function send(Request $request)
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
            $provider = $request->provider ?? 'gemini';

            // Use AI Service with selected provider
            $aiService = new GroqAIService($provider);
            $result = $aiService->generateResponse($message, $conversationHistory);

            // Calculate response time
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            $result['insights']['response_time'] = $responseTime . 'ms';

            // Add usage counters if available
            try {
                $usage = $aiService->getDailyUsage();
                $remaining = null;
                if (!is_null($usage['request_limit'])) {
                    $remaining = max(0, $usage['request_limit'] - $usage['requests']);
                }
                $result['insights']['usage'] = array_merge($usage, ['remaining_requests' => $remaining]);
            } catch (\Throwable $e) {
                $result['insights']['usage'] = null;
            }

            return response()->json([
                'success' => $result['success'] ?? true,
                'response' => $result['response'] ?? '',
                'insights' => $result['insights'] ?? [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process message: ' . $e->getMessage(),
            ], 500);
        }
    }
}
