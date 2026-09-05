<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuipuPaymentService
{
    /**
     * Create an order at the Quipu/ProCredit gateway (mutual TLS + Merchant
     * ID) and return its id/password/hppUrl so the customer can be
     * redirected to the hosted payment page to enter their card details.
     *
     * @return array{id: int, password: string, hppUrl: string, status: string}
     */
    public function createOrder(Order $order, string $returnUrl, Request $request): array
    {
        $config = config('services.quipu');

        $response = Http::withOptions([
            'cert' => base_path($config['cert_path']),
            'ssl_key' => base_path($config['key_path']),
            'verify' => base_path($config['ca_path']),
            'timeout' => 20,
        ])->post($config['order_endpoint'], [
            'order' => [
                'typeRid' => '1',
                // The order model's total is a decimal:2 cast, so this is
                // already a "10.00"-style string — the format the gateway
                // expects for order creation (not the cents-style integer
                // used in the paid-order detail response).
                'amount' => (string) $order->total,
                'currency' => 'EUR',
                'description' => 'Order '.$order->order_number,
                'language' => 'en',
                'hppRedirectUrl' => $returnUrl,
                'initiationEnvKind' => 'Browser',
                'consumerDevice' => [
                    'browser' => [
                        'javaEnabled' => false,
                        'jsEnabled' => true,
                        'acceptHeader' => 'application/json,application/jose;charset=utf-8',
                        'ip' => (string) $request->ip(),
                        'colorDepth' => '24',
                        'screenW' => '1920',
                        'screenH' => '1080',
                        'tzOffset' => '0',
                        'language' => 'en-EN',
                        'userAgent' => (string) $request->userAgent(),
                    ],
                ],
            ],
        ]);

        $data = $response->json('order');

        if (! $response->successful() || ! isset($data['id'], $data['password'], $data['hppUrl'])) {
            throw new \RuntimeException('Quipu order creation failed: HTTP '.$response->status().' — '.$response->body());
        }

        return $data;
    }

    /**
     * The gateway's hppUrl is just the base card-entry page — id/password
     * must be appended as query params for it to know which order this is
     * (integration doc §4.1.3 "Building up the url after receiving create
     * order response"). Without this, the hosted page 500s.
     *
     * @param  array{id: int, password: string, hppUrl: string}  $gatewayOrder
     */
    public function buildRedirectUrl(array $gatewayOrder): string
    {
        return $gatewayOrder['hppUrl'].'?'.http_build_query([
            'id' => $gatewayOrder['id'],
            'password' => $gatewayOrder['password'],
        ]);
    }

    /**
     * The source of truth for what actually happened to a payment — the
     * callback query string (STATUS/ID/code) is only ever a hint to come
     * check, never proof, since anyone can hit that URL with any query
     * string they like. This re-queries Quipu directly, using the order
     * password issued at creation time plus certificate auth, and only
     * trusts what comes back from that authenticated call.
     *
     * @return array<string, mixed>
     */
    public function getOrderDetails(Order $order): array
    {
        $config = config('services.quipu');

        $response = Http::withOptions([
            'cert' => base_path($config['cert_path']),
            'ssl_key' => base_path($config['key_path']),
            'verify' => base_path($config['ca_path']),
            'timeout' => 20,
        ])->get($config['order_endpoint'].'/'.$order->payment_gateway_order_id, [
            'password' => $order->payment_gateway_order_password,
            'tokenDetailLevel' => 2,
            'tranDetailLevel' => 1,
        ]);

        $data = $response->json('order');

        if (! $response->successful() || ! is_array($data)) {
            throw new \RuntimeException('Quipu getOrderDetails failed: HTTP '.$response->status().' — '.$response->body());
        }

        return $data;
    }

    /**
     * Re-queries Quipu for $order and applies the result: marks it Paid or
     * Failed, stores the card/approval details, and sends the confirmation
     * email exactly once. Idempotent — calling this again on an order that
     * has already left Pending is a no-op and just returns its current
     * status, so a customer refreshing the return page (or Quipu retrying
     * the callback) can't double-process or double-email.
     */
    public function confirmPayment(Order $order): PaymentStatus
    {
        if ($order->payment_status !== PaymentStatus::Pending) {
            return $order->payment_status;
        }

        try {
            $data = $this->getOrderDetails($order);
        } catch (\Throwable $e) {
            // Gateway/network trouble, not a decline — leave the order
            // Pending so the next visit (or an admin) can try again, rather
            // than wrongly recording it as Failed.
            Log::error('Quipu getOrderDetails failed while confirming payment', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
            ]);

            return PaymentStatus::Pending;
        }

        if (! $this->isPaid($data) || ! $this->amountAndCurrencyMatch($order, $data)) {
            $order->forceFill(['payment_status' => PaymentStatus::Failed])->save();

            return PaymentStatus::Failed;
        }

        $transaction = $this->extractTransactionDetails($data);

        // Prefer the gateway's own regTime (when the bank actually recorded
        // the payment) over our own clock — falls back to now() only if
        // that field is ever missing.
        $confirmedAt = $transaction['regTime'] !== null
            ? \Illuminate\Support\Carbon::parse($transaction['regTime'])
            : now();

        $order->forceFill([
            'payment_status' => PaymentStatus::Paid,
            'payment_approval_code' => $transaction['approvalCode'],
            'payment_card_brand' => $transaction['cardBrand'],
            'payment_card_last_four' => $transaction['cardLastFour'],
            'payment_confirmed_at' => $confirmedAt,
        ])->save();

        $this->sendConfirmationEmail($order);

        return PaymentStatus::Paid;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isPaid(array $data): bool
    {
        return strtolower((string) ($data['status'] ?? '')) === 'fullypaid';
    }

    /**
     * Confirms the gateway actually charged the amount/currency we asked
     * for — without this, a tampered or stale response could get an order
     * marked Paid for the wrong amount. Confirmed against a real completed
     * order response: "amount" is a plain number matching the currency unit
     * (10 for €10.00) both on the order and inside trans[] — no cents
     * multiplication, so a straight bccomp is all that's needed.
     *
     * @param  array<string, mixed>  $data
     */
    private function amountAndCurrencyMatch(Order $order, array $data): bool
    {
        $currency = strtoupper((string) ($data['currency'] ?? ''));

        if ($currency !== 'EUR') {
            return false;
        }

        return bccomp((string) ($data['amount'] ?? ''), (string) $order->total, 2) === 0;
    }

    /**
     * Confirmed against a real completed-order response (not a guess):
     * transaction records are under "trans" (an array — [0] is the
     * purchase), approval code and the actual payment timestamp ("regTime")
     * live there; the card brand and masked number ("displayName", e.g.
     * "410221******3572") are under "srcToken"/"srcToken.card". Falls back
     * to null for anything missing rather than throwing, in case a
     * different order type ever comes back with a slightly different shape.
     *
     * @param  array<string, mixed>  $data
     * @return array{approvalCode: ?string, cardBrand: ?string, cardLastFour: ?string, regTime: ?string}
     */
    private function extractTransactionDetails(array $data): array
    {
        $tran = $data['trans'][0] ?? [];
        $srcToken = $data['srcToken'] ?? [];
        $displayName = $srcToken['displayName'] ?? null; // e.g. "410221******3572"

        return [
            'approvalCode' => $tran['approvalCode'] ?? null,
            'cardBrand' => $srcToken['card']['brand'] ?? null,
            'cardLastFour' => is_string($displayName) ? (substr(preg_replace('/\D/', '', $displayName) ?: '', -4) ?: null) : null,
            'regTime' => $tran['regTime'] ?? null,
        ];
    }

    /**
     * Mirrors CheckoutService::placeOrder()'s own email step — deliberately
     * deferred until now for card orders specifically, so it can include
     * the approval code and card details the bank's sample invoice
     * requires, which don't exist until the payment is actually confirmed.
     */
    private function sendConfirmationEmail(Order $order): void
    {
        if ($order->payment_method !== PaymentMethod::Card) {
            return;
        }

        $order->loadMissing('user');
        $recipient = $order->user?->email ?? $order->guest_email;

        if ($recipient === null) {
            return;
        }

        try {
            Mail::to($recipient)
                ->locale($order->user?->locale ?? app()->getLocale())
                ->send(new OrderPlacedMail($order));
        } catch (\Throwable $e) {
            Log::error('Order confirmation email failed after card payment', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
