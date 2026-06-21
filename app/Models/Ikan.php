<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ikan extends Model
{
    use HasFactory;

    protected $fillable = [
        'peserta_id', 
        'nama_peserta',
        'detail_anggota', 
        'jenis_keanggotaan', // ★ TAMBAHKAN INI
        'kategori', 
        'kelas', 
        'nomor_tank',
        'foto_path',
        'foto_replace_requested',
        'dibuat_oleh',
        'diubah_oleh',
        'is_locked',
        'is_mvp',
        'is_team_champion',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_mvp' => 'boolean',
        'is_team_champion' => 'boolean',
        'foto_replace_requested' => 'boolean',
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

    public function fotos()
    {
        return $this->hasMany(IkanFoto::class);
    }
}