<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_first_name')->nullable()->after('payment_status');
            $table->string('shipping_last_name')->nullable()->after('shipping_first_name');
            $table->string('shipping_street')->nullable()->after('shipping_last_name');
            $table->string('shipping_building')->nullable()->after('shipping_street');
            $table->string('shipping_region')->nullable()->after('shipping_city');
            $table->text('shipping_delivery_notes')->nullable()->after('shipping_country');
        });

        $rows = DB::table('orders')->select(
            'id',
            'shipping_full_name',
            'shipping_line1',
            'shipping_line2'
        )->get();

        foreach ($rows as $row) {
            $name = trim((string) $row->shipping_full_name);
            $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY);
            $first = $parts[0] ?? '';
            $last = $parts[1] ?? '';
            if ($last === '' && $first !== '') {
                $last = $first;
            }

            DB::table('orders')->where('id', $row->id)->update([
                'shipping_first_name' => $first,
                'shipping_last_name' => $last,
                'shipping_street' => $row->shipping_line1,
                'shipping_building' => $row->shipping_line2,
            ]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_full_name', 'shipping_line1', 'shipping_line2']);
        });

        DB::table('orders')->whereNull('shipping_first_name')->update(['shipping_first_name' => '']);
        DB::table('orders')->whereNull('shipping_last_name')->update(['shipping_last_name' => '']);
        DB::table('orders')->whereNull('shipping_street')->update(['shipping_street' => '']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY shipping_first_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE orders MODIFY shipping_last_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE orders MODIFY shipping_street VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE orders MODIFY shipping_phone VARCHAR(48) NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_full_name')->nullable();
            $table->string('shipping_line1')->nullable();
            $table->string('shipping_line2')->nullable();
        });

        $rows = DB::table('orders')->select(
            'id',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_street',
            'shipping_building'
        )->get();

        foreach ($rows as $row) {
            $full = trim($row->shipping_first_name.' '.$row->shipping_last_name);
            DB::table('orders')->where('id', $row->id)->update([
                'shipping_full_name' => $full !== '' ? $full : '—',
                'shipping_line1' => $row->shipping_street ?? '—',
                'shipping_line2' => $row->shipping_building,
            ]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_first_name',
                'shipping_last_name',
                'shipping_street',
                'shipping_building',
                'shipping_region',
                'shipping_delivery_notes',
            ]);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY shipping_full_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE orders MODIFY shipping_line1 VARCHAR(255) NOT NULL');
        }
    }
};
