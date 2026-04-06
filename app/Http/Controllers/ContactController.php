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
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactMessage::query()->create($validated);

        return redirect()
            ->route('contact')
            ->with('success', 'Thanks for reaching out. We received your message and will get back to you soon.');
    }
}
