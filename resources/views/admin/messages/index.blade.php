@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
    <x-page-header title="Messages" subtitle="Contact form submissions from the storefront.">

    </x-page-header>

    <form method="GET" action="{{ route('admin.messages.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4">
        <div class="min-w-0 flex-1">
            <label for="q" class="form-label">Search</label>
            <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name, email, or message..." class="form-input">
        </div>
        <button type="submit" class="btn-dark shrink-0">Filter</button>
        @if (request()->filled('q'))
            <a href="{{ route('admin.messages.index') }}" class="btn-secondary shrink-0">Clear</a>
        @endif
    </form>

    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $message->name }}</td>
                            <td><a href="mailto:{{ $message->email }}" class="text-ink-700 hover:text-accent-700">{{ $message->email }}</a></td>
                            <td class="max-w-[34rem] text-ink-600">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</td>
                            <td class="text-ink-600">{{ $message->created_at->format('M j, Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.messages.show', $message->id) }}" class="admin-action-link">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty text-ink-500">No messages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap pagination-wrap--admin">
        {{ $messages->links() }}
    </div>
@endsection
