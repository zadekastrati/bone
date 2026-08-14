@extends('layouts.app')

@section('title', __('Log in'))

@section('content')
    <div class="mx-auto max-w-md">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ __('Welcome back') }}</h1>
            <p class="text-muted mt-2">{{ __('Sign in with the email and password you used when you registered.') }}</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="panel mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input @error('email') form-input-error @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <a href="{{ route('password.reset') }}" class="text-xs font-medium text-accent-700 hover:text-accent-900">{{ __('Forgot password?') }}</a>
                </div>
                <input type="password" name="password" id="password" required autocomplete="current-password" class="form-input">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" value="1" @checked(old('remember')) class="rounded border-ink-300 text-accent-600 focus:ring-accent-500">
                <label for="remember" class="text-sm text-ink-600">{{ __('Remember me') }}</label>
            </div>
            <button type="submit" class="btn-primary w-full py-3">{{ __('Log in') }}</button>
        </form>

        <p class="mt-8 text-center text-sm text-ink-600">
            {{ __('No account?') }}
            <a href="{{ route('register') }}" class="link-brand">{{ __('Register') }}</a>
        </p>
    </div>
@endsection
