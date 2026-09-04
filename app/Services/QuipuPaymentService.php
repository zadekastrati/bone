<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
}
