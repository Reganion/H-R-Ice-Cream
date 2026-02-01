<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'customer_name',
        'photo',
        'rating',
        'testimonial',
        'feedback_date',
    ];

    protected $casts = [
        'feedback_date' => 'date',
    ];

}
