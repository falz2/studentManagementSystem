<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RecurringOrder;
use App\Notifications\MarketplaceAlert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecurringOrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isConsumer(), 403);

        return view('recurring-orders.index', [
            'recurringOrders' => $request->user()->recurringOrders()->with(['product', 'seller'])->latest()->paginate(20),
            'products' => Product::query()
                ->where('category', 'feed')
                ->where('status', 'active')
                ->where('quantity', '>', 0)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
                ->whereHas('seller', fn ($query) => $query->role('supplier'))
                ->with('seller')
                ->get(),
            'role' => $request->user()->getRoleNames()->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isConsumer(), 403);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'frequency' => ['required', Rule::in(['weekly', 'monthly'])],
            'next_run_at' => ['required', 'date', 'after_or_equal:tomorrow'],
        ]);
        $product = Product::query()->with('seller')->findOrFail($data['product_id']);
        abort_unless($product->seller?->isSupplier() && $product->status === 'active' && ! $product->expires_at?->lt(today()), 422);

        if ($request->user()->recurringOrders()->where('product_id', $product->id)->where('status', 'active')->exists()) {
            return back()->withErrors(['product_id' => 'You already have an active recurring order for this product.'])->withInput();
        }

        $schedule = $request->user()->recurringOrders()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'frequency' => $data['frequency'],
            'next_run_at' => $data['next_run_at'],
        ]);

        $product->seller->notify(new MarketplaceAlert(
            'New recurring order schedule',
            $request->user()->name.' scheduled '.$data['quantity'].' '.$product->unit.'(s) of '.$product->name.'.',
            route('recurring-orders.index'),
            '🔁',
        ));

        return to_route('recurring-orders.index')->with('success', 'Recurring supplier order saved. Orders are created on the scheduled date; payment is not automated.');
    }

    public function update(Request $request, RecurringOrder $recurringOrder): RedirectResponse
    {
        $recurringOrder = $request->user()->recurringOrders()->findOrFail($recurringOrder->id);
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'paused'])]]);

        $recurringOrder->update([
            'status' => $data['status'],
            'next_run_at' => $data['status'] === 'active' && $recurringOrder->next_run_at->lte(now())
                ? now()->addDay()
                : $recurringOrder->next_run_at,
        ]);

        return to_route('recurring-orders.index')->with('success', 'Recurring order '.($data['status'] === 'active' ? 'resumed' : 'paused').'.');
    }

    public function destroy(Request $request, RecurringOrder $recurringOrder): RedirectResponse
    {
        $request->user()->recurringOrders()->findOrFail($recurringOrder->id)->delete();

        return to_route('recurring-orders.index')->with('success', 'Recurring order removed.');
    }
}
