<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('We will send a 6-digit code to your email. Enter the code to confirm account deletion.') }}
            </p>

            <div class="mt-6">
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                    <form method="POST" action="{{ route('profile.delete-otp.send') }}" class="inline">
                        @csrf
                        <x-secondary-button type="submit">
                            {{ __('Send deletion code') }}
                        </x-secondary-button>
                    </form>
                    @if (session('status') === 'deletion-otp-sent')
                        <p class="text-xs text-green-700 font-medium">{{ __('Code sent. Check your inbox and spam folder.') }}</p>
                    @endif
                </div>

                <form method="post" action="{{ route('profile.destroy') }}" class="mt-3">
                    @csrf
                    @method('delete')

                    <x-text-input
                        id="code"
                        name="code"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="{{ __('6-digit code') }}"
                        inputmode="numeric"
                    />

                    <x-input-error :messages="$errors->userDeletion->get('code')" class="mt-2" />

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button type="button" x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="ms-3">
                            {{ __('Delete Account') }}
                        </x-danger-button>
                    </div>
                </form>
            </div>
        </div>
    </x-modal>
</section>
