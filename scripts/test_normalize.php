<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::createFromGlobals();
$response = $kernel->handle($request);

$svc = new App\Services\GroqAIService('gemini');
$rm = new ReflectionMethod(App\Services\GroqAIService::class, 'normalizeText');
$rm->setAccessible(true);

$tests = [
    'Perdí a mi gato hace una semana, estoy muy triste',
    'Mi perro acaba de morir, necesito ayuda urgente',
    'Tengo una nueva perrita Laura y estoy muy feliz',
    '¿Cómo funciona el servicio?'
];

foreach ($tests as $t) {
    $norm = $rm->invoke($svc, $t);
    echo "Original: $t\nNormalized: $norm\n\n";
}

$kernel->terminate($request, $response);
