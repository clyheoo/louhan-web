<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    // Wajib diisi agar data bisa disimpan menggunakan Peserta::create()
    protected $fillable = [
        'user_id',
        'nama_peserta',
        'kategori',
        'kelas',
        'jenis_keanggotaan',
        'detail_anggota',
    ];

    // Tambahkan di dalam class Peserta
    public function scorings() {
        return $this->hasMany(Scoring::class);
    }
}