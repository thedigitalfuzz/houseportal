<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthAnyGuard
{
    public function handle($request, Closure $next)
    {
        // Set the active guard for this request
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');
        } elseif (Auth::guard('staff')->check()) {
            Auth::shouldUse('staff');
        } else {
            // none authenticated -> redirect to login
            return redirect()->route('login');
        }

        return $next($request);
    }
}
