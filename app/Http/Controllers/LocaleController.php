<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', array_keys(config('app.available_locales', ['en' => 'English'])))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $user = $request->user();
        if ($user instanceof User) {
            $user->update(['locale' => $validated['locale']]);
        }

        return redirect()->back();
    }
}
