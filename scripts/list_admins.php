<?php
// Lightweight script to bootstrap Laravel and list admin users.
// Run from project root: php scripts/list_admins.php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the framework kernel so Eloquent and config are available
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Try common admin markers: role='admin' or is_admin=1
try {
    $admins = [];
    if (class_exists(\App\Models\User::class)) {
        // First try role='admin'
        try {
            $admins = \App\Models\User::where('role', 'admin')->get(['id','name','email','created_at'])->toArray();
        } catch (Throwable $e) {
            // ignore
        }

        if (empty($admins)) {
            try {
                $admins = \App\Models\User::where('is_admin', 1)->get(['id','name','email','created_at'])->toArray();
            } catch (Throwable $e) {
                // ignore
            }
        }

        // As a last resort, show users with 'admin' in role or email containing admin
        if (empty($admins)) {
            try {
                $admins = \App\Models\User::where('role', 'like', '%admin%')
                    ->orWhere('email', 'like', '%admin%')
                    ->get(['id','name','email','created_at'])->toArray();
            } catch (Throwable $e) {
                // ignore
            }
        }
    }

    echo "Admin users (id, name, email, created_at):\n";
    print_r($admins);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
