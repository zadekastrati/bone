@extends('layouts.app')

@section('title', __('Choose a new password'))

@section('content')
    <div class="mx-auto max-w-md">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ __('Choose a new password') }}</h1>
            <p class="text-muted mt-2">
                {!! __('Code verified for :email. Set a new password below.', ['email' => '<span class="font-medium text-ink-800">'.e($emailMasked).'</span>']) !!}
            </p>
        </div>

        <form method="POST" action="{{ route('password.reset.password.store') }}" class="panel mt-8 space-y-5">
            @csrf
            <div>
                <label for="password" class="form-label">{{ __('New password') }}</label>
                <input type="password" name="password" id="password" required autofocus autocomplete="new-password" class="form-input @error('password') form-input-error @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="form-label">{{ __('Confirm new password') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="form-input">
            </div>
            <button type="submit" class="btn-primary w-full py-3">{{ __('Update password') }}</button>
        </form>

        <form method="POST" action="{{ route('password.reset.cancel') }}" class="mt-8 text-center">
            @csrf
            <button type="submit" class="text-sm font-medium text-ink-600 underline decoration-ink-300 underline-offset-2 hover:text-ink-900">
                {{ __('Cancel and start over') }}
            </button>
        </form>
    </div>
@endsection
