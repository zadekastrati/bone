<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Created directly rather than via User::factory() — every field is
     * overridden anyway, and the factory's definition() still calls Faker
     * to build its base array even when every value gets replaced, which
     * fails in any environment built with `composer install --no-dev`
     * (fakerphp/faker is dev-only, so it's not present on deployed builds).
     */
    public function run(): void
    {
        User::forceCreate([
            'first_name' => 'Site',
            'last_name' => 'Administrator',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'remember_token' => Str::random(10),
        ]);

        User::forceCreate([
            'first_name' => 'Demo',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
            'remember_token' => Str::random(10),
        ]);

        $this->call([
            StoreSeeder::class,
        ]);
    }
}
