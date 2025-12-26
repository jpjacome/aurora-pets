<?php

namespace Tests\Unit;

use App\Mcp\Tools\SendWhatsAppMessageTool;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Contracts\Bus\Dispatcher;
use Tests\TestCase;

class SendWhatsAppMessageToolTest extends TestCase
{
    public function test_dry_run_returns_dry_run_status()
    {
        $tool = new SendWhatsAppMessageTool();
        $req = new \Laravel\Mcp\Request(['to' => '+15550001111', 'message' => 'Hello', 'dry_run' => true]);

        $dispatcher = \Mockery::mock(Dispatcher::class);

        $response = $tool->handle($req, $dispatcher);

        $this->assertEquals(200, $response->status);
        $this->assertStringContainsString('dry_run', $response->getContent());
    }

    public function test_dispatches_job_when_not_dry_run()
    {
        $tool = new SendWhatsAppMessageTool();
        $req = new \Laravel\Mcp\Request(['to' => '+15550001111', 'message' => 'Hi there', 'dry_run' => false]);

        $dispatcher = \Mockery::mock(Dispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once()->withArgs(function ($job) {
            return $job instanceof SendWhatsAppMessage && $job->to === '+15550001111';
        });

        $response = $tool->handle($req, $dispatcher);

        $this->assertEquals(200, $response->status);
        $this->assertStringContainsString('queued', $response->getContent());
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
