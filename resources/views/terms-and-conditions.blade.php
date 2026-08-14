@extends('layouts.app')

@section('title', __('Terms & Conditions'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="terms-heading">{{ __('Terms & Conditions') }}</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="terms-heading">
            <p class="text-sm text-ink-500">{{ __('Last updated: :date', ['date' => now()->format('F j, Y')]) }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-ink-700">
                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('1. General') }}</h2>
                    <p class="mt-2">
                        {{ __('By using this storefront, you agree to these terms. If you do not agree, please do not use the site.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('2. Orders and Pricing') }}</h2>
                    <p class="mt-2">
                        {{ __('Product availability, pricing, and descriptions may change at any time. We reserve the right to cancel orders that cannot be fulfilled or contain pricing errors.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('3. Payments') }}</h2>
                    <p class="mt-2">
                        {{ __('You must provide accurate billing and shipping details. Orders are processed after payment confirmation based on the selected payment method.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('4. Shipping and Returns') }}</h2>
                    <p class="mt-2">
                        {{ __('Delivery times are estimates and may vary. Return eligibility and timelines are governed by the posted return policy available on this site.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('5. Contact') }}</h2>
                    <p class="mt-2">
                        {!! __('For questions about these terms, please use the :link page.', ['link' => '<a href="'.route('contact').'" class="font-medium text-accent-700 hover:text-accent-600">'.__('Contact Us').'</a>']) !!}
                    </p>
                </section>
            </div>
        </div>
    </div>
@endsection
