<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $to;
    public string $message;
    public bool $dryRun;

    public function __construct(string $to, string $message, bool $dryRun = true)
    {
        $this->to = $to;
        $this->message = $message;
        $this->dryRun = $dryRun;
    }

    public function handle()
    {
        // Placeholder: actual implementation will call Meta/WhatsApp API using configured credentials.
        if ($this->dryRun) {
            // Log or noop for dry runs
            return ['status' => 'dry_run'];
        }

        // Simulate sending (the real implementation would use an injected service)
        // Return fake provider message id for now
        return ['status' => 'sent', 'provider_message_id' => 'mock-123'];
    }
}
