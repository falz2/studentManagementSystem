<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryShareController;
use App\Http\Controllers\FarmMapController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RecurringOrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SellerLocationController;
use App\Http\Controllers\SellerProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::view('/farmfresh-demo', 'farmfresh-demo')->name('farmfresh.demo');
Route::get('/delivery-share', [DeliveryShareController::class, 'showPublic'])->name('delivery-share.show');
Route::post('/delivery-share/location', [DeliveryShareController::class, 'updateOrPoll'])
    ->middleware('throttle:30,1')
    ->name('delivery-share.location');

Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/map', [FarmMapController::class, 'index'])->name('marketplace.map');
    Route::get('/marketplace/{product}', [MarketplaceController::class, 'show'])->name('marketplace.show');
    Route::post('/marketplace/{product}/reviews', [ReviewController::class, 'store'])->name('marketplace.reviews.store');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/delivery-location', [DeliveryShareController::class, 'orderLocation'])->name('orders.delivery-location');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/messages', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/orders/{order}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'makeDefault'])->name('addresses.default');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/recurring-orders', [RecurringOrderController::class, 'index'])->name('recurring-orders.index');
    Route::post('/recurring-orders', [RecurringOrderController::class, 'store'])->name('recurring-orders.store');
    Route::patch('/recurring-orders/{recurringOrder}', [RecurringOrderController::class, 'update'])->name('recurring-orders.update');
    Route::delete('/recurring-orders/{recurringOrder}', [RecurringOrderController::class, 'destroy'])->name('recurring-orders.destroy');

    Route::middleware('role:farmer|supplier')->group(function () {
        Route::put('/seller/location', [SellerLocationController::class, 'update'])->name('seller.location.update');
        Route::get('/seller', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
        Route::get('/seller/analytics', [SellerDashboardController::class, 'analytics'])->name('seller.analytics');
        Route::get('/seller/products', [SellerProductController::class, 'index'])->name('seller.products.index');
        Route::get('/seller/products/create', [SellerProductController::class, 'create'])->name('seller.products.create');
        Route::post('/seller/products', [SellerProductController::class, 'store'])->name('seller.products.store');
        Route::get('/seller/products/{product}/edit', [SellerProductController::class, 'edit'])->name('seller.products.edit');
        Route::put('/seller/products/{product}', [SellerProductController::class, 'update'])->name('seller.products.update');
        Route::delete('/seller/products/{product}', [SellerProductController::class, 'destroy'])->name('seller.products.destroy');
        Route::get('/seller/orders', [OrderController::class, 'sellerIndex'])->name('seller.orders');
        Route::patch('/seller/orders/{order}', [OrderController::class, 'updateStatus'])->name('seller.orders.update');
        Route::patch('/seller/orders/{order}/delivery', [OrderController::class, 'updateDelivery'])->name('seller.orders.delivery');
        Route::post('/seller/orders/{order}/delivery-share', [DeliveryShareController::class, 'create'])->name('seller.orders.delivery-share');
        Route::delete('/seller/orders/{order}/delivery-share', [DeliveryShareController::class, 'revoke'])->name('seller.orders.delivery-share.revoke');
    });

    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
});
