@extends('layouts.juri')

@section('content')
<div class="relative" style="min-height:70vh;">

    {{-- ════════════════════════════════════════════════════════════
         LAYER 0: LOADING
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-loading" class="absolute inset-0 z-40 flex flex-col items-center justify-center rounded-3xl" style="background:rgba(4,7,15,0.88);backdrop-filter:blur(8px);">
        <div class="w-12 h-12 border-4 rounded-full animate-spin mb-4" style="border-color:var(--glass-strong);border-top-color:var(--cyan-400);"></div>
        <p class="text-sm font-bold" style="color:var(--text-mid);">Memeriksa status nominasi...</p>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 1: HALAMAN NOMINASI
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-page" class="hidden">

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
                    <div class="flex items-center gap-2">
                        <button onclick="viewPendingNominations()" class="px-3 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors" style="background:rgba(245,158,11,0.10);border:1px solid rgba(245,158,11,0.30);color:var(--gold-300);">
                            <i class="fas fa-hourglass-half"></i> Lihat Pending
                        </button>
                        <button onclick="nomLoadData()" class="px-3 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">
                            <i id="nom-refresh-icon" class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
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
    <div id="nom-waiting" class="hidden">
        <div class="glass-card p-8 md:p-12 text-center max-w-lg mx-auto">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" style="background:rgba(245,158,11,0.10);border:1px solid rgba(245,158,11,0.25);">
                <i class="fas fa-hourglass-half text-3xl animate-pulse" style="color:var(--gold-400);"></i>
            </div>
            <h2 class="text-xl font-extrabold mb-2" style="color:var(--text-hi);">Nominasi Sedang Ditinjau</h2>
            <p class="text-sm mb-6" style="color:var(--text-mid);">Grand Juri sedang memeriksa pilihan Anda. Halaman akan otomatis diperbarui.</p>
            <div id="nom-waiting-list" class="text-left rounded-xl p-4 border mb-4 max-h-60 overflow-y-auto custom-scrollbar" style="background:rgba(255,255,255,0.03);border-color:var(--bd-1);"></div>
            <button onclick="goToNominasiPage()" class="w-full py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all mb-4 active:scale-[0.98]" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:white;box-shadow:0 6px 16px -6px rgba(6,182,212,0.5),inset 0 1px 0 rgba(255,255,255,0.18);cursor:pointer;">
                <i class="fas fa-plus-circle"></i> Tambah / Pilih Nominasi Lagi
            </button>
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
         LAYER 3B: ANIMASI REJECTION
         ════════════════════════════════════════════════════════════ --}}
    <div id="nom-rejected-anim" class="hidden fixed inset-0 z-[9998] flex items-center justify-center" style="background:radial-gradient(ellipse 60% 40% at 50% 50%, rgba(239,68,68,0.10), transparent 70%),linear-gradient(135deg, var(--ocean-950) 0%, var(--ocean-900) 50%, var(--ocean-850) 100%);">
        <div class="text-center fade-in">
            <div class="w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl" style="background:linear-gradient(135deg,#EF4444,#B91C1C);box-shadow:0 0 60px rgba(239,68,68,0.45);animation:popIn 0.5s cubic-bezier(0.16,1,0.3,1) both;">
                <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path class="cross-line-1" d="M6 6L18 18"/>
                    <path class="cross-line-2" d="M18 6L6 18"/>
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold mb-2" style="color:var(--text-hi);animation:fadeUp 0.5s 0.4s ease both;">Nominasi Ditolak</h2>
            <p id="nom-rejected-anim-sub" class="text-sm" style="color:var(--text-mid);animation:fadeUp 0.5s 0.6s ease both;">Mempersiapkan halaman pilih ulang...</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 4: HALAMAN PENILAIAN
         ════════════════════════════════════════════════════════════ --}}
    <div id="scoring-page" class="hidden relative">
        
        {{-- OVERLAY PENGUIJAN TERKUNCI --}}
        <div id="scoring-lock-overlay" class="hidden absolute inset-0 z-50 flex flex-col items-center justify-center rounded-3xl text-center p-8" style="background: rgba(4,7,15,0.92); backdrop-filter: blur(10px); border: 1px solid var(--bd-2);">
            <div id="scoring-lock-iconwrap" class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl" style="background: linear-gradient(135deg, #F59E0B, #B45309); box-shadow: 0 0 40px rgba(245,158,11,0.3);">
                <i id="scoring-lock-icon" class="fas fa-lock text-white text-4xl"></i>
            </div>
            <h2 id="scoring-lock-title" class="text-xl font-extrabold mb-2" style="color: var(--text-hi);">Sesi Penjurian Terkunci</h2>
            <p id="scoring-lock-desc" class="text-sm mb-6" style="color: var(--text-mid); max-width: 320px;">Admin belum membuka akses untuk melakukan penilaian. Silakan menunggu hingga sesi dibuka.</p>
            <div class="flex items-center justify-center gap-2 text-xs" style="color:var(--text-faint);">
                <div class="w-2 h-2 rounded-full animate-pulse" style="background:var(--gold-400);"></div>
                Auto-refresh status
            </div>
        </div>

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
                                <th class="px-2 py-2.5 sticky left-0 z-30 w-12 text-center" style="background:#15293D !important;border-right:1px solid var(--bd-1);">Tank</th>
                                <th class="px-2 py-2.5 w-16" style="border-right:1px solid var(--bd-1);">Kat/Kelas</th>
                                <th class="px-2 py-2.5 text-center" style="border-right:1px solid var(--bd-1);">Overall</th>
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

    {{-- ════════════════════════════════════════════════════════════
         LAYER 5: HALAMAN FOTO IKAN (ikan yang sudah di-ACC)
         ════════════════════════════════════════════════════════════ --}}
    <div id="foto-page" class="hidden" style="position:relative;min-height:60vh;">
        <style>
            .fotorow-btn{padding:7px 12px;border-radius:9px;font-size:11px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:filter .15s;}
            .fotorow-btn:hover{filter:brightness(1.08);}
        </style>
        <div id="foto-lock-overlay" class="hidden absolute inset-0 z-50 flex flex-col items-center justify-center rounded-3xl text-center p-8" style="background: rgba(4,7,15,0.92); backdrop-filter: blur(10px); border: 1px solid var(--bd-2);">
            <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl" style="background: linear-gradient(135deg, #F59E0B, #B45309); box-shadow: 0 0 40px rgba(245,158,11,0.3);">
                <i class="fas fa-lock text-white text-4xl"></i>
            </div>
            <h2 class="text-xl font-extrabold mb-2" style="color: var(--text-hi);">Foto Ikan Terkunci</h2>
            <p class="text-sm mb-6" style="color: var(--text-mid); max-width: 320px;">Admin belum membuka akses upload foto ikan. Silakan menunggu hingga halaman dibuka.</p>
            <div class="flex items-center justify-center gap-2 text-xs" style="color:var(--text-faint);">
                <div class="w-2 h-2 rounded-full animate-pulse" style="background:var(--gold-400);"></div>
                Auto-refresh saat halaman dibuka kembali
            </div>
        </div>
        <div class="glass-card p-4 mb-4 flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-bold flex items-center gap-2 text-sm" style="color:var(--text-hi);">
                <i class="fas fa-camera-retro" style="color:var(--cyan-400);"></i> Foto Ikan — Ikan Disetujui
            </h2>
            <div class="flex items-center gap-2">
                <input type="text" id="foto-search" oninput="fotoListSearch(this.value)" placeholder="Cari no tank / kategori..." class="px-3 py-2 rounded-lg text-xs font-semibold outline-none" style="border:1px solid var(--bd-2);background:var(--glass-2);color:var(--text-hi);width:170px;">
                <button onclick="loadFotoIkanList()" class="px-3 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
        </div>
        <div id="foto-loading-list" class="text-center py-16">
            <i class="fas fa-spinner fa-spin text-2xl" style="color:var(--cyan-400);"></i>
            <p class="text-xs font-bold mt-3" style="color:var(--text-low);">Memuat data ikan...</p>
        </div>
        <div id="foto-list" class="flex flex-col gap-2.5"></div>
        <div id="foto-list-empty" class="hidden text-center py-16 glass-card">
            <i class="fas fa-fish text-4xl mb-3" style="color:var(--text-faint);"></i>
            <p class="text-xs font-bold" style="color:var(--text-low);">Belum ada ikan yang disetujui.</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LAYER 6: HALAMAN REVISI & RANKING (juri)
         ════════════════════════════════════════════════════════════ --}}
    <div id="revisi-page" class="hidden relative" style="min-height:60vh;">

        <div class="revisi-hero">
            <div class="revisi-hero-left">
                <div class="revisi-hero-icon">
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <div>
                    <h2>Revisi &amp; Ranking</h2>
                    <p>Edit nilai yang sudah tersimpan/terkirim dan lihat ranking anonim tanpa nama peserta.</p>
                </div>
            </div>

            <button type="button" onclick="loadRevisiView(true)" class="revisi-refresh-btn">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
        </div>

        <div class="revisi-subtabs">
            <button id="revisi-tab-edit-btn" onclick="revisiSwitchTab('edit')" class="revisi-subtab-btn active">
                <i class="fas fa-list-check"></i>
                Edit Nilai
            </button>
            <button id="revisi-tab-ranking-btn" onclick="revisiSwitchTab('ranking')" class="revisi-subtab-btn">
                <i class="fas fa-trophy"></i>
                Ranking &amp; Juara
            </button>
        </div>

        <div id="revisi-subtab-edit">
            <div class="revisi-info-card">
                <i class="fas fa-circle-info"></i>
                <span>Nilai yang belum dikunci Grand Juri masih bisa direvisi. Jika sudah final, tombol edit otomatis terkunci.</span>
            </div>

            <div class="revisi-edit-filter-card">
                <div class="revisi-edit-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input
                        type="text"
                        id="revisi-edit-search"
                        oninput="revisiApplyEditFilter()"
                        placeholder="Cari nomor tank, kategori, kelas, atau point..."
                    >
                </div>

                <div id="revisi-edit-filter-chips" class="revisi-edit-filter-chips"></div>
            </div>

            <div id="revisi-loading" class="hidden revisi-loading-card">
                <div class="revisi-loading-spinner"></div>
                <p>Memuat data revisi...</p>
                <span>Mengambil nilai juri dan komponen penilaian.</span>
            </div>

            <div id="revisi-list" class="revisi-list-wrap"></div>
        </div>

        <div id="revisi-subtab-ranking" class="hidden">
            <div class="revisi-filter-card">
                <div>
                    <h3><i class="fas fa-filter"></i> Filter Ranking</h3>
                    <p>Ranking ditampilkan anonim, hanya berdasarkan tank dan point.</p>
                </div>

                <div class="revisi-filter-actions">
                    <div class="revisi-scope-wrap">
                        <button id="revisi-scope-per_kategori_kelas" onclick="setRevisiRankScope('per_kategori_kelas')" class="revisi-scope-btn active">
                            <i class="fas fa-layer-group"></i>
                            Per Kat + Kelas
                        </button>
                        <button id="revisi-scope-per_kategori" onclick="setRevisiRankScope('per_kategori')" class="revisi-scope-btn">
                            <i class="fas fa-tags"></i>
                            Per Kategori
                        </button>
                        <button id="revisi-scope-global" onclick="setRevisiRankScope('global')" class="revisi-scope-btn">
                            <i class="fas fa-globe"></i>
                            Global
                        </button>
                    </div>

                    <div class="revisi-select-wrap">
                        <select id="revisi-rank-kategori" onchange="loadRevisiRanking()" class="revisi-rank-select"></select>
                        <span id="revisi-rank-kelas-wrap">
                            <select id="revisi-rank-kelas" onchange="loadRevisiRanking()" class="revisi-rank-select"></select>
                        </span>
                    </div>
                </div>
            </div>

            <div id="revisi-ranking-content" class="revisi-ranking-wrap"></div>
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

{{-- MODAL CONFIRM CANCEL NOMINASI --}}
<div id="cancel-confirm-modal" class="hidden fixed inset-0 z-[270] flex items-center justify-center p-4" style="background:rgba(2,6,14,0.88);backdrop-filter:blur(8px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-sm flex flex-col fade-in" style="background:linear-gradient(180deg,var(--ocean-800),var(--ocean-900));border:1px solid var(--bd-2);">
        <div class="p-6 text-center" style="border-bottom:1px solid var(--bd-1);">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:linear-gradient(135deg,#EF4444,#B91C1C);box-shadow:0 8px 24px rgba(239,68,68,0.4);">
                <i class="fas fa-trash-can text-white text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold mb-2" style="color:var(--text-hi);">Batalkan Nominasi?</h3>
            <p class="text-xs" style="color:var(--text-mid);">Nominasi <b>Tank <span id="cancel-tank-num" style="color:var(--cyan-300);">-</span></b> akan hilang dari antrian review Grand Juri.</p>
        </div>
        <div class="p-5 grid grid-cols-2 gap-3">
            <button id="cancel-modal-btn-no" onclick="hideCancelConfirm()" class="py-3 rounded-xl font-bold text-xs transition-colors" style="color:var(--text-mid);background:var(--glass-2);border:1px solid var(--bd-2);">
                <i class="fas fa-xmark"></i> Tidak
            </button>
            <button id="cancel-modal-btn-yes" onclick="confirmCancelNominasi()" class="py-3 rounded-xl font-bold text-xs text-white flex items-center justify-center gap-2" style="background:linear-gradient(135deg,#EF4444,#B91C1C);box-shadow:0 6px 16px -6px rgba(239,68,68,0.5),inset 0 1px 0 rgba(255,255,255,0.18);">
                <i class="fas fa-check"></i> Ya, Batalkan
            </button>
        </div>
    </div>
</div>

{{-- MODAL DEFECT ALL-IN-ONE (UNTUK NOMINASI) --}}
<div id="modal-nom-defect-all" class="hidden fixed inset-0 z-[260] flex items-center justify-center p-3 md:p-4" style="background:rgba(2,6,14,0.88);backdrop-filter:blur(8px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col fade-in" style="background:linear-gradient(180deg,var(--ocean-800),var(--ocean-900));border:1px solid var(--bd-2);max-height:92vh;">
        <div class="px-5 py-4 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <div class="min-w-0">
                <h3 class="text-base md:text-lg font-bold flex items-center gap-2" style="color:var(--text-hi);">
                    <i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i> Tandai Defect
                </h3>
                <p class="text-[11px] md:text-xs mt-1" style="color:var(--text-mid);">
                    Tank <span id="nom-defect-all-tank" style="color:var(--cyan-300);font-weight:800;">-</span> — pilih defect untuk seluruh komponen
                </p>
            </div>
            <button onclick="closeNomDefectAll()" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors flex-shrink-0" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div id="nom-defect-all-body" class="px-4 md:px-5 py-4 overflow-y-auto flex-1 custom-scrollbar"></div>
        <div class="px-4 md:px-5 py-4 grid grid-cols-2 gap-3" style="border-top:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <button onclick="resetNomDefectAll()" class="py-3 rounded-xl font-bold text-xs transition-colors flex items-center justify-center gap-1.5" style="color:var(--text-mid);background:var(--glass-2);border:1px solid var(--bd-2);">
                <i class="fas fa-eraser"></i> Reset Semua
            </button>
            <button onclick="saveNomDefectAll()" class="py-3 rounded-xl font-bold text-xs text-white flex items-center justify-center gap-2" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));box-shadow:0 6px 16px -6px rgba(6,182,212,0.5),inset 0 1px 0 rgba(255,255,255,0.18);">
                <i class="fas fa-check"></i> Selesai
            </button>
        </div>
    </div>
</div>

{{-- MODAL EDIT REVISI --}}
<div id="revisi-edit-modal" class="hidden fixed inset-0 z-[250] flex items-center justify-center p-4" style="background:rgba(2,6,14,0.88);backdrop-filter:blur(8px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col fade-in" style="background:linear-gradient(180deg,var(--ocean-800),var(--ocean-900));border:1px solid var(--bd-2);">
        <div class="px-5 py-4 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <div class="min-w-0">
                <h3 id="revisi-edit-title" class="text-base font-bold flex items-center gap-2" style="color:var(--text-hi);"><i class="fas fa-pen-to-square" style="color:var(--cyan-400);"></i> Revisi Nilai</h3>
                <p class="text-[11px] mt-1" style="color:var(--text-mid);">Ubah nilai lalu simpan. Perubahan langsung menggantikan nilai lama.</p>
            </div>
            <button onclick="closeRevisiEdit()" class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);"><i class="fas fa-xmark"></i></button>
        </div>
        <div id="revisi-form-body" class="px-5 py-4 overflow-y-auto flex-1 custom-scrollbar"></div>
        <div class="px-5 py-4 flex items-center justify-end gap-3" style="border-top:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <button onclick="closeRevisiEdit()" class="py-2.5 px-4 rounded-xl font-bold text-xs" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">Batal</button>
            <button id="revisi-submit-btn" onclick="submitRevisi()" class="py-2.5 px-5 rounded-xl font-bold text-xs text-white flex items-center gap-2" style="background:linear-gradient(135deg,#10B981,#059669);box-shadow:0 6px 16px -6px rgba(16,185,129,.5);"><i class="fas fa-floppy-disk"></i> Simpan Revisi</button>
        </div>
    </div>
</div>

{{-- [DIHAPUS] Picker popup diganti view list inline (#foto-page). --}}

{{-- ════════ MODAL FOTO IKAN (JURI) — Galeri + Upload ════════ --}}
<div id="jf-modal" class="hidden fixed inset-0 z-[290] flex items-center justify-center p-4" style="background:rgba(2,6,14,0.9);backdrop-filter:blur(8px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md max-h-[88vh] flex flex-col fade-in" style="background:linear-gradient(180deg,var(--ocean-800),var(--ocean-900));border:1px solid var(--bd-2);">
        <div class="px-5 py-4 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <div class="min-w-0">
                <h3 class="text-base md:text-lg font-bold flex items-center gap-2" style="color:var(--text-hi);"><i class="fas fa-images" style="color:var(--cyan-400);"></i> Foto Ikan</h3>
                <p id="jf-desc" class="text-[11px] md:text-xs mt-1" style="color:var(--text-mid);">-</p>
            </div>
            <button onclick="closeJuriFotoModal()" class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="px-5 py-4 overflow-y-auto flex-1 custom-scrollbar">
            <div id="jf-status" style="display:none;margin-bottom:12px;padding:10px 12px;border-radius:12px;font-size:12px;font-weight:700;"></div>
            <div id="jf-gallery" style="margin-bottom:14px;"></div>

            <div id="jf-camera" style="display:none;margin-bottom:14px;">
                <video id="jf-video" autoplay playsinline muted style="width:100%;border-radius:16px;border:1px solid var(--bd-2);background:#000;display:block;"></video>
                <div style="display:flex;gap:10px;margin-top:10px;">
                    <button type="button" onclick="jfCapture()" class="flex-1 py-2.5 rounded-xl font-bold text-xs text-white flex items-center justify-center gap-2" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));"><i class="fas fa-camera"></i> Jepret</button>
                    <button type="button" onclick="jfCancelCamera()" class="flex-1 py-2.5 rounded-xl font-bold text-xs" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">Batal</button>
                </div>
            </div>

            <div id="jf-preview" style="margin-bottom:14px;display:none;"></div>
            <div id="jf-quota" style="display:none;font-size:11px;color:var(--text-mid);margin-bottom:12px;"></div>
            <div id="jf-fullnote" style="display:none;margin-bottom:14px;padding:12px 14px;border-radius:12px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);color:var(--gold-300);font-size:12px;line-height:1.55;"><i class="fas fa-circle-check" style="margin-right:6px;"></i> Total foto sudah mencapai batas 3MB.</div>

            <input type="file" id="jf-input-file" accept="image/jpeg,image/png" style="display:none;">
            <input type="file" id="jf-input-cam" accept="image/*" capture="environment" style="display:none;">

            <div id="jf-upload-actions" style="display:none;gap:10px;">
                <button type="button" onclick="jfStartCamera()" class="flex-1 py-2.5 rounded-xl font-bold text-xs text-white flex items-center justify-center gap-2" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));"><i class="fas fa-camera"></i> Ambil Foto</button>
                <button type="button" onclick="document.getElementById('jf-input-file').click()" class="flex-1 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-2" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);"><i class="fas fa-folder-open"></i> Pilih File</button>
            </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-3" style="border-top:1px solid var(--bd-1);background:rgba(255,255,255,0.03);">
            <button onclick="closeJuriFotoModal()" class="py-2.5 px-4 rounded-xl font-bold text-xs" style="background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);">Tutup</button>
            <button id="jf-btn-save" onclick="jfSubmit()" class="py-2.5 px-5 rounded-xl font-bold text-xs text-white items-center justify-center gap-2" style="display:none;background:linear-gradient(135deg,#10B981,#059669);"><i class="fas fa-cloud-arrow-up" style="margin-right:6px;"></i> Simpan Foto</button>
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
</div>{{-- End content panel --}}
@endsection

@push('scripts')
<style>
    /* ── REVISI & RANKING ── */
    .revisi-hero{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:14px;
        flex-wrap:wrap;
        padding:18px;
        margin-bottom:16px;
        border-radius:22px;
        background:
            radial-gradient(circle at 0% 0%, rgba(34,211,238,.12), transparent 38%),
            linear-gradient(180deg, rgba(255,255,255,.055), rgba(255,255,255,.025));
        border:1px solid var(--bd-2);
        box-shadow:var(--shadow-card);
    }

    .revisi-hero-left{
        display:flex;
        align-items:center;
        gap:14px;
        min-width:0;
    }

    .revisi-hero-icon{
        width:46px;
        height:46px;
        border-radius:15px;
        display:grid;
        place-items:center;
        flex-shrink:0;
        background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));
        color:#fff;
        box-shadow:0 8px 22px -8px rgba(6,182,212,.65), inset 0 1px 0 rgba(255,255,255,.25);
    }

    .revisi-hero h2{
        font-size:18px;
        font-weight:900;
        color:var(--text-hi);
        letter-spacing:-.02em;
        line-height:1.1;
    }

    .revisi-hero p{
        margin-top:4px;
        font-size:12px;
        color:var(--text-mid);
        line-height:1.45;
    }

    .revisi-refresh-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        padding:9px 14px;
        border-radius:12px;
        border:1px solid var(--bd-2);
        background:var(--glass-2);
        color:var(--text);
        font-size:12px;
        font-weight:800;
        cursor:pointer;
        font-family:inherit;
        transition:all .18s ease;
    }

    .revisi-refresh-btn:hover{
        background:var(--glass-3);
        color:var(--text-hi);
        border-color:var(--bd-cyan);
    }

    .revisi-subtabs{
        display:flex;
        gap:8px;
        margin-bottom:14px;
        flex-wrap:wrap;
    }

    .revisi-subtab-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        padding:10px 16px;
        border-radius:13px;
        font-size:12px;
        font-weight:900;
        cursor:pointer;
        font-family:inherit;
        background:var(--glass-2);
        border:1px solid var(--bd-2);
        color:var(--text-mid);
        transition:all .18s ease;
    }

    .revisi-subtab-btn:hover{
        color:var(--text-hi);
        background:var(--glass-3);
    }

    .revisi-subtab-btn.active{
        background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));
        color:#fff;
        border-color:transparent;
        box-shadow:0 8px 20px -8px rgba(6,182,212,.55);
    }

    .revisi-info-card{
        display:flex;
        align-items:flex-start;
        gap:10px;
        padding:12px 14px;
        margin-bottom:14px;
        border-radius:15px;
        background:rgba(34,211,238,.07);
        border:1px solid rgba(34,211,238,.18);
        color:var(--cyan-300);
        font-size:12px;
        font-weight:700;
        line-height:1.5;
    }

    .revisi-info-card i{
        margin-top:2px;
        color:var(--cyan-400);
    }

    .revisi-list-wrap{
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .revisi-row{
        display:grid;
        grid-template-columns:82px minmax(180px,1fr) minmax(120px,auto) minmax(120px,auto) auto;
        align-items:center;
        gap:12px;
        padding:14px;
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.025));
        border:1px solid var(--bd-2);
        box-shadow:0 14px 30px -24px rgba(0,0,0,.65);
    }

    .revisi-row-tank{
        min-height:54px;
        border-radius:15px;
        display:grid;
        place-items:center;
        background:rgba(34,211,238,.10);
        border:1px solid rgba(34,211,238,.22);
        font-weight:900;
        font-size:16px;
        color:var(--cyan-300);
        font-family:'JetBrains Mono',monospace;
    }

    .revisi-row-info{
        min-width:0;
    }

    .revisi-row-kat{
        font-weight:900;
        font-size:14px;
        color:var(--text-hi);
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .revisi-row-kelas{
        margin-top:4px;
        font-size:11px;
        color:var(--text-mid);
        font-weight:700;
    }

    .revisi-row-point{
        padding:9px 12px;
        border-radius:13px;
        background:rgba(255,255,255,.04);
        border:1px solid var(--bd-1);
        font-family:'JetBrains Mono',monospace;
        font-weight:900;
        font-size:14px;
        color:var(--text-hi);
        text-align:center;
        white-space:nowrap;
    }

    .revisi-row-point span{
        display:block;
        margin-top:2px;
        font-family:'Plus Jakarta Sans',sans-serif;
        font-size:9px;
        font-weight:900;
        color:var(--text-low);
        letter-spacing:.10em;
        text-transform:uppercase;
    }

    .revisi-row-status,
    .revisi-row-act{
        display:flex;
        justify-content:flex-end;
    }

    .revisi-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:5px;
        min-width:92px;
        font-size:10px;
        font-weight:900;
        padding:7px 10px;
        border-radius:999px;
        white-space:nowrap;
    }

    .revisi-badge-lock{
        background:rgba(245,158,11,.12);
        color:var(--gold-300);
        border:1px solid rgba(245,158,11,.30);
    }

    .revisi-badge-sent{
        background:rgba(6,182,212,.10);
        color:var(--cyan-300);
        border:1px solid var(--bd-cyan);
    }

    .revisi-badge-saved{
        background:rgba(16,185,129,.10);
        color:#6EE7B7;
        border:1px solid rgba(16,185,129,.25);
    }

    .revisi-edit-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        min-width:92px;
        padding:9px 14px;
        border-radius:12px;
        font-size:12px;
        font-weight:900;
        cursor:pointer;
        font-family:inherit;
        color:#fff;
        border:0;
        background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));
        box-shadow:0 6px 16px -8px rgba(6,182,212,.55);
    }

    .revisi-filter-card{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        padding:16px;
        margin-bottom:16px;
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.025));
        border:1px solid var(--bd-2);
        box-shadow:var(--shadow-card);
    }

    .revisi-filter-card h3{
        display:flex;
        align-items:center;
        gap:8px;
        color:var(--text-hi);
        font-size:14px;
        font-weight:900;
    }

    .revisi-filter-card h3 i{
        color:var(--cyan-400);
    }

    .revisi-filter-card p{
        margin-top:4px;
        color:var(--text-mid);
        font-size:11px;
        font-weight:600;
    }

    .revisi-filter-actions{
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:10px;
    }

    .revisi-scope-wrap,
    .revisi-select-wrap{
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:8px;
        flex-wrap:wrap;
    }

    .revisi-scope-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        padding:8px 13px;
        border-radius:11px;
        font-size:11px;
        font-weight:800;
        cursor:pointer;
        font-family:inherit;
        background:var(--glass-2);
        border:1px solid var(--bd-2);
        color:var(--text-mid);
        transition:all .18s;
    }

    .revisi-scope-btn.active{
        background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));
        color:#fff;
        border-color:transparent;
        box-shadow:0 6px 16px -8px rgba(6,182,212,.55);
    }

    .revisi-rank-select{
        min-width:150px;
        padding:9px 12px;
        border-radius:11px;
        font-size:12px;
        font-weight:800;
        outline:none;
        background:rgba(15,23,42,.88);
        border:1px solid var(--bd-2);
        color:var(--text-hi);
        font-family:inherit;
    }

    .revisi-ranking-wrap{
        display:flex;
        flex-direction:column;
        gap:14px;
    }

    .revisi-rank-card{
        overflow:hidden;
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.025));
        border:1px solid var(--bd-2);
        box-shadow:0 16px 32px -26px rgba(0,0,0,.65);
    }

    .revisi-rank-head{
        padding:14px 16px;
        border-bottom:1px solid var(--bd-1);
        background:rgba(255,255,255,.035);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        flex-wrap:wrap;
    }

    .revisi-rank-head-title{
        display:flex;
        align-items:center;
        gap:10px;
        font-weight:900;
        font-size:13px;
        color:var(--text-hi);
    }

    .revisi-rank-head-title i{
        color:var(--gold-400);
    }

    .revisi-rank-table-wrap{
        overflow:auto;
    }

    .revisi-rank-table{
        width:100%;
        min-width:620px;
        border-collapse:collapse;
        font-size:12px;
    }

    .revisi-rank-table th{
        padding:10px 12px;
        background:rgba(255,255,255,.055);
        color:var(--text-mid);
        font-size:10px;
        font-weight:900;
        letter-spacing:.08em;
        text-transform:uppercase;
        text-align:center;
    }

    .revisi-rank-table td{
        padding:11px 12px;
        border-top:1px solid var(--bd-1);
        text-align:center;
        color:var(--text);
    }

    .revisi-rank-top{
        background:rgba(245,158,11,.06);
    }

    .revisi-rank-medal{
        font-size:16px;
        font-weight:900;
    }

    .revisi-rank-tank{
        font-family:'JetBrains Mono',monospace;
        font-weight:900;
        color:var(--cyan-300) !important;
    }

    .revisi-rank-point{
        font-family:'JetBrains Mono',monospace;
        font-weight:900;
        color:var(--text-hi) !important;
    }

    .revisi-rank-final{
        font-weight:900;
        color:#6EE7B7 !important;
    }

    .revisi-group { margin-bottom:14px; }
    .revisi-group-title { font-size:10px; font-weight:800; letter-spacing:.12em; color:var(--cyan-300); margin-bottom:8px; padding-bottom:4px; border-bottom:1px solid rgba(34,211,238,.12); }
    .revisi-fields { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:10px; }
    .revisi-field { display:flex; flex-direction:column; gap:4px; }
    .revisi-field label { font-size:10px; font-weight:700; color:var(--text-mid); text-transform:uppercase; }
    .revisi-select { padding:8px 10px; border-radius:9px; font-size:13px; font-weight:700; text-align:center; outline:none; background:var(--glass-2); border:1px solid var(--bd-2); color:var(--text-hi); cursor:pointer; font-family:'JetBrains Mono',monospace; }
    .revisi-locked { padding:8px 10px; border-radius:9px; font-size:12px; font-weight:700; text-align:center; background:rgba(255,255,255,0.02); border:1px solid var(--bd-1); color:var(--text-faint); }

    @media (max-width:900px){
        .revisi-row{
            grid-template-columns:72px minmax(0,1fr);
        }

        .revisi-row-point,
        .revisi-row-status,
        .revisi-row-act{
            grid-column:1 / -1;
            justify-content:stretch;
        }

        .revisi-row-status .revisi-badge,
        .revisi-row-act .revisi-edit-btn{
            width:100%;
        }

        .revisi-filter-actions{
            width:100%;
            align-items:stretch;
        }

        .revisi-scope-wrap,
        .revisi-select-wrap{
            justify-content:flex-start;
        }
    }

    @media (max-width:640px){
        .revisi-hero{
            padding:15px;
        }

        .revisi-hero-left{
            align-items:flex-start;
        }

        .revisi-subtab-btn,
        .revisi-refresh-btn,
        .revisi-scope-btn,
        .revisi-rank-select{
            width:100%;
        }

        .revisi-scope-wrap,
        .revisi-select-wrap{
            width:100%;
        }

        #revisi-rank-kelas-wrap{
            width:100%;
        }
    }
    @keyframes popIn { 0%{transform:scale(0) rotate(-10deg);opacity:0} 60%{transform:scale(1.1) rotate(2deg);opacity:1} 100%{transform:scale(1) rotate(0deg);opacity:1} }
    @keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
    .check-draw { stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.5s 0.6s ease-out forwards; }
    @keyframes drawCheck { to { stroke-dashoffset: 0; } }
    /* ── ANIMASI X (REJECTION) ── */
    .cross-line-1, .cross-line-2 { stroke-dasharray: 30; stroke-dashoffset: 30; }
    .cross-line-1 { animation: drawCross 0.35s 0.5s ease-out forwards; }
    .cross-line-2 { animation: drawCross 0.35s 0.85s ease-out forwards; }
    @keyframes drawCross { to { stroke-dashoffset: 0; } }

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
    /* ── TOMBOL "TANDAI DEFECT" DI CARD NOMINASI ── */
    #nom-grid .nom-defect-btn {
        width: 100%;
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.02em;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid var(--bd-2);
        line-height: 1.3;
        font-family: inherit;
    }
    #nom-grid .nom-defect-empty {
        background: rgba(239,68,68,0.06);
        color: #FCA5A5;
        border-color: rgba(239,68,68,0.30);
        border-style: dashed;
    }
    #nom-grid .nom-defect-empty:hover {
        background: rgba(239,68,68,0.12);
        border-style: solid;
        border-color: rgba(239,68,68,0.50);
    }
    #nom-grid .nom-defect-minor {
        background: rgba(249,115,22,0.18);
        color: #FDBA74;
        border-color: rgba(249,115,22,0.40);
    }
    #nom-grid .nom-defect-minor:hover { background: rgba(249,115,22,0.26); }
    #nom-grid .nom-defect-mayor {
        background: rgba(239,68,68,0.20);
        color: #FCA5A5;
        border-color: rgba(239,68,68,0.45);
        box-shadow: 0 0 0 1px rgba(239,68,68,0.18);
    }
    #nom-grid .nom-defect-mayor:hover { background: rgba(239,68,68,0.30); }

    /* ── MODAL ALL-IN-ONE DEFECT (NOMINASI) ── */
    .nom-defect-all-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
    @media (max-width: 720px) {
        .nom-defect-all-grid { grid-template-columns: 1fr; gap: 12px; }
    }
    .nom-da-part {
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--bd-1);
        border-radius: 14px;
        padding: 12px 14px;
    }
    .nom-da-part-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px dashed var(--bd-1);
    }
    .nom-da-part-head h4 {
        font-size: 12.5px;
        font-weight: 800;
        color: var(--cyan-300);
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin: 0;
    }
    .nom-da-tag {
        font-size: 9px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 999px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .nom-da-tag-aman {
        background: rgba(16,185,129,0.10);
        color: #6EE7B7;
        border: 1px solid rgba(16,185,129,0.30);
    }
    .nom-da-tag-minor {
        background: rgba(249,115,22,0.15);
        color: #FDBA74;
        border: 1px solid rgba(249,115,22,0.40);
    }
    .nom-da-tag-mayor {
        background: rgba(239,68,68,0.18);
        color: #FCA5A5;
        border: 1px solid rgba(239,68,68,0.45);
    }
    .nom-da-section { margin-top: 10px; }
    .nom-da-section:first-of-type { margin-top: 0; }
    .nom-da-section-title {
        font-size: 9.5px;
        font-weight: 800;
        color: var(--text-faint);
        letter-spacing: 0.12em;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    .nom-da-opts {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .nom-da-opt {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 8px 10px;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.15s;
        border: 1px solid transparent;
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        line-height: 1.35;
    }
    .nom-da-opt:hover { background: var(--glass-3); }
    .nom-da-opt.checked {
        background: rgba(34,211,238,0.08);
        border-color: rgba(34,211,238,0.22);
        color: var(--text-hi);
    }
    .nom-da-opt input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin-top: 1px;
        flex-shrink: 0;
        accent-color: var(--cyan-500);
        cursor: pointer;
    }
    .nom-da-opt span { flex: 1; }

    /* ── MOBILE TWEAKS ── */
    @media (max-width: 640px) {
        #nom-grid .nom-defect-btn { font-size: 10px; padding: 7px 8px; }
        .nom-da-part { padding: 10px 11px; border-radius: 12px; }
        .nom-da-part-head h4 { font-size: 11.5px; }
        .nom-da-opt { font-size: 11.5px; padding: 7px 9px; }
        .nom-da-tag { font-size: 8.5px; padding: 2px 6px; }
    }

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
        .revisi-loading-card{
        display:flex;
        align-items:center;
        gap:12px;
        padding:16px;
        margin-bottom:14px;
        border-radius:16px;
        background:rgba(34,211,238,.07);
        border:1px solid rgba(34,211,238,.20);
        color:var(--cyan-300);
    }

    .revisi-loading-card i{
        font-size:18px;
        color:var(--cyan-400);
    }

    .revisi-loading-card b{
        display:block;
        color:var(--text-hi);
        font-size:13px;
        font-weight:900;
    }

    .revisi-loading-card span{
        display:block;
        margin-top:2px;
        color:var(--text-mid);
        font-size:11px;
        font-weight:700;
    }

    .revisi-refresh-btn.loading i{
        animation:spin 1s linear infinite;
    }

    .revisi-components{
        grid-column:1 / -1;
        display:flex;
        flex-wrap:wrap;
        gap:7px;
        padding-top:10px;
        margin-top:2px;
        border-top:1px solid var(--bd-1);
    }

    .revisi-score-chip{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 9px;
        border-radius:10px;
        background:rgba(255,255,255,.045);
        border:1px solid var(--bd-1);
        font-size:10px;
        font-weight:800;
        color:var(--text-mid);
    }

    .revisi-score-chip b{
        color:var(--cyan-300);
        font-family:'JetBrains Mono',monospace;
        font-size:11px;
    }

    .revisi-score-chip.missing{
        color:var(--text-faint);
        opacity:.7;
    }

    @keyframes spin{
        from{ transform:rotate(0deg); }
        to{ transform:rotate(360deg); }
    }
    /* Loading revisi harus benar-benar hilang saat class hidden aktif */
    .revisi-loading-card.hidden{
        display:none !important;
    }

    .revisi-loading-card{
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:10px;
        padding:34px 18px;
        margin-bottom:14px;
        border-radius:18px;
        background:rgba(4,7,15,.72);
        border:1px solid var(--bd-2);
        color:var(--text-mid);
        text-align:center;
        box-shadow:var(--shadow-card);
    }

    .revisi-loading-spinner{
        width:44px;
        height:44px;
        border-radius:999px;
        border:4px solid var(--glass-strong);
        border-top-color:var(--cyan-400);
        animation:spin 1s linear infinite;
    }

    .revisi-loading-card p{
        margin:0;
        color:var(--text-hi);
        font-size:13px;
        font-weight:900;
    }

    .revisi-loading-card span{
        color:var(--text-mid);
        font-size:11px;
        font-weight:700;
    }

    .revisi-refresh-btn.loading i{
        animation:spin 1s linear infinite;
    }

    .revisi-edit-filter-card{
        display:flex;
        flex-direction:column;
        gap:12px;
        padding:14px;
        margin-bottom:14px;
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.025));
        border:1px solid var(--bd-2);
        box-shadow:0 14px 30px -24px rgba(0,0,0,.65);
    }

    .revisi-edit-search{
        display:flex;
        align-items:center;
        gap:10px;
        padding:10px 13px;
        border-radius:13px;
        background:rgba(15,23,42,.80);
        border:1px solid var(--bd-2);
    }

    .revisi-edit-search i{
        color:var(--cyan-400);
        font-size:12px;
    }

    .revisi-edit-search input{
        width:100%;
        background:transparent;
        border:0;
        outline:0;
        color:var(--text-hi);
        font-family:inherit;
        font-size:12px;
        font-weight:800;
    }

    .revisi-edit-search input::placeholder{
        color:var(--text-low);
    }

    .revisi-edit-filter-chips{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
        max-height:86px;
        overflow:auto;
        padding-bottom:2px;
    }

    .revisi-edit-chip{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:8px 13px;
        border-radius:999px;
        background:var(--glass-2);
        border:1px solid var(--bd-2);
        color:var(--text-mid);
        font-family:inherit;
        font-size:11px;
        font-weight:900;
        cursor:pointer;
        transition:all .18s ease;
    }

    .revisi-edit-chip:hover{
        color:var(--text-hi);
        background:var(--glass-3);
    }

    .revisi-edit-chip.active{
        background:linear-gradient(135deg,rgba(34,211,238,.20),rgba(37,99,235,.18));
        border-color:var(--bd-cyan);
        color:var(--cyan-300);
        box-shadow:0 8px 20px -12px rgba(6,182,212,.55);
    }

    @keyframes spin{
        from{ transform:rotate(0deg); }
        to{ transform:rotate(360deg); }
    }
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
    defects: {},
    kategoris: [],
    kelass: [],
    filterKat: '',
    filterKelas: '',
    searchTerm: '',
    autoRefreshTimer: null,
    excludedTankIds: new Set(),
};

// ═══════════════════════════════════════════════════════════════
// PERSIST PILIHAN NOMINASI (DRAFT) — bertahan saat refresh / keluar halaman
// ═══════════════════════════════════════════════════════════════
function getNomDraftKey() { return 'juri_nom_draft_' + authUserId; }

function saveNomDraft() {
    try {
        // Jangan simpan tank yang sudah pending (sumbernya server) atau approved/rejected.
        var pendingIds = new Set();
        (nomState.tanks || []).forEach(function(t) { if (t.is_pending) pendingIds.add(t.id); });

        var sel = [];
        nomState.selected.forEach(function(id) {
            if (pendingIds.has(id)) return;
            if (nomState.excludedTankIds.has(id)) return;
            sel.push(id);
        });

        var defs = {};
        sel.forEach(function(id) {
            if (nomState.defects[id]) defs[id] = nomState.defects[id];
        });

        localStorage.setItem(getNomDraftKey(), JSON.stringify({ selected: sel, defects: defs }));
    } catch (e) {}
}

function loadNomDraft() {
    try {
        var raw = localStorage.getItem(getNomDraftKey());
        if (!raw) return;
        var d = JSON.parse(raw);

        // Hanya pulihkan tank yang masih ada di daftar & bukan excluded.
        var validIds = new Set((nomState.tanks || []).map(function(t) { return t.id; }));

        (d.selected || []).forEach(function(id) {
            id = parseInt(id, 10);
            if (nomState.excludedTankIds.has(id)) return;
            if (validIds.size && !validIds.has(id)) return;
            nomState.selected.add(id);
        });

        Object.keys(d.defects || {}).forEach(function(key) {
            var nid = parseInt(key, 10);
            if (nomState.excludedTankIds.has(nid)) return;
            if (validIds.size && !validIds.has(nid)) return;
            nomState.defects[nid] = d.defects[key];
        });
    } catch (e) {}
}

function clearNomDraft() {
    try { localStorage.removeItem(getNomDraftKey()); } catch (e) {}
}

let currentJuriView = 'nominasi';
let isScoringUnlocked = false;
let isAssignmentPending = false;
let scoringLockTimer = null;
let lastApprovedIkanSig = '';

function makeApprovedIkanSig(ids) {
    return (ids || [])
        .map(function(id) { return parseInt(id, 10); })
        .filter(function(id) { return !isNaN(id); })
        .sort(function(a, b) { return a - b; })
        .join('|');
}

function setApprovedIkanSig(ids) {
    lastApprovedIkanSig = makeApprovedIkanSig(ids);
}

function nomShow(id) {
    var el = document.getElementById(id);
    if (!el) return;

    el.classList.remove('hidden');

    // Penting: forceHidePage() pernah memberi display:none.
    // Jadi saat show, display harus dikembalikan.
    if (el.style.display === 'none') {
        el.style.display = '';
    }
}

function nomHide(id) {
    var el = document.getElementById(id);
    if (!el) return;

    el.classList.add('hidden');
    el.style.display = 'none';
}

async function checkNominasiStatus(attempt = 1) {
    try {
        const res = await apiFetch('/api/juri/nominasi-status');

        // ★ GUARD: hitung tab AKTIF SETELAH request selesai.
        //    Tab bisa berpindah ke 'penjurian' selama menunggu response,
        //    jadi snapshot di awal fungsi bisa basi & memunculkan halaman pending
        //    di atas halaman penjurian. Baca currentJuriView yang terbaru di sini.
        const isOnPenjurianTab = (currentJuriView === 'penjurian');

        // Status nominasi hanya boleh mengubah UI saat tab Nominasi/Penjurian.
        // Ini mencegah halaman lain ikut berubah karena response lama dari request async.
        if (currentJuriView !== 'nominasi' && currentJuriView !== 'penjurian') {
            nomHide('nom-loading');
            return;
        }

        const status = res.status;
        const wasPending = sessionStorage.getItem('nom_was_pending') === '1';
        
        // ★ Update daftar tank yang diexclude (sudah approve/reject)
        nomState.excludedTankIds = new Set();
        (res.nominations || []).forEach(function(n) {
            if (n.status === 'approved' || n.status === 'rejected') {
                nomState.excludedTankIds.add(n.ikan_id);
            }
        });
        nomState.excludedTankIds.forEach(function(id) {
            nomState.selected.delete(id);
            delete nomState.defects[id];
        });
        
        if (!isOnPenjurianTab) nomHide('nom-loading');

        if (status === 'approved') {
            if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }

            isScoringUnlocked = res.scoring_unlocked === true;
            isAssignmentPending = res.assignment_pending === true;
            updateScoringLockUI();

            // ★ Jika di tab penjurian, cukup update state
            if (isOnPenjurianTab) return;

            var scoringPage = document.getElementById('scoring-page');
            var scoringAlreadyVisible = scoringPage && !scoringPage.classList.contains('hidden');
            
            if (wasPending) {
                sessionStorage.removeItem('nom_was_pending');
                nomShowApprovedAnim();
            } else if (currentJuriView === 'nominasi') {
                nomShowNominasiPage(res.nominations);
            } else if (!scoringAlreadyVisible) {
                nomHide('nom-page'); nomHide('nom-waiting');
                nomHide('nom-approved-anim'); nomHide('nom-rejected-anim');
                nomShow('scoring-page'); initScoringPage();
            }

        } else if (status === 'pending') {
            // ★ Di tab penjurian: JANGAN tampilkan halaman pending/waiting,
            //    dan JANGAN jalankan timer nominasi. Halaman pending hanya milik tab nominasi.
            if (isOnPenjurianTab) {
                if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }
                return;
            }
            nomShowWaiting(res.nominations);
        } else {
            // ★ Jika di tab penjurian, JANGAN pindah ke nominasi
            if (isOnPenjurianTab) return;
            
            const hasRejected = (res.nominations || []).some(function(n) { return n.status === 'rejected'; });
            if (wasPending && hasRejected) {
                sessionStorage.removeItem('nom_was_pending');
                nomShowRejectedAnim(res.nominations);
            } else {
                nomShowNominasiPage(res.nominations);
            }
        }
    } catch (e) {
        if (attempt < 3) {
            await new Promise(r => setTimeout(r, attempt * 800));
            return checkNominasiStatus(attempt + 1);
        }
        // ★ Baca tab terbaru langsung (variabel guard kini hanya hidup di dalam try).
        if (currentJuriView !== 'penjurian') nomHide('nom-loading');
        showWarningModal([{type:'select', msg:'Gagal memeriksa status nominasi. Periksa koneksi internet Anda.'}]);
    }
}

function nomShowNominasiPage(rejectedNoms) {
    nomHide('nom-waiting'); nomHide('scoring-page'); nomShow('nom-page');
    // ★ Reset selection karena tidak ada pending (status = 'none')
    nomState.selected = new Set();
    nomState.defects = {};
    nomUpdateCount();
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

// ★ State untuk modal cancel
let pendingCancel = null; // {nominasiId, nomorTank}

function cancelNominasi(nominasiId, nomorTank) {
    pendingCancel = { nominasiId, nomorTank };
    document.getElementById('cancel-tank-num').textContent = nomorTank;
    // Reset state tombol modal
    var btnYes = document.getElementById('cancel-modal-btn-yes');
    btnYes.disabled = false;
    btnYes.innerHTML = '<i class="fas fa-check"></i> Ya, Batalkan';
    document.getElementById('cancel-modal-btn-no').disabled = false;
    document.getElementById('cancel-confirm-modal').classList.remove('hidden');
}

function hideCancelConfirm() {
    document.getElementById('cancel-confirm-modal').classList.add('hidden');
    pendingCancel = null;
}

async function confirmCancelNominasi() {
    if (!pendingCancel) return;
    const { nominasiId, nomorTank } = pendingCancel;

    // ★ Loading state pada tombol modal
    const btnYes = document.getElementById('cancel-modal-btn-yes');
    const btnNo = document.getElementById('cancel-modal-btn-no');
    btnYes.disabled = true;
    btnNo.disabled = true;
    btnYes.innerHTML = '<div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Membatalkan...';

    // ★ Pause auto-refresh sementara biar tidak bentrok request
    const wasRunning = !!nomState.autoRefreshTimer;
    if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }

    // ★ OPTIMISTIC: hapus item dari DOM dulu, langsung tutup modal — feedback instan
    const itemEl = document.getElementById('wait-nom-' + nominasiId);
    let itemBackup = null;
    let itemParent = null;
    let itemNext = null;
    if (itemEl) {
        itemBackup = itemEl.cloneNode(true);
        itemParent = itemEl.parentNode;
        itemNext = itemEl.nextSibling;
        itemEl.style.transition = 'all .25s';
        itemEl.style.opacity = '0';
        itemEl.style.transform = 'translateX(20px)';
        setTimeout(function() { if (itemEl.parentNode) itemEl.remove(); checkWaitingEmpty(); }, 250);
    }
    document.getElementById('cancel-confirm-modal').classList.add('hidden');
    pendingCancel = null;

    // ★ API call di background — user tidak menunggu
    try {
        const res = await apiFetch('/api/juri/cancel-nominasi', {
            method: 'POST',
            body: JSON.stringify({ nominasi_id: nominasiId })
        });
        if (!res.success) {
            // ROLLBACK: kembalikan item ke DOM
            if (itemBackup && itemParent) {
                itemBackup.style.opacity = '';
                itemBackup.style.transform = '';
                if (itemNext) itemParent.insertBefore(itemBackup, itemNext);
                else itemParent.appendChild(itemBackup);
            }
            showWarningModal([{ type: 'select', msg: res.message || 'Gagal membatalkan nominasi.' }]);
        }
    } catch (e) {
        // ROLLBACK
        if (itemBackup && itemParent) {
            itemBackup.style.opacity = '';
            itemBackup.style.transform = '';
            if (itemNext) itemParent.insertBefore(itemBackup, itemNext);
            else itemParent.appendChild(itemBackup);
        }
        showWarningModal([{ type: 'select', msg: 'Gagal menghubungi server. Coba lagi.' }]);
    }

    // ★ Restart auto-refresh kalau tadi running (terus pantau approval di background)
    if (wasRunning) {
        nomState.autoRefreshTimer = setInterval(checkNominasiStatus, 5000);
    }
}

// ★ Cek apakah list pending kosong setelah cancel — tampilkan empty state dengan tombol manual
function checkWaitingEmpty() {
    const list = document.getElementById('nom-waiting-list');
    if (!list) return;
    if (list.children.length === 0) {
        list.innerHTML =
            '<div class="text-center py-4">' +
                '<div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center" style="background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.30);">' +
                    '<i class="fas fa-inbox" style="color:#A78BFA;font-size:20px;"></i>' +
                '</div>' +
                '<p class="text-xs" style="color:var(--text-mid);">Tidak ada nominasi pending.</p>' +
            '</div>';
        // ★ Jangan stop auto-refresh — biarkan polling tetap jalan.
        //   Kalau grand juri approve nominasi lain, auto-refresh akan otomatis
        //   pindahkan ke scoring page. Tombol "Tambah Nominasi" di template
        //   sudah menyediakan cara manual untuk kembali pilih nominasi.
    }
}

async function viewPendingNominations() {
    nomShow('nom-loading');
    try {
        const res = await apiFetch('/api/juri/nominasi-status');
        const pendingNoms = (res.nominations || []).filter(n => n.status === 'pending');

        nomHide('nom-loading');

        if (pendingNoms.length > 0) {
            // ★ Paksa tampilkan halaman waiting meskipun ada yang sudah approved
            nomHide('nom-page');
            nomHide('scoring-page');
            nomHide('nom-approved-anim');
            nomHide('nom-rejected-anim');
            nomShowWaiting(res.nominations); // Fungsi ini akan filter & render yang pending saja, lalu nyalakan auto-refresh
        } else {
            // Jika tidak ada yang pending, beri tahu user
            showSuccessPopup('Status Nominasi', 'Tidak ada nominasi yang sedang pending. Semua sudah diproses atau belum diajukan.');
        }
    } catch (e) {
        nomHide('nom-loading');
        showWarningModal([{type:'select', msg:'Gagal memuat status nominasi. Cek koneksi internet Anda.'}]);
    }
}

async function goToNominasiPage() {
    if (nomState.autoRefreshTimer) {
        clearInterval(nomState.autoRefreshTimer);
        nomState.autoRefreshTimer = null;
    }

    sessionStorage.removeItem('nom_was_pending');

    nomHide('scoring-page');
    nomHide('nom-waiting');
    nomHide('nom-approved-anim');
    nomHide('nom-rejected-anim');
    nomShow('nom-page');

    // Jangan hapus pilihan manual yang sudah ada,
    // tapi pending akan tetap dipaksa selected oleh nomLoadData().
    nomShow('nom-loading');

    try {
        await nomLoadData();

        const pendingCount = (nomState.tanks || []).filter(function(t) {
            return t.is_pending;
        }).length;

        let bannerHtml = '';

        if (pendingCount > 0) {
            bannerHtml +=
                '<div style="background:rgba(34,211,238,.10);border:1px solid rgba(34,211,238,.30);border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:12px;color:var(--cyan-300);">' +
                    '<i class="fas fa-check-double" style="margin-right:6px;"></i>' +
                    '<b>' + pendingCount + ' tank pending otomatis terpilih</b> dan ditampilkan paling atas. Anda bisa menambah nominasi lain lalu kirim ulang.' +
                '</div>';
        }

        let bannerEl = document.getElementById('nom-info-banner');

        if (!bannerEl) {
            bannerEl = document.createElement('div');
            bannerEl.id = 'nom-info-banner';

            const page = document.getElementById('nom-page');
            if (page) page.insertBefore(bannerEl, page.firstChild);
        }

        bannerEl.innerHTML = bannerHtml;
        bannerEl.style.display = bannerHtml ? 'block' : 'none';
    } catch (e) {
        showWarningModal([{type:'select', msg:'Gagal membuka halaman nominasi. Periksa koneksi internet Anda.'}]);
    } finally {
        nomHide('nom-loading');
    }
}

function nomShowWaiting(nominations) {
    sessionStorage.setItem('nom_was_pending', '1');  // ★ tandai sudah masuk waiting
    nomHide('nom-page'); nomHide('scoring-page'); nomShow('nom-waiting');
    const list = document.getElementById('nom-waiting-list');
    list.innerHTML = nominations.filter(n => n.status === 'pending').map(n =>
        '<div class="flex items-center gap-3" id="wait-nom-' + n.id + '">' +
        '<div class="wait-tank-num">' + n.nomor_tank + '</div>' +
        '<div style="flex:1;min-width:0;">' +
        '<div style="font-size:10px;color:var(--text-mid);">' + n.kategori + (n.kelas ? ' — Kelas ' + n.kelas : '') + '</div></div>' +
        '<span class="wait-pending">Pending</span>' +
        '<button onclick="cancelNominasi(' + n.id + ', \'' + n.nomor_tank + '\')" title="Batalkan nominasi ini" style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25);padding:5px 10px;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:all .2s;font-family:inherit;"><i class="fas fa-xmark"></i> Batal</button>' +
        '</div>'
    ).join('');
    if (nomState.autoRefreshTimer) clearInterval(nomState.autoRefreshTimer);
    nomState.autoRefreshTimer = setInterval(checkNominasiStatus, 5000);
}
function nomShowApprovedAnim() {
    // ★ Stop auto-refresh — sudah approved, tidak perlu polling
    if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }
    nomHide('nom-page'); nomHide('nom-waiting'); nomHide('scoring-page'); nomHide('nom-rejected-anim'); nomHide('nom-loading');
    nomShow('nom-approved-anim');
    setTimeout(function() {
        nomHide('nom-approved-anim');
        nomHide('nom-page');
        nomHide('nom-waiting');
        nomShow('scoring-page');

        currentJuriView = 'penjurian';

        var navNom = document.getElementById('nav-btn-nominasi');
        var navPen = document.getElementById('nav-btn-penjurian');

        if (navNom) navNom.classList.remove('active');
        if (navPen) navPen.classList.add('active');

        initScoringPage();
        startScoringLockPolling();
    }, 2500);
}

function nomShowRejectedAnim(nominations) {
    if (nomState.autoRefreshTimer) { clearInterval(nomState.autoRefreshTimer); nomState.autoRefreshTimer = null; }
    nomHide('nom-page'); nomHide('nom-waiting'); nomHide('scoring-page'); nomHide('nom-approved-anim');

    // Customize subtitle berdasarkan jumlah tank yang ditolak
    const rejectedCount = (nominations || []).filter(function(n) { return n.status === 'rejected'; }).length;
    const sub = document.getElementById('nom-rejected-anim-sub');
    if (sub) {
        sub.textContent = rejectedCount > 0
            ? rejectedCount + ' tank ditolak — silakan pilih ulang...'
            : 'Silakan pilih ulang tank...';
    }

    nomShow('nom-rejected-anim');
    setTimeout(function() {
        nomHide('nom-rejected-anim');
        nomShowNominasiPage(nominations);
    }, 3000);
}

async function nomLoadData() {
    var icon = document.getElementById('nom-refresh-icon');
    var grid = document.getElementById('nom-grid');

    if (icon) icon.classList.add('animate-spin');

    if (grid) {
        grid.innerHTML =
            '<div class="glass-card" style="grid-column:1/-1;padding:28px;text-align:center;">' +
                '<div class="w-10 h-10 border-4 rounded-full animate-spin mx-auto mb-3" style="border-color:var(--glass-strong);border-top-color:var(--cyan-400);"></div>' +
                '<p class="text-xs font-bold" style="color:var(--text-mid);">Memuat pilihan tank nominasi...</p>' +
            '</div>';
    }

    try {
        const res = await apiFetch('/api/juri/tanks-nominasi?_t=' + Date.now());

        nomState.tanks = res.tanks || [];
        nomState.kategoris = res.kategoris || [];
        nomState.kelass = res.kelass || [];

        // Pending harus selalu otomatis terpilih,
        // walaupun sebelumnya user sudah memilih tank lain.
        const pendingIds = res.pending_ikan_ids || [];
        pendingIds.forEach(function(id) {
            nomState.selected.add(parseInt(id, 10));
        });

        // Restore defect dari pending.
        var pd = res.pending_defects || {};
        Object.keys(pd).forEach(function(id) {
            nomState.defects[parseInt(id, 10)] = pd[id];
        });

        // ★ Pulihkan pilihan draft (belum dikirim) dari localStorage — tahan refresh/keluar halaman.
        loadNomDraft();

        // Paksa HANYA pending tampil paling atas, lalu urut nomor tank.
        // Selected (sekadar dipilih) sengaja TIDAK ikut menentukan urutan.
        nomState.tanks.sort(function(a, b) {
            var ap = a.is_pending ? 1 : 0;
            var bp = b.is_pending ? 1 : 0;
            if (ap !== bp) return bp - ap;

            return (parseInt(a.nomor_tank || 0, 10) || 0) - (parseInt(b.nomor_tank || 0, 10) || 0);
        });

        nomUpdateCount();
        nomRenderFilterBtns();
        nomRenderGrid();
    } catch (e) {
        if (grid) {
            grid.innerHTML =
                '<div class="glass-card" style="grid-column:1/-1;padding:28px;text-align:center;color:var(--danger);">' +
                    '<i class="fas fa-triangle-exclamation text-2xl mb-2"></i>' +
                    '<p class="text-xs font-bold">Gagal memuat data tank nominasi.</p>' +
                '</div>';
        }
        showWarningModal([{type:'select', msg:'Gagal memuat data tank. Periksa koneksi internet Anda.'}]);
    }

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
        if (nomState.excludedTankIds.has(t.id)) return false;
        if (nomState.filterKat && t.kategori !== nomState.filterKat) return false;
        if (nomState.filterKelas && t.kelas !== nomState.filterKelas) return false;
        if (nomState.searchTerm) {
            const s = nomState.searchTerm.toLowerCase();
            if (!String(t.nomor_tank).includes(s) && !t.kategori.toLowerCase().includes(s) && !t.kelas.toLowerCase().includes(s)) return false;
        }
        return true;
    });
}

// ★ Rangkum status defect untuk satu tank (untuk styling tombol Defect di card)
function nomDefectSummary(tankId) {
    const d = nomState.defects[tankId];
    if (!d) return { count: 0, level: 'none' };

    let count = 0;
    const ts = { defects: {} };
    ['head','face','body','finnage'].forEach(function(p) {
        const vals = d['raw_'+p+'_penalty'] || ['0'];
        ts.defects['raw_'+p+'_penalty'] = vals;
        vals.forEach(function(v) { if (v && v !== '0') count++; });
    });
    if (count === 0) return { count: 0, level: 'none' };

    const ev = evalDefects(ts);
    const anyMayor = ['head','face','body','finnage'].some(function(p) { return ev[p] === '30%'; });
    return { count: count, level: anyMayor ? 'mayor' : 'minor' };
}

function nomRenderGrid() {
    const filtered = nomGetFiltered();
    const grid = document.getElementById('nom-grid');
    const empty = document.getElementById('nom-grid-empty');

    if (filtered.length === 0) {
        grid.innerHTML = '';
        nomShow('nom-grid-empty');
        return;
    }

    nomHide('nom-grid-empty');

    const sorted = filtered.slice().sort(function(a, b) {
        var ap = a.is_pending ? 1 : 0;
        var bp = b.is_pending ? 1 : 0;
        if (ap !== bp) return bp - ap;

        // Tank yang sekadar dipilih (selected) TIDAK naik ke atas — tetap di posisi nomor tank-nya.
        // Hanya PENDING yang dipaksa di atas (sudah ditangani blok is_pending di atas).
        return (parseInt(a.nomor_tank || 0, 10) || 0) - (parseInt(b.nomor_tank || 0, 10) || 0);
    });

    grid.innerHTML = sorted.map(function(t) {
        const sel = nomState.selected.has(t.id);

        let pendingBadge = '';
        if (t.is_pending) {
            pendingBadge =
                '<div style="margin-top:6px;display:inline-flex;align-items:center;gap:4px;padding:3px 7px;border-radius:7px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.28);color:var(--gold-300);font-size:9px;font-weight:900;letter-spacing:.03em;">' +
                    '<i class="fas fa-hourglass-half"></i> PENDING' +
                '</div>';
        }

        let defectBtn = '';
        if (sel) {
            const sum = nomDefectSummary(t.id);
            let klass = 'nom-defect-btn nom-defect-empty';
            let label = '<i class="fas fa-circle-exclamation"></i> Tandai Defect';

            if (sum.level === 'minor') {
                klass = 'nom-defect-btn nom-defect-minor';
                label = '<i class="fas fa-circle-exclamation"></i> Defect Minor (' + sum.count + ')';
            } else if (sum.level === 'mayor') {
                klass = 'nom-defect-btn nom-defect-mayor';
                label = '<i class="fas fa-triangle-exclamation"></i> Defect Mayor (' + sum.count + ')';
            }

            defectBtn = '<button type="button" class="' + klass + '" onclick="event.stopPropagation();openNomDefectAll(' + t.id + ')">' + label + '</button>';
        }

        return '<div class="p-3 cursor-pointer ' + (sel ? 'selected-card' : '') + '" onclick="nomToggle(' + t.id + ')">' +
            '<div class="flex justify-between items-start mb-3">' +
                '<div>' +
                    '<div class="tank-num-badge">' + t.nomor_tank + '</div>' +
                    pendingBadge +
                '</div>' +
                '<button class="star-btn" onclick="event.stopPropagation();nomToggle(' + t.id + ')">' +
                    '<i class="' + (sel ? 'fas' : 'fa-regular') + ' fa-star"></i>' +
                '</button>' +
            '</div>' +
            '<div class="flex flex-col gap-1">' +
                '<div class="cat-badge">' + t.kategori + '</div>' +
                kelasDisplay(t.kategori, t.kelas) +
            '</div>' +
            defectBtn +
        '</div>';
    }).join('');
}

// ═══════════════════════════════════════════════════════════════
// NOM DEFECT ALL-IN-ONE MODAL
// ═══════════════════════════════════════════════════════════════
let nomDefectAllCtx = null; // { tankId, working: { raw_*_penalty: [...] } }

function openNomDefectAll(tankId) {
    const existing = nomState.defects[tankId] || {};
    nomDefectAllCtx = {
        tankId: tankId,
        working: {
            raw_head_penalty:    [...(existing.raw_head_penalty    || ['0'])],
            raw_face_penalty:    [...(existing.raw_face_penalty    || ['0'])],
            raw_body_penalty:    [...(existing.raw_body_penalty    || ['0'])],
            raw_finnage_penalty: [...(existing.raw_finnage_penalty || ['0'])],
        },
    };
    const tank = nomState.tanks.find(function(x) { return x.id === tankId; });
    document.getElementById('nom-defect-all-tank').textContent = tank ? 'No ' + tank.nomor_tank : '#' + tankId;
    renderNomDefectAllBody();
    document.getElementById('modal-nom-defect-all').classList.remove('hidden');
}

function renderNomDefectAllBody() {
    if (!nomDefectAllCtx) return;
    const body = document.getElementById('nom-defect-all-body');
    const parts = ['head','face','body','finnage'];

    // Eval status saat ini (untuk badge Minor/Mayor di header tiap komponen)
    const ts = { defects: {} };
    parts.forEach(function(p) {
        ts.defects['raw_'+p+'_penalty'] = nomDefectAllCtx.working['raw_'+p+'_penalty'] || ['0'];
    });
    const ev = evalDefects(ts);

    body.innerHTML = '<div class="nom-defect-all-grid">' + parts.map(function(p) {
        const partKey = 'raw_'+p+'_penalty';
        const currentVals = nomDefectAllCtx.working[partKey] || ['0'];
        const groups = defectOpts(p);
        const partLabel = DEFECT_MAP[p].label;

        let headerTag = '';
        if (ev[p] === '30%')      headerTag = '<span class="nom-da-tag nom-da-tag-mayor">Mayor −30%</span>';
        else if (ev[p] === '10%') headerTag = '<span class="nom-da-tag nom-da-tag-minor">Minor −10%</span>';
        else                      headerTag = '<span class="nom-da-tag nom-da-tag-aman">Aman</span>';

        const sections = groups.map(function(g) {
            const opts = g.options.map(function(o) {
                const checked = currentVals.includes(o.v);
                const safeVal = String(o.v).replace(/'/g, "\\'");
                return '<label class="nom-da-opt' + (checked ? ' checked' : '') + '">' +
                    '<input type="checkbox" value="' + o.v + '"' + (checked ? ' checked' : '') + ' '
                    + 'onchange="onNomDefectAllCheck(\'' + p + '\', this)"><span>' + o.l + '</span></label>';
            }).join('');
            return '<div class="nom-da-section">' +
                '<div class="nom-da-section-title">' + g.label + '</div>' +
                '<div class="nom-da-opts">' + opts + '</div>' +
            '</div>';
        }).join('');

        return '<div class="nom-da-part">' +
            '<div class="nom-da-part-head">' +
                '<h4>' + partLabel + '</h4>' +
                headerTag +
            '</div>' +
            sections +
        '</div>';
    }).join('') + '</div>';
}

function onNomDefectAllCheck(partKey, cb) {
    if (!nomDefectAllCtx) return;
    const k = 'raw_'+partKey+'_penalty';
    let vals = [...(nomDefectAllCtx.working[k] || ['0'])];
    if (cb.value === '0') {
        vals = ['0'];
    } else {
        vals = vals.filter(function(v) { return v !== '0'; });
        if (vals.includes(cb.value)) {
            vals = vals.filter(function(v) { return v !== cb.value; });
        } else {
            vals.push(cb.value);
        }
    }
    if (vals.length === 0) vals = ['0'];
    nomDefectAllCtx.working[k] = vals;
    renderNomDefectAllBody(); // re-render supaya tag Minor/Mayor & checked state ter-update
}

function resetNomDefectAll() {
    if (!nomDefectAllCtx) return;
    nomDefectAllCtx.working = {
        raw_head_penalty:    ['0'],
        raw_face_penalty:    ['0'],
        raw_body_penalty:    ['0'],
        raw_finnage_penalty: ['0'],
    };
    renderNomDefectAllBody();
}

function saveNomDefectAll() {
    if (!nomDefectAllCtx) return;
    const tankId = nomDefectAllCtx.tankId;
    const w = nomDefectAllCtx.working;
    const hasAny = ['head','face','body','finnage'].some(function(p) {
        const v = w['raw_'+p+'_penalty'] || ['0'];
        return v.some(function(x) { return x && x !== '0'; });
    });
    if (hasAny) {
        nomState.defects[tankId] = {
            raw_head_penalty:    w.raw_head_penalty,
            raw_face_penalty:    w.raw_face_penalty,
            raw_body_penalty:    w.raw_body_penalty,
            raw_finnage_penalty: w.raw_finnage_penalty,
        };
    } else {
        delete nomState.defects[tankId];
    }
    nomDefectAllCtx = null;
    document.getElementById('modal-nom-defect-all').classList.add('hidden');
    saveNomDraft();
    nomRenderGrid();
}

function closeNomDefectAll() {
    nomDefectAllCtx = null;
    document.getElementById('modal-nom-defect-all').classList.add('hidden');
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
    saveNomDraft();
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
    const oldHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Mengirim...';

    try {
        const defectsPayload = {};

        nomState.selected.forEach(function(id) {
            const d = nomState.defects[id];
            if (d) defectsPayload[id] = d;
        });

        const res = await apiFetch('/api/juri/submit-nominasi', {
            method: 'POST',
            body: JSON.stringify({
                ikan_ids: Array.from(nomState.selected),
                defects: defectsPayload
            })
        });

    if (res.success) {
        const createdCount = (res.count === undefined || res.count === null)
            ? 1
            : parseInt(res.count || 0, 10);

        nomClosePreview();

        if (createdCount > 0) {
            clearNomDraft();
            sessionStorage.setItem('nom_was_pending', '1');

            showSuccessPopup('Nominasi Terkirim!', res.message);

            // Refresh list supaya pending baru langsung selected dan tampil paling atas.
            await nomLoadData();

            setTimeout(function() {
                viewPendingNominations();
            }, 600);
        } else {
            showWarningModal([{
                type: 'select',
                msg: res.message || 'Tidak ada nominasi baru yang masuk pending. Kemungkinan semua tank yang dipilih sudah approved untuk akun juri ini.'
            }]);

            await nomLoadData();
        }
    } else {
        showWarningModal([{type:'select', msg: res.message || 'Gagal mengirim nominasi.'}]);
    }
    } catch (e) {
        showWarningModal([{type:'select', msg:'Gagal mengirim. Periksa koneksi internet Anda.'}]);
    }

    btn.disabled = false;
    btn.innerHTML = oldHtml || '<i class="fas fa-paper-plane"></i> Kirim Fix';
}

document.getElementById('nom-search')?.addEventListener('input', function(e) {
    nomState.searchTerm = e.target.value; nomUpdateFilterInfo(); nomRenderGrid();
});
document.getElementById('cancel-confirm-modal')?.addEventListener('click', function(e) {
    if (e.target === this && !document.getElementById('cancel-modal-btn-yes').disabled) {
        hideCancelConfirm();
    }
});
document.getElementById('nom-preview-modal')?.addEventListener('click', function(e) {
    if (e.target === this) nomClosePreview();
});

document.getElementById('modal-nom-defect-all')?.addEventListener('click', function(e) {
    if (e.target === this) closeNomDefectAll();
});

// ═══════════════════════════════════════════════════════════════
// KONSTANTA
// ═══════════════════════════════════════════════════════════════
// KODE BARU
const MINOR_DEFECTS = [
    "Kutil",
    "Bibir Miring",                      // legacy
    "Bibir Miring (kasat mata)",
    "Katarak",
    "Bibir Tidak Menutup & Selaput Bergerak",
    "Abses / Luka",
    "Fintail Bleaching",                 // legacy
    "Fintail Bleaching / Transparan",
    "Pangkal Ekor Naik/Trn",             // legacy
    "Pangkal Ekor Naik atau Turun",
    "Dayung Tdk Seimbang",               // legacy
    "Sirip Dayung Tidak Seimbang"
];

const MAYOR_DEFECTS = [
    "Bagian Bibir Hilang",
    "Mulut Terbuka Terus",                                  // legacy
    "Muka Miring",
    "Pangkal Bengkok/Patah",                                // legacy
    "Pangkal Bengkok / Melintir",
    "Fin/Tulang Hilang 1 Ruas"
];

// ★ Hanya label BARU yang dimunculkan ke juri saat memilih defect
const DEFECT_MAP = {
    head:    { label:'Head',    minor:['Kutil'], mayor:[] },
    face:    { label:'Face',    minor:['Bibir Miring (kasat mata)','Katarak','Bibir Tidak Menutup & Selaput Bergerak'], mayor:['Bagian Bibir Hilang','Muka Miring'] },
    body:    { label:'Body',    minor:['Kutil','Abses / Luka'], mayor:[] },
    finnage: { label:'Finnage', minor:['Kutil','Fintail Bleaching / Transparan','Pangkal Ekor Naik atau Turun','Sirip Dayung Tidak Seimbang'], mayor:['Fin/Tulang Hilang 1 Ruas','Pangkal Bengkok / Melintir'] },
};

function normalizeDefectName(v) {
    return String(v || '').replace(/\s+Sempurna/g, '').trim();
}

function normalizeDefectArr(v) {
    if (!v) return ['0'];
    if (typeof v === 'string') v = [v];
    if (!Array.isArray(v)) return ['0'];

    var arr = v
        .map(normalizeDefectName)
        .filter(function(x) { return x !== ''; });

    arr = Array.from(new Set(arr));

    if (arr.length === 0) return ['0'];

    if (arr.indexOf('0') !== -1 && arr.length > 1) {
        arr = arr.filter(function(x) { return x !== '0'; });
    }

    return arr.length ? arr : ['0'];
}

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

        appData.my_scores.forEach(function(s) {
            scoredIds[parseInt(s.ikan_id, 10)] = true;
        });

        // ★ Pulihkan SEMUA draft yang belum final di server.
        //   Sengaja TIDAK disaring pakai available_tanks: kalau penugasan/sesi
        //   sempat berubah, tank bisa hilang sebentar dari available_tanks dan
        //   draftnya jadi tidak ikut ter-restore (kelihatan seperti "terhapus").
        //   Form hanya merender tank yang tersedia, jadi draft ekstra di memori
        //   tidak mengganggu — tapi aman & otomatis muncul lagi saat tank tersedia.
        Object.keys(saved).forEach(function(id) {
            var nid = parseInt(id, 10);
            if (scoredIds[nid]) return; // sudah tersimpan permanen di server
            tankScores[id] = saved[id];
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

function pruneUnavailableTankScores() {
    // ★ PENTING: JANGAN hapus draft dari localStorage di sini.
    //   available_tanks bisa KOSONG sementara (sesi dikunci, penugasan juri
    //   di-reset/diubah, atau request data sesaat gagal). Kalau saat itu kita
    //   panggil removeDraft(), SEMUA pekerjaan juri yang belum disimpan hilang
    //   permanen — inilah penyebab "data lokal tiba-tiba terhapus".
    //   Cukup rapikan dari memori; localStorage dipertahankan & muncul lagi
    //   begitu tanknya kembali tersedia.
    if (!appData.available_tanks || appData.available_tanks.length === 0) {
        return; // jangan utak-atik apa pun saat daftar tank kosong/transient
    }

    var validIds = {};
    appData.available_tanks.forEach(function(t) {
        validIds[parseInt(t.id, 10)] = true;
    });

    Object.keys(tankScores || {}).forEach(function(id) {
        var nid = parseInt(id, 10);
        if (!validIds[nid]) {
            delete tankScores[id]; // hanya dari memori — localStorage TIDAK disentuh
        }
    });
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
    parts.forEach(p => { status[p] = {hasMinor:false, mayor:false, items:[]}; });
    parts.forEach(p => {
        const defs = normalizeDefectArr(ts.defects['raw_'+p+'_penalty'] || ['0']);
        defs.forEach(d => {
            if (d && d !== '0') {
                status[p].items.push(d);
                if (MINOR_DEFECTS.includes(d)) { status[p].hasMinor = true; }
                if (MAYOR_DEFECTS.includes(d)) { status[p].mayor = true; }
            }
        });
    });

    // ★ FIX: hitung jumlah KOMPONEN yang punya minor (bukan total minor)
    let componentsWithMinor = 0;
    parts.forEach(p => { if (status[p].hasMinor) componentsWithMinor++; });
    const isGlobalMayor = componentsWithMinor >= 3;

    const results = {};
    parts.forEach(p => {
        if (status[p].items.length > 0) {
            // Mayor jika: ada defect mayor langsung, ATAU punya minor tapi ≥3 komponen global punya minor
            const isMayor = status[p].mayor || (status[p].hasMinor && isGlobalMayor);
            results[p] = isMayor ? '30%' : '10%';
        } else { results[p] = ''; }
    });
    return results;
}

// ★ Tambahan: Cek apakah komponen dikunci berdasarkan kategori
function isFieldLocked(kategori, fieldKey) {
    if (!kategori) return false;
    if ((kategori === 'Freemarking' || kategori === 'Goldenbase') && fieldKey.startsWith('marking.')) return true;
    if (kategori === 'Klasik' && fieldKey.startsWith('pearl.')) return true;
    return false;
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
            const locked = isFieldLocked(tank.kategori, f.key);
            const val = locked ? '' : getVal(tank.id, f.key);
            if (locked) {
                return '<td class="p-1.5"><div class="w-full px-2 py-2 border rounded text-center font-mono font-bold text-xs flex items-center justify-center gap-1.5" style="border-color:var(--bd-1);background:rgba(255,255,255,0.02);color:var(--text-faint);"><i class="fas fa-lock text-[9px] opacity-50"></i> -</div></td>';
            }
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
        return '<tr><td class="px-2 py-2 font-bold text-center text-xs sticky left-0 z-10" style="background:#112234 !important;border-right:1px solid var(--bd-1);color:var(--cyan-300);">'+(t?t.nomor_tank:'-')+'</td><td class="px-2 py-2" style="border-right:1px solid var(--bd-1);"><div style="font-size:10px;font-weight:700;color:var(--text-hi);">'+(t?.kategori||'-')+'</div><div style="font-size:9px;color:var(--cyan-400);font-weight:700;">KLS:'+(s.kelas||'-')+'</div></td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+(nd.overall?.impression||'-')+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('head',['size','bentuk'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+(nd.face?.face||'-')+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('body',['bentuk','proporsi','pangkal'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('marking',['fullness','contrast','bentuk'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('pearl',['shinning','fullness','bentuk'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('color',['komposisi','kecerahan','fullness'])+'</td><td class="px-2 py-2 text-center font-mono text-[11px]" style="border-right:1px solid var(--bd-1);color:var(--text-hi);">'+fmt('finnage',['bentuk','kecerahan'])+'</td><td class="px-2 py-2 text-left align-top min-w-[100px] whitespace-normal" style="border-right:1px solid var(--bd-1);">'+defHtml+'</td><td class="px-2 py-2 text-center"><button onclick="lihatDetail('+s.id+')" class="live-detail-btn"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>Detail</button></td></tr>';
    }).join('');
}

function populateFilter() {
    document.getElementById('filter-kategori').innerHTML = '<option value="">Cek Kategori</option>' + ['Bonsai','Cencu','Chingwa','Freemarking','Goldenbase','Jumbo','Klasik'].map(c => '<option value="'+c+'">'+c+'</option>').join('');
    document.getElementById('filter-kelas').innerHTML = '<option value="">Cek Kelas</option>' + ['A','B','C','D','E'].map(c => '<option value="'+c+'">Kelas '+c+'</option>').join('');
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
function openDefect(tankId, partKey, context) {
    context = context || 'scoring';
    let vals;
    if (context === 'nomination') {
        const d = nomState.defects[tankId] || {};
        vals = d['raw_'+partKey+'_penalty'] || ['0'];
    } else if (context === 'revisi') {
        vals = revisiState.defects?.['raw_'+partKey+'_penalty'] || ['0'];
    } else {
        vals = tankScores[tankId]?.defects?.['raw_'+partKey+'_penalty'] || ['0'];
    }
    defectModal = { tankId, partKey, values: [...vals], context };
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
    const { tankId, partKey, values, context } = defectModal;

    if (context === 'revisi') {
        revisiState.defects['raw_'+partKey+'_penalty'] = values;
        defectModal = null;
        document.getElementById('modal-defect').classList.add('hidden');
        renderRevisiForm();
        return;
    }

    if (context === 'nomination') {
        if (!nomState.defects[tankId]) {
            nomState.defects[tankId] = {
                raw_head_penalty:    ['0'],
                raw_face_penalty:    ['0'],
                raw_body_penalty:    ['0'],
                raw_finnage_penalty: ['0'],
            };
        }
        nomState.defects[tankId]['raw_'+partKey+'_penalty'] = values;
        defectModal = null;
        document.getElementById('modal-defect').classList.add('hidden');
        saveNomDraft();
        nomRenderGrid();
        return;
    }

    if (!tankScores[tankId]) {
        defectModal = null;
        document.getElementById('modal-defect').classList.add('hidden');
        return;
    }
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
        SCORING_GROUPS.forEach(function(group) { group.fields.forEach(function(field) { if (field.type === 'defect') return; if (isFieldLocked(tank.kategori, field.key)) return; if (getVal(tank.id, field.key) === '') { isComplete = false; missingFields.push(group.title + ' > ' + field.label); } }); });
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

async function loadJuriData() {
    try {
        const res = await apiFetch('/api/juri/data?_t=' + Date.now());
        appData.available_tanks    = res.available_tanks || [];
        appData.my_scores          = res.my_scores || [];
        appData.all_scored         = res.all_scored || {};
        appData.scored_counts      = res.scored_counts || {};
        appData.nomination_defects = res.nomination_defects || {};

        initTankScores(appData.available_tanks);

        // ★ Restore draft DULU agar pre-fill di bawah ini bisa diputuskan
        //    berdasarkan state final tankScores (draft + default).
        loadDraft();

        // ★ Pre-fill defect dari nominasi yang sudah approved.
        //    Aturan: hanya menimpa kalau current value masih default ['0']/kosong,
        //    dan nominasi punya defect nyata (selain '0'). Edit manual juri TIDAK hilang.
        Object.keys(appData.nomination_defects).forEach(function(ikanId) {
            const nd = appData.nomination_defects[ikanId];
            if (!nd || !tankScores[ikanId]) return;

            // Defensive: kalau draft lama tidak punya struktur defects, init dulu.
            if (!tankScores[ikanId].defects) {
                tankScores[ikanId].defects = {
                    raw_head_penalty:    ['0'],
                    raw_face_penalty:    ['0'],
                    raw_body_penalty:    ['0'],
                    raw_finnage_penalty: ['0']
                };
            }

            ['head','face','body','finnage'].forEach(function(p) {
                const k = 'raw_'+p+'_penalty';
                const cur = tankScores[ikanId].defects[k];
                const isCurDefault =
                    !cur ||
                    cur.length === 0 ||
                    (cur.length === 1 && cur[0] === '0');
                const ndArr = normalizeDefectArr(nd[k]);
                const hasReal = Array.isArray(ndArr) && ndArr.some(function(v) {
                    return v && v !== '0';
                });
                if (isCurDefault && hasReal) {
                    tankScores[ikanId].defects[k] = ndArr.slice();
                }
            });
        });

        populateFilter(); renderFormTable(); renderLiveTable();
    } catch(e) { showWarningModal([{type:'select',msg:'Gagal memuat data dari server. Periksa koneksi internet Anda.'}]); }
}

function initScoringPage() {
    activeTab = 'overall';
    showGuideline = false;
    isConfirmed = false;
    isSubmitting = false;

    renderTabs();
    updateGuidelineBtn();

    document.getElementById('guideline-panel').classList.add('hidden');

    // Pakai loader penjurian yang membaca status approved terbaru.
    loadJuriDataForPenjurian();
}

function forceHidePage(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hidden');
    el.style.display = 'none';
}

function forceShowPage(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('hidden');
    el.style.display = '';
}

function switchJuriView(view) {
    currentJuriView = view;

    ['nominasi', 'penjurian', 'foto', 'revisi'].forEach(function (v) {
        var btn = document.getElementById('nav-btn-' + v);
        if (btn) btn.classList.toggle('active', view === v);
    });

    if (typeof window.setJuriTopbar === 'function') {
        window.setJuriTopbar(view);
    }

    document.body.classList.remove('sidebar-open');

    if (nomState.autoRefreshTimer) {
        clearInterval(nomState.autoRefreshTimer);
        nomState.autoRefreshTimer = null;
    }

    stopScoringLockPolling();

    [
        'nom-page',
        'nom-waiting',
        'nom-approved-anim',
        'nom-rejected-anim',
        'nom-loading',
        'scoring-page',
        'foto-page',
        'revisi-page'
    ].forEach(forceHidePage);

    if (view === 'nominasi') {
        forceShowPage('nom-loading');
        checkNominasiStatus();
        return;
    }

    if (view === 'penjurian') {
        forceShowPage('scoring-page');
        loadJuriDataForPenjurian();
        startScoringLockPolling();
        return;
    }

    if (view === 'foto') {
        forceShowPage('foto-page');
        loadFotoIkanList();
        return;
    }

    if (view === 'revisi') {
        forceShowPage('revisi-page');
        loadRevisiView();
        return;
    }
}

// Pastikan function bisa dipanggil oleh onclick="" di HTML.
window.switchJuriView = switchJuriView;

/* ════════ FOTO IKAN (JURI) — upload + galeri, mandiri (tidak tergantung apiFetch layout) ════════ */
(function(){
    function _csrf(){ var m=document.querySelector('meta[name=csrf-token]'); return m?m.content:''; }
    function _el(id){ return document.getElementById(id); }
    var jf = { currentId:null, stream:null, queue:[], usedBytes:0, maxBytes:3*1024*1024, canUpload:false, canUploadServer:false, locked:false, tanks:[] };

    /* ---------- VIEW LIST FOTO (ikan approved) ---------- */
    window.loadFotoIkanList = function(){
        var L=_el('foto-loading-list'), wrap=_el('foto-list'), empty=_el('foto-list-empty');
        if(L) L.style.display='block'; if(wrap) wrap.innerHTML=''; if(empty) empty.classList.add('hidden');
        fetch('/api/juri/foto-tanks', {headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();})
        .then(function(d){
            if(L) L.style.display='none';
            if(!d||!d.success){ wrap.innerHTML='<div class="glass-card p-4 text-center text-xs" style="color:#fca5a5;">Gagal memuat data.</div>'; return; }
            var ov=_el('foto-lock-overlay');
            if(ov){ if(d.foto_page_unlocked===false){ ov.classList.remove('hidden'); } else { ov.classList.add('hidden'); } }
            jf.tanks=d.tanks||[]; renderFotoList('');
        })
        .catch(function(){ if(L) L.style.display='none'; wrap.innerHTML='<div class="glass-card p-4 text-center text-xs" style="color:#fca5a5;">Gagal memuat data.</div>'; });
    };
    window.fotoListSearch = function(v){ renderFotoList(v||''); };

    function renderFotoList(q){
        q=(q||'').toLowerCase().trim();
        var wrap=_el('foto-list'), empty=_el('foto-list-empty');
        var list=jf.tanks.filter(function(t){ if(!q) return true; return String(t.nomor_tank).indexOf(q)!==-1 || (t.kategori||'').toLowerCase().indexOf(q)!==-1 || (String(t.kelas||'')).toLowerCase().indexOf(q)!==-1; });
        if(!list.length){ wrap.innerHTML=''; if(empty) empty.classList.remove('hidden'); return; }
        if(empty) empty.classList.add('hidden');
        wrap.innerHTML=list.map(function(t){
            var statusChip = t.foto_count>0
                ? '<span style="padding:3px 9px;border-radius:8px;background:rgba(16,185,129,.14);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;font-size:10px;font-weight:900;"><i class="fas fa-camera"></i> '+t.foto_count+' Foto</span>'
                : '<span style="padding:3px 9px;border-radius:8px;background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-low);font-size:10px;font-weight:900;">BELUM ADA</span>';
            var reFlag = t.reupload_requested ? '<span style="margin-left:6px;padding:2px 7px;border-radius:7px;background:rgba(245,158,11,.14);border:1px solid rgba(245,158,11,.3);color:var(--gold-300);font-size:9px;font-weight:900;"><i class="fas fa-rotate"></i> DIMINTA ULANG</span>' : '';
            var btnDetail = '<button onclick="openJuriFotoModal('+t.id+')" class="fotorow-btn" style="background:rgba(34,211,238,.10);border:1px solid rgba(34,211,238,.28);color:var(--cyan-300);"><i class="fas fa-eye"></i> Detail</button>';
            var btnUpload = '';
            if(t.can_upload){
                var lbl = t.reupload_requested ? 'Upload Ulang' : (t.foto_count>0 ? 'Ganti Foto' : 'Upload');
                btnUpload = '<button onclick="openJuriFotoModal('+t.id+')" class="fotorow-btn" style="background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));border:1px solid transparent;color:#fff;"><i class="fas fa-cloud-arrow-up"></i> '+lbl+'</button>';
            } else if(t.foto_count>0){
                btnUpload = '<span class="fotorow-btn" style="background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.28);color:var(--gold-300);cursor:default;"><i class="fas fa-lock"></i> Terkunci</span>';
            }
            return '<div class="glass-card" style="padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">'
                +'<div style="min-width:0;display:flex;flex-direction:column;gap:3px;">'
                    +'<div style="font-size:14px;font-weight:800;color:var(--text-hi);">Tank '+t.nomor_tank+reFlag+'</div>'
                    +'<div style="font-size:11px;color:var(--text-mid);">'+(t.kategori||'-')+(t.kelas?(' · Kelas '+t.kelas):'')+' · '+(t.nama_peserta||'-')+'</div>'
                +'</div>'
                +'<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">'+statusChip+btnDetail+btnUpload+'</div>'
            +'</div>';
        }).join('');
    }

    /* ---------- MODAL GALERI + UPLOAD ---------- */
    window.openJuriFotoModal = function(id){
        jf.currentId=id; jf.canUpload=false; jfStopCamera(); jfClearQueue(); jfHideStatus();
        _el('jf-modal').classList.remove('hidden');
        _el('jf-gallery').innerHTML=''; _el('jf-desc').textContent='Memuat info foto...';
        _el('jf-upload-actions').style.display='none'; _el('jf-camera').style.display='none';
        _el('jf-quota').style.display='none'; _el('jf-fullnote').style.display='none';
        fetch('/api/juri/ikan-foto/'+id, {headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();})
        .then(function(d){
            if(!d||!d.success){ _el('jf-desc').textContent=(d&&d.message)||'Gagal memuat foto.'; return; }
            _el('jf-desc').textContent='Tank '+(d.nomor_tank||'-')+' · '+(d.kategori||'');
            jfRenderGallery(d);
        })
        .catch(function(){ _el('jf-desc').textContent='Gagal memuat foto.'; });
    };
    window.closeJuriFotoModal = function(){
        jfStopCamera(); jfClearQueue(); _el('jf-modal').classList.add('hidden');
        var fp=_el('foto-page'); if(fp && !fp.classList.contains('hidden') && window.loadFotoIkanList) loadFotoIkanList();
    };

    function jfBadge(role){
        if(role==='juri')  return '<span style="position:absolute;left:5px;top:5px;padding:2px 6px;border-radius:6px;background:rgba(34,211,238,.9);color:#04121e;font-size:8px;font-weight:900;">JURI</span>';
        if(role==='admin') return '<span style="position:absolute;left:5px;top:5px;padding:2px 6px;border-radius:6px;background:rgba(124,58,237,.9);color:#fff;font-size:8px;font-weight:900;">ADMIN</span>';
        return '<span style="position:absolute;left:5px;top:5px;padding:2px 6px;border-radius:6px;background:rgba(148,163,184,.9);color:#0b1220;font-size:8px;font-weight:900;">LAMA</span>';
    }
    function jfRenderGallery(d){
        var g=_el('jf-gallery'); var fotos=d.fotos||[];
        if(fotos.length){
            g.innerHTML='<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">'+fotos.map(function(f){
                return '<div style="position:relative;border-radius:12px;overflow:hidden;border:1px solid var(--bd-2);">'+jfBadge(f.role)+'<img src="'+f.url+'" onclick="window.open(this.src,\'_blank\')" style="width:100%;height:92px;object-fit:cover;display:block;cursor:zoom-in;"></div>';
            }).join('')+'</div>';
        } else {
            g.innerHTML='<div style="padding:20px 14px;border:1px dashed var(--bd-2);border-radius:16px;color:var(--text-mid);font-size:13px;text-align:center;"><i class="fas fa-fish" style="font-size:24px;color:var(--cyan-400);display:block;margin-bottom:8px;"></i>Belum ada foto untuk tank ini.</div>';
        }
        jf.usedBytes=d.used_bytes||0; if(d.max_bytes) jf.maxBytes=d.max_bytes;
        jf.canUploadServer = !!d.can_upload;   // ★ izin dari server (kunci juri)
        jf.locked = !!d.locked;                // ★ sudah dikirim → terkunci utk juri
        jfRefreshQuota();
    }
    function jfQueueBytes(){ return jf.queue.reduce(function(s,it){return s+(it.size||0);},0); }
    function jfRefreshQuota(){
        var quota=_el('jf-quota'); var act=_el('jf-upload-actions'); var full=_el('jf-fullnote');

        // ★ Terkunci: foto sudah dikirim, juri tak boleh ubah.
        if(jf.locked){
            quota.style.display='block';
            quota.innerHTML='<i class="fas fa-lock" style="margin-right:5px;color:var(--gold-400);"></i>Foto sudah dikirim. Hanya admin yang dapat menghapus / mengganti / meminta upload ulang.';
            act.style.display='none'; full.style.display='none'; jf.canUpload=false; return;
        }

        var camOpen=_el('jf-camera').style.display==='block';
        var used=jf.usedBytes+jfQueueBytes();
        var canAddBytes=(jf.maxBytes-used)>1024;
        var canAdd=canAddBytes && jf.canUploadServer; jf.canUpload=canAdd;

        quota.style.display='block';
        var txt='Terpakai '+(used/1048576).toFixed(2)+' MB dari 3 MB'; if(jf.queue.length) txt+=' · '+jf.queue.length+' menunggu disimpan';
        quota.innerHTML='<i class="fas fa-database" style="margin-right:5px;color:var(--cyan-400);"></i>'+txt;

        if(camOpen){ act.style.display='none'; return; }
        if(canAdd){ act.style.display='flex'; full.style.display='none'; }
        else { act.style.display='none'; full.style.display = canAddBytes ? 'none' : 'block'; }
    }
    function jfRenderQueue(){
        var wrap=_el('jf-preview'); var btn=_el('jf-btn-save');
        if(!jf.queue.length){ wrap.innerHTML=''; wrap.style.display='none'; btn.style.display='none'; return; }
        wrap.innerHTML='<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">'+jf.queue.map(function(it,idx){
            return '<div style="position:relative;border-radius:12px;overflow:hidden;border:1px solid var(--bd-2);"><img src="'+it.url+'" style="width:100%;height:80px;object-fit:cover;display:block;"><button type="button" onclick="jfRemoveQueue('+idx+')" style="position:absolute;top:4px;right:4px;width:22px;height:22px;border:none;border-radius:7px;background:rgba(239,68,68,.92);color:#fff;cursor:pointer;font-size:10px;display:grid;place-items:center;"><i class="fas fa-xmark"></i></button></div>';
        }).join('')+'</div>';
        wrap.style.display='block'; btn.style.display='inline-flex';
        btn.innerHTML='<i class="fas fa-cloud-arrow-up" style="margin-right:6px;"></i> Simpan Foto ('+jf.queue.length+')';
    }
    window.jfRemoveQueue=function(idx){ var it=jf.queue[idx]; if(it&&it.url){try{URL.revokeObjectURL(it.url);}catch(e){}} jf.queue.splice(idx,1); jfRenderQueue(); jfRefreshQuota(); };
    function jfClearQueue(){ jf.queue.forEach(function(it){if(it.url){try{URL.revokeObjectURL(it.url);}catch(e){}}}); jf.queue=[]; jfRenderQueue(); }
    function jfHideStatus(){ var s=_el('jf-status'); if(s) s.style.display='none'; }
    function jfShowStatus(msg,ok){ var s=_el('jf-status'); if(!s) return; s.style.display='block'; s.style.background=ok?'rgba(16,185,129,.12)':'rgba(239,68,68,.12)'; s.style.border='1px solid '+(ok?'rgba(16,185,129,.35)':'rgba(239,68,68,.35)'); s.style.color=ok?'#6ee7b7':'#fca5a5'; s.innerHTML='<i class="fas '+(ok?'fa-circle-check':'fa-circle-xmark')+'" style="margin-right:6px;"></i>'+msg; }

    window.jfStartCamera=function(){
        jfHideStatus();
        if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia){ _el('jf-input-cam').click(); return; }
        navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'},audio:false})
        .then(function(stream){ jf.stream=stream; _el('jf-video').srcObject=stream; _el('jf-camera').style.display='block'; _el('jf-upload-actions').style.display='none'; _el('jf-preview').style.display='none'; _el('jf-btn-save').style.display='none'; })
        .catch(function(){ _el('jf-input-cam').click(); });
    };
    window.jfCapture=function(){
        var v=_el('jf-video'); if(!v||!v.videoWidth){ jfCancelCamera(); return; }
        var c=document.createElement('canvas'); c.width=v.videoWidth; c.height=v.videoHeight; c.getContext('2d').drawImage(v,0,0,c.width,c.height);
        c.toBlob(function(b){ jfStopCamera(); if(!b){ jfRenderQueue(); jfRefreshQuota(); return; } jfPick(new File([b],'kamera_'+Date.now()+'.jpg',{type:'image/jpeg'})); },'image/jpeg',0.85);
    };
    function jfStopCamera(){ if(jf.stream){jf.stream.getTracks().forEach(function(t){t.stop();});jf.stream=null;} var v=_el('jf-video'); if(v)v.srcObject=null; var cam=_el('jf-camera'); if(cam)cam.style.display='none'; }
    window.jfCancelCamera=function(){ jfStopCamera(); jfRenderQueue(); jfRefreshQuota(); };

    function jfPick(file){
        if(!file) return; jfHideStatus();
        jfResize(file,function(resized){
            var size=resized.size||0; var used=jf.usedBytes+jfQueueBytes();
            if(used+size>jf.maxBytes){ jfShowStatus('Foto tidak ditambahkan: melebihi batas total 3 MB.',false); jfRefreshQuota(); return; }
            jf.queue.push({file:resized,url:URL.createObjectURL(resized),size:size}); jfRenderQueue(); jfRefreshQuota();
        });
    }
    function jfResize(file,cb){
        try{
            var img=new Image(); var url=URL.createObjectURL(file);
            img.onload=function(){ URL.revokeObjectURL(url); var maxDim=1280; var w=img.width,h=img.height; var scale=Math.min(1,maxDim/Math.max(w,h)); var nw=Math.round(w*scale),nh=Math.round(h*scale); var c=document.createElement('canvas'); c.width=nw;c.height=nh; c.getContext('2d').drawImage(img,0,0,nw,nh); c.toBlob(function(b){ if(!b){cb(file);return;} var base=(file.name||'foto').replace(/\.(png|jpe?g)$/i,''); cb(new File([b],base+'.jpg',{type:'image/jpeg'})); },'image/jpeg',0.82); };
            img.onerror=function(){ URL.revokeObjectURL(url); cb(file); }; img.src=url;
        }catch(e){ cb(file); }
    }

    window.jfSubmit=function(){
        if(!jf.currentId||!jf.queue.length) return;
        var btn=_el('jf-btn-save'); btn.disabled=true;
        var items=jf.queue.slice(); var total=items.length; var i=0,ok=0,lastErr=null;
        function step(){
            if(i>=items.length){
                jfClearQueue();
                if(lastErr) jfShowStatus((ok>0?ok+'/'+total+' foto tersimpan. ':'')+'Sebagian gagal: '+lastErr, ok>0);
                else jfShowStatus(ok+' foto berhasil disimpan.', true);
                fetch('/api/juri/ikan-foto/'+jf.currentId,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(info){ if(info&&info.success) jfRenderGallery(info); }).finally(function(){ btn.disabled=false; });
                return;
            }
            btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Mengunggah '+(i+1)+'/'+total+'...';
            var fd=new FormData(); fd.append('_token',_csrf()); fd.append('ikan_id',jf.currentId); fd.append('foto',items[i].file);
            fetch('/api/juri/upload-foto',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':_csrf()},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){ if(!d.success) throw new Error(d.message||'gagal'); ok++; })
            .catch(function(e){ lastErr=e.message||'gagal'; })
            .finally(function(){ i++; step(); });
        }
        step();
    };

    // pasang listener input (elemen modal sudah ada di DOM saat script ini jalan)
    var _ff=_el('jf-input-file'); if(_ff) _ff.addEventListener('change', function(){ if(this.files[0]) jfPick(this.files[0]); this.value=''; });
    var _fc=_el('jf-input-cam');  if(_fc) _fc.addEventListener('change', function(){ if(this.files[0]) jfPick(this.files[0]); this.value=''; });
})();

function updateScoringLockUI() {
    const lockOverlay = document.getElementById('scoring-lock-overlay');
    if (!lockOverlay) return;

    if (isScoringUnlocked) {
        lockOverlay.classList.add('hidden');
        return;
    }

    lockOverlay.classList.remove('hidden');

    const iconWrap = document.getElementById('scoring-lock-iconwrap');
    const icon     = document.getElementById('scoring-lock-icon');
    const title    = document.getElementById('scoring-lock-title');
    const desc     = document.getElementById('scoring-lock-desc');

    if (isAssignmentPending) {
        // Sesi sudah dibuka admin, tapi juri ini belum diatur kategori/kelasnya.
        if (icon)  icon.className = 'fas fa-hourglass-half text-white text-4xl';
        if (iconWrap) { iconWrap.style.background = 'linear-gradient(135deg, #22D3EE, #0891B2)'; iconWrap.style.boxShadow = '0 0 40px rgba(34,211,238,0.3)'; }
        if (title) title.textContent = 'Halaman Penilaian Sedang Diatur';
        if (desc)  desc.textContent = 'Admin sedang mengatur kategori & kelas penilaian Anda. Silakan tunggu hingga penugasan selesai.';
    } else {
        // Admin benar-benar mengunci sesi penjurian.
        if (icon)  icon.className = 'fas fa-lock text-white text-4xl';
        if (iconWrap) { iconWrap.style.background = 'linear-gradient(135deg, #F59E0B, #B45309)'; iconWrap.style.boxShadow = '0 0 40px rgba(245,158,11,0.3)'; }
        if (title) title.textContent = 'Sesi Penjurian Terkunci';
        if (desc)  desc.textContent = 'Admin belum membuka akses untuk melakukan penilaian. Silakan menunggu hingga sesi dibuka.';
    }
}

// ═══════════════════════════════════════════════════════════════
// LOAD DATA UNTUK PENJURIAN — Selalu load meskipun terkunci
// ═══════════════════════════════════════════════════════════════
async function loadJuriDataForPenjurian() {
    try {
        // Cek status nominasi dulu untuk update lock UI
        const statusRes = await apiFetch('/api/juri/nominasi-status?_t=' + Date.now());

        isScoringUnlocked = statusRes.scoring_unlocked === true;
        isAssignmentPending = statusRes.assignment_pending === true;
        updateScoringLockUI();
        setApprovedIkanSig(statusRes.approved_ikan_ids || []);

        // Selalu load data juri terbaru.
        // Cache buster dipakai agar hasil reset/acak ulang nomor tank dari admin langsung terbaca.
        const dataRes = await apiFetch('/api/juri/data?_t=' + Date.now());

        appData.available_tanks    = dataRes.available_tanks || [];
        appData.my_scores          = dataRes.my_scores || [];
        appData.all_scored         = dataRes.all_scored || {};
        appData.scored_counts      = dataRes.scored_counts || {};
        appData.nomination_defects = dataRes.nomination_defects || {};

        pruneUnavailableTankScores();

        initTankScores(appData.available_tanks);
        loadDraft();

        // Pre-fill defect dari nominasi yang sudah approved.
        Object.keys(appData.nomination_defects).forEach(function(ikanId) {
            const nd = appData.nomination_defects[ikanId];

            if (!nd || !tankScores[ikanId]) return;

            if (!tankScores[ikanId].defects) {
                tankScores[ikanId].defects = {
                    raw_head_penalty:    ['0'],
                    raw_face_penalty:    ['0'],
                    raw_body_penalty:    ['0'],
                    raw_finnage_penalty: ['0']
                };
            }

            ['head', 'face', 'body', 'finnage'].forEach(function(p) {
                const k = 'raw_' + p + '_penalty';
                const cur = tankScores[ikanId].defects[k];

                const isCurDefault =
                    !cur ||
                    cur.length === 0 ||
                    (cur.length === 1 && cur[0] === '0');

                const ndArr = normalizeDefectArr(nd[k]);

                const hasReal = Array.isArray(ndArr) && ndArr.some(function(v) {
                    return v && v !== '0';
                });

                if (isCurDefault && hasReal) {
                    tankScores[ikanId].defects[k] = ndArr.slice();
                }
            });
        });

        populateFilter();
        renderFormTable();
        renderLiveTable();

    } catch(e) {
        console.error('Gagal load data penjurian:', e);

        showWarningModal([
            {
                type: 'select',
                msg: 'Gagal memuat data penjurian. Periksa koneksi internet Anda.'
            }
        ]);
    }
}

async function checkScoringLockAndInit() {
    try {
        const res = await apiFetch('/api/juri/nominasi-status?_t=' + Date.now());

        isScoringUnlocked = res.scoring_unlocked === true;
        isAssignmentPending = res.assignment_pending === true;
        updateScoringLockUI();

    } catch (e) {
        isScoringUnlocked = false;
        isAssignmentPending = false;
        updateScoringLockUI();
    }
}

function startScoringLockPolling() {
    stopScoringLockPolling();

    scoringLockTimer = setInterval(async function() {
        // Hanya poll jika user masih di tab penjurian.
        if (currentJuriView !== 'penjurian') {
            stopScoringLockPolling();
            return;
        }

        try {
            const res = await apiFetch('/api/juri/nominasi-status?_t=' + Date.now());

            const wasLocked = !isScoringUnlocked;
            const nextUnlocked = res.scoring_unlocked === true;
            const nextPending = res.assignment_pending === true;

            const newApprovedSig = makeApprovedIkanSig(res.approved_ikan_ids || []);
            const approvedChanged = lastApprovedIkanSig !== '' && newApprovedSig !== lastApprovedIkanSig;

            isScoringUnlocked = nextUnlocked;
            isAssignmentPending = nextPending;
            updateScoringLockUI();

            // Update signature setelah dihitung perbedaannya.
            lastApprovedIkanSig = newApprovedSig;

            if (approvedChanged) {
                // Ini yang membuat tank hilang/masuk otomatis tanpa refresh browser.
                await loadJuriDataForPenjurian();
                return;
            }

            if (wasLocked && isScoringUnlocked) {
                showSuccessPopup('Sesi Dibuka!', 'Admin telah membuka akses penjurian. Anda bisa mulai menilai.');
                await loadJuriDataForPenjurian();
                return;
            }

            if (!wasLocked && !isScoringUnlocked) {
                showWarningModal([{type:'select', msg:'Admin telah MENGUNCI sesi penjurian.'}]);
            }
        } catch (e) {}
    }, 3000);
}

function stopScoringLockPolling() {
    if (scoringLockTimer) { clearInterval(scoringLockTimer); scoringLockTimer = null; }
}

// ★ Auto init tab aktif saat pertama load
function initJuriSidebar() {
    switchJuriView('nominasi');
}

document.addEventListener('DOMContentLoaded', function() {
    renderTabs();

    if (typeof initJuriSidebar === 'function') {
        initJuriSidebar();
    }

    var modalDefect = document.getElementById('modal-defect');

    if (modalDefect) {
        modalDefect.addEventListener('click', function(e) {
            if (e.target === this) saveDefect();
        });
    }

    document.addEventListener('keydown', function(e) {
        var modalDefectNow = document.getElementById('modal-defect');

        if (
            e.key === 'Escape' &&
            modalDefectNow &&
            !modalDefectNow.classList.contains('hidden')
        ) {
            saveDefect();
        }
    });
});

/* ════════════════════ REVISI & RANKING (JURI) ════════════════════ */
let revisiState = { scoringId:null, kategori:'', kelas:'', scores:{}, defects:{} };
let revisiRankScope = 'per_kategori_kelas';
let revisiEditFilter = { key:'', search:'' };
let revisiLoadedOnce = false;

async function loadRevisiView(forceRefresh) {
    populateRevisiRankFilters();

    var refreshBtn = document.querySelector('.revisi-refresh-btn');
    var loading = document.getElementById('revisi-loading');
    var list = document.getElementById('revisi-list');

    var hasData = Array.isArray(appData.my_scores) && appData.my_scores.length > 0;
    var shouldShowLoading = forceRefresh === true || !revisiLoadedOnce || !hasData;

    if (refreshBtn) refreshBtn.classList.add('loading');

    if (shouldShowLoading) {
        if (loading) loading.classList.remove('hidden');
        if (list) list.innerHTML = '';
    }

    try {
        await refreshRevisiScores();

        revisiLoadedOnce = true;

        populateRevisiEditFilter();
        renderRevisiList();
    } catch (e) {
        if (list) {
            list.innerHTML =
                '<div class="text-center py-16 glass-card" style="color:var(--danger);">' +
                    '<i class="fas fa-triangle-exclamation text-2xl mb-2"></i>' +
                    '<p class="text-sm font-bold">Gagal memuat data revisi.</p>' +
                '</div>';
        }
    } finally {
        if (loading) loading.classList.add('hidden');
        if (refreshBtn) refreshBtn.classList.remove('loading');
    }
}

function populateRevisiRankFilters() {
    var kat = document.getElementById('revisi-rank-kategori');
    var kelas = document.getElementById('revisi-rank-kelas');
    if (kat && !kat.dataset.filled) {
        kat.innerHTML = '<option value="">Semua Kategori</option>' + ['Bonsai','Cencu','Chingwa','Freemarking','Goldenbase','Jumbo','Klasik'].map(function(c){return '<option value="'+c+'">'+c+'</option>';}).join('');
        kat.dataset.filled = '1';
    }
    if (kelas && !kelas.dataset.filled) {
        kelas.innerHTML = ['A','B','C','D','E'].map(function(c){return '<option value="'+c+'">Kelas '+c+'</option>';}).join('');
        kelas.dataset.filled = '1';
    }
}

function revisiSwitchTab(tab) {
    var subEdit = document.getElementById('revisi-subtab-edit');
    var subRank = document.getElementById('revisi-subtab-ranking');
    var bEdit = document.getElementById('revisi-tab-edit-btn');
    var bRank = document.getElementById('revisi-tab-ranking-btn');

    if (!subEdit || !subRank || !bEdit || !bRank) {
        console.warn('Elemen Revisi & Ranking belum ditemukan.');
        return;
    }

    if (tab === 'ranking') {
        subEdit.classList.add('hidden');
        subRank.classList.remove('hidden');
        bEdit.classList.remove('active');
        bRank.classList.add('active');
        loadRevisiRanking();
        return;
    }

    subRank.classList.add('hidden');
    subEdit.classList.remove('hidden');
    bRank.classList.remove('active');
    bEdit.classList.add('active');

    if (!revisiLoadedOnce) {
        loadRevisiView(false);
    } else {
        populateRevisiEditFilter();
        renderRevisiList();
    }
}

async function refreshRevisiScores() {
    var res = await apiFetch('/api/juri/data?_t=' + Date.now());
    appData.my_scores = res.my_scores || [];
    appData.available_tanks = res.available_tanks || appData.available_tanks || [];
    return appData.my_scores;
}

function revisiScoreValueFromDetail(score, key) {
    var nd = score.nilai_detail || {};
    var parts = key.split('.');
    return nd[parts[0]] && nd[parts[0]][parts[1]] != null ? nd[parts[0]][parts[1]] : '';
}

function revisiRenderScoreChips(score) {
    var kategori = score.ikan ? score.ikan.kategori : '';
    var chips = [];

    SCORING_GROUPS.forEach(function(group) {
        group.fields.forEach(function(f) {
            if (f.type === 'defect') return;
            if (isFieldLocked(kategori, f.key)) return;

            var val = revisiScoreValueFromDetail(score, f.key);
            var cls = val === '' ? 'revisi-score-chip missing' : 'revisi-score-chip';

            chips.push(
                '<span class="'+cls+'">' +
                    f.label + ': <b>' + (val !== '' ? val : '-') + '</b>' +
                '</span>'
            );
        });
    });

    if (!chips.length) {
        return '<div class="revisi-components"><span class="revisi-score-chip missing">Tidak ada komponen aktif</span></div>';
    }

    return '<div class="revisi-components">' + chips.join('') + '</div>';
}

function revisiGetScoreFilterKey(score) {
    var t = score.ikan || {};
    var kategori = t.kategori || '-';
    var kelas = score.kelas || t.kelas || '';
    return kategori + '||' + kelas;
}

function revisiGetScoreFilterLabel(score) {
    var t = score.ikan || {};
    var kategori = t.kategori || '-';
    var kelas = score.kelas || t.kelas || '';

    return kategori + (kelas ? ' – Kelas ' + kelas : ' – Tanpa Kelas');
}

function populateRevisiEditFilter() {
    var wrap = document.getElementById('revisi-edit-filter-chips');
    if (!wrap) return;

    var scores = appData.my_scores || [];
    var map = {};

    scores.forEach(function(s) {
        var key = revisiGetScoreFilterKey(s);
        if (!map[key]) {
            map[key] = revisiGetScoreFilterLabel(s);
        }
    });

    var chips = [
        '<button type="button" onclick="setRevisiEditFilter(\'\')" class="revisi-edit-chip '+(revisiEditFilter.key === '' ? 'active' : '')+'">' +
            '<i class="fas fa-layer-group"></i> Semua' +
        '</button>'
    ];

    Object.keys(map).sort(function(a, b) {
        return map[a].localeCompare(map[b]);
    }).forEach(function(key) {
        chips.push(
            '<button type="button" onclick="setRevisiEditFilter(\''+key.replace(/'/g, "\\'")+'\')" class="revisi-edit-chip '+(revisiEditFilter.key === key ? 'active' : '')+'">' +
                '<i class="fas fa-fish"></i> ' + map[key] +
            '</button>'
        );
    });

    wrap.innerHTML = chips.join('');
}

function setRevisiEditFilter(key) {
    revisiEditFilter.key = key || '';
    populateRevisiEditFilter();
    renderRevisiList();
}

function revisiApplyEditFilter() {
    var input = document.getElementById('revisi-edit-search');
    revisiEditFilter.search = input ? input.value.trim().toLowerCase() : '';
    renderRevisiList();
}

function revisiFilterScores(scores) {
    var q = revisiEditFilter.search || '';
    var key = revisiEditFilter.key || '';

    return (scores || []).filter(function(s) {
        var t = s.ikan || {};
        var kategori = t.kategori || '';
        var kelas = s.kelas || t.kelas || '';
        var nomor = t.nomor_tank || '';
        var point = Number(s.total_point || 0).toFixed(2);

        var thisKey = revisiGetScoreFilterKey(s);
        var haystack = [
            nomor,
            kategori,
            kelas,
            'kelas ' + kelas,
            point
        ].join(' ').toLowerCase();

        if (key && thisKey !== key) return false;
        if (q && haystack.indexOf(q) === -1) return false;

        return true;
    });
}

function renderRevisiList() {
    var box = document.getElementById('revisi-list');

    if (!box) {
        console.warn('Elemen #revisi-list tidak ditemukan.');
        return;
    }

    var allScores = appData.my_scores || [];
    var scores = revisiFilterScores(allScores);

    // ★ Urutkan dari point TERTINGGI ke terendah
    scores = scores.slice().sort(function(a, b) {
        return (Number(b.total_point) || 0) - (Number(a.total_point) || 0);
    });

    if (allScores.length === 0) {
        box.innerHTML =
            '<div class="text-center py-16 glass-card" style="color:var(--text-low);">' +
                '<i class="fas fa-inbox" style="font-size:26px;opacity:.4;"></i>' +
                '<p class="mt-2 text-sm font-bold">Belum ada nilai tersimpan untuk direvisi.</p>' +
            '</div>';
        return;
    }

    if (scores.length === 0) {
        box.innerHTML =
            '<div class="text-center py-16 glass-card" style="color:var(--text-low);">' +
                '<i class="fas fa-filter" style="font-size:26px;opacity:.4;"></i>' +
                '<p class="mt-2 text-sm font-bold">Tidak ada nilai sesuai filter.</p>' +
            '</div>';
        return;
    }

    box.innerHTML = scores.map(function(s) {
        var t = s.ikan || {};
        var locked = !!(t && t.is_locked);
        var sent = !!s.submitted_to_grand;

        var statusHtml = locked
            ? '<span class="revisi-badge revisi-badge-lock"><i class="fas fa-lock"></i> Dikunci</span>'
            : (
                sent
                    ? '<span class="revisi-badge revisi-badge-sent"><i class="fas fa-paper-plane"></i> Terkirim</span>'
                    : '<span class="revisi-badge revisi-badge-saved"><i class="fas fa-floppy-disk"></i> Tersimpan</span>'
            );

        var btn = locked
            ? '<button disabled class="revisi-edit-btn" style="opacity:.45;cursor:not-allowed;"><i class="fas fa-lock"></i> Final</button>'
            : '<button type="button" onclick="openRevisiEdit('+s.id+')" class="revisi-edit-btn"><i class="fas fa-pen-to-square"></i> Edit</button>';

        return '<div class="revisi-row">' +
            '<div class="revisi-row-tank">'+(t.nomor_tank || '-')+'</div>' +

            '<div class="revisi-row-info">' +
                '<div class="revisi-row-kat">'+(t.kategori || '-')+'</div>' +
                '<div class="revisi-row-kelas">'+((s.kelas || t.kelas) ? ('Kelas ' + (s.kelas || t.kelas)) : 'Tanpa kelas')+'</div>' +
            '</div>' +

            '<div class="revisi-row-point">'+Number(s.total_point || 0).toFixed(2)+'<span>point</span></div>' +
            '<div class="revisi-row-status">'+statusHtml+'</div>' +
            '<div class="revisi-row-act">'+btn+'</div>' +

            revisiRenderScoreChips(s) +
        '</div>';
    }).join('');
}

function openRevisiEdit(scoringId) {
    var s = (appData.my_scores || []).find(function(x) {
        return String(x.id) === String(scoringId);
    });

    if (!s) {
        showToast('Data nilai tidak ditemukan. Silakan refresh halaman.', 'error');
        return;
    }

    if (s.ikan && s.ikan.is_locked) {
        showToast('Nilai sudah DIKUNCI Grand Juri — tidak dapat direvisi.', 'error');
        return;
    }

    var modal = document.getElementById('revisi-edit-modal');
    var body = document.getElementById('revisi-form-body');

    if (!modal || !body) {
        showToast('Modal revisi tidak ditemukan. Cek struktur Blade.', 'error');
        return;
    }

    body.innerHTML =
        '<div class="text-center py-10" style="color:var(--text-mid);">' +
            '<i class="fas fa-spinner fa-spin text-xl mb-3" style="color:var(--cyan-400);"></i>' +
            '<p class="text-xs font-bold">Menyiapkan form revisi...</p>' +
        '</div>';

    modal.classList.remove('hidden');

    var nd = s.nilai_detail || {};
    var g = function(o,k){ return (nd[o] && nd[o][k] != null) ? nd[o][k] : ''; };
    revisiState.scoringId = s.id;
    revisiState.kategori  = s.ikan ? s.ikan.kategori : '';
    revisiState.kelas     = s.kelas || (s.ikan ? s.ikan.kelas : '');
    revisiState.scores = {
        overall:{impression:g('overall','impression')},
        head:{size:g('head','size'), bentuk:g('head','bentuk')},
        face:{face:g('face','face')},
        body:{bentuk:g('body','bentuk'), proporsi:g('body','proporsi'), pangkal:g('body','pangkal')},
        marking:{fullness:g('marking','fullness'), contrast:g('marking','contrast'), bentuk:g('marking','bentuk')},
        pearl:{shinning:g('pearl','shinning'), fullness:g('pearl','fullness'), bentuk:g('pearl','bentuk')},
        color:{komposisi:g('color','komposisi'), kecerahan:g('color','kecerahan'), fullness:g('color','fullness')},
        finnage:{bentuk:g('finnage','bentuk'), kecerahan:g('finnage','kecerahan')}
    };
    revisiState.defects = {
        raw_head_penalty:    normalizeDefectArr(s.raw_head_penalty    || ['0']),
        raw_face_penalty:    normalizeDefectArr(s.raw_face_penalty    || ['0']),
        raw_body_penalty:    normalizeDefectArr(s.raw_body_penalty    || ['0']),
        raw_finnage_penalty: normalizeDefectArr(s.raw_finnage_penalty || ['0'])
    };
    document.getElementById('revisi-edit-title').textContent = 'Revisi Tank ' + (s.ikan ? s.ikan.nomor_tank : '-') + ' — ' + revisiState.kategori;
    renderRevisiForm();
}

function closeRevisiEdit() {
    document.getElementById('revisi-edit-modal').classList.add('hidden');
    revisiState.scoringId = null;
}

function revisiGetVal(key){ var p=key.split('.'); return (revisiState.scores[p[0]] && revisiState.scores[p[0]][p[1]] != null) ? revisiState.scores[p[0]][p[1]] : ''; }
function revisiSetVal(key,val){ var p=key.split('.'); if(!revisiState.scores[p[0]]) revisiState.scores[p[0]]={}; revisiState.scores[p[0]][p[1]]=val; }

function revisiDefectBtn(partKey) {
    var ts = { defects: revisiState.defects };
    var vals = revisiState.defects['raw_'+partKey+'_penalty'] || ['0'];
    var isAman = vals.includes('0') || vals.length === 0;
    var ev = evalDefects(ts);
    var score = ev[partKey];
    var cls = (isAman || !score) ? 'defect-btn-aman' : (score === '30%' ? 'defect-btn-30' : 'defect-btn-10');
    var lbl = (isAman || !score) ? 'Aman' : (score === '30%' ? '30% Defect' : '10% Defect');
    return '<button type="button" onclick="openDefect(0,\''+partKey+'\',\'revisi\')" class="'+cls+'">'+lbl+'</button>';
}

function renderRevisiForm() {
    var kat = revisiState.kategori;
    var html = '';

    SCORING_GROUPS.forEach(function(group) {
        var groupHtml = '';

        group.fields.forEach(function(f) {
            if (f.type === 'defect') {
                groupHtml += '<div class="revisi-field"><label>Defect</label>'+revisiDefectBtn(f.part)+'</div>';
                return;
            }

            if (isFieldLocked(kat, f.key)) {
                return;
            }

            var val = revisiGetVal(f.key);

            groupHtml +=
                '<div class="revisi-field">' +
                    '<label>'+f.label+'</label>' +
                    '<select onchange="revisiSetVal(\''+f.key+'\',this.value)" class="revisi-select">' +
                        buildSelectHtml(val, f.type) +
                    '</select>' +
                '</div>';
        });

        if (groupHtml !== '') {
            html += '<div class="revisi-group">' +
                '<div class="revisi-group-title">'+group.title+'</div>' +
                '<div class="revisi-fields">'+groupHtml+'</div>' +
            '</div>';
        }
    });

    document.getElementById('revisi-form-body').innerHTML =
        html ||
        '<div class="text-center py-10" style="color:var(--text-low);font-size:12px;font-weight:800;">Tidak ada komponen penilaian aktif untuk kategori ini.</div>';
}

async function submitRevisi() {
    if (!revisiState.scoringId) return;
    var missing = [];
    SCORING_GROUPS.forEach(function(group){
        group.fields.forEach(function(f){
            if (f.type === 'defect') return;
            if (isFieldLocked(revisiState.kategori, f.key)) return;
            if (revisiGetVal(f.key) === '') missing.push(group.title + ' > ' + f.label);
        });
    });
    if (missing.length) { showWarningModal(missing.map(function(m){return {type:'select',msg:m};})); return; }
    var btn = document.getElementById('revisi-submit-btn');
    var old = btn.innerHTML; btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    try {
        var res = await apiFetch('/api/juri/update-nilai', { method:'POST', body: JSON.stringify({
            scoring_id: revisiState.scoringId,
            all_scores: revisiState.scores,
            defect_data: revisiState.defects
        })});
        if (res.success) {
            closeRevisiEdit();
            showSuccessPopup('Revisi Tersimpan', res.message || 'Nilai berhasil diperbarui.');
            await refreshRevisiScores();
            renderRevisiList();
        } else {
            showWarningModal([{type:'select', msg: res.message || 'Gagal menyimpan revisi.'}]);
        }
    } catch(e) {
        showWarningModal([{type:'select', msg:'Koneksi error saat menyimpan revisi.'}]);
    } finally {
        btn.disabled = false; btn.innerHTML = old;
    }
}

function setRevisiRankScope(scope) {
    revisiRankScope = scope;
    ['per_kategori_kelas','per_kategori','global'].forEach(function(sc){
        var b = document.getElementById('revisi-scope-'+sc);
        if (b) b.classList.toggle('active', sc === scope);
    });
    var kelasWrap = document.getElementById('revisi-rank-kelas-wrap');
    if (kelasWrap) kelasWrap.style.display = (scope === 'global') ? 'none' : '';
    loadRevisiRanking();
}

async function loadRevisiRanking() {
    var content = document.getElementById('revisi-ranking-content');
    var refreshBtn = document.querySelector('.revisi-refresh-btn');

    if (!content) {
        console.warn('Elemen #revisi-ranking-content tidak ditemukan.');
        return;
    }

    if (refreshBtn) refreshBtn.classList.add('loading');

    content.innerHTML =
        '<div class="text-center py-16 glass-card" style="color:var(--text-low);">' +
            '<i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--cyan-400);"></i>' +
            '<p class="mt-3 text-sm font-bold">Memuat ranking...</p>' +
        '</div>';
    var kat = document.getElementById('revisi-rank-kategori') ? document.getElementById('revisi-rank-kategori').value : '';
    var kelas = document.getElementById('revisi-rank-kelas') ? document.getElementById('revisi-rank-kelas').value : '';
    var qs = '?scope=' + encodeURIComponent(revisiRankScope);
    if (revisiRankScope !== 'global') {
        if (kat) qs += '&kategori=' + encodeURIComponent(kat);
        if (kelas) qs += '&kelas=' + encodeURIComponent(kelas);
    }
    try {
        var groups = await apiFetch('/api/juri/point-ranking' + qs);
        renderRevisiRanking(groups);
    } catch(e) {
        content.innerHTML = '<div class="text-center py-16 glass-card" style="color:var(--danger);">Gagal memuat ranking.</div>';
    } finally {
        if (refreshBtn) refreshBtn.classList.remove('loading');
    }
}

function renderRevisiRanking(groups) {
    var content = document.getElementById('revisi-ranking-content');

    if (!Array.isArray(groups) || groups.length === 0) {
        content.innerHTML =
            '<div class="text-center py-16 glass-card" style="color:var(--text-low);">' +
                '<i class="fas fa-trophy" style="font-size:28px;opacity:.4;"></i>' +
                '<p class="mt-2 text-sm font-bold">Belum ada data ranking.</p>' +
                '<p class="mt-1 text-xs font-semibold" style="color:var(--text-faint);">Ranking baru muncul setelah Grand Juri mengunci nilai.</p>' +
            '</div>';
        return;
    }

    content.innerHTML = groups.map(function(gp){
        var data = gp.data || [];

        var rows = data.map(function(it){
            var pos = Number(it.position || 0);
            var medal = pos === 1 ? '🥇' : (pos === 2 ? '🥈' : (pos === 3 ? '🥉' : ('#' + pos)));
            var finalPoint = it.final_rank_point != null ? it.final_rank_point : (it.rank_point != null ? it.rank_point : 0);

            return '<tr class="'+(pos <= 3 ? 'revisi-rank-top' : '')+'">' +
                '<td class="revisi-rank-medal">'+medal+'</td>' +
                '<td class="revisi-rank-tank">'+(it.nomor_tank != null ? it.nomor_tank : '-')+'</td>' +
                '<td>'+(it.kategori || gp.kategori || '-')+'</td>' +
                '<td>'+(it.kelas || gp.kelas || '-')+'</td>' +
                '<td class="revisi-rank-point">'+Number(it.total_point || 0).toFixed(2)+'</td>' +
                '<td style="color:var(--gold-300);font-weight:900;">'+(it.total_bonus ? ('+' + it.total_bonus) : '-')+'</td>' +
                '<td class="revisi-rank-final">'+finalPoint+'</td>' +
            '</tr>';
        }).join('');

        return '<div class="revisi-rank-card">' +
            '<div class="revisi-rank-head">' +
                '<div class="revisi-rank-head-title">' +
                    '<i class="fas fa-medal"></i>' +
                    '<span>'+(gp.group_name || 'Ranking')+'</span>' +
                '</div>' +
                '<span class="revisi-badge revisi-badge-sent"><i class="fas fa-user-secret"></i> Anonim</span>' +
            '</div>' +
            '<div class="revisi-rank-table-wrap custom-scrollbar">' +
                '<table class="revisi-rank-table">' +
                    '<thead>' +
                        '<tr>' +
                            '<th>Juara</th>' +
                            '<th>Tank</th>' +
                            '<th>Kategori</th>' +
                            '<th>Kelas</th>' +
                            '<th>Total Point</th>' +
                            '<th>Bonus</th>' +
                            '<th>Rank Pt</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>'+rows+'</tbody>' +
                '</table>' +
            '</div>' +
        '</div>';
    }).join('');
}
</script>
@endpush