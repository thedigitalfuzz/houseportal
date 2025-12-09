<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthAnyGuard
{
    /**
     * Allow either the 'web' (users) or 'staff' guard to access.
     * Also set the active guard for the request so other middleware (eg. verified)
     * reads the correct authenticated user.
     */
    public function handle($request, Closure $next)
    {
        // If web (users) is authenticated, make it the active guard for the request.
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web'); // important: set default for the request
            return $next($request);
        }

        // If staff guard is authenticated, set staff as default for the request.
        if (Auth::guard('staff')->check()) {
            Auth::shouldUse('staff'); // important: set default for the request
            return $next($request);
        }

        // none authenticated -> redirect to login
        return redirect()->route('login');
    }
}
