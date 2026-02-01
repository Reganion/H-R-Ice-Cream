<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flavor extends Model
{
    protected $fillable = [
        'name',
        'flavor_type', // ✅ REQUIRED
        'category',
        'price',
        'image',
        'status',
    ];

}
