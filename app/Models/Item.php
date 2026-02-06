<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Item extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'name',
        'buy_price',
        'sell_price',
        'stock',
        'stock_threshold',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'buy_price' => 'integer',
        'sell_price' => 'integer',
        'stock' => 'integer',
        'stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category of this item
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Check if stock is safe
     */
    public function isStockSafe(): bool
    {
        return $this->stock > $this->stock_threshold;
    }

    /**
     * Check if stock is low (warning)
     */
    public function isStockLow(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->stock_threshold;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->sell_price, 0, ',', '.');
    }

    /**
     * Scope for active items only
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true');
    }

    /**
     * Scope for available items (active and in stock)
     */
    public function scopeAvailable($query)
    {
        return $query->active()->where('stock', '>', 0);
    }
}
