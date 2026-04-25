<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'has_units',
        'base_unit',
    ];

    protected $casts = [
        'buy_price'       => 'integer',
        'sell_price'      => 'integer',
        'stock'           => 'integer',
        'stock_threshold' => 'integer',
        'is_active'       => 'boolean',
        'has_units'       => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ItemUnit::class)->orderBy('quantity_base', 'desc');
    }

    public function activeUnits(): HasMany
    {
        return $this->hasMany(ItemUnit::class)->where('is_active', true)->orderBy('quantity_base', 'desc');
    }

    public function isStockSafe(): bool
    {
        return $this->stock > $this->stock_threshold;
    }

    public function isStockLow(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /** Display stock: for gram items shows "X,X Kg", others show raw integer + unit */
    public function displayStock(): string
    {
        if ($this->has_units && $this->base_unit === 'gram') {
            return number_format($this->stock / 1000, 1, ',', '.') . ' Kg';
        }
        return $this->stock . ' ' . ($this->base_unit ?? 'pcs');
    }

    /** Max qty available for a given unit variant */
    public function availableForUnit(int $quantityBase): int
    {
        if ($quantityBase <= 0) return 0;
        return (int) floor($this->stock / $quantityBase);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->sell_price, 0, ',', '.');
    }

    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true');
    }

    public function scopeAvailable($query)
    {
        return $query->active()->where('stock', '>', 0);
    }
}
