<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'org_id',
        'bank_name',
        'account_no',
        'account_holder_name',
        'iban',
        'branch_address',
        'zelle_name',
        'zelle_email',
        'zelle_phone',
        'isZelle',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'org_id', 'org_key_id');
    }


}
