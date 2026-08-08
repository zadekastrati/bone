@extends('layouts.app')

@section('title', __('Register'))

@section('content')
    <div class="mx-auto max-w-md">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ __('Create your account') }}</h1>
            <p class="text-muted mt-2">{!! __('New accounts use the :role role. We\'ll email you a code to confirm your address before your account is created.', ['role' => '<strong>'.__('user').'</strong>']) !!}</p>
        </div>

        <form
            method="POST"
            action="{{ route('register') }}"
            class="panel mt-8 space-y-5"
            x-data="{
                password: '',
                submitBlocked: false,
                get hasMinLength() { return this.password.length >= 8; },
                get hasUpper() { return /[A-Z]/.test(this.password); },
                get hasLower() { return /[a-z]/.test(this.password); },
                get hasNumber() { return /[0-9]/.test(this.password); },
                get hasSymbol() { return /[^A-Za-z0-9]/.test(this.password); },
                get isValid() { return this.hasMinLength && this.hasUpper && this.hasLower && this.hasNumber && this.hasSymbol; },
            }"
            @submit="if (! isValid) { $event.preventDefault(); submitBlocked = true; } else { submitBlocked = false; }"
        >
            @csrf
            <div>
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="form-input @error('name') form-input-error @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="username" class="form-input @error('email') form-input-error @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" id="password" x-model="password" required autocomplete="new-password" class="form-input @error('password') form-input-error @enderror">
                <x-password-requirements />
                @if ($errors->has('password'))
                    <ul class="mt-1 space-y-0.5 text-sm text-red-600">
                        @foreach ($errors->get('password') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-1 text-sm text-red-600" x-show="submitBlocked && ! isValid" x-cloak>{{ __('Please meet all password requirements above.') }}</p>
            </div>
            <div>
                <label for="password_confirmation" class="form-label">{{ __('Confirm password') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="form-input">
            </div>
            <button type="submit" class="btn-primary w-full py-3">{{ __('Register') }}</button>
        </form>

        <p class="mt-8 text-center text-sm text-ink-600">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}" class="link-brand">{{ __('Log in') }}</a>
        </p>
    </div>
@endsection
