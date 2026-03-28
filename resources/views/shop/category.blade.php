@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span class="mx-1.5 text-ink-300" aria-hidden="true">/</span>
        <a href="{{ route('shop.index') }}">Shop</a>
        <span class="mx-1.5 text-ink-300" aria-hidden="true">/</span>
        <span class="text-ink-800" aria-current="page">{{ $category->name }}</span>
    </nav>

    <x-page-header :title="$category->name" :subtitle="$category->description" />

    @if ($products->isNotEmpty())
        <ul class="mt-10 grid list-none gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
            @foreach ($products as $product)
                <li class="min-w-0">
                    @include('shop.partials.product-grid-card', ['product' => $product, 'showCategory' => false])
                </li>
            @endforeach
        </ul>
    @else
        <p class="mt-10 rounded-2xl border border-pink-200/70 bg-pink-50/50 px-6 py-12 text-center text-ink-600">No products in this category yet.</p>
    @endif

    @if ($products->isNotEmpty())
        <div class="pagination-wrap">
            {{ $products->links() }}
        </div>
    @endif
@endsection
