@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
    <div class="mx-auto max-w-2xl py-12">
        <h1 class="heading-page mb-8 text-center" id="contact-heading">Contact Us</h1>
        
        <div class="panel p-8 sm:p-12 relative" aria-labelledby="contact-heading">
            <p class="text-center text-muted mb-8 text-lg">
                Have a question about an order, our products, or just want to say hi? We'd love to hear from you.
            </p>

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" rows="5" class="form-textarea" required>{{ old('message') }}</textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full py-4 text-sm">Send Message</button>
                </div>
            </form>
        </div>
        
        <div class="mt-8 text-center text-sm text-ink-500">
            <p>Our support team typically responds within 24 hours.</p>
        </div>
    </div>
@endsection
