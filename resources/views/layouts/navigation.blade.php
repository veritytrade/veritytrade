<nav class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    <span class="hidden sm:inline font-semibold text-gray-800">{{ config('app.name') }}</span>
                </a>
                <div class="hidden md:flex md:ml-8 md:gap-1">
                    <a href="{{ url('/#hot-deals') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('home') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        Hot Deals
                    </a>
                    <a href="{{ url('/#phones') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('phones.*') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        Phones
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                            Dashboard
                        </a>
                    @endauth
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden md:flex md:items-center md:gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 text-sm font-medium rounded-lg transition">Register</a>
                        @endif
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg px-3 py-2">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-48 rounded-lg bg-white shadow-lg border border-gray-200 py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">Profile</a>
                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">Log out</button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
                <button type="button" @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-gray-200 bg-white">
        <div class="pt-2 pb-4 px-4 space-y-1">
            <a href="{{ url('/#hot-deals') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-100">Hot Deals</a>
            <a href="{{ url('/#phones') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-100">Phones</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-100">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-100">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-100">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-100">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-green-600 hover:bg-green-50">Register</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
