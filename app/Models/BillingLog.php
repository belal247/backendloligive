<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'period_start',
        'period_end',
        'base_calls',
        'overage_calls',
        'overage_charge',
        'due_date',
        'is_paid',
    ];
}
