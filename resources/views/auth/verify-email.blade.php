@extends('layouts.app')

@section('title', __('Verify your email'))
@section('noindex', 'true')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ __('Verify your email') }}</h1>
            <p class="text-muted mt-2">{{ __('We need to confirm your email before you can place an order. Check your inbox for a link from us. If you don\'t see it, check spam or request a new email below.') }}</p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <p class="mt-6 rounded-2xl border border-emerald-200/80 bg-emerald-50/95 px-4 py-3 text-sm text-emerald-900 shadow-soft ring-1 ring-emerald-500/10">
                {{ __('A new verification link has been sent to your email address.') }}
            </p>
        @endif

        <div class="panel mt-8 space-y-5">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-primary w-full py-3">{{ __('Resend verification email') }}</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary w-full py-3">{{ __('Log out') }}</button>
            </form>
        </div>

        <p class="mt-8 text-center text-sm text-ink-600">
            <a href="{{ route('cart.index') }}" class="link-brand">{{ __('Back to cart') }}</a>
        </p>
    </div>
@endsection
