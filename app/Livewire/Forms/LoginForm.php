<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class LoginForm extends Form
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function authenticate(): bool
    {
        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        // Try admin login first
        if (Auth::guard('web')->attempt($credentials, $this->remember)) {
            session()->regenerate(); // important for session security
            $user = Auth::guard('web')->user();
            $userId = $user->id;
            cache()->put('user-is-online-' . $userId, true, now()->addMinutes(5));

            return true;
        }

        // Try staff login
        if (Auth::guard('staff')->attempt($credentials, $this->remember)) {
            session()->regenerate();
            $staff = Auth::guard('staff')->user();
            //$userId = $staff->staff_id;
            $userId = $staff->id;
            cache()->put('user-is-online-' . $userId, true, now()->addMinutes(5));
            return true;
        }

        return false;
    }
}
