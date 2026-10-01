<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * Validates a phone number's length, prefix, and format using Google's
 * libphonenumber metadata rather than a loose digits-only regex. The
 * country selected at checkout (region code, e.g. XK/AL/MK) is only used as
 * the default region for parsing a number typed without a country code —
 * it does NOT require the number itself to belong to that country, since
 * plenty of shoppers (e.g. foreign residents) ship to one country while
 * using a phone number from another.
 */
class ValidPhoneNumber implements ValidationRule
{
    public function __construct(private readonly ?string $region) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail(__('Enter a valid phone number.'));

            return;
        }

        $region = $this->region !== null ? strtoupper($this->region) : null;
        $util = PhoneNumberUtil::getInstance();

        try {
            $parsed = $util->parse($value, $region);
        } catch (NumberParseException) {
            $fail(__('Enter a valid phone number.'));

            return;
        }

        if (! $util->isValidNumber($parsed)) {
            $fail(__('Enter a valid phone number.'));
        }
    }
}
