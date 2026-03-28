<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('shop.index')
        : view('welcome');
})->name('home');

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
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('users', AdminUserController::class);
        Route::resource('categories', AdminCategoryController::class)
            ->parameters(['categories' => 'id'])
            ->except(['show']);
        Route::resource('products', AdminProductController::class)
            ->parameters(['products' => 'id'])
            ->except(['show']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    });
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:5,1');
    Route::get('register/verify', [RegisterController::class, 'showVerify'])->name('register.verify');
    Route::post('register/verify', [RegisterController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('register.verify.store');
    Route::post('register/resend', [RegisterController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('register.resend');
    Route::post('register/cancel', [RegisterController::class, 'cancel'])->name('register.cancel');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
