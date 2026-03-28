<?php

namespace App\Http\Requests\Checkout;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $allowedCountries = array_keys(config('store.shipping.countries', []));

        return [
            'shipping_first_name' => ['required', 'string', 'max:120'],
            'shipping_last_name' => ['required', 'string', 'max:120'],
            'shipping_phone' => ['required', 'string', 'max:48', 'regex:/^[0-9+\s\-()]+$/'],
            'shipping_street' => ['required', 'string', 'max:255'],
            'shipping_building' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_region' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['nullable', 'string', 'max:24'],
            'shipping_country' => ['required', 'string', Rule::in($allowedCountries)],
            'shipping_delivery_notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shipping_country.in' => 'Choose Kosovo, Albania, or North Macedonia.',
            'shipping_phone.regex' => 'Enter a valid phone number.',
        ];
    }
}
