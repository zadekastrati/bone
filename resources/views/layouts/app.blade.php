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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@inject('cartService', \App\Services\CartService::class)
<body class="flex min-h-full flex-col bg-page-mesh font-sans text-ink-950 antialiased">
    <div class="flex min-h-full flex-1 flex-col" x-data="{ mobileOpen: false }" @keydown.window.escape="mobileOpen = false">
        <p class="bg-ink-950 px-4 py-2.5 text-center text-[10px] font-bold uppercase leading-snug tracking-mega text-white/90 text-balance sm:text-xs">
            Women&apos;s performance gym wear · engineered fit · free returns on first order
        </p>

        <header class="sticky top-0 z-50 border-b border-white/[0.06] bg-ink-950/75 text-white shadow-[0_8px_32px_-8px_rgba(0,0,0,0.45)] backdrop-blur-2xl backdrop-saturate-150 supports-[backdrop-filter]:bg-ink-950/65">
            <div class="page-shell flex h-14 items-center justify-between gap-4 lg:h-16">
                <div class="flex min-w-0 items-center gap-6 lg:gap-10">
                    <a href="{{ route('shop.index') }}" class="font-display truncate text-lg font-bold uppercase tracking-mega text-white transition-opacity hover:opacity-90 lg:text-xl">
                        {{ config('app.name') }}
                    </a>

                    <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Primary">
                        <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'nav-link-active' : '' }}">Shop</a>
                        <a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.*') ? 'nav-link-active' : '' }}">
                            Cart
                            @if ($cartService->count() > 0)
                                <span class="ml-1 rounded-full bg-accent-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $cartService->count() }}</span>
                            @endif
                        </a>
                        @guest
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                        @endguest
                        @auth
                            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'nav-link-active' : '' }}">Orders</a>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'nav-link-active' : '' }}">Categories</a>
                                <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'nav-link-active' : '' }}">Products</a>
                                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'nav-link-active' : '' }}">Store orders</a>
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Team</a>
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
                            <span class="max-w-[9rem] truncate text-xs font-medium text-white/65" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                            <span class="rounded-full border border-white/15 bg-white/5 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white/90 backdrop-blur-sm">{{ auth()->user()->role }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="hidden lg:inline">
                            @csrf
                            <button type="submit" class="btn-outline-light px-4 py-2 text-[10px]">Log out</button>
                        </form>
                    @endguest

                    <button
                        type="button"
                        class="btn-ghost-inverse inline-flex size-10 items-center justify-center rounded-xl lg:hidden"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen"
                        aria-controls="mobile-nav"
                        aria-label="Toggle menu"
                    >
                        <svg x-show="!mobileOpen" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
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
                class="border-t border-white/10 bg-ink-950/95 backdrop-blur-xl lg:hidden"
            >
                <nav class="page-shell flex flex-col gap-0.5 py-4" aria-label="Mobile">
                    <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'nav-link-active' : '' }}">Shop</a>
                    <a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.*') ? 'nav-link-active' : '' }}">Cart @if ($cartService->count() > 0) ({{ $cartService->count() }}) @endif</a>
                    @guest
                        <a href="{{ route('home') }}" class="nav-link">Home</a>
                        <a href="{{ route('login') }}" class="nav-link">Log in</a>
                        <a href="{{ route('register') }}" class="btn-on-dark mt-3 justify-center">Join</a>
                    @else
                        <a href="{{ route('orders.index') }}" class="nav-link">Orders</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.categories.index') }}" class="nav-link">Categories</a>
                            <a href="{{ route('admin.products.index') }}" class="nav-link">Products</a>
                            <a href="{{ route('admin.orders.index') }}" class="nav-link">Store orders</a>
                            <a href="{{ route('admin.users.index') }}" class="nav-link">Team</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-white/10 pt-4">
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
            <div class="page-shell flex-1 pb-14 pt-1 lg:pb-20 lg:pt-2">
                @yield('content')
            </div>
        @endif

        <footer class="mt-auto border-t border-white/[0.07] bg-ink-950 text-white">
            <div class="page-shell grid gap-12 py-14 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10 lg:py-16">
                <div class="sm:col-span-2 lg:col-span-1">
                    <p class="font-display text-lg font-bold uppercase tracking-mega">{{ config('app.name') }}</p>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/55 text-pretty">Performance gym wear designed for women who train hard. Cut, fabric, and finish built for the floor.</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-white/35">Shop</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('shop.index') }}" class="text-white/65 transition-colors duration-200 hover:text-white">All women&apos;s</a></li>
                        <li><a href="{{ route('shop.index') }}#leggings" class="text-white/65 transition-colors duration-200 hover:text-white">Leggings</a></li>
                        <li><a href="{{ route('shop.index') }}#bras" class="text-white/65 transition-colors duration-200 hover:text-white">Sports bras</a></li>
                        <li><a href="{{ route('shop.index') }}#layers" class="text-white/65 transition-colors duration-200 hover:text-white">Layers</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-white/35">Help</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="#" class="text-white/65 transition-colors duration-200 hover:text-white">Shipping</a></li>
                        <li><a href="#" class="text-white/65 transition-colors duration-200 hover:text-white">Returns</a></li>
                        <li><a href="#" class="text-white/65 transition-colors duration-200 hover:text-white">Size guide</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-white/35">Account</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        @guest
                            <li><a href="{{ route('login') }}" class="text-white/65 transition-colors duration-200 hover:text-white">Log in</a></li>
                            <li><a href="{{ route('register') }}" class="text-white/65 transition-colors duration-200 hover:text-white">Create account</a></li>
                        @else
                            <li><a href="{{ route('orders.index') }}" class="text-white/65 transition-colors duration-200 hover:text-white">Orders</a></li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/[0.06] py-7">
                <div class="page-shell flex flex-col items-center justify-between gap-4 text-center text-[11px] leading-relaxed text-white/40 sm:flex-row sm:text-left">
                    <p class="text-balance">&copy; {{ date('Y') }} {{ config('app.name') }}. Women&apos;s gym wear.</p>
                    <p class="max-w-md text-pretty">Independent storefront demo — not affiliated with any third-party brand.</p>
                </div>
            </div>
        </footer>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
