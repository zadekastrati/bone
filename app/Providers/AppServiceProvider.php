<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use App\Models\Category;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers()->symbols());

        Mail::extend('brevo', fn (array $config) => new BrevoApiTransport($config['key']));

        // The footer's category list is shared across every page (shop, admin,
        // auth, ...), so it's resolved here once rather than duplicated in
        // every controller. Categories with no active products are dropped —
        // same reasoning as the homepage's training-tag filter: a link into
        // an empty listing is a dead end, so don't offer it.
        View::composer('layouts.app', function ($view): void {
            $view->with('footerCategories', Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->withCount('activeProducts')
                ->get()
                ->filter(fn (Category $category) => $category->active_products_count > 0)
                ->values());
        });
    }
}
