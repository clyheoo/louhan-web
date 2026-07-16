<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piagam extends Model
{
    protected $fillable = [
        'peserta_id',
        'kategori',
        'kelas',
        'judul',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }
}