<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $fillable = [
        'user_id',
        'old_password',
        'new_password',
        'changed_by',
    ];
}