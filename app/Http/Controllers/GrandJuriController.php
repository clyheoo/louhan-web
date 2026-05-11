<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\GrandJuriEdit;

class GrandJuriController extends Controller
{
    public function index()
    {
        return view('dashboard.grand-juri', ['user' => auth()->user()->fresh()]);
    }

    /* ═══════════════════════════════════════════
       STATS (UPDATE KE TABEL IKANS)
       ═══════════════════════════════════════════ */
    public function getStats()
    {
        // Total Ikan yang sudah mendapat nomor tank
        $totalTank = Ikan::whereNotNull('nomor_tank')->count();
        $totalPeserta = Peserta::count();
        
        // Total Scoring yang sudah masuk (berdasarkan ikan_id)
        $sudahPlot = Scoring::distinct('ikan_id')->count('ikan_id');
        $belumPlot = max(0, $totalTank - $sudahPlot);
        $sisaTank = max(0, 300 - $totalTank);

        // Rincian Per Kategori (Ikan yang sudah dapat tank)
        $rincian = Ikan::whereNotNull('nomor_tank')
            ->selectRaw('ikans.kategori, COUNT(ikans.id) as ekor,
                SUM(CASE WHEN scorings.id IS NULL THEN 1 ELSE 0 END) as belum_tank')
            ->leftJoin('scorings', 'ikans.id', '=', 'scorings.ikan_id') // DIUBAH: Join ke ikans.id
            ->groupBy('ikans.kategori')
            ->orderByDesc('ekor')
            ->get();

        return response()->json([
            'total_tank'    => $totalTank,
            'total_peserta' => $totalPeserta,
            'sudah_plot'    => $sudahPlot,
            'belum_plot'    => $belumPlot,
            'sisa_tank'     => $sisaTank,
            'rincian'       => $rincian,
        ]);
    }

    /* ═══════════════════════════════════════════
       PESERTA LIST (QUERY DARI TABEL IKANS)
       ═══════════════════════════════════════════ */
    public function getPeserta(Request $request)
    {
        // DIUBAH: Query utama dari IKAN
        $query = Ikan::whereNotNull('nomor_tank')
            ->with(['peserta', 'scorings' => function ($q) {
                $q->latest()->limit(1);
            }, 'scorings.juri', 'scorings.grandJuri']);

        if ($request->filled('search')) {
            $query->whereHas('peserta', function ($q) use ($request) {
                $q->where('nama_peserta', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        $data = $query->orderBy('nomor_tank')->get()->map(function ($ikan) {
            $peserta = $ikan->peserta;
            $scoring = $ikan->scorings->first();

            return [
                'id'               => $ikan->id, // Sekarang ini adalah IKAN_ID
                'nama_peserta'     => $peserta->nama_peserta ?? 'Unknown',
                'kategori'         => $ikan->kategori,     // Diambil dari tabel Ikan
                'kelas'            => $scoring ? $scoring->kelas : $ikan->kelas, // Prioritas kelas dari Juri, jika belum ada ambil dari Ikan
                'nomor_tank'       => $ikan->nomor_tank,   // Diambil dari tabel Ikan
                'detail_anggota'   => $peserta->detail_anggota ?? '—',
                'status'           => $scoring 
                                    ? ($scoring->edited_by_grand_juri ? 'Diubah Grand Juri' : 'Sudah Dinilai') 
                                    : 'Belum Dinilai',
                'status_class'     => $scoring 
                                    ? ($scoring->edited_by_grand_juri ? 'badge-warning' : 'badge-success') 
                                    : 'badge-warning',
                'juri_nama'        => $scoring?->juri?->name ?? '—',
                'grand_juri_nama'  => $scoring?->grandJuri?->name ?? null,
                'nilai_detail'     => $scoring?->nilai_detail ?? null,
                'total_nilai'      => $scoring?->total_nilai ?? 0,
            ];
        });

        return response()->json($data);
    }

    /* ═══════════════════════════════════════════
       EDIT NILAI (UPDATE: BERDASARKAN IKAN_ID)
       ═══════════════════════════════════════════ */
    public function editNilai(Request $request)
    {
        $data      = $request->json()->all();
        $ikanId    = $data['ikan_id'] ?? null; // DIUBAH: Terima ikan_id
        $changed   = $data['changed_fields'] ?? null;

        if (!$ikanId || !Ikan::find($ikanId)) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        if (!$changed || !is_array($changed)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perubahan nilai.'], 422);
        }

        /* ── KASUS 1: Sudah ada scoring dari juri ── */
        $existing = Scoring::where('ikan_id', $ikanId)->latest()->first(); // DIUBAH KE ikan_id

        if ($existing && $existing->nilai_detail) {
            // Simpan nilai asli juri saat pertama kali grand juri edit
            $nilaiAsli = null;
            if (!$existing->edited_by_grand_juri) {
                $nilaiAsli = $existing->nilai_detail;
            } elseif ($existing->nilai_detail_asli) {
                $nilaiAsli = $existing->nilai_detail_asli;
            }

            // Gabungkan nilai lama dengan perubahan baru dari Grand Juri
            $finalScores = $existing->nilai_detail;
            foreach ($changed as $kat => $fields) {
                if (!is_array($fields)) continue;
                foreach ($fields as $fieldId => $value) {
                    if (is_numeric($value)) {
                        if (!isset($finalScores[$kat])) $finalScores[$kat] = [];
                        $finalScores[$kat][$fieldId] = (int) $value;
                    }
                }
            }

            // Hitung total dari SELURUH finalScores
            $totalNilai = 0;
            foreach ($finalScores as $katDetail) {
                if (is_array($katDetail)) {
                    foreach ($katDetail as $val) $totalNilai += (int) $val;
                }
            }

            $totalSebelum = $existing->total_nilai ?? 0;

            // Update scoring
            $existing->update([
                'grand_juri_id'        => auth()->id(),
                'nilai_detail'         => $finalScores,
                'nilai_detail_asli'    => $nilaiAsli,
                'total_nilai'          => $totalNilai,
                'edited_by_grand_juri' => true,
                'status'               => 'submitted',
            ]);

            GrandJuriEdit::create([
                'scoring_id'     => $existing->id,
                'peserta_id'     => $existing->peserta_id,
                'grand_juri_id'  => auth()->id(),
                'nilai_sebelum'  => $existing->getRawOriginal('nilai_detail'),
                'nilai_sesudah'  => $finalScores,
                'changed_fields' => $changed,
                'total_sebelum'  => $totalSebelum,
                'total_sesudah'  => $totalNilai,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui oleh Grand Juri!',
                'total'   => $totalNilai,
            ]);
        }

        /* ── KASUS 2: Belum ada scoring sama sekali ── */
        $ikan = Ikan::find($ikanId);
        $totalNilai = 0;
        foreach ($changed as $fields) {
            if (is_array($fields)) {
                foreach ($fields as $val) $totalNilai += (int) $val;
            }
        }

        // DIUBAH: Gunakan ikan_id saat membuat baru, hapus kolom kategori & kelas karena sudah tidak ada di tabel scorings
        $newScoring = Scoring::create([
            'ikan_id'              => $ikanId,
            'juri_id'              => auth()->id(),
            'grand_juri_id'        => auth()->id(),
            'nilai_detail'         => $changed,
            'total_nilai'          => $totalNilai,
            'status'               => 'submitted',
            'edited_by_grand_juri' => true,
        ]);

        GrandJuriEdit::create([
            'scoring_id'     => $newScoring->id,
            'peserta_id'     => $ikan->peserta_id,
            'grand_juri_id'  => auth()->id(),
            'nilai_sebelum'  => null,
            'nilai_sesudah'  => $changed,
            'changed_fields' => $changed,
            'total_sebelum'  => 0,
            'total_sesudah'  => $totalNilai,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nilai baru berhasil disimpan oleh Grand Juri!',
            'total'   => $totalNilai,
        ]);
    }

    /* ═══════════════════════════════════════════
       JURI SUMMARY (UPDATE: BERDASARKAN IKAN_ID)
       ═══════════════════════════════════════════ */
    public function getJuriSummary()
    {
        // DIUBAH: Menggunakan ikan_id
        $juriList = Scoring::with('juri')
            ->whereNotNull('juri_id')
            ->selectRaw('juri_id, COUNT(DISTINCT ikan_id) as total_ikan') // DIUBAH
            ->groupBy('juri_id')
            ->orderByDesc('total_ikan')
            ->get()
            ->map(function ($item) {
                return [
                    'name'         => $item->juri?->name ?? 'Unknown',
                    'role'         => 'juri',
                    'total_peserta' => $item->total_ikan,
                ];
            });

        // Grand Juri yang pernah edit
        $grandList = Scoring::with('grandJuri')
            ->whereNotNull('grand_juri_id')
            ->where('edited_by_grand_juri', true)
            ->selectRaw('grand_juri_id, COUNT(DISTINCT ikan_id) as total_ikan') // DIUBAH
            ->groupBy('grand_juri_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name'         => $item->grandJuri?->name ?? 'Unknown',
                    'role'         => 'grand_juri',
                    'total_peserta' => $item->total_ikan,
                ];
            });

        // Merge, hindari duplikat
        $seen = [];
        $merged = [];
        foreach ($juriList->merge($grandList) as $j) {
            $key = $j['name'] . '_' . $j['role'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $merged[] = $j;
            }
        }

        return response()->json($merged);
    }
}