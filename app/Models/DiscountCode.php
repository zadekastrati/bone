<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'label',
        'percent_off',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'percent_off' => 'integer',
        'used_at' => 'datetime',
    ];

    public function usedByOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'used_by_order_id');
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
