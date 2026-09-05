<?php

namespace Tests\Feature;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPlacedMailTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-'.uniqid(),
            'guest_email' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'Buyer',
            'shipping_street' => 'Test street',
            'shipping_city' => 'Pristina',
            'shipping_region' => 'Pristina',
            'shipping_postal_code' => '10000',
            'shipping_country' => 'XK',
            'shipping_phone' => '123456789',
            'subtotal' => '20.00',
            'shipping_amount' => '0.00',
            'total' => '20.00',
        ], $overrides));
    }

    public function test_card_order_email_shows_approval_code_brand_last_four_and_paid_time(): void
    {
        $order = $this->makeOrder([
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'payment_approval_code' => '151139',
            'payment_card_brand' => 'Visa',
            'payment_card_last_four' => '3572',
            'payment_confirmed_at' => '2026-09-05 01:48:00',
        ]);

        $html = (new OrderPlacedMail($order))->render();

        $this->assertStringContainsString($order->order_number, $html);
        $this->assertStringContainsString('Visa', $html);
        $this->assertStringContainsString('3572', $html);
        $this->assertStringContainsString('151139', $html);
    }

    public function test_bank_transfer_order_email_shows_reference_not_card_details(): void
    {
        $order = $this->makeOrder(['payment_method' => 'bank_transfer']);

        $html = (new OrderPlacedMail($order))->render();

        $this->assertStringContainsString('Bank transfer reference', $html);
        $this->assertStringNotContainsString('Approval code', $html);
    }

    public function test_cash_on_delivery_order_email_shows_neither_payment_block(): void
    {
        $order = $this->makeOrder(['payment_method' => 'cash_on_delivery']);

        $html = (new OrderPlacedMail($order))->render();

        $this->assertStringNotContainsString('Bank transfer reference', $html);
        $this->assertStringNotContainsString('Approval code', $html);
    }
}
