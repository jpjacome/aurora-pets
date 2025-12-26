<?php

namespace App\Mcp\Tools;

use App\Jobs\SendWhatsAppMessage;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Json\Jsonable;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use App\Mcp\Attributes\IsOpenWorld;
use App\Mcp\Attributes\IsIdempotent;

#[IsOpenWorld]
#[IsIdempotent(true)]
class SendWhatsAppMessageTool extends Tool
{
    public function schema($schema): array
    {
        return [
            'to' => $schema->string()->description('Phone number with country code (E.164)')->required(),
            'message' => $schema->string()->description('Message body')->required(),
            'dry_run' => $schema->boolean()->default(true)->description('When true, do not actually send the message'),
        ];
    }

    public function outputSchema($schema): array
    {
        return [
            'status' => $schema->string()->required(),
            'provider_message_id' => $schema->string()->nullable(),
        ];
    }

    public function handle(Request $request, Dispatcher $dispatcher): Response
    {
        $to = $request->string('to');
        $message = $request->string('message');
        $dryRun = (bool) ($request->get('dry_run') ?? true);

        if ($dryRun) {
            // Do not dispatch job for dry runs; return dry_run status
            return Response::structured(['status' => 'dry_run', 'provider_message_id' => null]);
        }

        // Dispatch the job to send the message
        $job = new SendWhatsAppMessage($to, $message, false);
        $dispatcher->dispatch($job);

        return Response::structured(['status' => 'queued', 'provider_message_id' => null]);
    }
}
