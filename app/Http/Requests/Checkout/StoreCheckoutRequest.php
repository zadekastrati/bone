<?php

namespace App\Http\Requests\Checkout;

use App\Enums\PaymentMethod;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Guest checkout is allowed — anyone may submit this form regardless
        // of authentication. Rate limiting (route middleware) and the
        // validation rules below carry the abuse/quality protection instead.
        return true;
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
            'shipping_phone' => ['required', 'string', 'max:48', new ValidPhoneNumber($this->input('shipping_country'))],
            // Required for guests (there's no account email to fall back to
            // for their confirmation email); logged-in users already have
            // one on file and don't see this field at all.
            'guest_email' => [$this->user() === null ? 'required' : 'nullable', 'email', 'max:255'],
            'shipping_street' => ['required', 'string', 'max:255'],
            'shipping_building' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_region' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['nullable', 'string', 'max:24'],
            'shipping_country' => ['required', 'string', Rule::in($allowedCountries)],
            'shipping_delivery_notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'terms_accepted' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shipping_country.in' => __('Choose Kosovo, Albania, or North Macedonia.'),
            'terms_accepted.accepted' => __('You must agree to the Terms & Conditions and Return & Refund Policy to place an order.'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $isCard = $this->input('payment_method') === PaymentMethod::Card->value;
            $isBankTransfer = $this->input('payment_method') === PaymentMethod::BankTransfer->value;

            if ($isCard && ! config('services.quipu.enabled')) {
                $validator->errors()->add('payment_method', __('Card payment is not currently available. Please choose a different payment method.'));
            }

            if ($isBankTransfer) {
                $validator->errors()->add('payment_method', __('Bank transfer is not currently available. Please choose a different payment method.'));
            }
        });
    }
}
