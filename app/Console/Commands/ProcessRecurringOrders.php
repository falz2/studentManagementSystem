<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\RecurringOrder;
use App\Notifications\MarketplaceAlert;
use App\Services\InventoryAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessRecurringOrders extends Command
{
    protected $signature = 'marketplace:process-recurring-orders';

    protected $description = 'Create due supplier orders from active buyer schedules';

    public function handle(InventoryAlertService $inventoryAlerts): int
    {
        $dueOrders = RecurringOrder::query()
            ->where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->pluck('id');

        $processed = 0;
        foreach ($dueOrders as $id) {
            $processed += (int) DB::transaction(function () use ($id, $inventoryAlerts): bool {
                $schedule = RecurringOrder::query()->lockForUpdate()->find($id);
                if (! $schedule || $schedule->status !== 'active' || $schedule->next_run_at->isFuture()) {
                    return false;
                }

                $product = Product::query()->lockForUpdate()->find($schedule->product_id);
                $buyer = $schedule->buyer;
                $seller = $schedule->seller;
                if (! $product || ! $buyer || ! $seller) {
                    $schedule->update(['status' => 'paused']);

                    return false;
                }

                $nextRun = $this->nextRun($schedule);
                if ($product->status !== 'active' || $product->quantity < $schedule->quantity || $product->expires_at?->lt(today())) {
                    $buyer->notify(new MarketplaceAlert(
                        'Scheduled order could not be placed',
                        $product->name.' is unavailable or has insufficient stock. Your next scheduled order is '.$nextRun->format('M j, Y').'.',
                        route('recurring-orders.index'),
                        '⚠️',
                    ));
                    $schedule->update(['next_run_at' => $nextRun]);

                    return true;
                }

                $order = Order::create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                    'order_number' => 'PH-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                    'total' => $product->price * $schedule->quantity,
                    'status' => 'pending',
                    'notes' => 'Created from recurring schedule #'.$schedule->id,
                ]);
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category' => $product->category,
                    'unit' => $product->unit,
                    'quantity' => $schedule->quantity,
                    'unit_price' => $product->price,
                    'total' => $product->price * $schedule->quantity,
                ]);
                $product->decrement('quantity', $schedule->quantity);
                $inventoryAlerts->notifyFor($product->fresh());
                $schedule->update(['next_run_at' => $nextRun]);

                $buyer->notify(new MarketplaceAlert(
                    'Your scheduled order was placed',
                    $order->order_number.' for '.$product->name.' is awaiting seller confirmation.',
                    route('orders.index'),
                    '🔁',
                ));
                $seller->notify(new MarketplaceAlert(
                    'New scheduled order',
                    $order->order_number.' from '.$buyer->name.' is ready for confirmation.',
                    route('seller.orders'),
                    '📦',
                ));

                return true;
            });
        }

        $this->info("Processed {$processed} due supplier orders.");

        return self::SUCCESS;
    }

    private function nextRun(RecurringOrder $schedule): Carbon
    {
        $next = $schedule->next_run_at->copy();
        do {
            $next = $schedule->frequency === 'weekly'
                ? $next->addWeek()
                : $next->addMonthNoOverflow();
        } while ($next->lte(now()));

        return $next;
    }
}
