<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Keyed by user id, not the phone number itself — the code is emailed to the
 * account's own address (no SMS provider configured), so it proves account
 * access rather than phone ownership.
 */
class PhoneChangeOtpService
{
    private const CACHE_PREFIX = 'phone_change_otp:';

    private const TTL_MINUTES = 15;

    private const MAX_ATTEMPTS = 5;

    private function key(int $userId): string
    {
        return self::CACHE_PREFIX.$userId;
    }

    private function attemptsKey(int $userId): string
    {
        return self::CACHE_PREFIX.'attempts:'.$userId;
    }

    public function hasActiveCode(int $userId): bool
    {
        return Cache::has($this->key($userId));
    }

    public function issue(int $userId): string
    {
        $code = (string) random_int(100000, 999999);

        Cache::put(
            $this->key($userId),
            hash('sha256', $code),
            now()->addMinutes(self::TTL_MINUTES)
        );

        return $code;
    }

    public function verify(int $userId, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        $attemptsKey = $this->attemptsKey($userId);
        if ((int) Cache::get($attemptsKey, 0) >= self::MAX_ATTEMPTS) {
            $this->forget($userId);

            return false;
        }

        $stored = Cache::get($this->key($userId));
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

    public function forget(int $userId): void
    {
        Cache::forget($this->key($userId));
        Cache::forget($this->attemptsKey($userId));
    }
}
