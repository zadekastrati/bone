<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Populated only after a Quipu card payment is verified by
            // re-querying the gateway — never trust the callback alone.
            $table->string('payment_approval_code')->nullable()->after('payment_gateway_order_password');
            $table->string('payment_card_brand')->nullable()->after('payment_approval_code');
            $table->string('payment_card_last_four', 4)->nullable()->after('payment_card_brand');
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_card_last_four');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_approval_code',
                'payment_card_brand',
                'payment_card_last_four',
                'payment_confirmed_at',
            ]);
        });
    }
};
