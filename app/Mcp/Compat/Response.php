<?php

namespace Laravel\Mcp;

class Response
{
    public int $status = 200;
    protected $content;

    public static function structured($data): self
    {
        $r = new self();
        $r->status = 200;
        $r->content = json_encode($data);
        return $r;
    }

    public static function text(string $text): self
    {
        $r = new self();
        $r->status = 200;
        $r->content = $text;
        return $r;
    }

    public static function error(string $message): self
    {
        $r = new self();
        $r->status = 400;
        $r->content = $message;
        return $r;
    }

    public function getContent(): string
    {
        return (string) $this->content;
    }

    public function assertOk()
    {
        if ($this->status < 200 || $this->status >= 300) {
            throw new \Exception('Response not OK: status ' . $this->status);
        }

        return true;
    }

    public function assertStatus(int $code)
    {
        if ($this->status !== $code) {
            throw new \Exception('Unexpected status: ' . $this->status . ' expected ' . $code);
        }

        return true;
    }
}
