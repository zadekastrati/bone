<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\User;

class CurrencyService
{
    private const SESSION_KEY = 'store_country';

    /**
     * @return array<string, array{label: string, amount: string, currency: string}>
     */
    public function countries(): array
    {
        return config('store.shipping.countries', []);
    }

    public function currentCountry(): string
    {
        $countries = $this->countries();

        $sessionCountry = session(self::SESSION_KEY);
        if (is_string($sessionCountry) && isset($countries[$sessionCountry])) {
            return $sessionCountry;
        }

        $user = auth()->user();
        if ($user instanceof User && $user->country !== null && isset($countries[$user->country])) {
            return $user->country;
        }

        return config('store.shipping.default_country', 'XK');
    }

    public function currentCurrency(): string
    {
        return $this->currencyForCountry($this->currentCountry());
    }

    public function currencyForCountry(string $countryCode): string
    {
        $countries = $this->countries();

        return $countries[$countryCode]['currency'] ?? config('store.currency', 'EUR');
    }

    /**
     * Persist the shopper's country (and derived currency) choice: in the
     * session for guests, and on the account too when logged in.
     */
    public function setCountry(string $countryCode): void
    {
        $countries = $this->countries();
        if (! isset($countries[$countryCode])) {
            return;
        }

        session([self::SESSION_KEY => $countryCode]);

        $user = auth()->user();
        if ($user instanceof User) {
            $user->update([
                'country' => $countryCode,
                'currency' => $this->currencyForCountry($countryCode),
            ]);
        }
    }

    /**
     * Convert an amount stored in the base store currency (EUR) into the
     * given (or currently selected) display currency.
     */
    public function convert(float|string $baseAmount, ?string $currencyCode = null): float
    {
        $conf = $this->currencyConfig($currencyCode);

        return ((float) $baseAmount) * (float) $conf['rate'];
    }

    /**
     * Format an amount stored in the base store currency (EUR) for display
     * in the given (or currently selected) currency, with the correct
     * symbol placement, decimals, and separators for that currency.
     */
    public function format(float|string $baseAmount, ?string $currencyCode = null): string
    {
        $conf = $this->currencyConfig($currencyCode);
        $value = $this->convert($baseAmount, $currencyCode);

        $number = number_format(
            $value,
            (int) $conf['decimals'],
            (string) $conf['decimal_separator'],
            (string) $conf['thousands_separator']
        );

        return $conf['symbol_position'] === 'before'
            ? $conf['symbol'].$number
            : $number.' '.$conf['symbol'];
    }

    /**
     * @return array{label: string, symbol: string, decimals: int, decimal_separator: string, thousands_separator: string, symbol_position: string, rate: float}
     */
    public function currencyConfig(?string $currencyCode = null): array
    {
        $currencies = config('store.currencies', []);
        $base = config('store.currency', 'EUR');
        $code = $currencyCode ?? $this->currentCurrency();

        $conf = $currencies[$code] ?? $currencies[$base];

        if ($code !== $base) {
            $conf['rate'] = $this->liveRate($code) ?? (float) $conf['rate'];
        }

        return $conf;
    }

    /**
     * The most recently fetched live rate for $code (see
     * ExchangeRateService, run on a schedule — never fetched here). Null
     * when the scheduled job hasn't stored one yet (a brand new deploy) or
     * the value on file is somehow invalid, in which case the caller falls
     * back to the static config rate instead of showing no price at all.
     */
    private function liveRate(string $code): ?float
    {
        $rate = ExchangeRate::where('currency', $code)->value('rate');

        return $rate !== null && (float) $rate > 0 ? (float) $rate : null;
    }
}
