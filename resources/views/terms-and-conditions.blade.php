@extends('layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="terms-heading">Terms &amp; Conditions</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="terms-heading">
            <p class="text-sm text-ink-500">Last updated: {{ now()->format('F j, Y') }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-ink-700">
                <section>
                    <h2 class="text-base font-semibold text-ink-900">1. General</h2>
                    <p class="mt-2">
                        By using this storefront, you agree to these terms. If you do not agree, please do not use the site.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">2. Orders and Pricing</h2>
                    <p class="mt-2">
                        Product availability, pricing, and descriptions may change at any time. We reserve the right to cancel orders
                        that cannot be fulfilled or contain pricing errors.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">3. Payments</h2>
                    <p class="mt-2">
                        You must provide accurate billing and shipping details. Orders are processed after payment confirmation based on
                        the selected payment method.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">4. Shipping and Returns</h2>
                    <p class="mt-2">
                        Delivery times are estimates and may vary. Return eligibility and timelines are governed by the posted return
                        policy available on this site.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">5. Contact</h2>
                    <p class="mt-2">
                        For questions about these terms, please use the
                        <a href="{{ route('contact') }}" class="font-medium text-accent-700 hover:text-accent-600">Contact Us</a>
                        page.
                    </p>
                </section>
            </div>
        </div>
    </div>
@endsection
