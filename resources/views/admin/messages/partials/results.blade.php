<div class="table-shell--admin mt-10">
    <div class="overflow-x-auto">
        <table class="data-table data-table--admin">
            <thead>
                <tr>
                    <th class="w-10">
                        <input type="checkbox" class="js-select-all" autocomplete="off" x-on:click.prevent="toggleAll()" aria-label="Select all">
                    </th>
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
                        <td class="w-10">
                            <input type="checkbox" name="ids[]" value="{{ $message->id }}" form="messages-bulk-delete-form" class="js-select-message" autocomplete="off" aria-label="Select message from {{ $message->name }}">
                        </td>
                        <td class="font-medium text-ink-900">{{ $message->name }}</td>
                        <td><a href="mailto:{{ $message->email }}" class="text-ink-700 hover:text-accent-700">{{ $message->email }}</a></td>
                        <td class="max-w-[34rem] text-ink-600">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</td>
                        <td class="text-ink-600">{{ $message->created_at->copy()->timezone(config('store.display_timezone'))->format('M j, Y H:i') }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.messages.show', $message->id) }}" class="admin-action-link">Open</a>
                                <form method="POST" action="{{ route('admin.messages.destroy', $message->id) }}" class="inline" data-confirm="Delete this message from {{ $message->name }}?" data-confirm-label="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action-link text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="data-table-empty text-ink-500">No messages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap pagination-wrap--admin">
    {{ $messages->links() }}
</div>
