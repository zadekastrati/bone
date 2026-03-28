@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="panel">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h1 class="font-display text-2xl font-bold text-ink-950 sm:text-3xl">{{ $user->name }}</h1>
                    <p class="mt-2 text-slate-600">{{ $user->email }}</p>
                    <p class="mt-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $user->role === 'admin' ? 'bg-accent-100 text-accent-900' : 'bg-ink-100 text-ink-800' }}">
                            {{ $user->role }}
                        </span>
                    </p>
                    <p class="mt-6 text-xs text-ink-500">Joined {{ $user->created_at->format('M j, Y') }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary py-2 text-sm">Back</a>
                    @can('update', $user)
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary py-2 text-sm">Edit</a>
                    @endcan
                    @can('delete', $user)
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger py-2 text-sm">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
