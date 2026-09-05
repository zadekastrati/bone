<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\User;
use App\Services\QuipuPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuipuPaymentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCardOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-'.uniqid(),
            'guest_email' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'payment_gateway_order_id' => '1027',
            'payment_gateway_order_password' => 'secret-pass',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'Buyer',
            'shipping_street' => 'Test street',
            'shipping_building' => '1',
            'shipping_phone' => '123456789',
            'shipping_city' => 'Pristina',
            'shipping_region' => 'Pristina',
            'shipping_postal_code' => '10000',
            'shipping_country' => 'XK',
            'subtotal' => '20.00',
            'shipping_amount' => '0.00',
            'total' => '20.00',
        ], $overrides));
    }

    /**
     * Shape confirmed against a real completed-order response (not a guess) —
     * see receipt requirements.md. "trans" is an array, the card brand and
     * masked number live under "srcToken"/"srcToken.card", and amounts are
     * plain numbers matching the currency unit (20 for €20.00).
     */
    private function fullyPaidResponse(): array
    {
        return [
            'order' => [
                'id' => 1027,
                'status' => 'FullyPaid',
                'amount' => 20,
                'currency' => 'EUR',
                'trans' => [[
                    'approvalCode' => '151139',
                    'regTime' => '2026-04-27 13:13:06',
                    'amount' => 20,
                    'currency' => 'EUR',
                ]],
                'srcToken' => [
                    'displayName' => '410221******3572',
                    'card' => ['brand' => 'Visa'],
                ],
            ],
        ];
    }

    public function test_confirm_payment_marks_order_paid_and_stores_details_on_completion(): void
    {
        Mail::fake();
        Http::fake(['*3dss2test.quipu.de*' => Http::response($this->fullyPaidResponse(), 200)]);

        $order = $this->makeCardOrder();

        $status = app(QuipuPaymentService::class)->confirmPayment($order);

        $this->assertSame(PaymentStatus::Paid, $status);
        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame('151139', $order->payment_approval_code);
        $this->assertSame('Visa', $order->payment_card_brand);
        $this->assertSame('3572', $order->payment_card_last_four);
        // Uses the gateway's own regTime, not just "now" — confirms the
        // real payment timestamp is preserved rather than discarded.
        $this->assertSame('2026-04-27 13:13:06', $order->payment_confirmed_at->format('Y-m-d H:i:s'));
        Mail::assertQueued(OrderPlacedMail::class);
    }

    public function test_confirm_payment_marks_order_failed_when_gateway_status_is_not_fully_paid(): void
    {
        $response = $this->fullyPaidResponse();
        $response['order']['status'] = 'Declined';

        Mail::fake();
        Http::fake(['*3dss2test.quipu.de*' => Http::response($response, 200)]);

        $order = $this->makeCardOrder();

        $status = app(QuipuPaymentService::class)->confirmPayment($order);

        $this->assertSame(PaymentStatus::Failed, $status);
        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payment_status);
        Mail::assertNothingQueued();
    }

    public function test_confirm_payment_marks_order_failed_when_amount_does_not_match(): void
    {
        $response = $this->fullyPaidResponse();
        // Gateway says it charged 5, but the order total is 20 — must not
        // be trusted as a valid payment for this order.
        $response['order']['amount'] = 5;

        Http::fake(['*3dss2test.quipu.de*' => Http::response($response, 200)]);

        $order = $this->makeCardOrder(['total' => '20.00']);

        $status = app(QuipuPaymentService::class)->confirmPayment($order);

        $this->assertSame(PaymentStatus::Failed, $status);
    }

    public function test_confirm_payment_marks_order_failed_when_currency_does_not_match(): void
    {
        $response = $this->fullyPaidResponse();
        $response['order']['currency'] = 'USD';

        Http::fake(['*3dss2test.quipu.de*' => Http::response($response, 200)]);

        $order = $this->makeCardOrder(['total' => '20.00']);

        $status = app(QuipuPaymentService::class)->confirmPayment($order);

        $this->assertSame(PaymentStatus::Failed, $status);
    }

    public function test_confirm_payment_leaves_order_pending_on_gateway_error(): void
    {
        Http::fake(['*3dss2test.quipu.de*' => Http::response(['error' => 'timeout'], 500)]);

        $order = $this->makeCardOrder();

        $status = app(QuipuPaymentService::class)->confirmPayment($order);

        $this->assertSame(PaymentStatus::Pending, $status);
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_confirm_payment_is_idempotent_and_never_reprocesses_a_resolved_order(): void
    {
        Mail::fake();
        Http::fake(['*3dss2test.quipu.de*' => Http::response($this->fullyPaidResponse(), 200)]);

        $order = $this->makeCardOrder();
        $service = app(QuipuPaymentService::class);

        $first = $service->confirmPayment($order);
        $second = $service->confirmPayment($order->fresh());

        $this->assertSame(PaymentStatus::Paid, $first);
        $this->assertSame(PaymentStatus::Paid, $second);
        Http::assertSentCount(1);
        Mail::assertQueued(OrderPlacedMail::class, 1);
    }

    public function test_callback_route_redirects_guest_to_signed_confirmation_on_success(): void
    {
        Mail::fake();
        Http::fake(['*3dss2test.quipu.de*' => Http::response($this->fullyPaidResponse(), 200)]);

        $order = $this->makeCardOrder();

        $response = $this->get(route('payment.quipu.return', $order));

        $response->assertRedirect();
        $this->assertStringContainsString('/order-confirmation/', $response->headers->get('Location'));
    }

    public function test_callback_route_redirects_logged_in_user_to_their_order_on_success(): void
    {
        Mail::fake();
        Http::fake(['*3dss2test.quipu.de*' => Http::response($this->fullyPaidResponse(), 200)]);

        $user = User::factory()->create();
        $order = $this->makeCardOrder(['user_id' => $user->id, 'guest_email' => null]);

        $response = $this->actingAs($user)->get(route('payment.quipu.return', $order));

        $response->assertRedirect(route('orders.show', $order));
    }

    public function test_callback_route_shows_failure_page_on_decline(): void
    {
        $response = $this->fullyPaidResponse();
        $response['order']['status'] = 'Declined';

        Http::fake(['*3dss2test.quipu.de*' => Http::response($response, 200)]);

        $order = $this->makeCardOrder();

        $this->get(route('payment.quipu.return', $order))
            ->assertOk()
            ->assertSee('Payment not completed');
    }
}
