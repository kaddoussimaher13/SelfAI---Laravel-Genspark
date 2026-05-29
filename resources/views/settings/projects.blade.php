<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="fa-solid fa-folder-tree text-indigo-600"></i>
                {{ __('settings.projects_title') }}
            </h2>
            <a href="{{ route('settings.edit') }}"
               class="text-sm text-gray-500 hover:text-gray-800 inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                {{ __('settings.tab_general') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="projectsPanel()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Intro --}}
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-600">{{ __('settings.projects_subtitle') }}</p>
                <p class="text-xs text-indigo-700 mt-2">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    {{ __('settings.project_help_external') }}
                    <a href="https://www.genspark.ai" target="_blank" rel="noopener" class="underline">genspark.ai</a>
                </p>
            </div>

            {{-- Add new project --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-indigo-600"></i>
                    {{ __('settings.projects_add') }}
                </h3>

                <form @submit.prevent="addProject($event)" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.project_id') }}</label>
                        <input type="text"
                               x-model="newProject.gsk_id"
                               maxlength="128"
                               required
                               placeholder="{{ __('settings.project_id_placeholder') }}"
                               class="w-full border-gray-300 rounded-md text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.project_label_field') }}</label>
                        <input type="text"
                               x-model="newProject.label"
                               maxlength="80"
                               placeholder="{{ __('settings.project_label_placeholder') }}"
                               class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                :disabled="busy"
                                class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md inline-flex items-center justify-center gap-1 disabled:opacity-50">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="busy ? jsMsg.saving : '{{ __('settings.projects_add') }}'"></span>
                        </button>
                    </div>
                </form>

                <p x-show="formError" x-text="formError" x-cloak
                   class="mt-3 p-3 bg-red-50 border border-red-300 text-red-800 text-xs rounded"></p>
            </div>

            {{-- List --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <template x-if="projects.length === 0">
                    <div class="p-8 text-center text-gray-500 text-sm">
                        <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-3"></i>
                        <p>{{ __('settings.projects_empty') }}</p>
                    </div>
                </template>

                <ul class="divide-y divide-gray-100">
                    <template x-for="p in projects" :key="p.id">
                        <li class="p-4 hover:bg-gray-50 transition flex items-center gap-3 flex-wrap">
                            <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                                 :class="p.is_default ? 'bg-yellow-100 text-yellow-700' : 'bg-indigo-100 text-indigo-700'">
                                <i class="fa-solid" :class="p.is_default ? 'fa-star' : 'fa-folder'"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-gray-900 text-sm" x-text="p.label"></span>
                                    <template x-if="p.is_default">
                                        <span class="text-[10px] uppercase font-semibold text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded">
                                            <i class="fa-solid fa-star"></i> {{ __('settings.project_default') }}
                                        </span>
                                    </template>
                                </div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5" x-text="p.gsk_id"></div>
                            </div>

                            <div class="flex gap-1">
                                <template x-if="!p.is_default">
                                    <button type="button" @click="makeDefault(p)"
                                            class="px-2 py-1.5 text-xs bg-yellow-50 hover:bg-yellow-100 text-yellow-800 rounded inline-flex items-center gap-1">
                                        <i class="fa-solid fa-star"></i>
                                        <span class="hidden sm:inline">{{ __('settings.project_make_default') }}</span>
                                    </button>
                                </template>
                                <button type="button" @click="deleteProject(p)"
                                        class="px-2 py-1.5 text-xs bg-red-50 hover:bg-red-100 text-red-800 rounded inline-flex items-center gap-1">
                                    <i class="fa-solid fa-trash"></i>
                                    <span class="hidden sm:inline">{{ __('common.delete') }}</span>
                                </button>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Toast --}}
            <div x-show="toast" x-cloak x-transition.opacity
                 class="fixed bottom-6 right-6 max-w-sm px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50"
                 :class="toastType === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'"
                 x-text="toast"></div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
                   || '{{ csrf_token() }}';
        const SETTINGS_JS = @json(__('settings.js'));

        function projectsPanel() {
            return {
                projects: @json($projects->map(fn ($p) => [
                    'id'         => $p->id,
                    'gsk_id'     => $p->gsk_id,
                    'label'      => $p->label,
                    'is_default' => (bool) $p->is_default,
                ])->values()),
                newProject: { gsk_id: '', label: '' },
                busy: false,
                formError: '',
                toast: '',
                toastType: 'success',
                jsMsg: SETTINGS_JS,

                async req(url, body = null, method = 'POST') {
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
                    const res = await fetch(url, opts);
                    return { ok: res.ok, status: res.status, data: await res.json().catch(() => ({})) };
                },

                showToast(msg, type = 'success') {
                    this.toast = msg; this.toastType = type;
                    setTimeout(() => this.toast = '', 3500);
                },

                async addProject() {
                    this.formError = '';
                    const id = (this.newProject.gsk_id || '').trim();
                    if (!id) { this.formError = this.jsMsg.project_id_required; return; }
                    if (!/^[A-Za-z0-9_\-:.]+$/.test(id)) { this.formError = this.jsMsg.project_id_bad_format; return; }

                    this.busy = true;
                    const r = await this.req('{{ route('projects.store') }}', this.newProject);
                    this.busy = false;

                    if (r.ok && r.data.ok) {
                        // first project becomes the default
                        if (r.data.project.is_default) this.projects.forEach(x => x.is_default = false);
                        this.projects.push(r.data.project);
                        this.newProject = { gsk_id: '', label: '' };
                        this.showToast(r.data.message);
                    } else {
                        this.formError = r.data.error || ('HTTP ' + r.status);
                    }
                },

                async makeDefault(p) {
                    const r = await this.req('{{ url('/settings/projects') }}/' + p.id + '/default');
                    if (r.ok && r.data.ok) {
                        this.projects.forEach(x => x.is_default = false);
                        p.is_default = true;
                        this.showToast(r.data.message);
                    } else {
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },

                async deleteProject(p) {
                    if (!confirm(this.jsMsg.confirm_delete_project)) return;
                    const r = await this.req('{{ url('/settings/projects') }}/' + p.id, null, 'DELETE');
                    if (r.ok && r.data.ok) {
                        const wasDefault = p.is_default;
                        this.projects = this.projects.filter(x => x.id !== p.id);
                        // promote first project as default if needed
                        if (wasDefault && this.projects.length) this.projects[0].is_default = true;
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
