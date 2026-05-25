<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Penjurian LCI – Dashboard penilaian juri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
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
        @keyframes toastIn { from { opacity: 0; transform: translateX(-50%) translateY(20px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
        .toast-in { animation: toastIn 0.3s ease-out forwards; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .slide-down { animation: slideDown 0.25s ease-out forwards; 
        }
        :root {
            --bg-main: #f0f4f8;
            --bg-card: #ffffff;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background: var(--bg-main) !important; }
        .top-nav { background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .brand h1 { font-size: 18px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .brand h1 i { font-size: 16px; }
        .brand span { font-size: 11px; color: var(--text-light); }
        .nav-right { display: flex; align-items: center; gap: 15px; }
        .nav-right .info { text-align: right; }
        .nav-right .info h4 { font-size: 13px; font-weight: 700; }
        .nav-right .info span { font-size: 10px; color: #d97706; background: #fef3c7; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
        .btn-logout { padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-family: inherit; }
        .btn-logout:hover { border-color: var(--danger); color: var(--danger); }

        /* ── WARNING MODAL ── */
        .warning-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity .3s ease; }
        .warning-overlay.show { opacity: 1; pointer-events: all; }
        .warning-card { background: white; border-radius: 24px; width: 90%; max-width: 450px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); transform: translateY(40px) scale(0.95); opacity: 0; transition: all .4s cubic-bezier(0.16,1,0.3,1); overflow: hidden; }
        .warning-overlay.show .warning-card { transform: translateY(0) scale(1); opacity: 1; }
        .warning-header { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px 30px 20px; text-align: center; }
        .warning-icon { width: 64px; height: 64px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 10px 15px -3px rgba(245,158,11,0.3); animation: iconBounce .6s cubic-bezier(0.68,-0.55,0.265,1.55) .3s both; }
        @keyframes iconBounce { 0%{transform:scale(0)} 50%{transform:scale(1.2)} 100%{transform:scale(1)} }
        .warning-icon i { font-size: 28px; color: #d97706; }
        .warning-title { font-size: 20px; font-weight: 800; color: #92400e; }
        .warning-subtitle { font-size: 13px; color: #b45309; margin-top: 4px; }
        .warning-body { padding: 24px 30px 30px; max-height: 300px; overflow-y: auto; }
        .warning-body::-webkit-scrollbar { width: 4px; }
        .warning-body::-webkit-scrollbar-thumb { background: #fde68a; border-radius: 10px; }
        .error-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .error-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; transform: translateX(-20px); opacity: 0; animation: slideInError .4s ease forwards; }
        .error-item:nth-child(1){animation-delay:.1s} .error-item:nth-child(2){animation-delay:.15s} .error-item:nth-child(3){animation-delay:.2s} .error-item:nth-child(4){animation-delay:.25s} .error-item:nth-child(5){animation-delay:.3s} .error-item:nth-child(n+6){animation-delay:.35s}
        @keyframes slideInError { to { transform: translateX(0); opacity: 1; } }
        .error-item i { color: #ef4444; font-size: 16px; margin-top: 2px; flex-shrink: 0; }
        .error-item div { flex: 1; }
        .error-item .err-title { font-size: 12px; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
        .error-item .err-desc { font-size: 12px; color: #b91c1c; font-weight: 500; }
        .warning-footer { padding: 0 30px 30px; }
        .btn-close-warning { width: 100%; padding: 14px; border: none; border-radius: 14px; background: #d97706; color: white; font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; transition: all .2s; box-shadow: 0 4px 14px rgba(217,119,6,0.3); }
        .btn-close-warning:hover { background: #b45309; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(217,119,6,0.4); }

        /* ── SUCCESS POPUP ── */
        .popup-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.4); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity .4s ease; }
        .popup-overlay.show { opacity: 1; pointer-events: all; }
        .popup-card { background: white; border-radius: 24px; padding: 48px 40px 36px; text-align: center; max-width: 360px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.15); transform: scale(0.8) translateY(20px); transition: transform .4s cubic-bezier(0.16,1,0.3,1); }
        .popup-overlay.show .popup-card { transform: scale(1) translateY(0); }
        .popup-check { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg,#22c55e,#16a34a); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(34,197,94,0.3); }
        .popup-check i { font-size: 36px; color: white; animation: checkPop .5s .3s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes checkPop { 0%{transform:scale(0) rotate(-45deg);opacity:0} 100%{transform:scale(1) rotate(0deg);opacity:1} }
        .popup-title { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .popup-desc { font-size: 13.5px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .popup-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .3s ease; box-shadow: 0 4px 12px rgba(37,99,235,0.25); }
        .popup-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.35); }

        /* ── CONFIRM POPUP ── */
        .popup-icon.confirm { background: linear-gradient(135deg,#3b82f6,#2563eb); box-shadow: 0 8px 24px rgba(59,130,246,0.3); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
        .popup-icon.confirm i { font-size: 32px; color: white; }
        .popup-btn-outline { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: 2px solid #e2e8f0; border-radius: 14px; background: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .2s; color: var(--text-muted); }
        .popup-btn-outline:hover { border-color: #94a3b8; color: var(--text-main); }
        .popup-actions { display: flex; gap: 12px; justify-content: center; }

        @media (max-width: 768px) {
            .top-nav { padding: 10px 16px; flex-wrap: wrap; gap: 8px; }
            .brand h1 { font-size: 15px; }
            .brand span { display: none; }
            .nav-right { gap: 10px; flex-wrap: wrap; }
            .nav-right .info h4 { font-size: 12px; }
            .btn-logout { padding: 6px 10px; font-size: 11px; }
            .warning-card { width: 93%; }
            .warning-header { padding: 22px 20px 14px; }
            .warning-icon { width: 52px; height: 52px; }
            .warning-icon i { font-size: 24px; }
            .warning-title { font-size: 17px; }
            .warning-body { padding: 14px 20px 20px; max-height: 260px; }
            .warning-footer { padding: 0 20px 20px; }
            .popup-card { padding: 36px 24px 28px; }
            .popup-title { font-size: 17px; }
            .popup-desc { font-size: 12.5px; margin-bottom: 22px; }
            .popup-btn, .popup-btn-outline { padding: 10px 22px; font-size: 13px; }
            .popup-actions { flex-direction: column; gap: 10px; }
            .popup-actions .popup-btn, .popup-actions .popup-btn-outline { width: 100%; justify-content: center; }
            .popup-check { width: 64px; height: 64px; }
            .popup-check i { font-size: 30px; }
            .popup-icon.confirm { width: 64px; height: 64px; }
            .popup-icon.confirm i { font-size: 28px; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800">

    <nav class="top-nav">
        <div class="brand">
            <h1><i class="fas fa-gavel"></i> Penjurian LCI</h1>
            <span>Dashboard penilaian juri</span>
        </div>
        <div class="nav-right">
            <div class="info">
                <h4>{{ Auth::user()->name }}</h4>
                <span>JURI AKTIF</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">
        @yield('content')
    </main>

    <!-- Warning Modal -->
    <div class="warning-overlay" id="warningModal">
        <div class="warning-card">
            <div class="warning-header">
                <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="warning-title">Perhatian</h2>
                <p class="warning-subtitle">Silakan periksa kembali sebelum melanjutkan:</p>
            </div>
            <div class="warning-body">
                <ul class="error-list" id="errorListContainer"></ul>
            </div>
            <div class="warning-footer">
                <button class="btn-close-warning" onclick="closeWarningModal()">
                    <i class="fas fa-check" style="margin-right:6px;"></i> OK, Saya Mengerti
                </button>
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

    <!-- Confirm Popup -->
    <div class="popup-overlay" id="popupConfirm">
        <div class="popup-card">
            <div class="popup-icon confirm"><i class="fas fa-paper-plane"></i></div>
            <h2 class="popup-title">Kirim ke Grand Juri?</h2>
            <p class="popup-desc" id="confirm-message">Nilai yang sudah Anda simpan akan dikirim ke Grand Juri untuk ditinjau. Tindakan ini tidak dapat dibatalkan.</p>
            <div class="popup-actions">
                <button class="popup-btn-outline" id="confirm-cancel">
                    <i class="fas fa-xmark"></i> Batal
                </button>
                <button class="popup-btn" id="confirm-ok" style="background:linear-gradient(135deg,#22c55e,#16a34a);box-shadow:0 4px 12px rgba(34,197,94,0.25);">
                    <i class="fas fa-paper-plane"></i> Ya, Kirim
                </button>
            </div>
        </div>
    </div>

    @yield('modals')

<script>
lucide.createIcons();
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── WARNING MODAL ──
function showWarningModal(errorsArray) {
    const container = document.getElementById('errorListContainer');
    container.innerHTML = '';
    (errorsArray || []).forEach(function(err) {
        let iconClass = 'fas fa-circle-xmark', errTitle = 'Kesalahan', errDesc = err.msg || err;
        if (err.type === 'minus') { iconClass = 'fas fa-arrow-down'; errTitle = 'Nilai Minus'; }
        else if (err.type === 'limit') { iconClass = 'fas fa-arrow-up'; errTitle = 'Melebihi Batas'; }
        else if (err.type === 'select') { iconClass = 'fas fa-hand-pointer'; errTitle = 'Aksi Diperlukan'; }
        const li = document.createElement('li');
        li.className = 'error-item';
        li.innerHTML = '<i class="' + iconClass + '"></i><div><span class="err-title">' + errTitle + '</span><span class="err-desc">' + errDesc + '</span></div>';
        container.appendChild(li);
    });
    document.getElementById('warningModal').classList.add('show');
}
function closeWarningModal() { document.getElementById('warningModal').classList.remove('show'); }
document.getElementById('warningModal')?.addEventListener('click', function(e) { if (e.target === this) closeWarningModal(); });
document.getElementById('successPopup')?.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
document.getElementById('confirm-ok')?.addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (confirmResolve) confirmResolve(true);
});
document.getElementById('confirm-cancel')?.addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (confirmResolve) confirmResolve(false);
});
document.getElementById('popupConfirm')?.addEventListener('click', function(e) { if (e.target === this) { this.classList.remove('show'); if (confirmResolve) confirmResolve(false); } });

// ── SUCCESS POPUP ──
function showSuccessPopup(title, desc) {
    document.getElementById('popupTitle').innerText = title;
    document.getElementById('popupDesc').innerHTML = desc;
    document.getElementById('successPopup').classList.add('show');
}
document.getElementById('successPopup').addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });

// ── CONFIRM POPUP ──
let confirmResolve = null;
function showConfirm(message) {
    document.getElementById('confirm-message').textContent = message;
    document.getElementById('popupConfirm').classList.add('show');
    return new Promise(resolve => { confirmResolve = resolve; });
}
document.getElementById('confirm-ok').addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (confirmResolve) confirmResolve(true);
});
document.getElementById('confirm-cancel').addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (confirmResolve) confirmResolve(false);
});
document.getElementById('popupConfirm').addEventListener('click', function(e) { if (e.target === this) { this.classList.remove('show'); if (confirmResolve) confirmResolve(false); } });

// ── TOAST (minor use only) ──
let toastTimer;
function showToast(msg, type = 'success') {
    // Fallback: redirect ke warning modal untuk error, success popup untuk success
    if (type === 'error') { showWarningModal([{type:'select', msg: msg}]); return; }
    showSuccessPopup('Berhasil', msg);
}

// ── API FETCH ──
async function apiFetch(url, opts = {}) {
    const defaults = { headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
    const res = await fetch(url, { ...defaults, ...opts, headers: { ...defaults.headers, ...(opts.headers || {}) } });
    return res.json();
}

if (typeof window.onFilterChange !== 'function') {
    window.onFilterChange = function() {};
}

</script>

@stack('scripts')

</body>
</html>