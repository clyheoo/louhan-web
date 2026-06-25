<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IkanFoto extends Model
{
    protected $table = 'ikan_fotos';

    protected $fillable = ['ikan_id', 'path', 'size', 'uploaded_by', 'uploaded_role'];

    public function ikan()
    {
        return $this->belongsTo(Ikan::class);
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}