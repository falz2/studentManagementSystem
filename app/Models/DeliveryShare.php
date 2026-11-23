<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryShare extends Model
{
    protected $fillable = [
        'order_id',
        'token_hash',
        'latitude',
        'longitude',
        'accuracy_meters',
        'location_updated_at',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_meters' => 'float',
            'location_updated_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture()
            && $this->order?->delivery_status === 'in_transit'
            && $this->order?->status === 'confirmed';
    }
}
