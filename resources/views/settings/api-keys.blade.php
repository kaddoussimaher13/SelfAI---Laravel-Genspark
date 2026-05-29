<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="fa-solid fa-key text-indigo-600"></i>
                {{ __('settings.api_keys_title') }}
            </h2>
            <a href="{{ route('settings.edit') }}"
               class="text-sm text-gray-500 hover:text-gray-800 inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                {{ __('settings.tab_general') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="apiKeysPanel()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Intro card --}}
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-600">{{ __('settings.api_keys_subtitle') }}</p>

                {{-- Auto-failover toggle --}}
                <div class="mt-4 flex items-center gap-3 p-3 bg-indigo-50 border border-indigo-200 rounded-md">
                    <i class="fa-solid fa-shuffle text-indigo-600 text-lg"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ __('settings.auto_failover') }}</p>
                        <p class="text-xs text-gray-600">{{ __('settings.auto_failover_help') }}</p>
                    </div>
                    <form id="failoverForm" method="POST" action="{{ route('settings.update') }}" class="inline">
                        @csrf @method('PATCH')
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_failover" value="1"
                                   onchange="this.form.submit()"
                                   @if($user->auto_failover) checked @endif
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </form>
                </div>
            </div>

            {{-- Add new key form --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-indigo-600"></i>
                    {{ __('settings.api_keys_add') }}
                </h3>
                <p class="text-xs text-gray-500 mb-4">{{ __('settings.api_keys_validate') }}</p>

                <form @submit.prevent="addKey($event)" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.api_key_label_field') }}</label>
                        <input type="text"
                               x-model="newKey.label"
                               maxlength="80"
                               placeholder="{{ __('settings.api_key_label_placeholder') }}"
                               class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.api_key_field') }}</label>
                        <div class="flex gap-2">
                            <input :type="showNew ? 'text' : 'password'"
                                   x-model="newKey.key"
                                   autocomplete="off"
                                   pattern="^gsk[-_].+"
                                   minlength="20"
                                   maxlength="4000"
                                   placeholder="gsk-…"
                                   class="flex-1 border-gray-300 rounded-md text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" @click="showNew = !showNew"
                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs">
                                <i class="fa-solid" :class="showNew ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                            <button type="submit"
                                    :disabled="busy"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md inline-flex items-center gap-1 disabled:opacity-50">
                                <i class="fa-solid fa-check"></i>
                                <span x-text="busy ? jsMsg.saving : '{{ __('settings.api_keys_add') }}'"></span>
                            </button>
                        </div>
                    </div>
                </form>

                <p x-show="formError" x-text="formError" x-cloak
                   class="mt-3 p-3 bg-red-50 border border-red-300 text-red-800 text-xs font-mono rounded break-words"></p>
                <p x-show="formSuccess" x-text="formSuccess" x-cloak
                   class="mt-3 p-3 bg-green-50 border border-green-300 text-green-800 text-xs rounded"></p>
            </div>

            {{-- Existing keys list --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <template x-if="keys.length === 0">
                    <div class="p-8 text-center text-gray-500 text-sm">
                        <i class="fa-solid fa-key text-4xl text-gray-300 mb-3"></i>
                        <p>{{ __('settings.api_keys_empty') }}</p>
                    </div>
                </template>

                <ul class="divide-y divide-gray-100">
                    <template x-for="k in keys" :key="k.id">
                        <li class="p-4 hover:bg-gray-50 transition">
                            <div class="flex flex-wrap items-center gap-3">

                                {{-- Status icon --}}
                                <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                                     :class="k.is_primary ? 'bg-yellow-100 text-yellow-700' : (k.usable ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400')">
                                    <i class="fa-solid" :class="k.is_primary ? 'fa-star' : (k.usable ? 'fa-check' : 'fa-pause')"></i>
                                </div>

                                {{-- Label + preview --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-gray-900 text-sm" x-text="k.label"></span>

                                        <template x-if="k.is_primary">
                                            <span class="text-[10px] uppercase font-semibold text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded">
                                                <i class="fa-solid fa-star"></i> {{ __('settings.api_key_primary') }}
                                            </span>
                                        </template>

                                        <template x-if="!k.is_active">
                                            <span class="text-[10px] uppercase font-semibold text-gray-700 bg-gray-200 px-2 py-0.5 rounded">
                                                <i class="fa-solid fa-pause"></i> {{ __('settings.api_key_inactive') }}
                                            </span>
                                        </template>

                                        <template x-if="k.is_active && k.disabled_until">
                                            <span class="text-[10px] uppercase font-semibold text-orange-700 bg-orange-100 px-2 py-0.5 rounded">
                                                <i class="fa-solid fa-clock"></i>
                                                <span>cool-down</span>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-3 items-center">
                                        <span class="font-mono" x-text="k.preview"></span>
                                        <template x-if="k.account_email">
                                            <span><i class="fa-solid fa-user text-[10px] mr-1"></i><span x-text="k.account_email"></span></span>
                                        </template>
                                        <template x-if="k.plan">
                                            <span class="capitalize"><i class="fa-solid fa-tag text-[10px] mr-1"></i><span x-text="k.plan"></span></span>
                                        </template>
                                        <template x-if="k.last_used_at">
                                            <span><i class="fa-regular fa-clock text-[10px] mr-1"></i><span x-text="k.last_used_at"></span></span>
                                        </template>
                                    </div>
                                    <p x-show="k.last_error" x-text="k.last_error" x-cloak
                                       class="text-[11px] text-red-600 mt-1 break-words"></p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex flex-wrap gap-1">
                                    <button type="button" @click="testKey(k)"
                                            class="px-2 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded inline-flex items-center gap-1"
                                            title="{{ __('settings.api_key_test') }}">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span class="hidden sm:inline">{{ __('settings.api_key_test') }}</span>
                                    </button>

                                    <template x-if="!k.is_primary">
                                        <button type="button" @click="makePrimary(k)"
                                                :disabled="!k.is_active"
                                                class="px-2 py-1.5 text-xs bg-yellow-50 hover:bg-yellow-100 text-yellow-800 rounded inline-flex items-center gap-1 disabled:opacity-40"
                                                title="{{ __('settings.api_key_make_primary') }}">
                                            <i class="fa-solid fa-star"></i>
                                            <span class="hidden sm:inline">{{ __('settings.api_key_make_primary') }}</span>
                                        </button>
                                    </template>

                                    <button type="button" @click="toggleActive(k)"
                                            class="px-2 py-1.5 text-xs rounded inline-flex items-center gap-1"
                                            :class="k.is_active ? 'bg-orange-50 hover:bg-orange-100 text-orange-800' : 'bg-green-50 hover:bg-green-100 text-green-800'">
                                        <i class="fa-solid" :class="k.is_active ? 'fa-pause' : 'fa-play'"></i>
                                        <span class="hidden sm:inline" x-text="k.is_active ? '{{ __('settings.api_key_disable') }}' : '{{ __('settings.api_key_enable') }}'"></span>
                                    </button>

                                    <button type="button" @click="deleteKey(k)"
                                            class="px-2 py-1.5 text-xs bg-red-50 hover:bg-red-100 text-red-800 rounded inline-flex items-center gap-1">
                                        <i class="fa-solid fa-trash"></i>
                                        <span class="hidden sm:inline">{{ __('settings.api_key_delete') }}</span>
                                    </button>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Toast --}}
            <div x-show="toast" x-cloak x-transition.opacity
                 class="fixed bottom-6 right-6 max-w-sm px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50"
                 :class="toastType === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'"
                 x-text="toast">
            </div>
        </div>
    </div>

    {{-- ─── FontAwesome (icons used throughout the UI) ─── --}}
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
                   || '{{ csrf_token() }}';
        const SETTINGS_JS = @json(__('settings.js'));

        function apiKeysPanel() {
            return {
                keys: @json($keys->map(fn ($k) => [
                    'id'             => $k->id,
                    'label'          => $k->label,
                    'preview'        => $k->preview(),
                    'plan'           => $k->plan,
                    'account_email'  => $k->account_email,
                    'is_primary'     => (bool) $k->is_primary,
                    'is_active'      => (bool) $k->is_active,
                    'last_used_at'   => $k->last_used_at?->diffForHumans(),
                    'disabled_until' => $k->disabled_until?->toIso8601String(),
                    'usable'         => $k->isUsable(),
                    'last_error'     => $k->last_error,
                ])->values()),
                newKey: { label: '', key: '' },
                showNew: false,
                busy: false,
                formError: '',
                formSuccess: '',
                toast: '',
                toastType: 'success',
                jsMsg: SETTINGS_JS,

                async post(url, body = null, method = 'POST') {
                    const opts = {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    };
                    if (body) opts.body = JSON.stringify(body);
                    const res  = await fetch(url, opts);
                    const data = await res.json().catch(() => ({}));
                    return { ok: res.ok, status: res.status, data };
                },

                showToast(msg, type = 'success') {
                    this.toast = msg; this.toastType = type;
                    setTimeout(() => this.toast = '', 3500);
                },

                async addKey(event) {
                    this.formError = ''; this.formSuccess = '';
                    const k = (this.newKey.key || '').trim();
                    if (!k) { this.formError = this.jsMsg.api_key_required_onboarding; return; }
                    if (!/^gsk[-_].{18,}/.test(k)) { this.formError = this.jsMsg.api_key_bad_format; return; }

                    this.busy = true;
                    const r = await this.post('{{ route('apikeys.store') }}', {
                        label: this.newKey.label, key: k
                    });
                    this.busy = false;

                    if (r.ok && r.data.ok) {
                        this.keys.push(r.data.key);
                        this.formSuccess = (r.data.message || '✓') + (r.data.account ? '  ·  ' + r.data.account : '');
                        this.newKey = { label: '', key: '' };
                        this.showToast(r.data.message || '✓');
                    } else {
                        this.formError = r.data.error || ('HTTP ' + r.status);
                    }
                },

                async makePrimary(k) {
                    const r = await this.post('{{ url('/settings/api-keys') }}/' + k.id + '/primary');
                    if (r.ok && r.data.ok) {
                        this.keys.forEach(x => x.is_primary = false);
                        k.is_primary = true;
                        this.showToast(r.data.message);
                    } else {
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },

                async toggleActive(k) {
                    const r = await this.post('{{ url('/settings/api-keys') }}/' + k.id + '/toggle');
                    if (r.ok && r.data.ok) {
                        Object.assign(k, r.data.key);
                    } else {
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },

                async testKey(k) {
                    const r = await this.post('{{ url('/settings/api-keys') }}/' + k.id + '/test');
                    if (r.ok && r.data.ok) {
                        Object.assign(k, r.data.key);
                        this.showToast(r.data.message + (r.data.account ? '  ·  ' + r.data.account : ''));
                    } else {
                        if (r.data.key) Object.assign(k, r.data.key);
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },

                async deleteKey(k) {
                    if (!confirm(this.jsMsg.confirm_delete_key)) return;
                    const r = await this.post('{{ url('/settings/api-keys') }}/' + k.id, null, 'DELETE');
                    if (r.ok && r.data.ok) {
                        this.keys = this.keys.filter(x => x.id !== k.id);
                        this.showToast(r.data.message);
                    } else {
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },
            }
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-app-layout>
