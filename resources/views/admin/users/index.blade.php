@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <x-page-header title="Users" subtitle="Manage accounts and roles (admin only).">
        <a href="{{ route('admin.users.create') }}" class="btn-primary">New user</a>
    </x-page-header>

    <div x-data="adminLiveSearch('users-results')">
        <form method="GET" action="{{ route('admin.users.index') }}" class="search-bar mt-8" x-ref="form">
            <div class="min-w-0 flex-1">
                <label for="q" class="form-label">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name or email…" class="form-input">
            </div>
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2 sm:w-auto">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Search</span>
            </button>
            @if (request()->filled('q'))
                <a href="{{ route('admin.users.index') }}" class="btn-secondary shrink-0 sm:w-auto">Clear</a>
            @endif
        </form>

        <div id="users-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
            @include('admin.users.partials.results')
        </div>
    </div>
@endsection
