@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
    <x-page-header title="Messages" subtitle="Contact form submissions from the storefront." />

    <div x-data="adminLiveSearch('messages-results')">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4" x-ref="form">
            <div class="min-w-0 flex-1">
                <label for="q" class="form-label">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name, email, or message..." class="form-input">
            </div>
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Filter</span>
            </button>
            @if (request()->filled('q'))
                <a href="{{ route('admin.messages.index') }}" class="btn-secondary shrink-0">Clear</a>
            @endif
        </form>

        <div id="messages-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
            @include('admin.messages.partials.results')
        </div>
    </div>
@endsection
