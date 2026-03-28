<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout
    ) {}

    public function create(): View|RedirectResponse
    {
        if ($this->cart->lines()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal();
        $shippingCountries = config('store.shipping.countries', []);
        $defaultCountry = config('store.shipping.default_country', 'XK');
        $shipping = $this->shippingForCountry($defaultCountry);
        $total = bcadd($subtotal, $shipping, 2);
        $shippingRateMap = collect($shippingCountries)
            ->map(fn (array $c): string => (string) $c['amount'])
            ->all();

        return view('shop.checkout', compact(
            'lines',
            'subtotal',
            'shipping',
            'total',
            'defaultCountry',
            'shippingCountries',
            'shippingRateMap'
        ));
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->lines()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validated();
        $pm = $validated['payment_method'];
        $validated['payment_method'] = $pm instanceof PaymentMethod
            ? $pm
            : PaymentMethod::from($pm);

        try {
            $order = $this->checkout->placeOrder($request->user(), $validated);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully. Thank you.');
    }

    private function shippingForCountry(string $countryCode): string
    {
        $code = strtoupper($countryCode);
        $countries = config('store.shipping.countries', []);
        if (! isset($countries[$code])) {
            $fallback = config('store.shipping.default_country', 'XK');

            return (string) $countries[$fallback]['amount'];
        }

        return (string) $countries[$code]['amount'];
    }
}
