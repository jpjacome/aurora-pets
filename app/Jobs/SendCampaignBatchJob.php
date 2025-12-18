<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Models\EmailMessage;

class SendCampaignBatchJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected array $messageIds;

    public function __construct(array $messageIds)
    {
        $this->messageIds = $messageIds;
    }

    public function handle(): void
    {
        $messages = EmailMessage::whereIn('id', $this->messageIds)->get();

        foreach ($messages as $msg) {
            // Dispatch individual send job to help with per-message retry/failure
            SendCampaignEmailJob::dispatch($msg);
        }

        Log::info('SendCampaignBatchJob queued ' . count($messages) . ' messages');
    }
}
