<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ikan extends Model
{
    use HasFactory;

    protected $fillable = [
        'peserta_id', 
        'kategori', 
        'kelas', 
        'nomor_tank',
        'dibuat_oleh',
        'diubah_oleh',
        'is_locked',
        'is_mvp', // ★ TAMBAHKAN INI
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_mvp' => 'boolean', // ★ TAMBAHKAN INI
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }
    
    public function scorings() 
    {
        return $this->hasMany(Scoring::class);
    }

    public function bonusPoints()
    {
        return $this->hasMany(IkanBonusPoint::class);
    }
}