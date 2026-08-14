@extends('layouts.admin')

@section('title', 'New user')

@section('content')
    <div class="mx-auto max-w-xl">
        <x-page-header title="Create user" subtitle="Set name, email, password, and role." />

        <form method="POST" action="{{ route('admin.users.store') }}" class="admin-pro-form mt-8">
            @csrf

            <x-admin.form-section title="Account" description="Set name, email, password, and role.">
                <div class="grid gap-5 sm:grid-cols-2 sm:gap-6">
                    <div>
                        <label for="first_name" class="form-label">First name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required class="form-input @error('first_name') form-input-error @enderror">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="form-label">Last name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required class="form-input @error('last_name') form-input-error @enderror">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="username" class="form-input @error('email') form-input-error @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2 sm:gap-6">
                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" required autocomplete="new-password" class="form-input @error('password') form-input-error @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="form-input">
                    </div>
                </div>
                <div>
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" required class="form-select @error('role') form-input-error @enderror">
                        <option value="user" @selected(old('role') === 'user')>User</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-admin.form-section>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Create user</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
