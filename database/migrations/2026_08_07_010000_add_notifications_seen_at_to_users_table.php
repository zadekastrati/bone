<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('notifications_seen_at')->nullable()->after('role');
        });

        // Existing admins start with a clean slate — only orders/messages
        // created after this migration count as unread.
        DB::table('users')->update(['notifications_seen_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('notifications_seen_at');
        });
    }
};
