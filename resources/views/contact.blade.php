@extends('layouts.app')

@section('title', __('Contact Us'))

@section('content')
    <div class="mx-auto max-w-2xl py-12">
        <h1 class="heading-page mb-8 text-center" id="contact-heading">{{ __('Contact Us') }}</h1>

        <div class="panel p-8 sm:p-12 relative" aria-labelledby="contact-heading">
            <p class="text-center text-muted mb-8 text-lg">
                {{ __('Questions about an order, our products, or finding what is right for you? We\'re here to help.') }}
            </p>

            @auth
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Email') }}</label>
                        <p class="form-input flex items-center bg-zinc-50 text-ink-600">{{ auth()->user()->email }}</p>
                        <p class="mt-1.5 text-xs text-ink-400">{{ __('We\'ll reply to your account email.') }}</p>
                    </div>
                    <div>
                        <label for="message" class="form-label">{{ __('Message') }}</label>
                        <textarea id="message" name="message" rows="5" class="form-textarea" required>{{ old('message') }}</textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full py-4 text-sm">{{ __('Send Message') }}</button>
                    </div>
                </form>
            @else
                <div class="text-center">
                    <p class="text-ink-600">{{ __('Please log in or create an account to send us a message.') }}</p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('login') }}" class="btn-primary px-8 py-3 text-sm">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="btn-secondary px-8 py-3 text-sm">{{ __('Create account') }}</a>
                    </div>
                </div>
            @endauth
        </div>

        <div class="mt-8 text-center text-sm text-ink-500">
            <p>{{ __('Our support team typically responds within 24 hours.') }}</p>
        </div>
    </div>
@endsection
