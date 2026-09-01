<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Optional — only ever set for guest orders (no user_id), where
            // it's the sole way to reach the customer about their order.
            $table->string('guest_email')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('guest_email');
        });
    }
};
