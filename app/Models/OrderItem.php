<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'item_id',
        'item_unit_id',
        'quantity',
        'quantity_base_used',
        'price',
        'buy_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity'           => 'integer',
        'quantity_base_used' => 'integer',
        'price'              => 'integer',
        'buy_price'          => 'integer',
        'subtotal'           => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ItemUnit::class, 'item_unit_id');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
