<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CurrencyService;
use App\Services\QuipuPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly CurrencyService $currency,
        private readonly QuipuPaymentService $quipu
    ) {}

    public function create(): View|RedirectResponse
    {
        if ($this->cart->lines()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your cart is empty.'));
        }

        if ($redirect = $this->verifiedGuard()) {
            return $redirect;
        }

        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal();
        $shippingCountries = config('store.shipping.countries', []);
        $defaultCountry = $this->currency->currentCountry();
        $shipping = $this->shippingForCountry($defaultCountry, $subtotal);
        $total = bcadd($subtotal, $shipping, 2);
        $freeShipping = $this->qualifiesForFreeShipping($subtotal);
        $shippingRateMap = collect($shippingCountries)
            ->map(fn (array $c): string => $freeShipping ? '0.00' : (string) $c['amount'])
            ->all();
        $displayCurrency = $this->currency->currencyConfig();
        $paymentMethods = collect(PaymentMethod::cases())
            ->reject(fn (PaymentMethod $m): bool => $m === PaymentMethod::Card && ! config('services.quipu.enabled'))
            ->values();

        return view('shop.checkout', compact(
            'lines',
            'subtotal',
            'shipping',
            'total',
            'defaultCountry',
            'shippingCountries',
            'shippingRateMap',
            'displayCurrency',
            'paymentMethods'
        ));
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->lines()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your cart is empty.'));
        }

        if ($redirect = $this->verifiedGuard()) {
            return $redirect;
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

        if ($validated['payment_method'] === PaymentMethod::Card) {
            try {
                $gatewayOrder = $this->quipu->createOrder(
                    $order,
                    route('payment.quipu.return', $order),
                    $request
                );

                $order->forceFill([
                    'payment_gateway_order_id' => $gatewayOrder['id'],
                    'payment_gateway_order_password' => $gatewayOrder['password'],
                ])->save();

                return redirect()->away($this->quipu->buildRedirectUrl($gatewayOrder));
            } catch (\Throwable $e) {
                Log::error('Quipu order creation failed', [
                    'order_id' => $order->id,
                    'exception' => $e->getMessage(),
                ]);

                return redirect()->route('cart.index')->with('error', __('Card payment is temporarily unavailable. Please choose a different payment method.'));
            }
        }

        if ($request->user() !== null) {
            return redirect()->route('orders.show', $order)->with('success', __('Order placed successfully. Thank you.'));
        }

        // No account to view "My orders" from — a signed link is the guest's
        // only way back to this confirmation page.
        return redirect()->to(URL::signedRoute('orders.guest-confirmation', ['order' => $order]))
            ->with('success', __('Order placed successfully. Thank you.'));
    }

    /**
     * Logged-in shoppers must still verify their email before checking out
     * — unchanged from before guest checkout existed. Guests skip this
     * entirely since there's no account/email to verify.
     */
    private function verifiedGuard(): ?RedirectResponse
    {
        $user = auth()->user();

        if ($user !== null && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return null;
    }

    private function shippingForCountry(string $countryCode, string $subtotal): string
    {
        if ($this->qualifiesForFreeShipping($subtotal)) {
            return '0.00';
        }

        $code = strtoupper($countryCode);
        $countries = config('store.shipping.countries', []);
        if (! isset($countries[$code])) {
            $fallback = config('store.shipping.default_country', 'XK');

            return (string) $countries[$fallback]['amount'];
        }

        return (string) $countries[$code]['amount'];
    }

    /**
     * Strictly over the threshold — an order of exactly the threshold amount
     * still pays normal shipping. Mirrors CheckoutService's own check, which
     * is what actually applies at order placement.
     */
    private function qualifiesForFreeShipping(string $subtotal): bool
    {
        return bccomp($subtotal, (string) config('store.shipping.free_over', '100.00'), 2) > 0;
    }
}
