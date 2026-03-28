<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductVariantSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductVariantSyncService $variantSync
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::query()->with(['category'])->withCount('variants')->latest();

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

        $product = Product::create($data);

        $this->variantSync->sync($product, $request->input('variants', []));

        foreach ($request->file('images', []) as $index => $file) {
            if ($file === null) {
                continue;
            }
            $path = $file->store('products', 'public');
            $product->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(int $id): View
    {
        $product = Product::query()
            ->with(['category', 'images', 'variants'])
            ->findOrFail($id);
        $this->authorize('update', $product);

        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);
        $this->authorize('update', $product);

        $data = collect($request->validated())
            ->except(['images', 'variants', 'delete_image_ids'])
            ->all();
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        $this->variantSync->sync($product, $request->input('variants', []));

        $deleteIds = $request->input('delete_image_ids', []);
        if ($deleteIds !== []) {
            $images = ProductImage::query()
                ->where('product_id', $product->id)
                ->whereIn('id', $deleteIds)
                ->get();
            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $maxSort = (int) $product->images()->max('sort_order');
        foreach ($request->file('images', []) as $i => $file) {
            if ($file === null) {
                continue;
            }
            $path = $file->store('products', 'public');
            $product->images()->create([
                'path' => $path,
                'sort_order' => $maxSort + $i + 1,
            ]);
        }

        return redirect()->route('admin.products.edit', $product->id)->with('success', 'Product updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::query()->with('images')->findOrFail($id);
        $this->authorize('delete', $product);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product archived.');
    }
}
