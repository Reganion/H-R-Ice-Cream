<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',         // link to order (null until downpayment is paid)
        'customer_id',      // set when invoice is created; used when order_id is null
        'order_payload',    // order data stored until payment succeeds
        'idempotency_key',  // client-provided key to prevent duplicate downpayment creation
        'payment_intent_id',// PayMongo PaymentIntent
        'source_id',        // PayMongo source (GCash)
        'amount',
        'currency',
        'status',           // pending, paid, failed
        'payment_method',   // gcash, card, etc.
        'qr_image_url',     // stored so duplicate-key responses can return same QR
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'order_payload' => 'array',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
