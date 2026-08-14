<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:3000'],
        ];

        if (! $request->user()) {
            $rules['email'] = ['required', 'email:rfc,dns', 'max:190'];
        }

        $validated = $request->validate($rules);

        // Logged-in users can't submit an arbitrary email — always use the account's.
        $validated['email'] = $request->user()?->email ?? $validated['email'];

        ContactMessage::query()->create($validated);

        return redirect()
            ->route('contact')
            ->with('success', __('Thanks for reaching out. We received your message and will get back to you soon.'));
    }
}
