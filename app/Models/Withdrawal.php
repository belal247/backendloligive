<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'org_id',
        'amount',
        'bank_name',
        'account_no',
        'account_holder_name',
        'iban',
        'branch_address',
        'zelle_name',
        'zelle_email',
        'zelle_phone',
        'isZelle',
        'withdrawal_status', 
    ];
}
