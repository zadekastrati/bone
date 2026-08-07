@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="panel">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ $user->name }}</h1>
                    <p class="mt-2 text-ink-600">{{ $user->email }}</p>
                    <p class="mt-4">
                        <x-admin.badge :tone="$user->role === 'admin' ? 'accent' : 'neutral'">{{ $user->role }}</x-admin.badge>
                    </p>
                    <p class="mt-6 text-xs text-ink-500">Joined {{ $user->created_at->format('M j, Y') }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary py-2 text-sm">Back</a>
                    @can('update', $user)
                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex size-10 items-center justify-center rounded-lg text-accent-700 transition hover:bg-accent-50 hover:text-accent-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/30" title="Edit user" aria-label="Edit user">
                            <x-icons.pencil-square class="h-5 w-5" />
                        </a>
                    @endcan
                    @can('delete', $user)
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" data-confirm="Delete this user? This cannot be undone." data-confirm-label="Delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex size-10 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30" title="Delete user" aria-label="Delete user">
                                <x-icons.trash class="h-5 w-5" />
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
