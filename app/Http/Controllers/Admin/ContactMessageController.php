<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
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

        return view('admin.messages.index', compact('messages'));
    }

    public function show(int $id): View
    {
        $message = ContactMessage::query()->findOrFail($id);

        return view('admin.messages.show', compact('message'));
    }
}
