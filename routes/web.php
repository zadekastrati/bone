<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Enums\TrainingTag;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::get('/', function () {
    $categories = Category::query()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    // One query for every active product's tags, tallied in PHP — cheaper than
    // a separate whereJsonContains() count query per training tag, and this
    // list is only ever the size of the active catalog.
    $activeTrainingTags = Product::query()->where('is_active', true)->pluck('training_tags');

    // Tags with zero matching products are dropped entirely rather than shown
    // disabled/empty — clicking through to a filter with nothing in it is a
    // dead end, so it's better not to offer it in the first place.
    $trainingTags = collect(TrainingTag::cases())
        ->map(fn (TrainingTag $tag) => [
            'tag' => $tag,
            'count' => $activeTrainingTags->filter(fn ($tags) => is_array($tags) && in_array($tag->value, $tags, true))->count(),
        ])
        ->filter(fn (array $row) => $row['count'] > 0)
        ->values();

    return view('welcome', compact('categories', 'trainingTags'));
})->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function (): \Illuminate\Http\Response {
    $lines = [
        'User-agent: *',
        'Disallow: /admin',
        'Disallow: /cart',
        'Disallow: /checkout',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /forgot-password',
        'Disallow: /profile',
        'Disallow: /orders',
        'Disallow: /order-confirmation',
        'Disallow: /payment',
        '',
        'Sitemap: '.route('sitemap'),
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/terms-and-conditions', 'terms-and-conditions')->name('terms');
Route::view('/returns', 'returns')->name('returns');
Route::view('/size-guide', 'size-guide')->name('size-guide');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('contact.store');

Route::post('/country', [CountryController::class, 'update'])
    ->middleware('throttle:30,1')
    ->name('country.update');

Route::post('/locale', [LocaleController::class, 'update'])
    ->middleware('throttle:30,1')
    ->name('locale.update');

// Public, unauthenticated, cacheable image responses — they never touch the
// session, cookies, CSRF, or locale, so skip that middleware entirely rather
// than paying its (very real, on this filesystem) autoload cost on every
// single image request.
Route::get('media/product-images/{productImage}/{variant}', [MediaController::class, 'productImage'])
    ->where('variant', 'thumb|display|grid')
    ->withoutMiddleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\ClearAbandonedRegistration::class,
        \App\Http\Middleware\SetLocale::class,
    ])
    ->name('media.product-images.show');

Route::get('media/category-images/{category}/{variant}', [MediaController::class, 'categoryImage'])
    ->where('variant', 'thumb|display|grid')
    ->withoutMiddleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\ClearAbandonedRegistration::class,
        \App\Http\Middleware\SetLocale::class,
    ])
    ->name('media.category-images.show');

Route::prefix('shop')->name('shop.')->group(function (): void {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/{category:slug}/{product:slug}', [ShopController::class, 'product'])->name('product');
    Route::get('/{category:slug}', [ShopController::class, 'category'])->name('category');
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update')->whereNumber('variant');
Route::delete('/cart/{variant}', [CartController::class, 'destroy'])->name('cart.destroy')->whereNumber('variant');

// Guest checkout is allowed, so these sit outside the auth/verified groups —
// CheckoutController itself still enforces the "logged-in users must have a
// verified email" rule for authenticated shoppers (see its verifiedGuard()).
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware(['throttle:30,1', 'throttle:guest-checkout'])
    ->name('checkout.store');

// A guest has no account to view "My orders" from, so their order
// confirmation page is reached via a signed link handed out right after
// checkout instead — the signature is what proves it's theirs, the same way
// the email-verification link above does.
Route::get('/order-confirmation/{order}', [OrderController::class, 'guestConfirmation'])
    ->middleware('signed')
    ->name('orders.guest-confirmation');

// Where the Quipu hosted payment page redirects the customer back to after a
// card payment attempt. Only a holding page for now — see PaymentController.
Route::get('/payment/quipu/return/{order}', [PaymentController::class, 'quipuReturn'])
    ->name('payment.quipu.return');

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', function (): View {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request): RedirectResponse {
        $request->fulfill();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Your email address has been verified. You can place orders now.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request): RedirectResponse {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    // Polled from the "Verify your email" page so a user who verifies via
    // the emailed link in another tab sees this tab pick it up on its own —
    // no separate real-time infrastructure (websockets/broadcasting) exists
    // in this app, so a lightweight poll is the proportionate fix here.
    Route::get('/email/verify/status', function (Request $request): JsonResponse {
        return response()->json(['verified' => $request->user()->hasVerifiedEmail()]);
    })->middleware('throttle:30,1')->name('verification.status');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/email/verify', [ProfileController::class, 'verifyEmail'])
        ->middleware('throttle:10,1')
        ->name('profile.email.verify');
    Route::post('/profile/email/resend', [ProfileController::class, 'resendEmailCode'])
        ->middleware('throttle:3,1')
        ->name('profile.email.resend');
    Route::post('/profile/email/cancel', [ProfileController::class, 'cancelEmailChange'])->name('profile.email.cancel');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])
        ->middleware('throttle:10,1')
        ->name('profile.phone.verify');
    Route::post('/profile/phone/resend', [ProfileController::class, 'resendPhoneCode'])
        ->middleware('throttle:3,1')
        ->name('profile.phone.resend');
    Route::post('/profile/phone/cancel', [ProfileController::class, 'cancelPhoneChange'])->name('profile.phone.cancel');

    Route::middleware('verified')->group(function (): void {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/seen', [AdminNotificationController::class, 'markSeen'])->name('notifications.seen');
        Route::resource('users', AdminUserController::class);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        // The picker can fire a couple hundred of these thumbnail requests in one
        // session — CSRF/locale/registration-cleanup middleware add real per-request
        // overhead on this filesystem for zero benefit on a read-only, already-admin
        // GET endpoint, so they're skipped here (session/cookies/auth stay, since the
        // route is still admin-only).
        $mediaLibraryWithoutMiddleware = [
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\ClearAbandonedRegistration::class,
            \App\Http\Middleware\SetLocale::class,
        ];
        Route::get('media-library', [MediaLibraryController::class, 'index'])
            ->withoutMiddleware($mediaLibraryWithoutMiddleware)
            ->name('media-library.index');
        Route::get('media-library/thumbnail', [MediaLibraryController::class, 'thumbnail'])
            ->withoutMiddleware($mediaLibraryWithoutMiddleware)
            ->name('media-library.thumbnail');
        // POST rather than GET — a folder's worth of paths in a query string
        // risks the URL length limit; VerifyCsrfToken is already excluded
        // above, so no CSRF token is needed for this admin-only endpoint.
        Route::post('media-library/folder-thumbnails', [MediaLibraryController::class, 'folderThumbnails'])
            ->withoutMiddleware($mediaLibraryWithoutMiddleware)
            ->name('media-library.folder-thumbnails');
        Route::get('products-archived', [AdminProductController::class, 'archived'])->name('products.archived');
        Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])
            ->withTrashed()
            ->name('products.restore');
        Route::delete('products/{product}/force-delete', [AdminProductController::class, 'forceDelete'])
            ->withTrashed()
            ->name('products.forceDelete');
        Route::delete('products/bulk-delete', [AdminProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');
        Route::delete('products/bulk-force-delete', [AdminProductController::class, 'bulkForceDelete'])->name('products.bulkForceDelete');
        Route::post('products/bulk-restore', [AdminProductController::class, 'bulkRestore'])->name('products.bulkRestore');
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::post('products/{product}/images/reorder', [AdminProductController::class, 'reorderImages'])->name('products.images.reorder');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::delete('messages', [AdminContactMessageController::class, 'destroyAll'])->name('messages.destroyAll');
        Route::delete('messages/bulk-delete', [AdminContactMessageController::class, 'bulkDestroy'])->name('messages.bulkDestroy');
        Route::resource('messages', AdminContactMessageController::class)
            ->parameters(['messages' => 'id'])
            ->only(['index', 'show', 'destroy']);
        Route::get('orders/archived', [AdminOrderController::class, 'archived'])->name('orders.archived');
        Route::post('orders/{order}/restore', [AdminOrderController::class, 'restore'])
            ->withTrashed()
            ->name('orders.restore');
        Route::delete('orders/{order}/force-delete', [AdminOrderController::class, 'forceDelete'])
            ->withTrashed()
            ->name('orders.forceDelete');
        Route::get('orders/{order}/details', [AdminOrderController::class, 'details'])->name('orders.details');
        Route::patch('orders/{order}/quick-status', [AdminOrderController::class, 'quickStatus'])->name('orders.quickStatus');
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update', 'destroy']);
    });
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('register/verify', [RegisterController::class, 'showVerify'])->name('register.verify');
    Route::post('register/verify', [RegisterController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('register.verify.store');
    Route::post('register/resend', [RegisterController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('register.resend');
    Route::post('register/cancel', [RegisterController::class, 'cancel'])->name('register.cancel');

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.reset');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.reset.send');
    Route::get('forgot-password/verify', [ForgotPasswordController::class, 'showVerify'])->name('password.reset.verify');
    Route::post('forgot-password/verify', [ForgotPasswordController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('password.reset.verify.store');
    Route::get('forgot-password/new-password', [ForgotPasswordController::class, 'showNewPassword'])->name('password.reset.password');
    Route::post('forgot-password/new-password', [ForgotPasswordController::class, 'updatePassword'])
        ->middleware('throttle:10,1')
        ->name('password.reset.password.store');
    Route::post('forgot-password/resend', [ForgotPasswordController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('password.reset.resend');
    Route::post('forgot-password/cancel', [ForgotPasswordController::class, 'cancel'])->name('password.reset.cancel');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
