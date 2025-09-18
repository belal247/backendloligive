<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanSession extends Model
{
    protected $fillable = [
        'scan_id',
        'merchant_id',
        'merchant_contact',
        'isMobile',
        'tries',
        'encryption_key',
        'encrypted_data',
        'scanned_at',    
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];
}
