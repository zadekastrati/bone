<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'first_name' => 'Site',
            'last_name' => 'Administrator',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'first_name' => 'Demo',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        $this->call([
            StoreSeeder::class,
        ]);
    }
}
