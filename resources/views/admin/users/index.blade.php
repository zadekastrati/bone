@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <x-page-header title="Users" subtitle="Manage accounts and roles (admin only).">
        <a href="{{ route('admin.users.create') }}" class="btn-primary">New user</a>
    </x-page-header>

    <form method="GET" action="{{ route('admin.users.index') }}" class="search-bar mt-8">
        <div class="min-w-0 flex-1">
            <label for="q" class="form-label">Search</label>
            <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name or email…" class="form-input">
        </div>
        <button type="submit" class="btn-dark shrink-0 sm:w-auto">Search</button>
        @if (request()->filled('q'))
            <a href="{{ route('admin.users.index') }}" class="btn-secondary shrink-0 sm:w-auto">Clear</a>
        @endif
    </form>

    <div class="mt-10 overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03] backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
                <thead class="bg-ink-50/90">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Email</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Role</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-accent-50/40">
                            <td class="px-5 py-3.5 font-medium text-ink-950">{{ $user->name }}</td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $user->email }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide {{ $user->role === 'admin' ? 'bg-accent-100 text-accent-900' : 'bg-ink-100 text-ink-800' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="link-brand text-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-ink-600">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10 flex justify-center">
        {{ $users->links() }}
    </div>
@endsection
