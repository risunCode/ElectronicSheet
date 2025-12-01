<?php

use App\Models\User;
use App\Models\ReferralCode;
use App\Models\ReferralUsage;
use App\Models\UserSetting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $referral_code = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'referral_code' => ['required', 'string', 'exists:referral_codes,code'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Validate referral code
        $referralCode = ReferralCode::where('code', $validated['referral_code'])->first();
        
        if (!$referralCode || !$referralCode->isValid()) {
            $this->addError('referral_code', 'Referral code tidak valid atau sudah kadaluarsa.');
            return;
        }

        DB::transaction(function () use ($validated, $referralCode) {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'referred_by' => $referralCode->created_by,
            ]);

            // Assign role from referral code
            $user->roles()->attach($referralCode->assigned_role_id, [
                'assigned_by' => $referralCode->created_by,
            ]);

            // Create user settings
            UserSetting::create(['user_id' => $user->id]);

            // Record referral usage
            ReferralUsage::create([
                'referral_code_id' => $referralCode->id,
                'user_id' => $user->id,
            ]);

            // Increment referral usage count
            $referralCode->incrementUsage();

            event(new Registered($user));
            Auth::login($user);
        });

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Referral Code -->
        <div class="mt-4">
            <x-input-label for="referral_code" :value="__('Referral Code')" />
            <x-text-input wire:model="referral_code" id="referral_code" class="block mt-1 w-full" type="text" name="referral_code" required placeholder="Masukkan kode referral" />
            <x-input-error :messages="$errors->get('referral_code')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kode referral diperlukan untuk registrasi.</p>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
