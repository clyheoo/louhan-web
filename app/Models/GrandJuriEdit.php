<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrandJuriEdit extends Model
{
    use HasFactory;

    protected $table = 'grand_juri_edits';

    protected $fillable = [
        'scoring_id',
        'peserta_id',
        'grand_juri_id',
        'nilai_sebelum',
        'nilai_sesudah',
        'changed_fields',
        'total_sebelum',
        'total_sesudah',
        'catatan',
    ];

    protected $casts = [
        'nilai_sebelum'  => 'array',
        'nilai_sesudah'  => 'array',
        'changed_fields' => 'array',
    ];

    /* ── Relasi ── */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function scoring()
    {
        return $this->belongsTo(Scoring::class);
    }

    public function grandJuri()
    {
        return $this->belongsTo(User::class, 'grand_juri_id');
    }
}