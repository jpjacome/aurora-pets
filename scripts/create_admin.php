<?php
// Create a default admin user
// Run from project root: php scripts/create_admin.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

try {
    $email = 'admin@example.com';
    $exists = User::where('email', $email)->first();
    if ($exists) {
        echo "Admin already exists: {$exists->email} (id={$exists->id})\n";
        exit(0);
    }

    $user = User::create([
        'name' => 'Administrator',
        'email' => $email,
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    echo "Created admin user: {$user->email} (id={$user->id}).\n";
    echo "Password: password (please change this immediately).\n";
} catch (Throwable $e) {
    echo "Failed creating admin: " . $e->getMessage() . "\n";
    exit(1);
}
