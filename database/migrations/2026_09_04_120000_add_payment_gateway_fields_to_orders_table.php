<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Only set for card orders — the Quipu order id/password returned
            // when the payment was created, needed to look up its status later.
            $table->string('payment_gateway_order_id')->nullable()->after('payment_status');
            $table->string('payment_gateway_order_password')->nullable()->after('payment_gateway_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['payment_gateway_order_id', 'payment_gateway_order_password']);
        });
    }
};
