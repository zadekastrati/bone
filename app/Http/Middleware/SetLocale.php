<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // The admin panel is an internal tool for staff, not translated —
        // it always renders in the base locale regardless of a shopper's
        // storefront language preference (which may belong to the same
        // account if an admin also has a customer-facing preference set).
        if ($request->routeIs('admin.*')) {
            return config('app.locale', 'en');
        }

        $available = array_keys(config('app.available_locales', ['en' => 'English']));

        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, $available, true)) {
            return $sessionLocale;
        }

        $user = $request->user();
        if ($user instanceof User && $user->locale !== null && in_array($user->locale, $available, true)) {
            return $user->locale;
        }

        return config('app.locale', 'en');
    }
}
