{{-- Single message bubble — server-rendered for the initial page load.
     AJAX-appended messages use the inline x-for template inside chat/index. --}}
<div class="flex {{ $m->role === 'user' ? 'justify-end' : 'justify-start' }} group"
     x-data="{ editing: false, copied: false }">
    <div class="max-w-2xl w-full">
        <div class="flex items-center gap-2 mb-1 {{ $m->role === 'user' ? 'justify-end' : 'justify-start' }}">
            <span class="text-xs font-semibold {{ $m->role === 'user' ? 'text-indigo-600' : 'text-gray-600' }}">
                {{ $m->role === 'user' ? __('chat.role_user') : __('chat.role_assistant') }}
            </span>
            @if ($m->role === 'assistant' && $m->model)
                <span class="text-[10px] text-gray-400 px-1.5 py-0.5 bg-gray-100 rounded">{{ $m->model }}</span>
            @endif
        </div>

        <div class="px-4 py-3 rounded-lg text-sm leading-relaxed
                    {{ $m->role === 'user'
                        ? 'bg-indigo-600 text-white ml-auto whitespace-pre-wrap'
                        : 'bg-gray-100 text-gray-800' }}">

            <div x-show="!editing">
                @if ($m->role === 'assistant')
                    <div class="prose-chat" x-html="renderMarkdown(@js($m->content))"></div>
                @else
                    {{ $m->content }}
                @endif

                @if ($m->role === 'assistant' && $m->tokens)
                    <div class="text-[10px] opacity-60 mt-2">{{ __('chat.msg_tokens', ['count' => $m->tokens]) }}</div>
                @endif
            </div>

            @if ($m->role === 'user' && isset($currentConversation))
                <form x-show="editing" x-cloak
                      method="POST"
                      action="{{ route('chat.message.edit', [$currentConversation, $m]) }}"
                      class="space-y-2">
                    @csrf @method('PATCH')
                    <textarea name="content" rows="3" required
                              class="w-full bg-white text-gray-800 border-0 rounded p-2 text-sm focus:ring-2 focus:ring-indigo-300">{{ $m->content }}</textarea>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="editing = false"
                                class="text-xs px-3 py-1 bg-white text-gray-700 rounded">{{ __('common.cancel') }}</button>
                        <button type="submit"
                                class="text-xs px-3 py-1 bg-white text-indigo-600 font-semibold rounded">{{ __('common.save') }}</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="flex gap-1 mt-1.5 opacity-0 group-hover:opacity-100 transition
                    {{ $m->role === 'user' ? 'justify-end' : 'justify-start' }}">
            <button type="button"
                    @click="copyText(@js($m->content)); copied = true; setTimeout(() => copied = false, 1500)"
                    class="text-[11px] text-gray-500 hover:text-gray-800 px-2 py-1 rounded hover:bg-gray-100 inline-flex items-center gap-1">
                <i class="fa-regular fa-copy text-[10px]"></i>
                <span x-text="copied ? '{{ __('chat.msg_copied') }}' : '{{ __('chat.msg_copy') }}'"></span>
            </button>

            @if ($m->role === 'user' && isset($currentConversation))
                <button type="button" @click="editing = true"
                        class="text-[11px] text-gray-500 hover:text-gray-800 px-2 py-1 rounded hover:bg-gray-100 inline-flex items-center gap-1">
                    <i class="fa-solid fa-pen text-[10px]"></i>
                    {{ __('chat.msg_edit') }}
                </button>
            @endif

            @if ($m->role === 'assistant' && isset($isLast) && $isLast && isset($currentConversation) && $currentConversation)
                <form method="POST" action="{{ route('chat.regenerate', $currentConversation) }}">
                    @csrf
                    <button type="submit"
                            class="text-[11px] text-gray-500 hover:text-gray-800 px-2 py-1 rounded hover:bg-gray-100 inline-flex items-center gap-1">
                        <i class="fa-solid fa-rotate text-[10px]"></i>
                        {{ __('chat.msg_regenerate') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
