@extends('layouts.admin')

@section('title', 'Message')

@section('content')
    <div class="mx-auto w-full max-w-3xl">
        <x-page-header title="Message details" subtitle="Review the full contact form submission.">
            <a href="{{ route('admin.messages.index') }}" class="btn-secondary">Back to messages</a>
        </x-page-header>

        <div class="panel p-6 sm:p-8">
            <dl class="grid gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Name</dt>
                    <dd class="mt-2 text-sm font-medium text-ink-900">{{ $message->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Email</dt>
                    <dd class="mt-2 text-sm text-ink-800">
                        <a href="mailto:{{ $message->email }}" class="hover:text-accent-700">{{ $message->email }}</a>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Received</dt>
                    <dd class="mt-2 text-sm text-ink-700">{{ $message->created_at->format('M j, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Message</dt>
                    <dd class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-800">{{ $message->message }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
