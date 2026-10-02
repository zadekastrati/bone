<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Previously there was no way to see how a customer paid (card/bank
 * transfer/cash on delivery) without opening each order's full "Manage"
 * page individually — not on the orders list, not in the quick "Details"
 * dropdown, and not on the invoice for anything but bank transfer.
 */
class AdminOrderPaymentMethodVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeOrder(string $paymentMethod): Order
    {
        return Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'guest_email' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
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

    public function test_orders_list_shows_each_orders_payment_method(): void
    {
        $this->makeOrder('cash_on_delivery');
        $this->makeOrder('card');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Cash on delivery')
            ->assertSee('Card (Visa/Mastercard)');
    }

    public function test_the_quick_details_dropdown_shows_the_payment_method(): void
    {
        $order = $this->makeOrder('cash_on_delivery');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.orders.details', $order))
            ->assertOk()
            ->assertSee('Cash on delivery');
    }

    public function test_the_invoice_shows_the_payment_method_for_every_payment_type(): void
    {
        $cardOrder = $this->makeOrder('card');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.orders.invoice', $cardOrder))
            ->assertOk()
            ->assertSee('Card (Visa/Mastercard)');
    }
}
