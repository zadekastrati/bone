<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RegistrationOtpService
{
    private const CACHE_PREFIX = 'register_otp:';

    private const TTL_MINUTES = 15;

    private function normalizedEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function key(string $email): string
    {
        return self::CACHE_PREFIX.hash('sha256', $this->normalizedEmail($email));
    }

    public function issue(string $email): string
    {
        $code = (string) random_int(100000, 999999);

        Cache::put(
            $this->key($email),
            hash('sha256', $code),
            now()->addMinutes(self::TTL_MINUTES)
        );

        return $code;
    }

    public function verify(string $email, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        $stored = Cache::get($this->key($email));
        if ($stored === null || strlen($code) < 6) {
            return false;
        }

        return hash_equals($stored, hash('sha256', $code));
    }

    public function forget(string $email): void
    {
        Cache::forget($this->key($email));
    }
}
