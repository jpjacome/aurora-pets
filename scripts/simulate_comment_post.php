<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/chatbot/comment', 'POST', [], [], [], [], json_encode([
    'comment' => 'Simulated comment from script',
    'conversation_context' => json_encode([['role'=>'user','content'=>'hola'],['role'=>'assistant','content'=>'Hola']])
]));
$response = $kernel->handle($request);
echo 'HTTP status: ' . $response->getStatusCode() . "\n";
// Show body
echo (string)$response->getContent() . "\n";
$kernel->terminate($request, $response);
