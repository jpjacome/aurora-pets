<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

use App\Services\GroqAIService;

$svc = new GroqAIService('gemini');
$tests = [
    'Tengo una nueva perrita Laura y estoy muy feliz',
    'Perdí a mi gato hace una semana, estoy muy triste',
    'Mi perro acaba de morir, necesito ayuda urgente',
    '¿Cómo funciona el servicio?' 
];

foreach ($tests as $t) {
    $res = $svc->generateResponse($t, []);
    echo "Message: $t\n";
    echo "Expression: " . ($res['insights']['expression'] ?? '--') . "\n";
    echo "Response: " . substr($res['response'], 0, 140) . "...\n\n";
}
