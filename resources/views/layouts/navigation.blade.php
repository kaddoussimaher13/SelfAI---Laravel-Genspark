<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('chat.index') }}" class="text-xl font-bold text-gray-800 inline-flex items-center gap-2">
                        <span class="inline-flex w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-500 text-white items-center justify-center text-sm">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        {{ __('common.app_name') }}
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                        <i class="fa-solid fa-comment text-xs me-1"></i>
                        {{ __('common.nav_chat') }}
                    </x-nav-link>
                    <x-nav-link :href="route('conversations.index')" :active="request()->routeIs('conversations.*')">
                        <i class="fa-solid fa-list text-xs me-1"></i>
                        {{ __('common.nav_conversations') }}
                    </x-nav-link>
                    @auth
                        @if (auth()->user()->hasApiKey())
                            <x-nav-link :href="route('tools.index')" :active="request()->routeIs('tools.*')">
                                <i class="fa-solid fa-wand-magic-sparkles text-xs me-1"></i>
                                {{ __('chat.tools_title') }}
                            </x-nav-link>
                            <x-nav-link :href="route('stats.index')" :active="request()->routeIs('stats.*')">
                                <i class="fa-solid fa-chart-line text-xs me-1"></i>
                                {{ __('common.nav_stats') }}
                            </x-nav-link>
                        @endif
                    @endauth
                    <x-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*') || request()->routeIs('apikeys.*') || request()->routeIs('projects.*')">
                        <i class="fa-solid fa-sliders text-xs me-1"></i>
                        {{ __('common.nav_settings') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <i class="fa-solid fa-circle-user text-base"></i>
                            <div>{{ Auth::user()->name }}</div>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fa-solid fa-user text-xs me-1 text-gray-500"></i>
                            {{ __('common.nav_profile') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('apikeys.index')">
                            <i class="fa-solid fa-key text-xs me-1 text-gray-500"></i>
                            {{ __('settings.tab_keys') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('projects.index')">
                            <i class="fa-solid fa-folder-tree text-xs me-1 text-gray-500"></i>
                            {{ __('settings.tab_projects') }}
                        </x-dropdown-link>
                        <div class="border-t my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fa-solid fa-arrow-right-from-bracket text-xs me-1 text-gray-500"></i>
                                {{ __('common.nav_logout') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <i class="fa-solid" :class="open ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                <i class="fa-solid fa-comment me-1"></i> {{ __('common.nav_chat') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('conversations.index')" :active="request()->routeIs('conversations.*')">
                <i class="fa-solid fa-list me-1"></i> {{ __('common.nav_conversations') }}
            </x-responsive-nav-link>
            @auth
                @if (auth()->user()->hasApiKey())
                    <x-responsive-nav-link :href="route('tools.index')" :active="request()->routeIs('tools.*')">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> {{ __('chat.tools_title') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('stats.index')" :active="request()->routeIs('stats.*')">
                        <i class="fa-solid fa-chart-line me-1"></i> {{ __('common.nav_stats') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
            <x-responsive-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*')">
                <i class="fa-solid fa-sliders me-1"></i> {{ __('common.nav_settings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('apikeys.index')">
                <i class="fa-solid fa-key me-1"></i> {{ __('settings.tab_keys') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('projects.index')">
                <i class="fa-solid fa-folder-tree me-1"></i> {{ __('settings.tab_projects') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="fa-solid fa-user me-1"></i> {{ __('common.nav_profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> {{ __('common.nav_logout') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
