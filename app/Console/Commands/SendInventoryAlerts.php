<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\InventoryAlertService;
use Illuminate\Console\Command;

class SendInventoryAlerts extends Command
{
    protected $signature = 'marketplace:send-inventory-alerts';

    protected $description = 'Notify sellers about low stock and upcoming product expiry dates';

    public function handle(InventoryAlertService $alerts): int
    {
        Product::query()
            ->with('seller')
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereColumn('quantity', '<=', 'low_stock_threshold')
                    ->orWhere(function ($expiryQuery): void {
                        $expiryQuery->whereNotNull('expires_at')
                            ->whereDate('expires_at', '<=', today()->addDays(30));
                    });
            })
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($alerts): void {
                foreach ($products as $product) {
                    $alerts->notifyFor($product);
                }
            });

        $this->info('Inventory alerts checked.');

        return self::SUCCESS;
    }
}
