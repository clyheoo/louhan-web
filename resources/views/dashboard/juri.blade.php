<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Juri - LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after  { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --bg-main: #f0f4f8; --bg-card: #ffffff; --primary: #2563eb; --primary-dark: #1d4ed8; --primary-light: #eff6ff; --text-main: #1e293b; --text-muted: #64748b; --text-light: #94a3b8; --border: #e2e8f0; --success: #10b981; --danger: #ef4444; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-main); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        .top-nav { background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .brand h1 { font-size: 18px; font-weight: 800; color: var(--primary); }
        .brand span { font-size: 11px; color: var(--text-light); }
        .nav-right { display: flex; align-items: center; gap: 15px; }
        .nav-right .info { text-align: right; }
        .nav-right .info h4 { font-size: 13px; font-weight: 700; }
        .nav-right .info span { font-size: 10px; color: #d97706; background: #fef3c7; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
        .btn-logout { padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-logout:hover { border-color: var(--danger); color: var(--danger); }
        .main-container { padding: 20px; max-width: 1100px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; gap: 20px; }
        .card { background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; }
        .card-title { font-size: 15px; font-weight: 800; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
        .card-title i { color: var(--primary); }
        .card-body { padding: 20px; }
        .form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-family: inherit; font-size: 13px; color: var(--text-main); outline: none; transition: 0.2s; background: #f8fafc; }
        .form-control:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .form-control:disabled { background: #f1f5f9; color: var(--text-light); cursor: not-allowed; }
        .form-control option:disabled { color: var(--text-light); }
        .content-grid { display: grid; grid-template-columns: 200px 1fr; gap: 20px; }
        .kategori-list { display: flex; flex-direction: column; gap: 8px; }
        .kat-btn { padding: 12px; background: white; border: 1px solid var(--border); border-radius: 10px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: 0.2s; }
        .kat-btn:hover { border-color: var(--primary); color: var(--primary); }
        .kat-btn.active { background: var(--primary-light); border-color: var(--primary); color: var(--primary-dark); font-weight: 800; }
        .kat-btn.filled::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; float: right; color: var(--success); font-size: 11px; }
        .pedoman-box { background: var(--primary-light); border-left: 4px solid var(--primary); padding: 16px; border-radius: 0 12px 12px 0; margin-bottom: 20px; }
        .pedoman-box h3 { font-size: 14px; color: var(--primary-dark); margin-bottom: 8px; font-weight: 800; }
        .pedoman-list { list-style: none; font-size: 12.5px; color: var(--text-main); line-height: 1.6; }
        .pedoman-list li { margin-bottom: 6px; padding-left: 16px; position: relative; }
        .pedoman-list li::before { content: '•'; color: var(--primary); font-weight: bold; position: absolute; left: 0; }
        .pedoman-list ul { margin-top: 4px; padding-left: 20px; list-style: circle; }
        .score-grid { display: grid; grid-template-columns: 1fr 120px; gap: 15px; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .score-grid:last-child { border-bottom: none; }
        .score-label h4 { font-size: 13px; font-weight: 700; }
        .score-label p { font-size: 11px; color: var(--text-light); margin-top: 2px; }
        
        /* ★ DROPDOWN STYLE */
        .score-select {
            width: 100%;
            padding: 10px 8px;
            text-align: center;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-weight: 800;
            color: var(--primary);
            outline: none;
            transition: 0.2s;
            background: white;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }
        .score-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .score-select optgroup { font-weight: 700; font-size: 11px; color: var(--text-muted); }
        .score-select option { font-weight: 600; }
        
        /* ★ DEFECT BUTTON STYLE */
        .defect-btn {
            width: 100%;
            padding: 10px;
            text-align: center;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s;
            background: white;
            color: var(--text-muted);
        }
        .defect-btn:hover { border-color: var(--primary); color: var(--primary); }
        .defect-btn.minor { background: #fff7ed; color: #c2410c; border-color: #fb923c; }
        .defect-btn.minor:hover { background: #fed7aa; }
        .defect-btn.mayor { background: #fef2f2; color: #dc2626; border-color: #f87171; }
        .defect-btn.mayor:hover { background: #fecaca; }
        
        .submit-area { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .btn-primary { padding: 14px 30px; background: var(--primary); color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-primary:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }
        .result-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .result-table th { background: #f8fafc; padding: 12px; text-align: left; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .result-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; color: var(--text-main); }
        .badge-success { background: #dcfce7; color: #16a34a; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; }
        .badge-edited { background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; }
        .btn-view { background: var(--primary-light); color: var(--primary); border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; }
        .btn-view:hover { background: var(--primary); color: white; }
        .modal-bg { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99; display: none; place-items: center; }
        .modal-bg.show { display: grid; }
        .modal-box { background: white; border-radius: 20px; width: 90%; max-width: 700px; max-height: 85vh; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.2); display: grid; grid-template-rows: auto 1fr; }
        .modal-head { padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-head h3 { font-size: 16px; font-weight: 800; }
        .modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); }
        .modal-content { padding: 20px; overflow-y: auto; }
        .detail-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px; }
        .detail-table th, .detail-table td { padding: 10px; border: 1px solid var(--border); text-align: left; }
        .detail-table th { background: #f8fafc; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .detail-table tr:hover td { background: #fafafa; }
        .grand-total { text-align: right; font-size: 18px; font-weight: 900; color: var(--primary); padding-top: 10px; border-top: 2px solid var(--primary); }

        /* ★ DEFECT INFO IN DETAIL */
        .defect-badge { display: inline-block; padding: 4px 12px; border-radius: 6px; font-weight: 800; font-size: 11px; }
        .defect-badge.minor { background: #fff7ed; color: #c2410c; border: 2px solid #fb923c; }
        .defect-badge.mayor { background: #fef2f2; color: #dc2626; border: 2px solid #f87171; }
        .keterangan-box { margin-top: 16px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; }
        .keterangan-box .label { font-size: 11px; font-weight: 700; color: #991b1b; margin-bottom: 4px; }
        .keterangan-box .value { font-size: 12px; color: #b91c1c; font-weight: 500; }

        /* ── LOCKED BANNER ── */
        .locked-banner {
            display: none;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid #fbbf24;
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            animation: lockFadeIn 0.4s ease;
        }
        @keyframes lockFadeIn {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }
        .locked-banner .lock-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(245,158,11,0.3);
        }
        .locked-banner .lock-icon i { font-size: 30px; color: white; }
        .locked-banner h3 { font-size: 18px; font-weight: 800; color: #92400e; margin-bottom: 6px; }
        .locked-banner .scorer-name { font-size: 14px; color: #b45309; font-weight: 700; margin-bottom: 4px; }
        .locked-banner .scorer-name i { margin-right: 4px; }
        .locked-banner .scorer-name.grand { color: #7c3aed; }
        .locked-banner .scorer-name.grand i { color: #7c3aed; }
        .locked-banner .locked-note { font-size: 12px; color: #a16207; margin-top: 8px; }

        /* ── WARNING MODAL ── */
        .warning-overlay {
            position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(6px);
            z-index: 9999; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .warning-overlay.show { opacity: 1; pointer-events: all; }
        .warning-card {
            background: white; border-radius: 24px; width: 90%; max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: translateY(40px) scale(0.95); opacity: 0;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        .warning-overlay.show .warning-card { transform: translateY(0) scale(1); opacity: 1; }
        .warning-header { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px 30px 20px; text-align: center; }
        .warning-icon { width: 64px; height: 64px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3); animation: iconBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s both; }
        @keyframes iconBounce { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .warning-icon i { font-size: 28px; color: #d97706; }
        .warning-title { font-size: 20px; font-weight: 800; color: #92400e; }
        .warning-subtitle { font-size: 13px; color: #b45309; margin-top: 4px; }
        .warning-body { padding: 24px 30px 30px; max-height: 300px; overflow-y: auto; }
        .warning-body::-webkit-scrollbar { width: 4px; }
        .warning-body::-webkit-scrollbar-thumb { background: #fde68a; border-radius: 10px; }
        .error-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .error-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; transform: translateX(-20px); opacity: 0; animation: slideInError 0.4s ease forwards; }
        .error-item:nth-child(1) { animation-delay: 0.1s; }
        .error-item:nth-child(2) { animation-delay: 0.15s; }
        .error-item:nth-child(3) { animation-delay: 0.2s; }
        .error-item:nth-child(4) { animation-delay: 0.25s; }
        .error-item:nth-child(5) { animation-delay: 0.3s; }
        .error-item:nth-child(n+6) { animation-delay: 0.35s; }
        @keyframes slideInError { to { transform: translateX(0); opacity: 1; } }
        .error-item i { color: #ef4444; font-size: 16px; margin-top: 2px; flex-shrink: 0; }
        .error-item div { flex: 1; }
        .error-item .err-title { font-size: 12px; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px; }
        .error-item .err-desc { font-size: 12px; color: #b91c1c; font-weight: 500; }
        .warning-footer { padding: 0 30px 30px; }
        .btn-close-warning { width: 100%; padding: 14px; border: none; border-radius: 14px; background: #d97706; color: white; font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3); }
        .btn-close-warning:hover { background: #b45309; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4); }

        /* ── SUCCESS POPUP ── */
        .popup-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.4); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; }
        .popup-overlay.show { opacity: 1; pointer-events: all; }
        .popup-card { background: white; border-radius: 24px; padding: 48px 40px 36px; text-align: center; max-width: 360px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.15); transform: scale(0.8) translateY(20px); transition: transform 0.4s cubic-bezier(0.16,1,0.3,1); }
        .popup-overlay.show .popup-card { transform: scale(1) translateY(0); }
        .popup-check { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #22c55e, #16a34a); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(34,197,94,0.3); }
        .popup-check i { font-size: 36px; color: white; animation: checkPop 0.5s 0.3s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes checkPop { 0% { transform: scale(0) rotate(-45deg); opacity: 0; } 100% { transform: scale(1) rotate(0deg); opacity: 1; } }
        .popup-title { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .popup-desc { font-size: 13.5px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .popup-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: 14px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(37,99,235,0.25); }
        .popup-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.35); }
        .btn-kirim { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; transition:all .2s; font-family:inherit; }
        .btn-kirim:hover { background:#16a34a; color:white; border-color:#16a34a; }
        .btn-kirim:disabled { background:#f1f5f9; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed; }
        .badge-terkirim { background:#f5f3ff; color:#7c3aed; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:700; display:inline-flex; align-items:center; gap:3px; }
        .popup-icon.confirm { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 8px 24px rgba(59,130,246,0.3); }
        .popup-btn-outline { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: 2px solid #e2e8f0; border-radius: 14px; background: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .2s; color: var(--text-muted); }
        .popup-btn-outline:hover { border-color: #94a3b8; color: var(--text-main); }
        .popup-actions { display: flex; gap: 12px; justify-content: center; }

        /* ★ DEFECT MODAL STYLES */
        .defect-modal-box { max-width: 450px; }
        .defect-group { margin-bottom: 20px; }
        .defect-group-title { font-size: 11px; font-weight: 700; color: var(--text-light); text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px; }
        .defect-options { display: flex; flex-direction: column; gap: 8px; }
        .defect-option { display: flex; align-items: center; gap: 12px; padding: 12px; border: 2px solid var(--border); border-radius: 10px; cursor: pointer; background: white; transition: 0.2s; }
        .defect-option:hover { border-color: var(--primary); }
        .defect-option.selected { border-color: var(--primary); background: var(--primary-light); }
        .defect-option input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }
        .defect-option span { font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="brand">
            <h1><i class="fas fa-gavel"></i> PANEL JURI</h1>
            <span>Sistem Penilaian Kontes LCI</span>
        </div>
        <div class="nav-right">
            <div class="info">
                <h4>{{ $user->name }}</h4>
                <span>JURI AKTIF</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
            </form>
        </div>
    </nav>

    <div class="main-container">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fas fa-clipboard-check"></i> Input Penilaian Ikan</div>
                    <div style="font-size: 11px; color: var(--text-light);">Pilih ikan yang belum dinilai untuk mulai menilai.</div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row" style="grid-template-columns: 2fr 1fr 0.8fr 1fr 1.5fr;">
                    <div class="form-group">
                        <label class="form-label">Nomor Tank (Ikan)</label>
                        <select id="selectTank" class="form-control"><option value="">-- Pilih Ikan Berdasarkan Tank --</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kelas Penilaian</label>
                        <select id="selectKelas" class="form-control">
                            <option value="">- Pilih -</option>
                            <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <input type="text" id="inputKategori" class="form-control" value="- Pilih Ikan -" disabled style="font-weight: 700; text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kelas Asli</label>
                        <input type="text" id="inputKelas" class="form-control" value="- Pilih Ikan -" disabled style="font-weight: 700;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Juri</label>
                        <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                    </div>
                </div>

                <!-- BANNER PERINGATAN PERUBAHAN KELAS -->
                <div id="warningKelasBox" style="display: none; margin-bottom: 20px; padding: 12px 16px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; color: #92400e; font-size: 12.5px; font-weight: 600; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 14px; color: #d97706;"></i>
                    <span id="warningKelasText"></span>
                </div>

                <!-- ★ LOCKED BANNER -->
                <div class="locked-banner" id="lockedBanner">
                    <div class="lock-icon"><i class="fas fa-lock"></i></div>
                    <h3>Peserta Ini Sudah Dinilai</h3>
                    <div class="scorer-name" id="lockedScorerName"><i class="fas fa-user-pen"></i> —</div>
                    <div class="locked-note">Nilai tidak dapat diubah atau diinput ulang. Silakan pilih ikan lain yang belum dinilai.</div>
                </div>

                <!-- AREA FORM -->
                <div id="formArea">
                    <div class="content-grid">
                        <div class="kategori-list" id="katListContainer"></div>
                        <div>
                            <div class="pedoman-box">
                                <h3 id="pedoman-title">Pedoman: OVERALL IMPRESSION</h3>
                                <ul id="pedoman-list" class="pedoman-list"></ul>
                            </div>
                            <div id="form-input-area"></div>
                            <div class="submit-area">
                                <label style="display:flex; align-items:center; gap:8px; font-size:12px; color:var(--text-muted); cursor:pointer; font-weight:600;">
                                    <input type="checkbox" id="checkConfirm"> Saya yakin dengan nilai ini
                                </label>
                                <button type="button" class="btn-primary" id="btnSaveAll" onclick="submitAllScores()">
                                    <i class="fas fa-paper-plane"></i> SIMPAN SELURUH NILAI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fas fa-table-list"></i> Riwayat Penilaian Saya</div>
                    <div style="font-size: 11px; color: var(--text-light);">Klik "Lihat Detail" untuk melihat breakdown nilai.</div>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="result-table">
                    <thead><tr><th>No. Tank</th><th>Kelas</th><th>Total Nilai</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody id="tbody-scores"><tr><td colspan="6"><div style="padding:30px;text-align:center;color:var(--text-light);">Belum ada data.</div></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detail Nilai -->
    <div class="modal-bg" id="modalDetail">
        <div class="modal-box">
            <div class="modal-head">
                <h3 id="modalTitle">Detail Nilai</h3>
                <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('show')"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-content" id="modalBodyContent"></div>
        </div>
    </div>

    <!-- ★ MODAL DEFECT -->
    <div class="modal-bg" id="modalDefect">
        <div class="modal-box defect-modal-box">
            <div class="modal-head">
                <h3 id="defectModalTitle">Pilih Defect</h3>
                <button class="modal-close" onclick="closeDefectModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-content" id="defectModalBody"></div>
        </div>
    </div>

    <!-- Modal Peringatan -->
    <div class="warning-overlay" id="warningModal">
        <div class="warning-card">
            <div class="warning-header">
                <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="warning-title">Data Belum Lengkap</h2>
                <p class="warning-subtitle">Silakan periksa kembali form berikut sebelum menyimpan:</p>
            </div>
            <div class="warning-body">
                <ul class="error-list" id="errorListContainer"></ul>
            </div>
            <div class="warning-footer">
                <button class="btn-close-warning" onclick="closeWarningModal()">
                    <i class="fas fa-check" style="margin-right: 6px;"></i> OK, Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Popup Sukses -->
    <div class="popup-overlay" id="successPopup">
        <div class="popup-card">
            <div class="popup-check"><i class="fas fa-check"></i></div>
            <h2 class="popup-title" id="popupTitle">Nilai Berhasil Disimpan!</h2>
            <p class="popup-desc" id="popupDesc">Seluruh nilai telah tersimpan ke sistem.</p>
            <button class="popup-btn" onclick="document.getElementById('successPopup').classList.remove('show')">
                <i class="fas fa-circle-check"></i> OK, Tutup
            </button>
        </div>
    </div>

    <!-- Popup Konfirmasi Kirim -->
    <div class="popup-overlay" id="popupConfirm">
        <div class="popup-card">
            <div class="popup-icon confirm"><i class="fas fa-paper-plane"></i></div>
            <h2 class="popup-title">Kirim ke Grand Juri?</h2>
            <p class="popup-desc">Nilai yang sudah Anda simpan akan dikirim ke Grand Juri untuk ditinjau. Tindakan ini tidak dapat dibatalkan.</p>
            <div class="popup-actions">
                <button class="popup-btn-outline" onclick="document.getElementById('popupConfirm').classList.remove('show')">
                    <i class="fas fa-xmark"></i> Batal
                </button>
                <button class="popup-btn" id="btnConfirmKirim" style="background:linear-gradient(135deg,#22c55e,#16a34a);box-shadow:0 4px 12px rgba(34,197,94,0.25);">
                    <i class="fas fa-paper-plane"></i> Ya, Kirim
                </button>
            </div>
        </div>
    </div>

<script>
// ==================== PEDOMAN DATA ====================
const pedomanData = {
    overall: { title: "Pedoman: OVERALL IMPRESSION", list: "<li>IMPRESSION (100%): Menarik perhatian pada pandangan pertama.</li><li>Memiliki keistimewaan yang menarik.</li><li>MENTAL: Ikan tidak takut, aktif berinteraksi, menguasai area.</li><li>KESEHATAN: Tidak terkena penyakit, tidak luka, performa bagus.</li>" },
    head: { title: "Pedoman: HEAD (KEPALA)", list: "<li>SIZE (60%): Ukuran kepala menjadi prioritas utama.</li><li>BENTUK (40%):<ul><li>Kepala Bulat Bola (Nilai 85 - 95)</li><li>Kepala Swan Head (Nilai 70 - 80)</li><li>Kepala Tidak Simetris (Nilai 60 - 70)</li></ul></li>" },
    face: { title: "Pedoman: FACE (WAJAH)", list: "<li>Pipi: Tidak terlalu tembem maupun berkerut.</li><li>Mata: Rata, seimbang, tidak ada titik putih.</li><li>Bibir: Menutup simetris (garis lurus atas-bawah).</li><li>Kondisi: Tidak berair, tidak ada marking di bawah mata.</li><li>Insang: Tertutup rapat & tidak terdorong dayung.</li>" },
    body: { title: "Pedoman: BODY (BADAN)", list: "<li>BENTUK (50%):<ul><li>Kotak tidak simetris (Nilai 80 - 90)</li><li>Daun simetris (Nilai 70 - 80)</li><li>Daun tidak simetris (Nilai 60 - 70)</li><li>Lancip (Nilai 10 - 50)</li></ul></li><li>PROPORSIONAL (40%): Perbandingan ideal 1 : 1.5</li><li>PANGKAL (10%): Besar dan kokoh.</li><li><i>Catatan Bonsai: Short body > 1:1.2 diskualifikasi kelas bonsai.</i></li>" },
    marking: { title: "Pedoman: MARKING (MUTIARA HITAM)", list: "<li>FULLNESS (40%): Sepanjang badan (dari pangkal ekor s.d. insang).</li><li>CONTRAST (40%): Hitam pekat.</li><li>BENTUK (20%): Rapi.</li><li><i>Catatan Free Marking: Marking tidak boleh lebih dari setengah badan.</i></li>" },
    pearl: { title: "Pedoman: PEARL (MUTIARA)", list: "<li>SHINING (45%): Berkilau.</li><li>FULLNESS (35%): Penuh sampai kepala.</li><li>BENTUK (20%): Rapi (tipe cacing/pasir).</li><li><i>Catatan Klasik: Mutiara tidak boleh melebihi 25%.</i></li>" },
    color: { title: "Pedoman: COLOR (WARNA)", list: "<li>KOMPOSISI (45%): Memiliki dua warna (Dasar merah/kuning).</li><li>KECERAHAN (35%): Warna bersih.</li><li>FULLNESS (20%): Warna merata.</li>" },
    finnage: { title: "Pedoman: FINNAGE (SIRIP)", list: "<li>BENTUK (75%):<ul><li>Sirip atas & bawah menutup ekor (wrapping).</li><li>Ekor mekar (seperti kipas).</li><li>Dayung seimbang.</li></ul></li><li>KECERAHAN (25%): Bersih, tidak ada bercak/jamur.</li>" }
};

// ==================== ★ DEFECT DATA ====================
const MINOR_DEFECTS = [
    'Kutil', 'Bibir Miring', 'Katarak', 'Abses / Luka',
    'Fintail Bleaching', 'Pangkal Ekor Naik/Trn', 'Dayung Tdk Seimbang'
];

const MAYOR_DEFECTS = [
    'Bagian Bibir Hilang', 'Mulut Terbuka Terus', 'Muka Miring',
    'Pangkal Bengkok/Patah', 'Fin/Tulang Hilang 1 Ruas'
];

const DEFECT_OPTIONS = {
    raw_head_penalty: [
        { label: '--- AMAN ---', options: [{ value: '0', label: 'Aman (0)' }] },
        { label: '--- MINOR ---', options: [{ value: 'Kutil', label: 'Kutil' }] }
    ],
    raw_face_penalty: [
        { label: '--- AMAN ---', options: [{ value: '0', label: 'Aman (0)' }] },
        { label: '--- MINOR ---', options: [
            { value: 'Bibir Miring', label: 'Bibir Miring' },
            { value: 'Katarak', label: 'Katarak' }
        ]},
        { label: '--- MAYOR ---', options: [
            { value: 'Bagian Bibir Hilang', label: 'Bagian Bibir Hilang' },
            { value: 'Mulut Terbuka Terus', label: 'Mulut Terbuka Terus' },
            { value: 'Muka Miring', label: 'Muka Miring' }
        ]}
    ],
    raw_body_penalty: [
        { label: '--- AMAN ---', options: [{ value: '0', label: 'Aman (0)' }] },
        { label: '--- MINOR ---', options: [
            { value: 'Kutil', label: 'Kutil' },
            { value: 'Abses / Luka', label: 'Abses / Luka' }
        ]},
        { label: '--- MAYOR ---', options: [
            { value: 'Pangkal Bengkok/Patah', label: 'Pangkal Bengkok/Patah' }
        ]}
    ],
    raw_finnage_penalty: [
        { label: '--- AMAN ---', options: [{ value: '0', label: 'Aman (0)' }] },
        { label: '--- MINOR ---', options: [
            { value: 'Kutil', label: 'Kutil' },
            { value: 'Fintail Bleaching', label: 'Fintail Bleaching' },
            { value: 'Pangkal Ekor Naik/Trn', label: 'Pangkal Ekor Naik/Trn' },
            { value: 'Dayung Tdk Seimbang', label: 'Dayung Tdk Seimbang' }
        ]},
        { label: '--- MAYOR ---', options: [
            { value: 'Fin/Tulang Hilang 1 Ruas', label: 'Fin/Tulang Hilang 1 Ruas' }
        ]}
    ]
};

// ==================== ★ DROPDOWN OPTIONS GENERATOR ====================
function getStandardOptions() {
    const options = [];
    for (let i = 90; i >= 10; i -= 5) {
        options.push({ value: i.toString(), label: i.toString() });
    }
    return options;
}

// ==================== ★ FORM FIELDS (UPDATED) ====================
const formFields = {
    overall: [{id:'impression', label:'Impression (Mental & Kesehatan)', desc:'Kelipatan 5 (10-90)', type: 'standard'}],
    head: [
        {id:'size', label:'Size (Ukuran)', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'bentuk', label:'Bentuk Kepala', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'defect', label:'Defect', desc:'Pilih jika ada defect', type: 'defect', defectKey: 'raw_head_penalty'}
    ],
    face: [
        {id:'face', label:'Face', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'defect', label:'Defect', desc:'Pilih jika ada defect', type: 'defect', defectKey: 'raw_face_penalty'}
    ],
    body: [
        {id:'bentuk', label:'Bentuk Badan', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'proporsi', label:'Proporsional', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'pangkal', label:'Pangkal', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'defect', label:'Defect', desc:'Pilih jika ada defect', type: 'defect', defectKey: 'raw_body_penalty'}
    ],
    marking: [
        {id:'fullness', label:'Fullness', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'contrast', label:'Contrast', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'bentuk', label:'Bentuk', desc:'Kelipatan 5 (10-90)', type: 'standard'}
    ],
    pearl: [
        {id:'shining', label:'Shining', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'fullness', label:'Fullness', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'bentuk', label:'Bentuk', desc:'Kelipatan 5 (10-90)', type: 'standard'}
    ],
    color: [
        {id:'komposisi', label:'Komposisi', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'kecerahan', label:'Kecerahan', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'fullness', label:'Fullness', desc:'Kelipatan 5 (10-90)', type: 'standard'}
    ],
    finnage: [
        {id:'bentuk', label:'Bentuk Sirip & Ekor', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'kecerahan', label:'Kecerahan', desc:'Kelipatan 5 (10-90)', type: 'standard'},
        {id:'defect', label:'Defect', desc:'Pilih jika ada defect', type: 'defect', defectKey: 'raw_finnage_penalty'}
    ]
};

// ==================== STATE ====================
let currentTab = 'overall';
let memoryScores = { overall: {}, head: {}, face: {}, body: {}, marking: {}, pearl: {}, color: {}, finnage: {} };
let defectData = {
    raw_head_penalty: ['0'],
    raw_face_penalty: ['0'],
    raw_body_penalty: ['0'],
    raw_finnage_penalty: ['0']
};
let detailDataStorage = {};
let myScoredMap = {};
let scoredCounts = {};
let activeDefectKey = null;

var pendingKirimId = null;
var pendingKirimBtn = null;

// ==================== ★ DEFECT EVALUATION ====================
function evaluateDefects() {
    const parts = ['head', 'face', 'body', 'finnage'];
    let partStatus = {
        head: { minor: false, mayor: false, items: [] },
        face: { minor: false, mayor: false, items: [] },
        body: { minor: false, mayor: false, items: [] },
        finnage: { minor: false, mayor: false, items: [] }
    };

    let minorCount = 0;

    parts.forEach(p => {
        let defs = defectData['raw_' + p + '_penalty'] || ['0'];
        if (!Array.isArray(defs)) defs = [defs];

        defs.forEach(d => {
            if (d && d !== '0') {
                partStatus[p].items.push(d);
                if (MINOR_DEFECTS.includes(d)) {
                    minorCount++;
                    partStatus[p].minor = true;
                }
                if (MAYOR_DEFECTS.includes(d)) {
                    partStatus[p].mayor = true;
                }
            }
        });
    });

    const isGlobalMayor = minorCount >= 3;
    let results = {};
    let globalNotes = [];

    parts.forEach(p => {
        if (partStatus[p].items.length > 0) {
            let isMayor = partStatus[p].mayor || (partStatus[p].minor && isGlobalMayor);
            results[p + '_penalty'] = isMayor ? '30%' : '10%';
            globalNotes.push(p.toUpperCase() + ': ' + partStatus[p].items.join(', '));
        } else {
            results[p + '_penalty'] = '';
        }
    });

    results.keterangan = globalNotes.join(' | ');
    return results;
}

function evaluateDefectsFromData(data) {
    const parts = ['head', 'face', 'body', 'finnage'];
    let partStatus = {};
    parts.forEach(p => { partStatus[p] = { minor: false, mayor: false, items: [] }; });

    let minorCount = 0;

    parts.forEach(p => {
        let defs = data['raw_' + p + '_penalty'] || ['0'];
        if (!Array.isArray(defs)) defs = [defs];

        defs.forEach(d => {
            if (d && d !== '0') {
                partStatus[p].items.push(d);
                if (MINOR_DEFECTS.includes(d)) { minorCount++; partStatus[p].minor = true; }
                if (MAYOR_DEFECTS.includes(d)) { partStatus[p].mayor = true; }
            }
        });
    });

    const isGlobalMayor = minorCount >= 3;
    let results = {};

    parts.forEach(p => {
        if (partStatus[p].items.length > 0) {
            let isMayor = partStatus[p].mayor || (partStatus[p].minor && isGlobalMayor);
            results['raw_' + p + '_penalty'] = isMayor ? '30%' : '10%';
        } else {
            results['raw_' + p + '_penalty'] = '';
        }
    });

    return results;
}

// ==================== DEFECT MODAL FUNCTIONS ====================
function openDefectModal(defectKey) {
    activeDefectKey = defectKey;
    const partName = defectKey.replace('raw_', '').replace('_penalty', '').toUpperCase();
    document.getElementById('defectModalTitle').innerText = 'Pilih Defect - ' + partName;

    const options = DEFECT_OPTIONS[defectKey];
    const currentValues = defectData[defectKey] || ['0'];

    let html = '';
    options.forEach(function(group) {
        html += '<div class="defect-group">';
        html += '<div class="defect-group-title">' + group.label + '</div>';
        html += '<div class="defect-options">';

        group.options.forEach(function(opt) {
            const isChecked = currentValues.includes(opt.value);
            html += '<label class="defect-option ' + (isChecked ? 'selected' : '') + '" onclick="toggleDefect(\'' + defectKey + '\', \'' + opt.value.replace(/'/g, "\\'") + '\')">';
            html += '<input type="checkbox" ' + (isChecked ? 'checked' : '') + ' onclick="event.stopPropagation(); toggleDefect(\'' + defectKey + '\', \'' + opt.value.replace(/'/g, "\\'") + '\')">';
            html += '<span>' + opt.label + '</span>';
            html += '</label>';
        });

        html += '</div></div>';
    });

    document.getElementById('defectModalBody').innerHTML = html;
    document.getElementById('modalDefect').classList.add('show');
}

function closeDefectModal() {
    document.getElementById('modalDefect').classList.remove('show');
    activeDefectKey = null;
    renderFormInputs(currentTab);
}

function toggleDefect(defectKey, value) {
    let current = defectData[defectKey] || ['0'];

    if (value === '0') {
        defectData[defectKey] = ['0'];
    } else {
        current = current.filter(v => v !== '0');
        if (current.includes(value)) {
            current = current.filter(v => v !== value);
        } else {
            current.push(value);
        }
        if (current.length === 0) current = ['0'];
        defectData[defectKey] = current;
    }

    openDefectModal(defectKey);
}

// ==================== KIRIM KE GRAND ====================
function kirimKeGrand(scoringId, btnEl) {
    pendingKirimId = scoringId;
    pendingKirimBtn = btnEl;
    document.getElementById('popupConfirm').classList.add('show');
}

document.getElementById('btnConfirmKirim').addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (!pendingKirimId || !pendingKirimBtn) return;

    var scoringId = pendingKirimId;
    var btnEl = pendingKirimBtn;
    pendingKirimId = null;
    pendingKirimBtn = null;

    btnEl.disabled = true;
    btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

    fetch('/api/juri/kirim-ke-grand', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'), scoring_id: scoringId })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            document.getElementById('popupTitle').innerHTML = 'Berhasil Dikirim!';
            document.getElementById('popupDesc').innerHTML = 'Nilai telah dikirim ke Grand Juri untuk ditinjau.';
            document.getElementById('successPopup').classList.add('show');
            btnEl.outerHTML = '<span class="badge-terkirim"><i class="fas fa-check"></i> Terkirim</span>';
        } else {
            showWarningModal([{ type: 'select', msg: d.message || 'Gagal mengirim nilai.' }]);
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim';
        }
    })
    .catch(function() {
        showWarningModal([{ type: 'select', msg: 'Gagal mengirim. Periksa koneksi internet Anda.' }]);
        btnEl.disabled = false;
        btnEl.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim';
    });
});

function renderKategoriList() {
    const c = document.getElementById('katListContainer');
    c.innerHTML = '';
    Object.keys(pedomanData).forEach(function(kat) {
        const btn = document.createElement('button');
        btn.className = 'kat-btn ' + (kat === currentTab ? 'active' : '');
        btn.id = 'btn-' + kat;
        btn.innerText = kat.charAt(0).toUpperCase() + kat.slice(1);
        btn.onclick = function() { changeKat(kat); };
        c.appendChild(btn);
    });
}

function changeKat(kat) {
    saveCurrentTabToMemory();
    currentTab = kat;
    const allBtns = document.querySelectorAll('.kat-btn');
    for (let i = 0; i < allBtns.length; i++) { allBtns[i].classList.remove('active'); }
    const activeBtn = document.getElementById('btn-' + kat);
    if (activeBtn) activeBtn.classList.add('active');
    document.getElementById('pedoman-title').innerText = pedomanData[kat].title;
    document.getElementById('pedoman-list').innerHTML = pedomanData[kat].list;
    renderFormInputs(kat);
}

// ★ FUNGSI RENDER FORM INPUTS (DENGAN DROPDOWN)
function renderFormInputs(kat) {
    if (!formFields[kat]) return;
    if (!memoryScores[kat]) { memoryScores[kat] = {}; }
    let html = '';
    
    formFields[kat].forEach(function(field) {
        if (field.type === 'defect') {
            // ★ RENDER TOMBOL DEFECT DENGAN INFO PENGURANGAN
            const defectKey = field.defectKey;
            const currentValues = defectData[defectKey] || ['0'];
            const isAman = currentValues.includes('0') || currentValues.length === 0;
            const evaluated = evaluateDefects();
            const evalString = evaluated[defectKey.substring(4)];
            
            let btnLabel = 'AMAN';
            let btnClass = 'defect-btn';
            
            if (!isAman && evalString && evalString !== '') {
                const isMayor = evalString === '30%';
                const persen = isMayor ? 30 : 10;
                const defectNames = currentValues.filter(v => v !== '0').join(', ');
                btnLabel = defectNames + ' (-' + persen + '%)';
                btnClass = 'defect-btn ' + (isMayor ? 'mayor' : 'minor');
            }
            
            html += '<div class="score-grid">';
            html += '<div class="score-label"><h4>' + field.label + '</h4><p>' + field.desc + '</p></div>';
            html += '<button type="button" class="' + btnClass + '" onclick="openDefectModal(\'' + defectKey + '\')">' + btnLabel + '</button>';
            html += '</div>';
            
        } else {
            // ★ RENDER DROPDOWN STANDAR (10-90 KELIPATAN 5)
            const options = getStandardOptions();
            const val = memoryScores[kat][field.id] || '';
            html += '<div class="score-grid">';
            html += '<div class="score-label"><h4>' + field.label + '</h4><p>' + field.desc + '</p></div>';
            html += '<select class="score-select" id="input-' + field.id + '" onchange="updateMemory()">';
            html += '<option value="">-</option>';
            options.forEach(function(opt) {
                html += '<option value="' + opt.value + '"' + (val == opt.value ? ' selected' : '') + '>' + opt.label + '</option>';
            });
            html += '</select></div>';
        }
    });
    
    document.getElementById('form-input-area').innerHTML = html;
}

function updateMemory() {
    if (!formFields[currentTab]) return;
    if (!memoryScores[currentTab]) { memoryScores[currentTab] = {}; }
    formFields[currentTab].forEach(function(field) {
        if (field.type === 'defect') return; // Skip defect field
        const el = document.getElementById('input-' + field.id);
        if (el) {
            memoryScores[currentTab][field.id] = el.value;
        }
    });
    updateFilledBadges();
}

function saveCurrentTabToMemory() {
    if (!formFields[currentTab]) return;
    if (!memoryScores[currentTab]) { memoryScores[currentTab] = {}; }
    formFields[currentTab].forEach(function(field) {
        if (field.type === 'defect') return;
        const el = document.getElementById('input-' + field.id);
        if (el) memoryScores[currentTab][field.id] = el.value;
    });
}

function updateFilledBadges() {
    Object.keys(formFields).forEach(function(kat) {
        const btn = document.getElementById('btn-' + kat);
        if (!btn) return;
        if (!memoryScores[kat]) { memoryScores[kat] = {}; }
        let isFilled = true;
        formFields[kat].forEach(function(f) {
            if (f.type === 'defect') return; // Skip defect dari pengecekan filled
            if (!memoryScores[kat][f.id] && memoryScores[kat][f.id] !== 0) isFilled = false;
        });
        if (isFilled) btn.classList.add('filled'); else btn.classList.remove('filled');
    });
}

function submitAllScores() {
    saveCurrentTabToMemory();

    if (!document.getElementById('selectTank').value) {
        showWarningModal([{ type: 'select', msg: 'Anda belum memilih Nomor Tank.' }]);
        return;
    }

    if (!document.getElementById('checkConfirm').checked) {
        showWarningModal([{ type: 'select', msg: 'Checkbox <b>"Saya yakin dengan nilai ini"</b> belum dicentang.' }]);
        return;
    }

    let errors = [];
    let grandTotal = 0;
    let grandTotalAfterDefect = 0;

    Object.keys(formFields).forEach(function(kat) {
        if (!memoryScores[kat]) { memoryScores[kat] = {}; }
        let subTotal = 0;
        
        formFields[kat].forEach(function(field) {
            if (field.type === 'defect') return;
            const val = memoryScores[kat][field.id];
            const namaKat = kat.charAt(0).toUpperCase() + kat.slice(1);
            if (val === "" || val === null || val === undefined) {
                errors.push({ type: 'empty', msg: 'Menu <b>' + namaKat + '</b>, kolom: <b>' + field.label + '</b>' });
            } else if (parseInt(val) < 0) {
                errors.push({ type: 'minus', msg: 'Menu <b>' + namaKat + '</b>, kolom: <b>' + field.label + '</b> (Tidak boleh minus)' });
            } else {
                subTotal += parseInt(val);
            }
        });
        
        grandTotal += subTotal;
        
        // ★ KURANGI DEFECT DARI SUBTOTAL KATEGORI
        const evaluated = evaluateDefects();
        const penaltyKey = kat + '_penalty';
        const penaltyStr = evaluated[penaltyKey];
        if (penaltyStr && penaltyStr !== '') {
            const persen = parseInt(penaltyStr);
            subTotal = subTotal * (1 - persen / 100);
        }
        
        grandTotalAfterDefect += subTotal;
    });

    if (errors.length > 0) { showWarningModal(errors); return; }

    const btn = document.getElementById('btnSaveAll');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> MEMPROSES...';

    const dropdownKelas = document.getElementById('selectKelas');
    const elKelasAsli = document.getElementById('inputKelas');
    let kelasAkhir = 'A';
    if (dropdownKelas && dropdownKelas.value) { kelasAkhir = dropdownKelas.value; }
    else if (elKelasAsli && elKelasAsli.value && elKelasAsli.value !== '- Pilih Ikan -') { kelasAkhir = elKelasAsli.value.replace('Kelas ', ''); }

    const payload = {
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        ikan_id: document.getElementById('selectTank').value,
        kelas: kelasAkhir,
        all_scores: memoryScores,
        defect_data: defectData // ★ KIRIM DEFECT DATA
    };

    fetch('/api/juri/simpan-nilai', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(d) {
        if (d.success) {
            document.getElementById('popupTitle').innerHTML = 'Nilai Berhasil Disimpan!';
            document.getElementById('popupDesc').innerHTML = 
                'Total nilai mentah: <strong style="color:var(--text-main); font-size:15px;">' + grandTotal + '</strong>' +
                '<br>Setelah defect: <strong style="color:#2563eb; font-size:18px;">' + Math.round(grandTotalAfterDefect) + '</strong>' +
                '<br><span style="font-size:11px;color:var(--text-muted);">Point final akan dihitung oleh sistem menggunakan bobot kategori.</span>';
            document.getElementById('successPopup').classList.add('show');
            memoryScores = { overall: {}, head: {}, face: {}, body: {}, marking: {}, pearl: {}, color: {}, finnage: {} };
            defectData = { raw_head_penalty: ['0'], raw_face_penalty: ['0'], raw_body_penalty: ['0'], raw_finnage_penalty: ['0'] };
            document.getElementById('checkConfirm').checked = false;
            changeKat('overall');
            loadJuriData();
        } else {
            showWarningModal([{ type: 'select', msg: d.message || 'Terjadi kesalahan.' }]);
        }
    })
    .catch(function() { alert('Terjadi kesalahan pada server'); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> SIMPAN SELURUH NILAI';
    });
}

/* ================================================================
   LOAD DATA
   ================================================================ */
function loadJuriData() {
    fetch('/api/juri/data', { headers: { 'Accept': 'application/json' } })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        const sel = document.getElementById('selectTank');
        sel.innerHTML = '<option value="">-- Pilih Ikan Berdasarkan Tank --</option>';
        sel.disabled = false;

        myScoredMap = {};
        if (data.all_scored) {
            Object.keys(data.all_scored).forEach(function(ikanId) {
                myScoredMap[ikanId] = data.all_scored[ikanId];
            });
        }

        scoredCounts = data.scored_counts || {};

        data.available_tanks.forEach(function(t) {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.setAttribute('data-kategori', t.kategori);
            opt.setAttribute('data-kelas', t.kelas);
            opt.textContent = 'Tank ' + t.nomor_tank;

            if (myScoredMap[t.id] && myScoredMap[t.id].is_mine) {
                opt.disabled = true;
                opt.textContent += '  [✓ Sudah Anda Nilai]';
            } else if (scoredCounts[t.id]) {
                var jml = scoredCounts[t.id];
                var labelJuri = jml === 1 ? '1 juri' : jml + ' juri';
                opt.textContent += '  · Sudah dinilai oleh ' + labelJuri;
            }

            sel.appendChild(opt);
        });

        document.getElementById('inputKategori').value = '- Pilih Ikan -';
        document.getElementById('inputKelas').value = '- Pilih Ikan -';
        document.getElementById('selectKelas').value = '';
        document.getElementById('warningKelasBox').style.display = 'none';
        showIdleState();
        memoryScores = { overall: {}, head: {}, face: {}, body: {}, marking: {}, pearl: {}, color: {}, finnage: {} };
        defectData = { raw_head_penalty: ['0'], raw_face_penalty: ['0'], raw_body_penalty: ['0'], raw_finnage_penalty: ['0'] };
        renderFormInputs(currentTab);
        updateFilledBadges();

        sel.onchange = function() {
            const selectedId = this.value;

            if (selectedId === "") {
                showIdleState();
                document.getElementById('inputKategori').value = '- Pilih Ikan -';
                document.getElementById('inputKelas').value = '- Pilih Ikan -';
                document.getElementById('selectKelas').value = "";
                document.getElementById('warningKelasBox').style.display = 'none';
                return;
            }

            const selectedOpt = this.options[this.selectedIndex];
            const elKategori = document.getElementById('inputKategori');
            const elKelasAsli = document.getElementById('inputKelas');
            const dropdownKelas = document.getElementById('selectKelas');
            const warningBox = document.getElementById('warningKelasBox');

            elKategori.value = selectedOpt.getAttribute('data-kategori');

            const kelasAsliUser = selectedOpt.getAttribute('data-kelas') || '';
            if (dropdownKelas) dropdownKelas.value = kelasAsliUser;
            if (elKelasAsli) elKelasAsli.value = kelasAsliUser ? 'Kelas ' + kelasAsliUser : '- Pilih Ikan -';
            if (dropdownKelas && dropdownKelas.value === "") dropdownKelas.selectedIndex = 1;
            if (warningBox) warningBox.style.display = 'none';

            if (dropdownKelas) {
                dropdownKelas.onchange = function() {
                    if (this.value === "") {
                        if (warningBox) warningBox.style.display = 'none';
                    } else if (this.value !== kelasAsliUser) {
                        if (warningBox) {
                            warningBox.style.display = 'flex';
                            document.getElementById('warningKelasText').innerHTML = '<b>Perhatian:</b> Anda mengubah kelas penilaian menjadi <b>Kelas ' + this.value + '</b> (Kelas asli: Kelas ' + kelasAsliUser + '). Pastikan sudah sesuai keputusan panitia.';
                        }
                    } else {
                        if (warningBox) warningBox.style.display = 'none';
                    }
                };
            }

            memoryScores = { overall: {}, head: {}, face: {}, body: {}, marking: {}, pearl: {}, color: {}, finnage: {} };
            defectData = { raw_head_penalty: ['0'], raw_face_penalty: ['0'], raw_body_penalty: ['0'], raw_finnage_penalty: ['0'] };
            document.getElementById('checkConfirm').checked = false;
            document.getElementById('lockedBanner').style.display = 'none';
            document.getElementById('formArea').style.display = 'block';
            renderFormInputs(currentTab);
            updateFilledBadges();
        };

        const tbody = document.getElementById('tbody-scores');
        tbody.innerHTML = '';
        detailDataStorage = {};

        if (!data.my_scores || data.my_scores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5"><div style="padding:30px;text-align:center;color:var(--text-light);">Belum ada data penilaian.</div></td></tr>';
            return;
        }

        data.my_scores.forEach(function(s) {
            detailDataStorage[s.ikan_id] = {
                tank: s.ikan.nomor_tank,
                nama: s.ikan.peserta.nama_peserta,
                kategori: s.ikan.kategori,
                nilai: s.nilai_detail,
                total: s.total_nilai,
                keterangan: s.keterangan || '',
                raw_head_penalty: s.raw_head_penalty || ['0'],
                raw_face_penalty: s.raw_face_penalty || ['0'],
                raw_body_penalty: s.raw_body_penalty || ['0'],
                raw_finnage_penalty: s.raw_finnage_penalty || ['0']
            };

            var statusHtml = '<span class="badge-success">SUBMITTED</span>';
            if (s.edited_by_grand_juri) {
                statusHtml = '<span class="badge-edited"><i class="fas fa-crown" style="margin-right:3px;font-size:9px;"></i> GRAND EDITED</span>';
            }

            var tr = document.createElement('tr');
            var aksiHtml = '<button class="btn-view" onclick="showDetail(' + s.ikan_id + ')"><i class="fas fa-eye"></i> Lihat Detail</button>';
            if (s.submitted_to_grand) {
                aksiHtml += ' <span class="badge-terkirim"><i class="fas fa-check"></i> Terkirim</span>';
            } else {
                aksiHtml += ' <button class="btn-kirim" onclick="kirimKeGrand(' + s.id + ', this)"><i class="fas fa-paper-plane"></i> Kirim</button>';
            }
            tr.innerHTML =
            '<td style="font-weight:700; color:var(--primary);">Tank ' + s.ikan.nomor_tank + ' <span style="font-size:10px;color:var(--text-light);">(' + s.ikan.kategori + ')</span></td>' +
            '<td>Kelas ' + s.kelas + '</td>' +
            '<td style="font-weight:800; font-size:15px;">' + s.total_nilai + '</td>' +
            '<td>' + statusHtml + '</td>' +
            '<td>' + aksiHtml + '</td>';
            tbody.appendChild(tr);
        });
    })
    .catch(function(err) {
        console.error("Error fetching data:", err);
        document.getElementById('selectTank').innerHTML = '<option disabled>Gagal memuat data</option>';
    });
}

function showIdleState() {
    var banner = document.getElementById('lockedBanner');
    banner.style.display = 'block';
    document.getElementById('formArea').style.display = 'none';

    banner.style.background = 'linear-gradient(135deg, #eff6ff, #dbeafe)';
    banner.style.borderColor = '#93c5fd';

    var lockIcon = banner.querySelector('.lock-icon');
    lockIcon.style.background = 'linear-gradient(135deg, #3b82f6, #2563eb)';
    lockIcon.style.boxShadow = '0 8px 20px rgba(59,130,246,0.3)';
    lockIcon.innerHTML = '<i class="fas fa-hand-pointer"></i>';

    banner.querySelector('h3').innerText = 'Silakan Pilih Peserta';
    banner.querySelector('h3').style.color = '#1e40af';

    var nameEl = document.getElementById('lockedScorerName');
    nameEl.innerHTML = '';
    nameEl.className = 'scorer-name';

    var noteEl = banner.querySelector('.locked-note');
    noteEl.innerHTML = 'Pilih Nomor Tank pada dropdown di atas untuk mulai menginput penilaian.<br><span style="color:#dc2626; font-weight:700; margin-top:8px; display:inline-block;"><i class="fas fa-exclamation-circle"></i> Nilai yang sudah Anda simpan tidak dapat diubah.</span>';
    noteEl.style.color = '#1d4ed8';
}

/* ================================================================
   MODAL DETAIL (DITAMBAH INFO DEFECT)
   ================================================================ */
function showDetail(id) {
    const data = detailDataStorage[id];
    if (!data) return;
    document.getElementById('modalTitle').innerText = 'Detail Nilai Anda: Tank ' + data.tank + ' (' + data.kategori + ')';
    let html = '<table class="detail-table"><thead><tr><th style="width:25%;">KOMPONEN</th><th style="width:15%;">SKALA</th><th style="text-align:center; width:15%;">NILAI</th></tr></thead><tbody>';
    Object.keys(formFields).forEach(function(kat) {
        let subTotal = 0;
        html += '<tr style="background:#f8fafc;"><td colspan="3" style="font-weight:800; text-transform:uppercase; font-size:12px; color:var(--primary); letter-spacing:1px; padding:12px;"><i class="fas fa-tag" style="margin-right:6px;"></i>' + kat.toUpperCase() + '</td></tr>';
        formFields[kat].forEach(function(field) {
            if (field.type === 'defect') return;
            let val = 0;
            if (data.nilai[kat]) {
                if (data.nilai[kat][field.id] !== undefined) {
                    val = data.nilai[kat][field.id];
                }
            }
            subTotal += parseInt(val);
            html += '<tr><td style="padding-left:20px; font-weight:600;">' + field.label + '</td><td style="font-size:12px; color:var(--text-light);">' + field.desc + '</td><td style="text-align:center; font-weight:800; font-size:15px; color:var(--text-main);">' + val + '</td></tr>';
        });
        
        // ★ TAMPILKAN DEFECT JIKA ADA DI KOMPONEN INI
        const defectKeyForKat = 'raw_' + kat + '_penalty';
        let hasDefect = false;
        let defectPersen = 0;
        if (data[defectKeyForKat]) {
            let defs = data[defectKeyForKat];
            if (!Array.isArray(defs)) defs = [defs];
            const defectItems = defs.filter(v => v !== '0');
            if (defectItems.length > 0) {
                hasDefect = true;
                const evaluated = evaluateDefectsFromData(data);
                const penaltyStr = evaluated[defectKeyForKat] || '';
                const isMayor = penaltyStr === '30%';
                defectPersen = isMayor ? 30 : 10;
                html += '<tr style="background:#fef2f2;"><td style="padding-left:20px; font-weight:700; color:#dc2626;"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>Defect</td><td style="font-size:12px; color:#b91c1c; font-weight:600;">' + defectItems.join(', ') + '</td><td style="text-align:center; font-weight:800; font-size:14px; color:#dc2626;">-' + defectPersen + '%</td></tr>';
            }
        }
        
        // ★ HITUNG SUBTOTAL SETELAH DEFECT
        let displaySubtotal = subTotal;
        if (hasDefect) {
            displaySubtotal = Math.round(subTotal * (1 - defectPersen / 100) * 10) / 10;
        }
        
        html += '<tr style="background:#eff6ff;"><td colspan="2" style="text-align:right; font-weight:700; font-size:12px; padding:10px;">Subtotal ' + kat.toUpperCase() + '</td><td style="text-align:center; font-weight:800; color:var(--primary); font-size:13px; padding:10px;">' + displaySubtotal + '</td></tr>';
    });
    html += '</tbody></table><div class="grand-total">TOTAL NILAI: ' + data.total + '</div>';
    
    document.getElementById('modalBodyContent').innerHTML = html;
    document.getElementById('modalDetail').classList.add('show');
}

/* ================================================================
   WARNING MODAL
   ================================================================ */
function showWarningModal(errorsArray) {
    const container = document.getElementById('errorListContainer');
    container.innerHTML = '';
    errorsArray.forEach(function(err) {
        let iconClass = 'fas fa-circle-xmark', errTitle = 'Kolom Kosong', errDesc = err.msg;
        if (err.type === 'minus') { iconClass = 'fas fa-arrow-down'; errTitle = 'Nilai Minus Dilarang'; }
        else if (err.type === 'limit') { iconClass = 'fas fa-arrow-up'; errTitle = 'Melebihi Batas Maksimal'; }
        else if (err.type === 'select') { iconClass = 'fas fa-hand-pointer'; errTitle = 'Aksi Diperlukan'; }
        const li = document.createElement('li');
        li.className = 'error-item';
        li.innerHTML = '<i class="' + iconClass + '"></i><div><span class="err-title">' + errTitle + '</span><span class="err-desc">' + errDesc + '</span></div>';
        container.appendChild(li);
    });
    document.getElementById('warningModal').classList.add('show');
}

function closeWarningModal() { document.getElementById('warningModal').classList.remove('show'); }

document.getElementById('warningModal').addEventListener('click', function(e) { if (e.target === this) closeWarningModal(); });
document.getElementById('modalDetail').addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
document.getElementById('modalDefect').addEventListener('click', function(e) { if (e.target === this) closeDefectModal(); });

// INISIALISASI
renderKategoriList();
changeKat('overall');
loadJuriData();
</script>
</body>
</html>