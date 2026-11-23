<?php

namespace App\Console\Commands;

use App\Models\DeliveryShare;
use Illuminate\Console\Command;

class PurgeExpiredDeliveryLocations extends Command
{
    protected $signature = 'delivery-shares:purge-expired';

    protected $description = 'Remove saved GPS points from expired or revoked rider shares';

    public function handle(): int
    {
        $purged = DeliveryShare::query()
            ->whereNotNull('latitude')
            ->where(function ($query): void {
                $query->where('expires_at', '<=', now())
                    ->orWhereNotNull('revoked_at');
            })
            ->update([
                'latitude' => null,
                'longitude' => null,
                'accuracy_meters' => null,
                'location_updated_at' => null,
                'updated_at' => now(),
            ]);

        $this->info("Removed {$purged} expired delivery locations.");

        return self::SUCCESS;
    }
}
