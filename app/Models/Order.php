<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'order_number',
        'total',
        'status',
        'notes',
        'completed_at',
        'delivery_status',
        'confirmed_at',
        'recipient_name',
        'delivery_phone',
        'delivery_address',
        'delivery_instructions',
        'estimated_delivery_at',
        'delivery_scheduled_at',
        'in_transit_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'completed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'delivery_scheduled_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function deliveryShare(): HasOne
    {
        return $this->hasOne(DeliveryShare::class);
    }
}
