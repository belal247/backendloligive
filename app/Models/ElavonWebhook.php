<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElavonWebhook extends Model
{
    protected $fillable = ['payload'];

    protected $casts = [
        'payload' => 'array',
    ];
}
