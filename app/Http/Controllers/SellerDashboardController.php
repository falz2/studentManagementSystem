<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $seller = $request->user();
        $requestedDays = $request->integer('days', 30);
        $days = in_array($requestedDays, [7, 30, 90], true) ? $requestedDays : 30;
        $periodStart = now()->subDays($days - 1)->startOfDay();
        $products = $seller->products()->latest()->limit(5)->get();
        $orders = $seller->sales()->with(['buyer', 'items'])->latest()->limit(5)->get();
        $completedOrders = $seller->sales()->where('status', 'completed');
        $revenue = (clone $completedOrders)->sum('total');
        $periodRevenue = (clone $completedOrders)->where('completed_at', '>=', $periodStart)->sum('total');
        $orderCount = $seller->sales()->count();

        $dailyRevenue = (clone $completedOrders)
            ->where('completed_at', '>=', $periodStart)
            ->selectRaw('date(completed_at) as day, sum(total) as amount')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chartDays = collect(range($days - 1, 0))->map(function (int $daysAgo) use ($dailyRevenue): array {
            $date = now()->subDays($daysAgo)->toDateString();

            return ['date' => $date, 'amount' => (int) ($dailyRevenue->get($date)?->amount ?? 0)];
        });
        $maxRevenue = max(1, (int) $chartDays->max('amount'));

        return view('dashboard.seller', [
            'role' => $seller->getRoleNames()->first(),
            'products' => $products,
            'orders' => $orders,
            'productCount' => $seller->products()->count(),
            'activeProductCount' => $seller->products()->where('status', 'active')->count(),
            'orderCount' => $orderCount,
            'pendingCount' => $seller->sales()->where('status', 'pending')->count(),
            'lowStockProducts' => $seller->products()->where('status', 'active')->whereColumn('quantity', '<=', 'low_stock_threshold')->orderBy('quantity')->limit(5)->get(),
            'expiringProducts' => $seller->products()->where('status', 'active')->whereNotNull('expires_at')->whereDate('expires_at', '<=', today()->addDays(30))->orderBy('expires_at')->limit(5)->get(),
            'revenue' => $revenue,
            'periodRevenue' => $periodRevenue,
            'days' => $days,
            'chartDays' => $chartDays,
            'maxRevenue' => $maxRevenue,
            'topProducts' => $seller->products()
                ->withSum(['orderItems as sales_total' => fn ($query) => $query->whereHas('order', fn ($order) => $order
                    ->where('status', 'completed')
                    ->where('completed_at', '>=', $periodStart))], 'total')
                ->orderByDesc('sales_total')
                ->limit(5)
                ->get(),
            'categoryRevenue' => $seller->sales()
                ->where('status', 'completed')
                ->where('completed_at', '>=', $periodStart)
                ->with('items')
                ->get()
                ->flatMap(fn (Order $order) => $order->items)
                ->groupBy('category')
                ->map(fn ($items) => $items->sum('total')),
        ]);
    }

    public function analytics(Request $request): View
    {
        $response = $this->index($request);

        return view('seller.analytics', $response->getData());
    }
}
