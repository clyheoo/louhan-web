<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Ikan;

class JuriController extends Controller
{
    public function getJuriData()
    {
        // Mengambil data IKAN yang sudah mendapat nomor tank
        $availableTanks = Ikan::whereNotNull('nomor_tank')
            ->with('peserta')
            ->orderBy('nomor_tank')
            ->get();

        $myScores = Scoring::where('juri_id', auth()->id())
            ->with('ikan', 'ikan.peserta')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'available_tanks' => $availableTanks,
            'my_scores' => $myScores
        ]);
    }

    public function simpanNilai(Request $request)
    {
        $data = $request->json()->all();

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

        // Hitung total nilai
        $totalNilai = 0;
        foreach ($allScores as $detailNilai) {
            if (is_array($detailNilai)) {
                foreach ($detailNilai as $nilai) {
                    $totalNilai += (int)$nilai;
                }
            }
        }

        // Cegah duplikat nilai dari juri yang sama untuk ikan yang sama
        $existingScore = Scoring::where('ikan_id', $ikanId)
                        ->where('juri_id', auth()->id())
                        ->first();

        if ($existingScore) {
            return response()->json([
                'success' => false,
                'message' => 'ANDA TIDAK DAPAT MENGUBAH NILAI. Data sudah dikunci karena sudah disubmit.'
            ]);
        }

        Scoring::create([
            'ikan_id'      => $ikanId,
            'juri_id'      => auth()->id(),
            'kelas'        => $kelas, // PASTIKAN INI ADA
            'nilai_detail' => $allScores,
            'total_nilai'  => $totalNilai,
            'status'       => 'submitted'
        ]);

        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan!']);
    }
}