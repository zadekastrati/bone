@extends('layouts.app')

@section('title', __('Home'))

@section('content_outer', 'w-full max-w-none flex-1')

@section('content')
    {{-- Hero: full-bleed campaign block (layout inspired by major athletic retail sites) --}}
    <section class="relative flex min-h-[85vh] flex-col justify-end overflow-hidden pb-20 pt-28 text-white sm:min-h-[90vh] sm:justify-center sm:pb-28 sm:pt-20 lg:pt-24">
        <video
            id="hero-banner-video"
            class="absolute inset-0 h-full w-full object-cover"
            autoplay
            muted
            loop
            playsinline
        >
            {{--
                Encoded specifically for mobile startup speed: the original
                mobile export was ~25 Mbps/60fps (70MB) — fine on desktop
                wifi/broadband, but far more than typical phone networks can
                sustain, causing the noticeable delay before autoplay/loop
                could start smoothly. Re-encoded at 30fps/~2.2 Mbps (6MB,
                same resolution/dimensions, audio stripped since the element
                is muted anyway) — visually identical, ~11x lighter. Desktop's
                source below is untouched.
            --}}
            <source src="{{ Storage::disk('public')->url('bone-selected3/vertical-hero-mobile.mp4') }}" type="video/mp4" media="(max-width: 767px)" />
            <source src="{{ Storage::disk('public')->url('Horizontal_1.mp4') }}" type="video/mp4" />
        </video>
        <script>
            {{--
                On a genuinely cold load (no cache yet), some mobile browsers
                don't reliably honor the media="" match on <source> above —
                they can briefly commit to/fetch the desktop landscape source
                first (visible as the video looking "zoomed in", since a
                landscape video cropped via object-cover into a portrait box
                loses most of its width), before eventually settling on the
                right one. A refresh doesn't show this because the correct
                file is then already cache-warm. Setting .src directly here,
                as early as possible (this script runs immediately after the
                video tag, before the browser continues on to other page
                resources), removes that ambiguity entirely — the browser
                never has more than one candidate source to consider. The
                <source> tags above are untouched and still serve as the
                fallback for the rare case JS is unavailable.
            --}}
            (function () {
                var video = document.getElementById('hero-banner-video');
                if (!video) return;
                var isMobile = window.matchMedia('(max-width: 767px)').matches;
                video.src = isMobile
                    ? @json(Storage::disk('public')->url('bone-selected3/vertical-hero-mobile.mp4'))
                    : @json(Storage::disk('public')->url('Horizontal_1.mp4'));
                video.load();
            })();
        </script>
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-black/10"></div>
        <div class="relative mx-auto mt-8 w-full max-w-6xl px-4 sm:mt-14 sm:px-6 lg:px-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-white drop-shadow-[0_2px_10px_rgba(0,0,0,0.65)] sm:text-xs">{{ __('Women\'s performance · gym to street') }}</p>
            <h1 class="font-display mt-5 max-w-4xl text-5xl font-bold uppercase {{ app()->getLocale() === 'sq' ? 'leading-[1.3]' : 'leading-[0.9]' }} tracking-tight text-balance text-white drop-shadow-[0_4px_16px_rgba(0,0,0,0.55)] sm:text-7xl md:text-8xl lg:text-9xl">
                {!! __('Become your<br class="hidden sm:inline" /> personal best') !!}
            </h1>
            <p class="mx-auto mt-8 max-w-xl text-base leading-relaxed text-white/90 text-pretty sm:mx-0 sm:text-lg sm:leading-relaxed">
                {{ __('A gear designed for performance, comfort, and confidence') }}
            </p>
            <div class="mt-10 flex flex-wrap items-center gap-3 sm:mt-12 sm:gap-4">
                <a href="{{ route('shop.index') }}" class="btn-primary inline-flex min-h-[3rem] px-10 py-3.5 text-xs sm:px-12">
                    {{ __('Shop women\'s') }}
                </a>
                @guest
                    <a href="{{ route('register') }}" class="btn-secondary min-h-[3rem] px-10 py-3.5 text-xs sm:px-12">{{ __('Join the list') }}</a>
                @endguest
            </div>
        </div>
    </section>

    {{-- Category strip: editorial labels + quick links --}}
    <section class="border-y border-zinc-200/80 bg-zinc-100/80 px-4 py-4 sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row sm:gap-8">
            <p class="text-center text-[10px] font-bold uppercase tracking-mega text-accent-700 sm:text-left">{{ __('Trending') }}</p>
            <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                <a href="{{ route('shop.index') }}" class="rounded-full border border-zinc-200/90 bg-white/90 px-4 py-2 text-[10px] font-bold uppercase tracking-mega text-ink-700 shadow-sm transition hover:border-accent-300 hover:bg-zinc-50">{{ __('Shop all') }}</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('shop.category', $cat) }}" class="rounded-full border border-zinc-200/90 bg-white/90 px-4 py-2 text-[10px] font-bold uppercase tracking-mega text-ink-700 shadow-sm transition hover:border-accent-300 hover:bg-zinc-50">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Shop by category --}}
    <section class="bg-gradient-to-b from-zinc-50 via-zinc-50/90 to-white px-4 py-16 sm:px-6 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-6 border-b border-ink-200/60 pb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-accent-600">{{ __('Shop') }}</p>
                    <h2 class="font-display mt-2 text-3xl font-bold uppercase tracking-wide text-ink-950 text-balance sm:text-4xl lg:text-5xl">{{ __('Shop by category') }}</h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-600 text-pretty sm:text-base">{{ __('Pick your lane, every category is built for how you train.') }}</p>
                </div>
                <a href="{{ route('shop.index') }}" class="link-brand inline-flex shrink-0 text-sm font-bold uppercase tracking-mega">{{ __('View all') }}</a>
            </div>
            <div class="mt-12 grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-5 lg:gap-6">
                @foreach ($categories as $cat)
                    @php
                        // gridImageUrl(): resized JPEG instead of the raw
                        // multi-MB camera-original upload — this grid shows
                        // every category's image at once, and the originals
                        // were causing a multi-second stall on first paint
                        // (desktop especially, where all columns are visible
                        // simultaneously). Same visual result, just lighter.
                        $overlay = 'linear-gradient(180deg, rgba(255,255,255,0.1) 0%, rgba(250,246,241,0.86) 74%, rgba(247,241,235,0.94) 100%)';
                        $bgImage = $cat->gridImageUrl() ? "{$overlay}, url('{$cat->gridImageUrl()}')" : $overlay;
                    @endphp
                    <a
                        href="{{ route('shop.category', $cat) }}"
                        class="group relative flex min-h-[260px] flex-col justify-end overflow-hidden rounded-3xl p-7 text-ink-900 shadow-float ring-1 ring-zinc-200/60 transition duration-500 ease-out-expo motion-reduce:transition-none motion-safe:hover:-translate-y-1 motion-safe:hover:shadow-[0_28px_56px_-16px_rgba(63,63,70,0.35)] motion-safe:hover:ring-accent-300/50"
                        style="background-image: {{ $bgImage }}; background-size: cover; background-position: center;"
                    >
                        <div class="absolute inset-0 bg-card-shine opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        @if ($cat->description)
                            <span class="relative text-xs font-bold uppercase tracking-mega text-ink-600">{{ $cat->description }}</span>
                        @endif
                        <span class="relative mt-2 font-display text-2xl font-bold uppercase tracking-wide lg:text-3xl">{{ $cat->name }}</span>
                        <span class="relative mt-4 inline-flex text-xs font-bold uppercase tracking-mega text-accent-800 underline-offset-4 transition-all group-hover:translate-x-0.5 group-hover:underline">{{ __('Shop now →') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Training modes: pill row — only tags with at least one active product show up here (see routes/web.php) --}}
    @if ($trainingTags->isNotEmpty())
        <section class="border-y border-ink-200/60 bg-white px-4 py-12 sm:px-6 lg:py-16">
            <div class="mx-auto max-w-5xl text-center">
                <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-ink-950 sm:text-3xl">{{ __('How do you move?') }}</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm text-ink-600 text-pretty">{{ __('Choose your activity and find your fit.') }}</p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                    @foreach ($trainingTags as $row)
                        <a
                            href="{{ route('shop.index', ['training' => $row['tag']->value]) }}"
                            class="rounded-full border border-ink-200/90 bg-ink-50/80 px-5 py-2.5 text-[11px] font-bold uppercase tracking-mega text-ink-800 transition hover:border-accent-300 hover:bg-accent-50/60 hover:text-ink-950"
                        >
                            {{ $row['tag']->label() }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Editorial band --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-zinc-100 via-zinc-100 to-zinc-50 px-4 py-24 text-ink-900 sm:px-6 lg:py-32">
        <div class="pointer-events-none absolute -right-20 -top-20 size-[28rem] rounded-full bg-accent-200/50 blur-3xl sm:size-[36rem]"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 size-64 rounded-full bg-white/60 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl text-center">
            <p class="text-[10px] font-bold uppercase tracking-mega text-accent-700">{{ __('Why bone') }}</p>
            <h2 class="font-display mt-4 text-3xl font-bold uppercase tracking-wide text-balance text-ink-950 sm:text-5xl lg:text-6xl">{{ __('Built for movement, designed for you') }}</h2>
            <p class="mx-auto mt-8 text-lg leading-relaxed text-ink-600 text-pretty sm:text-xl">
                {{ __('Four-way stretch. Squat-proof coverage. Breathable, moisture-wicking fabrics. Designed for women and built to move with you.') }}
            </p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-12 inline-flex px-10 py-3.5 text-sm sm:px-12">{{ __('Explore the shop') }}</a>
        </div>
    </section>

    {{-- Trust + SEO --}}
    <section class="border-t border-ink-200/80 bg-white px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-6 sm:grid-cols-3 sm:gap-6 lg:gap-8">
            @foreach ([
                ['t' => __('Fast dispatch'), 'd' => __('Ships within 2-3 business days.')],
                ['t' => __('First fit guarantee'), 'd' => __('Not the right fit? Your first full-price item, fully refunded.'), 'strong' => true],
                ['t' => __('Secure checkout'), 'd' => __('Encrypted payments, cards and wallets supported.')],
            ] as $row)
                <div class="rounded-2xl border border-zinc-100/90 bg-zinc-50/60 p-8 text-center ring-1 ring-zinc-900/[0.04] transition-shadow duration-300 hover:shadow-soft sm:text-left lg:p-9">
                    <p class="font-display text-lg font-bold uppercase tracking-wide lg:text-xl {{ ($row['strong'] ?? false) ? 'text-black' : 'text-ink-950' }}">{{ $row['t'] }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-ink-600 text-pretty">{{ $row['d'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mx-auto mt-16 max-w-3xl border-t border-ink-200/60 pt-14 text-center">
            <h3 class="font-display text-xl font-bold uppercase tracking-wide text-ink-950">{{ __('Women\'s activewear designed for the way you move') }}</h3>
            <p class="mt-4 text-sm leading-relaxed text-ink-600 text-pretty">
                {{ __('Performance meets comfort in thoughtful fabrics and considered fits, giving you the freedom and confidence to move through every workout and whatever comes next.') }}
            </p>
        </div>
    </section>
@endsection
