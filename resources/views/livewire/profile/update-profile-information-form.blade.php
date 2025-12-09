<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // Get currently authenticated user from either guard
        $user = auth()->guard('web')->user() ?? auth()->guard('staff')->user();

        // Initialize properties safely
        $this->name = property_exists($user, 'name') ? $user->name : ($user->staff_name ?? '');
        $this->email = $user->email ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = auth()->guard('web')->user() ?? auth()->guard('staff')->user();

        // Determine which fields to validate based on user type
        $rules = [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ];

        if ($user instanceof \App\Models\User) {
            $rules['name'] = ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($user->id)];
        } elseif ($user instanceof \App\Models\Staff) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        $validated = $this->validate($rules);

        // Map validated 'name' to correct field
        if ($user instanceof \App\Models\User) {
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        } elseif ($user instanceof \App\Models\Staff) {
            $user->email = $validated['email'];
            $user->staff_name = $validated['name'];
        }

        $user->save();

        $this->dispatch('profile-updated', name: $validated['name']);
    }

    /**
     * Send an email verification notification to the current user (only for User).
     */
    public function sendVerification(): void
    {
        $user = auth()->guard('web')->user();
        if (! $user) return; // staff accounts don't use email verification

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if(auth()->guard('web')->check() && auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
