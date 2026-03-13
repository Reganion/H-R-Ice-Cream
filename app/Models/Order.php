<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $casts = [
        'delivery_date' => 'date',
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'downpayment' => 'decimal:2',
        'balance' => 'decimal:2',
        'qty' => 'integer',
        'delivered_at' => 'datetime',
    ];

    protected $fillable = [
        'customer_id',
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
        'downpayment',
        'balance',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }
}

