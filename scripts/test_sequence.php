<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

$svc = new App\Services\GroqAIService('gemini');

// First message (no history)
$res1 = $svc->generateResponse('hola', []);
echo "First message expression: " . ($res1['insights']['expression'] ?? '--') . "\n";

// Simulate conversation history after first exchange
$history = [
    ['role' => 'user', 'content' => 'hola'],
    ['role' => 'assistant', 'content' => $res1['response']]
];

// Second message (should detect grief if present)
$res2 = $svc->generateResponse('Perdí a mi gato hace una semana', $history);
echo "Second message expression: " . ($res2['insights']['expression'] ?? '--') . "\n";

$kernel->terminate($request, $response);
