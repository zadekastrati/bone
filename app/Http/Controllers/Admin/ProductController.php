<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductMediaService;
use App\Services\ProductVariantSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductVariantSyncService $variantSync,
        private readonly ProductMediaService $mediaService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::query()->with(['category', 'images'])->withCount('variants')->latest();

        if ($request->boolean('inactive')) {
            $query->where('is_active', false);
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        $products = $query->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return view('admin.products.partials.results', compact('products'));
        }

        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = collect($request->validated())
            ->except(['images', 'variants'])
            ->all();
        $data['is_active'] = $request->boolean('is_active', true);

        $product = DB::transaction(function () use ($data, $request): Product {
            $product = Product::create($data);

            $this->variantSync->sync($product, $request->input('variants', []));

            $this->mediaService->storeUploads($product, $request->file('images', []));

            return $product;
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'images', 'variants']);
        $this->authorize('update', $product);

        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = collect($request->validated())
            ->except(['images', 'variants', 'delete_image_ids', 'thumbnail_image_id'])
            ->all();
        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($product, $data, $request): void {
            $product->update($data);

            $this->variantSync->sync($product, $request->input('variants', []));

            $this->mediaService->deleteImages($product, $request->input('delete_image_ids', []));

            $maxSort = (int) $product->images()->max('sort_order');
            $this->mediaService->storeUploads($product, $request->file('images', []), $maxSort + 1);

            $this->mediaService->setThumbnail($product, $request->input('thumbnail_image_id'));
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product archived.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $products = Product::whereIn('id', $validated['ids'])->get();

        foreach ($products as $product) {
            $this->authorize('delete', $product);
            $product->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Selected products archived.');
    }

    public function archived(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::onlyTrashed()->with(['category', 'images'])->withCount('variants')->latest('deleted_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        $products = $query->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return view('admin.products.partials.archived-results', compact('products'));
        }

        return view('admin.products.archived', compact('products'));
    }

    public function restore(Product $product): RedirectResponse
    {
        $this->authorize('restore', $product);

        $product->restore();

        return redirect()->route('admin.products.archived')->with('success', 'Product restored.');
    }

    public function forceDelete(Product $product): RedirectResponse
    {
        $this->authorize('forceDelete', $product);

        $product->load('images');

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->forceDelete();

        return redirect()->route('admin.products.archived')->with('success', 'Product permanently deleted.');
    }
}
