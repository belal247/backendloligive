<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardScan extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'merchant_key',
        'card_number_masked',
        'status',
    ];

}
