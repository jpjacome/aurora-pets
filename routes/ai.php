<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\ChatbotServer;

// Register Chatbot MCP web server (protect with Sanctum middleware for internal use)
Mcp::web('/mcp/chatbot', ChatbotServer::class)
    ->middleware(['auth:sanctum']);
