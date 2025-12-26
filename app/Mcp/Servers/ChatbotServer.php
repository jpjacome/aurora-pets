<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class ChatbotServer extends Server
{
    /**
     * The MCP server's name.
     *
     * @var string
     */
    protected string $name = 'Aurora Chatbot Server';

    /**
     * The MCP server's version.
     *
     * @var string
     */
    protected string $version = '0.1.0';

    /**
     * Server instructions for LLMs / clients.
     *
     * @var string
     */
    protected string $instructions = 'Provides chat-related tools, knowledge resources, and prompts for Aurora chatbot.';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        \App\Mcp\Tools\PlantLookupTool::class,
        \App\Mcp\Tools\PlantRecommendTool::class,
        // \App\Mcp\Tools\SendWhatsAppMessageTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        \App\Mcp\Resources\KnowledgeResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        \App\Mcp\Prompts\AuroraPrompt::class,
    ];
}
