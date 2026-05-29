@extends('landing._layout', ['pageTitle' => __('common.nav_features')])

@section('content')

<section class="pt-32 pb-16 hero-grid">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center">
        <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">{{ __('landing.features_eyebrow') }}</p>
        <h1 class="mt-2 text-4xl sm:text-5xl font-extrabold tracking-tight">
            <span class="gradient-text">{{ __('landing.features_title') }}</span>
        </h1>
        <p class="mt-4 mx-auto max-w-2xl text-gray-600">{{ __('landing.features_subtitle') }}</p>
    </div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $all = [
                ['fa-comments',              'text-indigo-500',  'feat_chat'],
                ['fa-key',                   'text-violet-500',  'feat_keys'],
                ['fa-wand-magic-sparkles',   'text-pink-500',    'feat_tools'],
                ['fa-database',              'text-emerald-500', 'feat_tenants'],
                ['fa-chart-line',            'text-amber-500',   'feat_stats'],
                ['fa-language',              'text-cyan-500',    'feat_i18n'],
                ['fa-lock',                  'text-red-500',     'feat_security'],
                ['fa-shield-halved',         'text-orange-500',  'feat_validation'],
                ['fa-server',                'text-teal-500',    'feat_subfolder'],
                ['fa-bolt',                  'text-yellow-500',  'feat_ajax'],
                ['fa-cubes',                 'text-blue-500',    'feat_models'],
                ['fa-code-branch',           'text-fuchsia-500', 'feat_open'],
            ];
        @endphp

        @foreach ($all as [$icon, $color, $key])
            <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-lg transition bg-white">
                <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center {{ $color }} text-xl">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>
                <h3 class="mt-4 font-semibold text-lg text-gray-900">{{ __('landing.' . $key . '_title') }}</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('landing.' . $key . '_desc') }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- The "model & tool catalogue" callout --}}
<section class="bg-gradient-to-br from-indigo-50 via-violet-50 to-pink-50 py-24">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">58+ models. 171 tools.<br>One panel.</h2>
        <p class="mt-4 text-gray-700 max-w-2xl mx-auto">
            SelfAI exposes the entire Genspark catalogue through a single, polished UI — pick a tool, fill out the form, and the result lands on your screen seconds later.
        </p>

        <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-6 rounded-xl bg-white shadow border border-gray-200">
                <i class="fa-solid fa-microchip text-3xl text-indigo-500"></i>
                <div class="mt-3 text-3xl font-extrabold">16</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">LLM models</div>
            </div>
            <div class="p-6 rounded-xl bg-white shadow border border-gray-200">
                <i class="fa-solid fa-image text-3xl text-pink-500"></i>
                <div class="mt-3 text-3xl font-extrabold">11</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Image models</div>
            </div>
            <div class="p-6 rounded-xl bg-white shadow border border-gray-200">
                <i class="fa-solid fa-video text-3xl text-violet-500"></i>
                <div class="mt-3 text-3xl font-extrabold">18</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Video models</div>
            </div>
            <div class="p-6 rounded-xl bg-white shadow border border-gray-200">
                <i class="fa-solid fa-music text-3xl text-emerald-500"></i>
                <div class="mt-3 text-3xl font-extrabold">13</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Audio models</div>
            </div>
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

@endsection
