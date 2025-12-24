<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ChatbotController;

$request = Request::create('/admin/chatbot/test/send', 'POST', [
    'message' => 'Hola, quería saber cómo funciona el servicio',
    'provider' => 'gemini'
]);

$controller = new ChatbotController();
$response = $controller->testSend($request);

echo $response->getContent();
