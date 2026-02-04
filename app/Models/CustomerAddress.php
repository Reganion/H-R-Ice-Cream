<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'firstname',
        'lastname',
        'contact_no',
        'province',
        'city',
        'barangay',
        'postal_code',
        'street_name',
        'label_as',
        'reason',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Build full address string for display.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_name,
            $this->barangay,
            $this->city ? $this->city . ' City' : null,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts) ?: '';
    }
}
