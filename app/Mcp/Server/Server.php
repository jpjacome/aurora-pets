<?php

namespace Laravel\Mcp\Server;

class Server
{
    // Minimal placeholder Server class to support tests and avoid hard dependency on Laravel MCP package
    public static function tool(string $toolClass, array $args = [])
    {
        // Instantiate the tool and return a structured response if possible
        $tool = new $toolClass();
        return $tool;
    }
}
