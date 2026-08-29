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
