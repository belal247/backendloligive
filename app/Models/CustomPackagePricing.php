<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPackagePricing extends Model
{
    use HasFactory;

    protected $table = 'custom_package_pricing';

    protected $fillable = [
        'package_id',
        'business_user_per_custom_api_cost',
        'enterprise_user_per_custom_api_cost',
        'sub_business_fee',
        'billing_cycle'
    ];

    protected $casts = [
        'business_user_per_custom_api_cost' => 'decimal:2',
        'enterprise_user_per_custom_api_cost' => 'decimal:2',
        'sub_business_fee' => 'decimal:2'
    ];
}
