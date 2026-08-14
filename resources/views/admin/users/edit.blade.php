@extends('layouts.admin')

@section('title', 'Edit user')

@section('content')
    <div class="mx-auto mt-8 max-w-xl">
        <x-page-header title="Edit user" :subtitle="$user->name" />

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-pro-form">
            @csrf
            @method('PUT')

            <x-admin.form-section title="Account" description="Leave password fields empty to keep the current password.">
                <div class="grid gap-5 sm:grid-cols-2 sm:gap-6">
                    <div>
                        <label for="first_name" class="form-label">First name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required class="form-input @error('first_name') form-input-error @enderror">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="form-label">Last name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" required class="form-input @error('last_name') form-input-error @enderror">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="form-input @error('email') form-input-error @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2 sm:gap-6">
                    <div>
                        <label for="password" class="form-label">New password</label>
                        <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Optional" class="form-input @error('password') form-input-error @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="form-input" placeholder="Repeat if changing">
                    </div>
                </div>
                <div>
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" required class="form-select @error('role') form-input-error @enderror">
                        <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-admin.form-section>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
