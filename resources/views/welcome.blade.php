<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('common.app_name') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-50 to-indigo-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-xl text-center p-10 bg-white rounded-2xl shadow-xl">
        <div class="text-5xl mb-4">✨</div>
        <h1 class="text-4xl font-bold text-gray-800 mb-3">{{ __('common.app_name') }}</h1>
        <p class="text-gray-600 mb-8">{{ __('common.tagline') }}</p>
        <div class="space-x-3">
            @auth
                <a href="{{ route('chat.index') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">{{ __('common.go_to_chat') }}</a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">{{ __('common.nav_login') }}</a>
                <a href="{{ route('register') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50">{{ __('common.nav_register') }}</a>
            @endauth
        </div>
    </div>
</body>
</html>
