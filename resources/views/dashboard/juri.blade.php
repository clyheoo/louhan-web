@extends('layouts.juri')

@section('content')
<div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 0: LOADING (SAAT CEK STATUS NOMINASI)
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-loading" class="lg:col-span-12 flex flex-col items-center justify-center py-32">
        <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-bold text-slate-400">Memeriksa status nominasi...</p>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 1: HALAMAN NOMINASI
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-page" class="hidden lg:col-span-12">

        {{-- Notifikasi Ditolak --}}
        <div id="nom-rejected-notice" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-start gap-3">
                <i class="fas fa-circle-xmark text-red-500 mt-0.5"></i>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-red-800">Beberapa Nominasi Ditolak</h4>
                    <ul id="nom-rejected-list" class="mt-2 space-y-1 text-xs text-red-700"></ul>
                    <p class="mt-2 text-xs font-semibold text-red-600">Silakan pilih ulang tank yang ingin dinominasikan.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">

            {{-- SIDEBAR FILTER --}}
            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden lg:sticky lg:top-24">
                    <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2 text-sm">
                            <i class="fas fa-filter text-blue-600"></i>
                            Filter Tank
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <input type="text" id="nom-search" placeholder="Cari no tank..." class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Kategori</label>
                            <div id="nom-kategori-btns" class="flex flex-wrap gap-1.5"></div>
                        </div>
                        <div id="nom-kelas-wrap">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Kelas</label>
                            <div id="nom-kelas-btns" class="flex flex-wrap gap-1.5"></div>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">Tank Terpilih</span>
                            <span id="nom-count-badge" class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-black">0</span>
                        </div>
                        <button id="nom-btn-submit" onclick="nomSubmit()" disabled class="w-full py-3 rounded-xl font-bold text-sm text-white bg-slate-300 cursor-not-allowed transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i> Kirim Nominasi
                        </button>
                    </div>
                </div>
            </div>

            {{-- GRID TANK --}}
            <div class="lg:col-span-8 xl:col-span-9">
                <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-4 mb-4 flex items-center justify-between">
                    <h2 class="font-bold text-slate-700 flex items-center gap-2 text-sm">
                        <i class="fas fa-award text-blue-600"></i>
                        Pilih Tank untuk Dinominasikan
                    </h2>
                    <button onclick="nomLoadData()" class="px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-200 transition-colors flex items-center gap-1.5">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div id="nom-filter-info" class="hidden mb-4 px-4 py-2.5 bg-blue-50 border border-blue-200 rounded-xl text-xs font-semibold text-blue-700 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-400"></i>
                    <span id="nom-filter-info-text">-</span>
                </div>
                <div id="nom-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3"></div>
                <div id="nom-grid-empty" class="hidden text-center py-16 bg-white rounded-xl shadow-lg border border-slate-200">
                    <i class="fas fa-database text-4xl text-slate-200 mb-3"></i>
                    <p class="text-xs font-bold text-slate-400">Tidak ada tank di filter ini</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 2: HALAMAN MENUNGGU
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-waiting" class="hidden lg:col-span-12">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8 md:p-12 text-center max-w-lg mx-auto">
            <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-hourglass-half text-3xl text-amber-500 animate-pulse"></i>
            </div>
            <h2 class="text-xl font-extrabold text-slate-800 mb-2">Nominasi Sedang Ditinjau</h2>
            <p class="text-sm text-slate-500 mb-6">Grand Juri sedang memeriksa pilihan Anda. Halaman akan otomatis diperbarui.</p>
            <div id="nom-waiting-list" class="text-left bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6 max-h-60 overflow-y-auto custom-scrollbar"></div>
            <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                Auto-refresh setiap 5 detik
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 3: ANIMASI APPROVAL
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-approved-anim" class="hidden fixed inset-0 z-[9998] flex items-center justify-center" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #bfdbfe 100%);">
        <div class="text-center fade-in">
            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-emerald-300/50" style="animation: popIn 0.5s cubic-bezier(0.16,1,0.3,1) both;">
                <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path class="check-draw" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 mb-2" style="animation: fadeUp 0.5s 0.4s ease both;">Nominasi Disetujui!</h2>
            <p class="text-sm text-slate-500" style="animation: fadeUp 0.5s 0.6s ease both;">Mempersiapkan halaman penilaian...</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 4: HALAMAN PENILAIAN (KODE EXISTING)
         ════════════════════════════════════════════════════════════ --}}
    <div id="scoring-page" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">

            {{-- ── KOLOM KIRI: FORM BATCH ────────────────────── --}}
            <div class="lg:col-span-5 flex flex-col gap-3">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden lg:sticky lg:top-24 flex flex-col">

                {{-- Header Form --}}
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 space-y-3">
                    <div class="flex justify-between items-center">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2 text-sm md:text-base">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Form Penilaian
                        </h2>
                    </div>

                    {{-- Filter + Kelas --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kategori</label>
                            <select id="filter-kategori" onchange="onFilterChange()" class="w-full px-2 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none text-xs font-semibold bg-white"></select>
                        </div>
                        <div id="scoring-kelas-wrap">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kelas</label>
                            <select id="filter-kelas" onchange="onFilterChange()" class="w-full px-2 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none text-xs font-bold text-center bg-white">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Juri</label>
                            <input type="text" value="{{ Auth::user()->name }}" disabled class="w-full px-2 py-2 border border-slate-200 rounded-md text-xs font-semibold bg-slate-50 text-slate-500 cursor-not-allowed">
                        </div>
                    </div>

                    {{-- Info Counter --}}
                    <div id="filter-info" class="hidden px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-md text-[10px] font-semibold text-blue-800 flex justify-between items-center">
                        <span>Tersisa: <b id="filter-remaining">0</b></span>
                        <span class="text-blue-400">|</span>
                        <span>Sudah Nilai: <b id="filter-scored">0</b></span>
                    </div>
                </div>

                {{-- Tab Kriteria + Pedoman --}}
                <div class="bg-slate-100 border-b border-slate-200 p-2 flex flex-col gap-2">
                    <div class="flex justify-between items-center px-1">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Kriteria:</span>
                        <button type="button" onclick="toggleGuideline()" id="btn-guideline" class="flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold transition shadow-sm border bg-white border-slate-300 text-slate-600 hover:bg-slate-50">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Pedoman
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1 px-1" id="tab-buttons"></div>
                </div>

                {{-- Panel Pedoman --}}
                <div id="guideline-panel" class="hidden bg-amber-50 border-b border-amber-100 px-4 py-2.5 slide-down">
                    <h4 class="text-[11px] font-bold text-amber-800 mb-1.5" id="guideline-title">-</h4>
                    <ul class="space-y-0.5" id="guideline-points"></ul>
                </div>

                {{-- TABEL FORM --}}
                <div class="overflow-auto flex-1 bg-slate-50 custom-scrollbar" style="max-height:460px;">
                    <table class="w-full text-xs text-left min-w-[max-content]">
                        <thead class="bg-slate-200 text-slate-700 font-bold sticky top-0 z-20 shadow-sm">
                            <tr id="form-thead"></tr>
                        </thead>
                        <tbody id="form-tbody" class="divide-y divide-slate-200 bg-white"></tbody>
                    </table>
                    <div id="form-empty" class="hidden text-center py-12">
                        <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-xs font-bold text-slate-400">Semua tank sudah dinilai atau tidak ada data.</p>
                    </div>
                </div>

                {{-- Footer Submit --}}
                <div class="p-3 bg-slate-50 border-t border-slate-200 space-y-3">
                    <div id="confirm-check" onclick="toggleConfirm()" class="flex items-center gap-2.5 p-2.5 rounded-lg border transition cursor-pointer bg-slate-100 border-slate-200 opacity-50">
                        <div id="confirm-icon" class="w-5 h-5 flex-shrink-0 rounded border flex items-center justify-center bg-white border-amber-400 transition-colors"></div>
                        <label class="text-[10px] font-bold text-amber-900 cursor-pointer select-none leading-snug">Saya menyatakan data siap disimpan.</label>
                    </div>
                    <button id="btn-batch-submit" onclick="batchSubmit()" disabled class="w-full text-white font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm bg-slate-300 cursor-not-allowed text-slate-500">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        SIMPAN NILAI
                    </button>
                </div>
            </div>
        </div>

        {{-- ── KOLOM KANAN: LIVE DATA ─────────────────────── --}}
        <div class="lg:col-span-7 flex flex-col">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden flex flex-col h-[500px] lg:h-[calc(100vh-10rem)]">
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center gap-3">
                    <h2 class="font-bold text-slate-700 flex items-center gap-2 text-sm md:text-base">
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Live Data (Nilai Saya)
                    </h2>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-full" id="live-count">0</span>
                </div>
                <div class="overflow-auto flex-1 bg-white custom-scrollbar">
                    <table class="w-full text-[10px] md:text-xs text-left whitespace-nowrap">
                        <thead class="bg-slate-100 text-slate-600 font-bold sticky top-0 z-20 shadow-sm border-b border-slate-200">
                            <tr>
                                <th class="px-2 py-2.5 border-r sticky left-0 bg-slate-100 z-30 w-12 text-center">Tank</th>
                                <th class="px-2 py-2.5 border-r w-16">Kelas</th>
                                <th class="px-2 py-2.5 border-r text-center bg-blue-50/50">Overall</th>
                                <th class="px-2 py-2.5 border-r text-center">Head</th>
                                <th class="px-2 py-2.5 border-r text-center">Face</th>
                                <th class="px-2 py-2.5 border-r text-center">Body</th>
                                <th class="px-2 py-2.5 border-r text-center">Marking</th>
                                <th class="px-2 py-2.5 border-r text-center">Pearl</th>
                                <th class="px-2 py-2.5 border-r text-center">Color</th>
                                <th class="px-2 py-2.5 border-r text-center">Finnage</th>
                                <th class="px-2 py-2.5 border-r text-center">Defect</th>
                                <th class="px-2 py-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="live-body" class="divide-y divide-slate-100"></tbody>
                    </table>
                    <div id="live-empty" class="hidden text-center py-16">
                        <p class="text-xs font-bold text-slate-400">Belum ada data nilai.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('modals')
{{-- MODAL PREVIEW NOMINASI --}}
<div id="nom-preview-modal" class="hidden fixed inset-0 bg-black/60 z-[260] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[85vh] flex flex-col fade-in">
        <div class="p-5 border-b border-slate-200 bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-clipboard-check text-blue-600"></i> Konfirmasi Nominasi
            </h3>
            <p class="text-xs text-slate-500 mt-1">Pastikan pilihan Anda sudah benar</p>
        </div>
        <div class="p-5 overflow-y-auto flex-1 custom-scrollbar">
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold text-slate-500">Tank Terpilih</span>
                <span id="nom-preview-count" class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-black">0</span>
            </div>
            <div id="nom-preview-list" class="space-y-2"></div>
        </div>
        <div class="p-5 border-t border-slate-200 grid grid-cols-2 gap-3">
            <button onclick="nomClosePreview()" class="py-3 rounded-xl font-bold text-xs text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">Ubah Pilihan</button>
            <button id="nom-btn-confirm" onclick="nomConfirmSubmit()" class="py-3 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> Kirim Fix
            </button>
        </div>
    </div>
</div>

{{-- MODAL DEFECT --}}
<div id="modal-defect" class="hidden fixed inset-0 bg-black/60 z-[260] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm max-h-[85vh] flex flex-col fade-in">
        <h3 class="text-lg font-bold mb-4 text-slate-800 border-b pb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Pilih Defect (<span id="defect-part-label">-</span>)
        </h3>
        <div class="overflow-y-auto flex-1 mb-4 space-y-4 custom-scrollbar pr-1" id="defect-modal-body"></div>
        <button onclick="saveDefect()" class="w-full py-3.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-md active:scale-95 transition-transform">Selesai & Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @keyframes popIn { 0%{transform:scale(0) rotate(-10deg);opacity:0} 60%{transform:scale(1.1) rotate(2deg);opacity:1} 100%{transform:scale(1) rotate(0deg);opacity:1} }
    @keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
    .check-draw { stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.5s 0.6s ease-out forwards; }
    @keyframes drawCheck { to { stroke-dashoffset: 0; } }
</style>
<script>

var NO_KELAS_KATEGORI = ['Bonsai', 'Jumbo'];
function isNoKelas(kat) { return NO_KELAS_KATEGORI.indexOf(kat) !== -1; }
function kelasLabel(kelas) { return kelas ? 'Kelas ' + kelas : ''; }
function kelasDisplay(kategori, kelas) { return isNoKelas(kategori) ? '' : '<div class="text-[10px] font-bold px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 truncate text-center border border-emerald-100/50">Kelas ' + kelas + '</div>'; }

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
                nomHide('nom-loading');
                nomHide('nom-page');
                nomHide('nom-waiting');
                nomShow('scoring-page');
                initScoringPage();
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
    nomHide('nom-waiting');
    nomHide('scoring-page');
    nomShow('nom-page');

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
    nomHide('nom-page');
    nomHide('scoring-page');
    nomShow('nom-waiting');

    const list = document.getElementById('nom-waiting-list');
    list.innerHTML = '<div class="space-y-2">' + nominations.map(n =>
        '<div class="flex items-center gap-3 p-2.5 bg-white rounded-lg border border-slate-200">' +
        '<div class="w-9 h-9 bg-slate-900 text-white rounded-lg flex items-center justify-center text-xs font-bold shrink-0">T' + n.nomor_tank + '</div>' +
        '<div><div class="text-xs font-bold text-slate-700">' + n.nama_peserta + '</div>' +
        '<div class="text-[10px] text-slate-500">' + n.kategori + (n.kelas ? ' — Kelas ' + n.kelas : '') + '</div></div>' +
        '<span class="ml-auto px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-bold">Pending</span></div>'
    ).join('') + '</div>';

    if (nomState.autoRefreshTimer) clearInterval(nomState.autoRefreshTimer);
    nomState.autoRefreshTimer = setInterval(checkNominasiStatus, 5000);
}

function nomShowApprovedAnim() {
    if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }
    nomHide('nom-page');
    nomHide('nom-waiting');
    nomHide('scoring-page');
    nomShow('nom-approved-anim');

    setTimeout(function() {
        nomHide('nom-approved-anim');
        nomShow('scoring-page');
        initScoringPage();
    }, 3000);
}

async function nomLoadData() {
    try {
        const res = await apiFetch('/api/juri/tanks-nominasi');
        nomState.tanks = res.tanks || [];
        nomState.kategoris = res.kategoris || [];
        nomState.kelass = res.kelass || [];
        nomRenderFilterBtns();
        nomRenderGrid();
    } catch (e) {
        showWarningModal([{type:'select', msg:'Gagal memuat data tank.'}]);
    }
}

function nomRenderFilterBtns() {
    const katDiv = document.getElementById('nom-kategori-btns');
    const kelDiv = document.getElementById('nom-kelas-btns');

    katDiv.innerHTML = '<button onclick="nomSetKat(\'\')" class="nom-kat-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-slate-900 text-white border-slate-900">Semua</button>' +
        nomState.kategoris.map(k => '<button onclick="nomSetKat(\'' + k + '\')" class="nom-kat-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-white text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600">' + k + '</button>').join('');

    kelDiv.innerHTML = '<button onclick="nomSetKelas(\'\')" class="nom-kel-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-slate-900 text-white border-slate-900">Semua</button>' +
        nomState.kelass.map(k => '<button onclick="nomSetKelas(\'' + k + '\')" class="nom-kel-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-white text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600">' + k + '</button>').join('');
}

function nomSetKat(val) {
    nomState.filterKat = val;
    nomState.filterKelas = '';
    var kelasWrap = document.getElementById('nom-kelas-wrap');
    if(kelasWrap) kelasWrap.style.display = isNoKelas(val) ? 'none' : '';
    document.querySelectorAll('.nom-kat-btn').forEach(function(b) {
        if (b.textContent.trim() === (val || 'Semua')) {
            b.className = 'nom-kat-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-slate-900 text-white border-slate-900';
        } else {
            b.className = 'nom-kat-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-white text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600';
        }
    });
    nomUpdateFilterInfo();
    nomRenderGrid();
}

function nomSetKelas(val) {
    nomState.filterKelas = val;
    document.querySelectorAll('.nom-kel-btn').forEach(function(b) {
        if (b.textContent.trim() === (val || 'Semua')) {
            b.className = 'nom-kel-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-slate-900 text-white border-slate-900';
        } else {
            b.className = 'nom-kel-btn px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-colors bg-white text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600';
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
        return '<div class="p-3 rounded-xl border transition-all cursor-pointer hover:shadow-md ' +
            (sel ? 'bg-blue-50 border-blue-400 shadow-md ring-2 ring-blue-200 -translate-y-0.5' : 'bg-white border-slate-200 hover:border-slate-300') +
            '" onclick="nomToggle(' + t.id + ')">' +
            '<div class="flex justify-between items-start mb-3">' +
            '<div class="w-16 h-16 rounded-xl flex items-center justify-center font-extrabold text-2xl shadow-md ' +            (sel ? 'bg-blue-600 text-white' : 'bg-slate-800 text-white') + '">' + t.nomor_tank + '</div>' +
            '<button class="p-2 rounded-[10px] transition-all ' +
            (sel ? 'text-amber-500 bg-amber-100' : 'text-slate-300 hover:text-amber-400 hover:bg-slate-50') +
            '" onclick="event.stopPropagation();nomToggle(' + t.id + ')">' +
            '<i class="fas fa-star ' + (sel ? '' : 'fa-regular') + '"></i></button></div>' +
            '<div class="flex flex-col gap-1.5">' +
            '<div class="text-[10px] font-bold px-2 py-1.5 rounded-lg bg-blue-50 text-blue-700 truncate text-center border border-blue-100/50">' + t.kategori + '</div>' +
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
        txt.innerHTML = 'Menampilkan filter — ' + parts.join(' <span class="text-blue-300 mx-1">|</span> ');
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function nomToggle(id) {
    if (nomState.selected.has(id)) { nomState.selected.delete(id); }
    else { nomState.selected.add(id); }
    nomUpdateCount();
    nomRenderGrid();
}

function nomUpdateCount() {
    const c = nomState.selected.size;
    document.getElementById('nom-count-badge').textContent = c;
    const btn = document.getElementById('nom-btn-submit');
    if (c > 0) {
        btn.disabled = false;
        btn.className = 'w-full py-3 rounded-xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 cursor-pointer transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25 active:scale-[0.98]';
    } else {
        btn.disabled = true;
        btn.className = 'w-full py-3 rounded-xl font-bold text-sm text-white bg-slate-300 cursor-not-allowed transition-all flex items-center justify-center gap-2';
    }
}

function nomSubmit() {
    if (nomState.selected.size === 0) return;
    const selectedTanks = nomState.tanks.filter(function(t) { return nomState.selected.has(t.id); });
    const list = document.getElementById('nom-preview-list');
    list.innerHTML = selectedTanks.map(function(t) {
        return '<div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-100">' +
            '<div class="w-10 h-10 bg-slate-900 text-white rounded-lg flex items-center justify-center text-sm font-bold shrink-0">T' + t.nomor_tank + '</div>' +
            '<div class="flex-1 min-w-0">' +
            '<div class="text-[10px] font-bold text-blue-600">' + t.kategori + '</div>' +
            (t.kelas ? '<div class="text-[10px] font-bold text-emerald-600">Kelas ' + t.kelas + '</div>' : '') + '</div>' +
            '<i class="fas fa-star text-amber-400"></i></div>';
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
        } else {
            showWarningModal([{type:'select', msg: res.message}]);
        }
    } catch (e) {
        showWarningModal([{type:'select', msg:'Gagal mengirim. Periksa koneksi internet Anda.'}]);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Fix';
}

document.getElementById('nom-search')?.addEventListener('input', function(e) {
    nomState.searchTerm = e.target.value;
    nomUpdateFilterInfo();
    nomRenderGrid();
});

document.getElementById('nom-preview-modal')?.addEventListener('click', function(e) {
    if (e.target === this) nomClosePreview();
});


// ═══════════════════════════════════════════════════════════════
// KONSTANTA (EXISTING)
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
// STATE (EXISTING)
// ═══════════════════════════════════════════════════════════════
let appData = { available_tanks:[], my_scores:[], all_scored:{}, scored_counts:{} };
let tankScores = {};
let activeTab = 'overall';
let showGuideline = false;
let isConfirmed = false;
let isSubmitting = false;
let defectModal = null;

// ═══════════════════════════════════════════════════════════════
// HELPERS: OPTIONS BUILDER (EXISTING)
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
// HELPERS: DEFECT EVALUATION (EXISTING)
// ═══════════════════════════════════════════════════════════════
function evalDefects(ts) {
    let minorCount = 0;
    const parts = ['head','face','body','finnage'];
    const status = {};
    parts.forEach(p => { status[p] = {minor:false,mayor:false,items:[]}; });
    parts.forEach(p => { const defs = ts.defects['raw_'+p+'_penalty'] || ['0']; defs.forEach(d => { if (d && d !== '0') { status[p].items.push(d); if (MINOR_DEFECTS.includes(d)) { minorCount++; status[p].minor = true; } if (MAYOR_DEFECTS.includes(d)) { status[p].mayor = true; } } }); });
    const globalMayor = minorCount >= 3;
    const results = {};
    parts.forEach(p => { if (status[p].items.length > 0) { const isMayor = status[p].mayor || (status[p].minor && globalMayor); results[p] = isMayor ? '30%' : '10%'; } else { results[p] = ''; } });
    return results;
}

function getDefectBtnHtml(tankId, partKey, ts) {
    const vals = ts.defects['raw_'+partKey+'_penalty'] || ['0'];
    const isAman = vals.includes('0') || vals.length === 0;
    const ev = evalDefects(ts);
    const score = ev[partKey];
    if (isAman || !score) { return '<button type="button" onclick="openDefect('+tankId+',\''+partKey+'\')" class="w-full px-1 py-2 border rounded text-center font-bold text-[10px] shadow-sm bg-white border-slate-300 text-slate-700 hover:border-blue-400">Aman</button>'; }
    if (score === '30%') { return '<button type="button" onclick="openDefect('+tankId+',\''+partKey+'\')" class="w-full px-1 py-2 border rounded text-center font-bold text-[10px] shadow-sm bg-red-600 border-red-700 text-white hover:bg-red-700">30% Defect</button>'; }
    return '<button type="button" onclick="openDefect('+tankId+',\''+partKey+'\')" class="w-full px-1 py-2 border rounded text-center font-bold text-[10px] shadow-sm bg-orange-100 border-orange-400 text-orange-800 hover:bg-orange-200">10% Defect</button>';
}

// ═══════════════════════════════════════════════════════════════
// STATE MANAGEMENT (EXISTING)
// ═══════════════════════════════════════════════════════════════
function initTankScores(tanks) { tanks.forEach(t => { if (!tankScores[t.id]) { tankScores[t.id] = { scores: { overall:{impression:''}, head:{size:'',bentuk:''}, face:{face:''}, body:{bentuk:'',proporsi:'',pangkal:''}, marking:{fullness:'',contrast:'',bentuk:''}, pearl:{shinning:'',fullness:'',bentuk:''}, color:{komposisi:'',kecerahan:'',fullness:''}, finnage:{bentuk:'',kecerahan:''} }, defects: { raw_head_penalty:['0'], raw_face_penalty:['0'], raw_body_penalty:['0'], raw_finnage_penalty:['0'] } }; } }); }
function getVal(tankId, key) { const parts = key.split('.'); if (parts[0] === 'defect') return null; return tankScores[tankId]?.scores?.[parts[0]]?.[parts[1]] || ''; }
function setVal(tankId, key, val) { const parts = key.split('.'); if (!tankScores[tankId]) return; if (!tankScores[tankId].scores[parts[0]]) tankScores[tankId].scores[parts[0]] = {}; tankScores[tankId].scores[parts[0]][parts[1]] = val; }
function getFilteredTanks() { const fKat = document.getElementById('filter-kategori').value; const fKelas = document.getElementById('filter-kelas').value; let tanks = appData.available_tanks; if (fKat) tanks = tanks.filter(t => t.kategori === fKat); if (fKelas && !isNoKelas(fKat)) tanks = tanks.filter(t => t.kelas === fKelas); return tanks.filter(t => !appData.all_scored[t.id]); }

// ═══════════════════════════════════════════════════════════════
// RENDER (EXISTING)
// ═══════════════════════════════════════════════════════════════
function renderTabs() { document.getElementById('tab-buttons').innerHTML = SCORING_GROUPS.map(g => '<button type="button" onclick="switchTab(\''+g.id+'\')" id="tab-'+g.id+'" class="px-3 py-1.5 text-[10px] font-bold whitespace-nowrap rounded-md transition-all flex-shrink-0 border '+(g.id===activeTab?'bg-blue-600 border-blue-700 text-white shadow-md':'bg-white border-slate-300 text-slate-600 hover:bg-slate-50')+'">'+g.title+'</button>').join(''); }

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
    document.getElementById('form-thead').innerHTML = '<th class="px-2 py-2.5 w-16 text-center border-b border-r border-slate-300 sticky left-0 bg-slate-200 z-30 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">No Tank</th>' + group.fields.map(f => '<th class="px-2 py-2.5 border-b border-slate-300 text-center min-w-[100px]">'+f.label+'</th>').join('');
    const tbody = document.getElementById('form-tbody');
    const empty = document.getElementById('form-empty');
    if (tanks.length === 0) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    tbody.innerHTML = tanks.map(tank => { const ts = tankScores[tank.id]; if (!ts) return ''; const cells = group.fields.map(f => { if (f.type === 'defect') { return '<td class="p-1.5">'+getDefectBtnHtml(tank.id, f.part, ts)+'</td>'; } const val = getVal(tank.id, f.key); return '<td class="p-1.5"><select onchange="setVal('+tank.id+',\''+f.key+'\',this.value)" class="w-full px-2 py-2 border border-slate-300 rounded text-center font-mono font-bold focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none bg-white cursor-pointer hover:border-blue-400 text-slate-800 text-sm shadow-sm">'+buildSelectHtml(val, f.type)+'</select></td>'; }).join(''); return '<tr class="hover:bg-blue-50/30"><td class="p-1 border-r border-slate-200 sticky left-0 bg-white z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]"><input type="number" disabled value="'+tank.nomor_tank+'" class="w-[50px] mx-auto block px-1 py-2 bg-slate-100 border border-slate-200 rounded text-center font-bold text-slate-600 text-sm cursor-not-allowed"></td>'+cells+'</tr>'; }).join('');
}

function renderLiveTable() {
    const body = document.getElementById('live-body');
    const empty = document.getElementById('live-empty');
    document.getElementById('live-count').textContent = appData.my_scores.length;
    if (appData.my_scores.length === 0) { body.innerHTML=''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    body.innerHTML = appData.my_scores.map(s => { const t = s.ikan; const nd = s.nilai_detail||{}; const fmt = (obj,keys) => keys.map(k=>nd[obj]?.[k]||'-').join('/'); let defHtml = ''; ['head','face','body','finnage'].forEach(p => { const raw = s['raw_'+p+'_penalty']; if (raw && Array.isArray(raw) && raw[0]!=='0' && raw.length>0) defHtml += '<span class="inline-block bg-red-100 text-red-700 text-[8px] font-bold px-1 py-0 rounded mb-0.5">'+raw.join(', ')+'</span>'; }); if (!defHtml) defHtml = '<span class="text-slate-300">-</span>'; const toG = s.submitted_to_grand; return '<tr class="hover:bg-amber-50/50"><td class="px-2 py-2 border-r font-bold text-slate-800 text-center bg-slate-50 sticky left-0 z-10 text-xs">T'+(t?t.nomor_tank:'-')+'</td><td class="px-2 py-2 border-r"><div class="font-bold text-[10px]">'+(t?.kategori||'-')+'</div><div class="text-[9px] text-blue-600 font-bold">KLS:'+(s.kelas||'-')+'</div></td><td class="px-2 py-2 border-r text-center font-mono font-bold text-blue-700 bg-blue-50/30">'+(nd.overall?.impression||'-')+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+fmt('head',['size','bentuk'])+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+(nd.face?.face||'-')+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+fmt('body',['bentuk','proporsi','pangkal'])+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+fmt('marking',['fullness','contrast','bentuk'])+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+fmt('pearl',['shinning','fullness','bentuk'])+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+fmt('color',['komposisi','kecerahan','fullness'])+'</td><td class="px-2 py-2 border-r text-center font-mono text-[10px]">'+fmt('finnage',['bentuk','kecerahan'])+'</td><td class="px-2 py-2 border-r text-left align-top min-w-[100px] whitespace-normal">'+defHtml+'</td><td class="px-2 py-2 text-center">'+'<button onclick="lihatDetail('+s.id+')" class="px-2 py-1 bg-slate-700 text-white text-[9px] font-bold rounded hover:bg-slate-800 flex items-center gap-1 mx-auto"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>Detail</button>'+'</td></tr>'; }).join('');
}

function populateFilter() { 
    document.getElementById('filter-kategori').innerHTML = '<option value="">Semua Kategori</option>' + ['Bonsai','Cencu','Chginwa','Freemarking','Goldenbase','Jumbo','Klasik'].map(c => '<option value="'+c+'">'+c+'</option>').join(''); 
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
// TAB & GUIDELINE (EXISTING)
// ═══════════════════════════════════════════════════════════════
function switchTab(id) { activeTab = id; showGuideline = false; document.getElementById('guideline-panel').classList.add('hidden'); updateGuidelineBtn(); renderTabs(); renderFormTable(); }
function toggleGuideline() { showGuideline = !showGuideline; const panel = document.getElementById('guideline-panel'); if (showGuideline) { const g = GUIDELINES[activeTab]; document.getElementById('guideline-title').textContent = 'Pedoman: '+g.title; document.getElementById('guideline-points').innerHTML = g.points.map(p=>'<li class="text-[10px] text-slate-700 flex items-start gap-1.5 leading-snug"><span class="text-amber-500 mt-0.5">•</span><span>'+p+'</span></li>').join(''); panel.classList.remove('hidden'); } else { panel.classList.add('hidden'); } updateGuidelineBtn(); }
function updateGuidelineBtn() { const b = document.getElementById('btn-guideline'); b.className = 'flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold transition shadow-sm border '+(showGuideline?'bg-amber-500 border-amber-600 text-white':'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'); }

// ═══════════════════════════════════════════════════════════════
// CONFIRM CHECKBOX (EXISTING)
// ═══════════════════════════════════════════════════════════════
function toggleConfirm() {
    isConfirmed = !isConfirmed;
    const box = document.getElementById('confirm-check');
    const icon = document.getElementById('confirm-icon');
    const btn = document.getElementById('btn-batch-submit');
    if (isConfirmed) {
        box.className = 'flex items-center gap-2.5 p-2.5 rounded-lg border transition cursor-pointer bg-amber-100/50 border-amber-200 hover:bg-amber-100';
        icon.className = 'w-5 h-5 flex-shrink-0 rounded border flex items-center justify-center bg-amber-500 border-amber-600 transition-colors';
        icon.innerHTML = '<svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
        btn.disabled = false;
        btn.className = 'w-full text-white font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm bg-slate-900 hover:bg-slate-800 active:scale-[0.98] cursor-pointer';
        btn.querySelector('svg').className = 'w-5 h-5 text-amber-400';
    } else {
        box.className = 'flex items-center gap-2.5 p-2.5 rounded-lg border transition cursor-pointer bg-slate-100 border-slate-200 opacity-50';
        icon.className = 'w-5 h-5 flex-shrink-0 rounded border flex items-center justify-center bg-white border-amber-400 transition-colors';
        icon.innerHTML = '';
        btn.disabled = true;
        btn.className = 'w-full text-white font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm bg-slate-300 cursor-not-allowed text-slate-500';
        btn.querySelector('svg').className = 'w-5 h-5 text-slate-400';
    }
}

// ═══════════════════════════════════════════════════════════════
// DEFECT MODAL (EXISTING)
// ═══════════════════════════════════════════════════════════════
function openDefect(tankId, partKey) {
    const vals = tankScores[tankId]?.defects?.['raw_'+partKey+'_penalty'] || ['0'];
    defectModal = { tankId, partKey, values: [...vals] };
    document.getElementById('defect-part-label').textContent = partKey.toUpperCase();
    const groups = defectOpts(partKey);
    document.getElementById('defect-modal-body').innerHTML = groups.map(g => '<div class="bg-slate-50 p-3 rounded-lg border border-slate-100"><div class="text-xs font-black text-slate-500 uppercase mb-3">'+g.label+'</div><div class="space-y-2">'+g.options.map(o => { const checked = defectModal.values.includes(o.v) ? 'checked' : ''; const bg = checked ? 'bg-blue-50 border-blue-200' : 'bg-white border-slate-200 hover:bg-slate-100'; return '<label class="flex items-start gap-3 p-2.5 rounded border cursor-pointer transition-colors '+bg+'"><input type="checkbox" class="defect-cb w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mt-0.5" value="'+o.v+'" '+checked+' onchange="onDefectCheck(this)"><span class="text-sm font-semibold text-slate-700">'+o.l+'</span></label>'; }).join('')+'</div></div>').join('');
    document.getElementById('modal-defect').classList.remove('hidden');
}
function onDefectCheck(cb) { if (!defectModal) return; let vals = [...defectModal.values]; if (cb.value === '0') { vals = ['0']; } else { vals = vals.filter(v => v !== '0'); if (vals.includes(cb.value)) { vals = vals.filter(v => v !== cb.value); } else { vals.push(cb.value); } } if (vals.length === 0) vals = ['0']; defectModal.values = vals; }
function saveDefect() { if (!defectModal) return; const { tankId, partKey, values } = defectModal; if (!tankScores[tankId]) return; tankScores[tankId].defects['raw_'+partKey+'_penalty'] = values; defectModal = null; document.getElementById('modal-defect').classList.add('hidden'); renderFormTable(); }

// ═══════════════════════════════════════════════════════════════
// BATCH SUBMIT (EXISTING)
// ═══════════════════════════════════════════════════════════════
async function batchSubmit() {
    if (!isConfirmed || isSubmitting) return;
    const tanks = getFilteredTanks();

    /* ── PISAHKAN: TANK LENGKAP (BOLEH SIMPAN) & TANK BELUM LENGKAP (DILEWATI) ── */
    const toSubmit = [];
    const skippedErrors = [];

    tanks.forEach(function(tank) {
        const ts = tankScores[tank.id];
        if (!ts) return;

        let isComplete = true;
        const missingFields = [];
        SCORING_GROUPS.forEach(function(group) {
            group.fields.forEach(function(field) {
                if (field.type === 'defect') return;
                if (getVal(tank.id, field.key) === '') {
                    isComplete = false;
                    missingFields.push(group.title + ' > ' + field.label);
                }
            });
        });

        if (isComplete) {
            toSubmit.push(tank);
        } else {
            skippedErrors.push('Tank T' + tank.nomor_tank + ': ' + missingFields.join(', '));
        }
    });

    if (toSubmit.length === 0) {
        showWarningModal(skippedErrors.map(function(e) { return {type:'select', msg: e}; }));
        return;
    }

    /* ── PROSES SIMPAN HANYA YANG LENGKAP ── */
    isSubmitting = true;
    const btn = document.getElementById('btn-batch-submit');
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Menyimpan 0/' + toSubmit.length + '...';
    btn.disabled = true;

    let success = 0, fail = 0, loopErrors = [];
    for (let i = 0; i < toSubmit.length; i++) {
        const tank = toSubmit[i];
        const ts = tankScores[tank.id];
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Menyimpan ' + (i+1) + '/' + toSubmit.length + '...';

        try {
            const res = await apiFetch('/api/juri/simpan-nilai', {
                method: 'POST',
                body: JSON.stringify({
                    ikan_id: tank.id,
                    kelas: tank.kelas,
                    all_scores: ts.scores,
                    defect_data: ts.defects,
                })
            });
            if (res.success) success++; else { fail++; loopErrors.push(res.message || 'Gagal menyimpan Tank ' + tank.nomor_tank); }
        } catch(e) { fail++; loopErrors.push('Gagal menyimpan Tank ' + tank.nomor_tank + ' (koneksi error)'); }
    }

    isSubmitting = false;
    if (fail === 0) {
        showSuccessPopup('Nilai Berhasil Disimpan!', 'Berhasil menyimpan <strong>' + success + '</strong> nilai.');
        /* Setelah notifikasi sukses, tampilkan peringatan tank yang dilewati */
        if (skippedErrors.length > 0) {
            setTimeout(function() {
                showWarningModal(skippedErrors.map(function(e) { return {type:'select', msg: 'Dilewati (belum lengkap): ' + e}; }));
            }, 500);
        }
        isConfirmed = false;
        toggleConfirm();
    } else {
        showWarningModal(loopErrors.map(function(e) { return {type:'select', msg: e}; }));
    }

    await loadJuriData();
    btn.innerHTML = '<svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> SIMPAN NILAI';
    if (isConfirmed) { btn.disabled = false; btn.className = 'w-full text-white font-bold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 text-sm bg-slate-900 hover:bg-slate-800 active:scale-[0.98] cursor-pointer'; }
}

// ═══════════════════════════════════════════════════════════════
// KIRIM GRAND JURI (EXISTING)
// ═══════════════════════════════════════════════════════════════
function lihatDetail(scoringId) {
    var s = appData.my_scores.find(function(x) { return x.id === scoringId; });
    if (!s) return;
    var nd = s.nilai_detail || {};
    var html = '<div style="text-align:left;font-size:12px;line-height:2;">';
    html += '<b>Tank:</b> T' + (s.ikan ? s.ikan.nomor_tank : '-') + '<br>';
    html += '<b>Kelas:</b> ' + (s.kelas || '-') + '<br>';
    html += '<b>Total Nilai:</b> ' + (s.total_nilai || 0) + '<br>';
    html += '<hr style="margin:8px 0;border-color:#e2e8f0;">';
    html += '<b>Overall:</b> ' + (nd.overall ? nd.overall.impression || '-' : '-') + '<br>';
    html += '<b>Head:</b> ' + (nd.head ? (nd.head.size||'-') + ' / ' + (nd.head.bentuk||'-') : '-') + '<br>';
    html += '<b>Face:</b> ' + (nd.face ? nd.face.face || '-' : '-') + '<br>';
    html += '<b>Body:</b> ' + (nd.body ? (nd.body.bentuk||'-') + ' / ' + (nd.body.proporsi||'-') + ' / ' + (nd.body.pangkal||'-') : '-') + '<br>';
    html += '<b>Marking:</b> ' + (nd.marking ? (nd.marking.fullness||'-') + ' / ' + (nd.marking.contrast||'-') + ' / ' + (nd.marking.bentuk||'-') : '-') + '<br>';
    html += '<b>Pearl:</b> ' + (nd.pearl ? (nd.pearl.shinning||'-') + ' / ' + (nd.pearl.fullness||'-') + ' / ' + (nd.pearl.bentuk||'-') : '-') + '<br>';
    html += '<b>Color:</b> ' + (nd.color ? (nd.color.komposisi||'-') + ' / ' + (nd.color.kecerahan||'-') + ' / ' + (nd.color.fullness||'-') : '-') + '<br>';
    html += '<b>Finnage:</b> ' + (nd.finnage ? (nd.finnage.bentuk||'-') + ' / ' + (nd.finnage.kecerahan||'-') : '-') + '<br>';
    html += '</div>';
    showSuccessPopup('Detail Nilai T' + (s.ikan ? s.ikan.nomor_tank : '-'), html);
}

// ═══════════════════════════════════════════════════════════════
// LOAD DATA (EXISTING)
// ═══════════════════════════════════════════════════════════════
async function loadJuriData() {
    try {
        const res = await apiFetch('/api/juri/data');
        appData.available_tanks = res.available_tanks || [];
        appData.my_scores = res.my_scores || [];
        appData.all_scored = res.all_scored || {};
        appData.scored_counts = res.scored_counts || {};
        initTankScores(appData.available_tanks);
        populateFilter();
        renderFormTable();
        renderLiveTable();
    } catch(e) { showWarningModal([{type:'select',msg:'Gagal memuat data dari server. Periksa koneksi internet Anda.'}]); }
}

// ═══════════════════════════════════════════════════════════════
// INIT SCORING PAGE (DIPANGGIL SETELAH APPROVED ANIM)
// ═══════════════════════════════════════════════════════════════
function initScoringPage() {
    activeTab = 'overall';
    showGuideline = false;
    isConfirmed = false;
    isSubmitting = false;
    renderTabs();
    updateGuidelineBtn();
    document.getElementById('guideline-panel').classList.add('hidden');
    loadJuriData();
}

// ═══════════════════════════════════════════════════════════════
// INIT ON LOAD
// ═══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    renderTabs();
    checkNominasiStatus();
    document.getElementById('modal-defect').addEventListener('click', function(e) { if (e.target === this) saveDefect(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('modal-defect').classList.contains('hidden')) saveDefect();
    });
});
</script>
@endpush