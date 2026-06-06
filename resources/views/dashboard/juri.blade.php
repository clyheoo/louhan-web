@extends('layouts.juri')

@section('content')
<div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 0: LOADING
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-loading" class="lg:col-span-12 flex flex-col items-center justify-center py-32">
        <div class="w-12 h-12 border-4 rounded-full animate-spin mb-4" style="border-color:var(--glass-strong);border-top-color:var(--cyan-400);"></div>
        <p class="text-sm font-bold" style="color:var(--text-mid);">Memeriksa status nominasi...</p>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 1: HALAMAN NOMINASI
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-page" class="hidden lg:col-span-12">

        {{-- Notifikasi Ditolak --}}
        <div id="nom-rejected-notice" class="hidden mb-4 p-4 rounded-xl" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">
            <div class="flex items-start gap-3">
                <i class="fas fa-circle-xmark mt-0.5" style="color:var(--danger);"></i>
                <div class="flex-1">
                    <h4 class="text-sm font-bold" style="color:#FECACA;">Beberapa Nominasi Ditolak</h4>
                    <ul id="nom-rejected-list" class="mt-2 space-y-1 text-xs" style="color:#FCA5A5;"></ul>
                    <p class="mt-2 text-xs font-semibold" style="color:#FCA5A5;">Silakan pilih ulang tank yang ingin dinominasikan.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">

            {{-- SIDEBAR FILTER --}}
            <div class="lg:col-span-4 xl:col-span-3">
                <div class="glass-card lg:sticky lg:top-24">
                    <div class="px-4 py-3" style="border-bottom:1px solid var(--bd-1);">
                        <h2 class="font-bold flex items-center gap-2 text-sm" style="color:var(--text-hi);">
                            <i class="fas fa-filter" style="color:var(--cyan-400);"></i>
                            Filter Tank
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <input type="text" id="nom-search" placeholder="Cari no tank..." class="w-full px-3 py-2.5 rounded-lg text-xs font-semibold outline-none" style="border:1px solid var(--bd-2);background:var(--glass-2);color:var(--text-hi);">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase mb-2" style="color:var(--text);">Kategori</label>
                            <div id="nom-kategori-btns" class="flex flex-wrap gap-1.5"></div>
                        </div>
                        <div id="nom-kelas-wrap">
                            <label class="block text-[11px] font-bold uppercase mb-2" style="color:var(--text);">Kelas</label>
                            <div id="nom-kelas-btns" class="flex flex-wrap gap-1.5"></div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3" style="background:rgba(255,255,255,0.03);border-top:1px solid var(--bd-1);">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold" style="color:var(--text);">Tank Terpilih</span>
                            <span id="nom-count-badge" class="px-2.5 py-1 rounded-lg text-xs font-black" style="background:rgba(34,211,238,0.12);color:var(--cyan-300);">0</span>
                        </div>
                        <button id="nom-btn-submit" onclick="nomSubmit()" disabled class="w-full py-3 rounded-xl font-bold text-sm text-white cursor-not-allowed transition-all flex items-center justify-center gap-2" style="background:var(--glass-strong);">
                            <i class="fas fa-paper-plane"></i> Kirim Nominasi
                        </button>
                    </div>
                </div>
            </div>

            {{-- GRID TANK --}}
            <div class="lg:col-span-8 xl:col-span-9">
                <div class="glass-card p-4 mb-4 flex items-center justify-between">
                    <h2 class="font-bold flex items-center gap-2 text-sm" style="color:var(--text-hi);">
                        <i class="fas fa-award" style="color:var(--cyan-400);"></i>
                        Pilih Tank untuk Dinominasikan
                    </h2>
                    <button onclick="nomLoadData()" class="px-3 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">
                        <i id="nom-refresh-icon" class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div id="nom-filter-info" class="hidden mb-4 px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2" style="background:rgba(34,211,238,0.08);border:1px solid rgba(34,211,238,0.22);color:var(--cyan-300);">
                    <i class="fas fa-info-circle" style="color:var(--cyan-400);opacity:0.6;"></i>
                    <span id="nom-filter-info-text">-</span>
                </div>
                <div id="nom-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3"></div>
                <div id="nom-grid-empty" class="hidden text-center py-16 glass-card">
                    <i class="fas fa-database text-4xl mb-3" style="color:var(--text-faint);"></i>
                    <p class="text-xs font-bold" style="color:var(--text-low);">Tidak ada tank di filter ini</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 2: HALAMAN MENUNGGU
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-waiting" class="hidden lg:col-span-12">
        <div class="glass-card p-8 md:p-12 text-center max-w-lg mx-auto">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" style="background:rgba(245,158,11,0.10);border:1px solid rgba(245,158,11,0.25);">
                <i class="fas fa-hourglass-half text-3xl animate-pulse" style="color:var(--gold-400);"></i>
            </div>
            <h2 class="text-xl font-extrabold mb-2" style="color:var(--text-hi);">Nominasi Sedang Ditinjau</h2>
            <p class="text-sm mb-6" style="color:var(--text-mid);">Grand Juri sedang memeriksa pilihan Anda. Halaman akan otomatis diperbarui.</p>
            <div id="nom-waiting-list" class="text-left rounded-xl p-4 border mb-6 max-h-60 overflow-y-auto custom-scrollbar" style="background:rgba(255,255,255,0.03);border-color:var(--bd-1);"></div>
            <div class="flex items-center justify-center gap-2 text-xs" style="color:var(--text-faint);">
                <div class="w-2 h-2 rounded-full animate-pulse" style="background:var(--cyan-500);"></div>
                Auto-refresh setiap 5
                 detik
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 3: ANIMASI APPROVAL
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-approved-anim" class="hidden fixed inset-0 z-[9998] flex items-center justify-center" style="background:linear-gradient(135deg, var(--ocean-950) 0%, var(--ocean-900) 50%, var(--ocean-850) 100%);">
        <div class="text-center fade-in">
            <div class="w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl" style="background:linear-gradient(135deg,#10B981,#059669);box-shadow:0 0 60px rgba(16,185,129,0.4);animation:popIn 0.5s cubic-bezier(0.16,1,0.3,1) both;">
                <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path class="check-draw" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold mb-2" style="color:var(--text-hi);animation:fadeUp 0.5s 0.4s ease both;">Nominasi Disetujui!</h2>
            <p class="text-sm" style="color:var(--text-mid);animation:fadeUp 0.5s 0.6s ease both;">Mempersiapkan halaman penilaian...</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 4: HALAMAN PENILAIAN
         ════════════════════════════════════════════════════════════ --}}
    <div id="scoring-page" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">

            {{-- ── KOLOM KIRI: FORM BATCH ────────────────────── --}}
            <div class="lg:col-span-5 flex flex-col gap-3">
            <div class="glass-card lg:sticky lg:top-24 flex flex-col">

                {{-- Header Form --}}
                <div class="px-4 py-3 space-y-3" style="border-bottom:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
                    <div class="flex justify-between items-center">
                        <h2 class="font-bold flex items-center gap-2 text-sm md:text-base" style="color:var(--text-hi);">
                            <svg class="w-4 h-4" style="color:var(--cyan-400);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Form Penilaian
                        </h2>
                    </div>

                    {{-- Filter + Kelas --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold uppercase mb-1" style="color:var(--text);">Kategori</label>
                            <select id="filter-kategori" onchange="onFilterChange()" class="w-full px-2 py-2 rounded-md outline-none text-xs font-semibold" style="border:1px solid var(--bd-2);background:var(--glass-2);color:var(--text-hi);"></select>
                        </div>
                        <div id="scoring-kelas-wrap">
                            <label class="block text-[11px] font-bold uppercase mb-1" style="color:var(--text);">Kelas</label>
                            <select id="filter-kelas" onchange="onFilterChange()"class="w-full px-2 py-2 rounded-md font-bold text-center outline-none text-xs" style="border:1px solid var(--bd-2);background:var(--glass-2);color:var(--text-hi);">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase mb-1" style="color:var(--text);">Nama Juri</label>
                            <input type="text" value="{{ Auth::user()->name }}" disabled class="w-full px-2 py-2 rounded-md text-xs font-semibold cursor-not-allowed" style="border:1px solid var(--bd-1);background:rgba(255,255,255,0.02);color:var(--text-low);">
                        </div>
                    </div>

                    {{-- Info Counter --}}
                    <div id="filter-info" class="hidden px-3 py-1.5 rounded-md text-[11px] font-semibold flex justify-between items-center" style="background:rgba(34,211,238,0.08);border:1px solid rgba(34,211,238,0.22);color:var(--cyan-300);">
                        <span>Tersisa: <b id="filter-remaining">0</b></span>
                        <span style="color:rgba(34,211,238,0.3);">|</span>
                        <span>Sudah Nilai: <b id="filter-scored">0</b></span>
                    </div>
                </div>

                {{-- Tab Kriteria + Pedoman --}}
                <div class="p-2 flex flex-col gap-2" style="background:rgba(255,255,255,0.04);border-bottom:1px solid var(--bd-1);">
                    <div class="flex justify-between items-center px-1">
                        <span class="text-[11px] font-black uppercase tracking-widest" style="color:var(--text-low);">Kriteria:</span>
                        <button type="button" onclick="toggleGuideline()" id="btn-guideline" class="flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold transition shadow-sm" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Pedoman
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1 px-1" id="tab-buttons"></div>
                </div>

                {{-- Panel Pedoman --}}
                <div id="guideline-panel" class="hidden px-4 py-2.5 slide-down" style="background:rgba(245,158,11,0.06);border-bottom:1px solid rgba(245,158,11,0.15);">
                    <h4 class="text-[11px] font-bold mb-1.5" id="guideline-title" style="color:var(--gold-300);">-</h4>
                    <ul class="space-y-0.5" id="guideline-points"></ul>
                </div>

                {{-- TABEL FORM --}}
                <div class="overflow-auto flex-1 custom-scrollbar" style="max-height:460px;background:rgba(255,255,255,0.02);">
                    <table class="w-full text-xs text-left min-w-[max-content]">
                        <thead style="background:rgba(255,255,255,0.10);color:var(--text);font-weight:bold;" class="sticky top-0 z-20 shadow-sm">
                            <tr id="form-thead"></tr>
                        </thead>
                        <tbody id="form-tbody" style="border-top-color:var(--bd-1);"></tbody>
                    </table>
                    <div id="form-empty" class="hidden text-center py-12">
                        <svg class="w-8 h-8 mx-auto mb-2" style="color:var(--text-faint);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-xs font-bold" style="color:var(--text-low);">Semua tank sudah dinilai atau tidak ada data.</p>
                    </div>
                </div>

                {{-- Footer Submit --}}
                <div class="p-3 space-y-3" style="background:rgba(255,255,255,0.03);border-top:1px solid var(--bd-1);">
                    <div id="confirm-check" onclick="toggleConfirm()" class="flex items-center gap-2.5 p-2.5 rounded-lg transition cursor-pointer" style="background:rgba(255,255,255,0.04);border:1px solid var(--bd-2);opacity:0.5;">
                        <div id="confirm-icon" class="w-5 h-5 flex-shrink-0 rounded flex items-center justify-center transition-colors" style="background:rgba(255,255,255,0.06);border:1px solid var(--gold-400);"></div>
                        <label class="text-[10px] font-bold cursor-pointer select-none leading-snug" style="color:var(--gold-300);">Saya menyatakan data siap disimpan.</label>
                    </div>
                    <button id="btn-batch-submit" onclick="batchSubmit()" disabled class="w-full font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm cursor-not-allowed" style="background:var(--glass-strong);color:var(--text-faint);">
                        <svg class="w-5 h-5" style="color:var(--text-faint);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        SIMPAN NILAI
                    </button>
                </div>
            </div>
        </div>

        {{-- ── KOLOM KANAN: LIVE DATA ─────────────────────── --}}
        <div class="lg:col-span-7 flex flex-col">
            <div class="glass-card flex flex-col" style="height:500px;">
                <div class="px-4 py-3 flex items-center gap-3" style="border-bottom:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
                    <h2 class="font-bold flex items-center gap-2 text-sm md:text-base" style="color:var(--text-hi);">
                        <svg class="w-4 h-4 md:w-5 md:h-5" style="color:var(--cyan-400);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Data Penilaian Saya
                    </h2>
                    <span class="text-[12px] font-bold px-2 py-0.5 rounded-full" id="live-count" style="background:var(--glass-3);color:var(--text);">0 </span>
                </div>
                <div class="overflow-auto flex-1 custom-scrollbar">
                    <table class="w-full text-[10px] md:text-xs text-left whitespace-nowrap">
                        <thead class="sticky top-0 z-20 shadow-sm" style="background:rgba(255,255,255,0.10);color:var(--text);font-weight:bold;border-bottom:1px solid var(--bd-1);">
                            <tr>
                                <th class="px-2 py-2.5 sticky left-0 z-30 w-12 text-center" style="background:rgba(255,255,255,0.10);border-right:1px solid var(--bd-1);">Tank</th>
                                <th class="px-2 py-2.5 w-16" style="border-right:1px solid var(--bd-1);">Kelas</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);background:rgba(34,211,238,0.06);">Overall</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Head</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Face</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Body</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Marking</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Pearl</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Color</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Finnage</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Defect</th>
                                <th class="px-2 py-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="live-body"></tbody>
                    </table>
                    <div id="live-empty" class="hidden text-center py-16">
                        <p class="text-xs font-bold" style="color:var(--text-low);">Belum ada data nilai.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('modals')
{{-- MODAL PREVIEW NOMINASI --}}
<div id="nom-preview-modal" class="hidden fixed inset-0 z-[260] flex items-center justify-center p-4" style="background:rgba(2,6,14,0.88);backdrop-filter:blur(8px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md max-h-[85vh] flex flex-col fade-in" style="background:linear-gradient(180deg,var(--ocean-800),var(--ocean-900));border:1px solid var(--bd-2);">
        <div class="p-5" style="border-bottom:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <h3 class="text-lg font-bold flex items-center gap-2" style="color:var(--text-hi);">
                <i class="fas fa-clipboard-check" style="color:var(--cyan-400);"></i> Konfirmasi Nominasi
            </h3>
            <p class="text-xs mt-1" style="color:var(--text-mid);">Pastikan pilihan Anda sudah benar</p>
        </div>
        <div class="p-5 overflow-y-auto flex-1 custom-scrollbar">
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold" style="color:var(--text);">Tank Terpilih</span>
                <span id="nom-preview-count" class="px-2.5 py-1 rounded-lg text-xs font-black" style="background:rgba(34,211,238,0.12);color:var(--cyan-300);">0</span>
            </div>
            <div id="nom-preview-list" class="space-y-2"></div>
        </div>
        <div class="p-5 grid grid-cols-2 gap-3" style="border-top:1px solid var(--bd-1);">
            <button onclick="nomClosePreview()" class="py-3 rounded-xl font-bold text-xs transition-colors" style="color:var(--text-mid);background:var(--glass-2);border:1px solid var(--bd-2);">Ubah Pilihan</button>
            <button id="nom-btn-confirm" onclick="nomConfirmSubmit()" class="py-3 rounded-xl font-bold text-xs text-white flex items-center justify-center gap-2" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));box-shadow:0 6px 16px -6px rgba(6,182,212,0.5),inset 0 1px 0 rgba(255,255,255,0.18);">
                <i class="fas fa-paper-plane"></i> Kirim Fix
            </button>
        </div>
    </div>
</div>

{{-- MODAL DEFECT --}}
<div id="modal-defect" class="hidden fixed inset-0 z-[260] flex items-center justify-center p-4" style="background:rgba(2,6,14,0.88);backdrop-filter:blur(8px);">
    <div class="rounded-2xl shadow-2xl p-6 w-full max-w-sm max-h-[85vh] flex flex-col fade-in" style="background:linear-gradient(180deg,var(--ocean-800),var(--ocean-900));border:1px solid var(--bd-2);">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2" style="color:var(--text-hi);border-bottom:1px solid var(--bd-1);padding-bottom:12px;">
            <svg class="w-5 h-5" style="color:var(--danger);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Pilih Defect (<span id="defect-part-label">-</span>)
        </h3>
        <div class="overflow-y-auto flex-1 mb-4 space-y-4 custom-scrollbar pr-1" id="defect-modal-body"></div>
        <button onclick="saveDefect()" class="w-full py-3.5 text-white font-bold rounded-xl transition-transform active:scale-95" style="background:linear-gradient(135deg,var(--ocean-600),var(--ocean-700));box-shadow:0 6px 16px -6px rgba(0,0,0,0.5),inset 0 1px 0 rgba(255,255,255,0.1);">Selesai & Simpan</button>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @keyframes popIn { 0%{transform:scale(0) rotate(-10deg);opacity:0} 60%{transform:scale(1.1) rotate(2deg);opacity:1} 100%{transform:scale(1) rotate(0deg);opacity:1} }
    @keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
    .check-draw { stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.5s 0.6s ease-out forwards; }
    @keyframes drawCheck { to { stroke-dashoffset: 0; } }

    /* ── FORM TABLE ROW HOVER ── */
    #form-tbody tr { transition: background 0.15s ease; border-bottom: 1px solid var(--bd-1); }
    #form-tbody tr:hover { background: rgba(34,211,238,0.05) !important; }
    #form-tbody tr td { padding: 6px; }
    #form-tbody tr td:first-child { border-right: 1px solid var(--bd-1); }
    #form-thead th { padding: 10px 8px; border-bottom: 1px solid var(--bd-1); }
    #form-thead th:first-child { border-right: 1px solid var(--bd-1); }

    /* ── LIVE TABLE ROW HOVER ── */
    #live-body tr { transition: background 0.15s ease; border-bottom: 1px solid var(--bd-1); }
    #live-body tr:hover { background: rgba(245,158,11,0.05) !important; }
    #live-body td { padding: 8px; }
    #live-body td:first-child { background: rgba(255,255,255,0.04); border-right: 1px solid var(--bd-1); }

    /* ── NOMINATION GRID CARD ── */
    #nom-grid > div {
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.015) 100%);
        border: 1px solid var(--bd-2);
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
    }
    #nom-grid > div:hover {
        border-color: rgba(34,211,238,0.22);
        box-shadow: 0 10px 25px -10px rgba(6,182,212,0.20);
        background: linear-gradient(135deg, rgba(34,211,238,0.06) 0%, rgba(255,255,255,0.02) 100%);
    }
    #nom-grid > div.selected-card {
        background: linear-gradient(135deg, rgba(34,211,238,0.10) 0%, rgba(37,99,235,0.06) 100%) !important;
        border-color: var(--cyan-400) !important;
        box-shadow: 0 0 0 1px var(--cyan-400), 0 10px 25px -10px rgba(6,182,212,0.30) !important;
    }
    #nom-grid .tank-num-badge {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 800;
        background: linear-gradient(135deg, var(--ocean-600), var(--ocean-700));
        color: var(--text-hi);
        box-shadow: 0 4px 12px -4px rgba(0,0,0,0.5);
        transition: all 0.3s;
    }
    #nom-grid .selected-card .tank-num-badge {
        background: linear-gradient(135deg, var(--royal-600), var(--cyan-500));
        box-shadow: 0 4px 16px -4px rgba(6,182,212,0.5);
    }
    #nom-grid .cat-badge, #nom-grid .kelas-badge {
        font-size: 10px; font-weight: 700; text-align: center;
        padding: 4px 8px; border-radius: 8px; display: block;
    }
    #nom-grid .cat-badge { background: rgba(34,211,238,0.08); color: var(--cyan-300); border: 1px solid rgba(34,211,238,0.15); }
    #nom-grid .kelas-badge { background: rgba(16,185,129,0.08); color: #6EE7B7; border: 1px solid rgba(16,185,129,0.15); margin-top: 4px; }
    #nom-grid .star-btn {
        padding: 6px; border-radius: 10px; transition: all 0.25s;
        background: transparent; border: none; cursor: pointer; font-size: 14px;
        color: var(--text-faint);
    }
    #nom-grid .star-btn:hover { color: var(--gold-400); background: rgba(245,158,11,0.10); }
    #nom-grid .selected-card .star-btn { color: var(--gold-400); background: rgba(245,158,11,0.12); }

    /* ── WAITING LIST ITEMS ── */
    #nom-waiting-list > div > div {
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--bd-1);
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 6px;
    }
    #nom-waiting-list .wait-tank-num {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800;
        background: linear-gradient(135deg, var(--ocean-600), var(--ocean-700));
        color: var(--text-hi);
        flex-shrink: 0;
    }
    #nom-waiting-list .wait-pending {
        font-size: 11px; font-weight: 700; padding: 3px 8px;
        border-radius: 999px; margin-left: auto;
        background: rgba(245,158,11,0.10); color: var(--gold-300);
        border: 1px solid rgba(245,158,11,0.25);
    }

    /* ── PREVIEW LIST ITEMS ── */
    #nom-preview-list > div {
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--bd-1);
        border-radius: 12px;
        padding: 12px;
    }
    #nom-preview-list .prev-tank-num {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800;
        background: linear-gradient(135deg, var(--ocean-600), var(--ocean-700));
        color: var(--text-hi);
        flex-shrink: 0;
    }

    /* ── DEFECT MODAL ITEMS ── */
    #defect-modal-body > div {
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--bd-1);
        border-radius: 12px;
        padding: 12px;
    }
    #defect-modal-body .defect-group-title {
        font-size: 11px; font-weight: 800; color: var(--text-faint);
        text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 10px;
    }
    #defect-modal-body label {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 8px 10px; border-radius: 10px; cursor: pointer;
        transition: all 0.2s; border: 1px solid transparent;
        font-size: 13px; font-weight: 600; color: var(--text);
        margin-bottom: 4px;
    }
    #defect-modal-body label:hover { background: var(--glass-3); }
    #defect-modal-body label.checked {
        background: rgba(34,211,238,0.08);
        border-color: rgba(34,211,238,0.22);
    }

    /* ── DEFECT BUTTONS IN TABLE ── */
    .defect-btn-aman {
        width: 100%; padding: 6px 4px; border: 1px solid var(--bd-2); border-radius: 8px;
        text-align: center; font-weight: 700; font-size: 10px;
        background: var(--glass-2); color: var(--text-mid);
        cursor: pointer; transition: all 0.2s;
    }
    .defect-btn-aman:hover { border-color: rgba(34,211,238,0.30); }
    .defect-btn-10 {
        width: 100%; padding: 6px 4px; border: 1px solid rgba(249,115,22,0.35); border-radius: 8px;
        text-align: center; font-weight: 700; font-size: 10px;
        background: rgba(249,115,22,0.10); color: #FDBA74;
        cursor: pointer; transition: all 0.2s;
    }
    .defect-btn-10:hover { background: rgba(249,115,22,0.18); }
    .defect-btn-30 {
        width: 100%; padding: 6px 4px; border: 1px solid rgba(239,68,68,0.35); border-radius: 8px;
        text-align: center; font-weight: 700; font-size: 10px;
        background: rgba(239,68,68,0.12); color: #FCA5A5;
        cursor: pointer; transition: all 0.2s;
    }
    .defect-btn-30:hover { background: rgba(239,68,68,0.20); }

    /* ── LIVE TABLE DEFECT TAGS ── */
    .live-defect-tag {
        display: inline-block; font-size: 8px; font-weight: 700;
        padding: 1px 5px; border-radius: 4px; margin-bottom: 2px;
        background: rgba(239,68,68,0.10); color: #FCA5A5;
    }

    /* ── LIVE DETAIL BUTTON ── */
    .live-detail-btn {
        display: flex; align-items: center; gap: 4px; margin: 0 auto;
        padding: 4px 8px; border-radius: 6px;
        background: rgba(255,255,255,0.06); border: 1px solid var(--bd-2);
        color: var(--text-mid); font-size: 9px; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
    }
    .live-detail-btn:hover { background: var(--glass-3); border-color: var(--bd-3); color: var(--text-hi); }

    /* ── GUIDELINE POINTS ── */
    #guideline-points li {
        display: flex; align-items: flex-start; gap: 6px;
        font-size: 11px; color: var(--text); line-height: 1.55;
    }
    #guideline-points li::before { content: '•'; color: var(--gold-400); margin-top: 1px; flex-shrink: 0; }

    /* ── TAB BUTTONS ── */
    #tab-buttons button { transition: all 0.2s ease; }
    #tab-buttons button:hover { opacity: 0.9; }

    /* ── RESPONSIVE SCORING PAGE ── */
    @media (max-width: 1024px) {
        #scoring-page > div > div:last-child .glass-card { height: auto !important; max-height: 60vh; }
    }
    @media (max-width: 640px) {
        #nom-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
        #nom-grid .tank-num-badge { width: 44px; height: 44px; font-size: 1.15rem; border-radius: 11px; }
        #nom-grid > div { padding: 10px !important; }
        #form-thead th, #form-tbody td { padding: 6px 4px !important; font-size: 11px !important; }
        #live-body td { padding: 6px 4px !important; }
    }
        /* ── DETAIL POPUP HIDDEN SCROLLBAR ── */
    .detail-scroll-hidden::-webkit-scrollbar { display: none; }
    .detail-scroll-hidden { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<script>

var NO_KELAS_KATEGORI = ['Bonsai', 'Jumbo'];
var authUserId = {{ Auth::id() }};
function isNoKelas(kat) { return NO_KELAS_KATEGORI.indexOf(kat) !== -1; }
function kelasLabel(kelas) { return kelas ? 'Kelas ' + kelas : ''; }
function kelasDisplay(kategori, kelas) {
    if (isNoKelas(kategori) || !kelas) return '';
    return '<div class="kelas-badge">Kelas ' + kelas + '</div>';
}

// ═══════════════════════════════════════════════════════════════
// NOMINASI STATE & LOGIC
// ═══════════════════════════════════════════════════════════════
let nomState = {
    tanks: [],
    selected: new Set(),
    kategoris: [],
    kelass: [],
    filterKat: '',
    filterKelas: '',
    searchTerm: '',
    autoRefreshTimer: null,
};

function nomShow(id) { document.getElementById(id)?.classList.remove('hidden'); }
function nomHide(id) { document.getElementById(id)?.classList.add('hidden'); }

async function checkNominasiStatus() {
    try {
        const res = await apiFetch('/api/juri/nominasi-status');
        const status = res.status;
        nomHide('nom-loading');
        if (status === 'approved') {
            if (sessionStorage.getItem('nom_anim_done')) {
                nomHide('nom-loading'); nomHide('nom-page'); nomHide('nom-waiting');
                nomShow('scoring-page'); initScoringPage();
            } else {
                sessionStorage.setItem('nom_anim_done', '1');
                nomShowApprovedAnim();
            }
        } else if (status === 'pending') {
            nomShowWaiting(res.nominations);
        } else {
            nomShowNominasiPage(res.nominations);
        }
    } catch (e) {
        nomHide('nom-loading');
        showWarningModal([{type:'select', msg:'Gagal memeriksa status nominasi. Periksa koneksi internet Anda.'}]);
    }
}

function nomShowNominasiPage(rejectedNoms) {
    nomHide('nom-waiting'); nomHide('scoring-page'); nomShow('nom-page');
    if (rejectedNoms && rejectedNoms.length > 0) {
        const rejected = rejectedNoms.filter(n => n.status === 'rejected');
        if (rejected.length > 0) {
            const list = document.getElementById('nom-rejected-list');
            list.innerHTML = rejected.map(n =>
                '<li class="flex items-start gap-2"><span class="font-bold">No Tank: ' + n.nomor_tank + '</span> <span>(' + n.kategori + ', Kelas ' + n.kelas + ')</span>' + (n.catatan ? ': <em>"' + n.catatan + '"</em>' : '') + '</li>'
            ).join('');
            nomShow('nom-rejected-notice');
        }
    }
    nomLoadData();
}

function nomShowWaiting(nominations) {
    nomHide('nom-page'); nomHide('scoring-page'); nomShow('nom-waiting');
    const list = document.getElementById('nom-waiting-list');
    list.innerHTML = nominations.map(n =>
        '<div class="flex items-center gap-3">' +
        '<div class="wait-tank-num">' + n.nomor_tank + '</div>' +
        '<div style="flex:1;min-width:0;">' +
        '<div style="font-size:12px;font-weight:700;color:var(--text-hi);">' + n.nama_peserta + '</div>' +
        '<div style="font-size:10px;color:var(--text-mid);">' + n.kategori + (n.kelas ? ' — Kelas ' + n.kelas : '') + '</div></div>' +
        '<span class="wait-pending">Pending</span></div>'
    ).join('');
    if (nomState.autoRefreshTimer) clearInterval(nomState.autoRefreshTimer);
    nomState.autoRefreshTimer = setInterval(checkNominasiStatus, 5000);
}

function nomShowApprovedAnim() {
    if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }
    nomHide('nom-page'); nomHide('nom-waiting'); nomHide('scoring-page'); nomShow('nom-approved-anim');
    setTimeout(function() { nomHide('nom-approved-anim'); nomShow('scoring-page'); initScoringPage(); }, 3000);
}

async function nomLoadData() {
    var icon = document.getElementById('nom-refresh-icon');
    if (icon) icon.classList.add('animate-spin');
    try {
        const res = await apiFetch('/api/juri/tanks-nominasi');
        nomState.tanks = res.tanks || [];
        nomState.kategoris = res.kategoris || [];
        nomState.kelass = res.kelass || [];
        nomRenderFilterBtns();
        nomRenderGrid();
    } catch (e) { showWarningModal([{type:'select', msg:'Gagal memuat data tank.'}]); }
    if (icon) icon.classList.remove('animate-spin');
}

function nomRenderFilterBtns() {
    const katDiv = document.getElementById('nom-kategori-btns');
    const kelDiv = document.getElementById('nom-kelas-btns');
    katDiv.innerHTML = '<button onclick="nomSetKat(\'\')" class="nom-kat-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;border-color:transparent;box-shadow:0 2px 8px -4px rgba(6,182,212,0.4);">Semua</button>' +
        nomState.kategoris.map(k => '<button onclick="nomSetKat(\'' + k + '\')" class="nom-kat-btn px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors" style="background:var(--glass-2);color:var(--text);border-color:var(--bd-2);">' + k + '</button>').join('');
    kelDiv.innerHTML = '<button onclick="nomSetKelas(\'\')" class="nom-kel-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;border-color:transparent;box-shadow:0 2px 8px -4px rgba(6,182,212,0.4);">Semua</button>' +
        nomState.kelass.map(k => '<button onclick="nomSetKelas(\'' + k + '\')" class="nom-kel-btn px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition-colors" style="background:var(--glass-2);color:var(--text);border-color:var(--bd-2);">' + k + '</button>').join('');
}

function nomSetKat(val) {
    nomState.filterKat = val; nomState.filterKelas = '';
    var kelasWrap = document.getElementById('nom-kelas-wrap');
    if(kelasWrap) kelasWrap.style.display = isNoKelas(val) ? 'none' : '';
    document.querySelectorAll('.nom-kat-btn').forEach(function(b) {
        if (b.textContent.trim() === (val || 'Semua')) {
            b.style.cssText = 'background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;border-color:transparent;box-shadow:0 2px 8px -4px rgba(6,182,212,0.4);';
        } else {
            b.style.cssText = 'background:var(--glass-2);color:var(--text);border-color:var(--bd-2);';
        }
    });
    nomUpdateFilterInfo(); nomRenderGrid();
}

function nomSetKelas(val) {
    nomState.filterKelas = val;
    document.querySelectorAll('.nom-kel-btn').forEach(function(b) {
        if (b.textContent.trim() === (val || 'Semua')) {
            b.style.cssText = 'background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;border-color:transparent;box-shadow:0 2px 8px -4px rgba(6,182,212,0.4);';
        } else {
            b.style.cssText = 'background:var(--glass-2);color:var(--text);border-color:var(--bd-2);';
        }
    });
    nomRenderGrid();
}

function nomGetFiltered() {
    return nomState.tanks.filter(function(t) {
        if (nomState.filterKat && t.kategori !== nomState.filterKat) return false;
        if (nomState.filterKelas && t.kelas !== nomState.filterKelas) return false;
        if (nomState.searchTerm) {
            const s = nomState.searchTerm.toLowerCase();
            if (!String(t.nomor_tank).includes(s) && !t.kategori.toLowerCase().includes(s) && !t.kelas.toLowerCase().includes(s)) return false;
        }
        return true;
    });
}

function nomRenderGrid() {
    const filtered = nomGetFiltered();
    const grid = document.getElementById('nom-grid');
    const empty = document.getElementById('nom-grid-empty');
    if (filtered.length === 0) { grid.innerHTML = ''; nomShow('nom-grid-empty'); return; }
    nomHide('nom-grid-empty');

    grid.innerHTML = filtered.map(function(t) {
        const sel = nomState.selected.has(t.id);
        return '<div class="p-3 cursor-pointer ' + (sel ? 'selected-card' : '') + '" onclick="nomToggle(' + t.id + ')">' +
            '<div class="flex justify-between items-start mb-3">' +
            '<div class="tank-num-badge">' + t.nomor_tank + '</div>' +
            '<button class="star-btn" onclick="event.stopPropagation();nomToggle(' + t.id + ')">' +
            '<i class="' + (sel ? 'fas' : 'fa-regular') + ' fa-star"></i></button></div>' +
            '<div class="flex flex-col gap-1">' +
            '<div class="cat-badge">' + t.kategori + '</div>' +
            kelasDisplay(t.kategori, t.kelas) +
            '</div></div>';
    }).join('');
}

function nomUpdateFilterInfo() {
    const el = document.getElementById('nom-filter-info');
    const txt = document.getElementById('nom-filter-info-text');
    const parts = [];
    if (nomState.filterKat) parts.push('Kategori: <b>' + nomState.filterKat + '</b>');
    if (nomState.filterKelas && !isNoKelas(nomState.filterKat)) parts.push('Kelas: <b>' + nomState.filterKelas + '</b>');
    if (nomState.searchTerm) parts.push('Cari: <b>"' + nomState.searchTerm + '"</b>');
    if (parts.length > 0) {
        txt.innerHTML = 'Menampilkan filter — ' + parts.join(' <span style="color:rgba(34,211,238,0.3);margin:0 4px;">|</span> ');
        el.classList.remove('hidden');
    } else { el.classList.add('hidden'); }
}

function nomToggle(id) {
    if (nomState.selected.has(id)) { nomState.selected.delete(id); }
    else { nomState.selected.add(id); }
    nomUpdateCount(); nomRenderGrid();
}

function nomUpdateCount() {
    const c = nomState.selected.size;
    document.getElementById('nom-count-badge').textContent = c;
    const btn = document.getElementById('nom-btn-submit');
    if (c > 0) {
        btn.disabled = false;
        btn.style.cssText = 'background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;box-shadow:0 6px 16px -6px rgba(6,182,212,0.5),inset 0 1px 0 rgba(255,255,255,0.18);cursor:pointer;';
        btn.className = 'w-full py-3 rounded-xl font-bold text-sm text-white cursor-pointer transition-all flex items-center justify-center gap-2 active:scale-[0.98]';
    } else {
        btn.disabled = true;
        btn.style.cssText = 'background:var(--glass-strong);color:var(--text-faint);cursor:not-allowed;';
        btn.className = 'w-full py-3 rounded-xl font-bold text-sm text-white cursor-not-allowed transition-all flex items-center justify-center gap-2';
    }
}

function nomSubmit() {
    if (nomState.selected.size === 0) return;
    const selectedTanks = nomState.tanks.filter(function(t) { return nomState.selected.has(t.id); });
    const list = document.getElementById('nom-preview-list');
    list.innerHTML = selectedTanks.map(function(t) {
        return '<div class="flex items-center gap-3">' +
            '<div class="prev-tank-num">' + t.nomor_tank + '</div>' +
            '<div style="flex:1;min-width:0;">' +
            '<div style="font-size:10px;font-weight:700;color:var(--cyan-300);">' + t.kategori + '</div>' +
            (t.kelas ? '<div style="font-size:10px;font-weight:700;color:#6EE7B7;">Kelas ' + t.kelas + '</div>' : '') + '</div>' +
            '<i class="fas fa-star" style="color:var(--gold-400);"></i></div>';
    }).join('');
    document.getElementById('nom-preview-count').textContent = selectedTanks.length + ' Tank';
    document.getElementById('nom-preview-modal').classList.remove('hidden');
}

function nomClosePreview() { document.getElementById('nom-preview-modal').classList.add('hidden'); }

async function nomConfirmSubmit() {
    const btn = document.getElementById('nom-btn-confirm');
    btn.disabled = true;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Mengirim...';
    try {
        const res = await apiFetch('/api/juri/submit-nominasi', {
            method: 'POST',
            body: JSON.stringify({ ikan_ids: Array.from(nomState.selected) })
        });
        if (res.success) {
            nomClosePreview();
            showSuccessPopup('Nominasi Terkirim!', res.message);
            setTimeout(function() { checkNominasiStatus(); }, 1000);
        } else { showWarningModal([{type:'select', msg: res.message}]); }
    } catch (e) { showWarningModal([{type:'select', msg:'Gagal mengirim. Periksa koneksi internet Anda.'}]); }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Fix';
}

document.getElementById('nom-search')?.addEventListener('input', function(e) {
    nomState.searchTerm = e.target.value; nomUpdateFilterInfo(); nomRenderGrid();
});
document.getElementById('nom-preview-modal')?.addEventListener('click', function(e) {
    if (e.target === this) nomClosePreview();
});

// ═══════════════════════════════════════════════════════════════
// KONSTANTA
// ═══════════════════════════════════════════════════════════════
const MINOR_DEFECTS = ["Kutil","Bibir Miring","Katarak","Abses / Luka","Fintail Bleaching","Pangkal Ekor Naik/Trn","Dayung Tdk Seimbang"];
const MAYOR_DEFECTS = ["Bagian Bibir Hilang","Mulut Terbuka Terus","Muka Miring","Pangkal Bengkok/Patah","Fin/Tulang Hilang 1 Ruas"];

const DEFECT_MAP = {
    head:    { label:'Head',    minor:['Kutil'], mayor:['Bagian Bibir Hilang','Mulut Terbuka Terus','Muka Miring'] },
    face:    { label:'Face',    minor:['Bibir Miring','Katarak'], mayor:['Bagian Bibir Hilang','Mulut Terbuka Terus','Muka Miring'] },
    body:    { label:'Body',    minor:['Abses / Luka','Kutil'], mayor:['Pangkal Bengkok/Patah','Fin/Tulang Hilang 1 Ruas'] },
    finnage: { label:'Finnage', minor:['Fintail Bleaching','Pangkal Ekor Naik/Trn','Dayung Tdk Seimbang'], mayor:['Fin/Tulang Hilang 1 Ruas'] },
};

const SCORING_GROUPS = [
    { id:'overall', title:"Overall", fields:[{key:'overall.impression',label:'Impression',type:'standard'}] },
    { id:'head',    title:"Head",    fields:[{key:'head.size',label:'Size',type:'standard'},{key:'head.bentuk',label:'Bentuk',type:'head_shape'},{key:'defect.head',label:'Defect',type:'defect',part:'head'}] },
    { id:'face',    title:"Face",    fields:[{key:'face.face',label:'Face',type:'standard'},{key:'defect.face',label:'Defect',type:'defect',part:'face'}] },
    { id:'body',    title:"Body",    fields:[{key:'body.bentuk',label:'Bentuk',type:'body_shape'},{key:'body.proporsi',label:'Proporsi',type:'standard'},{key:'body.pangkal',label:'Pangkal',type:'standard'},{key:'defect.body',label:'Defect',type:'defect',part:'body'}] },
    { id:'marking', title:"Marking", fields:[{key:'marking.fullness',label:'Full',type:'standard'},{key:'marking.contrast',label:'Kontras',type:'standard'},{key:'marking.bentuk',label:'Bentuk',type:'standard'}] },
    { id:'pearl',   title:"Pearl",   fields:[{key:'pearl.shinning',label:'Shinning',type:'standard'},{key:'pearl.fullness',label:'Full',type:'standard'},{key:'pearl.bentuk',label:'Bentuk',type:'standard'}] },
    { id:'color',   title:"Color",   fields:[{key:'color.komposisi',label:'Komp',type:'standard'},{key:'color.kecerahan',label:'Cerah',type:'standard'},{key:'color.fullness',label:'Full',type:'standard'}] },
    { id:'finnage', title:"Finnage", fields:[{key:'finnage.bentuk',label:'Bentuk',type:'standard'},{key:'finnage.kecerahan',label:'Cerah',type:'standard'},{key:'defect.finnage',label:'Defect',type:'defect',part:'finnage'}] },
];

const GUIDELINES = {
    overall:{title:"OVERALL IMPRESSION",points:["IMPRESSION (100%): Menarik perhatian pada pandangan pertama.","Memiliki keistimewaan yang menarik.","MENTAL: Ikan tidak takut, aktif berinteraksi.","KESEHATAN: Tidak terkena penyakit, tidak luka."]},
    head:{title:"HEAD (KEPALA)",points:["SIZE (60%): Ukuran kepala prioritas utama.","BENTUK (40%):","• Bulat Bola (85-95)","• Swan Head (70-80)","• Tidak Simetris (60-70)"]},
    face:{title:"FACE (WAJAH)",points:["Pipi: Tidak terlalu tembem/berkerut.","Mata: Rata, seimbang, tidak ada titik putih.","Bibir: Menutup simetris.","Kondisi: Tidak berair, tidak ada marking.","Insang: Tertutup rapat."]},
    body:{title:"BODY (BADAN)",points:["BENTUK (50%):","• Kotak Tdk Simetris (80-90)","• Daun Simetris (70-80)","• Daun Tdk Simetris (60-70)","• Lancip (10-50)","PROPORSIONAL (40%): Ideal 1:1.5","PANGKAL (10%): Besar kokoh.","Bonsai: Short body >1:1.2 diskualifikasi."]},
    marking:{title:"MARKING",points:["FULLNESS (40%): Sepanjang badan.","CONTRAST (40%): Hitam pekat.","BENTUK (20%): Rapi.","Free Marking: Tidak > setengah badan."]},
    pearl:{title:"PEARL",points:["SHINING (45%): Berkilau.","FULLNESS (35%): Penuh sampai kepala.","BENTUK (20%): Rapi (cacing/pasir).","Klasik: Mutiara tidak > 25%."]},
    color:{title:"COLOR",points:["KOMPOSISI (45%): Dua warna (merah/kuning).","KECERAHAN (35%): Bersih.","FULLNESS (20%): Merata."]},
    finnage:{title:"FINNAGE",points:["BENTUK (75%): Wrapping, ekor kipas, dayung seimbang.","KECERAHAN (25%): Bersih, tidak bercak/jamur."]}
};

// ═══════════════════════════════════════════════════════════════
// LOCALSTORAGE PERSIST
// ═══════════════════════════════════════════════════════════════
function getDraftKey() { return 'juri_draft_' + authUserId; }
function saveDraft() { try { localStorage.setItem(getDraftKey(), JSON.stringify(tankScores)); } catch(e) {} }
function loadDraft() {
    try {
        var raw = localStorage.getItem(getDraftKey());
        if (!raw) return;
        var saved = JSON.parse(raw);
        var scoredIds = {};
        appData.my_scores.forEach(function(s) { scoredIds[s.ikan_id] = true; });
        Object.keys(saved).forEach(function(id) {
            if (!scoredIds[parseInt(id)]) { tankScores[id] = saved[id]; }
        });
    } catch(e) {}
}
function removeDraft(tankId) {
    try {
        var raw = localStorage.getItem(getDraftKey());
        if (!raw) return;
        var saved = JSON.parse(raw);
        delete saved[tankId];
        localStorage.setItem(getDraftKey(), JSON.stringify(saved));
    } catch(e) {}
}

// ═══════════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════════
let appData = { available_tanks:[], my_scores:[], all_scored:{}, scored_counts:{} };
let tankScores = {};
let activeTab = 'overall';
let showGuideline = false;
let isConfirmed = false;
let isSubmitting = false;
let defectModal = null;

// ═══════════════════════════════════════════════════════════════
// HELPERS: OPTIONS BUILDER
// ═══════════════════════════════════════════════════════════════
function stdOpts() { const o = []; for (let i=90;i>=10;i-=5) o.push({v:String(i),l:String(i)}); return o; }
function headShapeOpts() { return [{label:'--- BULAT BOLA (85-95) ---',options:[{v:'95',l:'95'},{v:'90',l:'90'},{v:'85',l:'85'}]},{label:'--- SWAN HEAD (70-80) ---',options:[{v:'80',l:'80'},{v:'75',l:'75'},{v:'70',l:'70'}]},{label:'--- TDK SIMETRIS (60-70) ---',options:[{v:'70',l:'70'},{v:'65',l:'65'},{v:'60',l:'60'}]},{label:'--- KURANG (<60) ---',options:Array.from({length:11},(_,i)=>({v:String(55-i*5),l:String(55-i*5)}))}]; }
function bodyShapeOpts() { return [{label:'--- KOTAK TDK SIMETRIS (80-90) ---',options:[{v:'90',l:'90'},{v:'85',l:'85'},{v:'80',l:'80'}]},{label:'--- DAUN SIMETRIS (70-80) ---',options:[{v:'80',l:'80'},{v:'75',l:'75'},{v:'70',l:'70'}]},{label:'--- DAUN TDK SIMETRIS (60-70) ---',options:[{v:'70',l:'70'},{v:'65',l:'65'},{v:'60',l:'60'}]},{label:'--- LANCIP (<60) ---',options:Array.from({length:11},(_,i)=>({v:String(55-i*5),l:String(55-i*5)}))}]; }
function defectOpts(partKey) { const p = DEFECT_MAP[partKey]; if (!p) return []; const r = [{label:'--- AMAN ---',options:[{v:'0',l:'Aman (0)'}]}]; if (p.minor.length) r.push({label:'--- MINOR ---',options:p.minor.map(d=>({v:d,l:d}))}); if (p.mayor.length) r.push({label:'--- MAYOR ---',options:p.mayor.map(d=>({v:d,l:d}))}); return r; }

function buildSelectHtml(currentVal, type) {
    let html = '<option value="">-</option>';
    if (type === 'head_shape') { headShapeOpts().forEach(g => { html += '<optgroup label="'+g.label+'">'; g.options.forEach(o => { html += '<option value="'+o.v+'"'+(String(currentVal)===o.v?' selected':'')+'>'+o.l+'</option>'; }); html += '</optgroup>'; }); }
    else if (type === 'body_shape') { bodyShapeOpts().forEach(g => { html += '<optgroup label="'+g.label+'">'; g.options.forEach(o => { html += '<option value="'+o.v+'"'+(String(currentVal)===o.v?' selected':'')+'>'+o.l+'</option>'; }); html += '</optgroup>'; }); }
    else { stdOpts().forEach(o => { html += '<option value="'+o.v+'"'+(String(currentVal)===o.v?' selected':'')+'>'+o.l+'</option>'; }); }
    return html;
}

// ═══════════════════════════════════════════════════════════════
// HELPERS: DEFECT EVALUATION
// ═══════════════════════════════════════════════════════════════
function evalDefects(ts) {
    const parts = ['head','face','body','finnage'];
    const status = {};
    parts.forEach(p => { status[p] = {minorCount:0, mayor:false, items:[]}; });
    parts.forEach(p => {
        const defs = ts.defects['raw_'+p+'_penalty'] || ['0'];
        defs.forEach(d => {
            if (d && d !== '0') {
                status[p].items.push(d);
                if (MINOR_DEFECTS.includes(d)) { status[p].minorCount++; }
                if (MAYOR_DEFECTS.includes(d)) { status[p].mayor = true; }
            }
        });
    });
    const results = {};
    parts.forEach(p => {
        if (status[p].items.length > 0) {
            const isMayor = status[p].mayor || (status[p].minorCount >= 2);
            results[p] = isMayor ? '30%' : '10%';
        } else { results[p] = ''; }
    });
    return results;
}

function getDefectBtnHtml(tankId, partKey, ts) {
    const vals = ts.defects['raw_'+partKey+'_penalty'] || ['0'];
    const isAman = vals.includes('0') || vals.length === 0;
    const ev = evalDefects(ts);
    const score = ev[partKey];
    if (isAman || !score) { return '<button type="button" onclick="openDefect('+tankId+',\''+partKey+'\')" class="defect-btn-aman">Aman</button>'; }
    if (score === '30%') { return '<button type="button" onclick="openDefect('+tankId+',\''+partKey+'\')" class="defect-btn-30">30% Defect</button>'; }
    return '<button type="button" onclick="openDefect('+tankId+',\''+partKey+'\')" class="defect-btn-10">10% Defect</button>';
}

// ═══════════════════════════════════════════════════════════════
// STATE MANAGEMENT
// ═══════════════════════════════════════════════════════════════
function initTankScores(tanks) { tanks.forEach(t => { if (!tankScores[t.id]) { tankScores[t.id] = { scores: { overall:{impression:''}, head:{size:'',bentuk:''}, face:{face:''}, body:{bentuk:'',proporsi:'',pangkal:''}, marking:{fullness:'',contrast:'',bentuk:''}, pearl:{shinning:'',fullness:'',bentuk:''}, color:{komposisi:'',kecerahan:'',fullness:''}, finnage:{bentuk:'',kecerahan:''} }, defects: { raw_head_penalty:['0'], raw_face_penalty:['0'], raw_body_penalty:['0'], raw_finnage_penalty:['0'] } }; } }); }
function getVal(tankId, key) { const parts = key.split('.'); if (parts[0] === 'defect') return null; return tankScores[tankId]?.scores?.[parts[0]]?.[parts[1]] || ''; }
function setVal(tankId, key, val) { const parts = key.split('.'); if (!tankScores[tankId]) return; if (!tankScores[tankId].scores[parts[0]]) tankScores[tankId].scores[parts[0]] = {}; tankScores[tankId].scores[parts[0]][parts[1]] = val; saveDraft(); }
function getFilteredTanks() { const fKat = document.getElementById('filter-kategori').value; const fKelas = document.getElementById('filter-kelas').value; let tanks = appData.available_tanks; if (fKat) tanks = tanks.filter(t => t.kategori === fKat); if (fKelas && !isNoKelas(fKat)) tanks = tanks.filter(t => t.kelas === fKelas); return tanks.filter(t => !appData.all_scored[t.id]); }

// ═══════════════════════════════════════════════════════════════
// RENDER
// ═══════════════════════════════════════════════════════════════
function renderTabs() {
    document.getElementById('tab-buttons').innerHTML = SCORING_GROUPS.map(g => {
        const isActive = g.id === activeTab;
        const style = isActive
            ? 'background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;border-color:transparent;box-shadow:0 2px 8px -4px rgba(6,182,212,0.4);'
            : 'background:var(--glass-2);color:var(--text);border-color:var(--bd-2);';
        return '<button type="button" onclick="switchTab(\''+g.id+'\')" id="tab-'+g.id+'" class="px-3 py-1.5 text-[11px] font-bold whitespace-nowrap rounded-md flex-shrink-0 border" style="'+style+'">'+g.title+'</button>';
    }).join('');
}

function renderFormTable() {
    const group = SCORING_GROUPS.find(g => g.id === activeTab);
    const tanks = getFilteredTanks();
    const fKat = document.getElementById('filter-kategori').value;
    const fKelas = document.getElementById('filter-kelas').value;
    let allFiltered = appData.available_tanks;
    if (fKat) allFiltered = allFiltered.filter(t => t.kategori === fKat);
    if (fKelas) allFiltered = allFiltered.filter(t => t.kelas === fKelas);
    const scoredCount = allFiltered.filter(t => appData.all_scored[t.id]).length;
    const info = document.getElementById('filter-info');
    info.classList.toggle('hidden', allFiltered.length === 0);
    document.getElementById('filter-remaining').textContent = allFiltered.length - scoredCount;
    document.getElementById('filter-scored').textContent = scoredCount;
    document.getElementById('form-thead').innerHTML = '<th class="px-2 py-2.5 w-16 text-center sticky left-0 z-30" style="background:rgba(255,255,255,0.10);border-right:1px solid var(--bd-1);box-shadow:2px 0 5px -2px rgba(0,0,0,0.2);">No Tank</th>' + group.fields.map(f => '<th class="px-2 py-2.5 text-center min-w-[100px]" style="border-bottom:1px solid var(--bd-1);">'+f.label+'</th>').join('');
    const tbody = document.getElementById('form-tbody');
    const empty = document.getElementById('form-empty');
    if (tanks.length === 0) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    tbody.innerHTML = tanks.map(tank => {
        const ts = tankScores[tank.id]; if (!ts) return '';
        const cells = group.fields.map(f => {
            if (f.type === 'defect') { return '<td class="p-1.5">'+getDefectBtnHtml(tank.id, f.part, ts)+'</td>'; }
            const val = getVal(tank.id, f.key);
            return '<td class="p-1.5"><select onchange="setVal('+tank.id+',\''+f.key+'\',this.value)" class="w-full px-2 py-2 border rounded text-center font-mono font-bold outline-none cursor-pointer text-sm" style="border-color:var(--bd-2);background:var(--glass-2);color:var(--text-hi);">'+buildSelectHtml(val, f.type)+'</select></td>';
        }).join('');
        return '<tr><td class="p-1 sticky left-0 z-10" style="background:rgba(255,255,255,0.04);border-right:1px solid var(--bd-1);box-shadow:2px 0 5px -2px rgba(0,0,0,0.1);"><input type="number" disabled value="'+tank.nomor_tank+'" class="w-[50px] mx-auto block px-1 py-2 rounded text-center font-bold text-sm cursor-not-allowed" style="background:rgba(255,255,255,0.02);border:1px solid var(--bd-1);color:var(--text-hi);"></td>'+cells+'</tr>';
    }).join('');
}

function renderLiveTable() {
    const body = document.getElementById('live-body');
    const empty = document.getElementById('live-empty');
    document.getElementById('live-count').textContent = appData.my_scores.length;
    if (appData.my_scores.length === 0) { body.innerHTML=''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    body.innerHTML = appData.my_scores.map(s => {
        const t = s.ikan; const nd = s.nilai_detail||{};
        const fmt = (obj,keys) => keys.map(k=>nd[obj]?.[k]||'-').join('/');
        let defHtml = '';
        ['head','face','body','finnage'].forEach(p => {
            const raw = s['raw_'+p+'_penalty'];
            if (raw && Array.isArray(raw) && raw[0]!=='0' && raw.length>0) defHtml += '<span class="live-defect-tag">'+raw.join(', ')+'</span>';
        });
        if (!defHtml) defHtml = '<span style="color:var(--text-faint);">-</span>';
        return '<tr><td class="px-2 py-2 font-bold text-center text-xs" style="background:rgba(255,255,255,0.04);border-right:1px solid var(--bd-1);color:var(--text-hi);">'+(t?t.nomor_tank:'-')+'</td><td class="px-2 py-2" style="border-right:1px solid var(--bd-1);"><div style="font-size:10px;font-weight:700;color:var(--text-hi);">'+(t?.kategori||'-')+'</div><div style="font-size:9px;color:var(--cyan-400);font-weight:700;">KLS:'+(s.kelas||'-')+'</div></td><td class="px-2 py-2 text-center font-mono font-bold" style="border-right:1px solid var(--bd-1);color:var(--cyan-300);background:rgba(34,211,238,0.06);">'+(nd.overall?.impression||'-')+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('head',['size','bentuk'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+(nd.face?.face||'-')+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('body',['bentuk','proporsi','pangkal'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('marking',['fullness','contrast','bentuk'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('pearl',['shinning','fullness','bentuk'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('color',['komposisi','kecerahan','fullness'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('finnage',['bentuk','kecerahan'])+'</td><td class="px-2 py-2 text-left align-top min-w-[100px] whitespace-normal" style="border-right:1px solid var(--bd-1);">'+defHtml+'</td><td class="px-2 py-2 text-center"><button onclick="lihatDetail('+s.id+')" class="live-detail-btn"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>Detail</button></td></tr>';
    }).join('');
}

function populateFilter() {
    document.getElementById('filter-kategori').innerHTML = '<option value="">Semua Kategori</option>' + ['Bonsai','Cencu','Chingwa','Freemarking','Goldenbase','Jumbo','Klasik'].map(c => '<option value="'+c+'">'+c+'</option>').join('');
    document.getElementById('filter-kelas').innerHTML = '<option value="">Semua Kelas</option>' + ['A','B','C','D','E'].map(c => '<option value="'+c+'">Kelas '+c+'</option>').join('');
    document.getElementById('filter-kategori').onchange = function() {
        var kelasWrap = document.getElementById('scoring-kelas-wrap');
        if(kelasWrap) kelasWrap.style.display = isNoKelas(this.value) ? 'none' : '';
        if(isNoKelas(this.value)) document.getElementById('filter-kelas').value = '';
        onFilterChange();
    };
}
function onFilterChange() { renderFormTable(); }

// ═══════════════════════════════════════════════════════════════
// TAB & GUIDELINE
// ═══════════════════════════════════════════════════════════════
function switchTab(id) { activeTab = id; showGuideline = false; document.getElementById('guideline-panel').classList.add('hidden'); updateGuidelineBtn(); renderTabs(); renderFormTable(); }
function toggleGuideline() {
    showGuideline = !showGuideline;
    const panel = document.getElementById('guideline-panel');
    if (showGuideline) {
        const g = GUIDELINES[activeTab];
        document.getElementById('guideline-title').textContent = 'Pedoman: '+g.title;
        document.getElementById('guideline-points').innerHTML = g.points.map(p=>'<li><span>'+p+'</span></li>').join('');
        panel.classList.remove('hidden');
    } else { panel.classList.add('hidden'); }
    updateGuidelineBtn();
}
function updateGuidelineBtn() {
    const b = document.getElementById('btn-guideline');
    if (showGuideline) {
        b.style.cssText = 'background:linear-gradient(135deg,var(--gold-500),#B45309);border-color:transparent;color:white;box-shadow:0 2px 8px -4px rgba(245,158,11,0.4);';
    } else {
        b.style.cssText = 'background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text);';
    }
}

// ═══════════════════════════════════════════════════════════════
// CONFIRM CHECKBOX
// ═══════════════════════════════════════════════════════════════
function toggleConfirm() {
    isConfirmed = !isConfirmed;
    const box = document.getElementById('confirm-check');
    const icon = document.getElementById('confirm-icon');
    const btn = document.getElementById('btn-batch-submit');
    if (isConfirmed) {
        box.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px;border-radius:12px;transition:cursor:pointer;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.22);opacity:1;';
        icon.style.cssText = 'width:20px;height:20px;flex-shrink:0;border-radius:4px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--gold-500),#B45309);border:1px solid var(--gold-400);transition:color 0.2s;';
        icon.innerHTML = '<svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
        btn.disabled = false;
        btn.style.cssText = 'background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;box-shadow:0 6px 16px -6px rgba(6,182,212,0.5),inset 0 1px 0 rgba(255,255,255,0.18);cursor:pointer;';
        btn.className = 'w-full text-white font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm active:scale-[0.98]';
    } else {
        box.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px;border-radius:12px;transition:cursor:pointer;background:rgba(255,255,255,0.04);border:1px solid var(--bd-2);opacity:0.5;';
        icon.style.cssText = 'width:20px;height:20px;flex-shrink:0;border-radius:4px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.06);border:1px solid var(--gold-400);transition:color 0.2s;';
        icon.innerHTML = '';
        btn.disabled = true;
        btn.style.cssText = 'background:var(--glass-strong);color:var(--text-faint);cursor:not-allowed;';
        btn.className = 'w-full font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm cursor-not-allowed';
    }
}

// ═══════════════════════════════════════════════════════════════
// DEFECT MODAL
// ═══════════════════════════════════════════════════════════════
function openDefect(tankId, partKey) {
    const vals = tankScores[tankId]?.defects?.['raw_'+partKey+'_penalty'] || ['0'];
    defectModal = { tankId, partKey, values: [...vals] };
    document.getElementById('defect-part-label').textContent = partKey.toUpperCase();
    const groups = defectOpts(partKey);
    document.getElementById('defect-modal-body').innerHTML = groups.map(g => '<div><div class="defect-group-title">'+g.label+'</div><div class="space-y-1">'+g.options.map(o => {
        const checked = defectModal.values.includes(o.v) ? 'checked' : '';
        const checkedClass = checked ? ' checked' : '';
        return '<label class="'+checkedClass+'"><input type="checkbox" class="defect-cb" style="width:18px;height:18px;margin-top:2px;flex-shrink:0;" value="'+o.v+'" '+checked+' onchange="onDefectCheck(this)"><span>'+o.l+'</span></label>';
    }).join('')+'</div></div>').join('');
    document.getElementById('modal-defect').classList.remove('hidden');
}
function onDefectCheck(cb) {
    if (!defectModal) return;
    let vals = [...defectModal.values];
    if (cb.value === '0') { vals = ['0']; }
    else { vals = vals.filter(v => v !== '0'); if (vals.includes(cb.value)) { vals = vals.filter(v => v !== cb.value); } else { vals.push(cb.value); } }
    if (vals.length === 0) vals = ['0'];
    defectModal.values = vals;
    cb.closest('label').classList.toggle('checked', cb.checked);
}
function saveDefect() {
    if (!defectModal) return;
    const { tankId, partKey, values } = defectModal;
    if (!tankScores[tankId]) return;
    tankScores[tankId].defects['raw_'+partKey+'_penalty'] = values;
    defectModal = null;
    document.getElementById('modal-defect').classList.add('hidden');
    saveDraft(); renderFormTable();
}

// ═══════════════════════════════════════════════════════════════
// BATCH SUBMIT
// ═══════════════════════════════════════════════════════════════
async function batchSubmit() {
    if (!isConfirmed || isSubmitting) return;
    const tanks = getFilteredTanks();
    const toSubmit = [];
    const skippedErrors = [];
    tanks.forEach(function(tank) {
        const ts = tankScores[tank.id]; if (!ts) return;
        let isComplete = true; const missingFields = [];
        SCORING_GROUPS.forEach(function(group) { group.fields.forEach(function(field) { if (field.type === 'defect') return; if (getVal(tank.id, field.key) === '') { isComplete = false; missingFields.push(group.title + ' > ' + field.label); } }); });
        if (isComplete) { toSubmit.push(tank); } else { skippedErrors.push('Tank T' + tank.nomor_tank + ': ' + missingFields.join(', ')); }
    });
    if (toSubmit.length === 0) { showWarningModal(skippedErrors.map(function(e) { return {type:'select', msg: e}; })); return; }
    isSubmitting = true;
    const btn = document.getElementById('btn-batch-submit');
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Menyimpan 0/' + toSubmit.length + '...';
    btn.disabled = true;
    let success = 0, fail = 0, loopErrors = [];
    for (let i = 0; i < toSubmit.length; i++) {
        const tank = toSubmit[i]; const ts = tankScores[tank.id];
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Menyimpan ' + (i+1) + '/' + toSubmit.length + '...';
        try {
            const res = await apiFetch('/api/juri/simpan-nilai', { method: 'POST', body: JSON.stringify({ ikan_id: tank.id, kelas: tank.kelas, all_scores: ts.scores, defect_data: ts.defects }) });
            if (res.success) { success++; removeDraft(tank.id); } else { fail++; loopErrors.push(res.message || 'Gagal menyimpan Tank ' + tank.nomor_tank); }
        } catch(e) { fail++; loopErrors.push('Gagal menyimpan Tank ' + tank.nomor_tank + ' (koneksi error)'); }
    }
    isSubmitting = false;
    if (fail === 0) {
        showSuccessPopup('Nilai Berhasil Disimpan!', 'Berhasil menyimpan <strong>' + success + '</strong> nilai.');
        if (skippedErrors.length > 0) { setTimeout(function() { showWarningModal(skippedErrors.map(function(e) { return {type:'select', msg: 'Dilewati (belum lengkap): ' + e}; })); }, 500); }
        isConfirmed = false; toggleConfirm();
    } else { showWarningModal(loopErrors.map(function(e) { return {type:'select', msg: e}; })); }
    await loadJuriData();
    btn.innerHTML = '<svg class="w-5 h-5" style="color:var(--gold-400);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> SIMPAN NILAI';
    if (isConfirmed) { btn.disabled = false; btn.style.cssText = 'background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;box-shadow:0 6px 16px -6px rgba(6,182,212,0.5),inset 0 1px 0 rgba(255,255,255,0.18);cursor:pointer;'; }
}

// ═══════════════════════════════════════════════════════════════
// LIHAT DETAIL
// ═══════════════════════════════════════════════════════════════
function lihatDetail(scoringId) {
    var s = appData.my_scores.find(function(x) { return x.id === scoringId; }); if (!s) return;
    var nd = s.nilai_detail || {};

    function defectText(partKey) {
        var raw = s['raw_' + partKey + '_penalty'];
        if (raw && Array.isArray(raw) && raw.length > 0) {
            if (raw.length === 1 && raw[0] === '0') return 'Aman';
            var filtered = raw.filter(function(d) { return d !== '0'; });
            return filtered.length > 0 ? filtered.join(', ') : '-';
        }
        return '-';
    }

    function v(obj, key) { return (obj && obj[key]) || '-'; }

    var html = '<div class="detail-scroll-hidden" style="text-align:left;max-height:50vh;overflow-y:auto;padding-right:4px;">';

    html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:3px 0;">';
    html += '<span style="font-size:13px;color:#FFFFFF;">Tank</span>';
    html += '<span style="font-size:15px;font-weight:800;color:#FFFFFF;">' + (s.ikan ? s.ikan.nomor_tank : '-') + '</span>';
    html += '</div>';
    html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:3px 0;margin-bottom:6px;">';
    html += '<span style="font-size:13px;color:#FFFFFF;">Kelas</span>';
    html += '<span style="font-size:15px;font-weight:800;color:var(--cyan-300);">' + (s.kelas || '-') + '</span>';
    html += '</div>';
    html += '<div style="height:1px;background:var(--bd-2);margin:2px 0 8px;"></div>';

    var groups = [
        { title:'OVERALL', items:[{label:'Impression', val:v(nd.overall,'impression'), isDef:false}] },
        { title:'HEAD', items:[
            {label:'Size', val:v(nd.head,'size'), isDef:false},
            {label:'Bentuk', val:v(nd.head,'bentuk'), isDef:false},
            {label:'Defect', val:defectText('head'), isDef:true}
        ]},
        { title:'FACE', items:[
            {label:'Face', val:v(nd.face,'face'), isDef:false},
            {label:'Defect', val:defectText('face'), isDef:true}
        ]},
        { title:'BODY', items:[
            {label:'Bentuk', val:v(nd.body,'bentuk'), isDef:false},
            {label:'Proporsi', val:v(nd.body,'proporsi'), isDef:false},
            {label:'Pangkal', val:v(nd.body,'pangkal'), isDef:false},
            {label:'Defect', val:defectText('body'), isDef:true}
        ]},
        { title:'MARKING', items:[
            {label:'Fullness', val:v(nd.marking,'fullness'), isDef:false},
            {label:'Kontras', val:v(nd.marking,'contrast'), isDef:false},
            {label:'Bentuk', val:v(nd.marking,'bentuk'), isDef:false}
        ]},
        { title:'PEARL', items:[
            {label:'Shinning', val:v(nd.pearl,'shinning'), isDef:false},
            {label:'Fullness', val:v(nd.pearl,'fullness'), isDef:false},
            {label:'Bentuk', val:v(nd.pearl,'bentuk'), isDef:false}
        ]},
        { title:'COLOR', items:[
            {label:'Komposisi', val:v(nd.color,'komposisi'), isDef:false},
            {label:'Kecerahan', val:v(nd.color,'kecerahan'), isDef:false},
            {label:'Fullness', val:v(nd.color,'fullness'), isDef:false}
        ]},
        { title:'FINNAGE', items:[
            {label:'Bentuk', val:v(nd.finnage,'bentuk'), isDef:false},
            {label:'Kecerahan', val:v(nd.finnage,'kecerahan'), isDef:false},
            {label:'Defect', val:defectText('finnage'), isDef:true}
        ]}
    ];

    groups.forEach(function(group) {
        html += '<div style="font-size:10px;font-weight:800;letter-spacing:0.14em;color:var(--cyan-300);margin-top:8px;margin-bottom:2px;padding:3px 0 2px;border-bottom:1px solid rgba(34,211,238,0.12);">' + group.title + '</div>';
        group.items.forEach(function(item) {
            var valColor = '#FFFFFF';
            if (item.isDef) {
                if (item.val === 'Aman') valColor = 'var(--success)';
                else if (item.val === '-') valColor = 'var(--text-faint)';
                else valColor = '#FCA5A5';
            }
            html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:2px 0 2px 8px;">';
            html += '<span style="font-size:13px;color:#FFFFFF;">' + item.label + '</span>';
            html += '<span style="font-size:14px;font-weight:700;color:' + valColor + ';font-family:\'JetBrains Mono\',monospace;">' + item.val + '</span>';
            html += '</div>';
        });
    });

    html += '</div>';
    showSuccessPopup('Detail Nilai Tank ' + (s.ikan ? s.ikan.nomor_tank : '-'), html);
}

// ═══════════════════════════════════════════════════════════════
// LOAD DATA
// ═══════════════════════════════════════════════════════════════
async function loadJuriData() {
    try {
        const res = await apiFetch('/api/juri/data');
        appData.available_tanks = res.available_tanks || [];
        appData.my_scores = res.my_scores || [];
        appData.all_scored = res.all_scored || {};
        appData.scored_counts = res.scored_counts || {};
        initTankScores(appData.available_tanks);
        loadDraft(); populateFilter(); renderFormTable(); renderLiveTable();
    } catch(e) { showWarningModal([{type:'select',msg:'Gagal memuat data dari server. Periksa koneksi internet Anda.'}]); }
}

function initScoringPage() {
    activeTab = 'overall'; showGuideline = false; isConfirmed = false; isSubmitting = false;
    renderTabs(); updateGuidelineBtn();
    document.getElementById('guideline-panel').classList.add('hidden');
    loadJuriData();
}

document.addEventListener('DOMContentLoaded', function() {
    renderTabs(); checkNominasiStatus();
    document.getElementById('modal-defect').addEventListener('click', function(e) { if (e.target === this) saveDefect(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && !document.getElementById('modal-defect').classList.contains('hidden')) saveDefect(); });
});
</script>
@endpush