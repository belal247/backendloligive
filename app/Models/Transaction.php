<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'comment',
        'org_id',
        'paymentmethod',
        'purpose',
        'amount',
        'txn_id',
        'status',
        'is_approved',
        'raw_payload',
        'bank_fees', 
        'date_time'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'org_id', 'org_key_id');
    }

}