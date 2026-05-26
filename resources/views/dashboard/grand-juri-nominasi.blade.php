<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Grand Juri — Review Nominasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.97); } to { opacity: 1; transform: scale(1); } }
        .fade-in { animation: fadeIn 0.2s ease-out forwards; }
        :root {
            --bg-main: #f0f4f8;
            --bg-card: #ffffff;
            --primary: #2563eb;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background: var(--bg-main) !important; }
        .top-nav { background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .brand h1 { font-size: 18px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .brand span { font-size: 11px; color: var(--text-muted); }
        .nav-right { display: flex; align-items: center; gap: 15px; }
        .nav-right .info { text-align: right; }
        .nav-right .info h4 { font-size: 13px; font-weight: 700; }
        .nav-right .info span { font-size: 10px; color: #7c3aed; background: #ede9fe; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
        .btn-logout { padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-family: inherit; }
        .btn-logout:hover { border-color: #ef4444; color: #ef4444; }
        .btn-back { padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-family: inherit; }
        .btn-back:hover { border-color: var(--primary); color: var(--primary); }

        .warning-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity .3s ease; }
        .warning-overlay.show { opacity: 1; pointer-events: all; }
        .warning-card { background: white; border-radius: 24px; width: 90%; max-width: 450px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); transform: translateY(40px) scale(0.95); opacity: 0; transition: all .4s cubic-bezier(0.16,1,0.3,1); overflow: hidden; }
        .warning-overlay.show .warning-card { transform: translateY(0) scale(1); opacity: 1; }
        .warning-header { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px 30px 20px; text-align: center; }
        .warning-icon { width: 64px; height: 64px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 10px 15px -3px rgba(245,158,11,0.3); }
        .warning-icon i { font-size: 28px; color: #d97706; }
        .warning-title { font-size: 20px; font-weight: 800; color: #92400e; }
        .warning-body { padding: 24px 30px 30px; }
        .error-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .error-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; }
        .error-item i { color: #ef4444; font-size: 16px; margin-top: 2px; flex-shrink: 0; }
        .error-item .err-desc { font-size: 12px; color: #b91c1c; font-weight: 500; }
        .warning-footer { padding: 0 30px 30px; }
        .btn-close-warning { width: 100%; padding: 14px; border: none; border-radius: 14px; background: #d97706; color: white; font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; transition: all .2s; box-shadow: 0 4px 14px rgba(217,119,6,0.3); }
        .btn-close-warning:hover { background: #b45309; }

        .popup-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.4); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity .4s ease; }
        .popup-overlay.show { opacity: 1; pointer-events: all; }
        .popup-card { background: white; border-radius: 24px; padding: 48px 40px 36px; text-align: center; max-width: 360px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.15); transform: scale(0.8) translateY(20px); transition: transform .4s cubic-bezier(0.16,1,0.3,1); }
        .popup-overlay.show .popup-card { transform: scale(1) translateY(0); }
        .popup-check { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg,#22c55e,#16a34a); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(34,197,94,0.3); }
        .popup-check i { font-size: 36px; color: white; }
        .popup-title { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .popup-desc { font-size: 13.5px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .popup-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .3s ease; }
        .popup-btn:hover { transform: translateY(-1px); }

        .confirm-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.4); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity .4s ease; }
        .confirm-overlay.show { opacity: 1; pointer-events: all; }
        .confirm-actions { display: flex; gap: 12px; justify-content: center; }
        .btn-outline { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: 2px solid #e2e8f0; border-radius: 14px; background: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .2s; color: #64748b; }
        .btn-outline:hover { border-color: #94a3b8; color: #1e293b; }
        .btn-danger { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: 14px; background: linear-gradient(135deg,#ef4444,#dc2626); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .3s ease; box-shadow: 0 4px 12px rgba(239,68,68,0.25); }

        @media (max-width: 768px) {
            .top-nav { padding: 10px 16px; flex-wrap: wrap; gap: 8px; }
            .brand h1 { font-size: 15px; }
            .brand span { display: none; }
            .nav-right { gap: 10px; flex-wrap: wrap; }
        }
    </style>
</head>
<body class="min-h-screen font-sans text-slate-800">

    <nav class="top-nav">
        <div class="brand">
            <h1><i class="fas fa-gavel"></i> Penjurian LCI</h1>
            <span>Grand Juri — Review Nominasi</span>
        </div>
        <div class="nav-right">
            <a href="{{ route('grand-juri.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            <div class="info">
                <h4>{{ Auth::user()->name }}</h4>
                <span>GRAND JURI</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">

        {{-- Header Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-800" id="stat-juri">0</p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Juri Menunggu</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-fish text-amber-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-800" id="stat-tank">0</p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tank Pending</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-5 flex items-center gap-4">
                <button onclick="loadNominasi()" class="w-full flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-sync-alt text-emerald-600 text-xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Refresh Data</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-300"></i>
                </button>
            </div>
        </div>

        {{-- Daftar Nominasi per Juri --}}
        <div id="nom-list" class="space-y-6">
            <div id="nom-empty" class="hidden text-center py-20 bg-white rounded-xl shadow-lg border border-slate-200">
                <i class="fas fa-inbox text-5xl text-slate-200 mb-4"></i>
                <p class="text-sm font-bold text-slate-400">Tidak ada nominasi yang menunggu review.</p>
                <p class="text-xs text-slate-300 mt-1">Semua sudah ditinjau atau belum ada pengiriman.</p>
            </div>
        </div>

    </main>

    <!-- Warning Modal -->
    <div class="warning-overlay" id="warningModal">
        <div class="warning-card">
            <div class="warning-header">
                <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="warning-title">Perhatian</h2>
            </div>
            <div class="warning-body">
                <ul class="error-list" id="errorListContainer"></ul>
            </div>
            <div class="warning-footer">
                <button class="btn-close-warning" onclick="document.getElementById('warningModal').classList.remove('show')">OK, Saya Mengerti</button>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div class="popup-overlay" id="successPopup">
        <div class="popup-card">
            <div class="popup-check"><i class="fas fa-check"></i></div>
            <h2 class="popup-title" id="popupTitle">Berhasil!</h2>
            <p class="popup-desc" id="popupDesc">Data telah tersimpan.</p>
            <button class="popup-btn" onclick="document.getElementById('successPopup').classList.remove('show')">
                <i class="fas fa-circle-check"></i> OK, Tutup
            </button>
        </div>
    </div>

    <!-- Confirm Modal (untuk reject) -->
    <div class="confirm-overlay" id="confirmModal">
        <div class="popup-card" style="max-width:420px">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center mx-auto mb-6 shadow-lg shadow-red-300/30">
                <i class="fas fa-times text-3xl text-white"></i>
            </div>
            <h2 class="popup-title">Tolak Nominasi?</h2>
            <p class="popup-desc" id="confirmMessage">Anda yakin ingin menolak tank ini? Juri akan diminta mengirim ulang nominasi.</p>
            <div class="mb-5">
                <input type="text" id="rejectReason" placeholder="Alasan penolakan (opsional)" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
            </div>
            <div class="confirm-actions">
                <button class="btn-outline" onclick="document.getElementById('confirmModal').classList.remove('show'); confirmResolve=null;">
                    <i class="fas fa-xmark"></i> Batal
                </button>
                <button class="btn-danger" id="confirmOkBtn" onclick="executeReject()">
                    <i class="fas fa-ban"></i> Ya, Tolak
                </button>
            </div>
        </div>
    </div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let pendingRejectId = null;

async function apiFetch(url, opts = {}) {
    const defaults = { headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
    const res = await fetch(url, { ...defaults, ...opts, headers: { ...defaults.headers, ...(opts.headers || {}) } });
    return res.json();
}

function showWarningModal(errorsArray) {
    const container = document.getElementById('errorListContainer');
    container.innerHTML = '';
    (errorsArray || []).forEach(function(err) {
        const li = document.createElement('li');
        li.className = 'error-item';
        li.innerHTML = '<i class="fas fa-circle-xmark"></i><div><span class="err-desc">' + (err.msg || err) + '</span></div>';
        container.appendChild(li);
    });
    document.getElementById('warningModal').classList.add('show');
}

function showSuccessPopup(title, desc) {
    document.getElementById('popupTitle').innerText = title;
    document.getElementById('popupDesc').innerHTML = desc;
    document.getElementById('successPopup').classList.add('show');
}

function showRejectConfirm(nominasiId, nomorTank) {
    pendingRejectId = nominasiId;
    document.getElementById('confirmMessage').textContent = 'Anda yakin ingin menolak Tank T' + nomorTank + '? Juri akan diminta mengirim ulang nominasi.';
    document.getElementById('rejectReason').value = '';
    document.getElementById('confirmModal').classList.add('show');
}

async function executeReject() {
    if (!pendingRejectId) return;
    const catatan = document.getElementById('rejectReason').value.trim();
    document.getElementById('confirmModal').classList.remove('show');

    try {
        const res = await apiFetch('/api/grand-juri/nominasi-review', {
            method: 'POST',
            body: JSON.stringify({ nominasi_id: pendingRejectId, action: 'reject', catatan: catatan })
        });
        if (res.success) {
            showSuccessPopup('Ditolak', res.message);
            loadNominasi();
        } else {
            showWarningModal([{ msg: res.message }]);
        }
    } catch(e) {
        showWarningModal([{ msg: 'Gagal memproses. Periksa koneksi.' }]);
    }
    pendingRejectId = null;
}

async function approveNominasi(btn, nominasiId) {
    btn.disabled = true;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>';

    try {
        const res = await apiFetch('/api/grand-juri/nominasi-review', {
            method: 'POST',
            body: JSON.stringify({ nominasi_id: nominasiId, action: 'approve' })
        });
        if (res.success) {
            showSuccessPopup('Disetujui', res.message);
            loadNominasi();
        } else {
            showWarningModal([{ msg: res.message }]);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> ACC';
        }
    } catch(e) {
        showWarningModal([{ msg: 'Gagal memproses. Periksa koneksi.' }]);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> ACC';
    }
}

async function loadNominasi() {
    try {
        const res = await apiFetch('/api/grand-juri/nominasi');
        document.getElementById('stat-juri').textContent = res.total_juri || 0;
        document.getElementById('stat-tank').textContent = res.total_pending || 0;

        const list = document.getElementById('nom-list');
        const empty = document.getElementById('nom-empty');

        if (!res.grouped || res.grouped.length === 0) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        let html = '';

        res.grouped.forEach(function(group) {
            html += '<div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden fade-in">';
            html += '<div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex items-center justify-between">';
            html += '<div class="flex items-center gap-3">';
            html += '<div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-user text-blue-600"></i></div>';
            html += '<div><h3 class="text-sm font-extrabold text-slate-800">' + group.juri_name + '</h3>';
            html += '<p class="text-[10px] font-bold text-slate-500">' + group.tanks.length + ' tank dinominasikan</p></div></div>';
            html += '<div class="flex gap-2">';
            html += '<button onclick="approveAllInGroup(this, ' + JSON.stringify(group.tanks.map(function(t){return t.nominasi_id;})) + ')" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm"><i class="fas fa-check-double"></i> ACC Semua</button>';
            html += '</div></div>';
            html += '<div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">';

            group.tanks.forEach(function(tank) {
                html += '<div id="tank-card-' + tank.nominasi_id + '" class="p-3 rounded-xl border border-slate-200 bg-white hover:shadow-md transition-all">';
                html += '<div class="flex justify-between items-start mb-3">';
                html += '<div class="w-11 h-11 rounded-[10px] flex items-center justify-center font-extrabold text-lg shadow-sm bg-slate-800 text-white">T' + tank.nomor_tank + '</div>';
                html += '</div>';
                html += '<div class="flex flex-col gap-1.5 mb-3">';
                html += '<div class="text-[10px] font-bold px-2 py-1.5 rounded-lg bg-blue-50 text-blue-700 truncate text-center border border-blue-100/50">' + tank.kategori + '</div>';
                html += '<div class="text-[10px] font-bold px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 truncate text-center border border-emerald-100/50">Kelas ' + tank.kelas + '</div>';
                html += '</div>';
                html += '<div class="text-[10px] font-semibold text-slate-600 truncate mb-3" title="' + tank.nama_peserta + '">' + tank.nama_peserta + '</div>';
                html += '<div class="grid grid-cols-2 gap-2">';
                html += '<button onclick="approveNominasi(this, ' + tank.nominasi_id + ')" class="py-2 rounded-lg text-[10px] font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors flex items-center justify-center gap-1"><i class="fas fa-check"></i> ACC</button>';
                html += '<button onclick="showRejectConfirm(' + tank.nominasi_id + ', ' + tank.nomor_tank + ')" class="py-2 rounded-lg text-[10px] font-bold text-white bg-red-500 hover:bg-red-600 transition-colors flex items-center justify-center gap-1"><i class="fas fa-times"></i> Tolak</button>';
                html += '</div></div>';
            });

            html += '</div></div>';
        });

        list.innerHTML = html;
    } catch(e) {
        showWarningModal([{ msg: 'Gagal memuat data nominasi.' }]);
    }
}

async function approveAllInGroup(btn, ids) {
    btn.disabled = true;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses...';

    let success = 0, fail = 0;
    for (let i = 0; i < ids.length; i++) {
        try {
            const res = await apiFetch('/api/grand-juri/nominasi-review', {
                method: 'POST',
                body: JSON.stringify({ nominasi_id: ids[i], action: 'approve' })
            });
            if (res.success) success++; else fail++;
        } catch(e) { fail++; }
    }

    if (fail === 0) {
        showSuccessPopup('Berhasil', 'Semua ' + success + ' nominasi disetujui.');
    } else {
        showWarningModal([{ msg: success + ' berhasil, ' + fail + ' gagal.' }]);
    }
    loadNominasi();
}

document.addEventListener('DOMContentLoaded', function() {
    loadNominasi();
});
</script>

</body>
</html>