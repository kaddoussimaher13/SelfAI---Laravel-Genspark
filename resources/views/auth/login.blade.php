<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800 mb-4">{{ __('auth_ui.login_title') }}</h1>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div>
            <x-input-label for="email" :value="__('auth_ui.field_email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <p data-error-for="email" class="mt-1 text-xs text-red-600 hidden"></p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('auth_ui.field_password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <p data-error-for="password" class="mt-1 text-xs text-red-600 hidden"></p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('auth_ui.remember_me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('auth_ui.forgot_password') }}
                </a>
            @endif

            <x-primary-button class="ms-3">{{ __('auth_ui.login_button') }}</x-primary-button>
        </div>
    </form>

    <script>
        (function () {
            const MSG  = @json(__('auth_ui.js'));
            const form = document.getElementById('loginForm');

            function setError(name, msg) {
                const slot = form.querySelector('[data-error-for="' + name + '"]');
                if (!slot) return;
                if (msg) { slot.textContent = msg; slot.classList.remove('hidden'); }
                else     { slot.textContent = '';  slot.classList.add('hidden');   }
            }

            form.addEventListener('submit', function (e) {
                form.querySelectorAll('[data-error-for]').forEach(el => {
                    el.textContent = ''; el.classList.add('hidden');
                });
                let ok = true;

                const email = form.email.value.trim();
                if (!email)                                     { setError('email', MSG.email_required); ok = false; }
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setError('email', MSG.email_invalid); ok = false; }

                if (!form.password.value)                       { setError('password', MSG.password_required); ok = false; }

                if (!ok) e.preventDefault();
            });
        })();
    </script>
</x-guest-layout>
