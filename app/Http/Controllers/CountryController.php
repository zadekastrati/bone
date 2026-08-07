<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(private readonly CurrencyService $currency) {}

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'in:'.implode(',', array_keys($this->currency->countries()))],
        ]);

        $this->currency->setCountry($validated['country']);

        return redirect()->back();
    }
}
