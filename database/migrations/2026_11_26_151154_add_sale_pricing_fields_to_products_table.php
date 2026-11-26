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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('original_price', 8, 2)->nullable()->after('price');
            $table->unsignedTinyInteger('discount_percent')->nullable()->after('original_price');
            $table->timestamp('sale_starts_at')->nullable()->after('discount_percent');
            $table->timestamp('sale_ends_at')->nullable()->after('sale_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_percent', 'sale_starts_at', 'sale_ends_at']);
        });
    }
};
