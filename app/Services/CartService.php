<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'store_cart';

    /** @return array<int|string, int> variant_id => qty */
    public function contents(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function count(): int
    {
        return (int) array_sum($this->contents());
    }

    public function add(int $variantId, int $quantity = 1): void
    {
        if ($quantity < 1) {
            return;
        }

        $variant = ProductVariant::query()->with('product')->findOrFail($variantId);

        if (! $variant->product->is_active || $variant->product->trashed()) {
            abort(422, 'This product is not available.');
        }

        if (! $variant->isInStock($quantity)) {
            abort(422, 'Not enough stock for this variant.');
        }

        $current = $this->contents();
        $existing = (int) ($current[$variantId] ?? 0);
        $newQty = $existing + $quantity;

        if (! $variant->isInStock($newQty)) {
            abort(422, 'Not enough stock for this quantity.');
        }

        $current[$variantId] = $newQty;
        Session::put(self::SESSION_KEY, $current);
    }

    public function update(int $variantId, int $quantity): void
    {
        $current = $this->contents();

        if ($quantity < 1) {
            unset($current[$variantId]);
            Session::put(self::SESSION_KEY, $current);

            return;
        }

        $variant = ProductVariant::query()->with('product')->findOrFail($variantId);

        if (! $variant->product->is_active || $variant->product->trashed()) {
            abort(422, 'This product is not available.');
        }

        if (! $variant->isInStock($quantity)) {
            abort(422, 'Not enough stock for this quantity.');
        }

        $current[$variantId] = $quantity;
        Session::put(self::SESSION_KEY, $current);
    }

    public function remove(int $variantId): void
    {
        $current = $this->contents();
        unset($current[$variantId]);
        Session::put(self::SESSION_KEY, $current);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, array{variant: ProductVariant, quantity: int, line_total: string}>
     */
    public function lines(): Collection
    {
        $contents = $this->contents();
        if ($contents === []) {
            return collect();
        }

        $variantIds = array_map(intval(...), array_keys($contents));
        $variants = ProductVariant::query()
            ->with(['product.category', 'product.images'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $lines = collect();
        foreach ($contents as $variantId => $qty) {
            $id = (int) $variantId;
            $variant = $variants->get($id);
            if ($variant === null || ! $variant->product->is_active || $variant->product->trashed()) {
                continue;
            }

            $lineTotal = bcmul((string) $variant->product->price, (string) $qty, 2);
            $lines->push([
                'variant' => $variant,
                'quantity' => (int) $qty,
                'line_total' => $lineTotal,
            ]);
        }

        return $lines;
    }

    public function subtotal(): string
    {
        $sum = '0.00';
        foreach ($this->lines() as $line) {
            $sum = bcadd($sum, $line['line_total'], 2);
        }

        return $sum;
    }
}
