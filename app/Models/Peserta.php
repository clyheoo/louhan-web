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
        'is_mvp_submitted',
        'result_unlocked_at',
    ];

    protected $casts = [
        'is_mvp_submitted' => 'boolean',
        'result_unlocked_at' => 'datetime',
    ];

    public function ikans()
    {
        return $this->hasMany(Ikan::class);
    }
    
    // Deprecated: pindah ke Ikan (sisipkan dulu agar tidak error di file lain sementara)
    public function scorings() {
        return $this->hasMany(Scoring::class);
    }

        public function user()
    {
        return $this->belongsTo(User::class);
    }
}               