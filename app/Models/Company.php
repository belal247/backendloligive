<?php
// app/Models/Company.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'org_key_id',
        'alias',
        'logo',
        'description',
        'video',
        'purpose_reason',
        'location'
    ];

    protected $casts = [
        'purpose_reason' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'org_key_id', 'org_key_id');
    }
}