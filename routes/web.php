<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\CartController;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WebhookController;

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
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add'); // Legacy support
Route::patch('/cart/{itemId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{itemId}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::middleware([App\Http\Middleware\CheckOperatingHours::class])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Closed Route
Route::get('/closed', function () {
    if (App\Models\Setting::isWarungOpen()) {
        return redirect()->route('home');
    }

    $open = App\Models\Setting::getValue(App\Models\Setting::KEY_OPERATING_HOURS_OPEN, '08:00');
    $close = App\Models\Setting::getValue(App\Models\Setting::KEY_OPERATING_HOURS_CLOSE, '21:00');
    return view('closed', compact('open', 'close'));
})->name('closed');

// Tracking Routes
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/tracking/search', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{code}', [TrackingController::class, 'show'])->name('tracking.show');
Route::get('/tracking/{code}/status', [TrackingController::class, 'status'])->name('tracking.status');

// Payment Routes
Route::get('/payment/{code}', [PaymentController::class, 'show'])->name('payment.show');
Route::get('/payment/{code}/check', [PaymentController::class, 'check'])->name('payment.check');

// Webhook Routes
Route::post('/webhook/payment', [WebhookController::class, 'handle'])->name('webhook.payment');
