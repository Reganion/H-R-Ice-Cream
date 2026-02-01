<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    /** Status values as stored in database */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ON_ROUTE = 'on_route';
    public const STATUS_OFF_DUTY = 'off_duty';
    public const STATUS_DEACTIVATE = 'deactivate';

    /**
     * DB status value => [ label for display, filter value for tabs/data-status ]
     */
    public static function statusConfig(): array
    {
        return [
            self::STATUS_AVAILABLE => ['label' => 'Available', 'filter' => 'available'],
            self::STATUS_ON_ROUTE  => ['label' => 'On Route', 'filter' => 'on'],
            self::STATUS_OFF_DUTY  => ['label' => 'Off Duty', 'filter' => 'off'],
            self::STATUS_DEACTIVATE => ['label' => 'Deactivate', 'filter' => 'deactivate'],
        ];
    }

    /**
     * Statuses that appear in the driver list (excludes deactivate)
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
    ];
}
