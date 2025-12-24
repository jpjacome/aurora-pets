<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

$model = 'gemini-flash-lite-latest';
$reqKey = "ai_usage:" . date('Y-m-d') . ":{$model}:requests";
$tokKey = "ai_usage:" . date('Y-m-d') . ":{$model}:tokens";

$requests = \Cache::get($reqKey, 0);
$tokens = \Cache::get($tokKey, 0);

echo "Model: {$model}\n";
echo "Requests today: {$requests}\n";
echo "Tokens today: {$tokens}\n";

$kernel->terminate($request, $response);
