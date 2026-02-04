<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $table = 'customers';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'contact_no',
        'image',
        'status',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'province',
        'city',
        'barangay',
        'postal_code',
        'street_name',
        'label_as',
        'reason',
    ];

    protected $hidden = [
        'password',
        'otp',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at'   => 'datetime',
            'email_verified_at' => 'datetime',
            'password'         => 'hashed',
        ];
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Flavor::class, 'favorites')
            ->withTimestamps();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }
}
