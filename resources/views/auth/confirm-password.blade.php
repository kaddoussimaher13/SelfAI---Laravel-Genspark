<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800 mb-4">{{ __('auth_ui.confirm_title') }}</h1>

    <div class="mb-4 text-sm text-gray-600">{{ __('auth_ui.confirm_intro') }}</div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <x-input-label for="password" :value="__('auth_ui.field_password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>{{ __('auth_ui.confirm_button') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
