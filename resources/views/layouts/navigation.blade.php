<nav class="sticky top-0 z-30 border-b border-white/70 bg-white/85 backdrop-blur-md shadow-[0_8px_24px_rgba(2,6,23,0.06)]" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    <span class="hidden sm:inline font-semibold text-slate-800 tracking-tight">Verity Gadgets</span>
                </a>
                <div class="hidden md:flex md:ml-8 md:gap-1">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-xl {{ request()->routeIs('dashboard') ? 'text-emerald-700 bg-emerald-50 border border-emerald-100' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                            Dashboard
                        </a>
                    @endauth
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden md:flex md:items-center md:gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="text-slate-700 hover:text-slate-900 px-3 py-2 text-sm font-medium rounded-xl hover:bg-slate-100">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="premium-btn-primary">Register</a>
                        @endif
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex items-center gap-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl px-3 py-2">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-200 py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-t-xl">Profile</a>
                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Admin</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-b-xl">Log out</button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
                <button type="button" @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-slate-200 bg-white/95 backdrop-blur">
        <div class="pt-2 pb-4 px-4 space-y-1">
            @auth
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="block px-3 py-2 rounded-xl text-base font-medium text-emerald-700 hover:bg-emerald-50">Register</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
