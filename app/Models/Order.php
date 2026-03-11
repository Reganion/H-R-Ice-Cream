<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'delivery_date' => 'date',
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'qty' => 'integer',
        'delivered_at' => 'datetime',
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
        'received_amount',
        'delivery_payment_method',
        'delivery_proof_image',
        'qty',
        'payment_method',
        'status',
        'reason',
        'driver_id',
        'status_driver',
        'delivered_at',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}

