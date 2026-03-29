<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @hasSection('title')
            @yield('title') —
        @endif
        Admin · {{ config('app.name', 'Laravel') }}
    </title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=oswald:500,600,700|outfit:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-zinc-50 font-sans text-base leading-relaxed text-ink-900 antialiased lg:h-full lg:overflow-hidden">
    <div
        class="admin-layout"
        x-data="{ sidebarOpen: false }"
        @keydown.window.escape="sidebarOpen = false"
    >
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-zinc-950/25 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
            x-cloak
        ></div>

        <aside
            class="admin-layout__sidebar"
            :class="{ 'is-open': sidebarOpen }"
        >
            <div class="flex h-14 shrink-0 items-center gap-2 border-b border-zinc-200/80 bg-zinc-50/80 px-4 lg:h-16">
                <a href="{{ route('home') }}" @click="sidebarOpen = false" class="font-display text-lg font-bold uppercase tracking-mega text-ink-900">
                    {{ config('app.name') }}
                </a>
                <span class="rounded-md bg-accent-200/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-accent-800 ring-1 ring-zinc-300/50">Admin</span>
            </div>

            <div class="shrink-0 border-b border-zinc-200/80 px-3 pb-3 pt-2">
                <a
                    href="{{ route('shop.index') }}"
                    @click="sidebarOpen = false"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200/90 bg-white/90 px-3 py-2.5 text-xs font-semibold text-ink-700 shadow-sm transition hover:border-accent-300 hover:bg-zinc-100/80 hover:text-ink-900"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    View storefront
                </a>
            </div>

            <nav class="shrink-0 space-y-1 px-3 py-4 pb-6" aria-label="Admin">
                <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-mega text-accent-700">Overview</p>
                <a href="{{ route('admin.dashboard') }}" @click="sidebarOpen = false" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'admin-sidebar-link-active' : '' }}">
                    <svg class="size-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25a2.25 2.25 0 0 1-2.25 2.25h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    Dashboard
                </a>

                <p class="mt-6 px-3 pb-2 text-[10px] font-bold uppercase tracking-mega text-accent-700">Catalog</p>
                <a href="{{ route('admin.products.index') }}" @click="sidebarOpen = false" class="admin-sidebar-link {{ request()->routeIs('admin.products.*', 'admin.categories.*') ? 'admin-sidebar-link-active' : '' }}">
                    <svg class="size-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    Products
                </a>

                <p class="mt-6 px-3 pb-2 text-[10px] font-bold uppercase tracking-mega text-accent-700">Sales</p>
                <a href="{{ route('admin.orders.index') }}" @click="sidebarOpen = false" class="admin-sidebar-link {{ request()->routeIs('admin.orders.*') ? 'admin-sidebar-link-active' : '' }}">
                    <svg class="size-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.25 10.5a3.75 3.75 0 1 1 7.5 0" /></svg>
                    Orders
                </a>

                <p class="mt-6 px-3 pb-2 text-[10px] font-bold uppercase tracking-mega text-accent-700">People</p>
                <a href="{{ route('admin.users.index') }}" @click="sidebarOpen = false" class="admin-sidebar-link {{ request()->routeIs('admin.users.*') ? 'admin-sidebar-link-active' : '' }}">
                    <svg class="size-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    Users
                </a>
            </nav>
        </aside>

        <div class="admin-layout__main">
            <header class="sticky top-0 z-30 flex h-14 shrink-0 items-center justify-between gap-3 border-b border-zinc-200/80 bg-white/95 px-4 shadow-sm backdrop-blur-md sm:px-6 lg:h-16 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex size-10 items-center justify-center rounded-xl border border-zinc-200/90 bg-zinc-50/80 text-ink-700 shadow-sm lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Open menu"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium uppercase tracking-mega text-accent-700">Admin</p>
                        <p class="truncate font-display text-lg font-semibold text-ink-900 sm:text-xl">@yield('title', 'Dashboard')</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-2.5 py-2 text-xs font-semibold text-ink-700 shadow-sm transition hover:border-accent-300 hover:bg-zinc-50 sm:gap-2 sm:px-3"
                        title="Open the customer storefront"
                    >
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        <span class="hidden sm:inline">View storefront</span>
                        <span class="sm:hidden">Shop</span>
                    </a>
                    <span class="hidden max-w-[10rem] truncate text-sm text-ink-600 sm:block" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                    <span class="hidden rounded-full bg-zinc-100/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-accent-800 ring-1 ring-zinc-200/80 sm:inline">{{ auth()->user()->role }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-xl border border-zinc-200/90 bg-white px-3 py-2 text-xs font-semibold text-ink-700 shadow-sm transition hover:border-accent-300 hover:bg-zinc-50">Log out</button>
                    </form>
                </div>
            </header>

            <main class="mx-auto flex min-w-0 w-full max-w-[1600px] flex-1 flex-col px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
                @if (session('success'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="mb-6 flex items-start justify-between gap-4 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-950 shadow-sm"
                        role="status"
                    >
                        <span class="pt-0.5">{{ session('success') }}</span>
                        <button type="button" class="text-emerald-800 hover:text-emerald-950" @click="show = false" aria-label="Dismiss">×</button>
                    </div>
                @endif
                @if (session('error'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="mb-6 flex items-start justify-between gap-4 rounded-xl border border-red-200/80 bg-red-50 px-4 py-3 text-sm text-red-950 shadow-sm"
                        role="alert"
                    >
                        <span class="pt-0.5">{{ session('error') }}</span>
                        <button type="button" class="text-red-800" @click="show = false" aria-label="Dismiss">×</button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200/80 bg-red-50 px-4 py-3 text-sm text-red-950 shadow-sm" role="alert">
                        <p class="text-xs font-bold uppercase tracking-wide text-red-900">Please fix the following</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <style>[x-cloak]{display:none!important;}</style>
</body>
</html>
