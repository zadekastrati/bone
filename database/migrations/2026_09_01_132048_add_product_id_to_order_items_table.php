<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * order_items only ever linked to a product indirectly, through
     * product_variant_id — but that FK is nullOnDelete (see the original
     * create-table migration), and ProductVariantSyncService routinely
     * deletes and recreates variant rows on any product edit. So an order's
     * link to its product (and therefore to the product's images, used for
     * the "My Orders" thumbnail) could silently break long after the order
     * was placed, even though the product itself is untouched. A direct,
     * separate product_id gives that link a stable home that isn't affected
     * by variant churn.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('product_variant_id')->constrained()->nullOnDelete();
        });

        // Backfill every order item whose variant still exists — this covers
        // the overwhelming majority of historical rows, since only products
        // that have since had their variants edited are affected.
        DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->update(['order_items.product_id' => DB::raw('product_variants.product_id')]);

        // Best-effort backfill for the remainder (variant already gone by
        // the time this migration runs): match on the product_name snapshot
        // stored on the order item itself, but only when that name maps to
        // exactly one product — an ambiguous or missing match is left null
        // rather than risk linking an order to the wrong product.
        $orphaned = DB::table('order_items')->whereNull('product_id')->get(['id', 'product_name']);

        foreach ($orphaned as $row) {
            $matches = DB::table('products')->where('name', $row->product_name)->pluck('id');

            if ($matches->count() === 1) {
                DB::table('order_items')->where('id', $row->id)->update(['product_id' => $matches->first()]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
