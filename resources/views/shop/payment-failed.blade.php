@extends('layouts.app')

@section('title', __('Payment not completed'))
@section('noindex', 'true')

@section('content')
    <div class="mx-auto max-w-xl py-16 text-center">
        <div class="panel p-10">
            <h1 class="heading-page mb-4">{{ __('Payment not completed') }}</h1>
            <p class="text-sm leading-relaxed text-ink-700">
                {{ __("We couldn't confirm your card payment for order :number. You haven't been charged — please try again, or choose a different payment method.", ['number' => $order->order_number]) }}
            </p>
            <a href="{{ route('cart.index') }}" class="btn-primary mt-8 inline-flex px-8 py-3">
                {{ __('Back to cart') }}
            </a>
        </div>
    </div>
@endsection
