<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringPointConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori',
        'overall_bobot',
        'head_bobot',
        'face_bobot',
        'body_bobot',
        'marking_bobot',
        'pearl_bobot',
        'color_bobot',
        'finnage_bobot',
    ];

    protected $casts = [
        'overall_bobot' => 'decimal:2',
        'head_bobot' => 'decimal:2',
        'face_bobot' => 'decimal:2',
        'body_bobot' => 'decimal:2',
        'marking_bobot' => 'decimal:2',
        'pearl_bobot' => 'decimal:2',
        'color_bobot' => 'decimal:2',
        'finnage_bobot' => 'decimal:2',
    ];
}