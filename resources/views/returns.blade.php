@extends('layouts.app')

@section('title', __('Returns'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="returns-heading">{{ __('Returns') }}</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="returns-heading">
            <div class="space-y-6 text-sm leading-relaxed text-ink-700">
                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Return window') }}</h2>
                    <p class="mt-2">
                        {{ __('You may request a return for an eligible online purchase within 2-4 business days of delivery of your order, subject to applicable consumer protection law.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Condition requirements') }}</h2>
                    <p class="mt-2">
                        {{ __('Items may be inspected for fit and suitability but should otherwise be returned unworn, unwashed, with original tags attached, and in their original condition.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('You may be responsible for any reduction in value caused by handling beyond what is reasonably necessary to inspect the item.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('For health and hygiene reasons, certain sealed products may not be eligible for return once unsealed, where permitted by applicable law.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Return costs') }}</h2>
                    <p class="mt-2">
                        {{ __('For eligible change-of-mind returns, customers are responsible for return postage unless otherwise required by applicable law.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('If an item is faulty, damaged, or incorrect due to our error, BONÉ will cover the applicable return costs.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Exchanges') }}</h2>
                    <p class="mt-2">
                        {{ __('If you would prefer to exchange an item, exchanges are subject to product and size availability.') }}
                    </p>
                    <p class="mt-2">{{ __('A postage fee applies:') }}</p>
                    <ul class="mt-2 list-disc space-y-2 pl-5">
                        <li>{{ __(':country: :fee', ['country' => 'Kosovo', 'fee' => '€3']) }}</li>
                        <li>{{ __(':country: :fee', ['country' => 'Albania and North Macedonia', 'fee' => '€7']) }}</li>
                    </ul>
                    <p class="mt-2">
                        {{ __('No exchange fee applies if the item is faulty, damaged, or incorrect due to our error.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('How to start a return or exchange') }}</h2>
                    <p class="mt-2">
                        {!! __('Contact us through the :link page with your order number and whether you are requesting a return or exchange. We will provide the next steps and return instructions.', ['link' => '<a href="'.route('contact').'" class="font-medium text-accent-700 hover:text-accent-600">'.__('Contact Us').'</a>']) !!}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Refunds') }}</h2>
                    <p class="mt-2">
                        {{ __('Once your return is received and approved, your refund will be processed using the original payment method where possible, or another agreed method.') }}
                    </p>
                    <p class="mt-2">
                        {{ __('Refunds will be processed within the timeframe required by applicable consumer protection law.') }}
                    </p>
                </section>
            </div>
        </div>
    </div>
@endsection
