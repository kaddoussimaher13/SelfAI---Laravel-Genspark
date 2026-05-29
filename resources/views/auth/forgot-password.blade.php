<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800 mb-4">{{ __('auth_ui.forgot_title') }}</h1>

    <div class="mb-4 text-sm text-gray-600">{{ __('auth_ui.forgot_intro') }}</div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="forgotForm" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div>
            <x-input-label for="email" :value="__('auth_ui.field_email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <p data-error-for="email" class="mt-1 text-xs text-red-600 hidden"></p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('auth_ui.forgot_send_link') }}</x-primary-button>
        </div>
    </form>

    <script>
        (function () {
            const MSG  = @json(__('auth_ui.js'));
            const form = document.getElementById('forgotForm');
            const slot = form.querySelector('[data-error-for="email"]');

            form.addEventListener('submit', function (e) {
                slot.classList.add('hidden'); slot.textContent = '';
                const email = form.email.value.trim();
                let err = '';
                if (!email)                                       err = MSG.email_required;
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) err = MSG.email_invalid;
                if (err) { slot.textContent = err; slot.classList.remove('hidden'); e.preventDefault(); }
            });
        })();
    </script>
</x-guest-layout>
