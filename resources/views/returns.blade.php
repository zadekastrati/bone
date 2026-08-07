@extends('layouts.app')

@section('title', __('Returns'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="returns-heading">{{ __('Returns') }}</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="returns-heading">
            <p class="text-sm text-ink-500">{{ __('Last updated: :date', ['date' => now()->format('F j, Y')]) }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-ink-700">
                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Return window') }}</h2>
                    <p class="mt-2">
                        {{ __('You can request a return within 30 days of delivery for eligible items in original condition.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Condition requirements') }}</h2>
                    <p class="mt-2">
                        {{ __('Items must be unworn, unwashed, and returned with original tags attached.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('How to start a return') }}</h2>
                    <p class="mt-2">
                        {!! __('Contact support from the :link page with your order number and reason for return.', ['link' => '<a href="'.route('contact').'" class="font-medium text-accent-700 hover:text-accent-600">'.__('Contact Us').'</a>']) !!}
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ __('Refund timing') }}</h2>
                    <p class="mt-2">
                        {{ __('Approved refunds are issued to the original payment method within 5-10 business days after inspection.') }}
                    </p>
                </section>
            </div>
        </div>
    </div>
@endsection
