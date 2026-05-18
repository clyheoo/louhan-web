<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scoring extends Model
{
    protected $fillable = [
        'ikan_id',
        'juri_id',
        'kelas',
        'nilai_detail',
        'total_nilai',
        'total_point',
        'status',
        'submitted_to_grand',
        'edited_by_grand_juri',
        'grand_juri_id',
    ];

    protected $casts = [
        'nilai_detail'         => 'array',
        'edited_by_grand_juri' => 'boolean',
        'submitted_to_grand'   => 'boolean',
        'total_point'          => 'float',
    ];
    
    public function ikan()
    {
        return $this->belongsTo(Ikan::class);
    }

    public function juri()
    {
        return $this->belongsTo(User::class, 'juri_id');
    }

    public function grandJuri()
    {
        return $this->belongsTo(User::class, 'grand_juri_id');
    }
}