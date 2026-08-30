<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches live EUR-based exchange rates and persists them to the
 * exchange_rates table. Only ever called by the scheduled
 * exchange-rates:refresh command (see App\Console\Kernel) — never on a web
 * request, so a slow or failed rates API can't slow down or break a page
 * load. CurrencyService reads whatever was last stored here.
 */
class ExchangeRateService
{
    /**
     * ISO 4217 codes this store needs live rates for. EUR itself is the
     * base currency and is never fetched or stored — it's always exactly 1.
     */
    private const TARGET_CURRENCIES = ['ALL', 'MKD'];

    /**
     * open.er-api.com: a free, keyless, actively maintained rates API
     * (no signup, no expiring token to rotate) that returns direct
     * EUR -> {ALL, MKD} rates in one request. Verified during the audit to
     * return both currencies with the "success" result field this class
     * checks for.
     */
    private const API_URL = 'https://open.er-api.com/v6/latest/EUR';

    /**
     * Fetch the latest rates and persist whichever ones came back valid.
     * A failed request, an unexpected payload, or a single missing/invalid
     * currency never touches that currency's existing row — the storefront
     * keeps using the last known-good rate instead of ever falling back to
     * nothing. Errors are logged, not thrown, so a scheduler failure here
     * never becomes an app-wide problem.
     *
     * @return array<string, bool> currency => whether it was updated this run
     */
    public function refresh(): array
    {
        try {
            $response = Http::timeout(10)->retry(2, 500)->get(self::API_URL);
        } catch (\Throwable $e) {
            Log::warning('Exchange rate refresh: request failed', ['exception' => $e->getMessage()]);

            return array_fill_keys(self::TARGET_CURRENCIES, false);
        }

        if (! $response->successful()) {
            Log::warning('Exchange rate refresh: non-successful response', ['status' => $response->status()]);

            return array_fill_keys(self::TARGET_CURRENCIES, false);
        }

        $body = $response->json();
        $rates = is_array($body['rates'] ?? null) ? $body['rates'] : [];

        if (($body['result'] ?? null) !== 'success' || $rates === []) {
            Log::warning('Exchange rate refresh: unexpected payload shape', ['body' => $body]);

            return array_fill_keys(self::TARGET_CURRENCIES, false);
        }

        $results = [];

        foreach (self::TARGET_CURRENCIES as $currency) {
            $rate = $rates[$currency] ?? null;

            if (! is_numeric($rate) || (float) $rate <= 0) {
                Log::warning("Exchange rate refresh: missing or invalid rate for {$currency}", ['value' => $rate]);
                $results[$currency] = false;

                continue;
            }

            ExchangeRate::updateOrCreate(
                ['currency' => $currency],
                ['rate' => (float) $rate, 'fetched_at' => now(), 'source' => self::API_URL]
            );

            $results[$currency] = true;
        }

        return $results;
    }
}
