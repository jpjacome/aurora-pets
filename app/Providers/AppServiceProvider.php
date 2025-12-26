<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use the admin pagination view as the global default for links()
        Paginator::defaultView('vendor.pagination.admin');

        // Load MCP shims for tests when laravel/mcp package is not installed
        $shim = app_path('Helpers/mcp_shims.php');
        if (file_exists($shim)) {
            require_once $shim;
        }
    }
}
