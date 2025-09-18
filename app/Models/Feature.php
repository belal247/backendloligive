<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = [
        'user_id',
        'bank_logo',
        'chip',
        'mag_strip',
        'sig_strip',
        'hologram',
        'customer_service',
        'symmetry',
    ];
}
