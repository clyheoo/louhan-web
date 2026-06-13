<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JuriAssignment extends Model
{
    protected $fillable = ['juri_id', 'kategori', 'kelas'];

    public function juri()
    {
        return $this->belongsTo(User::class, 'juri_id');
    }
}