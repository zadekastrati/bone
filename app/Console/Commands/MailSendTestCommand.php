<?php

namespace App\Console\Commands;

use App\Mail\OrderPlacedMail;
use App\Mail\RegisterOtpMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailSendTestCommand extends Command
{
    protected $signature = 'mail:test
                            {email : Recipient address}
                            {--order= : Optional order ID to use for the order confirmation sample}';

    protected $description = 'Send test emails (registration OTP style + order confirmation) to verify SMTP configuration';

    public function handle(): int
    {
        $to = (string) $this->argument('email');
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');

            return self::FAILURE;
        }

        $appName = (string) config('app.name', 'Laravel');

        $this->info('Sending registration-style OTP email…');
        Mail::to($to)->send(new RegisterOtpMail(
            code: '123456',
            userName: 'Test user',
            appName: $appName,
        ));
        $this->line('  ✓ Register OTP template sent.');

        $orderOption = $this->option('order');
        if ($orderOption !== null && $orderOption !== '') {
            $order = Order::query()->with('items')->find((int) $orderOption);
            if ($order === null) {
                $this->error('Order not found: '.$orderOption);

                return self::FAILURE;
            }
        } else {
            $order = Order::query()->with('items')->latest()->first();
        }

        if ($order === null || 0 === $order->items->count()) {
            $this->warn('Skipping order confirmation: no order with line items found. Place a test order or run: php artisan mail:test '.$to.' --order=YOUR_ORDER_ID');
            $this->info('Done. Check the inbox (and spam) for: "'.$appName.' — Confirm your email"');

            return self::SUCCESS;
        }

        $this->info('Sending order confirmation email (order '.$order->order_number.')…');
        Mail::to($to)->send(new OrderPlacedMail($order));
        $this->line('  ✓ Order confirmation template sent.');

        $this->info('Done. Check the inbox (and spam) for both messages.');

        return self::SUCCESS;
    }
}
