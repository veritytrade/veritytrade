<section>
    @php
        $rawPhone = old('phone', $user->getDisplayPhone());
        $safePhone = preg_match('/^\+?[0-9]{6,20}$/', $rawPhone) ? $rawPhone : '';
        $ngStatesCities = (array) (config('nigeria.states_cities') ?? []);
    @endphp
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @if ($errors->any())
        <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <p class="font-medium">{{ __('Please fix the following:') }}</p>
            <ul class="mt-2 list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
    @endif
    @if (session('status') === 'profile-updated')
        <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">{{ __('Profile saved successfully.') }}</div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="$safePhone" required autocomplete="tel" inputmode="tel" placeholder="{{ __('Number reachable for delivery and logistics (e.g. call/WhatsApp)') }}" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        @if(feature_enabled('enable_customer_address', false))
            <div>
                <x-input-label for="address" :value="__('Address')" />
                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->getDisplayAddress())" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
             x-data="{
                statesCities: @js($ngStatesCities),
                state: @js(old('state', $user->getDisplayState())),
                city: @js(old('city', $user->getDisplayCity())),
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
                <x-input-label for="state" :value="__('State (Nigeria)')" />
                <input id="state" name="state" type="text"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       list="ng_states"
                       x-model="state"
                       x-on:input.debounce.100="onStateChange()"
                       autocomplete="address-level1"
                       placeholder="e.g. Lagos">
                <datalist id="ng_states">
                    <template x-for="s in states" :key="s">
                        <option :value="s"></option>
                    </template>
                </datalist>
                <x-input-error class="mt-2" :messages="$errors->get('state')" />
            </div>

            <div>
                <x-input-label for="city" :value="__('City')" />
                <input id="city" name="city" type="text"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       :list="cities.length ? 'ng_cities' : null"
                       x-model="city"
                       autocomplete="address-level2"
                       placeholder="e.g. Ikeja">
                <datalist id="ng_cities">
                    <template x-for="c in cities" :key="c">
                        <option :value="c"></option>
                    </template>
                </datalist>
                <p class="mt-1 text-xs text-gray-500" x-show="!cities.length && (state || '').trim().length">
                    {{ __('Select a state to see suggested cities.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
