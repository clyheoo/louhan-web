<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;

class JuriController extends Controller
{
    public function getJuriData()
    {
        $availableTanks = Peserta::whereNotNull('nomor_tank')
            ->orderBy('nomor_tank')
            ->get();

        $myScores = Scoring::where('juri_id', auth()->id())
            ->with('peserta')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'available_tanks' => $availableTanks,
            'my_scores' => $myScores
        ]);
    }

    public function simpanNilai(Request $request)
    {
        // Paksa baca sebagai JSON
        $data = $request->json()->all();

        $pesertaId = $data['peserta_id'] ?? null;
        $kelas     = $data['kelas'] ?? null;
        $allScores = $data['all_scores'] ?? null;

        // Validasi manual
        if (!$pesertaId || !$kelas || !$allScores) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap.'], 422);
        }

        // Pastikan peserta ada
        if (!\App\Models\Peserta::find($pesertaId)) {
            return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan.'], 422);
        }

        // Decode jika masih string
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

        // CEK: Sudah pernah submit untuk tank ini?
        $existingScore = Scoring::where('peserta_id', $pesertaId)
                        ->where('juri_id', auth()->id())
                        ->where('status', 'submitted')
                        ->first();

        if ($existingScore) {
            return response()->json([
                'success' => false,
                'message' => 'ANDA TIDAK DAPAT MENGUBAH NILAI. Data sudah dikunci karena sudah disubmit.'
            ]);
        }

        Scoring::create([
            'peserta_id'   => $pesertaId,
            'juri_id'      => auth()->id(),
            'kategori'     => 'FINAL',
            'kelas'        => $kelas,
            'nilai_detail' => $allScores,
            'total_nilai'  => $totalNilai,
            'status'       => 'submitted'
        ]);

        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan!']);
    }
}