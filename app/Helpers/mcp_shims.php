<?php

// Lightweight shims for Laravel MCP types used in tests when the package is not installed

namespace Laravel\Mcp\Server {
    if (!class_exists('\Laravel\\Mcp\\Server\\Tool')) {
        class Tool {}
    }
    if (!class_exists('\Laravel\\Mcp\\Server\\Server')) {
        class Server {}
    }
}

namespace Laravel\Mcp {
    if (!class_exists('\Laravel\\Mcp\\Request')) {
        class Request {
            protected array $data;
            public function __construct(array $data = []) { $this->data = $data; }
            public function string(string $key): string { return (string) ($this->data[$key] ?? ''); }
            public function int(string $key): ?int { return isset($this->data[$key]) ? (int) $this->data[$key] : null; }
            public function get(string $key, $default = null) { return $this->data[$key] ?? $default; }
        }
    }

    if (!class_exists('\Laravel\\Mcp\\Response')) {
        class Response {
            public int $status = 200;
            protected $content;
            public static function structured($data): self { $r = new self(); $r->status = 200; $r->content = json_encode($data); return $r; }
            public static function error(string $message): self { $r = new self(); $r->status = 400; $r->content = $message; return $r; }
            public function getContent(): string { return (string) $this->content; }
        }
    }
}
