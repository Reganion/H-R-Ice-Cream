<?php

namespace App\Models;

use App\Models\Driver;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'delivery_date' => 'date',
        'amount' => 'decimal:2',
    ];

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
        'status',
        'driver_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
