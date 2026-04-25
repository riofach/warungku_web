<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUnit extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'item_id',
        'label',
        'quantity_base',
        'sell_price',
        'buy_price',
        'is_active',
    ];

    protected $casts = [
        'quantity_base' => 'integer',
        'sell_price'    => 'integer',
        'buy_price'     => 'integer',
        'is_active'     => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedSellPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->sell_price, 0, ',', '.');
    }

    public function availableFrom(int $stock): int
    {
        if ($this->quantity_base <= 0) return 0;
        return (int) floor($stock / $this->quantity_base);
    }
}
