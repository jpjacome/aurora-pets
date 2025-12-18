<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueueCampaignJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected EmailCampaign $campaign;

    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle(): void
    {
        $batchSize = 100; // default chunk size

        $messageIds = $this->campaign->messages()->where('status', 'queued')->pluck('id')->toArray();

        // split in chunks and dispatch SendCampaignBatchJob
        $chunks = array_chunk($messageIds, $batchSize);

        foreach ($chunks as $chunk) {
            SendCampaignBatchJob::dispatch($chunk);
        }

        Log::info('QueueCampaignJob dispatched ' . count($chunks) . ' batch(es) for campaign ' . $this->campaign->id);
    }
}
