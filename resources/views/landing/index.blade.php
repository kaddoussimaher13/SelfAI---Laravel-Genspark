@extends('landing._layout', ['pageTitle' => __('landing.hero_title')])

@section('content')

{{-- ===================== HERO ===================== --}}
<section class="hero-grid pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium">
            <i class="fa-solid fa-sparkles"></i>
            {{ __('landing.hero_eyebrow') }}
        </span>

        <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
            <span class="gradient-text">{{ __('landing.hero_title') }}</span>
        </h1>

        <p class="mt-6 mx-auto max-w-2xl text-lg text-gray-600 leading-relaxed">
            {{ __('landing.hero_subtitle') }}
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('register') }}"
               class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 inline-flex items-center justify-center gap-2 shadow-lg shadow-indigo-200">
                <i class="fa-solid fa-rocket"></i>
                {{ __('landing.hero_cta_primary') }}
            </a>
            <a href="{{ route('landing.pricing') }}"
               class="px-6 py-3 rounded-lg bg-white text-gray-800 font-semibold border border-gray-300 hover:border-indigo-500 hover:text-indigo-600 inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-tag"></i>
                {{ __('landing.hero_cta_secondary') }}
            </a>
        </div>

        {{-- Dashboard mock-up --}}
        <div class="mt-16 mx-auto max-w-5xl rounded-2xl bg-white shadow-2xl border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex gap-1.5">
                <span class="w-3 h-3 rounded-full bg-red-400"></span>
                <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                <span class="w-3 h-3 rounded-full bg-green-400"></span>
                <span class="ml-3 text-xs text-gray-500 font-mono">selfai.app/chat</span>
            </div>
            <div class="grid grid-cols-4 min-h-[320px]">
                <aside class="col-span-1 bg-gray-50 border-r border-gray-200 p-4 text-left">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-2">Conversations</p>
                    <ul class="space-y-1 text-xs">
                        <li class="px-2 py-1.5 bg-indigo-100 text-indigo-700 rounded font-medium truncate">Marketing copy for v2 launch</li>
                        <li class="px-2 py-1.5 hover:bg-gray-100 rounded truncate">Refactor Laravel queue</li>
                        <li class="px-2 py-1.5 hover:bg-gray-100 rounded truncate">Generate hero illustration</li>
                        <li class="px-2 py-1.5 hover:bg-gray-100 rounded truncate">Summarise Q4 board memo</li>
                    </ul>
                </aside>
                <div class="col-span-3 p-6 text-left">
                    <div class="flex justify-end mb-3">
                        <span class="bg-indigo-600 text-white text-xs px-3 py-2 rounded-lg max-w-md">
                            Write a launch tweet for SelfAI v2 — focus on multi-key failover.
                        </span>
                    </div>
                    <div class="flex justify-start mb-3">
                        <span class="bg-gray-100 text-gray-800 text-xs px-3 py-2 rounded-lg max-w-md">
                            🚀 SelfAI v2 ships with multi-key auto-failover — drop in as many Genspark keys as you want, and we'll silently roll over when one hits its rate-limit. Your conversations never stop mid-thought.
                        </span>
                    </div>
                    <div class="flex justify-start">
                        <span class="bg-gray-100 text-gray-400 text-xs px-3 py-2 rounded-lg inline-flex items-center gap-1">
                            <i class="fa-solid fa-circle-notch fa-spin"></i> Drafting another option…
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== TRUST STRIP ===================== --}}
<section class="border-y border-gray-200 bg-white py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <p class="text-xs uppercase tracking-widest text-gray-500 mb-5">{{ __('landing.trust_strip') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-8 text-gray-400">
            <span class="font-bold text-lg">Acme Studio</span>
            <span class="font-bold text-lg">Sprintly</span>
            <span class="font-bold text-lg">Forge Labs</span>
            <span class="font-bold text-lg">Inkwell</span>
            <span class="font-bold text-lg">Bytecraft</span>
            <span class="font-bold text-lg">Pixelpop</span>
        </div>
    </div>
</section>

{{-- ===================== FEATURES (top 6) ===================== --}}
<section class="py-24" id="features">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">{{ __('landing.features_eyebrow') }}</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('landing.features_title') }}</h2>
            <p class="mt-4 mx-auto max-w-2xl text-gray-600">{{ __('landing.features_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $items = [
                    ['fa-key',            'text-indigo-500', 'feat_keys_title',     'feat_keys_desc'],
                    ['fa-shuffle',        'text-violet-500', 'feat_chat_title',     'feat_chat_desc'],
                    ['fa-wand-magic-sparkles', 'text-pink-500', 'feat_tools_title', 'feat_tools_desc'],
                    ['fa-database',       'text-emerald-500','feat_tenants_title',  'feat_tenants_desc'],
                    ['fa-chart-line',     'text-amber-500',  'feat_stats_title',    'feat_stats_desc'],
                    ['fa-lock',           'text-red-500',    'feat_security_title', 'feat_security_desc'],
                ];
            @endphp
            @foreach ($items as [$icon, $color, $tKey, $tDesc])
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center {{ $color }} text-xl">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <h3 class="mt-4 font-semibold text-lg text-gray-900">{{ __('landing.' . $tKey) }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('landing.' . $tDesc) }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('landing.features') }}"
               class="inline-flex items-center gap-1 text-indigo-600 hover:underline font-medium">
                See all features <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

{{-- ===================== PRICING TEASER ===================== --}}
<section class="bg-gray-50 py-24" id="pricing-teaser">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">{{ __('landing.pricing_eyebrow') }}</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('landing.pricing_title') }}</h2>
            <p class="mt-4 mx-auto max-w-2xl text-gray-600">{{ __('landing.pricing_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($plans as $plan)
                <div class="relative bg-white rounded-2xl p-6 border-2 {{ $plan['highlight'] ? 'border-indigo-500 shadow-xl scale-[1.02]' : 'border-gray-200' }} flex flex-col">
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

                    <ul class="space-y-2 text-sm text-gray-700 flex-1">
                        @foreach (array_slice($plan['features'], 0, 5) as $f)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-emerald-500 text-xs mt-1.5"></i>
                                <span>{{ $f }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('landing.pricing') }}#{{ $plan['id'] }}"
                       class="mt-5 w-full text-center px-4 py-2 rounded-lg font-medium text-sm {{ $plan['highlight'] ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-800' }}">
                        {{ $plan['cta'] }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== FINAL CTA ===================== --}}
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

@endsection
