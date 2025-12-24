<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// Initialize a request to boot the framework
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

try {
    $svc = new App\Services\GroqAIService('gemini');
    $res = $svc->generateResponse('hola', []);
    echo "=== RESPONSE ===\n";
    print_r($res);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

$kernel->terminate($request, $response);
