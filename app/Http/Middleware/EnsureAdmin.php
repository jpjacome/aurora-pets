<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            // For AJAX/JSON prefer 403, for web redirect to login
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
