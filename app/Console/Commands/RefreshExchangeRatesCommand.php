<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class RefreshExchangeRatesCommand extends Command
{
    protected $signature = 'exchange-rates:refresh';

    protected $description = 'Fetch the latest EUR -> ALL/MKD exchange rates from the live rates API and store them for CurrencyService to use';

    public function handle(ExchangeRateService $service): int
    {
        $results = $service->refresh();

        foreach ($results as $currency => $updated) {
            if ($updated) {
                $this->info("{$currency}: updated.");
            } else {
                $this->warn("{$currency}: fetch failed, keeping previous rate.");
            }
        }

        return self::SUCCESS;
    }
}
