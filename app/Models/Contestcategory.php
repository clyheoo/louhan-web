<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'uses_kelas', 'urutan'];

    protected $casts = [
        'uses_kelas' => 'boolean',
    ];
}