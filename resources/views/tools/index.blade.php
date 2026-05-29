<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🧰 {{ __('chat.tools_title') }}</h2>
            <a href="{{ route('chat.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 underline">{{ __('chat.tools_back') }}</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="toolsPanel()" x-cloak>
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600 mb-4">{{ __('chat.tools_subtitle') }}</p>

            @unless($hasProjectId)
                <div class="mb-4 p-3 bg-amber-50 border border-amber-300 rounded text-amber-900 text-sm">
                    {{ __('chat.tools_no_project') }}
                    <a href="{{ route('settings.edit') }}" class="underline font-medium">{{ __('settings.project_id') }} →</a>
                </div>
            @endunless

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- ─────── LEFT: Tool picker ─────── --}}
                <div class="lg:col-span-1 bg-white rounded-lg shadow p-4 space-y-2 h-fit">
                    @foreach ($tools as $name => $t)
                        <button type="button"
                                @click="selectTool('{{ $name }}')"
                                :class="active === '{{ $name }}' ? 'bg-indigo-100 border-indigo-400 text-indigo-900' : 'border-gray-200 hover:bg-gray-50'"
                                class="w-full text-left p-3 border rounded transition">
                            <div class="font-medium text-sm">{{ $t['icon'] ?? '🔧' }} {{ $t['label'] }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $t['description'] }}</div>
                            @if (! empty($t['requires_project']))
                                <div class="text-[10px] text-amber-600 mt-1">⚠ requires Project ID</div>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- ─────── RIGHT: Tool form + result ─────── --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-lg shadow p-5">
                        <template x-if="active">
                            <div>
                                <h3 class="text-lg font-semibold mb-4 text-gray-800" x-text="forms[active]?.label"></h3>

                                {{-- ── Image generation ── --}}
                                <div x-show="active === 'image_generation'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_model') }}</label>
                                    <select x-model="args.model" class="w-full mb-3 border-gray-300 rounded-md text-sm">
                                        @foreach ($imageModels as $k => $label)
                                            <option value="{{ $k }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prompt</label>
                                    <textarea x-model="args.query" rows="3" maxlength="4000"
                                              class="w-full mb-3 border-gray-300 rounded-md text-sm"
                                              placeholder="A red panda riding a skateboard, neon city background"></textarea>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_aspect_ratio') }}</label>
                                            <select x-model="args.aspect_ratio" class="w-full border-gray-300 rounded-md text-sm">
                                                @foreach ($imageAspectRatios as $r) <option value="{{ $r }}">{{ $r }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_image_size') }}</label>
                                            <select x-model="args.image_size" class="w-full border-gray-300 rounded-md text-sm">
                                                @foreach ($imageSizes as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Video generation ── --}}
                                <div x-show="active === 'video_generation'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_model') }}</label>
                                    <select x-model="args.model" class="w-full mb-3 border-gray-300 rounded-md text-sm">
                                        @foreach ($videoModels as $k => $label)
                                            <option value="{{ $k }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prompt</label>
                                    <textarea x-model="args.query" rows="3" maxlength="4000" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="A timelapse of a flower blooming in a garden"></textarea>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_aspect_ratio') }}</label>
                                            <select x-model="args.aspect_ratio" class="w-full border-gray-300 rounded-md text-sm">
                                                @foreach ($videoAspectRatios as $r) <option value="{{ $r }}">{{ $r }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_duration') }}</label>
                                            <input type="number" x-model.number="args.duration" min="1" max="60" class="w-full border-gray-300 rounded-md text-sm">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Audio generation ── --}}
                                <div x-show="active === 'audio_generation'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('chat.tools_model') }}</label>
                                    <select x-model="args.model" class="w-full mb-3 border-gray-300 rounded-md text-sm">
                                        @foreach ($audioModels as $k => $label)
                                            <option value="{{ $k }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prompt / Script</label>
                                    <textarea x-model="args.query" rows="3" maxlength="4000" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="A calm female voice saying: Welcome to SelfAI"></textarea>
                                </div>

                                {{-- ── Web search ── --}}
                                <div x-show="active === 'web_search'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Query</label>
                                    <input type="text" x-model="args.q" maxlength="4000" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="latest Laravel 12 release notes">
                                </div>

                                {{-- ── Image search ── --}}
                                <div x-show="active === 'image_search'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Query</label>
                                    <input type="text" x-model="args.query" maxlength="4000" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="Eiffel tower at sunset">
                                </div>

                                {{-- ── Crawler ── --}}
                                <div x-show="active === 'crawler'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                                    <input type="url" x-model="args.url" maxlength="2048" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="https://en.wikipedia.org/wiki/Laravel">
                                </div>

                                {{-- ── Summarize document ── --}}
                                <div x-show="active === 'summarize_large_document'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Document URL</label>
                                    <input type="url" x-model="args.url" maxlength="2048" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="https://example.com/whitepaper.pdf">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Question</label>
                                    <input type="text" x-model="args.question" maxlength="1000" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="What are the key conclusions?">
                                </div>

                                {{-- ── Understand images ── --}}
                                <div x-show="active === 'understand_images'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Image URLs (one per line)</label>
                                    <textarea x-model="imageUrlsRaw" rows="3" class="w-full mb-3 border-gray-300 rounded-md text-sm font-mono text-xs" placeholder="https://example.com/cat.jpg"></textarea>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Instruction</label>
                                    <textarea x-model="args.instruction" rows="2" maxlength="4000" class="w-full mb-3 border-gray-300 rounded-md text-sm" placeholder="Describe what's in these images."></textarea>
                                </div>

                                {{-- ── Stock price ── --}}
                                <div x-show="active === 'stock_price'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Symbol</label>
                                    <input type="text" x-model="args.symbol" maxlength="16" class="w-full mb-3 border-gray-300 rounded-md text-sm font-mono uppercase" placeholder="AAPL">
                                </div>

                                {{-- Client-side validation error --}}
                                <p x-show="clientError" x-text="clientError" x-cloak class="text-sm text-red-600 mb-3"></p>

                                {{-- Run button --}}
                                <button type="button"
                                        @click="run()"
                                        :disabled="running"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                    <svg x-show="running" x-cloak class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" class="opacity-75"></path></svg>
                                    <span x-text="running ? '{{ __('chat.tools_running') }}' : '{{ __('chat.tools_run') }}'"></span>
                                </button>
                            </div>
                        </template>

                        <template x-if="!active">
                            <p class="text-sm text-gray-500 italic text-center py-12">← {{ __('chat.tools_open') }}</p>
                        </template>
                    </div>

                    {{-- ─── Result ─── --}}
                    <div x-show="result || error" x-cloak class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-800">{{ __('chat.tools_result_title') }}</h3>
                            <button type="button" @click="copyResult()" class="text-xs text-indigo-600 hover:text-indigo-800 underline">{{ __('chat.tools_copy_json') }}</button>
                        </div>

                        <p x-show="error" x-text="error" x-cloak class="text-sm text-red-700 font-mono whitespace-pre-wrap break-words p-3 bg-red-50 border border-red-200 rounded"></p>

                        <template x-if="result">
                            <div>
                                <template x-for="url in extractMediaUrls(result)" :key="url">
                                    <div class="mb-3">
                                        <template x-if="url.match(/\.(png|jpe?g|webp|gif)(\?|$)/i)">
                                            <img :src="url" class="max-w-full rounded shadow border" loading="lazy">
                                        </template>
                                        <template x-if="url.match(/\.(mp4|webm|mov)(\?|$)/i)">
                                            <video :src="url" controls class="max-w-full rounded shadow border"></video>
                                        </template>
                                        <template x-if="url.match(/\.(mp3|wav|ogg|m4a)(\?|$)/i)">
                                            <audio :src="url" controls class="w-full"></audio>
                                        </template>
                                        <a :href="url" target="_blank" class="text-xs text-indigo-600 underline break-all" x-text="url"></a>
                                    </div>
                                </template>

                                <pre class="bg-gray-900 text-gray-100 rounded p-3 overflow-x-auto text-xs mt-3" x-text="JSON.stringify(result, null, 2)"></pre>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const TOOLS_JS_MSG = @json(__('chat.js'));

        function toolsPanel() {
            return {
                active: '',
                args: {},
                imageUrlsRaw: '',
                running: false,
                result: null,
                error: '',
                clientError: '',

                forms: @json(collect($tools)->map(fn($t)=>['label'=>$t['label']])),

                selectTool(name) {
                    this.active = name;
                    this.args = {};
                    this.imageUrlsRaw = '';
                    this.result = null;
                    this.error = '';
                    this.clientError = '';
                },

                /** Client-side validation FIRST — before any network call. */
                validate() {
                    this.clientError = '';
                    const a = this.active;

                    if (a === 'image_generation' || a === 'video_generation' || a === 'audio_generation') {
                        if (!this.args.query || !this.args.query.trim()) { this.clientError = TOOLS_JS_MSG.tool_query_required; return false; }
                    }
                    if (a === 'web_search') {
                        if (!this.args.q || !this.args.q.trim())         { this.clientError = TOOLS_JS_MSG.tool_query_required; return false; }
                    }
                    if (a === 'image_search') {
                        if (!this.args.query || !this.args.query.trim()) { this.clientError = TOOLS_JS_MSG.tool_query_required; return false; }
                    }
                    if (a === 'crawler') {
                        if (!this.args.url || !/^https?:\/\//.test(this.args.url)) { this.clientError = TOOLS_JS_MSG.tool_url_required; return false; }
                    }
                    if (a === 'summarize_large_document') {
                        if (!this.args.url || !/^https?:\/\//.test(this.args.url)) { this.clientError = TOOLS_JS_MSG.tool_url_required; return false; }
                        if (!this.args.question || !this.args.question.trim())     { this.clientError = TOOLS_JS_MSG.tool_query_required; return false; }
                    }
                    if (a === 'understand_images') {
                        const urls = (this.imageUrlsRaw || '').split('\n').map(s=>s.trim()).filter(Boolean);
                        if (urls.length === 0) { this.clientError = TOOLS_JS_MSG.tool_url_required; return false; }
                        this.args.image_urls = urls;
                        if (!this.args.instruction || !this.args.instruction.trim()) { this.clientError = TOOLS_JS_MSG.tool_query_required; return false; }
                    }
                    if (a === 'stock_price') {
                        if (!this.args.symbol || !this.args.symbol.trim()) { this.clientError = TOOLS_JS_MSG.tool_query_required; return false; }
                    }
                    return true;
                },

                async run() {
                    if (!this.validate()) return;

                    this.running = true;
                    this.result  = null;
                    this.error   = '';

                    try {
                        const res = await fetch('{{ url('/tools/run') }}/' + encodeURIComponent(this.active), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(this.args),
                        });

                        const body = await res.json().catch(() => ({}));
                        if (!res.ok || body.error) {
                            this.error = body.error || ('HTTP ' + res.status);
                        } else {
                            this.result = body.data || body;
                        }
                    } catch (e) {
                        this.error = 'Network error: ' + e.message;
                    } finally {
                        this.running = false;
                    }
                },

                extractMediaUrls(obj) {
                    const urls = [];
                    const walk = (v) => {
                        if (!v) return;
                        if (typeof v === 'string' && /^https?:\/\/.+\.(png|jpe?g|webp|gif|mp4|webm|mov|mp3|wav|ogg|m4a)(\?|$)/i.test(v)) {
                            urls.push(v);
                        } else if (Array.isArray(v)) {
                            v.forEach(walk);
                        } else if (typeof v === 'object') {
                            Object.values(v).forEach(walk);
                        }
                    };
                    walk(obj);
                    return [...new Set(urls)];
                },

                copyResult() {
                    const text = JSON.stringify(this.result, null, 2);
                    if (navigator.clipboard) navigator.clipboard.writeText(text);
                },
            };
        }
    </script>
</x-app-layout>
