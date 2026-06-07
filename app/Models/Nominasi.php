<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nominasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'juri_id',
        'ikan_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'catatan',
        'is_late_addition',
    ];

    protected $casts = [
        'reviewed_at'      => 'datetime',
        'is_late_addition' => 'boolean',
    ];

    public function juri()
    {
        return $this->belongsTo(User::class, 'juri_id');
    }

    public function ikan()
    {           
        return $this->belongsTo(Ikan::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}