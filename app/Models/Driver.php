<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    /** Status values as stored in database */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ON_ROUTE = 'on_route';
    public const STATUS_OFF_DUTY = 'off_duty';
    public const STATUS_DEACTIVATE = 'deactivate';
    public const STATUS_ARCHIVE = 'archive';

    /**
     * DB status value => [ label for display, filter value for tabs/data-status ]
     */
    public static function statusConfig(): array
    {
        return [
            self::STATUS_AVAILABLE => ['label' => 'Available', 'filter' => 'available'],
            self::STATUS_ON_ROUTE  => ['label' => 'On Route', 'filter' => 'on'],
            self::STATUS_OFF_DUTY  => ['label' => 'Off Duty', 'filter' => 'off'],
            self::STATUS_DEACTIVATE => ['label' => 'Inactive', 'filter' => 'deactivate'],
            self::STATUS_ARCHIVE => ['label' => 'Archived', 'filter' => 'archive'],
        ];
    }

    /**
     * Statuses that appear in the active driver list.
     */
    public static function statusesForList(): array
    {
        return [self::STATUS_AVAILABLE, self::STATUS_ON_ROUTE, self::STATUS_OFF_DUTY];
    }

    protected $fillable = [
        'name',
        'phone',
        'email',
        'license_no',
        'license_type',
        'image',
        'status',
        'driver_code',
        'password',
        'current_lat',
        'current_lng',
        'last_updated',
    ];

    protected $hidden = [
        'password',
    ];

    public function orderMessages(): HasMany
    {
        return $this->hasMany(OrderMessage::class, 'driver_id');
    }
}
