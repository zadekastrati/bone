@extends('layouts.app')

@section('title', __('Checkout'))
@section('noindex', 'true')

@section('content')
    @php
        $defaultFirst = old('shipping_first_name', auth()->user()->first_name ?? '');
        $defaultLast = old('shipping_last_name', auth()->user()->last_name ?? '');
    @endphp
    <x-page-header :title="__('Checkout')" :subtitle="__('Enter shipping details and choose bank transfer or cash on delivery. You will see bank instructions after you place the order if you choose transfer.')" />

    <div
        class="mt-10 grid gap-12 lg:grid-cols-5"
        x-data='{
            country: @json(old("shipping_country", $defaultCountry)),
            subtotal: {{ json_encode((float) $subtotal) }},
            rates: @json($shippingRateMap),
            displayCurrency: @json($displayCurrency),
            get shipping() { return this.rates[this.country] ?? "0.00"; },
            get total() { return (parseFloat(this.subtotal) + parseFloat(this.shipping)).toFixed(2); },
            format(baseAmount) {
                const c = this.displayCurrency;
                const value = (parseFloat(baseAmount) || 0) * c.rate;
                const parts = value.toFixed(c.decimals).split(".");
                const withThousands = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, c.thousands_separator);
                const number = c.decimals > 0 ? withThousands + c.decimal_separator + parts[1] : withThousands;
                return c.symbol_position === "before" ? c.symbol + number : number + " " + c.symbol;
            }
        }'
    >
        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-8 lg:col-span-3">
            @csrf

            <fieldset class="checkout-fieldset">
                <legend class="checkout-fieldset__legend">{{ __('Shipping') }}</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_first_name" class="form-label">{{ __('First name') }}</label>
                        <input type="text" name="shipping_first_name" id="shipping_first_name" value="{{ $defaultFirst }}" class="form-input @error('shipping_first_name') form-input-error @enderror" required autocomplete="given-name">
                        @error('shipping_first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="shipping_last_name" class="form-label">{{ __('Last name') }}</label>
                        <input type="text" name="shipping_last_name" id="shipping_last_name" value="{{ $defaultLast }}" class="form-input @error('shipping_last_name') form-input-error @enderror" required autocomplete="family-name">
                        @error('shipping_last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="shipping_phone" class="form-label">{{ __('Phone number') }}</label>
                    <p class="text-xs text-ink-500">{{ __('For delivery updates and if the courier cannot find you.') }}</p>
                    <input type="text" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone') }}" class="form-input mt-1.5 @error('shipping_phone') form-input-error @enderror" required autocomplete="tel">
                    @error('shipping_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_street" class="form-label">{{ __('Street / road') }}</label>
                    <input type="text" name="shipping_street" id="shipping_street" value="{{ old('shipping_street') }}" class="form-input @error('shipping_street') form-input-error @enderror" required autocomplete="address-line1" placeholder="{{ __('e.g. Mother Teresa Boulevard') }}">
                    @error('shipping_street')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_building" class="form-label">{{ __('House, building, apartment') }} <span class="font-normal text-ink-400">({{ __('optional') }})</span></label>
                    <input type="text" name="shipping_building" id="shipping_building" value="{{ old('shipping_building') }}" class="form-input" autocomplete="address-line2" placeholder="{{ __('No. 12, Building A, Apt 5') }}">
                </div>
                <div>
                    <label for="shipping_city" class="form-label">{{ __('City / municipality') }}</label>
                    <input type="text" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}" class="form-input @error('shipping_city') form-input-error @enderror" required autocomplete="address-level2" placeholder="{{ __('e.g. Pristina, Tirana, Skopje') }}">
                    @error('shipping_city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_region" class="form-label">{{ __('Region / district / county') }} <span class="font-normal text-ink-400">({{ __('optional') }})</span></label>
                    <input type="text" name="shipping_region" id="shipping_region" value="{{ old('shipping_region') }}" class="form-input" placeholder="{{ __('Helpful for rural areas') }}">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_postal_code" class="form-label">{{ __('Postal / ZIP code') }} <span class="font-normal text-ink-400">({{ __('optional') }})</span></label>
                        <input type="text" name="shipping_postal_code" id="shipping_postal_code" value="{{ old('shipping_postal_code') }}" class="form-input" autocomplete="postal-code">
                    </div>
                    <div>
                        <label for="shipping_country" class="form-label">{{ __('Country') }}</label>
                        <select
                            name="shipping_country"
                            id="shipping_country"
                            x-model="country"
                            class="form-select w-full @error('shipping_country') form-input-error @enderror"
                            required
                        >
                            @foreach ($shippingCountries as $code => $info)
                                <option value="{{ $code }}">{{ __($info['label']) }} — <x-price :amount="$info['amount']" /> {{ __('shipping') }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-500">{{ __('Kosovo (XK), Albania (AL), or North Macedonia (MK) only.') }}</p>
                        @error('shipping_country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="shipping_delivery_notes" class="form-label">{{ __('Delivery instructions') }} <span class="font-normal text-ink-400">({{ __('optional') }})</span></label>
                    <p class="text-xs text-ink-500">{{ __('Landmarks, gate codes, building entry, or other directions for the driver.') }}</p>
                    <textarea name="shipping_delivery_notes" id="shipping_delivery_notes" rows="3" class="form-input mt-1.5 min-h-[6rem] resize-y py-3">{{ old('shipping_delivery_notes') }}</textarea>
                </div>
            </fieldset>

            <fieldset class="checkout-fieldset">
                <legend class="checkout-fieldset__legend">{{ __('Payment') }}</legend>
                @foreach (\App\Enums\PaymentMethod::cases() as $method)
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-ink-200/70 bg-white/90 p-5 shadow-sm transition hover:border-ink-300/90 has-[:checked]:border-accent-400 has-[:checked]:bg-accent-50/25 has-[:checked]:shadow-md has-[:checked]:ring-2 has-[:checked]:ring-accent-500/20">
                        <input type="radio" name="payment_method" value="{{ $method->value }}" class="mt-1" {{ old('payment_method', \App\Enums\PaymentMethod::BankTransfer->value) === $method->value ? 'checked' : '' }} required>
                        <span>
                            <span class="font-semibold text-ink-950">{{ $method->label() }}</span>
                            @if ($method === \App\Enums\PaymentMethod::BankTransfer)
                                <span class="mt-1 block text-sm text-ink-600">{{ __('You will receive IBAN / reference details on the confirmation page. Mark as paid only after your transfer clears.') }}</span>
                            @else
                                <span class="mt-1 block text-sm text-ink-600">{{ __('Pay the courier when your parcel arrives.') }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </fieldset>

            <div class="checkout-fieldset space-y-4">
                <h2 class="checkout-fieldset__legend">{{ __('Notes') }}</h2>
                <div>
                    <label for="customer_notes" class="form-label">{{ __('Order notes') }} <span class="font-normal text-ink-400">({{ __('optional') }})</span></label>
                    <p class="text-xs text-ink-500">{{ __('Anything about your order (not the address) — gifts, sizing, or special requests.') }}</p>
                    <textarea name="customer_notes" id="customer_notes" rows="3" class="form-input mt-1.5 min-h-[6rem] resize-y py-3">{{ old('customer_notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary px-10 py-3">{{ __('Place order') }}</button>
                <a href="{{ route('cart.index') }}" class="btn-secondary px-6 py-3">{{ __('Back to cart') }}</a>
            </div>
        </form>

        <aside class="lg:col-span-2">
            <div class="checkout-summary sticky top-24 space-y-5">
                <h2 class="font-display text-xs font-bold uppercase tracking-[0.18em] text-ink-500">{{ __('Order summary') }}</h2>
                <ul class="space-y-3 text-sm text-ink-700">
                    @foreach ($lines as $line)
                        @php
                            $v = $line['variant'];
                            $product = $v->product;
                            $thumb = $product->thumbnailImage();
                        @endphp
                        <li class="flex items-start justify-between gap-4">
                            <span class="flex min-w-0 items-start gap-3">
                                <x-product-image-thumb :path="$thumb?->path" :thumb-src="$thumb?->thumbUrl()" :alt="$product->name" size="sm" />
                                <span class="min-w-0 leading-snug">{{ $product->name }} <span class="text-ink-500">× {{ $line['quantity'] }}</span></span>
                            </span>
                            <span class="shrink-0 font-medium"><x-price :amount="$line['line_total']" /></span>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-ink-200/80 pt-4 text-sm">
                    <div class="flex justify-between text-ink-600">
                        <span>{{ __('Subtotal') }}</span>
                        <span><x-price :amount="$subtotal" /></span>
                    </div>
                    <div class="mt-2 flex justify-between text-ink-600">
                        <span>{{ __('Shipping') }}</span>
                        <span x-text="format(shipping)"></span>
                    </div>
                    <div class="mt-4 flex justify-between text-base font-bold text-ink-950">
                        <span>{{ __('Total') }}</span>
                        <span x-text="format(total)"></span>
                    </div>
                    <p
                        x-show="displayCurrency.rate !== 1"
                        x-cloak
                        class="mt-1 text-right text-xs text-ink-500"
                    >
                        {{ __('Charged as') }} <strong x-text="'€' + Number(total).toFixed(2)"></strong> — {{ __('prices are set in EUR; the amount above is a converted estimate.') }}
                    </p>
                </div>
            </div>
        </aside>
    </div>
@endsection
