<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        <p class="font-semibold">Check your email</p>
        @if(!empty($maskedEmail))
            <p class="mt-1">We sent a 6-digit verification code to <span class="font-semibold">{{ $maskedEmail }}</span>.</p>
        @else
            <p class="mt-1">We sent a 6-digit verification code. Enter it below to secure your account.</p>
        @endif
        <p class="mt-1">If you don't see it, check your spam/junk folder.</p>
    </div>

    <form method="POST" action="{{ route('verification.code.verify') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email', session('verification_email', $email ?? ''))" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input id="code" class="block mt-1 w-full tracking-[0.3em]" type="text" name="code" maxlength="6" required />
            <p class="mt-1 text-xs text-gray-500">Enter the 6-digit code sent to your email.</p>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between gap-3">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium hover:bg-blue-700">
                Verify Code
            </button>
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Back to Login</a>
        </div>
    </form>

    <form method="POST" action="{{ route('verification.code.resend') }}" class="mt-4">
        @csrf
        <input type="hidden" name="email" value="{{ old('email', session('verification_email', $email ?? '')) }}">
        <div x-data="{ cooldownUntil: {{ (int) ($resendAvailableAt ?? 0) }}, nowTs: Math.floor(Date.now() / 1000), init(){ setInterval(() => this.nowTs = Math.floor(Date.now() / 1000), 1000); }, get remaining(){ return Math.max(0, this.cooldownUntil - this.nowTs); } }">
            <button type="submit"
                    :disabled="remaining > 0"
                    class="text-sm underline"
                    :class="remaining > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-blue-700 hover:text-blue-900'">
                <span x-show="remaining === 0">Resend code</span>
                <span x-show="remaining > 0">Resend in <span x-text="remaining"></span>s</span>
            </button>
        </div>
    </form>
</x-guest-layout>
