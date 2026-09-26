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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            // Free-text batch label (e.g. "CORE Pilates") shown in the admin
            // list so a mixed batch of codes stays identifiable later.
            $table->string('label')->nullable();
            $table->unsignedTinyInteger('percent_off');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();

            $table->index('used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
