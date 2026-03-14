<x-guest-layout>
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            Please fix the highlighted fields and try again.
        </div>
    @endif

    @if(session('verification_email'))
        <script>
            window.location.href = "{{ route('verification.code') }}?email={{ urlencode(session('verification_email')) }}";
        </script>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        @php($ngStatesCities = (array) (config('nigeria.states_cities') ?? []))

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            @if($errors->has('email'))
                @php($emailError = strtolower((string) $errors->first('email')))
                @if(str_contains($emailError, 'taken') || str_contains($emailError, 'already'))
                    <p class="mt-2 text-xs text-amber-700">
                        This email already has an account.
                        <a href="{{ route('login') }}" class="underline font-semibold">Log in</a>
                        or
                        <a href="{{ route('password.request') }}" class="underline font-semibold">reset password with OTP</a>.
                    </p>
                @endif
            @endif
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" inputmode="tel" placeholder="{{ __('Number reachable for delivery and logistics (e.g. call/WhatsApp)') }}" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4"
             x-data="{
                statesCities: @js($ngStatesCities),
                state: @js(old('state')),
                city: @js(old('city')),
                get states() { return Object.keys(this.statesCities || {}).sort(); },
                get cities() {
                    const s = (this.state || '').trim();
                    return (this.statesCities && this.statesCities[s]) ? this.statesCities[s] : [];
                },
                onStateChange() {
                    if (!this.cities.includes(this.city)) {
                        this.city = '';
                    }
                }
             }">
            <div>
                <x-input-label for="state" :value="__('State')" />
                <input id="state" name="state" type="text"
                       class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       list="ng_states_register"
                       x-model="state"
                       x-on:input.debounce.100="onStateChange()"
                       required autocomplete="address-level1" maxlength="100" placeholder="e.g. Lagos">
                <datalist id="ng_states_register">
                    <template x-for="s in states" :key="s">
                        <option :value="s"></option>
                    </template>
                </datalist>
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="city" :value="__('City')" />
                <input id="city" name="city" type="text"
                       class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       :list="cities.length ? 'ng_cities_register' : null"
                       x-model="city"
                       required autocomplete="address-level2" maxlength="100" placeholder="e.g. Ikeja">
                <datalist id="ng_cities_register">
                    <template x-for="c in cities" :key="c">
                        <option :value="c"></option>
                    </template>
                </datalist>
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
