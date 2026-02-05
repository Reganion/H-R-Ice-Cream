<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
{
    protected $table = 'customer_notifications';

    protected $fillable = [
        'customer_id',
        'type',
        'title',
        'message',
        'image_url',
        'related_type',
        'related_id',
        'read_at',
        'data',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data'    => 'array',
    ];

    public const TYPE_ORDER_PLACED = 'order_placed';
    public const TYPE_ORDER_DELIVERED = 'order_delivered';
    public const TYPE_ORDER_STATUS = 'order_status';
    public const TYPE_PROMO = 'promo';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
