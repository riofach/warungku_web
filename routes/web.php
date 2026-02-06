<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\CartController;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TrackingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| WarungKu Digital - Customer Website Routes
|
*/

// Shop Routes
Route::get('/', [ShopController::class, 'index'])->name('home');
// Route::get('/product/{item}', [ShopController::class, 'show'])->name('shop.show'); // Moved to later story or removed for now as not in this story requirements


// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{itemId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Tracking Routes
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/tracking/search', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{code}', [TrackingController::class, 'show'])->name('tracking.show');

// Payment Routes (to be implemented)
Route::get('/payment/qris/{code}', function ($code) {
    return view('payment.qris', ['code' => $code]);
})->name('payment.qris');
