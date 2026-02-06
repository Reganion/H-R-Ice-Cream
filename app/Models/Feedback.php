<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'flavor_id',
        'order_id',
        'customer_name',
        'photo',
        'rating',
        'testimonial',
        'feedback_date',
    ];

    public function flavor()
    {
        return $this->belongsTo(\App\Models\Flavor::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    protected $casts = [
        'feedback_date' => 'date',
    ];

}
