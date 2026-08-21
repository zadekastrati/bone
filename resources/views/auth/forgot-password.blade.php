@extends('layouts.app')

@section('title', __('Forgot password'))
@section('noindex', 'true')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ __('Reset your password') }}</h1>
            <p class="text-muted mt-2">{{ __('Enter your account email and we\'ll send you a 6-digit code to reset your password.') }}</p>
        </div>

        <form method="POST" action="{{ route('password.reset.send') }}" class="panel mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input @error('email') form-input-error @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-primary w-full py-3">{{ __('Send code') }}</button>
        </form>

        <p class="mt-8 text-center text-sm text-ink-600">
            {{ __('Remembered it?') }}
            <a href="{{ route('login') }}" class="link-brand">{{ __('Log in') }}</a>
        </p>
    </div>
@endsection
