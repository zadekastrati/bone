<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart
    ) {}

    public function index(): View
    {
        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal();

        return view('shop.cart', compact('lines', 'subtotal'));
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $variant = $request->variant();

        if (! $variant->isInStock((int) $request->validated('quantity'))) {
            return back()->withInput()->with('error', 'Not enough stock for this variant.');
        }

        $this->cart->add($variant->id, (int) $request->validated('quantity'));

        return redirect()->route('cart.index')->with('success', 'Added to cart.');
    }

    public function update(UpdateCartItemRequest $request, int $variant): RedirectResponse
    {
        if (! array_key_exists($variant, $this->cart->contents())) {
            return redirect()->route('cart.index')->with('error', 'That item is not in your cart.');
        }

        $model = ProductVariant::query()->with('product')->findOrFail($variant);

        if (! $model->product->is_active || $model->product->trashed()) {
            return redirect()->route('cart.index')->with('error', 'This product is no longer available.');
        }

        $qty = (int) $request->validated('quantity');
        if ($qty > 0 && ! $model->isInStock($qty)) {
            return redirect()->route('cart.index')->with('error', 'Not enough stock for this item.');
        }

        $this->cart->update($variant, $qty);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function destroy(int $variant): RedirectResponse
    {
        $this->cart->remove($variant);

        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }
}
