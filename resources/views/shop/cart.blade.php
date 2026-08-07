@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <x-page-header title="Cart" subtitle="Review items before checkout. Prices reflect the catalog at the time you added each line.">
        <a href="{{ route('shop.index') }}" class="btn-secondary">Continue shopping</a>
    </x-page-header>

    <div id="cart-body">
        @include('shop.partials.cart-body')
    </div>
@endsection
