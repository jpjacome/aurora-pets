<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Ensure framework is booted (start session, container bindings)
$initRequest = Illuminate\Http\Request::create('/', 'GET');
$initResponse = $kernel->handle($initRequest);

// Create admin user
$admin = App\Models\User::firstOrCreate(['email' => 'admin2@example.com'], ['name' => 'Admin Two', 'password' => password_hash('password', PASSWORD_BCRYPT), 'role' => App\Models\User::ROLE_ADMIN]);

// Login the admin for auth checks
Illuminate\Support\Facades\Auth::login($admin);

// Build a FormRequest instance
$req = App\Http\Requests\StoreChatbotAdminCommentRequest::create('/admin/chatbot/comment', 'POST', [
    'comment' => 'Simulated comment from authenticated script',
    'conversation_context' => json_encode([['role'=>'user','content'=>'hola'],['role'=>'assistant','content'=>'Hola']])
]);
// Ensure getUser() returns our admin
$req->setUserResolver(function() use ($admin) { return $admin; });
$req->setRouteResolver(function() use ($app) { return $app['router']->current(); });

$ref = new ReflectionClass(App\Http\Controllers\Admin\ChatbotAdminCommentController::class);
$controller = $ref->newInstanceWithoutConstructor();
$method = $ref->getMethod('store');
$method->setAccessible(true);
try {
    $resp = $method->invoke($controller, $req);
    // If response is a JsonResponse
    if (method_exists($resp, 'getData')) {
        echo 'Controller response: ' . json_encode($resp->getData(true)) . "\n";
    } else {
        echo 'Controller returned: ' . var_export($resp, true) . "\n";
    }
} catch (\Throwable $e) {
    echo 'Controller exception: ' . $e->getMessage() . "\n";
}
