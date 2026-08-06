<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @hasSection('title')
            @yield('title') —
        @endif
        {{ config('app.name', 'Laravel') }}
    </title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=oswald:500,600,700|outfit:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@inject('cartService', \App\Services\CartService::class)
<body @class([
    'flex min-h-full flex-col font-sans text-base leading-relaxed text-ink-900 antialiased',
    'bg-white' => request()->routeIs('cart.index'),
    'bg-page-mesh' => ! request()->routeIs('cart.index'),
])>
    <div class="flex min-h-full flex-1 flex-col" x-data="{ mobileOpen: false }" @keydown.window.escape="mobileOpen = false">
        <x-store-promo-bar />

        <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-gradient-to-b from-zinc-50/95 to-zinc-100/80 text-ink-900 shadow-[0_8px_30px_-12px_rgba(94,82,74,0.14)] backdrop-blur-md backdrop-saturate-150 supports-[backdrop-filter]:bg-zinc-50/90">
            <div class="page-shell flex h-14 min-w-0 items-center gap-2 sm:gap-3 lg:h-[4.25rem] lg:gap-4">
                <a href="{{ route('home') }}" class="font-display min-w-0 shrink truncate text-lg font-bold uppercase tracking-mega text-ink-900 transition-opacity hover:opacity-80 lg:max-w-none lg:text-xl">
                    {{ config('app.name') }}
                </a>

                {{-- Middle: search + desktop nav share the flexible width so the cart column is never pushed off-screen --}}
                <div class="flex min-w-0 flex-1 items-center gap-2 lg:gap-4">
                    @unless (request()->routeIs('shop.index'))
                        <x-store-search-form variant="header" />
                    @endunless

                    <nav class="hidden min-w-0 items-center gap-0.5 lg:flex" aria-label="Primary">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                        <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'nav-link-active' : '' }}">Shop</a>
                        @auth
                            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'nav-link-active' : '' }}">Orders</a>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">Dashboard</a>
                            @endif
                        @endauth
                    </nav>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn-ghost-inverse hidden px-3 py-2 sm:inline-flex">Log in</a>
                        <a href="{{ route('register') }}" class="btn-on-dark hidden px-4 py-2.5 sm:inline-flex">Join</a>
                    @else
                        <div class="hidden items-center gap-3 lg:flex">
                            <span class="{{ request()->routeIs('home') ? 'max-w-[14rem] text-ink-800' : 'max-w-[9rem] text-ink-500' }} truncate text-xs font-medium" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                            @unless (request()->routeIs('home'))
                                <span class="rounded-full border border-zinc-200/90 bg-white/80 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-ink-600">{{ auth()->user()->role }}</span>
                            @endunless
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="hidden lg:inline">
                            @csrf
                            <button type="submit" class="btn-outline-light px-4 py-2 text-[10px]">Log out</button>
                        </form>
                    @endguest

                    <button
                        type="button"
                        class="inline-flex size-10 items-center justify-center rounded-xl text-ink-950 transition-colors hover:bg-zinc-200/50 lg:hidden"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen"
                        aria-controls="mobile-nav"
                        aria-label="Toggle menu"
                    >
                        <svg x-show="!mobileOpen" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div
                id="mobile-nav"
                x-show="mobileOpen"
                x-transition:enter="transition ease-out duration-300 motion-reduce:duration-0"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 motion-reduce:duration-0"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                x-cloak
                class="border-t border-zinc-200/80 bg-zinc-50/98 backdrop-blur-xl lg:hidden"
            >
                <nav class="page-shell flex flex-col gap-0.5 py-4" aria-label="Mobile">
                    <div x-data="{ openSearch: false }" class="mb-1">
                        <button type="button" @click="openSearch = !openSearch; if(openSearch) $nextTick(() => { document.getElementById('store-search-q-drawer').focus(); })" class="nav-link flex w-full items-center justify-between" :aria-expanded="openSearch">
                            <span>Search products</span>
                            <svg class="size-5 text-ink-500 transition-colors duration-200" :class="{ 'text-ink-900': openSearch }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197M15.803 15.803A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                        <div x-show="openSearch" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="mt-2 pb-2">
                            <x-store-search-form variant="drawer" />
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                    <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'nav-link-active' : '' }}">Shop</a>
                    @guest
                        <a href="{{ route('login') }}" class="nav-link">Log in</a>
                        <a href="{{ route('register') }}" class="btn-on-dark mt-3 justify-center">Join</a>
                    @else
                        <a href="{{ route('orders.index') }}" class="nav-link">Orders</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-zinc-200/80 pt-4">
                            @csrf
                            <button type="submit" class="btn-outline-light w-full justify-center">Log out</button>
                        </form>
                    @endguest
                </nav>
            </div>
        </header>

        <div class="page-shell pt-6 lg:pt-8">
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    class="mb-6 flex items-start justify-between gap-4 rounded-2xl border border-emerald-200/60 bg-emerald-50/95 px-4 py-3.5 text-sm text-emerald-950 shadow-soft ring-1 ring-emerald-500/10 backdrop-blur-sm"
                    role="status"
                >
                    <span class="pt-0.5 text-pretty">{{ session('success') }}</span>
                    <button type="button" class="flex size-8 shrink-0 items-center justify-center rounded-xl text-emerald-800 transition-colors hover:bg-emerald-100/90" @click="show = false" aria-label="Dismiss">×</button>
                </div>
            @endif
            @if (session('error'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    class="mb-6 flex items-start justify-between gap-4 rounded-2xl border border-red-200/60 bg-red-50/95 px-4 py-3.5 text-sm text-red-950 shadow-soft ring-1 ring-red-500/10 backdrop-blur-sm"
                    role="alert"
                >
                    <span class="pt-0.5 text-pretty">{{ session('error') }}</span>
                    <button type="button" class="flex size-8 shrink-0 items-center justify-center rounded-xl text-red-800 transition-colors hover:bg-red-100/90" @click="show = false" aria-label="Dismiss">×</button>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200/60 bg-red-50/95 px-4 py-3.5 text-sm text-red-950 shadow-soft ring-1 ring-red-500/10 backdrop-blur-sm" role="alert">
                    <p class="text-xs font-bold uppercase tracking-wide text-red-900">Please fix the following</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-pretty">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @hasSection('content_outer')
            <div class="@yield('content_outer')">
                @yield('content')
            </div>
        @else
            <div class="page-shell flex-1 pb-28 pt-2 max-lg:pb-32 lg:pb-24 lg:pt-4">
                @yield('content')
            </div>
        @endif

        <footer class="mt-auto border-t border-zinc-200/80 bg-gradient-to-b from-zinc-100/90 via-zinc-50 to-zinc-50 text-ink-800">
            <div class="page-shell grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12 lg:py-20">
                <div class="sm:col-span-2 lg:col-span-1">
                    <p class="font-display text-lg font-bold uppercase tracking-mega text-ink-900">{{ config('app.name') }}</p>
                    <p class="mt-4 flex items-center gap-2 text-pretty text-sm text-ink-600">
                        <i class="fas fa-envelope"></i>
                        bone@example.com
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-pretty text-sm text-ink-600">
                        <i class="fas fa-phone"></i>
                        +383 49 123 456
                    </p>
                    <div class="mt-4 flex items-center gap-4 text-pretty text-ink-600">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-accent-700">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-accent-700">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-accent-700">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-700">Shop</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('cart.index') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Bag / cart</a></li>
                        <li><a href="{{ route('shop.index') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">All women&apos;s</a></li>
                        <li><a href="{{ route('shop.index') }}#leggings" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Leggings</a></li>
                        <li><a href="{{ route('shop.index') }}#bras" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Sports bras</a></li>
                        <li><a href="{{ route('shop.index') }}#layers" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Layers</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-700">Help</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('about') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">About us</a></li>
                        <li><a href="{{ route('contact') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Contact us</a></li>
                        <li><a href="{{ route('terms') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Terms &amp; conditions</a></li>
                        <li><a href="{{ route('returns') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Returns</a></li>
                        <li><a href="{{ route('size-guide') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Size guide</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-700">Account</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        @guest
                            <li><a href="{{ route('login') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Log in</a></li>
                            <li><a href="{{ route('register') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Create account</a></li>
                        @else
                            <li><a href="{{ route('orders.index') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">Orders</a></li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div class="border-t border-zinc-200/70 py-7">
                <div class="page-shell flex flex-col items-center justify-between gap-4 text-center text-[11px] leading-relaxed text-ink-500 sm:flex-row sm:text-left">
                    <p class="text-balance">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    {{-- Floating bag: fixed to viewport (all breakpoints), high z-index --}}
    <x-cart-fab :count="$cartService->count()" />

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
