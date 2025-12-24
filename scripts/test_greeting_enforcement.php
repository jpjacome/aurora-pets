<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

$svc = new App\Services\GroqAIService('groq');

$aiResponse = "Hola 🧡 ¿En qué puedo ayudarte hoy?";
$includeName = true;

// Case A: empty history (no previous messages)
$historyA = [];
$timeGreeting = 'Buenos días';

$ref = new ReflectionClass($svc);
$method = $ref->getMethod('ensureGreetingIncludesName');
$method->setAccessible(true);

$resultA = $method->invoke($svc, $aiResponse, $includeName, $historyA, $timeGreeting);

echo "Case A - empty history:\n";
echo "  Input AI response: $aiResponse\n";
echo "  Enforced response: $resultA\n\n";

// Case B: client includes the current user message in history (no assistant messages)
$historyB = [ ['role' => 'user', 'content' => 'hola hola'] ];
$resultB = $method->invoke($svc, $aiResponse, $includeName, $historyB, $timeGreeting);

echo "Case B - history contains only user message:\n";
echo "  Input AI response: $aiResponse\n";
echo "  Enforced response: $resultB\n\n";

// Also test analyzeConversation behavior (via reflection)
$analyzeMethod = $ref->getMethod('analyzeConversation');
$analyzeMethod->setAccessible(true);
$insights = $analyzeMethod->invoke($svc, 'hola hola', $aiResponse, $historyB);
echo "analyzeConversation insights for 'hola hola' with only user history:\n";
echo json_encode($insights, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// If include_name is set, simulate complete flow of replacing AI response
if (!empty($insights['include_name'])) {
    $final = $method->invoke($svc, $aiResponse, true, $historyB, $insights['time_greeting'] ?? null);
    echo "Final enforced response from simulated flow:\n" . $final . "\n";
} else {
    echo "include_name not set by analyzeConversation - enforcement will not occur.\n";
}


$kernel->terminate($request, $response);
