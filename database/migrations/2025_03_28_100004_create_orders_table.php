<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32);
            $table->string('payment_method', 32);
            $table->string('payment_status', 32);
            $table->string('shipping_full_name');
            $table->string('shipping_phone', 32);
            $table->string('shipping_line1');
            $table->string('shipping_line2')->nullable();
            $table->string('shipping_city', 64);
            $table->string('shipping_postal_code', 24);
            $table->string('shipping_country', 2)->default('US');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
