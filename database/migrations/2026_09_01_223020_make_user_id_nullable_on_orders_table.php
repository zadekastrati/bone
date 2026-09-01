<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        // Plain MODIFY COLUMN (no doctrine/dbal dependency, unlike ->change()).
        DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('orders', function (Blueprint $table): void {
            // Nullable so guest orders (no account) can be stored. The
            // cascadeOnDelete behavior for orders that DO belong to a user is
            // unchanged — it simply never fires for a null user_id row.
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
