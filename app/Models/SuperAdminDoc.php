<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminDoc extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'file_path', 'file_type'
    ];
}
