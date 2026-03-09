<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',         // link to order
        'payment_intent_id',// PayMongo PaymentIntent
        'source_id',        // PayMongo source (GCash)
        'amount',
        'currency',
        'status',           // pending, paid, failed
        'payment_method',   // gcash, card, etc.
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
