<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'user_id',
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

    public const TYPE_PROFILE_UPDATE = 'profile_update';
    public const TYPE_ADDRESS_UPDATE = 'address_update';
    public const TYPE_DELIVERY_SUCCESS = 'delivery_success';
    public const TYPE_ORDER_NEW = 'order_new';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Create a notification for all admin users (all users in users table).
     */
    public static function createForAllAdmins(
        string $type,
        string $title,
        ?string $message = null,
        ?string $imageUrl = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?array $data = null
    ): void {
        $userIds = User::pluck('id');
        $now = now();
        $rows = $userIds->map(fn ($userId) => [
            'user_id'      => $userId,
            'type'         => $type,
            'title'        => $title,
            'message'     => $message,
            'image_url'    => $imageUrl,
            'related_type' => $relatedType,
            'related_id'   => $relatedId,
            'data'         => $data ? json_encode($data) : null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->all();

        if (!empty($rows)) {
            self::insert($rows);
        }
    }

    /**
     * Notify all admins when a customer updates their profile.
     * $highlight: "Photo" | "Phone Number" | "Name" | "Profile" (what was updated).
     */
    public static function notifyProfileUpdated(Customer $customer, string $highlight = 'Profile'): void
    {
        $name = trim($customer->firstname . ' ' . $customer->lastname) ?: $customer->email ?? 'Customer';
        self::createForAllAdmins(
            self::TYPE_PROFILE_UPDATE,
            $name,
            null,
            $customer->image ?: null,
            'Customer',
            $customer->id,
            ['subtitle' => 'updated their', 'highlight' => $highlight]
        );
    }

    /**
     * Notify all admins when a customer updates their address.
     */
    public static function notifyAddressUpdated(Customer $customer): void
    {
        $name = trim($customer->firstname . ' ' . $customer->lastname) ?: $customer->email ?? 'Customer';
        self::createForAllAdmins(
            self::TYPE_ADDRESS_UPDATE,
            $name,
            null,
            $customer->image ?: null,
            'Customer',
            $customer->id,
            ['subtitle' => 'updated their', 'highlight' => 'Address']
        );
    }
}
