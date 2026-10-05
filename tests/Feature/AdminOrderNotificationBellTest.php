<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin notification bell previously listed every order the instant it
 * was placed — including a Card order still sitting on Quipu's hosted
 * payment page. That matched neither the confirmation email nor the
 * Telegram alert, both of which wait for payment to actually succeed before
 * announcing a Card order.
 */
class AdminOrderNotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeOrder(string $paymentMethod, string $paymentStatus): Order
    {
        return Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'guest_email' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'Buyer',
            'shipping_street' => 'Test street',
            'shipping_building' => '1',
            'shipping_phone' => '123456789',
            'shipping_city' => 'Pristina',
            'shipping_region' => 'Pristina',
            'shipping_postal_code' => '10000',
            'shipping_country' => 'XK',
            'subtotal' => '25.00',
            'shipping_amount' => '0.00',
            'total' => '25.00',
        ]);
    }

    public function test_an_unpaid_card_order_is_hidden_from_the_bell(): void
    {
        $order = $this->makeOrder('card', 'pending');

        $response = $this->actingAs($this->makeAdmin())->get(route('admin.notifications.index'));

        $response->assertOk();
        $ids = collect($response->json('items'))->pluck('id');
        $this->assertFalse($ids->contains('order-'.$order->id));
        $this->assertSame(0, $response->json('unread_count'));
    }

    public function test_a_paid_card_order_appears_in_the_bell(): void
    {
        $order = $this->makeOrder('card', 'paid');

        $response = $this->actingAs($this->makeAdmin())->get(route('admin.notifications.index'));

        $response->assertOk();
        $ids = collect($response->json('items'))->pluck('id');
        $this->assertTrue($ids->contains('order-'.$order->id));
        $this->assertSame(1, $response->json('unread_count'));
    }

    public function test_a_pending_cash_on_delivery_order_appears_in_the_bell(): void
    {
        $order = $this->makeOrder('cash_on_delivery', 'pending');

        $response = $this->actingAs($this->makeAdmin())->get(route('admin.notifications.index'));

        $response->assertOk();
        $ids = collect($response->json('items'))->pluck('id');
        $this->assertTrue($ids->contains('order-'.$order->id));
        $this->assertSame(1, $response->json('unread_count'));
    }
}
