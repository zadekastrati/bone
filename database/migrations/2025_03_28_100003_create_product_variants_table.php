<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('color', 64);
            $table->string('color_hex', 7)->nullable();
            $table->string('size', 32);
            $table->string('sku', 64)->nullable()->unique();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'color', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
