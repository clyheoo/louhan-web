<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_peserta',
        'jenis_keanggotaan',
        'detail_anggota',
    ];

    public function ikans()
    {
        return $this->hasMany(Ikan::class);
    }
    
    // Deprecated: pindah ke Ikan (sisipkan dulu agar tidak error di file lain sementara)
    public function scorings() {
        return $this->hasMany(Scoring::class);
    }
}               