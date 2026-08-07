<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RegistrationOtpService
{
    private const CACHE_PREFIX = 'register_otp:';

    private const TTL_MINUTES = 15;

    private const MAX_ATTEMPTS = 5;

    private function normalizedEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function key(string $email): string
    {
        return self::CACHE_PREFIX.hash('sha256', $this->normalizedEmail($email));
    }

    private function attemptsKey(string $email): string
    {
        return self::CACHE_PREFIX.'attempts:'.hash('sha256', $this->normalizedEmail($email));
    }

    /**
     * Whether an unexpired code still exists for this email — the source of
     * truth for whether a "register_pending" session is still resumable.
     */
    public function hasActiveCode(string $email): bool
    {
        return Cache::has($this->key($email));
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

        $attemptsKey = $this->attemptsKey($email);
        if ((int) Cache::get($attemptsKey, 0) >= self::MAX_ATTEMPTS) {
            $this->forget($email);

            return false;
        }

        $stored = Cache::get($this->key($email));
        if ($stored === null || strlen($code) < 6) {
            return false;
        }

        if (! hash_equals($stored, hash('sha256', $code))) {
            Cache::put($attemptsKey, (int) Cache::get($attemptsKey, 0) + 1, now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        Cache::forget($attemptsKey);

        return true;
    }

    public function forget(string $email): void
    {
        Cache::forget($this->key($email));
        Cache::forget($this->attemptsKey($email));
    }
}
