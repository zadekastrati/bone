<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Companion to ApplyBlackFridaySaleCommand — restores every product that
 * command touched back to its real price and clears the sale fields.
 * Matches on `original_price` not being null, so it only ever reverts
 * products this specific sale actually changed.
 */
class RevertBlackFridaySaleCommand extends Command
{
    protected $signature = 'sale:revert-black-friday {--dry-run : Preview changes without writing anything}';

    protected $description = 'Revert the Nov 2026 Black Friday sale — restores original prices and clears sale fields.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'DRY RUN — no changes will be written.' : 'LIVE RUN — changes will be written now.');
        $this->newLine();

        $products = Product::query()->whereNotNull('original_price')->get();

        if ($products->isEmpty()) {
            $this->info('No products currently have a sale price set — nothing to revert.');

            return self::SUCCESS;
        }

        foreach ($products as $product) {
            $this->line(sprintf(
                '%s "%s": %s -> %s',
                $dryRun ? '[would revert]' : '[reverting]',
                $product->name,
                (string) $product->price,
                (string) $product->original_price,
            ));

            if (! $dryRun) {
                $product->update([
                    'price' => $product->original_price,
                    'original_price' => null,
                    'discount_percent' => null,
                    'sale_starts_at' => null,
                    'sale_ends_at' => null,
                ]);
            }
        }

        $this->newLine();
        $this->info("Products reverted: {$products->count()}");

        return self::SUCCESS;
    }
}
