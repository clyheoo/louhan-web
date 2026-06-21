<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IkanFoto extends Model
{
    protected $table = 'ikan_fotos';

    protected $fillable = ['ikan_id', 'path', 'size'];

    public function ikan()
    {
        return $this->belongsTo(Ikan::class);
    }
}