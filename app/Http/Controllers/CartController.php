<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\MarketplaceAlert;
use App\Services\InventoryAlertService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::with('seller')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');
        $removedCount = 0;
        foreach ($cart as $id => $quantity) {
            $product = $products->get($id);
            if (! $product || $product->status !== 'active' || $product->quantity === 0 || $product->isExpired()) {
                unset($cart[$id]);
                $removedCount++;
            }
        }
        if ($removedCount > 0) {
            $request->session()->put('cart', $cart);
        }
        $items = collect($cart)->map(function (int $quantity, int|string $id) use ($products): array {
            $product = $products->get($id);

            return [
                'product' => $product,
                'quantity' => $quantity,
                'total' => $product->price * $quantity,
                'available' => $quantity <= $product->quantity,
            ];
        })->values();

        return view('marketplace.cart', [
            'role' => $request->user()->getRoleNames()->first(),
            'items' => $items,
            'total' => $items->sum('total'),
            'canCheckout' => $items->every(fn (array $item) => $item['available']),
            'removedCount' => $removedCount,
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->get(),
            'defaultAddressId' => $request->user()->addresses()->where('is_default', true)->value('id'),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $cart = $request->session()->get('cart', []);
        $quantity = (int) ($cart[$product->id] ?? 0) + $data['quantity'];

        if ($product->status !== 'active' || $product->isExpired() || $quantity > $product->quantity) {
            return back()->with('error', 'The requested quantity is not available.');
        }

        $cart[$product->id] = $quantity;
        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Product added to your cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        if ($product->status !== 'active' || $product->isExpired() || $data['quantity'] > $product->quantity) {
            return back()->with('error', 'The requested quantity is not available.');
        }

        $cart = $request->session()->get('cart', []);
        if (array_key_exists($product->id, $cart)) {
            $cart[$product->id] = $data['quantity'];
            $request->session()->put('cart', $cart);
        }

        return to_route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Product removed from your cart.');
    }

    public function checkout(Request $request, InventoryAlertService $inventoryAlerts): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'address_id' => ['nullable', 'integer'],
        ]);
        $cart = $request->session()->get('cart', []);
        $address = isset($data['address_id'])
            ? $request->user()->addresses()->find($data['address_id'])
            : $request->user()->addresses()->where('is_default', true)->first();
        if (isset($data['address_id']) && ! $address) {
            return back()->withErrors(['address_id' => 'Choose one of your saved delivery addresses.'])->withInput();
        }

        if ($cart === []) {
            return to_route('cart.index')->with('error', 'Your cart is empty.');
        }

        $orderNumbers = DB::transaction(function () use ($request, $cart, $data, $address, $inventoryAlerts): array {
            $products = Product::query()
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages(['cart' => 'A product in your cart is no longer available. Please review your cart.']);
            }

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);
                if ($product->status !== 'active' || $product->isExpired() || $quantity < 1 || $quantity > $product->quantity) {
                    throw ValidationException::withMessages(['cart' => 'Stock changed for '.$product->name.'. Please review your cart.']);
                }
            }

            $orders = [];
            foreach ($products->groupBy('seller_id') as $sellerProducts) {
                $total = $sellerProducts->sum(fn (Product $product) => $product->price * $cart[$product->id]);
                $order = Order::create([
                    'buyer_id' => $request->user()->id,
                    'seller_id' => $sellerProducts->first()->seller_id,
                    'order_number' => 'PH-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                    'total' => $total,
                    'status' => 'pending',
                    'notes' => $data['notes'] ?? null,
                    'recipient_name' => $address?->recipient_name,
                    'delivery_phone' => $address?->phone,
                    'delivery_address' => $address?->formattedAddress(),
                    'delivery_instructions' => $address?->instructions,
                ]);

                foreach ($sellerProducts as $product) {
                    $quantity = $cart[$product->id];
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'category' => $product->category,
                        'unit' => $product->unit,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'total' => $product->price * $quantity,
                    ]);
                    $product->decrement('quantity', $quantity);
                    $product->refresh();
                    $inventoryAlerts->notifyFor($product);
                }
                $sellerProducts->first()->seller->notify(new MarketplaceAlert(
                    'New marketplace order',
                    $order->order_number.' is waiting for your confirmation.',
                    route('seller.orders'),
                    '📦',
                ));
                $orders[] = $order->order_number;
            }

            return $orders;
        });

        $request->session()->forget('cart');
        $request->user()->notify(new MarketplaceAlert(
            'Order placed successfully',
            'Your order '.implode(', ', $orderNumbers).' is waiting for seller confirmation.',
            route('orders.index'),
            '✅',
        ));

        return to_route('orders.index')
            ->with('success', 'Order placed successfully: '.implode(', ', $orderNumbers));
    }
}
