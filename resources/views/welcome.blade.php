@extends('layouts.app')

@section('title', 'Home')

@section('content_outer', 'w-full max-w-none flex-1')

@section('content')
    <section class="relative flex min-h-[78vh] flex-col justify-center overflow-hidden bg-hero-radial px-4 pb-24 pt-20 text-white sm:min-h-[85vh] sm:px-6 sm:pb-32 sm:pt-28 lg:px-8 lg:pt-36">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative mx-auto w-full max-w-5xl text-center">
            <p class="text-xs font-bold uppercase tracking-mega text-accent-300/95">Women only · gym to street</p>
            <h1 class="font-display mt-6 text-5xl font-bold uppercase leading-[0.92] tracking-tight text-balance sm:text-7xl lg:text-8xl">
                Train in<br class="hidden sm:inline" /> your power
            </h1>
            <p class="mx-auto mt-8 max-w-xl text-base leading-relaxed text-white/70 text-pretty sm:text-lg sm:leading-relaxed">
                Compression that holds shape. Bras that lock in support. Layers that breathe from warm-up to last rep.
            </p>
            <div class="mt-12 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                <a href="{{ route('shop.index') }}" class="btn-on-dark px-8 py-3.5 text-sm sm:px-10">Shop women&apos;s</a>
                <a href="{{ route('register') }}" class="btn-outline-light px-8 py-3.5 text-sm sm:px-10">Join the list</a>
            </div>
        </div>
    </section>

    <section class="border-y border-white/5 bg-ink-950 px-4 py-5 text-center sm:px-6">
        <p class="mx-auto max-w-3xl text-[11px] font-bold uppercase leading-relaxed tracking-mega text-white/45 text-balance sm:text-xs">New drop every month · limited runs · no restock on select colourways</p>
    </section>

    <section class="bg-gradient-to-b from-zinc-100 to-zinc-100/80 px-4 py-16 sm:px-6 lg:py-28">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-xl">
                    <h2 class="font-display text-3xl font-bold uppercase tracking-wide text-ink-950 text-balance sm:text-4xl lg:text-5xl">Shop by category</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink-600 text-pretty sm:text-base">Built for squats, sprints, and everything after. Tap a line to jump in-store.</p>
                </div>
                <a href="{{ route('shop.index') }}" class="link-brand inline-flex shrink-0 text-sm font-bold uppercase tracking-mega">View all</a>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                @foreach ([
                    ['label' => 'Leggings', 'sub' => 'Sculpt & sweat', 'href' => route('shop.index') . '#leggings', 'tone' => 'from-zinc-800 to-zinc-950'],
                    ['label' => 'Sports bras', 'sub' => 'High support', 'href' => route('shop.index') . '#bras', 'tone' => 'from-accent-900/90 to-ink-950'],
                    ['label' => 'Tops', 'sub' => 'Crops & tanks', 'href' => route('shop.index') . '#tops', 'tone' => 'from-neutral-700 to-neutral-900'],
                    ['label' => 'Layers', 'sub' => 'Hoodies & jackets', 'href' => route('shop.index') . '#layers', 'tone' => 'from-zinc-700 to-zinc-900'],
                ] as $tile)
                    <a
                        href="{{ $tile['href'] }}"
                        class="group relative flex min-h-[240px] flex-col justify-end overflow-hidden rounded-3xl bg-gradient-to-br p-7 text-white shadow-float ring-1 ring-white/10 transition-all duration-500 ease-out-expo motion-reduce:transition-none {{ $tile['tone'] }} motion-safe:hover:-translate-y-1 motion-safe:hover:shadow-[0_32px_64px_-16px_rgba(0,0,0,0.45)] motion-safe:hover:ring-accent-400/30"
                    >
                        <div class="absolute inset-0 bg-card-shine opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <span class="relative text-xs font-bold uppercase tracking-mega text-white/55">{{ $tile['sub'] }}</span>
                        <span class="relative mt-2 font-display text-2xl font-bold uppercase tracking-wide lg:text-3xl">{{ $tile['label'] }}</span>
                        <span class="relative mt-4 inline-flex text-xs font-bold uppercase tracking-mega text-white/90 underline-offset-4 transition-all group-hover:translate-x-0.5 group-hover:underline">Shop now →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden bg-ink-900 px-4 py-24 text-white sm:px-6 lg:py-32">
        <div class="pointer-events-none absolute -right-20 -top-20 size-[28rem] rounded-full bg-accent-500/15 blur-3xl sm:size-[36rem]"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 size-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl text-center">
            <h2 class="font-display text-3xl font-bold uppercase tracking-wide text-balance sm:text-5xl lg:text-6xl">Fabric that survives the week</h2>
            <p class="mx-auto mt-8 text-lg leading-relaxed text-white/60 text-pretty sm:text-xl">
                Four-way stretch, squat-proof opacity, and seams that stay flat under barbells. Designed for women&apos;s bodies — not resized from a men&apos;s block.
            </p>
            <a href="{{ route('shop.index') }}" class="btn-on-dark mt-12 inline-flex px-10 py-3.5 text-sm sm:px-12">Explore the shop</a>
        </div>
    </section>

    <section class="border-t border-ink-200/80 bg-white px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-8 sm:grid-cols-3 sm:gap-6 lg:gap-8">
            @foreach ([
                ['t' => 'Fast dispatch', 'd' => 'Orders ship within 24h on business days.'],
                ['t' => 'First order returns', 'd' => 'Full refund on your first full-price item if the fit is off.'],
                ['t' => 'Secure checkout', 'd' => 'Encrypted payments — cards and wallets supported.'],
            ] as $row)
                <div class="rounded-2xl border border-ink-100 bg-zinc-50/50 p-8 text-center ring-1 ring-ink-950/[0.03] transition-shadow duration-300 hover:shadow-soft sm:text-left lg:p-9">
                    <p class="font-display text-lg font-bold uppercase tracking-wide text-ink-950 lg:text-xl">{{ $row['t'] }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-ink-600 text-pretty">{{ $row['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
