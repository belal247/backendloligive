<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'country_code',
        //'country_name',
        'phone_no',
        //'otp_verified',
        'organization_verified',
        'verification_reason',
        'org_key_id'
        /* 'aes_key',
        'role', // Add this line,
        'on_trial',
        'trial_calls_remaining',
        'trial_ends_at',
        'parent_id',
        'subscription_package_price',
        'over_limit_price',
        'subscription_amount',
        'price_per_scan' */
    ];

    /* protected $casts = [
        'otp_verified' => 'boolean',
        'business_verified' => 'string',
        'subscription_package_price' => 'decimal:2',
        'over_limit_price' => 'decimal:2',
        'price_per_scan' => 'decimal:2'
    ]; */

    //protected $guarded = []; // Or configure as needed

    protected $hidden = [
        'aes_key'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class, 'user_id');
    }
    
    public function getJWTIdentifier()
    {
        return $this->merchant_id; // Now using merchant_id as JWT identifier
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /* public function subBusinesses()
    {
        return $this->hasMany(User::class, 'parent_id');
    } */
}
