@extends('layouts.app')

@section('title', __('Confirming your payment'))
@section('noindex', 'true')

{{--
    Re-visiting this same page re-runs PaymentController::quipuReturn(), which
    re-confirms with Quipu — so an automatic refresh is what actually moves a
    stuck "gateway didn't answer in time" case forward, instead of leaving the
    customer stranded on a static page with nothing left to do but guess.
    Once resolved either way, the next refresh naturally lands on the
    success or failure page instead of this one.
--}}
@section('content')
    <div class="mx-auto max-w-xl py-16 text-center" x-data x-init="setTimeout(() => window.location.reload(), 8000)">
        <div class="panel p-10">
            <h1 class="heading-page mb-4">{{ __('Confirming your payment') }}</h1>
            <p class="text-sm leading-relaxed text-ink-700">
                {{ __("Thanks — we're confirming your card payment for order :number. This page will update itself automatically, usually within a few seconds — no need to refresh.", ['number' => $order->order_number]) }}
            </p>
            <p class="mt-4 text-xs leading-relaxed text-ink-500">
                {{ __('Taking longer than expected? If this page is still here in a few minutes,') }}
                <a href="{{ route('contact') }}" class="font-semibold text-accent-700 underline">{{ __('contact us') }}</a>
                {{ __('with your order number and we\'ll check on it for you.') }}
            </p>
        </div>
    </div>
@endsection
