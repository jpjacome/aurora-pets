<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\GroqAIService;

// Create service instance without running the constructor (skips API key checks)
$ref = new ReflectionClass(GroqAIService::class);
$svc = $ref->newInstanceWithoutConstructor();

// Access private method analyzeConversation
$method = $ref->getMethod('analyzeConversation');
$method->setAccessible(true);

$history = [];

$messages = [
    ['text' => 'hola aurora'],
    ['text' => 'tengo un problema grave'],
    ['text' => 'mi planta ha muerto. nos fuimos de vacaciones y nadie le puso agua'],
];

// Helper to choose greeting variants (shorter when user directly mentions 'aurora')
function greetingFor(string $userMessage): string {
    $user = strtolower($userMessage);

    $variantsWithName = [
        'Hola 🧡 ¿Cómo estás? ¿En qué puedo ayudarte hoy?',
        'Hola 🧡 ¿Qué necesitas hoy?'
    ];

    $variantsGeneral = [
        'Hola 🧡 Soy Aurora. Estoy aquí para ayudarte con lo que necesites. ¿Qué buscas hoy?',
        'Hola 🧡 Estoy aquí para ayudarte. ¿Cómo puedo asistirte hoy?'
    ];

    if (strpos($user, 'aurora') !== false) {
        return $variantsWithName[array_rand($variantsWithName)];
    }

    return $variantsGeneral[array_rand($variantsGeneral)];
}

foreach ($messages as $i => $m) {
    $user = $m['text'];
    $aiResponse = '';
    // For the analyzer, provide the last AI response where appropriate
    if ($i === 0) {
        // First message: no history
        $ins = $method->invoke($svc, $user, $aiResponse, $history);
        echo "Message: {$user}\n";
        echo " -> Expression: " . ($ins['expression'] ?? '--') . "\n\n";
        // Simulate AI reply and push to history
        $aiReply = greetingFor($user);
        // Apply server-side enforcement as GroqAIService would do (ensure name included when appropriate)
        $refMethod = $ref->getMethod('ensureGreetingIncludesName');
        $refMethod->setAccessible(true);
        $aiReply = $refMethod->invoke($svc, $aiReply, ($ins['include_name'] ?? false), $history);

        $history[] = ['role' => 'user', 'content' => $user];
        $history[] = ['role' => 'assistant', 'content' => $aiReply];
    } elseif ($i === 1) {
        $ins = $method->invoke($svc, $user, $aiResponse, $history);
        echo "Message: {$user}\n";
        echo " -> Expression: " . ($ins['expression'] ?? '--') . "\n\n";
        $aiReply = 'Entiendo que algo te preocupa mucho. Por favor, tómate un momento. Estoy aquí para escucharte y ayudarte en lo que pueda.';
        $history[] = ['role' => 'user', 'content' => $user];
        $history[] = ['role' => 'assistant', 'content' => $aiReply];
    } else {
        $ins = $method->invoke($svc, $user, $aiResponse, $history);
        echo "Message: {$user}\n";
        echo " -> Expression: " . ($ins['expression'] ?? '--') . "\n\n";
    }
}
