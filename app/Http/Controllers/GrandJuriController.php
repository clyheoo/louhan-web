<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\GrandJuriEdit;

class GrandJuriController extends Controller
{
    public function index()
    {
        return view('dashboard.grand-juri', ['user' => auth()->user()->fresh()]);
    }

    /* ═══════════════════════════════════════════
       STATS
       ═══════════════════════════════════════════ */
    public function getStats()
    {
        $totalTank    = Peserta::whereNotNull('nomor_tank')->count();
        $totalPeserta = Peserta::count();
        $sudahPlot    = Scoring::distinct('peserta_id')->count('peserta_id');
        $belumPlot    = max(0, $totalTank - $sudahPlot);
        $sisaTank     = max(0, 300 - $totalTank);

        $rincian = Peserta::whereNotNull('nomor_tank')
            ->selectRaw('pesertas.kategori, COUNT(pesertas.id) as ekor,
                SUM(CASE WHEN scorings.id IS NULL THEN 1 ELSE 0 END) as belum_tank')
            ->leftJoin('scorings', 'pesertas.id', '=', 'scorings.peserta_id')
            ->groupBy('pesertas.kategori')
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
       PESERTA LIST
       ═══════════════════════════════════════════ */
    public function getPeserta(Request $request)
    {
        $query = Peserta::whereNotNull('nomor_tank')
            ->with(['scorings' => function ($q) {
                $q->latest()->limit(1);
            }, 'scorings.juri', 'scorings.grandJuri']);

        if ($request->filled('search')) {
            $query->where('nama_peserta', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        $peserta = $query->orderBy('nomor_tank')->get()->map(function ($p) {
            $scoring = $p->scorings->first();

            return [
                'id'               => $p->id,
                'nama_peserta'     => $p->nama_peserta,
                'kategori'         => $p->kategori,
                'kelas'            => $scoring?->kelas ?? '—',
                'nomor_tank'       => $p->nomor_tank,
                'detail_anggota'   => $p->detail_anggota ?? '—',
                'status'           => $scoring?->edited_by_grand_juri
                                        ? 'Diubah Grand Juri'
                                        : ($scoring ? 'Sudah Dinilai' : 'Belum Dinilai'),
                'status_class'     => $scoring?->edited_by_grand_juri
                                        ? 'badge-warning'
                                        : ($scoring ? 'badge-success' : 'badge-warning'),
                'juri_nama'        => $scoring?->juri?->name ?? '—',
                'grand_juri_nama'  => $scoring?->grandJuri?->name ?? null,
                'nilai_detail'     => $scoring?->nilai_detail ?? null,
                'total_nilai'      => $scoring?->total_nilai ?? 0,
            ];
        });

        return response()->json($peserta);
    }

    /* ═══════════════════════════════════════════
       EDIT NILAI — GRAND JURI
       ═══════════════════════════════════════════ */
    public function editNilai(Request $request)
    {
        /* ── Baca payload ── */
        $data      = $request->all();
        $pesertaId = $data['peserta_id'] ?? null;
        $changed   = $data['changed_fields'] ?? null;

        if (!$pesertaId || !Peserta::find($pesertaId)) {
            return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan.'], 422);
        }

        if (!$changed || !is_array($changed)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perubahan nilai.'], 422);
        }

        /* Decode jika masih string */
        if (is_string($changed)) {
            $changed = json_decode($changed, true);
        }

        $existing = Scoring::where('peserta_id', $pesertaId)->latest()->first();

        /* ═══════════════════════════════════════
           KASUS 1: Sudah ada scoring dari juri
           ═══════════════════════════════════════ */
        if ($existing && $existing->nilai_detail) {

            $nilaiSebelum = $existing->nilai_detail; // snapshot sebelum diubah

            /* Simpan nilai_asli dari juri saat pertama kali grand juri edit */
            $nilaiAsli = null;
            if (!$existing->edited_by_grand_juri) {
                $nilaiAsli = $existing->nilai_detail;
            } elseif ($existing->nilai_detail_asli) {
                $nilaiAsli = $existing->nilai_detail_asli;
            }

            /* Gabungkan: lama + perubahan baru */
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

            /* Hitung total dari SELURUH finalScores (bukan hanya yang berubah) */
            $totalNilai = 0;
            foreach ($finalScores as $katDetail) {
                if (is_array($katDetail)) {
                    foreach ($katDetail as $val) {
                        $totalNilai += (int) $val;
                    }
                }
            }

            $totalSebelum = $existing->total_nilai ?? 0;

            /* Update scoring */
            $existing->update([
                'grand_juri_id'        => auth()->id(),
                'nilai_detail'         => $finalScores,
                'nilai_detail_asli'    => $nilaiAsli,
                'total_nilai'          => $totalNilai,
                'edited_by_grand_juri' => true,
                'status'               => 'submitted',
            ]);

            /* Simpan log ke tabel grand_juri_edits */
            GrandJuriEdit::create([
                'scoring_id'     => $existing->id,
                'peserta_id'     => $pesertaId,
                'grand_juri_id'  => auth()->id(),
                'nilai_sebelum'  => $nilaiSebelum,
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

        /* ═══════════════════════════════════════
           KASUS 2: Belum ada scoring sama sekali
           (Grand Juri menginput pertama kali)
           ═══════════════════════════════════════ */
        $peserta = Peserta::find($pesertaId);
        $totalNilai = 0;
        foreach ($changed as $fields) {
            if (is_array($fields)) {
                foreach ($fields as $val) {
                    $totalNilai += (int) $val;
                }
            }
        }

        $newScoring = Scoring::create([
            'peserta_id'           => $pesertaId,
            'juri_id'              => auth()->id(),
            'grand_juri_id'        => auth()->id(),
            'nilai_detail'         => $changed,
            'total_nilai'          => $totalNilai,
            'kategori'             => $peserta->kategori ?? 'FINAL',
            'kelas'                => $peserta->kelas ?? 'A',
            'status'               => 'submitted',
            'edited_by_grand_juri' => true,
        ]);

        GrandJuriEdit::create([
            'scoring_id'     => $newScoring->id,
            'peserta_id'     => $pesertaId,
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
    JURI SUMMARY — Komponen Daftar Juri
    ═══════════════════════════════════════════ */
    public function getJuriSummary()
    {
        $juriList = Scoring::with('juri')
            ->whereNotNull('juri_id')
            ->selectRaw('juri_id, COUNT(DISTINCT peserta_id) as total_peserta')
            ->groupBy('juri_id')
            ->orderByDesc('total_peserta')
            ->get()
            ->map(function ($item) {
                return [
                    'name'         => $item->juri?->name ?? 'Unknown',
                    'role'         => 'juri',
                    'total_peserta' => $item->total_peserta,
                ];
            });

        /* Tambahkan Grand Juri yang pernah edit */
        $grandList = Scoring::with('grandJuri')
            ->whereNotNull('grand_juri_id')
            ->where('edited_by_grand_juri', true)
            ->selectRaw('grand_juri_id, COUNT(DISTINCT peserta_id) as total_peserta')
            ->groupBy('grand_juri_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name'         => $item->grandJuri?->name ?? 'Unknown',
                    'role'         => 'grand_juri',
                    'total_peserta' => $item->total_peserta,
                ];
            });

        /* Merge, hindari duplikat jika grand_juri_id == juri_id */
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