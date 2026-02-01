<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'contact_no',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
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
}
