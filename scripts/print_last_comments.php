<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

$limit = $argv[1] ?? 3;
$comments = App\Models\ChatbotAdminComment::with('creator')->orderBy('created_at', 'desc')->limit($limit)->get();
if ($comments->isEmpty()) {
    echo "No comments found\n";
    exit(1);
}

foreach ($comments as $c) {
    echo "==============================\n";
    echo "Comment ID: {$c->id}\n";
    echo "Created by: " . ($c->creator?->name ?? 'System') . "\n";
    echo "Created at: " . $c->created_at->toDateTimeString() . "\n\n";
    echo "Comment:\n{$c->comment}\n\n";
    echo "Conversation context:\n";
    echo json_encode($c->conversation_context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

$kernel->terminate($request, $response);
