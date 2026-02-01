<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_name',
        'product_type',
        'gallon_size',
        'product_image',
        'customer_name',
        'customer_phone',
        'customer_image',
        'delivery_date',
        'delivery_time',
        'delivery_address',
        'amount',
        'payment_method',
        'status'
    ];
}
