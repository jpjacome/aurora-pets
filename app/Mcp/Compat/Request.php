<?php

namespace Laravel\Mcp;

class Request
{
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function string(string $key): string
    {
        return (string) ($this->data[$key] ?? '');
    }

    public function int(string $key): ?int
    {
        return isset($this->data[$key]) ? (int) $this->data[$key] : null;
    }

    // Allow get() alias
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}
