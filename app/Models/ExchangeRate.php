<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A live-fetched EUR -> $currency rate, refreshed on a schedule (see
 * App\Console\Commands\RefreshExchangeRatesCommand) and read by
 * App\Services\CurrencyService for all customer-facing price conversion.
 * Never written to on a web request — only the scheduled job persists here.
 */
class ExchangeRate extends Model
{
    protected $fillable = [
        'currency',
        'rate',
        'fetched_at',
        'source',
    ];

    protected $casts = [
        'rate' => 'float',
        'fetched_at' => 'datetime',
    ];
}
