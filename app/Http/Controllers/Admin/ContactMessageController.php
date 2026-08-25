<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ContactMessage::class);

        $query = ContactMessage::query()->latest();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('message', 'like', $term);
            });
        }

        $messages = $query->paginate(20)->appends($request->query());

        if ($request->ajax()) {
            return view('admin.messages.partials.results', compact('messages'));
        }

        return view('admin.messages.index', compact('messages'));
    }

    public function show(int $id): View
    {
        $message = ContactMessage::query()->findOrFail($id);

        $this->authorize('view', $message);

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $message = ContactMessage::query()->findOrFail($id);

        $this->authorize('delete', $message);

        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', ContactMessage::class);
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:contact_messages,id'],
        ]);

        $count = ContactMessage::query()->whereIn('id', $validated['ids'])->delete();

        return redirect()->route('admin.messages.index')->with('success', "Deleted {$count} message(s).");
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', ContactMessage::class);
        abort_unless($request->user()?->isAdmin(), 403);

        $query = ContactMessage::query();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('message', 'like', $term);
            });
        }

        $count = $query->delete();

        return redirect()->route('admin.messages.index')->with('success', "Deleted {$count} message(s).");
    }
}
