<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function (): string {
            $urls = [
                ['loc' => route('home'), 'priority' => '1.0'],
                ['loc' => route('shop.index'), 'priority' => '0.9'],
                ['loc' => route('about'), 'priority' => '0.3'],
                ['loc' => route('contact'), 'priority' => '0.3'],
                ['loc' => route('returns'), 'priority' => '0.3'],
                ['loc' => route('size-guide'), 'priority' => '0.3'],
                ['loc' => route('terms'), 'priority' => '0.2'],
            ];

            Category::query()->orderBy('sort_order')->each(function (Category $category) use (&$urls): void {
                $urls[] = [
                    'loc' => route('shop.category', $category),
                    'priority' => '0.7',
                ];
            });

            Product::query()
                ->where('is_active', true)
                ->with('category')
                ->latest('updated_at')
                ->each(function (Product $product) use (&$urls): void {
                    $urls[] = [
                        'loc' => route('shop.product', [$product->category, $product]),
                        'lastmod' => $product->updated_at?->toAtomString(),
                        'priority' => '0.8',
                    ];
                });

            return view('sitemap', compact('urls'))->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
