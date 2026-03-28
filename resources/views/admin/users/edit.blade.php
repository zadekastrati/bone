@extends('layouts.app')

@section('title', 'Edit user')

@section('content')
    <div class="mx-auto max-w-xl">
        <x-page-header title="Edit user" :subtitle="$user->name" />

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="panel mt-8 space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="form-input @error('name') form-input-error @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="form-input @error('email') form-input-error @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="form-label">New password (optional)</label>
                <input type="password" name="password" id="password" autocomplete="new-password" class="form-input @error('password') form-input-error @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="form-input">
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
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
