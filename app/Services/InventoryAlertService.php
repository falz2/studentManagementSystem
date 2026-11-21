<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Notifications\MarketplaceAlert;

class InventoryAlertService
{
    public function notifyFor(Product $product): void
    {
        $seller = $product->seller;
        if (! $seller || $product->status !== 'active') {
            return;
        }

        $lowStockKey = 'low-stock-'.$product->id;
        if ($product->quantity <= $product->low_stock_threshold) {
            $this->notifyOnce(
                $seller,
                $lowStockKey,
                'Stock is running low',
                $product->name.' has '.$product->quantity.' '.$product->unit.($product->quantity === 1 ? '' : 's').' left.',
                route('seller.products.index'),
                '⚠️',
            );
        } else {
            $seller->notifications()
                ->where('type', MarketplaceAlert::class)
                ->whereNull('read_at')
                ->where('data->key', $lowStockKey)
                ->update(['read_at' => now()]);
        }

        if ($product->expires_at && $product->expires_at->lte(today()->addDays(30))) {
            $expired = $product->expires_at->lt(today());
            $this->notifyOnce(
                $seller,
                'expiry-'.$product->id.'-'.$product->expires_at->toDateString(),
                $expired ? 'Product expiry date passed' : 'Product expiring soon',
                $product->name.($expired ? ' passed its expiry date.' : ' expires on '.$product->expires_at->format('M j, Y').'.'),
                route('seller.products.index'),
                '🗓️',
            );
        }
    }

    private function notifyOnce(
        User $seller,
        string $key,
        string $title,
        string $message,
        string $url,
        string $icon,
    ): void {
        $exists = $seller->notifications()
            ->where('type', MarketplaceAlert::class)
            ->whereNull('read_at')
            ->where('data->key', $key)
            ->exists();

        if (! $exists) {
            $seller->notify(new MarketplaceAlert($title, $message, $url, $icon, $key));
        }
    }
}
