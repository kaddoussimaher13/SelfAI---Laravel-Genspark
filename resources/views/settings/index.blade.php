<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-sliders text-indigo-600"></i>
            @if ($isOnboarding)
                {{ __('settings.onboarding_title') }}
            @else
                {{ __('settings.page_title') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Onboarding banner --}}
            @if ($isOnboarding)
                <div class="p-5 bg-indigo-50 border-l-4 border-indigo-500 rounded-r-lg">
                    <p class="text-sm text-indigo-900 leading-relaxed">{{ __('settings.onboarding_intro') }}</p>
                </div>
            @endif

            {{-- Success flash --}}
            @if (session('status') === 'settings-updated')
                <div class="p-3 bg-green-50 border border-green-200 rounded text-green-800 text-sm">
                    {{ __('common.flash_settings_updated') }}
                    @if (session('genspark_account'))
                        <div class="mt-1 font-mono text-xs text-green-700">
                            {{ __('settings.api_account_connected', ['account' => session('genspark_account')]) }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Quick-link cards (only if NOT onboarding) --}}
            @if (! $isOnboarding)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ route('apikeys.index') }}"
                       class="bg-white border-2 border-transparent hover:border-indigo-300 shadow rounded-lg p-4 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-800">{{ __('settings.tab_keys') }}</div>
                                <div class="text-xs text-gray-500">{{ $apiKeys->count() }} {{ str($apiKeys->count() === 1 ? 'key' : 'keys') }}</div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('projects.index') }}"
                       class="bg-white border-2 border-transparent hover:border-indigo-300 shadow rounded-lg p-4 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fa-solid fa-folder-tree"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-800">{{ __('settings.tab_projects') }}</div>
                                <div class="text-xs text-gray-500">{{ $projects->count() }} {{ str($projects->count() === 1 ? 'project' : 'projects') }}</div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('stats.index') }}"
                       class="bg-white border-2 border-transparent hover:border-indigo-300 shadow rounded-lg p-4 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-800">{{ __('common.nav_stats') }}</div>
                                <div class="text-xs text-gray-500">Daily charts</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- Detailed API key rejection --}}
            @if ($errors->has('genspark_api_key'))
                <div class="p-3 bg-red-50 border border-red-300 rounded">
                    <p class="text-sm text-red-800 font-medium">{{ __('settings.api_key') }}</p>
                    <p class="mt-1 text-xs text-red-700 font-mono break-all">{{ $errors->first('genspark_api_key') }}</p>
                </div>
            @endif

            {{-- ============ Settings form ============ --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                @if (! $isOnboarding)
                    <p class="text-sm text-gray-600 mb-6">{{ __('settings.page_description') }}</p>
                @endif

                <form id="settingsForm" method="POST" action="{{ route('settings.update') }}" class="space-y-6" novalidate>
                    @csrf @method('PATCH')

                    {{-- ===== Onboarding: legacy single-key input ===== --}}
                    @if ($isOnboarding)
                        <div class="p-4 border-2 border-indigo-300 bg-indigo-50 rounded-lg">
                            <x-input-label for="genspark_api_key">
                                <i class="fa-solid fa-key text-indigo-600 mr-1"></i>
                                {{ __('settings.api_key') }}
                            </x-input-label>

                            <input type="password"
                                   id="genspark_api_key"
                                   name="genspark_api_key"
                                   autocomplete="off"
                                   pattern="^gsk[-_].+"
                                   minlength="20"
                                   maxlength="4000"
                                   required
                                   placeholder="{{ __('settings.api_key_placeholder') }}"
                                   class="mt-2 block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            <p data-error-for="genspark_api_key" class="mt-1 text-xs text-red-600 hidden"></p>

                            <p class="text-xs text-gray-500 mt-2">{{ __('settings.api_key_help') }}</p>
                            <p class="text-xs text-indigo-600 mt-1">
                                <a href="https://www.genspark.ai" target="_blank" rel="noopener" class="underline">
                                    {{ __('settings.onboarding_get_key') }}
                                </a>
                            </p>
                        </div>
                    @endif

                    {{-- ===== Auto-failover toggle (always visible after onboarding) ===== --}}
                    @if (! $isOnboarding)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <i class="fa-solid fa-shuffle text-indigo-600 text-lg"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ __('settings.auto_failover') }}</p>
                                <p class="text-xs text-gray-600">{{ __('settings.auto_failover_help') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="auto_failover" value="1"
                                       @if($user->auto_failover) checked @endif
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    @endif

                    {{-- ===== Default project select (icon-styled) ===== --}}
                    @if (! $isOnboarding)
                        <div>
                            <x-input-label for="default_project_id">
                                <i class="fa-solid fa-folder-tree text-violet-600 mr-1"></i>
                                {{ __('settings.project_id') }}
                            </x-input-label>

                            @if ($projects->count() > 0)
                                <div class="relative mt-1">
                                    <select id="default_project_id" name="default_project_id"
                                            class="block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 pl-10 text-sm">
                                        <option value="">— no default —</option>
                                        @foreach ($projects as $p)
                                            <option value="{{ $p->gsk_id }}" {{ ($user->default_project_id === $p->gsk_id || (! $user->default_project_id && $p->is_default)) ? 'selected' : '' }}>
                                                {{ $p->is_default ? '★ ' : '' }}{{ $p->label }} — {{ $p->gsk_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fa-solid fa-folder absolute left-3 top-1/2 -translate-y-1/2 text-violet-500 pointer-events-none"></i>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ __('settings.project_id_help') }}
                                    <a href="{{ route('projects.index') }}" class="text-indigo-600 underline">
                                        {{ __('settings.projects_add') }}
                                    </a>
                                </p>
                            @else
                                <div class="mt-1 p-3 bg-violet-50 border border-violet-200 rounded text-xs">
                                    <p class="text-violet-900">{{ __('settings.projects_empty') }}</p>
                                    <a href="{{ route('projects.index') }}" class="mt-1 inline-flex items-center gap-1 text-indigo-600 hover:underline">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                        {{ __('settings.projects_add') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ===== Preferred model (with family icon) ===== --}}
                    <div>
                        <x-input-label for="preferred_model">
                            <i class="fa-solid fa-microchip text-indigo-600 mr-1"></i>
                            {{ __('settings.preferred_model') }}
                        </x-input-label>
                        <div class="relative mt-1">
                            <select id="preferred_model" name="preferred_model"
                                    class="block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 pl-10 text-sm">
                                <option value="">{{ __('settings.use_default', ['model' => $defaultModel]) }}</option>
                                @foreach ($availableModels as $k => $label)
                                    <option value="{{ $k }}" {{ $user->preferred_model === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-microchip absolute left-3 top-1/2 -translate-y-1/2 text-indigo-500 pointer-events-none"></i>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('settings.preferred_model_help') }}</p>
                        <x-input-error :messages="$errors->get('preferred_model')" class="mt-2" />
                    </div>

                    {{-- ===== System prompt ===== --}}
                    <div>
                        <x-input-label for="system_prompt">
                            <i class="fa-solid fa-comment-dots text-indigo-600 mr-1"></i>
                            {{ __('settings.system_prompt_optional') }}
                        </x-input-label>
                        <textarea id="system_prompt" name="system_prompt" rows="5" maxlength="8000"
                                  class="mt-1 block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                  placeholder="{{ __('settings.system_prompt_placeholder') }}">{{ old('system_prompt', $user->system_prompt) }}</textarea>
                        <p data-error-for="system_prompt" class="mt-1 text-xs text-red-600 hidden"></p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('settings.system_prompt_help') }}</p>
                    </div>

                    {{-- ===== Temperature & Max tokens (side-by-side) ===== --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="temperature">
                                <i class="fa-solid fa-temperature-half text-orange-500 mr-1"></i>
                                {{ __('settings.temperature') }}
                            </x-input-label>
                            <input id="temperature" name="temperature" type="number" step="0.1" min="0" max="2"
                                   value="{{ old('temperature', $user->temperature) }}"
                                   placeholder="{{ $defaultTemperature }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
                            <p data-error-for="temperature" class="mt-1 text-xs text-red-600 hidden"></p>
                            <p class="text-xs text-gray-500 mt-1">{{ __('settings.temperature_help', ['default' => $defaultTemperature]) }}</p>
                        </div>

                        <div>
                            <x-input-label for="max_tokens">
                                <i class="fa-solid fa-arrows-left-right-to-line text-orange-500 mr-1"></i>
                                {{ __('settings.max_tokens') }}
                            </x-input-label>
                            <input id="max_tokens" name="max_tokens" type="number" step="1" min="64" max="8192"
                                   value="{{ old('max_tokens', $user->max_tokens) }}"
                                   placeholder="{{ $defaultMaxTokens }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
                            <p data-error-for="max_tokens" class="mt-1 text-xs text-red-600 hidden"></p>
                            <p class="text-xs text-gray-500 mt-1">{{ __('settings.max_tokens_help', ['default' => $defaultMaxTokens]) }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <x-primary-button id="settingsSubmit" class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            @if ($isOnboarding)
                                {{ __('settings.onboarding_continue') }}
                            @else
                                {{ __('settings.save_settings') }}
                            @endif
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

    {{-- Client-side validation (same logic as before) --}}
    <script>
        (function () {
            const MSG = @json(__('settings.js'));
            const form = document.getElementById('settingsForm');
            const isOnboarding = {{ $isOnboarding ? 'true' : 'false' }};
            if (!form) return;

            function setError(name, msg) {
                const slot = form.querySelector('[data-error-for="' + name + '"]');
                if (!slot) return;
                if (msg) { slot.textContent = msg; slot.classList.remove('hidden'); }
                else     { slot.textContent = '';  slot.classList.add('hidden');   }
            }
            function clearAll() {
                form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent=''; el.classList.add('hidden'); });
            }

            form.addEventListener('submit', function (e) {
                clearAll();
                let ok = true;

                if (isOnboarding && form.genspark_api_key) {
                    const apiKey = form.genspark_api_key.value.trim();
                    if (!apiKey) { setError('genspark_api_key', MSG.api_key_required_onboarding); ok = false; }
                    else if (!/^gsk[-_].{18,}/.test(apiKey)) { setError('genspark_api_key', MSG.api_key_bad_format); ok = false; }
                }

                const temp = form.temperature?.value;
                if (temp && temp !== '' && (isNaN(+temp) || +temp < 0 || +temp > 2)) {
                    setError('temperature', MSG.temperature_range); ok = false;
                }

                const tokens = form.max_tokens?.value;
                if (tokens && tokens !== '' && (isNaN(+tokens) || +tokens < 64 || +tokens > 8192)) {
                    setError('max_tokens', MSG.max_tokens_range); ok = false;
                }

                const prompt = form.system_prompt?.value || '';
                if (prompt.length > 8000) { setError('system_prompt', MSG.system_prompt_too_long); ok = false; }

                if (!ok) {
                    e.preventDefault();
                    const first = form.querySelector('[data-error-for]:not(.hidden)');
                    if (first) first.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            });
        })();
    </script>
</x-app-layout>
