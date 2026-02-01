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
    ];

    protected $hidden = [
        'password',
    ];
}
