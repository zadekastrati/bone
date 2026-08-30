<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            // ISO 4217 code of the target currency (EUR is the base and is
            // never stored here — it's always exactly 1).
            $table->string('currency', 3)->unique();
            $table->decimal('rate', 12, 6);
            // When the rate that's actually stored was fetched — kept
            // separate from updated_at so "how stale is this" is unambiguous
            // even if a future migration ever touches other columns.
            $table->timestamp('fetched_at');
            // Which provider it came from, for troubleshooting a bad rate.
            $table->string('source', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
