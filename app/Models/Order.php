<?php

namespace App\Models;

use App\Models\Driver;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'delivery_date' => 'date',
        'amount' => 'decimal:2',
        'qty' => 'integer',
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
        'qty',
        'payment_method',
        'status',
        'reason',
        'driver_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
