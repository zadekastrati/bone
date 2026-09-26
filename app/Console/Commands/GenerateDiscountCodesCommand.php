<?php

namespace App\Console\Commands;

use App\Models\DiscountCode;
use Illuminate\Console\Command;

class GenerateDiscountCodesCommand extends Command
{
    protected $signature = 'discount-codes:generate
        {count=30 : How many unique codes to create}
        {--prefix=CORE : Prefix printed on every code, e.g. CORE-A7B2K9}
        {--percent=30 : Percent off the product subtotal each code gives}
        {--label= : Free-text note shown in the admin list, e.g. "CORE Pilates"}';

    protected $description = 'Generate a batch of single-use, first-order-only discount codes.';

    // Excludes 0/O and 1/I/L — easy to misread on a printed card.
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $prefix = strtoupper((string) $this->option('prefix'));
        $percent = (int) $this->option('percent');
        $label = $this->option('label') ?: null;

        if ($count < 1) {
            $this->error('Count must be at least 1.');

            return self::FAILURE;
        }

        if ($percent < 1 || $percent > 100) {
            $this->error('Percent must be between 1 and 100.');

            return self::FAILURE;
        }

        $codes = [];

        while (count($codes) < $count) {
            $candidate = $prefix.'-'.$this->randomSuffix();

            if (isset($codes[$candidate]) || DiscountCode::query()->where('code', $candidate)->exists()) {
                continue;
            }

            $codes[$candidate] = true;
        }

        foreach (array_keys($codes) as $code) {
            DiscountCode::create([
                'code' => $code,
                'label' => $label,
                'percent_off' => $percent,
            ]);
        }

        $this->info(count($codes)." discount code(s) created ({$percent}% off, first order only, single use):");
        $this->newLine();

        foreach (array_keys($codes) as $code) {
            $this->line($code);
        }

        return self::SUCCESS;
    }

    private function randomSuffix(int $length = 6): string
    {
        $suffix = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $suffix .= self::ALPHABET[random_int(0, $max)];
        }

        return $suffix;
    }
}
