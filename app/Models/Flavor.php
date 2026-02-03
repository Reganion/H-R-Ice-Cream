<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Flavor extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $fillable = [
        'name',
        'flavor_type', // ✅ REQUIRED
        'category',
        'price',
        'image',
        'mobile_image',
        'status',
    ];

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'favorites')
            ->withTimestamps();
    }
}
