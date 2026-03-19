<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverNotification extends Model
{
    protected $table = 'driver_notifications';

    protected $fillable = [
        'driver_id',
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
        'data' => 'array',
    ];

    public const TYPE_SHIPMENT_ASSIGNED = 'shipment_assigned';
    public const TYPE_SHIPMENT_COMPLETED = 'shipment_completed';
    public const TYPE_SHIPMENT_STATUS = 'shipment_status';

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
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

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }
}

