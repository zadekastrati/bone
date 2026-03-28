@extends('layouts.admin')

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

    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="text-slate-600">{{ $user->email }}</td>
                            <td>
                                <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide {{ $user->role === 'admin' ? 'border-rose-200/80 bg-rose-50 text-rose-900' : 'border-slate-200/80 bg-slate-100 text-slate-700' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="admin-action-link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="data-table-empty text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap pagination-wrap--admin">
        {{ $users->links() }}
    </div>
@endsection
