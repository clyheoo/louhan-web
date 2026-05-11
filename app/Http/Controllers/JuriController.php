<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Ikan;

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

        // ★ BARU: Ambil semua ikan yang SUDAH DINILAI oleh siapapun
        $allScored = Scoring::with(['juri', 'grandJuri'])
            ->select('ikan_id', 'juri_id', 'grand_juri_id', 'edited_by_grand_juri')
            ->get()
            ->groupBy('ikan_id')
            ->map(function ($items) {
                $first = $items->first();
                $scorerName = 'Juri lain';
                $isGrand = false;

                if ($first->edited_by_grand_juri && $first->grandJuri) {
                    $scorerName = $first->grandJuri->name;
                    $isGrand = true;
                } elseif ($first->juri) {
                    $scorerName = $first->juri->name;
                }

                return [
                    'scorer_name' => $scorerName,
                    'is_grand'    => $isGrand,
                    'is_mine'     => ($first->juri_id === auth()->id()),
                ];
            });

        return response()->json([
            'available_tanks' => $availableTanks,
            'my_scores'       => $myScores,
            'all_scored'      => $allScored,
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

        // ★ DIUBAH: Cek apakah ikan ini SUDAH DINILAI oleh siapapun
        $anyExisting = Scoring::where('ikan_id', $ikanId)->first();

        if ($anyExisting) {
            return response()->json([
                'success' => false,
                'message' => 'PESERTA INI SUDAH DINILAI. Nilai tidak dapat diubah atau diinput ulang.'
            ]);
        }

        Scoring::create([
            'ikan_id'      => $ikanId,
            'juri_id'      => auth()->id(),
            'kelas'        => $kelas,
            'nilai_detail' => $allScores,
            'total_nilai'  => $totalNilai,
            'status'       => 'submitted'
        ]);

        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan!']);
    }
}