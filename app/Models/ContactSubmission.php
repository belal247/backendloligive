<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_name',
        'business_email',
        'phone_no',
        'business_type',
        'expected_monthly_income',
        'current_payment_provider',
        'description'
    ];
}