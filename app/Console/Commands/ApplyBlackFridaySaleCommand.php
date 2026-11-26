<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * One-off command for the Nov 2026 Black Friday sale. The data below is
 * transcribed from the client's PDF, with four SKU typos corrected against
 * the real production data (verified via `railway ssh` on 2026-11-26):
 * "BASICSKIRT" -> "BASIC-SKIRT" (3 rows) and "BLKCHG" -> "BLK-CHG" (1 row).
 * The sale fields (price/original_price/discount_percent/sale dates) live
 * on `products`, not `product_variants` — every color of a given product
 * in the client's list always shares the same discount, so one SKU per
 * product is enough to find and discount the whole product.
 *
 * `price` is set directly to the client's stated sale price (not
 * recomputed from discount_percent), since the PDF's own math already
 * matches exactly and this avoids any dependence on live price staying
 * in sync with what the PDF assumed.
 */
class ApplyBlackFridaySaleCommand extends Command
{
    protected $signature = 'sale:apply-black-friday {--dry-run : Preview changes without writing anything}';

    protected $description = 'Apply the Nov 2026 Black Friday sale prices from the client\'s PDF.';

    /**
     * sku => [current_price (as stated in the PDF), discount_percent, sale_price]
     *
     * @var array<string, array{0: string, 1: int, 2: string}>
     */
    private const ROWS = [
        'THE-VERSA-BASIC-SKIRT-BLA-M' => ['36.00', 30, '25.20'],
        'THE-VERSA-BASIC-SKIRT-OLG-M' => ['36.00', 30, '25.20'],
        'THE-VERSA-BASIC-SKIRT-WHI-M' => ['36.00', 30, '25.20'],
        'BONE-TOP-BST-BLK' => ['31.00', 35, '20.15'],
        'BONE-TOP-BST-BUY' => ['31.00', 35, '20.15'],
        'BONE-TOP-BST-MDB' => ['31.00', 35, '20.15'],
        'BONE-TOP-BST-WAP' => ['31.00', 35, '20.15'],
        'BONE-SHT-VRS-BLK' => ['38.00', 30, '26.60'],
        'BONE-SHT-VRS-BUY' => ['38.00', 30, '26.60'],
        'BONE-SHT-VRS-MDB' => ['38.00', 30, '26.60'],
        'BONE-BDY-ZPS-BRB' => ['59.00', 40, '35.40'],
        'BONE-BDY-ZPS-PMB' => ['59.00', 40, '35.40'],
        'BONE-TOP-ACC-BLK' => ['34.00', 30, '23.80'],
        'BONE-TOP-ACC-OLV' => ['34.00', 30, '23.80'],
        'BONE-TOP-ACC-WHT' => ['34.00', 30, '23.80'],
        'BONE-BDY-SSU-BLK' => ['55.00', 50, '27.50'],
        'BONE-BDY-SSU-BLK-CHG-M' => ['55.00', 50, '27.50'],
        'BONE-BDY-SSU-GRY-01' => ['55.00', 50, '27.50'],
        'BONE-TOP-SST-DPE' => ['29.00', 20, '23.20'],
        'BONE-TOP-SST-KHB' => ['29.00', 20, '23.20'],
        'BONE-TOP-SST-PLP' => ['29.00', 20, '23.20'],
        'BONE-LEG-ESL-BTB' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-BLK' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-BUR' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-COF' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-DPE' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-DNB' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-FRG' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-KHB' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-LEP' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-PLP' => ['40.00', 20, '32.00'],
        'BONE-LEG-ESL-SWB' => ['40.00', 20, '32.00'],
        'BONE-TOP-RCT-MDB' => ['32.00', 25, '24.00'],
        'BONE-TOP-RCT-PMB' => ['32.00', 25, '24.00'],
        'BONE-TOP-RCT-RED' => ['32.00', 25, '24.00'],
        'BONE-LEG-SSL-MDB' => ['42.00', 30, '29.40'],
        'BONE-LEG-SSL-PMB' => ['42.00', 30, '29.40'],
        'BONE-LEG-SSL-RED' => ['42.00', 30, '29.40'],
        'BONE-TOP-FRT-BLK' => ['30.00', 25, '22.50'],
        'BONE-TOP-FRT-GRY' => ['30.00', 25, '22.50'],
        'BONE-TOP-FRT-SLB' => ['30.00', 25, '22.50'],
        'BONE-LEG-SCL-BLK' => ['42.00', 20, '33.60'],
        'BONE-LEG-SCL-GRY' => ['42.00', 20, '33.60'],
        'BONE-LEG-SCL-SLB' => ['42.00', 20, '33.60'],
        'BONE-TOP-HLT-BLK' => ['32.00', 15, '27.20'],
        'BONE-TOP-HLT-BUR' => ['32.00', 15, '27.20'],
        'BONE-TOP-HLT-COF' => ['32.00', 15, '27.20'],
        'BONE-BRA-ESB-DPE' => ['25.00', 35, '16.25'],
        'BONE-BRA-ESB-PLP' => ['25.00', 35, '16.25'],
        'BONE-BRA-ESB-WNB' => ['25.00', 35, '16.25'],
        'BONE-SHT-ESS-BTB' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-DPE' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-DNB' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-FRG' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-LEP' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-PLP' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-SWB' => ['32.00', 30, '22.40'],
        'BONE-SHT-ESS-WNB' => ['32.00', 30, '22.40'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Starts the moment this command actually runs (so the display goes
        // live immediately, whenever that is) — the end date stays fixed,
        // since that's the real intended cutoff regardless of start timing.
        $saleStartsAt = now();
        $saleEndsAt = Carbon::parse('2026-11-29 00:00:00', 'Europe/Belgrade')->utc();

        $this->info($dryRun ? 'DRY RUN — no changes will be written.' : 'LIVE RUN — changes will be written now.');
        $this->line("Sale window (UTC): {$saleStartsAt} to {$saleEndsAt}");
        $this->newLine();

        $seenProductIds = [];
        $updated = 0;
        $mismatches = 0;
        $notFound = 0;

        foreach (self::ROWS as $sku => [$expectedCurrentPrice, $discountPercent, $salePrice]) {
            $variant = ProductVariant::query()->where('sku', $sku)->first();

            if ($variant === null) {
                $this->error("NOT FOUND: no product variant with SKU {$sku}");
                $notFound++;

                continue;
            }

            $product = $variant->product;

            if (isset($seenProductIds[$product->id])) {
                continue;
            }
            $seenProductIds[$product->id] = true;

            $actualCurrentPrice = (string) $product->price;

            if (bccomp($actualCurrentPrice, $expectedCurrentPrice, 2) !== 0) {
                $this->warn("PRICE MISMATCH for \"{$product->name}\" ({$sku}): PDF expects current price {$expectedCurrentPrice}, but it's actually {$actualCurrentPrice} in the database. Skipping this product.");
                $mismatches++;

                continue;
            }

            $this->line(sprintf(
                '%s "%s": %s -> %s (-%d%%)',
                $dryRun ? '[would update]' : '[updating]',
                $product->name,
                $actualCurrentPrice,
                $salePrice,
                $discountPercent,
            ));

            if (! $dryRun) {
                $product->update([
                    'original_price' => $actualCurrentPrice,
                    'price' => $salePrice,
                    'discount_percent' => $discountPercent,
                    'sale_starts_at' => $saleStartsAt,
                    'sale_ends_at' => $saleEndsAt,
                ]);
            }

            $updated++;
        }

        $this->newLine();
        $this->info("Products updated: {$updated}");

        if ($mismatches > 0) {
            $this->warn("Price mismatches (skipped): {$mismatches}");
        }

        if ($notFound > 0) {
            $this->error("SKUs not found: {$notFound}");
        }

        return ($mismatches > 0 || $notFound > 0) ? self::FAILURE : self::SUCCESS;
    }
}
