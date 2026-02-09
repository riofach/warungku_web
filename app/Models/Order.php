<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'housing_block_id',
        'customer_name',
        'whatsapp_number',
        'payment_method',
        'delivery_type',
        'status',
        'total',
        'payment_url',
        'payment_token',
        'payment_expires_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'payment_expires_at' => 'datetime',
    ];

    // Order statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY = 'ready';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    // Payment methods
    const PAYMENT_CASH = 'cash';
    const PAYMENT_QRIS = 'qris';

    // Delivery types
    const DELIVERY_TYPE_DELIVERY = 'delivery';
    const DELIVERY_TYPE_PICKUP = 'pickup';

    /**
     * Get the housing block
     */
    public function housingBlock(): BelongsTo
    {
        return $this->belongsTo(HousingBlock::class);
    }

    /**
     * Get order items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PAID, self::STATUS_COMPLETED => 'success',
            self::STATUS_PROCESSING, self::STATUS_READY, self::STATUS_DELIVERED => 'info',
            self::STATUS_CANCELLED, self::STATUS_FAILED => 'error',
            default => 'secondary',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Dibayar',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_READY => 'Siap Diambil/Antar',
            self::STATUS_DELIVERED => 'Sedang Diantar',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_FAILED => 'Gagal',
            default => $this->status,
        };
    }

    /**
     * Generate unique order code
     */
    public static function generateCode(): string
    {
        $today = now()->format('Ymd');
        $count = self::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'WRG-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PAID]);
    }

    /**
     * Scope for processing orders
     */
    public function scopeProcessing($query)
    {
        return $query->whereIn('status', [self::STATUS_PROCESSING, self::STATUS_READY]);
    }
}
