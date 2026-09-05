<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\QuipuPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly QuipuPaymentService $quipu) {}

    /**
     * Where the Quipu hosted payment page sends the customer back to after
     * a card payment attempt. STATUS/ID/code on the query string are only
     * ever a hint that something happened, logged for troubleshooting —
     * they're never treated as proof of payment. The real result always
     * comes from QuipuPaymentService::confirmPayment() re-querying the
     * gateway directly.
     */
    public function quipuReturn(Request $request, Order $order): View|RedirectResponse
    {
        Log::info('Quipu payment callback received', [
            'order_id' => $order->id,
            'callback_status' => $request->query('STATUS'),
            'callback_gateway_order_id' => $request->query('ID'),
            'callback_code' => $request->query('code'),
        ]);

        $status = $this->quipu->confirmPayment($order);

        return match ($status) {
            PaymentStatus::Paid => $this->redirectToConfirmation($order),
            PaymentStatus::Failed => view('shop.payment-failed', compact('order')),
            PaymentStatus::Pending => view('shop.payment-pending', compact('order')),
        };
    }

    /**
     * Mirrors CheckoutController::store()'s own success branching for the
     * non-card payment methods — logged-in shoppers go to their order page,
     * guests get a signed link since they have no account to view it from.
     */
    private function redirectToConfirmation(Order $order): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('orders.show', $order)->with('success', __('Payment received. Thank you.'));
        }

        return redirect()->to(URL::signedRoute('orders.guest-confirmation', ['order' => $order]))
            ->with('success', __('Payment received. Thank you.'));
    }
}
