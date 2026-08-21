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
                        {{ __('You must provide accurate billing and shipping details. For cash on delivery orders, payment must be made to the postman/courier before opening the package. Orders are processed after payment confirmation based on the selected payment method.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('4. Shipping, Returns and Exchanges') }}</h2>
                    <ul class="mt-2 list-disc space-y-2 pl-5">
                        <li>{{ __('Returns must be made within 2-4 business days of delivery, and the product must be in its original condition.') }}</li>
                        <li>{{ __('To exchange a product, a postage fee applies: :kosovo within Kosovo, or :other for North Macedonia and Albania.', ['kosovo' => '€3', 'other' => '€7']) }}</li>
                        <li>{{ __('If the item is faulty, damaged, or incorrect due to our error, we cover all return/exchange costs and no fee applies.') }}</li>
                        <li>{{ __('Approved refunds will be issued either through the postman on the day of return, or directly to your bank account.') }}</li>
                        <li>{{ __('Delivery times are estimates and may vary.') }}</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('5. Contact') }}</h2>
                    <p class="mt-2">
                        {!! __('All returns and exchanges are handled through our support team via the :link page. For any questions about these terms, please use the same page.', ['link' => '<a href="'.route('contact').'" class="font-medium text-accent-700 hover:text-accent-600">'.__('Contact Us').'</a>']) !!}
                    </p>
                </section>
            </div>
        </div>
    </div>
@endsection
