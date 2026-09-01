<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * Validates a phone number's length, prefix, and format against the
 * country selected at checkout (region code, e.g. XK/AL/MK), using Google's
 * libphonenumber metadata rather than a loose digits-only regex.
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

            return;
        }

        if ($region !== null && ! $util->isValidNumberForRegion($parsed, $region)) {
            $fail(__('Enter a valid phone number for the selected country.'));
        }
    }
}
