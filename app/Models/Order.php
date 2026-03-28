<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_phone',
        'shipping_street',
        'shipping_building',
        'shipping_city',
        'shipping_region',
        'shipping_postal_code',
        'shipping_country',
        'shipping_delivery_notes',
        'subtotal',
        'shipping_amount',
        'total',
        'tracking_number',
        'shipped_at',
        'customer_notes',
        'admin_notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
        'subtotal' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        return 'BN-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingFullName(): string
    {
        return trim($this->shipping_first_name.' '.$this->shipping_last_name);
    }
}
