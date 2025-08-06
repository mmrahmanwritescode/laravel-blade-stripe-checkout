<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [CartController::class, 'index'])->name('cart.create');
Route::get('/clear-cart', [CartController::class, 'clear_cart'])->name('cart.clear');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.show');
Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/payment-init', [CheckoutController::class, 'payment_init'])->name('checkout.payment_init');
Route::get('/order-list', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/confirmed/{orderId}', [OrderController::class, 'confirm'])->name('orders.confirm');

// Stripe Webhook Route (exclude from CSRF protection)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware(['web']);
