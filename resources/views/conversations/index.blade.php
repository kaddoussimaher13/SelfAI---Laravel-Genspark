<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-comments text-indigo-600"></i>
            {{ __('chat.title_conversations') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="conversationsList()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">

                <template x-if="!convs.length">
                    <p class="text-gray-500">
                        <i class="fa-regular fa-comment text-gray-300 mr-1"></i>
                        {{ __('chat.conv_empty') }}
                        <a href="{{ route('chat.index') }}" class="text-indigo-600 underline">{{ __('chat.conv_start_one') }} →</a>
                    </p>
                </template>

                <template x-for="c in convs" :key="c.id">
                    <div class="flex flex-wrap items-center justify-between border-b py-3 gap-3"
                         x-data="{ editing: false }">
                        <a :href="'{{ url('/chat') }}/' + c.id" class="flex-1 min-w-0 hover:underline"
                           x-show="!editing">
                            <span class="font-medium block truncate" x-text="c.title"></span>
                            <span class="text-xs text-gray-500" x-text="c.subtitle"></span>
                        </a>

                        {{-- Rename form (inline) --}}
                        <form @submit.prevent="rename(c, $event)" class="flex gap-1 items-center" x-show="editing">
                            <input type="text" x-model="c.editTitle"
                                   maxlength="120"
                                   class="border-gray-300 rounded text-sm w-full sm:w-64">
                            <button type="submit"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded inline-flex items-center gap-1">
                                <i class="fa-solid fa-check"></i>
                                {{ __('common.save') }}
                            </button>
                            <button type="button" @click="editing = false; c.editTitle = c.title"
                                    class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs rounded">
                                {{ __('common.cancel') }}
                            </button>
                        </form>

                        <div class="flex gap-1" x-show="!editing">
                            <button type="button" @click="editing = true; c.editTitle = c.title"
                                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded inline-flex items-center gap-1">
                                <i class="fa-solid fa-pen"></i>
                                {{ __('common.rename') }}
                            </button>
                            <button type="button" @click="del(c)"
                                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs rounded inline-flex items-center gap-1">
                                <i class="fa-solid fa-trash"></i>
                                {{ __('common.delete') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="toast" x-cloak x-transition.opacity
                 class="fixed bottom-6 right-6 max-w-sm px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50"
                 :class="toastType === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'"
                 x-text="toast"></div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

    <script>
        const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const CHAT_JS = @json(__('chat.js'));

        function conversationsList() {
            return {
                convs: @json($conversations->map(fn ($c) => [
                    'id'        => $c->id,
                    'title'     => $c->title,
                    'editTitle' => $c->title,
                    'subtitle'  => __('chat.conv_count_messages', ['count' => $c->messages_count])
                                   . ' · ' . __('chat.conv_updated_ago', ['time' => $c->updated_at->diffForHumans()]),
                ])->values()),
                toast: '',
                toastType: 'success',

                showToast(msg, type = 'success') {
                    this.toast = msg; this.toastType = type;
                    setTimeout(() => this.toast = '', 3500);
                },

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
                    return { ok: res.ok, data: await res.json().catch(() => ({})) };
                },

                async rename(c, event) {
                    const title = (c.editTitle || '').trim();
                    if (!title) { this.showToast(CHAT_JS.title_required, 'error'); return; }
                    if (title.length > 120) { this.showToast(CHAT_JS.title_too_long, 'error'); return; }

                    const r = await this.req('{{ url('/conversations') }}/' + c.id, { title }, 'PATCH');
                    if (r.ok && r.data.ok) {
                        c.title = r.data.title;
                        event.target.closest('[x-data]').__x.$data.editing = false;
                        this.showToast(CHAT_JS.renamed || '✓');
                    } else {
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },

                async del(c) {
                    if (!confirm(CHAT_JS.confirm_delete_conv)) return;
                    const r = await this.req('{{ url('/conversations') }}/' + c.id, null, 'DELETE');
                    if (r.ok && r.data.ok) {
                        this.convs = this.convs.filter(x => x.id !== c.id);
                        this.showToast(CHAT_JS.deleted || '✓');
                    } else {
                        this.showToast(r.data.error || 'Error', 'error');
                    }
                },
            }
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-app-layout>
