<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate max-w-lg flex items-center gap-2"
                x-data x-text="$store.chat?.title || @js($currentConversation?->title ?? __('chat.title_new'))">
            </h2>
            <a href="{{ route('chat.index') }}"
               class="inline-flex items-center gap-2 text-sm px-3 py-1.5 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-plus text-xs"></i>
                {{ __('common.new_chat') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- ============================================================ --}}
            {{-- SIDEBAR                                                      --}}
            {{-- ============================================================ --}}
            <aside class="lg:col-span-1 bg-white shadow rounded-lg p-4" x-data="{ q: '' }">
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-comments text-gray-500"></i>
                    {{ __('chat.sidebar_heading') }}
                </h3>

                <input type="text" x-model="q"
                       placeholder="{{ __('chat.sidebar_search') }}"
                       class="w-full mb-3 border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500">

                <ul class="space-y-1 max-h-[60vh] overflow-y-auto">
                    @forelse ($conversations as $c)
                        <li x-show="q === '' || '{{ str_replace("'", "\\'", strtolower($c->title)) }}'.includes(q.toLowerCase())">
                            <a href="{{ route('chat.show', $c) }}"
                               class="block px-3 py-2 rounded text-sm truncate transition {{ $currentConversation && $currentConversation->id === $c->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'hover:bg-gray-100 text-gray-700' }}">
                                <i class="fa-regular fa-comment text-[10px] mr-1 opacity-60"></i>
                                {{ $c->title }}
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 px-3 py-2">{{ __('chat.sidebar_empty') }}</li>
                    @endforelse
                </ul>

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('conversations.index') }}" class="text-sm text-indigo-600 hover:underline">
                        {{ __('common.manage_all') }} →
                    </a>
                </div>
            </aside>

            {{-- ============================================================ --}}
            {{-- CHAT PANEL                                                   --}}
            {{-- ============================================================ --}}
            <section class="lg:col-span-3 bg-white shadow rounded-lg flex flex-col"
                     style="min-height: 75vh;"
                     x-data="chatPanel(@js([
                        'conversationId' => $currentConversation?->id,
                        'sendUrl'        => $currentConversation
                                                ? route('chat.send.existing', $currentConversation)
                                                : route('chat.send'),
                        'regenerateUrlTpl' => url('/chat/__ID__/regenerate'),
                        'showRoute'      => url('/chat/__ID__'),
                     ]))">

                {{-- Messages --}}
                <div class="flex-1 p-6 overflow-y-auto space-y-6" id="messages" x-ref="messages">

                    {{-- Server-rendered messages on first load --}}
                    @forelse ($messages as $m)
                        @include('chat._message', ['m' => $m, 'isLast' => $loop->last])
                    @empty
                        <div x-show="!messages.length" class="text-center text-gray-500 mt-12 px-4">
                            <div class="text-5xl mb-4">✨</div>
                            <p class="text-xl font-semibold text-gray-700">{{ __('chat.welcome_hero') }}</p>
                            <p class="text-sm mt-2 mb-8">{{ __('chat.welcome_subtitle') }}</p>

                            <p class="text-xs uppercase tracking-wide text-gray-400 mb-3">{{ __('chat.welcome_suggestions') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto">
                                @foreach (['chat.suggestion_1','chat.suggestion_2','chat.suggestion_3','chat.suggestion_4'] as $key)
                                    <button type="button"
                                            @click="useSuggestion(@js(__($key)))"
                                            class="text-left p-4 bg-gray-50 hover:bg-indigo-50 hover:border-indigo-300 border border-gray-200 rounded-lg text-sm text-gray-700 transition">
                                        {{ __($key) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforelse

                    {{-- AJAX-appended messages --}}
                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex group" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-2xl w-full">
                                <div class="flex items-center gap-2 mb-1" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                                    <span class="text-xs font-semibold"
                                          :class="msg.role === 'user' ? 'text-indigo-600' : 'text-gray-600'"
                                          x-text="msg.role === 'user' ? '{{ __('chat.role_user') }}' : '{{ __('chat.role_assistant') }}'"></span>
                                    <span class="text-[10px] text-gray-400 px-1.5 py-0.5 bg-gray-100 rounded"
                                          x-show="msg.role === 'assistant' && msg.model" x-text="msg.model"></span>
                                </div>
                                <div class="px-4 py-3 rounded-lg text-sm leading-relaxed"
                                     :class="msg.role === 'user' ? 'bg-indigo-600 text-white ml-auto whitespace-pre-wrap' : 'bg-gray-100 text-gray-800'">
                                    <template x-if="msg.role === 'assistant'">
                                        <div class="prose-chat" x-html="renderMarkdown(msg.content)"></div>
                                    </template>
                                    <template x-if="msg.role === 'user'">
                                        <div x-text="msg.content"></div>
                                    </template>
                                    <div class="text-[10px] opacity-60 mt-2"
                                         x-show="msg.role === 'assistant' && msg.tokens"
                                         x-text="msg.tokens + ' tokens'"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Thinking indicator --}}
                    <div x-show="thinking" x-cloak class="flex justify-start">
                        <div class="bg-gray-100 px-4 py-3 rounded-lg inline-flex items-center gap-2 text-sm text-gray-600">
                            <span class="flex gap-1">
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0s"></span>
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0.15s"></span>
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0.3s"></span>
                            </span>
                            {{ __('chat.composer_thinking') }}
                        </div>
                    </div>
                </div>

                {{-- Composer --}}
                <form @submit.prevent="sendMessage($event)"
                      class="border-t p-4 bg-gray-50 rounded-b-lg"
                      x-ref="composer"
                      novalidate>
                    @csrf

                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <label class="text-xs text-gray-500 inline-flex items-center gap-1">
                            <i class="fa-solid fa-microchip text-indigo-500"></i>
                            {{ __('chat.composer_model') }}:
                        </label>
                        <select x-model="selectedModel"
                                class="border-gray-300 rounded-md text-xs py-1 max-w-xs">
                            @foreach ($modelGroups as $family => $modelsInGroup)
                                <optgroup label="{{ $family }}">
                                    @foreach ($modelsInGroup as $k => $label)
                                        <option value="{{ $k }}" {{ $defaultModel === $k ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <a href="{{ route('tools.index') }}"
                           class="text-[11px] text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1 ml-2">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            {{ __('chat.tools_open') }}
                        </a>

                        <span class="text-[11px] text-gray-400 ml-auto" x-show="!clientError && !serverError" x-cloak>
                            {{ __('chat.composer_hint_shortcut') }}
                        </span>
                        <span class="text-[11px] text-red-600 ml-auto font-medium" x-show="clientError" x-text="clientError" x-cloak></span>
                        <span class="text-[11px] text-red-600 ml-auto font-medium" x-show="serverError" x-cloak>
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span x-text="serverError"></span>
                        </span>
                    </div>

                    <div class="flex gap-2 items-end">
                        <textarea x-ref="message"
                                  x-model="draft"
                                  rows="2"
                                  maxlength="8000"
                                  autofocus
                                  @input="clientError = ''; serverError = ''"
                                  @keydown.enter.prevent="if (!$event.shiftKey) { $refs.composer.requestSubmit(); } else { draft += '\n'; }"
                                  placeholder="{{ __('chat.composer_placeholder') }}"
                                  class="flex-1 border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"></textarea>

                        <button type="submit"
                                :disabled="thinking"
                                class="inline-flex items-center gap-1 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid" :class="thinking ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                            <span x-text="thinking ? '{{ __('common.loading') }}' : '{{ __('chat.composer_send') }}'"></span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

    {{-- ─────────────────────────────────────────────────────────────────────
         AJAX chat panel
         ───────────────────────────────────────────────────────────────────── --}}
    <script>
        const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const CHAT_JS   = @json(__('chat.js'));

        function chatPanel(opts) {
            return {
                conversationId : opts.conversationId,
                sendUrl        : opts.sendUrl,
                regenerateUrlTpl: opts.regenerateUrlTpl,
                showRoute      : opts.showRoute,

                draft         : '',
                thinking      : false,
                clientError   : '',
                serverError   : '',
                selectedModel : @json($defaultModel),
                messages      : [],    // AJAX-appended messages only

                useSuggestion(text) {
                    this.draft = text;
                    this.clientError = '';
                    this.$refs.message.focus();
                },

                async sendMessage(event) {
                    this.clientError = ''; this.serverError = '';
                    const val = (this.draft || '').trim();

                    if (!val) { this.clientError = CHAT_JS.message_required; return; }
                    if (val.length > 8000) { this.clientError = (CHAT_JS.message_too_long || '').replace(':max', '8000'); return; }

                    this.thinking = true;
                    const userText = val;
                    this.draft = '';

                    try {
                        const res = await fetch(this.sendUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ message: userText, model: this.selectedModel }),
                        });
                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data.ok) {
                            this.serverError = data.error || data.message || (CHAT_JS.send_failed_unknown || 'Error');
                            // Put the draft back so the user doesn't lose their message.
                            this.draft = userText;
                            this.thinking = false;
                            return;
                        }

                        // Append user + assistant messages
                        if (data.user_msg)  this.messages.push(data.user_msg);
                        if (data.assistant) this.messages.push(data.assistant);

                        // Update url + conversation id if this was a new chat
                        if (data.conversation && data.conversation.id) {
                            if (!this.conversationId) {
                                this.conversationId = data.conversation.id;
                                this.sendUrl = this.showRoute.replace('__ID__', this.conversationId) + '/send';
                                // Update the visible URL bar (no reload).
                                history.replaceState(null, '', this.showRoute.replace('__ID__', this.conversationId));
                            }
                            // Update the page header title.
                            document.title = (data.conversation.title || 'Chat') + ' · SelfAI';
                            const h = document.querySelector('h2.truncate');
                            if (h) h.textContent = data.conversation.title;
                        }
                    } catch (e) {
                        this.serverError = CHAT_JS.send_failed || 'Network error';
                        this.draft = userText;
                    } finally {
                        this.thinking = false;
                        this.$nextTick(() => {
                            if (this.$refs.messages) this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                            if (this.$refs.message)  this.$refs.message.focus();
                        });
                    }
                },

                copyText(text) {
                    if (navigator.clipboard) { navigator.clipboard.writeText(text); }
                    else {
                        const ta = document.createElement('textarea');
                        ta.value = text; document.body.appendChild(ta);
                        ta.select(); document.execCommand('copy'); ta.remove();
                    }
                },

                // ─── Minimal markdown renderer (same as before) ───
                renderMarkdown(src) {
                    if (typeof src !== 'string') return '';
                    let s = src.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    s = s.replace(/```([\s\S]*?)```/g, (_, code) =>
                        `<pre class="bg-gray-900 text-gray-100 rounded p-3 my-2 overflow-x-auto text-xs"><code>${code.trim()}</code></pre>`);
                    s = s.replace(/`([^`\n]+)`/g, '<code class="bg-gray-200 text-gray-900 px-1 py-0.5 rounded text-xs">$1</code>');
                    s = s.replace(/^### (.*)$/gm, '<h3 class="font-semibold text-base mt-3 mb-1">$1</h3>');
                    s = s.replace(/^## (.*)$/gm,  '<h2 class="font-semibold text-lg mt-3 mb-1">$1</h2>');
                    s = s.replace(/^# (.*)$/gm,   '<h1 class="font-bold text-xl mt-3 mb-1">$1</h1>');
                    s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                    s = s.replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>');
                    s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" class="text-indigo-600 underline">$1</a>');
                    s = s.replace(/(^|\n)- (.+)/g, '$1<li>$2</li>');
                    s = s.replace(/(<li>[\s\S]*?<\/li>)$/g, '<ul class="list-disc ml-5 my-2 space-y-1">$1</ul>');
                    s = s.replace(/\n{2,}/g, '<br><br>').replace(/\n/g, '<br>');
                    return s;
                },

                init() {
                    this.$nextTick(() => {
                        if (this.$refs.messages) {
                            this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                        }
                    });
                },
            }
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-app-layout>
