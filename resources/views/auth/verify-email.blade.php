<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800 mb-4">{{ __('auth_ui.verify_title') }}</h1>

    <div class="mb-4 text-sm text-gray-600">{{ __('auth_ui.verify_intro') }}</div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">{{ __('auth_ui.verify_sent') }}</div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('auth_ui.verify_resend') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('common.nav_logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
