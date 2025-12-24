<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

// Create a sample comment (created_by left null for simplicity in script)
$comment = App\Models\ChatbotAdminComment::create([
    'comment' => 'Sample comment created by script',
    'conversation_context' => [
        ['role' => 'user', 'content' => 'hola'],
        ['role' => 'assistant', 'content' => 'Hola!']
    ],
    'created_by' => null
]);

echo "Created comment id: {$comment->id}\n";
$kernel->terminate($request, $response);
