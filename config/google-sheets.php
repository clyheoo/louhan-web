<?php

return [
    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
    'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),

    'sheets' => [
        'peserta'         => 'PESERTA',
        'ploting'         => 'PLOTING TANK',
        'nominasi'        => 'NOMINASI',
        'pil_nom'         => 'PIL NOM',
        'rumus'           => 'RUMUS PENILAIAN',
        'nama_juri'       => 'NAMA JURI',
        'hasil_nominasi'  => 'HASIL NOMINASI',
        'nominasi_fix'    => 'NOMINASI FIX',                
        'nilai_juri'      => 'NILAI JURI',
        'cnt'             => 'CNT',
        'mvp'             => 'MVP',
        'hasil_juri'      => 'HASIL JURI',
    ],
];