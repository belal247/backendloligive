<?php
// app/Models/Company.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_key_id',
        'name',
        'alias',
        'logo',
        'video',
        'main_image',
        'welcome_text',
        'testimony_text',
        'about_us_text',
        'about_us_image',
        'donation_message',
        'video_url',
        'contact_info',
        'purpose_reason',
        'isVideo',
    ];

    protected $casts = [
        'purpose_reason' => 'array',
        'contact_info' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'isVideo' => 'boolean'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'org_key_id', 'org_key_id');
    }
}