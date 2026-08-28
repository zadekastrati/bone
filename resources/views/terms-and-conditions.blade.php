@extends('layouts.app')

@section('title', __('Terms & Conditions'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="terms-heading">{{ __('Terms & Conditions') }}</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="terms-heading">
            <div class="space-y-6 text-sm leading-relaxed text-ink-700">
                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('1. General') }}</h2>
                    <p class="mt-2">
                        {{ __('These Terms & Conditions apply to purchases made through the BONÉ online store. By placing an order, you agree to these terms.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Nothing in these Terms limits any rights you may have under applicable consumer protection law.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('2. Orders and Pricing') }}</h2>
                    <p class="mt-2">
                        {{ __('All orders are subject to availability and acceptance by BONÉ.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('We make every effort to ensure that product descriptions, images, prices, and availability are accurate. We reserve the right to correct errors or inaccuracies and to cancel or refuse an order where a product is unavailable, an obvious pricing or technical error has occurred, or the order cannot reasonably be fulfilled.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('If payment has already been made for an order that we cancel, the amount paid will be refunded.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('3. Payments') }}</h2>
                    <p class="mt-2">
                        {{ __('Customers must provide accurate and complete billing, contact, and delivery information.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Available payment methods will be displayed at checkout. For cash on delivery orders, payment must be made to the courier in accordance with the applicable delivery terms.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('BONÉ reserves the right to delay or cancel an order if payment cannot be confirmed or if the information provided is incomplete or inaccurate.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('4. Shipping') }}</h2>
                    <p class="mt-2">
                        {{ __('We aim to deliver orders within the estimated delivery timeframe provided at checkout or during order confirmation. Delivery times are estimates and may be affected by circumstances outside our reasonable control.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Customers are responsible for providing complete and accurate delivery information. BONÉ is not responsible for delays resulting from incorrect or incomplete information provided by the customer.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('5. Returns, Exchanges and Refunds') }}</h2>
                    <p class="mt-2">
                        {{ __('Eligible purchases may be returned in accordance with applicable consumer protection law and our Returns Policy. Return eligibility, conditions, exchange fees, return costs, and refund procedures are explained on our Returns page.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('If a product is faulty, damaged, or incorrect due to our error, BONÉ will provide an appropriate remedy and cover applicable return costs in accordance with applicable law.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Nothing in our Returns Policy or these Terms limits your mandatory consumer rights.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('6. Faulty, Damaged or Incorrect Products') }}</h2>
                    <p class="mt-2">
                        {{ __('If you receive a product that is faulty, damaged, or different from what you ordered, please contact BONÉ as soon as reasonably possible.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Where BONÉ is responsible for the issue, we will cover the applicable return or exchange costs and provide an appropriate remedy in accordance with applicable consumer protection law.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Nothing in this section limits your statutory rights regarding faulty or non-conforming goods.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('7. Refunds') }}</h2>
                    <p class="mt-2">
                        {{ __('Eligible refunds will be processed in accordance with applicable law after BONÉ has received the returned product or appropriate evidence that it has been returned.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Refunds will normally be made using the original payment method where possible, unless another method is agreed with the customer.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('8. Intellectual Property') }}</h2>
                    <p class="mt-2">
                        {{ __('All content on the BONÉ website, including the BONÉ name, logo, branding, product photography, graphics, text, and other original content, is owned by or licensed to BONÉ and may not be copied, reproduced, distributed, or used for commercial purposes without prior permission.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('9. Limitation of Liability') }}</h2>
                    <p class="mt-2">
                        {{ __('BONÉ is not responsible for delays or failure to perform obligations caused by circumstances outside our reasonable control.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('To the extent permitted by applicable law, BONÉ will not be liable for indirect or consequential losses arising from the use of this website or the purchase of our products.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Nothing in these Terms excludes or limits liability where doing so would be prohibited by applicable law or restricts a customer\'s mandatory consumer rights.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('10. Changes to These Terms') }}</h2>
                    <p class="mt-2">
                        {{ __('We may update these Terms & Conditions from time to time to reflect changes to our business, services, or applicable legal requirements.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('The Terms applicable to an order will be those in effect when the order is placed.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('11. Contact and Business Information') }}</h2>
                    <p class="mt-2">
                        {!! __('Returns, exchanges, complaints, and questions regarding these Terms can be submitted through our :link page.', ['link' => '<a href="'.route('contact').'" class="font-medium text-accent-700 hover:text-accent-600">'.__('Contact Us').'</a>']) !!}
                    </p>
                </section>
            </div>
        </div>
    </div>
@endsection
