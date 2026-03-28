@extends('layouts.app')

@section('title', 'Confirm your email')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">Almost there</h1>
            <p class="text-muted mt-2">
                We sent a 6-digit code to <span class="font-medium text-ink-800">{{ $emailMasked }}</span>. Enter it below to confirm this inbox is yours and finish signing up.
            </p>
        </div>

        <form method="POST" action="{{ route('register.verify.store') }}" class="panel mt-8 space-y-5">
            @csrf
            <div>
                <label for="code" class="form-label">Verification code</label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    value="{{ old('code') }}"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    required
                    autofocus
                    placeholder="000000"
                    class="form-input text-center font-mono text-2xl tracking-[0.4em] @error('code') form-input-error @enderror"
                >
                @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-primary w-full py-3">Create my account</button>
        </form>

        <form method="POST" action="{{ route('register.resend') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm font-medium text-accent-700 hover:text-accent-900">Resend code</button>
        </form>

        <form method="POST" action="{{ route('register.cancel') }}" class="mt-8 text-center">
            @csrf
            <button type="submit" class="text-sm font-medium text-ink-600 underline decoration-ink-300 underline-offset-2 hover:text-ink-900">
                Cancel and start over
            </button>
        </form>
    </div>
@endsection
