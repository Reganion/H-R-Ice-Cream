<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMessage extends Model
{
    public const SENDER_DRIVER = 'driver';
    public const SENDER_CUSTOMER = 'customer';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVE = 'archive';

    /** Customer-facing status: Active = visible, Archive = hidden (soft-deleted by customer) */
    public const CUSTOMER_STATUS_ACTIVE = 'active';
    public const CUSTOMER_STATUS_ARCHIVE = 'archive';

    protected $fillable = [
        'order_id',
        'driver_id',
        'customer_id',
        'sender_type',
        'message',
        'status',
        'customer_status',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
