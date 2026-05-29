@extends('landing._layout', ['pageTitle' => __('common.nav_pricing')])

@section('content')

<section class="pt-32 pb-16 hero-grid">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center">
        <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">{{ __('landing.pricing_eyebrow') }}</p>
        <h1 class="mt-2 text-4xl sm:text-5xl font-extrabold tracking-tight">
            <span class="gradient-text">{{ __('landing.pricing_title') }}</span>
        </h1>
        <p class="mt-4 mx-auto max-w-2xl text-gray-600">{{ __('landing.pricing_subtitle') }}</p>
    </div>
</section>

<section class="py-12">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($plans as $plan)
                <div id="{{ $plan['id'] }}"
                     class="relative bg-white rounded-2xl p-6 border-2 {{ $plan['highlight'] ? 'border-indigo-500 shadow-2xl shadow-indigo-100 scale-[1.02]' : 'border-gray-200' }} flex flex-col">
                    @if ($plan['highlight'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-indigo-600 text-white text-[10px] uppercase tracking-wide font-semibold rounded-full">
                            {{ __('landing.pricing_most_popular') }}
                        </span>
                    @endif

                    <div class="flex items-center gap-2">
                        <i class="fa-solid {{ $plan['icon'] }} {{ $plan['icon_color'] }} text-2xl"></i>
                        <h3 class="text-xl font-bold">{{ $plan['name'] }}</h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">{{ $plan['tagline'] }}</p>

                    <div class="mt-4 mb-4 flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold">{{ $plan['price'] }}</span>
                        <span class="text-sm text-gray-500">{{ $plan['period'] }}</span>
                    </div>

                    <ul class="space-y-2.5 text-sm text-gray-700 flex-1">
                        @foreach ($plan['features'] as $f)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-emerald-500 text-xs mt-1.5 shrink-0"></i>
                                <span>{{ $f }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($plan['id'] === 'enterprise')
                        <a href="mailto:sales@selfai.local"
                           class="mt-6 w-full text-center px-4 py-2.5 rounded-lg font-medium text-sm bg-gray-900 text-white hover:bg-gray-700 inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-envelope"></i>
                            {{ $plan['cta'] }}
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="mt-6 w-full text-center px-4 py-2.5 rounded-lg font-medium text-sm inline-flex items-center justify-center gap-2 {{ $plan['highlight'] ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-800' }}">
                            <i class="fa-solid fa-arrow-right"></i>
                            {{ $plan['cta'] }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Comparison matrix --}}
        <div class="mt-20 overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-4 px-4 text-gray-700">Feature</th>
                        @foreach ($plans as $plan)
                            <th class="text-center py-4 px-4 {{ $plan['highlight'] ? 'text-indigo-600 font-bold' : 'text-gray-700' }}">
                                <i class="fa-solid {{ $plan['icon'] }} {{ $plan['icon_color'] }} mr-1"></i>
                                {{ $plan['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $matrix = [
                            ['Active API keys',         ['1', '5', 'Unlimited', 'Unlimited']],
                            ['Auto-failover',           ['—', '✓', '✓', '✓']],
                            ['Daily chat calls',        ['100', '5 000', '50 000', 'Unlimited']],
                            ['Image / video / audio',   ['✓', '✓', '✓', '✓']],
                            ['Usage statistics',        ['—', '✓', '✓', '✓']],
                            ['Multi-project switching', ['—', '✓', '✓', '✓']],
                            ['Team members',            ['1', '1', '10', 'Unlimited']],
                            ['Shared project library',  ['—', '—', '✓', '✓']],
                            ['Slack / webhook alerts',  ['—', '—', '✓', '✓']],
                            ['SSO (Google + GitHub)',   ['—', '—', '✓', '✓']],
                            ['SAML / LDAP SSO',         ['—', '—', '—', '✓']],
                            ['White-label branding',    ['—', '—', '—', '✓']],
                            ['On-premise install',      ['—', '—', '—', '✓']],
                            ['Support',                 ['Community', 'Email', 'Email + chat', 'Dedicated AM']],
                            ['SLA',                     ['—', '—', '99%', '99.9%']],
                        ];
                    @endphp
                    @foreach ($matrix as [$label, $row])
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $label }}</td>
                            @foreach ($row as $cell)
                                <td class="py-3 px-4 text-center {{ $cell === '✓' ? 'text-emerald-600 font-semibold' : ($cell === '—' ? 'text-gray-300' : 'text-gray-700') }}">
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="bg-gray-50 py-24" id="faq">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold tracking-tight text-center mb-10">{{ __('landing.pricing_faq_title') }}</h2>

        <div class="space-y-3" x-data="{ open: 0 }">
            @foreach ($faq as $i => $f)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <button type="button"
                            @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                            class="w-full text-left px-5 py-4 flex items-center justify-between gap-4">
                        <span class="font-medium text-gray-900">{{ $f['q'] }}</span>
                        <i class="fa-solid fa-chevron-down transition-transform"
                           :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse x-cloak
                         class="px-5 pb-4 text-sm text-gray-600 leading-relaxed">
                        {{ $f['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-24">
    <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('landing.pricing_cta_title') }}</h2>
        <p class="mt-4 text-gray-600">{{ __('landing.pricing_cta_desc') }}</p>
        <a href="{{ route('register') }}"
           class="mt-8 inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200">
            <i class="fa-solid fa-rocket"></i>
            {{ __('landing.pricing_cta_button') }}
        </a>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>[x-cloak]{display:none!important}</style>

@endsection
