<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        Use the 6-digit reset code sent to your email. Codes expire quickly for your security.
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email', $email ?? '')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="code" :value="__('Reset Code')" />
            <x-text-input id="code" class="block mt-1 w-full tracking-[0.3em]" type="text" name="code" maxlength="6" required />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Reset Password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
