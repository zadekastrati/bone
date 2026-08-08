<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('id');
            $table->string('last_name', 100)->nullable()->after('first_name');
        });

        DB::table('users')->select('id', 'name')->orderBy('id')->each(function ($user) {
            $parts = preg_split('/\s+/u', trim((string) $user->name), 2, PREG_SPLIT_NO_EMPTY);

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? 'Unknown',
                'last_name' => $parts[1] ?? '',
            ]);
        });

        DB::statement('ALTER TABLE users MODIFY first_name VARCHAR(100) NOT NULL');
        DB::statement('ALTER TABLE users MODIFY last_name VARCHAR(100) NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('users')->select('id', 'first_name', 'last_name')->orderBy('id')->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'name' => trim($user->first_name.' '.$user->last_name),
            ]);
        });

        DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
