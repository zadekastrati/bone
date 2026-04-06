@extends('layouts.app')

@section('title', 'Home')

@section('content_outer', 'w-full max-w-none flex-1')

@section('content')
    {{-- Hero: full-bleed campaign block (layout inspired by major athletic retail sites) --}}
    <section
        class="relative flex min-h-[85vh] flex-col justify-end overflow-hidden pb-20 pt-28 text-ink-900 sm:min-h-[90vh] sm:justify-center sm:pb-28 sm:pt-20 lg:pt-24"
        style="background-image: linear-gradient(110deg, rgba(250, 247, 243, 0.92) 0%, rgba(250, 247, 243, 0.8) 42%, rgba(250, 247, 243, 0.5) 100%), url('https://images.pexels.com/photos/8436586/pexels-photo-8436586.jpeg?auto=compress&cs=tinysrgb&w=2000&dpr=2'); background-size: cover; background-position: center;"
    >
        <div class="relative mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-accent-600 sm:text-xs">Women&apos;s performance · gym to street</p>
            <h1 class="font-display mt-5 max-w-4xl text-5xl font-bold uppercase leading-[0.9] tracking-tight text-balance text-ink-950 sm:text-7xl md:text-8xl lg:text-9xl">
                Become your<br class="hidden sm:inline" /> personal best
            </h1>
            <p class="mx-auto mt-8 max-w-xl text-base leading-relaxed text-ink-600 text-pretty sm:mx-0 sm:text-lg sm:leading-relaxed">
                Kit that keeps up when the set gets ugly — compression, support, and layers that move with you from warm-up to last rep.
            </p>
            <div class="mt-10 flex flex-wrap items-center gap-3 sm:mt-12 sm:gap-4">
                <a href="{{ route('shop.index') }}" class="btn-primary inline-flex min-h-[3rem] px-10 py-3.5 text-xs sm:px-12">
                    Shop women&apos;s
                </a>
                <a href="{{ route('register') }}" class="btn-secondary min-h-[3rem] px-10 py-3.5 text-xs sm:px-12">Join the list</a>
            </div>
        </div>
    </section>

    {{-- Category strip: editorial labels + quick links --}}
    <section class="border-y border-zinc-200/80 bg-zinc-100/80 px-4 py-4 sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row sm:gap-8">
            <p class="text-center text-[10px] font-bold uppercase tracking-mega text-accent-700 sm:text-left">Trending</p>
            <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
                @foreach (['New arrivals' => route('shop.index') . '#new', 'Shop all' => route('shop.index'), 'Sports bras' => route('shop.index'), 'Layers' => route('shop.index')] as $label => $href)
                    <a href="{{ $href }}" class="rounded-full border border-zinc-200/90 bg-white/90 px-4 py-2 text-[10px] font-bold uppercase tracking-mega text-ink-700 shadow-sm transition hover:border-accent-300 hover:bg-zinc-50">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Shop by category --}}
    <section class="bg-gradient-to-b from-zinc-50 via-zinc-50/90 to-white px-4 py-16 sm:px-6 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-6 border-b border-ink-200/60 pb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-accent-600">Shop</p>
                    <h2 class="font-display mt-2 text-3xl font-bold uppercase tracking-wide text-ink-950 text-balance sm:text-4xl lg:text-5xl">Shop by category</h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-600 text-pretty sm:text-base">Pick your line — every category is built for how you train.</p>
                </div>
                <a href="{{ route('shop.index') }}" class="link-brand inline-flex shrink-0 text-sm font-bold uppercase tracking-mega">View all</a>
            </div>
            <div class="mt-12 grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4 lg:gap-6">
                @foreach ([
                    ['label' => 'Leggings', 'sub' => 'Sculpt & sweat', 'href' => route('shop.index'), 'image' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=1200&q=80'],
                    ['label' => 'Sports bras', 'sub' => 'High support', 'href' => route('shop.index'), 'image' => 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?auto=format&fit=crop&w=1200&q=80'],
                    ['label' => 'Tops', 'sub' => 'Crops & tanks', 'href' => route('shop.index'), 'image' => 'https://images.unsplash.com/photo-1518310952931-b1de897abd40?auto=format&fit=crop&w=1200&q=80'],
                    ['label' => 'Layers', 'sub' => 'Hoodies & jackets', 'href' => route('shop.index'), 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1200&q=80'],
                ] as $tile)
                    <a
                        href="{{ $tile['href'] }}"
                        class="group relative flex min-h-[260px] flex-col justify-end overflow-hidden rounded-3xl p-7 text-ink-900 shadow-float ring-1 ring-zinc-200/60 transition duration-500 ease-out-expo motion-reduce:transition-none motion-safe:hover:-translate-y-1 motion-safe:hover:shadow-[0_28px_56px_-16px_rgba(63,63,70,0.35)] motion-safe:hover:ring-accent-300/50"
                        style="background-image: linear-gradient(180deg, rgba(255,255,255,0.1) 0%, rgba(250,246,241,0.86) 74%, rgba(247,241,235,0.94) 100%), url('{{ $tile['image'] }}'); background-size: cover; background-position: center;"
                    >
                        <div class="absolute inset-0 bg-card-shine opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <span class="relative text-xs font-bold uppercase tracking-mega text-ink-600">{{ $tile['sub'] }}</span>
                        <span class="relative mt-2 font-display text-2xl font-bold uppercase tracking-wide lg:text-3xl">{{ $tile['label'] }}</span>
                        <span class="relative mt-4 inline-flex text-xs font-bold uppercase tracking-mega text-accent-800 underline-offset-4 transition-all group-hover:translate-x-0.5 group-hover:underline">Shop now →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Training modes: pill row --}}
    <section class="border-y border-ink-200/60 bg-white px-4 py-12 sm:px-6 lg:py-16">
        <div class="mx-auto max-w-5xl text-center">
            <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-ink-950 sm:text-3xl">How do you train?</h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-ink-600 text-pretty">Tap a focus — we&apos;ll drop you in the shop to build your kit.</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                @foreach (['Running', 'Lifting', 'HIIT', 'Pilates', 'Rest day'] as $tag)
                    <a
                        href="{{ route('shop.index') }}"
                        class="rounded-full border border-ink-200/90 bg-ink-50/80 px-5 py-2.5 text-[11px] font-bold uppercase tracking-mega text-ink-800 transition hover:border-accent-300 hover:bg-accent-50/60 hover:text-ink-950"
                    >
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Editorial band --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-zinc-100 via-zinc-100 to-zinc-50 px-4 py-24 text-ink-900 sm:px-6 lg:py-32">
        <div class="pointer-events-none absolute -right-20 -top-20 size-[28rem] rounded-full bg-accent-200/50 blur-3xl sm:size-[36rem]"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 size-64 rounded-full bg-white/60 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl text-center">
            <p class="text-[10px] font-bold uppercase tracking-mega text-accent-700">Why bone</p>
            <h2 class="font-display mt-4 text-3xl font-bold uppercase tracking-wide text-balance text-ink-950 sm:text-5xl lg:text-6xl">Built in the weight room mindset</h2>
            <p class="mx-auto mt-8 text-lg leading-relaxed text-ink-600 text-pretty sm:text-xl">
                Four-way stretch, squat-proof opacity, and seams that stay flat under the bar. Designed for women&apos;s bodies — not resized from a men&apos;s block.
            </p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-12 inline-flex px-10 py-3.5 text-sm sm:px-12">Explore the shop</a>
        </div>
    </section>

    {{-- Trust + SEO --}}
    <section class="border-t border-ink-200/80 bg-white px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-6 sm:grid-cols-3 sm:gap-6 lg:gap-8">
            @foreach ([
                ['t' => 'Fast dispatch', 'd' => 'Orders ship quickly on business days.'],
                ['t' => 'First order returns', 'd' => 'Full refund on your first full-price item if the fit is off.'],
                ['t' => 'Secure checkout', 'd' => 'Encrypted payments — cards and wallets supported.'],
            ] as $row)
                <div class="rounded-2xl border border-zinc-100/90 bg-zinc-50/60 p-8 text-center ring-1 ring-zinc-900/[0.04] transition-shadow duration-300 hover:shadow-soft sm:text-left lg:p-9">
                    <p class="font-display text-lg font-bold uppercase tracking-wide text-ink-950 lg:text-xl">{{ $row['t'] }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-ink-600 text-pretty">{{ $row['d'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mx-auto mt-16 max-w-3xl border-t border-ink-200/60 pt-14 text-center">
            <h3 class="font-display text-xl font-bold uppercase tracking-wide text-ink-950">Workout clothes for women who train</h3>
            <p class="mt-4 text-sm leading-relaxed text-ink-600 text-pretty">
                Functional kit for squats, sprints, and everything after. We obsess over fabric, fit, and finish so the only thing you&apos;re fighting for is the next rep — not your gear.
                {{ config('app.name') }} is an independent demo storefront and is not affiliated with any third-party brand.
            </p>
        </div>
    </section>
@endsection
