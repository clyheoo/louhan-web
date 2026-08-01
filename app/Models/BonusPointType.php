<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusPointType extends Model
{
    protected $fillable = [
        'name', 'slug', 'adds_point', 'point_value', 'sort_order', 'is_system', 'icon',
    ];

    protected $casts = [
        'adds_point'  => 'boolean',
        'is_system'   => 'boolean',
        'point_value' => 'integer',
        'sort_order'  => 'integer',
    ];
}