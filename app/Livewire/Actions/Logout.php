<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        $user = Auth::guard('staff')->user() ?? Auth::guard('web')->user();

        if ($user) {
            $userId = $user->staff_id ?? $user->id;
            cache()->forget('user-is-online-' . $userId);
        }

        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();
        Session::invalidate();
        Session::regenerateToken();
    }
}
