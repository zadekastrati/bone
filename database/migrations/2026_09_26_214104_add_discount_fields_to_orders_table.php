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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('discount_code_id')->nullable()->after('payment_confirmed_at')->constrained('discount_codes')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default('0.00')->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_code_id');
            $table->dropColumn('discount_amount');
        });
    }
};
