<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $__seoTitle = trim($__env->yieldContent('title'));
        $__seoTitle = $__seoTitle !== '' ? $__seoTitle.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel');
        $__seoDescription = trim($__env->yieldContent('meta_description'));
        $__seoDescription = $__seoDescription !== ''
            ? $__seoDescription
            : "A gear designed for performance, comfort, and confidence";
        $__seoImage = trim($__env->yieldContent('meta_image'));
        $__seoImage = $__seoImage !== '' ? $__seoImage : asset('logo.png');
    @endphp
    <title>{{ $__seoTitle }}</title>
    <meta name="description" content="{{ $__seoDescription }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpeg') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if (trim($__env->yieldContent('noindex')) !== '')
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type')) !== '' ? trim($__env->yieldContent('og_type')) : 'website' }}">
    <meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}">
    <meta property="og:title" content="{{ $__seoTitle }}">
    <meta property="og:description" content="{{ $__seoDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $__seoImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $__seoTitle }}">
    <meta name="twitter:description" content="{{ $__seoDescription }}">
    <meta name="twitter:image" content="{{ $__seoImage }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('structured_data')
</head>
@inject('cartService', \App\Services\CartService::class)
<body @class([
    'flex min-h-full flex-col font-sans text-base leading-relaxed text-ink-900 antialiased',
    'bg-white' => request()->routeIs('cart.index'),
    'bg-page-mesh bg-local' => ! request()->routeIs('cart.index'),
])>
    <div class="flex min-h-full flex-1 flex-col" x-data="{ mobileOpen: false }" @keydown.window.escape="mobileOpen = false">
        <x-store-promo-bar />

        <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-gradient-to-b from-zinc-50/95 to-zinc-100/80 text-ink-900 shadow-[0_8px_30px_-12px_rgba(94,82,74,0.14)] backdrop-blur-md backdrop-saturate-150 supports-[backdrop-filter]:bg-zinc-50/90">
            <div class="page-shell flex h-14 min-w-0 items-center gap-2 sm:gap-3 lg:h-[4.25rem] lg:gap-4">
                <a href="{{ route('home') }}" class="shrink-0 transition-opacity hover:opacity-80">
                    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" width="320" height="118" class="h-5 w-auto lg:h-6">
                </a>

                {{-- Middle: search + desktop nav share the flexible width so the cart column is never pushed off-screen --}}
                <div class="flex min-w-0 flex-1 items-center gap-2 lg:gap-4">

                    <nav class="hidden min-w-0 items-center gap-0.5 lg:flex" aria-label="Primary">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">{{ __('Home') }}</a>
                        <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'nav-link-active' : '' }}">{{ __('Shop') }}</a>
                        @auth
                            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'nav-link-active' : '' }}">{{ __('Orders') }}</a>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">{{ __('Dashboard') }}</a>
                            @endif
                        @endauth
                    </nav>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <x-store.locale-select class="hidden sm:block" />
                    <x-store.country-select class="hidden sm:block" />

                    @guest
                        <a href="{{ route('login') }}" class="btn-ghost-inverse hidden px-3 py-2 sm:inline-flex">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="btn-on-dark hidden px-4 py-2.5 sm:inline-flex">{{ __('Join') }}</a>
                    @else
                        <div class="relative hidden lg:block" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button
                                type="button"
                                @click="open = !open"
                                :aria-expanded="open"
                                class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 text-xs font-medium text-ink-700 transition-colors hover:bg-zinc-200/50 hover:text-ink-900"
                            >
                                <span class="flex size-8 items-center justify-center rounded-full bg-ink-900 text-[11px] font-semibold uppercase text-white">
                                    {{ Str::substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="max-w-[9rem] truncate">{{ auth()->user()->name }}</span>
                                <x-icons.chevron-down class="size-4 text-ink-500 transition-transform" x-bind:class="{ 'rotate-180': open }" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="absolute right-0 top-full z-50 mt-2 w-48 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white py-1.5 shadow-soft ring-1 ring-ink-950/5"
                            >
                                <div class="truncate border-b border-zinc-100 px-4 py-2 text-xs text-ink-500" title="{{ auth()->user()->email }}">
                                    {{ auth()->user()->email }}
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-ink-800 transition-colors hover:bg-zinc-100">
                                    <x-icons.user-circle class="size-4 text-ink-500" />
                                    {{ __('My profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-ink-800 transition-colors hover:bg-zinc-100">
                                        <svg class="size-4 text-ink-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3" />
                                        </svg>
                                        {{ __('Log out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
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
                            <span>{{ __('Search products') }}</span>
                            <svg class="size-5 text-ink-500 transition-colors duration-200" :class="{ 'text-ink-900': openSearch }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197M15.803 15.803A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                        <div x-show="openSearch" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="mt-2 pb-2">
                            <x-store-search-form variant="drawer" />
                        </div>
                    </div>
                    <div class="mb-2 flex gap-2">
                        <x-store.locale-select class="[&_select]:w-full" />
                        <x-store.country-select class="[&_select]:w-full" />
                    </div>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">{{ __('Home') }}</a>
                    <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'nav-link-active' : '' }}">{{ __('Shop') }}</a>
                    @guest
                        <a href="{{ route('login') }}" class="nav-link">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="btn-on-dark mt-3 justify-center">{{ __('Join') }}</a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'nav-link-active' : '' }}">{{ __('Profile') }}</a>
                        <a href="{{ route('orders.index') }}" class="nav-link">{{ __('Orders') }}</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">{{ __('Dashboard') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-zinc-200/80 pt-4">
                            @csrf
                            <button type="submit" class="btn-outline-light w-full justify-center">{{ __('Log out') }}</button>
                        </form>
                    @endguest
                </nav>
            </div>
        </header>

        <main class="flex flex-1 flex-col">
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
                    <p class="text-xs font-bold uppercase tracking-wide text-red-900">{{ __('Please fix the following') }}</p>
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
        </main>

        <footer class="mt-auto border-t border-zinc-200/80 bg-gradient-to-b from-zinc-100 via-zinc-50 to-zinc-50 text-ink-800">
            <div class="page-shell grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12 lg:py-20">
                <div class="sm:col-span-2 lg:col-span-1">
                    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" width="320" height="118" class="h-5 w-auto lg:h-6">
                    <p class="mt-4 flex items-center gap-2 text-pretty text-sm text-ink-600">
                        <x-icons.envelope class="h-4 w-4 shrink-0" />
                        info@bone-active.com
                    </p>
                    <div class="mt-4 flex items-center gap-4 text-pretty text-ink-600">
                        <a href="https://www.instagram.com/bone.active?igsi=M215OG4wZG1uYzI3" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-accent-700" aria-label="{{ __('Follow us on Instagram') }}">
                            <x-icons.instagram class="h-4 w-4" />
                        </a>
                        <a href="https://www.facebook.com/profile.php?id=61593204533823" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-accent-700" aria-label="{{ __('Follow us on Facebook') }}">
                            <x-icons.facebook class="h-4 w-4" />
                        </a>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-700">{{ __('Shop') }}</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('cart.index') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Bag / cart') }}</a></li>
                        <li><a href="{{ route('shop.index') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('All women\'s') }}</a></li>
                        @foreach ($footerCategories ?? [] as $footerCategory)
                            <li><a href="{{ route('shop.category', $footerCategory) }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ Str::ucfirst(Str::lower($footerCategory->name)) }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-700">{{ __('Information') }}</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('about') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('About us') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Contact us') }}</a></li>
                        <li><a href="{{ route('terms') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Terms & conditions') }}</a></li>
                        <li><a href="{{ route('returns') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Returns Policy') }}</a></li>
                        <li><a href="{{ route('size-guide') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Size guide') }}</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-700">{{ __('Account') }}</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        @guest
                            <li><a href="{{ route('login') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Log in') }}</a></li>
                            <li><a href="{{ route('register') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Create account') }}</a></li>
                        @else
                            <li><a href="{{ route('orders.index') }}" class="text-ink-600 transition-colors duration-200 hover:text-accent-700">{{ __('Orders') }}</a></li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div class="border-t border-zinc-200/70 py-7">
                <div class="page-shell flex flex-col items-center justify-between gap-4 text-center text-[11px] leading-relaxed text-ink-500 sm:flex-row sm:text-left">
                    <p class="text-balance">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/visa-verified.png') }}" alt="Verified by Visa" class="h-5 w-auto">
                        <img src="{{ asset('images/mastercard-securecode.png') }}" alt="MasterCard SecureCode" class="h-5 w-auto">
                    </div>
                </div>
            </div>
        </footer>
    </div>

    {{-- Floating bag: fixed to viewport (all breakpoints), high z-index --}}
    <x-cart-fab :count="$cartService->count()" />

    <x-confirm-modal />

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
