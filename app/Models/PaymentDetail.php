<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'merchant_id',
        'email',
        'billing_address',
        'city',
        'zipcode',
        'country',
        'payment_method',
        'payment_result' // Will be automatically cast to/from JSON
    ];

    protected $casts = [
        'payment_result' => 'array' // Automatically cast JSON to PHP array
    ];

    // Relationship to User model
    public function user()
    {
        return $this->belongsTo(User::class, 'merchant_id', 'merchant_id');
    }
}