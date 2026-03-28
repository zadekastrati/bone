<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount(['activeProducts'])
            ->get();

        $featured = Product::query()
            ->where('is_active', true)
            ->with(['category', 'images'])
            ->latest()
            ->take(12)
            ->get();

        return view('shop.index', compact('categories', 'featured'));
    }

    public function category(Category $category): View
    {
        $this->authorize('view', $category);

        $products = $category->activeProducts()
            ->with(['images'])
            ->latest()
            ->paginate(12);

        return view('shop.category', compact('category', 'products'));
    }

    public function product(Category $category, Product $product): View
    {
        abort_unless($product->category_id === $category->id, 404);

        $this->authorize('view', $product);

        $product->load(['images', 'variants']);

        $variantsByColor = $product->variants->groupBy('color');

        $stockByKey = $product->variants->mapWithKeys(
            fn ($v) => [$v->color.'|'.$v->size => $v->stock_quantity]
        );

        return view('shop.product', compact('category', 'product', 'variantsByColor', 'stockByKey'));
    }
}
