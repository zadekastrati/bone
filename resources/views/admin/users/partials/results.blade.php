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
                        <td class="font-medium text-ink-900">{{ $user->name }}</td>
                        <td class="text-ink-600">{{ $user->email }}</td>
                        <td>
                            <x-admin.badge :tone="$user->role === 'admin' ? 'accent' : 'neutral'">{{ $user->role }}</x-admin.badge>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="admin-action-link">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="data-table-empty text-ink-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap pagination-wrap--admin">
    {{ $users->links() }}
</div>
