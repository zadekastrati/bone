@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    @php
        $accountNameParts = preg_split('/\s+/u', trim((string) auth()->user()->name), 2, PREG_SPLIT_NO_EMPTY);
        $defaultFirst = old('shipping_first_name', $accountNameParts[0] ?? '');
        $defaultLast = old('shipping_last_name', $accountNameParts[1] ?? '');
    @endphp
    <x-page-header title="Checkout" subtitle="Enter shipping details and choose bank transfer or cash on delivery. You will see bank instructions after you place the order if you choose transfer." />

    <div
        class="mt-10 grid gap-12 lg:grid-cols-5"
        x-data="{
            country: @json(old('shipping_country', $defaultCountry)),
            subtotal: {{ json_encode((float) $subtotal) }},
            rates: @json($shippingRateMap),
            symbol: @json(config('store.currency_symbol')),
            get shipping() { return this.rates[this.country] ?? '0.00'; },
            get total() { return (parseFloat(this.subtotal) + parseFloat(this.shipping)).toFixed(2); }
        }"
    >
        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-8 lg:col-span-3">
            @csrf

            <fieldset class="checkout-fieldset">
                <legend class="checkout-fieldset__legend">Shipping</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_first_name" class="form-label">First name</label>
                        <input type="text" name="shipping_first_name" id="shipping_first_name" value="{{ $defaultFirst }}" class="form-input @error('shipping_first_name') form-input-error @enderror" required autocomplete="given-name">
                        @error('shipping_first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="shipping_last_name" class="form-label">Last name</label>
                        <input type="text" name="shipping_last_name" id="shipping_last_name" value="{{ $defaultLast }}" class="form-input @error('shipping_last_name') form-input-error @enderror" required autocomplete="family-name">
                        @error('shipping_last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="shipping_phone" class="form-label">Phone number</label>
                    <p class="text-xs text-ink-500">For delivery updates and if the courier cannot find you.</p>
                    <input type="text" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone') }}" class="form-input mt-1.5 @error('shipping_phone') form-input-error @enderror" required autocomplete="tel">
                    @error('shipping_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_street" class="form-label">Street / road</label>
                    <input type="text" name="shipping_street" id="shipping_street" value="{{ old('shipping_street') }}" class="form-input @error('shipping_street') form-input-error @enderror" required autocomplete="address-line1" placeholder="e.g. Mother Teresa Boulevard">
                    @error('shipping_street')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_building" class="form-label">House, building, apartment <span class="font-normal text-ink-400">(optional)</span></label>
                    <input type="text" name="shipping_building" id="shipping_building" value="{{ old('shipping_building') }}" class="form-input" autocomplete="address-line2" placeholder="No. 12, Building A, Apt 5">
                </div>
                <div>
                    <label for="shipping_city" class="form-label">City / municipality</label>
                    <input type="text" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}" class="form-input @error('shipping_city') form-input-error @enderror" required autocomplete="address-level2" placeholder="e.g. Pristina, Tirana, Skopje">
                    @error('shipping_city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_region" class="form-label">Region / district / county <span class="font-normal text-ink-400">(optional)</span></label>
                    <input type="text" name="shipping_region" id="shipping_region" value="{{ old('shipping_region') }}" class="form-input" placeholder="Helpful for rural areas">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_postal_code" class="form-label">Postal / ZIP code <span class="font-normal text-ink-400">(optional)</span></label>
                        <input type="text" name="shipping_postal_code" id="shipping_postal_code" value="{{ old('shipping_postal_code') }}" class="form-input" autocomplete="postal-code">
                    </div>
                    <div>
                        <label for="shipping_country" class="form-label">Country</label>
                        <select
                            name="shipping_country"
                            id="shipping_country"
                            x-model="country"
                            class="form-select w-full @error('shipping_country') form-input-error @enderror"
                            required
                        >
                            @foreach ($shippingCountries as $code => $info)
                                <option value="{{ $code }}">{{ $info['label'] }} — {{ config('store.currency_symbol') }}{{ $info['amount'] }} shipping</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-500">Kosovo (XK), Albania (AL), or North Macedonia (MK) only.</p>
                        @error('shipping_country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="shipping_delivery_notes" class="form-label">Delivery instructions <span class="font-normal text-ink-400">(optional)</span></label>
                    <p class="text-xs text-ink-500">Landmarks, gate codes, building entry, or other directions for the driver.</p>
                    <textarea name="shipping_delivery_notes" id="shipping_delivery_notes" rows="3" class="form-input mt-1.5 min-h-[6rem] resize-y py-3">{{ old('shipping_delivery_notes') }}</textarea>
                </div>
            </fieldset>

            <fieldset class="checkout-fieldset">
                <legend class="checkout-fieldset__legend">Payment</legend>
                @foreach (\App\Enums\PaymentMethod::cases() as $method)
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-ink-200/70 bg-white/90 p-5 shadow-sm transition hover:border-ink-300/90 has-[:checked]:border-accent-400 has-[:checked]:bg-accent-50/25 has-[:checked]:shadow-md has-[:checked]:ring-2 has-[:checked]:ring-accent-500/20">
                        <input type="radio" name="payment_method" value="{{ $method->value }}" class="mt-1" {{ old('payment_method', \App\Enums\PaymentMethod::BankTransfer->value) === $method->value ? 'checked' : '' }} required>
                        <span>
                            <span class="font-semibold text-ink-950">{{ $method->label() }}</span>
                            @if ($method === \App\Enums\PaymentMethod::BankTransfer)
                                <span class="mt-1 block text-sm text-ink-600">You will receive IBAN / reference details on the confirmation page. Mark as paid only after your transfer clears.</span>
                            @else
                                <span class="mt-1 block text-sm text-ink-600">Pay the courier when your parcel arrives.</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </fieldset>

            <div class="checkout-fieldset space-y-4">
                <h2 class="checkout-fieldset__legend">Notes</h2>
                <div>
                    <label for="customer_notes" class="form-label">Order notes <span class="font-normal text-ink-400">(optional)</span></label>
                    <p class="text-xs text-ink-500">Anything about your order (not the address) — gifts, sizing, or special requests.</p>
                    <textarea name="customer_notes" id="customer_notes" rows="3" class="form-input mt-1.5 min-h-[6rem] resize-y py-3">{{ old('customer_notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary px-10 py-3">Place order</button>
                <a href="{{ route('cart.index') }}" class="btn-secondary px-6 py-3">Back to cart</a>
            </div>
        </form>

        <aside class="lg:col-span-2">
            <div class="checkout-summary sticky top-24 space-y-5">
                <h2 class="font-display text-xs font-bold uppercase tracking-[0.18em] text-ink-500">Order summary</h2>
                <ul class="space-y-3 text-sm text-ink-700">
                    @foreach ($lines as $line)
                        @php
                            $v = $line['variant'];
                        @endphp
                        <li class="flex justify-between gap-4">
                            <span>{{ $v->product->name }} <span class="text-ink-500">× {{ $line['quantity'] }}</span></span>
                            <span class="shrink-0 font-medium">{{ config('store.currency_symbol') }}{{ number_format((float) $line['line_total'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-ink-200/80 pt-4 text-sm">
                    <div class="flex justify-between text-ink-600">
                        <span>Subtotal</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $subtotal, 2) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between text-ink-600">
                        <span>Shipping</span>
                        <span><span x-text="symbol"></span><span x-text="Number(shipping).toFixed(2)"></span></span>
                    </div>
                    <div class="mt-4 flex justify-between text-base font-bold text-ink-950">
                        <span>Total</span>
                        <span><span x-text="symbol"></span><span x-text="total"></span></span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
