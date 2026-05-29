<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800 mb-4">{{ __('auth_ui.reset_title') }}</h1>

    <form id="resetForm" method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('auth_ui.field_email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <p data-error-for="email" class="mt-1 text-xs text-red-600 hidden"></p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('auth_ui.field_password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" minlength="8" />
            <p data-error-for="password" class="mt-1 text-xs text-red-600 hidden"></p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('auth_ui.field_password_confirm')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8" />
            <p data-error-for="password_confirmation" class="mt-1 text-xs text-red-600 hidden"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('auth_ui.reset_button') }}</x-primary-button>
        </div>
    </form>

    <script>
        (function () {
            const MSG  = @json(__('auth_ui.js'));
            const form = document.getElementById('resetForm');

            function setError(name, msg) {
                const slot = form.querySelector('[data-error-for="' + name + '"]');
                if (!slot) return;
                if (msg) { slot.textContent = msg; slot.classList.remove('hidden'); }
                else     { slot.textContent = '';  slot.classList.add('hidden');   }
            }

            form.addEventListener('submit', function (e) {
                form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent=''; el.classList.add('hidden'); });
                let ok = true;
                const email = form.email.value.trim();
                if (!email)                                       { setError('email', MSG.email_required); ok = false; }
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setError('email', MSG.email_invalid); ok = false; }

                const pw = form.password.value;
                if (!pw)             { setError('password', MSG.password_required); ok = false; }
                else if (pw.length < 8) { setError('password', MSG.password_too_short); ok = false; }

                if (pw !== form.password_confirmation.value) { setError('password_confirmation', MSG.password_mismatch); ok = false; }

                if (!ok) e.preventDefault();
            });
        })();
    </script>
</x-guest-layout>
