<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-emerald-600"></i>
            {{ __('stats.page_title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-600">{{ __('stats.subtitle') }}</p>

            {{-- ============ KPI cards ============ --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $todayCalls  = (int) ($today->calls  ?? 0);
                    $todayTokens = (int) ($today->tokens ?? 0);
                    $todayErrors = (int) ($today->errors ?? 0);
                    $periodCalls = (int) ($period->calls ?? 0);
                @endphp

                <div class="bg-white shadow rounded-lg p-4 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-comments text-indigo-600 text-2xl"></i>
                        <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('stats.today') }}</span>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($todayCalls) }}</div>
                    <div class="text-xs text-gray-500">{{ __('stats.calls') }}</div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-coins text-emerald-600 text-2xl"></i>
                        <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('stats.today') }}</span>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($todayTokens) }}</div>
                    <div class="text-xs text-gray-500">{{ __('stats.tokens') }}</div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-triangle-exclamation text-red-600 text-2xl"></i>
                        <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('stats.today') }}</span>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($todayErrors) }}</div>
                    <div class="text-xs text-gray-500">{{ __('stats.errors') }}</div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 border-l-4 border-violet-500">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-calendar-days text-violet-600 text-2xl"></i>
                        <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('stats.last_30_days') }}</span>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($periodCalls) }}</div>
                    <div class="text-xs text-gray-500">{{ __('stats.calls') }}</div>
                </div>
            </div>

            @if ($periodCalls === 0)
                <div class="bg-white shadow rounded-lg p-8 text-center">
                    <i class="fa-solid fa-chart-simple text-5xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500 text-sm">{{ __('stats.no_data') }}</p>
                    <a href="{{ route('chat.index') }}" class="mt-3 inline-flex items-center gap-1 text-indigo-600 hover:underline text-sm">
                        <i class="fa-solid fa-comment"></i> Start a chat
                    </a>
                </div>
            @else
                {{-- ============ Daily chart ============ --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-area text-indigo-600"></i>
                        {{ __('stats.chart_title') }}
                    </h3>
                    <div class="relative" style="height:280px;">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>

                {{-- ============ Breakdown tables ============ --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Tools --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <h3 class="font-semibold text-gray-800 p-4 border-b flex items-center gap-2">
                            <i class="fa-solid fa-wrench text-indigo-600"></i>
                            {{ __('stats.by_tool_title') }}
                        </h3>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="text-left px-4 py-2">{{ __('stats.col_tool') }}</th>
                                    <th class="text-right px-4 py-2">{{ __('stats.col_calls') }}</th>
                                    <th class="text-right px-4 py-2">{{ __('stats.col_tokens') }}</th>
                                    <th class="text-right px-4 py-2">{{ __('stats.col_errors') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($by_tool as $row)
                                    @php
                                        $icon = match ($row->tool) {
                                            'chat'                         => 'fa-comment',
                                            'image_generation'             => 'fa-image',
                                            'video_generation'             => 'fa-video',
                                            'audio_generation'             => 'fa-music',
                                            'audio_transcribe'             => 'fa-microphone-lines',
                                            'web_search'                   => 'fa-magnifying-glass',
                                            'image_search'                 => 'fa-camera',
                                            'crawler'                      => 'fa-spider',
                                            'summarize_large_document'     => 'fa-file-lines',
                                            'understand_images'            => 'fa-eye',
                                            'stock_price'                  => 'fa-chart-line',
                                            default                        => 'fa-cube',
                                        };
                                        $label = __('stats.tool_' . $row->tool);
                                        if (str_starts_with($label, 'stats.tool_')) $label = $row->tool;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            <i class="fa-solid {{ $icon }} text-indigo-500 mr-2 w-4 text-center"></i>
                                            {{ $label }}
                                        </td>
                                        <td class="text-right px-4 py-2 font-medium">{{ number_format($row->calls) }}</td>
                                        <td class="text-right px-4 py-2 text-gray-600">{{ number_format($row->tokens) }}</td>
                                        <td class="text-right px-4 py-2 {{ $row->errors > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                            {{ number_format($row->errors) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-gray-500 py-6 text-xs">—</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Models --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <h3 class="font-semibold text-gray-800 p-4 border-b flex items-center gap-2">
                            <i class="fa-solid fa-microchip text-indigo-600"></i>
                            {{ __('stats.by_model_title') }}
                        </h3>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="text-left px-4 py-2">{{ __('stats.col_model') }}</th>
                                    <th class="text-right px-4 py-2">{{ __('stats.col_calls') }}</th>
                                    <th class="text-right px-4 py-2">{{ __('stats.col_tokens') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($by_model as $row)
                                    @php
                                        $family = match (true) {
                                            str_starts_with($row->model, 'claude') => ['fa-c', 'text-orange-500'],
                                            str_starts_with($row->model, 'gpt')    => ['fa-bolt', 'text-emerald-500'],
                                            str_starts_with($row->model, 'gemini') => ['fa-gem', 'text-blue-500'],
                                            str_starts_with($row->model, 'deep')   => ['fa-fish', 'text-violet-500'],
                                            str_starts_with($row->model, 'llama')  => ['fa-otter', 'text-amber-600'],
                                            str_starts_with($row->model, 'grok')   => ['fa-x', 'text-gray-800'],
                                            default => ['fa-microchip', 'text-gray-500'],
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 font-mono text-xs">
                                            <i class="fa-solid {{ $family[0] }} {{ $family[1] }} mr-2 w-4 text-center"></i>
                                            {{ $row->model }}
                                        </td>
                                        <td class="text-right px-4 py-2 font-medium">{{ number_format($row->calls) }}</td>
                                        <td class="text-right px-4 py-2 text-gray-600">{{ number_format($row->tokens) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-gray-500 py-6 text-xs">—</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Per-key breakdown --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <h3 class="font-semibold text-gray-800 p-4 border-b flex items-center gap-2">
                        <i class="fa-solid fa-key text-indigo-600"></i>
                        {{ __('stats.by_key_title') }}
                    </h3>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="text-left px-4 py-2">{{ __('stats.col_key') }}</th>
                                <th class="text-left px-4 py-2">{{ __('stats.col_account') }}</th>
                                <th class="text-right px-4 py-2">{{ __('stats.col_calls') }}</th>
                                <th class="text-right px-4 py-2">{{ __('stats.col_tokens') }}</th>
                                <th class="text-right px-4 py-2">{{ __('stats.col_errors') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($by_key as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <i class="fa-solid fa-key text-indigo-500 mr-2"></i>
                                        {{ $row->label }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 text-xs">{{ $row->account_email ?: '—' }}</td>
                                    <td class="text-right px-4 py-2 font-medium">{{ number_format($row->calls) }}</td>
                                    <td class="text-right px-4 py-2 text-gray-600">{{ number_format($row->tokens) }}</td>
                                    <td class="text-right px-4 py-2 {{ $row->errors > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                        {{ number_format($row->errors) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-gray-500 py-6 text-xs">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @if ($periodCalls > 0)
        <script>
            (function () {
                const ctx = document.getElementById('dailyChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chart['labels']),
                        datasets: [
                            {
                                label: '{{ __('stats.calls') }}',
                                data: @json($chart['calls']),
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,0.15)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true,
                                yAxisID: 'y',
                            },
                            {
                                label: '{{ __('stats.tokens') }}',
                                data: @json($chart['tokens']),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.0)',
                                borderWidth: 2,
                                tension: 0.3,
                                yAxisID: 'y1',
                            },
                            {
                                label: '{{ __('stats.errors') }}',
                                data: @json($chart['errors']),
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.15)',
                                borderWidth: 2,
                                tension: 0.3,
                                yAxisID: 'y',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            y:  { beginAtZero: true, position: 'left',  title: { display: true, text: '{{ __('stats.calls') }} / {{ __('stats.errors') }}' } },
                            y1: { beginAtZero: true, position: 'right', title: { display: true, text: '{{ __('stats.tokens') }}' }, grid: { drawOnChartArea: false } },
                        },
                    },
                });
            })();
        </script>
    @endif
</x-app-layout>
