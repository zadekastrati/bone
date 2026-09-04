<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Where the Quipu hosted payment page sends the customer back to after
     * they attempt a card payment. This only shows a holding page for now —
     * actually confirming the payment result (querying the gateway, marking
     * the order paid/failed, sending the confirmation email) is handled by a
     * separate follow-up ticket, not here.
     */
    public function quipuReturn(Order $order): View
    {
        return view('shop.payment-pending', compact('order'));
    }
}
