<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatbotAdminComment;
use Illuminate\Support\Facades\Log;

class ChatbotAdminCommentController extends Controller
{
    public function __construct()
    {
        // Ensure only admin users can access (EnsureAdmin middleware is applied globally to admin routes)
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ChatbotAdminComment::with('creator')->orderBy('created_at', 'desc');

        // If the request expects JSON (AJAX), return paginated JSON
        if ($request->wantsJson() || $request->ajax()) {
            $comments = $query->paginate(10);
            $data = $comments->through(function ($c) {
                return [
                    'id' => $c->id,
                    'comment_preview' => strlen($c->comment) > 140 ? substr($c->comment, 0, 137) . '...' : $c->comment,
                    'created_by' => $c->creator ? $c->creator->name : null,
                    'created_at' => $c->created_at->toDateTimeString(),
                ];
            });

            return response()->json($data);
        }

        // For normal browser requests, show management view
        $comments = $query->paginate(20);
        return view('admin.chatbot.comments', ['comments' => $comments]);
    }

    public function show($id)
    {
        $c = ChatbotAdminComment::with('creator')->findOrFail($id);
        return response()->json([
            'id' => $c->id,
            'comment' => $c->comment,
            'created_by' => $c->creator ? $c->creator->name : null,
            'created_at' => $c->created_at->toDateTimeString(),
            'conversation_context' => $c->conversation_context,
        ]);
    }

    public function store(\App\Http\Requests\StoreChatbotAdminCommentRequest $request)
    {
        // Log incoming request for debugging
        Log::info('ChatbotAdminComment store called', ['user_id' => Auth::id(), 'payload_keys' => array_keys($request->all())]);

        try {
            $context = null;
            if ($request->filled('conversation_context')) {
                try {
                    $decoded = json_decode($request->conversation_context, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $context = $decoded;
                    } else {
                        // If non-JSON, store raw text under a key
                        $context = ['raw' => $request->conversation_context];
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to decode conversation_context: ' . $e->getMessage());
                    $context = ['raw' => $request->conversation_context];
                }
            }

            $comment = ChatbotAdminComment::create([
                'comment' => $request->comment,
                'conversation_context' => $context,
                'created_by' => Auth::id(),
            ]);

            Log::info('ChatbotAdminComment created', ['id' => $comment->id, 'created_by' => $comment->created_by]);

            return response()->json([
                'success' => true,
                'comment' => $comment,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error storing ChatbotAdminComment: ' . $e->getMessage(), ['exception' => $e]);
            $resp = ['success' => false, 'message' => 'Server error saving comment'];
            if (config('app.debug')) {
                $resp['error'] = $e->getMessage();
            }
            return response()->json($resp, 500);
        }
    }
}
