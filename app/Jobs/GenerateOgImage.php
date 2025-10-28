<?php

namespace App\Jobs;

use App\Models\Test;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateOgImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $testId;

    public function __construct($testId)
    {
        $this->testId = $testId;
    }

    public function handle()
    {
        $test = Test::find($this->testId);
        if (!$test) return;

        // Attempt to use Spatie Browsershot to render an HTML template to PNG.
        try {
            // Render blade to HTML
            $html = view('plantscan.og-template', ['test' => $test])->render();

            if (class_exists('\Spatie\Browsershot\Browsershot')) {
                $fileName = 'og-images/' . ($test->share_token ?: $test->id) . '.png';
                $fullPath = storage_path('app/public/' . $fileName);

                // Create directory
                if (!file_exists(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);

                \Spatie\Browsershot\Browsershot::html($html)
                    ->windowSize(1200, 630)
                    ->save($fullPath);

                // Update test record
                $test->og_image = $fileName;
                $test->og_ready = true;
                $test->save();
            } else {
                // Browsershot not available — skip image generation
                logger()->warning('Browsershot not installed; skipping OG image generation.');
            }
        } catch (\Throwable $e) {
            logger()->error('GenerateOgImage failed: ' . $e->getMessage());
        }
    }
}
