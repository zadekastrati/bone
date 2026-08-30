<?php

namespace App\Http\Controllers;

use App\Enums\TrainingTag;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /** @var list<string> */
    private const SORT_OPTIONS = ['newest', 'oldest', 'price_asc', 'price_desc'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) > 200) {
            $q = mb_substr($q, 0, 200);
        }

        $sort = $this->resolveSort($request);
        $training = $this->resolveTraining($request);

        $searchResults = null;
        if ($q !== '') {
            $searchResults = $this->applySort(
                Product::query()
                    ->where('is_active', true)
                    ->search($q)
                    ->with(['category', 'images'])
                    ->withSum('variants', 'stock_quantity'),
                $sort
            )->paginate(12)->appends($request->query());
        }

        $products = $q === ''
            ? $this->applySort(
                Product::query()
                    ->where('is_active', true)
                    ->when($training !== null, fn (BuilderContract $query) => $query->whereJsonContains('training_tags', $training->value))
                    ->with(['category', 'images'])
                    ->withSum('variants', 'stock_quantity'),
                $sort
            )->paginate(12)->appends($request->query())
            : null;

        if ($request->ajax()) {
            return view('shop.partials.results', compact('products', 'q', 'searchResults', 'training'));
        }

        // Only needed for the full page shell (category dropdown) — the AJAX
        // partial above never references it, so skip it on every keystroke.
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount(['activeProducts'])
            ->get();

        return view('shop.index', compact('categories', 'products', 'q', 'searchResults', 'sort', 'training'));
    }

    public function category(Request $request, Category $category): View
    {
        $this->authorize('view', $category);

        $sort = $this->resolveSort($request);

        $products = $this->applySort(
            $category->activeProducts()
                ->with(['images'])
                ->withSum('variants', 'stock_quantity'),
            $sort
        )->paginate(12)->appends($request->query());

        return view('shop.category', compact('category', 'products', 'sort'));
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

        $imagesByColor = collect($product->availableColors())
            ->mapWithKeys(fn ($c) => [$c['name'] => $product->imagesForColor($c['name'])]);

        $defaultColor = $product->defaultColor();

        return view('shop.product', compact('category', 'product', 'variantsByColor', 'stockByKey', 'imagesByColor', 'defaultColor'));
    }

    private function resolveSort(Request $request): string
    {
        $sort = (string) $request->query('sort', 'newest');

        return in_array($sort, self::SORT_OPTIONS, true) ? $sort : 'newest';
    }

    private function resolveTraining(Request $request): ?TrainingTag
    {
        $value = $request->query('training');

        return is_string($value) ? TrainingTag::tryFrom($value) : null;
    }

    private function applySort(BuilderContract $query, string $sort): BuilderContract
    {
        return match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderBy('id'),
        };
    }
}
