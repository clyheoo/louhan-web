<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Ikan;
use App\Helpers\PointCalculator;

class JuriController extends Controller
{
public function getJuriData()
{
    $availableTanks = Ikan::whereNotNull('nomor_tank')
        ->with('peserta')
        ->orderBy('nomor_tank')
        ->get();

    $myScores = Scoring::where('juri_id', auth()->id())
        ->with('ikan', 'ikan.peserta')
        ->orderByDesc('created_at')
        ->get();

    $firstScoringIds = Scoring::select('ikan_id', \DB::raw('MIN(id) as first_id'))
        ->groupBy('ikan_id')
        ->pluck('first_id', 'ikan_id')
        ->toArray();

    // Tandai mana yang first scorer
    $myScores->transform(function ($s) use ($firstScoringIds) {
        $s->is_first_scorer = isset($firstScoringIds[$s->ikan_id]) && $firstScoringIds[$s->ikan_id] == $s->id;
        return $s;
    });

    // Hanya tank yang sudah dinilai JURI INI
    $myScoredTankIds = Scoring::where('juri_id', auth()->id())
        ->pluck('ikan_id')
        ->toArray();

    $allScored = [];
    foreach ($myScoredTankIds as $tankId) {
        $allScored[$tankId] = ['is_mine' => true];
    }

    // Hitung jumlah juri yang sudah menilai per tank
    $scoredCounts = Scoring::select('ikan_id', \DB::raw('COUNT(*) as total_juri'))
        ->groupBy('ikan_id')
        ->pluck('total_juri', 'ikan_id')
        ->toArray();

    return response()->json([
        'available_tanks' => $availableTanks,
        'my_scores'       => $myScores,
        'all_scored'      => $allScored,
        'scored_counts'   => $scoredCounts,
    ]);
}

    public function simpanNilai(Request $request)
    {
        $data      = $request->json()->all();
        $ikanId    = $data['ikan_id'] ?? null;
        $kelas     = $data['kelas'] ?? null;
        $allScores = $data['all_scores'] ?? null;

        if (!$ikanId || !$kelas || !$allScores) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap (Kelas wajib dipilih).'], 422);
        }

        $ikan = Ikan::find($ikanId);
        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        if (is_string($allScores)) {
            $allScores = json_decode($allScores, true);
        }

        if (!is_array($allScores)) {
            return response()->json(['success' => false, 'message' => 'Format all_scores tidak valid.'], 422);
        }

        $totalNilai = 0;
        foreach ($allScores as $detailNilai) {
            if (is_array($detailNilai)) {
                foreach ($detailNilai as $nilai) {
                    $totalNilai += (int)$nilai;
                }
            }
        }

        $myExisting = Scoring::where('ikan_id', $ikanId)
            ->where('juri_id', auth()->id())
            ->first();

        if ($myExisting) {
            return response()->json([
                'success' => false,
                'message' => 'ANDA SUDAH MENILAI peserta ini. Nilai Anda sudah tersimpan dan tidak dapat diubah.'
            ]);
        }

        $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $allScores);

        Scoring::create([
            'ikan_id'      => $ikanId,
            'juri_id'      => auth()->id(),
            'kelas'        => $kelas,
            'nilai_detail' => $allScores,
            'total_nilai'  => $totalNilai,
            'total_point'  => $totalPoint,
            'status'       => 'submitted'
        ]);

        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan!']);
    }

    public function kirimKeGrandJuri(Request $request)
    {
        $scoringId = $request->json('scoring_id');

        if (!$scoringId) {
            return response()->json(['success' => false, 'message' => 'Scoring ID wajib dikirim.'], 422);
        }

        /* Hanya boleh kirim nilai milik sendiri */
        $scoring = Scoring::where('id', $scoringId)
            ->where('juri_id', auth()->id())
            ->first();

        if (!$scoring) {
            return response()->json(['success' => false, 'message' => 'Data penilaian tidak ditemukan.'], 404);
        }

        if ($scoring->submitted_to_grand) {
            return response()->json(['success' => false, 'message' => 'Nilai ini sudah pernah dikirim ke Grand Juri.']);
        }

        $scoring->update(['submitted_to_grand' => true]);

        return response()->json(['success' => true, 'message' => 'Nilai berhasil dikirim ke Grand Juri.']);
    }
}