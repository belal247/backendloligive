<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_name',
        'organization_registration_number',
        'street',
        'street_line2',
        'city',
        'state',
        'zip_code',
        'country',
        'email', // New field
        'account_holder_first_name',
        'account_holder_last_name',
        'account_holder_email', // New field
        'account_holder_date_of_birth', // New field
        'account_holder_street', // New field
        'account_holder_street_line2', // New field
        'account_holder_city', // New field
        'account_holder_state', // New field
        'account_holder_zip_code', // New field
        'account_holder_country', // New field
        'account_holder_id_type', // New field
        'account_holder_id_number', // New field
        'account_holder_id_document_path', // New field
        'registration_document_path',
        'display_name', 
        'display_logo'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}