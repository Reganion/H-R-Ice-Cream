<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallon extends Model
{
    protected $fillable = [
        'size',
        'quantity',
        'addon_price',
        'status',
        'image',
    ];
}

