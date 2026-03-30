<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckStaffRole
{
public function handle($request, Closure $next, $roles)
{
// If admin logged in via web guard → allow everything
if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'admin') {
return $next($request);
}

// Staff guard: If no staff logged in, block the access
if (!Auth::guard('staff')->check()) {
abort(403, 'Unauthorized');
}

// Get the authenticated staff
$staff = Auth::guard('staff')->user();

// Handle roles if not empty
if (!empty($roles)) {
$rolesArray = explode(',', $roles);

// Check if the authenticated staff role is allowed
if (!in_array(strtolower($staff->role), array_map('strtolower', $rolesArray))) {
abort(403, 'Unauthorized');
}
}

return $next($request);
}
}
