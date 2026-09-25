<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Keeps the admin "choose from library" picker and customer product
        // pages fast for anything uploaded straight to R2 outside the app —
        // without this, a fresh batch of uploads pays the cold
        // fetch-and-resize cost live on whoever opens the picker/page first.
        $schedule->command('media:warm-library')->hourly()->withoutOverlapping();
        $schedule->command('media:warm-product-images')->hourly()->withoutOverlapping();

        // The rates API this hits only updates once every 24h anyway, so
        // this cadence is purely for resilience — if one run fails (the API
        // is briefly down, a network blip), the next one an hour later
        // retries instead of the store running on a stale rate all day.
        $schedule->command('exchange-rates:refresh')->hourly()->withoutOverlapping();

        // Releases stock reserved by card orders the customer abandoned
        // (redirected to Quipu's hosted payment page, never completed it)
        // instead of holding it forever — see config('store.orders').
        //
        // everyFiveMinutes(), not hourly(): hourly() only matches the exact
        // :00 minute of the hour, and the external trigger invoking
        // schedule:run (a Railway cron job, 5-minute minimum interval) isn't
        // guaranteed to land on that precise minute every time — a missed
        // window would silently delay expiry by up to another hour. Running
        // this check every 5 minutes instead means any one missed tick is
        // caught by the next one a few minutes later. The command's own
        // 30-minute abandonment threshold (config('store.orders')) is what
        // actually controls when an order expires — this only controls how
        // often that check runs.
        $schedule->command('orders:expire-abandoned-card-payments')->everyFiveMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
