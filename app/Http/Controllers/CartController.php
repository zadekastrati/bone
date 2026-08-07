<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(AddToCartRequest $request): RedirectResponse|JsonResponse
    {
        $variant = $request->variant();

        if (! $variant->isInStock((int) $request->validated('quantity'))) {
            $message = 'Not enough stock for this variant.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withInput()->with('error', $message);
        }

        $this->cart->add($variant->id, (int) $request->validated('quantity'));

        if ($request->wantsJson()) {
            return response()->json([
                'count' => $this->cart->count(),
                'subtotal' => $this->cart->subtotal(),
                'message' => 'Added to cart.',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Added to cart.');
    }

    public function update(UpdateCartItemRequest $request, int $variant): RedirectResponse|JsonResponse
    {
        if (! array_key_exists($variant, $this->cart->contents())) {
            $message = 'That item is not in your cart.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->route('cart.index')->with('error', $message);
        }

        $model = ProductVariant::query()->with('product')->findOrFail($variant);

        if (! $model->product->is_active || $model->product->trashed()) {
            $message = 'This product is no longer available.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->route('cart.index')->with('error', $message);
        }

        $qty = (int) $request->validated('quantity');
        if ($qty > 0 && ! $model->isInStock($qty)) {
            $message = 'Not enough stock for this item.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->route('cart.index')->with('error', $message);
        }

        $this->cart->update($variant, $qty);

        return $this->cartResponse($request, 'Cart updated.');
    }

    public function destroy(Request $request, int $variant): RedirectResponse|JsonResponse
    {
        $this->cart->remove($variant);

        return $this->cartResponse($request, 'Item removed.');
    }

    private function cartResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'count' => $this->cart->count(),
                'subtotal' => $this->cart->subtotal(),
                'message' => $message,
                'html' => view('shop.partials.cart-body', [
                    'lines' => $this->cart->lines(),
                    'subtotal' => $this->cart->subtotal(),
                ])->render(),
            ]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
}
