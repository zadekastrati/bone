<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductMediaService;
use App\Services\ProductVariantSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        $query = Product::query()->with(['category', 'images', 'variants'])->withCount('variants')->latest();

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

        /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
        $products = $query->paginate(20);
        $products->withQueryString();

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
            $this->storeVariantImages($product, $request);
            $this->mediaService->attachExisting($product, $request->input('attach_images', []));

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
            ->except(['images', 'variant_images', 'attach_images', 'attach_variant_images', 'variants', 'delete_image_ids', 'thumbnail_image_id'])
            ->all();
        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($product, $data, $request): void {
            $product->update($data);

            $colorRenames = $this->variantSync->sync($product, $request->input('variants', []));

            if ($colorRenames !== []) {
                // The image-gallery's per-color sections (headings, upload
                // widgets, "choose from library" pickers) are rendered once
                // at page load, keyed by the color names that existed then.
                // If a color got renamed in the variants table on this same
                // save, both the images already attached under the old name
                // and this request's own upload/attach submissions (still
                // keyed by that old name) need to follow the rename —
                // otherwise the old photos go orphaned and the ones the
                // admin just picked for "that section" silently never get
                // attached anywhere.
                foreach ($colorRenames as $from => $to) {
                    ProductImage::query()
                        ->where('product_id', $product->id)
                        ->where('color', $from)
                        ->update(['color' => $to]);
                }
            }

            $this->mediaService->deleteImages($product, $request->input('delete_image_ids', []));

            $this->mediaService->storeUploads($product, $request->file('images', []));
            $this->storeVariantImages($product, $request, $colorRenames);

            $this->mediaService->attachExisting($product, $request->input('attach_images', []));
            $this->attachVariantImages($product, $request, $colorRenames);

            $this->mediaService->setThumbnail($product, $request->input('thumbnail_image_id'));
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroyImage(Product $product, ProductImage $image): View
    {
        $this->authorize('update', $product);
        abort_unless($image->product_id === $product->id, 404);

        $color = $image->color;

        $this->mediaService->deleteImages($product, [$image->id]);

        $product->refresh()->load('images');

        return view('admin.products.partials.image-gallery-grid', [
            'product' => $product,
            'color' => $color,
            'images' => $product->images->where('color', $color)->values(),
        ]);
    }

    public function reorderImages(Request $request, Product $product): Response
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'color' => ['nullable', 'string'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('product_images', 'id')->where('product_id', $product->id)],
        ]);

        $this->mediaService->reorderImages($product, $validated['color'] ?? null, $validated['ids']);

        return response()->noContent();
    }

    /**
     * Store images uploaded into a specific color's upload widget
     * (admin.products.partials.image-gallery), keyed by that color's
     * exact variant color name at the time the page was rendered. A key
     * renamed this same save (see $colorRenames, from
     * ProductVariantSyncService::sync) resolves to its new name instead of
     * being treated as unknown; a key that matches neither is genuinely
     * stale (its color was removed entirely) and is ignored.
     *
     * @param  array<string, string>  $colorRenames  old color => new color
     */
    private function storeVariantImages(Product $product, Request $request, array $colorRenames = []): void
    {
        $validColors = $product->variants()->pluck('color')->unique()->all();

        foreach ((array) $request->file('variant_images', []) as $color => $files) {
            $color = $colorRenames[$color] ?? $color;

            if (! in_array($color, $validColors, true)) {
                continue;
            }

            $this->mediaService->storeUploads($product, $files, $color);
        }
    }

    /**
     * Attach R2 library files picked into a specific color's section
     * (admin.products.partials.image-gallery), keyed by that color's exact
     * variant color name at the time the page was rendered. See
     * storeVariantImages() above for why $colorRenames is applied first.
     *
     * @param  array<string, string>  $colorRenames  old color => new color
     */
    private function attachVariantImages(Product $product, Request $request, array $colorRenames = []): void
    {
        $validColors = $product->variants()->pluck('color')->unique()->all();

        foreach ((array) $request->input('attach_variant_images', []) as $color => $paths) {
            $color = $colorRenames[$color] ?? $color;

            if (! in_array($color, $validColors, true)) {
                continue;
            }

            $this->mediaService->attachExisting($product, (array) $paths, $color);
        }
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

        $query = Product::onlyTrashed()->with(['category', 'images', 'variants'])->withCount('variants')->latest('deleted_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
        $products = $query->paginate(20);
        $products->withQueryString();

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

    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $products = Product::onlyTrashed()->whereIn('id', $validated['ids'])->with('images')->get();

        foreach ($products as $product) {
            $this->authorize('forceDelete', $product);

            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $product->forceDelete();
        }

        return redirect()->route('admin.products.archived')->with('success', 'Selected products permanently deleted.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $products = Product::onlyTrashed()->whereIn('id', $validated['ids'])->get();

        foreach ($products as $product) {
            $this->authorize('restore', $product);
            $product->restore();
        }

        return redirect()->route('admin.products.archived')->with('success', 'Selected products restored.');
    }
}
