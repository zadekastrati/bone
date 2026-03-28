@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('shop.index') }}">Shop</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <span class="text-ink-800">{{ $category->name }}</span>
    </nav>

    <x-page-header :title="$category->name" :subtitle="$category->description" />

    <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            @include('shop.partials.product-grid-card', ['product' => $product, 'showCategory' => false])
        @empty
            <p class="text-ink-600 sm:col-span-2 lg:col-span-3">No products in this category yet.</p>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $products->links() }}
    </div>
@endsection
