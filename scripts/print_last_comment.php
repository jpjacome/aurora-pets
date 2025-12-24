<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

$c = App\Models\ChatbotAdminComment::with('creator')->latest()->first();
if (!$c) {
    echo "No comments found\n";
    exit(1);
}
echo "Comment ID: {$c->id}\n";
echo "Created by: " . ($c->creator?->name ?? 'System') . "\n";
echo "Created at: " . $c->created_at->toDateTimeString() . "\n\n";
echo "Comment:\n" . $c->comment . "\n\n";
echo "Conversation context:\n";
echo json_encode($c->conversation_context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$kernel->terminate($request, $response);
