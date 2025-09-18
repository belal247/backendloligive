<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchPaymentInfo extends Model
{
    use HasFactory;

    protected $table = 'ach_payment_info';

    protected $fillable = [
        'merchant_id',
        'ach_string',
        'ach_payment_api_response'
    ];
}