<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultPackage extends Model
{
    protected $fillable = [
        'package_name',
        'package_price', 
        'package_period',
        'package_description',
        'monthly_limit',
        'overage_rate',
    ];
}
