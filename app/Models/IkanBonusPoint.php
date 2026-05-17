<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IkanBonusPoint extends Model
{
    use HasFactory;

    protected $fillable = ['ikan_id', 'bonus_type', 'points', 'added_by'];

    public function ikan()
    {
        return $this->belongsTo(Ikan::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}