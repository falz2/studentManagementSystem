# Three Important PoultryHub Code Snippets

These examples show how PoultryHub creates demo data, restricts workflows by
role, and safely completes a marketplace checkout.

## 1. Create demo accounts and assign roles

Source: [PoultryHubSeeder.php](../database/seeders/PoultryHubSeeder.php)

The seeder creates farmer, supplier, and consumer accounts and assigns their
roles. `firstOrCreate` lets the seed run again without duplicating those
accounts. The same file also defines sample poultry and feed listings.

```php
$accounts = [
    ['name' => 'Amina Namusoke', 'email' => 'amina@poultryhub.ug', 'role' => 'farmer'],
    ['name' => 'Kampala Feeds Co.', 'email' => 'feeds@poultryhub.ug', 'role' => 'supplier'],
    ['name' => 'Demo Shopper', 'email' => 'shopper@poultryhub.ug', 'role' => 'consumer'],
];

foreach ($accounts as $account) {
    $user = User::firstOrCreate(
        ['email' => $account['email']],
        ['name' => $account['name'], 'password' => 'password', 'email_verified_at' => now()]
    );
    $user->syncRoles($account['role']);
}
```

The seeded password is for local demonstration only. Set a secure password for
any account used outside a local development environment.

## 2. Restrict routes by role

Source: [web.php](../routes/web.php)

All marketplace routes require authentication. Seller tools are limited to
farmers and suppliers, while the administration dashboard is limited to admins.

```php
Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
    // Authenticated marketplace and buyer routes are defined here.

    Route::middleware('role:farmer|supplier')->group(function () {
        Route::get('/seller', [SellerDashboardController::class, 'index'])
            ->name('seller.dashboard');
        Route::get('/seller/products', [SellerProductController::class, 'index'])
            ->name('seller.products.index');
        Route::get('/seller/orders', [OrderController::class, 'sellerIndex'])
            ->name('seller.orders');
    });

    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
});
```

## 3. Complete checkout atomically

Source: [CartController.php](../app/Http/Controllers/CartController.php)

Checkout locks the selected products while it checks stock, groups products into
one order per seller, records order-item price snapshots, and decrements inventory
inside a database transaction. If stock is no longer available, validation
aborts the transaction so no partial order is saved.

```php
$orderNumbers = DB::transaction(function () use ($request, $cart, $data, $address, $inventoryAlerts): array {
    $products = Product::query()
        ->whereIn('id', array_keys($cart))
        ->lockForUpdate()
        ->get()
        ->keyBy('id');

    if ($products->count() !== count($cart)) {
        throw ValidationException::withMessages([
            'cart' => 'A product in your cart is no longer available. Please review your cart.',
        ]);
    }

    foreach ($cart as $productId => $quantity) {
        $product = $products->get($productId);
        if ($product->status !== 'active' || $product->isExpired()
            || $quantity < 1 || $quantity > $product->quantity) {
            throw ValidationException::withMessages([
                'cart' => 'Stock changed for '.$product->name.'. Please review your cart.',
            ]);
        }
    }

    $orders = [];
    foreach ($products->groupBy('seller_id') as $sellerProducts) {
        $total = $sellerProducts->sum(
            fn (Product $product) => $product->price * $cart[$product->id]
        );
        $order = Order::create([
            'buyer_id' => $request->user()->id,
            'seller_id' => $sellerProducts->first()->seller_id,
            'order_number' => 'PH-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($sellerProducts as $product) {
            $quantity = $cart[$product->id];
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total' => $product->price * $quantity,
            ]);
            $product->decrement('quantity', $quantity);
            $product->refresh();
            $inventoryAlerts->notifyFor($product);
        }

        $orders[] = $order->order_number;
    }

    return $orders;
});
```

## Related code

The Leaflet map and live delivery-location updates are implemented in
[maps.js](../resources/js/maps.js) and
[DeliveryShareController.php](../app/Http/Controllers/DeliveryShareController.php).
