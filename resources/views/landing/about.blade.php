@extends('landing._layout', ['pageTitle' => __('common.nav_about')])

@section('content')

<section class="pt-32 pb-16 hero-grid">
    <div class="max-w-3xl mx-auto px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">
            <span class="gradient-text">{{ __('landing.about_title') }}</span>
        </h1>
        <p class="mt-4 text-gray-600">{{ __('landing.about_subtitle') }}</p>
    </div>
</section>

<section class="py-16">
    <div class="max-w-3xl mx-auto px-6 lg:px-8 prose prose-gray prose-lg">
        <p class="text-gray-700 leading-relaxed">{{ __('landing.about_p1') }}</p>
        <p class="mt-6 text-gray-700 leading-relaxed">{{ __('landing.about_p2') }}</p>
        <p class="mt-6 text-gray-700 leading-relaxed">{{ __('landing.about_p3') }}</p>
    </div>
</section>

<section class="bg-gray-50 py-24">
    <div class="max-w-5xl mx-auto px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-center mb-12">{{ __('landing.about_stack_title') }}</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
                $stack = [
                    ['fa-brands fa-php',    'text-violet-600',  'about_stack_php'],
                    ['fa-solid fa-leaf',    'text-emerald-600', 'about_stack_breeze'],
                    ['fa-solid fa-mountain','text-cyan-600',    'about_stack_alpine'],
                    ['fa-solid fa-wind',    'text-sky-600',     'about_stack_tw'],
                    ['fa-solid fa-database','text-amber-600',   'about_stack_sqlite'],
                    ['fa-solid fa-terminal','text-pink-600',    'about_stack_gen'],
                ];
            @endphp
            @foreach ($stack as [$icon, $color, $key])
                <div class="p-5 bg-white rounded-lg border border-gray-200 flex items-center gap-3">
                    <i class="{{ $icon }} {{ $color }} text-2xl"></i>
                    <span class="text-sm font-medium text-gray-800">{{ __('landing.' . $key) }}</span>
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

@endsection
