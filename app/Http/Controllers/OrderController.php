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
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->purchases()
            ->with(['seller', 'items.review', 'deliveryShare'])
            ->latest()
            ->paginate(12);

        return view('orders.index', [
            'role' => $request->user()->getRoleNames()->first(),
            'orders' => $orders,
            'sellerView' => false,
        ]);
    }

    public function sellerIndex(Request $request): View
    {
        $orders = $request->user()->sales()
            ->with(['buyer', 'items', 'deliveryShare'])
            ->latest()
            ->paginate(12);

        return view('orders.index', [
            'role' => $request->user()->getRoleNames()->first(),
            'orders' => $orders,
            'sellerView' => true,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'completed'])],
        ]);

        $updated = DB::transaction(function () use ($request, $order, $data): bool {
            $order = $request->user()->sales()->lockForUpdate()->findOrFail($order->id);
            if (! (($order->status === 'pending' && $data['status'] === 'confirmed')
                || ($order->status === 'confirmed' && $data['status'] === 'completed'))) {
                return false;
            }

            $order->update([
                'status' => $data['status'],
                'confirmed_at' => $data['status'] === 'confirmed' ? now() : $order->confirmed_at,
                'completed_at' => $data['status'] === 'completed' ? now() : null,
            ]);
            if ($data['status'] === 'completed') {
                $order->deliveryShare()->update([
                    'revoked_at' => now(),
                    'latitude' => null,
                    'longitude' => null,
                    'accuracy_meters' => null,
                    'location_updated_at' => null,
                ]);
            }

            return true;
        });

        if (! $updated) {
            return back()->with('error', 'This order cannot be moved to that status.');
        }

        $order->buyer->notify(new MarketplaceAlert(
            'Order status updated',
            $order->order_number.' is now '.$data['status'].'.',
            route('orders.index'),
            '📦',
        ));

        return to_route('seller.orders')->with('success', 'Order status updated.');
    }

    public function updateDelivery(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'delivery_status' => ['required', Rule::in(['scheduled', 'in_transit', 'delivered'])],
            'estimated_delivery_at' => ['nullable', 'date', 'after_or_equal:now'],
        ]);

        $updatedOrder = DB::transaction(function () use ($request, $order, $data): Order|false {
            $lockedOrder = $request->user()->sales()->lockForUpdate()->findOrFail($order->id);
            $transitions = [
                'not_scheduled' => 'scheduled',
                'scheduled' => 'in_transit',
                'in_transit' => 'delivered',
            ];
            if (($transitions[$lockedOrder->delivery_status] ?? null) !== $data['delivery_status']
                || ($data['delivery_status'] === 'scheduled' && $lockedOrder->status !== 'confirmed')) {
                return false;
            }

            $lockedOrder->delivery_status = $data['delivery_status'];
            if ($data['delivery_status'] === 'scheduled') {
                $lockedOrder->estimated_delivery_at = $data['estimated_delivery_at'] ?? null;
                $lockedOrder->delivery_scheduled_at = now();
            }
            if ($data['delivery_status'] === 'in_transit') {
                $lockedOrder->in_transit_at = now();
            }
            if ($data['delivery_status'] === 'delivered') {
                $lockedOrder->delivered_at = now();
                $lockedOrder->status = 'completed';
                $lockedOrder->completed_at = now();
                $lockedOrder->deliveryShare()->update([
                    'revoked_at' => now(),
                    'latitude' => null,
                    'longitude' => null,
                    'accuracy_meters' => null,
                    'location_updated_at' => null,
                ]);
            }
            $lockedOrder->save();

            return $lockedOrder->load('buyer');
        });

        if ($updatedOrder === false) {
            return back()->with('error', 'Update the order to confirmed before scheduling delivery, then follow the delivery steps in order.');
        }

        $updatedOrder->buyer->notify(new MarketplaceAlert(
            'Delivery update for '.$updatedOrder->order_number,
            match ($data['delivery_status']) {
                'scheduled' => 'The seller scheduled your delivery.',
                'in_transit' => 'Your order is on its way.',
                default => 'Your order has been marked delivered.',
            },
            route('orders.index'),
            '🚚',
        ));

        return to_route('seller.orders')->with('success', 'Delivery status updated.');
    }

    public function cancel(Request $request, Order $order, InventoryAlertService $inventoryAlerts): RedirectResponse
    {
        $cancelled = DB::transaction(function () use ($request, $order, $inventoryAlerts): bool {
            $order = $request->user()->purchases()->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== 'pending') {
                return false;
            }

            $order->update(['status' => 'cancelled']);
            $order->deliveryShare()->update([
                'revoked_at' => now(),
                'latitude' => null,
                'longitude' => null,
                'accuracy_meters' => null,
                'location_updated_at' => null,
            ]);
            foreach ($order->items as $item) {
                if ($item->product_id === null) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                    $inventoryAlerts->notifyFor($product->fresh());
                }
            }

            return true;
        });

        if (! $cancelled) {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->seller?->notify(new MarketplaceAlert(
            'Order cancelled',
            $order->order_number.' was cancelled by the buyer; available stock has been restored.',
            route('seller.orders'),
            '↩️',
        ));

        return to_route('orders.index')->with('success', 'Order cancelled and available stock restored.');
    }
}
