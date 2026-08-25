<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        // The 'auth' middleware on this route guarantees a user — always use the account's email.
        $validated['email'] = $request->user()->email;

        ContactMessage::query()->create($validated);

        return redirect()
            ->route('contact')
            ->with('success', __('Thanks for reaching out. We received your message and will get back to you soon.'));
    }
}
