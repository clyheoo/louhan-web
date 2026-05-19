<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringPointConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori',
        'overall_bobot', 'overall_point',
        'head_bobot', 'head_size_pct', 'head_bentuk_k_pct',
        'face_bobot', 'face_face_pct',
        'body_bobot', 'body_bentuk_pct', 'body_proposional_pct', 'body_pangkal_pct',
        'marking_bobot', 'marking_fullness_pct', 'marking_contrast_pct', 'marking_bentuk_pct',
        'pearl_bobot', 'pearl_shinning_pct', 'pearl_fullnes_pct', 'pearl_bentuk_pearl_pct',
        'color_bobot', 'color_komposisi_pct', 'color_kecerahan_pct', 'color_fullness_colour_pct',
        'finnage_bobot', 'finnage_bentuk_sirip_ekor_pct', 'finnage_kecerahan_pct',
    ];

    protected $casts = [
        'overall_bobot' => 'decimal:2',
        'overall_point' => 'decimal:2',
        'head_bobot' => 'decimal:2', 'head_size_pct' => 'decimal:2', 'head_bentuk_k_pct' => 'decimal:2',
        'face_bobot' => 'decimal:2', 'face_face_pct' => 'decimal:2',
        'body_bobot' => 'decimal:2', 'body_bentuk_pct' => 'decimal:2', 'body_proposional_pct' => 'decimal:2', 'body_pangkal_pct' => 'decimal:2',
        'marking_bobot' => 'decimal:2', 'marking_fullness_pct' => 'decimal:2', 'marking_contrast_pct' => 'decimal:2', 'marking_bentuk_pct' => 'decimal:2',
        'pearl_bobot' => 'decimal:2', 'pearl_shinning_pct' => 'decimal:2', 'pearl_fullnes_pct' => 'decimal:2', 'pearl_bentuk_pearl_pct' => 'decimal:2',
        'color_bobot' => 'decimal:2', 'color_komposisi_pct' => 'decimal:2', 'color_kecerahan_pct' => 'decimal:2', 'color_fullness_colour_pct' => 'decimal:2',
        'finnage_bobot' => 'decimal:2', 'finnage_bentuk_sirip_ekor_pct' => 'decimal:2', 'finnage_kecerahan_pct' => 'decimal:2',
    ];
}