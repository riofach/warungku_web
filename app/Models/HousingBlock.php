<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousingBlock extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get orders from this housing block
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
