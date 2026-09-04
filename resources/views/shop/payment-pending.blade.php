@extends('layouts.app')

@section('title', __('Confirming your payment'))
@section('noindex', 'true')

@section('content')
    <div class="mx-auto max-w-xl py-16 text-center">
        <div class="panel p-10">
            <h1 class="heading-page mb-4">{{ __('Confirming your payment') }}</h1>
            <p class="text-sm leading-relaxed text-ink-700">
                {{ __("Thanks — we're confirming your card payment for order :number. You'll receive an email confirmation shortly once it's complete.", ['number' => $order->order_number]) }}
            </p>
        </div>
    </div>
@endsection
