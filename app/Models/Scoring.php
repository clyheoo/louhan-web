<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scoring extends Model
{
    protected $fillable = [
        'peserta_id', 'juri_id', 'grand_juri_id', 'kategori', 'kelas',
        'nilai_detail', 'nilai_detail_asli', 'total_nilai', 'status', 'edited_by_grand_juri'
    ];

    protected $casts = [
        'nilai_detail'     => 'array',
        'nilai_detail_asli' => 'array',
        'edited_by_grand_juri' => 'boolean',
    ];

    public function peserta()   { return $this->belongsTo(Peserta::class); }
    public function juri()      { return $this->belongsTo(User::class, 'juri_id'); }
    public function grandJuri() { return $this->belongsTo(User::class, 'grand_juri_id'); }
}