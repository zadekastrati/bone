<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
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
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/terms-and-conditions', 'terms-and-conditions')->name('terms');
Route::view('/returns', 'returns')->name('returns');
Route::view('/size-guide', 'size-guide')->name('size-guide');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

Route::prefix('shop')->name('shop.')->group(function (): void {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/{category:slug}/{product:slug}', [ShopController::class, 'product'])->name('product');
    Route::get('/{category:slug}', [ShopController::class, 'category'])->name('category');
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update')->whereNumber('variant');
Route::delete('/cart/{variant}', [CartController::class, 'destroy'])->name('cart.destroy')->whereNumber('variant');

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
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('checkout.store');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/seen', [AdminNotificationController::class, 'markSeen'])->name('notifications.seen');
        Route::resource('users', AdminUserController::class);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::get('products-archived', [AdminProductController::class, 'archived'])->name('products.archived');
        Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])
            ->withTrashed()
            ->name('products.restore');
        Route::delete('products/{product}/force-delete', [AdminProductController::class, 'forceDelete'])
            ->withTrashed()
            ->name('products.forceDelete');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('messages', AdminContactMessageController::class)
            ->parameters(['messages' => 'id'])
            ->only(['index', 'show']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
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
