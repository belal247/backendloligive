<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 
        'merchant_id', 
        'package_id',
        'api_calls_limit', 
        'api_calls_used', 
        'overage_calls', 
        'subscription_date',
        'renewal_date', 
        'is_blocked',
        'custom_package',
        'custom_price',
        'custom_calls_used',
        'custom_status',
        'custom_api_count'
    ];

    public function package()
    {
        return $this->belongsTo(DefaultPackage::class);
    }
}
