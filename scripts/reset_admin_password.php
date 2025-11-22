<?php
// One-off script to reset admin password. Run from project root:
// php scripts/reset_admin_password.php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;

try {
    if (!class_exists(\App\Models\User::class)) {
        echo "User model not found (App\\Models\\User).\n";
        exit(1);
    }

    // Find admin by common markers
    $user = \App\Models\User::where('role', 'admin')->first();
    if (! $user) {
        $user = \App\Models\User::where('is_admin', 1)->first();
    }
    if (! $user) {
        // fallback: first user with email containing 'admin' or id=1
        $user = \App\Models\User::where('email', 'like', '%admin%')->first();
    }
    if (! $user) {
        $user = \App\Models\User::find(1);
    }

    if (! $user) {
        echo "No admin-like user found to reset.\n";
        exit(1);
    }

    $user->password = Hash::make('password');
    $user->save();

    echo "Password reset successful for {$user->email} (id={$user->id}).\n";
    echo "NOTE: The new password is the literal string: 'password' — this is insecure. Change it immediately via the app.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
