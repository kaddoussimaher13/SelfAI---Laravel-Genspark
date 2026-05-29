<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? config('app.name', 'SelfAI') }} · {{ config('app.name', 'SelfAI') }}</title>

    {{-- Tailwind via CDN — keeps the marketing pages totally independent of Vite. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                    },
                },
            },
        };
    </script>

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .gradient-text {
            background-image: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .hero-grid {
            background-image:
                radial-gradient(circle at 50% 0%, rgba(99,102,241,0.18), transparent 55%),
                linear-gradient(rgba(99,102,241,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.06) 1px, transparent 1px);
            background-size: cover, 40px 40px, 40px 40px;
            background-position: center top;
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

{{-- ===================== NAV ===================== --}}
<header class="absolute inset-x-0 top-0 z-50">
    <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 lg:px-8 py-5">
        <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 font-bold text-xl">
            <span class="inline-flex w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-500 text-white items-center justify-center">
                <i class="fa-solid fa-bolt"></i>
            </span>
            {{ config('app.name', 'SelfAI') }}
        </a>

        <div class="hidden md:flex items-center gap-8 text-sm text-gray-700">
            <a href="{{ route('landing.features') }}" class="hover:text-indigo-600 {{ request()->routeIs('landing.features') ? 'text-indigo-600 font-semibold' : '' }}">
                {{ __('common.nav_features') }}
            </a>
            <a href="{{ route('landing.pricing') }}" class="hover:text-indigo-600 {{ request()->routeIs('landing.pricing') ? 'text-indigo-600 font-semibold' : '' }}">
                {{ __('common.nav_pricing') }}
            </a>
            <a href="{{ route('landing.about') }}" class="hover:text-indigo-600 {{ request()->routeIs('landing.about') ? 'text-indigo-600 font-semibold' : '' }}">
                {{ __('common.nav_about') }}
            </a>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('chat.index') }}"
                   class="text-sm font-medium px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 inline-flex items-center gap-2">
                    <i class="fa-solid fa-comment text-xs"></i>
                    {{ __('common.nav_chat') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-indigo-600 hidden sm:inline">
                    {{ __('common.nav_login') }}
                </a>
                <a href="{{ route('register') }}"
                   class="text-sm font-medium px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 inline-flex items-center gap-2">
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                    {{ __('common.nav_get_started') }}
                </a>
            @endauth
        </div>
    </nav>
</header>

<main>
    {{ $slot ?? '' }}
    @yield('content')
</main>

{{-- ===================== FOOTER ===================== --}}
<footer class="bg-gray-50 border-t border-gray-200 mt-24">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 grid grid-cols-2 md:grid-cols-5 gap-8 text-sm">
        <div class="col-span-2 md:col-span-2">
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 font-bold text-lg">
                <span class="inline-flex w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-500 text-white items-center justify-center">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                {{ config('app.name', 'SelfAI') }}
            </a>
            <p class="mt-3 text-gray-600 max-w-xs">{{ __('landing.footer_tagline') }}</p>
            <div class="mt-4 flex gap-3 text-gray-500">
                <a href="#" class="hover:text-indigo-600"><i class="fa-brands fa-github text-lg"></i></a>
                <a href="#" class="hover:text-indigo-600"><i class="fa-brands fa-x-twitter text-lg"></i></a>
                <a href="#" class="hover:text-indigo-600"><i class="fa-brands fa-discord text-lg"></i></a>
            </div>
        </div>

        <div>
            <h4 class="font-semibold text-gray-900 mb-3">{{ __('landing.footer_product') }}</h4>
            <ul class="space-y-2 text-gray-600">
                <li><a href="{{ route('landing.features') }}" class="hover:text-indigo-600">{{ __('common.nav_features') }}</a></li>
                <li><a href="{{ route('landing.pricing') }}" class="hover:text-indigo-600">{{ __('common.nav_pricing') }}</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-indigo-600">{{ __('common.nav_get_started') }}</a></li>
            </ul>
        </div>

        <div>
            <h4 class="font-semibold text-gray-900 mb-3">{{ __('landing.footer_company') }}</h4>
            <ul class="space-y-2 text-gray-600">
                <li><a href="{{ route('landing.about') }}" class="hover:text-indigo-600">{{ __('common.nav_about') }}</a></li>
                <li><a href="mailto:hello@selfai.local" class="hover:text-indigo-600">Contact</a></li>
            </ul>
        </div>

        <div>
            <h4 class="font-semibold text-gray-900 mb-3">{{ __('landing.footer_resources') }}</h4>
            <ul class="space-y-2 text-gray-600">
                <li><a href="#" class="hover:text-indigo-600">{{ __('landing.footer_docs') }}</a></li>
                <li><a href="#" class="hover:text-indigo-600">{{ __('landing.footer_github') }}</a></li>
                <li><a href="#" class="hover:text-indigo-600">{{ __('landing.footer_privacy') }}</a></li>
                <li><a href="#" class="hover:text-indigo-600">{{ __('landing.footer_terms') }}</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-gray-200 py-6 text-center text-xs text-gray-500">
        {{ __('landing.footer_copyright', ['year' => date('Y')]) }}
    </div>
</footer>

</body>
</html>
