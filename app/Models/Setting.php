<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Setting extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'key',
        'value',
    ];

    // Setting keys
    const KEY_OPERATING_HOURS_OPEN = 'operating_hours_open';
    const KEY_OPERATING_HOURS_CLOSE = 'operating_hours_close';
    const KEY_WHATSAPP_NUMBER = 'whatsapp_number';
    const KEY_DELIVERY_ENABLED = 'delivery_enabled';
    const KEY_WARUNG_NAME = 'warung_name';

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if warung is currently open
     */
    public static function isWarungOpen(): bool
    {
        $openTime = self::getValue(self::KEY_OPERATING_HOURS_OPEN, '08:00');
        $closeTime = self::getValue(self::KEY_OPERATING_HOURS_CLOSE, '21:00');
        
        $now = now()->format('H:i');
        
        return $now >= $openTime && $now <= $closeTime;
    }

    /**
     * Check if delivery is enabled
     */
    public static function isDeliveryEnabled(): bool
    {
        return self::getValue(self::KEY_DELIVERY_ENABLED, 'true') === 'true';
    }

    /**
     * Get WhatsApp number
     */
    public static function getWhatsappNumber(): ?string
    {
        return self::getValue(self::KEY_WHATSAPP_NUMBER);
    }

    /**
     * Get warung name
     */
    public static function getWarungName(): string
    {
        return self::getValue(self::KEY_WARUNG_NAME, 'WarungKu');
    }
}
