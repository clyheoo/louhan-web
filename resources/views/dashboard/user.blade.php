<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Contest - LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --blue-50: #eff6ff; --blue-100: #dbeafe; --blue-200: #bfdbfe; --blue-300: #93c5fd;
            --blue-400: #60a5fa; --blue-500: #3b82f6; --blue-600: #2563eb; --blue-700: #1d4ed8; --blue-800: #1e40af;
            --white: #ffffff; --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
            --gray-300: #cbd5e1; --gray-400: #94a3b8; --gray-500: #64748b; --gray-600: #475569;
            --gray-700: #334155; --gray-800: #1e293b; --gray-900: #0f172a;
            --red-500: #ef4444; --green-500: #22c55e; --green-600: #16a34a;
            --dark-bg: #0f172a; --dark-surface: #1e293b;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--gray-50); min-height: 100vh; color: var(--gray-800); overflow-x: hidden; }
        
        .bg-layer { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
        .bg-gradient { position: absolute; inset: 0; background: radial-gradient(ellipse at 20% 0%, rgba(37,99,235,0.08) 0%, transparent 50%), radial-gradient(ellipse at 80% 100%, rgba(59,130,246,0.06) 0%, transparent 50%), linear-gradient(180deg, var(--blue-50) 0%, var(--white) 100%); }
        .orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0; animation: orbFloat 10s ease-in-out infinite; }
        .orb-1 { width: 400px; height: 400px; background: rgba(59,130,246,0.1); top: -10%; right: -5%; }
        .orb-2 { width: 300px; height: 300px; background: rgba(37,99,235,0.08); bottom: -10%; left: -5%; animation-delay: -4s; }
        @keyframes orbFloat { 0%, 100% { opacity: 0.4; transform: translateY(0); } 50% { opacity: 0.8; transform: translateY(-30px); } }

        .app-container { position: relative; z-index: 10; min-height: 100vh; display: flex; flex-direction: column; }
        
        .navbar { background: rgba(255,255,255,0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(59,130,246,0.08); padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; animation: slideDown 0.6s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes slideDown { from { opacity:0; transform: translateY(-20px); } to { opacity:1; transform: translateY(0); } }
        .nav-brand h1 { font-size: 20px; font-weight: 900; color: var(--blue-700); letter-spacing: -0.5px; line-height: 1; }
        .nav-brand p { font-size: 11px; color: var(--gray-500); font-weight: 500; margin-top: 2px; }
        .nav-user { display: flex; align-items: center; gap: 16px; }
        .user-info { text-align: right; }
        .user-info h4 { font-size: 13px; font-weight: 700; color: var(--gray-800); }
        .user-info span { font-size: 11px; color: var(--gray-400); }
        .btn-logout { background: var(--gray-100); border: 1px solid var(--gray-200); color: var(--gray-600); padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-logout:hover { background: var(--red-500); color: white; border-color: var(--red-500); }

        .main-content { flex: 1; padding: 32px; }
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; max-width: 1400px; margin: 0 auto; }
        .right-col { display: flex; flex-direction: column; gap: 24px; }

        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(59,130,246,0.08); border-radius: 24px; box-shadow: 0 20px 40px -8px rgba(59,130,246,0.08); position: relative; overflow: hidden; animation: cardEntry 0.8s 0.3s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes cardEntry { from { opacity:0; transform: translateY(30px) scale(0.97); } to { opacity:1; transform: translateY(0) scale(1); } }
        @keyframes textFadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
        
        .card-header { padding: 24px 32px 0; display: flex; justify-content: space-between; align-items: flex-start; }
        .card-title { font-size: 18px; font-weight: 800; color: var(--gray-900); }
        .card-subtitle { font-size: 12px; color: var(--gray-400); margin-top: 4px; }
        .card-body { padding: 24px 32px 32px; }

        .form-group { display: flex; flex-direction: column; margin-bottom: 16px; animation: textFadeIn 0.6s 0.4s both; }
        .form-label { font-size: 11px; font-weight: 700; color: var(--gray-600); margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase; }
        .input-wrapper { position: relative; }
        .input-wrapper i.input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--gray-400); pointer-events: none; z-index: 1; transition: color 0.3s; }
        .form-input, .form-select { width: 100%; padding: 11px 14px 11px 38px; border: 1.5px solid var(--gray-200); border-radius: 12px; background: var(--white); font-family: inherit; font-size: 13px; color: var(--gray-800); outline: none; transition: all 0.3s ease; appearance: none; }
        .form-select { cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
        .form-input:focus, .form-select:focus { border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .form-input:read-only { background: var(--gray-100); color: var(--gray-700); cursor: not-allowed; }
        
        .toggle-group { display: flex; background: var(--gray-100); border-radius: 12px; padding: 4px; border: 1px solid var(--gray-200); }
        .toggle-option { flex: 1; text-align: center; }
        .toggle-option input { display: none; }
        .toggle-option label { display: block; padding: 8px; border-radius: 10px; font-size: 12px; font-weight: 600; color: var(--gray-500); cursor: pointer; transition: all 0.3s ease; }
        .toggle-option input:checked + label { background: var(--white); color: var(--blue-700); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        .submit-btn { width: 100%; padding: 12px; border: none; border-radius: 14px; background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%); color: var(--white); font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); box-shadow: 0 4px 15px rgba(37,99,235,0.3); margin-top: 8px; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37,99,235,0.4); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; }
        .btn-green { background: linear-gradient(135deg, var(--green-500), var(--green-600)); box-shadow: 0 4px 15px rgba(34,197,94,0.3); }
        .btn-green:hover { box-shadow: 0 8px 25px rgba(34,197,94,0.4); }

        .input-error-msg { font-size: 11px; color: var(--red-500); margin-top: 4px; font-weight: 500; display: none; }

        /* MESIN UNDIAN & LIST IKAN */
        .machine-card { background: var(--dark-bg); border: 1px solid rgba(255,255,255,0.05); }
        .machine-card .card-header { border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 16px; }
        .machine-card .card-title { color: var(--white); display: flex; align-items: center; gap: 8px; }
        .machine-card .card-title i { color: var(--blue-400); }
        
        .status-badge { font-size: 10px; font-weight: 700; background: rgba(148,163,184,0.2); color: var(--gray-400); padding: 4px 10px; border-radius: 20px; letter-spacing: 1px; white-space: nowrap;}
        .status-badge.success { background: rgba(34,197,94,0.2); color: var(--green-500); }

        .machine-body { padding: 32px; display: flex; flex-direction: column; align-items: center; }
        .lcd-screen { width: 100%; background: var(--dark-surface); border-radius: 16px; padding: 24px; margin-bottom: 24px; border: 2px solid rgba(255,255,255,0.05); position: relative; overflow: hidden; text-align: center; }
        .lcd-screen::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, transparent 50%); pointer-events: none; }
        .lcd-label { font-size: 11px; color: var(--gray-500); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
        .number-display { font-size: 80px; font-weight: 900; color: var(--white); line-height: 1; text-shadow: 0 0 20px rgba(59,130,246,0.5); transition: all 0.1s; font-variant-numeric: tabular-nums; }
        .number-display.rolling { color: var(--blue-400); animation: glitch 0.1s infinite; }
        .number-display.final { color: var(--blue-400); text-shadow: 0 0 30px rgba(59,130,246,0.8); transform: scale(1.1); }
        @keyframes glitch { 0% { opacity: 0.8; } 50% { opacity: 1; } 100% { opacity: 0.8; } }

        .ikan-list-wrapper { width: 100%; max-height: 280px; overflow-y: auto; padding-right: 5px; }
        .ikan-list-wrapper::-webkit-scrollbar { width: 4px; }
        .ikan-list-wrapper::-webkit-scrollbar-thumb { background: var(--gray-600); border-radius: 10px; }
        
        .ikan-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; transition: all 0.2s; }
        .ikan-item:hover { background: rgba(255,255,255,0.06); }
        .ikan-item-info h4 { font-size: 12px; font-weight: 700; color: var(--white); }
        .ikan-item-info p { font-size: 10px; color: var(--gray-400); margin-top: 2px; }
        .ikan-item-right { display: flex; align-items: center; gap: 12px; }
        .tank-num { font-size: 16px; font-weight: 800; min-width: 30px; text-align: right; }
        .tank-num.empty { color: var(--gray-600); }
        .tank-num.filled { color: var(--blue-400); }
        
        .btn-acak-kecil { background: var(--blue-600); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 4px; }
        .btn-acak-kecil:hover { background: var(--blue-700); transform: scale(1.05); }
        .btn-acak-kecil:disabled { background: var(--gray-600); cursor: not-allowed; transform: none; }

        .ikan-empty-state { text-align: center; color: var(--gray-500); font-size: 13px; padding: 20px; width: 100%; }

        /* Modals */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; }
        .modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-card { background: var(--white); border-radius: 24px; padding: 40px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.2); transform: scale(0.8); transition: transform 0.4s cubic-bezier(0.16,1,0.3,1); }
        .modal-overlay.show .modal-card { transform: scale(1); }
        .modal-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .modal-icon i { font-size: 32px; color: white; }
        .modal-icon.blue { background: linear-gradient(135deg, var(--blue-500), var(--blue-700)); box-shadow: 0 8px 24px rgba(37,99,235,0.3); }
        .modal-icon.green { background: linear-gradient(135deg, var(--green-500), var(--green-600)); box-shadow: 0 8px 24px rgba(34,197,94,0.3); }
        .modal-title { font-size: 20px; font-weight: 800; color: var(--gray-900); margin-bottom: 8px; }
        .modal-desc { font-size: 13.5px; color: var(--gray-500); margin-bottom: 24px; line-height: 1.6; }
        .modal-form { text-align: left; margin-bottom: 20px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-close-btn { padding: 12px 24px; border: none; border-radius: 14px; background: var(--gray-100); color: var(--gray-700); font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .modal-close-btn:hover { background: var(--gray-200); }

        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } .right-col { flex-direction: column; } }
        @media (max-width: 640px) {
            .navbar { padding: 16px; }
            .main-content { padding: 16px; }
            .card-body, .card-header { padding-left: 20px; padding-right: 20px; }
            .number-display { font-size: 60px; }
        }
        .badge-admin { font-size: 9px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 2px 7px; border-radius: 4px; margin-left: 6px; vertical-align: middle; display: inline-flex; align-items: center; gap: 3px; border: 1px solid #fde68a; }
        
        /* MVP CARD */
        .mvp-card { border: 2px solid rgba(245,158,11,0.2); }
        .mvp-card .card-title { color: #b45309; }
        .mvp-card .card-title i { color: #f59e0b; }
        .btn-mvp-star { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--gray-500); width: 30px; height: 30px; border-radius: 8px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .btn-mvp-star:hover { border-color: #f59e0b; color: #f59e0b; }
        .btn-mvp-star.active { background: rgba(245,158,11,0.2); border-color: #f59e0b; color: #f59e0b; }
        .btn-mvp-star:disabled { opacity: 0.3; cursor: not-allowed; }
        .mvp-badge { font-size: 10px; font-weight: 700; background: rgba(245,158,11,0.2); color: #fbbf24; padding: 4px 10px; border-radius: 20px; letter-spacing: 1px; }
        .mvp-list-item { display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:rgba(245,158,11,0.05); border:1px solid rgba(245,158,11,0.15); border-radius:8px; margin-bottom:6px; font-size:12px; color:#92400e; }
        .mvp-list-item .mvp-remove { background:none; border:none; color:#ef4444; cursor:pointer; font-size:12px; padding:2px 5px; }
        .mvp-list-item.locked { opacity:0.7; }
        .btn-submit-mvp { width:100%; padding:12px; border:none; border-radius:12px; background:linear-gradient(135deg,#f59e0b,#d97706); color:white; font-family:inherit; font-size:13px; font-weight:800; cursor:pointer; margin-top:12px; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s; box-shadow:0 4px 12px rgba(245,158,11,.25); }
        .btn-submit-mvp:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(245,158,11,.35); }
        .btn-submit-mvp:disabled { background:#94a3b8; cursor:not-allowed; transform:none; box-shadow:none; }
    </style>
</head>
<body>
    @php 
        $profilLengkap = $pesertaSaya && !empty($pesertaSaya->detail_anggota); 
    @endphp
    <div class="bg-layer">
        <div class="bg-gradient"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="app-container">
        <nav class="navbar">
            <div class="nav-brand">
                <h1>LCI DASHBOARD</h1>
                <p>Sistem Kontes Louhan Club Indonesia</p>
            </div>
            <div class="nav-user">
                <div class="user-info">
                    <h4>{{ $user->name }}</h4>
                    <span>Peserta Kontes</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout"><i class="fas fa-right-from-bracket"></i> Keluar</button>
                </form>
            </div>
        </nav>

        <main class="main-content">
            <div class="dashboard-grid">
                
                <!-- ★ KOLOM KIRI: PROFIL & MVP -->
                <div style="display:flex;flex-direction:column;gap:24px;">
                    
                    <!-- CARD: PROFIL PESERTA -->
                    <div class="glass-card">
                        <div class="card-header">
                            <div>
                                <h2 class="card-title"><i class="fas fa-user-circle" style="color:var(--blue-500); margin-right:8px;"></i>Profil Saya</h2>
                                <p class="card-subtitle">Lengkapi data diri Anda terlebih dahulu.</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="regForm">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Nama Peserta</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="nama_peserta" id="namaPeserta" class="form-input" value="{{ $user->name }}" readonly>
                                        <i class="fas fa-lock input-icon" style="font-size:12px;"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Jenis Keanggotaan</label>
                                    <div class="toggle-group" @if($profilLengkap) style="opacity:0.5;pointer-events:none;" @endif>
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="perorangan" value="perorangan" {{ !$pesertaSaya || $pesertaSaya->jenis_keanggotaan == 'perorangan' ? 'checked' : '' }}>
                                            <label for="perorangan"><i class="fas fa-user" style="margin-right:4px"></i>Perorangan</label>
                                        </div>
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="team" value="team" {{ $pesertaSaya && $pesertaSaya->jenis_keanggotaan == 'team' ? 'checked' : '' }}>
                                            <label for="team"><i class="fas fa-users" style="margin-right:4px"></i>Team / Club</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" id="labelDetail">{{ $pesertaSaya && $pesertaSaya->jenis_keanggotaan == 'team' ? 'Nama Team / Club' : 'Kota Asal' }}</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="detail_anggota" id="inputDetail" class="form-input" placeholder="Contoh: Jakarta" value="{{ $pesertaSaya->detail_anggota ?? '' }}" {{ $profilLengkap ? 'readonly style="background:var(--gray-100);cursor:not-allowed;"' : '' }} required>
                                        <i class="fas {{ $pesertaSaya && $pesertaSaya->jenis_keanggotaan == 'team' ? 'fa-shield-halved' : 'fa-city' }} input-icon" id="iconDetail"></i>
                                    </div>
                                    <div class="input-error-msg" id="errDetail"></div>
                                </div>
                                
                                @if(!$profilLengkap)
                                <button type="submit" class="submit-btn" id="submitBtn">
                                    <i class="fas fa-save" style="margin-right:8px;"></i>SIMPAN PROFIL
                                </button>
                                @endif
                            </form>
                            
                            <button class="submit-btn btn-green" style="margin-top: 12px;" onclick="document.getElementById('modalIkan').classList.add('show')">
                                <i class="fas fa-plus" style="margin-right:8px;"></i>MASUKKAN DATA IKAN
                            </button>
                        </div>
                    </div>

                    <!-- CARD: PENDAFTARAN MVP -->
                    <div class="glass-card mvp-card" id="mvpCard">
                        <div class="card-header">
                            <div>
                                <h2 class="card-title"><i class="fas fa-star"></i> Pendaftaran MVP</h2>
                                <p class="card-subtitle">Pilih maksimal 30 ikan terbaik Anda.</p>
                            </div>
                            <div class="mvp-badge" id="mvpCountBadge">0/30 MVP</div>
                        </div>
                        <div class="card-body" id="mvpCardBody" style="padding-top:10px;">
                            <div id="mvpLockedState" style="text-align:center; color:var(--gray-500); padding: 20px 0; font-size:13px;">
                                <i class="fas fa-lock" style="font-size:24px; margin-bottom:8px; display:block; opacity:0.3;"></i>
                                Pendaftaran MVP belum dibuka oleh panitia.
                            </div>
                            <div id="mvpUnlockedState" style="display:none;">
                                <div id="mvpListContainer" style="max-height:220px; overflow-y:auto; margin-bottom:8px;"></div>
                                <div id="mvpEmptyList" style="text-align:center; color:#b45309; padding: 20px 0; font-size:13px;">
                                    <i class="fas fa-lock-open" style="font-size:24px; margin-bottom:8px; display:block; color:#f59e0b;"></i>
                                    Pendaftaran MVP DIBUKA! Klik <i class="fas fa-star" style="color:#f59e0b;"></i> pada ikan Anda di daftar sebelah.
                                </div>
                                <button class="btn-submit-mvp" id="btnSubmitMvp" onclick="confirmSubmitMvp()" style="display:none;">
                                    <i class="fas fa-paper-plane"></i> KIRIM IKAN MVP
                                </button>
                                <div id="mvpSubmittedBadge" style="display:none; text-align:center; margin-top:12px; background:#dcfce7; border:1px solid #86efac; border-radius:10px; padding:10px; color:#166534; font-size:12px; font-weight:700;">
                                    <i class="fas fa-circle-check"></i> Data MVP sudah dikirim dan terkunci.
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- ★ PENUTUP KOLOM KIRI (INI YANG HILANG SEBELUMNYA) -->

                <!-- ★ KOLOM KANAN: UNDIAN & IKAN -->
                <div class="right-col">

                    <!-- CARD: MESIN UNDIAN -->
                    <div class="glass-card machine-card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="fas fa-dice"></i>Undian Tank</h2>
                        </div>
                        <div class="machine-body">
                            <div class="lcd-screen">
                                <div class="lcd-label">Nomor Aquarium</div>
                                <div class="number-display" id="numberDisplay">--</div>
                                <div style="font-size:11px; color:var(--gray-500); margin-top:8px;" id="lcdInfo">Klik ACAK pada daftar ikan</div>
                            </div>
                            <div id="resetBanner" style="display:none; width:100%; background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.25); border-radius:12px; padding:12px 16px; margin-bottom:16px; align-items:center; gap:10px;">
                                <i class="fas fa-triangle-exclamation" style="color:#f59e0b; font-size:18px; flex-shrink:0;"></i>
                                <span id="resetBannerText" style="font-size:12px; color:#fbbf24; line-height:1.5; flex:1;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- CARD: DAFTAR IKAN -->
                    <div class="glass-card machine-card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="fas fa-list-fish"></i>Daftar Ikan Saya</h2>
                            <div class="status-badge {{ $ikansSaya->count() > 0 ? 'success' : '' }}">
                                {{ $ikansSaya->count() > 0 ? $ikansSaya->whereNotNull('nomor_tank')->count() . '/' . $ikansSaya->count() . ' DIUNDI' : 'MENUNGGU IKAN' }}
                            </div>
                        </div>
                        <div class="machine-body" style="padding:20px 24px;">
                            <div class="ikan-list-wrapper" id="ikanListWrapper" style="max-height:340px;">
                                @if($ikansSaya->count() > 0)
                                    <div class="ikan-list" id="ikanListContainer">
                                        @foreach($ikansSaya as $index => $ikan)
                                            <div class="ikan-item" id="ikan-item-{{ $ikan->id }}">
                                                <div class="ikan-item-info">
                                                    <h4><i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>Ikan #{{ $loop->iteration }}@if($ikan->dibuat_oleh === 'admin')<span class="badge-admin"><i class="fas fa-shield-halved"></i> Ditambah Admin</span>@endif</h4>
                                                    <p>{{ $ikan->kategori }} - Kelas {{ $ikan->kelas }}</p>
                                                </div>
                                                <div class="ikan-item-right">
                                                    <div class="tank-num {{ $ikan->nomor_tank ? 'filled' : 'empty' }}" id="tank-num-{{ $ikan->id }}">
                                                        {{ $ikan->nomor_tank ?? '--' }}
                                                    </div>
                                                    @if(!$ikan->nomor_tank)
                                                        <button class="btn-acak-kecil" onclick="mulaiAcak({{ $ikan->id }}, this)">
                                                            <i class="fas fa-shuffle"></i> ACAK
                                                        </button>
                                                    @else
                                                        <span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="ikan-empty-state">
                                        <i class="fas fa-fish" style="font-size:24px; margin-bottom:8px; display:block; opacity:0.5;"></i>
                                        Belum ada ikan yang didaftarkan.<br>Silakan klik tombol "Masukkan Data Ikan".
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> <!-- Penutup right-col -->
            </div> <!-- Penutup dashboard-grid -->
        </main>
    </div> <!-- Penutup app-container -->

    @if($profilLengkap)
    <script>document.addEventListener('DOMContentLoaded', function(){ lockProfilForm(); });</script>
    @endif

    <!-- MODAL TAMBAH IKAN -->
    <div class="modal-overlay" id="modalIkan">
        <div class="modal-card">
            <div class="modal-icon blue"><i class="fas fa-fish"></i></div>
            <h2 class="modal-title">Masukkan Data Ikan</h2>
            <p class="modal-desc">Isi kategori dan kelas untuk ikan yang akan dilombakan.</p>
            <form id="formIkan">
                @csrf
                <div class="modal-form">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label">Kategori</label>
                        <div class="input-wrapper">
                            <select name="kategori" class="form-select" required style="padding-left:14px;">
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="Cencu">Cencu</option><option value="Chginwa">Chginwa</option><option value="Freemarking">Freemarking</option><option value="Goldenbase">Goldenbase</option><option value="Klasik">Klasik</option><option value="Bonsai">Bonsai</option><option value="Jumbo">Jumbo</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Kelas</label>
                        <div class="input-wrapper">
                            <select name="kelas" class="form-select" required style="padding-left:14px;">
                                <option value="" disabled selected>Pilih Kelas</option>
                                <option value="A">Kelas A</option><option value="B">Kelas B</option><option value="C">Kelas C</option><option value="D">Kelas D</option><option value="E">Kelas E</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-close-btn" onclick="document.getElementById('modalIkan').classList.remove('show')">Batal</button>
                    <button type="submit" class="submit-btn" style="width:auto; padding: 12px 28px; margin-top:0; font-size:13px;">Simpan Ikan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SUKSES PROFIL -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-card">
            <div class="modal-icon green"><i class="fas fa-check"></i></div>
            <h2 class="modal-title" id="successModalTitle">Berhasil Disimpan!</h2>
            <p class="modal-desc" id="successModalDesc">Profil Anda sudah diperbarui. Sekarang silakan masukkan data ikan yang akan dilombakan.</p>
            <button class="modal-close-btn" onclick="document.getElementById('successModal').classList.remove('show')">Mengerti</button>
        </div>
    </div>

    <!-- MODAL KONFIRMASI MVP -->
    <div class="modal-overlay" id="modalConfirmMvp">
        <div class="modal-card">
            <div class="modal-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706); box-shadow:0 8px 24px rgba(245,158,11,.3);"><i class="fas fa-paper-plane"></i></div>
            <h2 class="modal-title">Kirim Data MVP?</h2>
            <p class="modal-desc">Pastikan pilihan Anda sudah benar. Ikan yang terdaftar sebagai MVP <b style="color:#b45309;">TIDAK DAPAT DIUBAH ATAU DIHAPUS</b> setelah dikirim.</p>
            <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:10px; padding:12px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="mvpAgree" style="width:18px; height:18px; cursor:pointer; accent-color:#d97706;">
                <label for="mvpAgree" style="font-size:12px; font-weight:700; color:#92400e; cursor:pointer;">Saya mengerti dan menyetujui bahwa data tidak dapat diubah.</label>
            </div>
            <div class="modal-actions">
                <button class="modal-close-btn" onclick="document.getElementById('modalConfirmMvp').classList.remove('show')">Batal</button>
                <button class="btn-submit-mvp" id="btnConfirmSubmitMvp" onclick="submitMvpIkan()" disabled style="width:auto; margin-top:0;"><i class="fas fa-paper-plane"></i> Ya, Kirim MVP</button>
            </div>
        </div>
    </div>
    
    <script>
        // --- LOGIC PROFIL ---
        const radioPerorangan = document.getElementById('perorangan');
        const radioTeam = document.getElementById('team');
        const labelDetail = document.getElementById('labelDetail');
        const inputDetail = document.getElementById('inputDetail');
        const iconDetail = document.getElementById('iconDetail');

        function updateToggleUI() {
            if (radioTeam.checked) {
                labelDetail.textContent = 'Nama Team / Club';
                inputDetail.placeholder = 'Contoh: Louhan Fanatic Jakarta';
                iconDetail.classList.replace('fa-city', 'fa-shield-halved');
            } else {
                labelDetail.textContent = 'Kota Asal';
                inputDetail.placeholder = 'Contoh: Jakarta';
                iconDetail.classList.replace('fa-shield-halved', 'fa-city');
            }
        }
        radioPerorangan.addEventListener('change', updateToggleUI);
        radioTeam.addEventListener('change', updateToggleUI);

        function lockProfilForm() {
            document.querySelectorAll('input[name="jenis_keanggotaan"]').forEach(function(r) { r.disabled = true; });
            document.querySelector('.toggle-group').style.opacity = '0.5';
            document.querySelector('.toggle-group').style.pointerEvents = 'none';
            var inp = document.getElementById('inputDetail');
            inp.readOnly = true; inp.style.background = 'var(--gray-100)'; inp.style.cursor = 'not-allowed';
            var btn = document.getElementById('submitBtn');
            if (btn) btn.style.display = 'none';
        }

        @if($profilLengkap) lockProfilForm(); @endif

        const regForm = document.getElementById('regForm');
        const submitBtn = document.getElementById('submitBtn');

        regForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>MEMPROSES...';
            const formData = new FormData(regForm);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            apiFetch('{{ route("store.registrasi") }}', { method: 'POST', body: formData })
            .then(res => { if (!res.ok) return res.json().then(data => { throw data; }); return res.json(); })
            .then(data => { if (data.success) { document.getElementById('successModal').classList.add('show'); lockProfilForm(); } })
            .catch(err => { 
                const errEl = document.getElementById('errDetail');
                if (err.errors && err.errors.detail_anggota) { errEl.textContent = err.errors.detail_anggota[0]; errEl.style.display = 'block'; } else { alert('Terjadi kesalahan pada server.'); }
            })
            .finally(() => { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-save" style="margin-right:8px;"></i>SIMPAN PROFIL'; });
        });

        // --- LOGIC TAMBAH IKAN ---
        const formIkan = document.getElementById('formIkan');
        formIkan.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = formIkan.querySelector('.submit-btn');
            btnSubmit.disabled = true; btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            const formData = new FormData(formIkan);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            apiFetch('{{ route("store.ikan") }}', { method: 'POST', body: formData })
            .then(res => { if (!res.ok) return res.json().then(data => { throw data; }); return res.json(); })
            .then(data => {
                if (data.success) {
                    document.getElementById('modalIkan').classList.remove('show');
                    formIkan.reset();
                    let listContainer = document.getElementById('ikanListContainer');
                    let emptyState = document.querySelector('.ikan-empty-state');
                    if (emptyState) emptyState.remove();
                    if (!listContainer) { listContainer = document.createElement('div'); listContainer.className = 'ikan-list'; listContainer.id = 'ikanListContainer'; document.getElementById('ikanListWrapper').appendChild(listContainer); }
                    const currentCount = listContainer.children.length;
                    const newEl = document.createElement('div');
                    newEl.className = 'ikan-item'; newEl.id = `ikan-item-${data.ikan.id}`;
                    newEl.innerHTML = `<div class="ikan-item-info"><h4><i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>Ikan #${currentCount + 1}</h4><p>${data.ikan.kategori} - Kelas ${data.ikan.kelas}</p></div><div class="ikan-item-right"><div class="tank-num empty" id="tank-num-${data.ikan.id}">--</div><button class="btn-acak-kecil" onclick="mulaiAcak(${data.ikan.id}, this)"><i class="fas fa-shuffle"></i> ACAK</button></div>`;
                    listContainer.prepend(newEl);
                    currentIkans[data.ikan.id] = { kategori: `${data.ikan.kategori} - Kelas ${data.ikan.kelas}`, nomor_tank: '--', is_mvp: false };
                }
            })
            .catch(err => { if (err.errors) { let msg = ''; Object.values(err.errors).forEach(function(e) { msg += e[0] + '\n'; }); alert(msg); } else { alert(err.message || 'Gagal menambahkan ikan.'); } })
            .finally(() => { btnSubmit.disabled = false; btnSubmit.innerHTML = 'Simpan Ikan'; });
        });

        // --- HELPER: API FETCH MENGGUNAKAN XMLHttpRequest ---
        function apiFetch(url, options = {}) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open(options.method || 'GET', url, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (options.method && options.method.toUpperCase() !== 'GET') {
                    var csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
                }
                xhr.withCredentials = true;
                xhr.onload = function() {
                    resolve({
                        ok: xhr.status >= 200 && xhr.status < 300,
                        status: xhr.status,
                        statusText: xhr.statusText,
                        json: function() { return JSON.parse(xhr.responseText); }
                    });
                };
                xhr.onerror = function() { reject(new Error('Network error')); };
                xhr.send(options.body || null);
            });
        }

        // --- REAL-TIME POLLING ---
        let currentIkans = {};
        let isMvpOpen = false;
        let currentMvpSubmitted = false;
        let pollingInterval = null;
        let auth401Count = 0;
        const MAX_401_RETRY = 5;

        document.querySelectorAll('.ikan-item').forEach(el => {
            const id = el.id.replace('ikan-item-', '');
            const pEl = el.querySelector('.ikan-item-info p');
            const tankEl = el.querySelector('.tank-num');
            if (id && pEl && tankEl) { currentIkans[id] = { kategori: pEl.textContent.trim(), nomor_tank: tankEl.textContent.trim(), is_mvp: false }; }
        });

        function pollIkans() {
            if (auth401Count >= MAX_401_RETRY) return;

            apiFetch('/api/user/my-ikans')
            .then(function(r) {
                if (r.status === 401) {
                    auth401Count++;
                    console.warn('Auth 401 count:', auth401Count, '- Session mungkin tidak valid');
                    if (auth401Count >= MAX_401_RETRY) {
                        console.error('Session expired confirmed - redirecting to login');
                        window.location.href = '/login';
                    }
                    return null;
                }
                
                // Reset counter on success
                auth401Count = 0;
                
                if (r.status === 419) {
                    console.warn('CSRF token mismatch - reloading page');
                    window.location.reload();
                    return null;
                }
                
                if (!r.ok) {
                    throw new Error('Server error: ' + r.status);
                }
                return r.json();
            })
            .then(function(response) {
                if (!response) return;
                
                const data = response.ikans || [];
                const resetInfo = response.reset_info;
                const mvpOpen = response.mvp_open || false;
                const mvpSubmitted = response.mvp_submitted || false;
                
                if (response.tank_range_max) tankDrawMax = response.tank_range_max;

                if (mvpOpen !== isMvpOpen || mvpSubmitted !== currentMvpSubmitted) {
                    isMvpOpen = mvpOpen;
                    currentMvpSubmitted = mvpSubmitted;
                    
                    if(isMvpOpen) {
                        document.getElementById('mvpLockedState').style.display = 'none';
                        document.getElementById('mvpUnlockedState').style.display = 'block';
                    } else {
                        document.getElementById('mvpLockedState').style.display = 'block';
                        document.getElementById('mvpUnlockedState').style.display = 'none';
                    }
                    
                    document.querySelectorAll('.ikan-item').forEach(el => {
                        const rightDiv = el.querySelector('.ikan-item-right');
                        if(!rightDiv) return;
                        let mvpBtn = rightDiv.querySelector('.btn-mvp-star');
                        
                        if(currentMvpSubmitted) {
                            if(mvpBtn) { mvpBtn.disabled = true; mvpBtn.style.opacity = '0.5'; mvpBtn.style.cursor = 'not-allowed'; }
                        } else {
                            if(isMvpOpen && !mvpBtn) {
                                const id = el.id.replace('ikan-item-', '');
                                const isMvp = currentIkans[id] ? currentIkans[id].is_mvp : false;
                                const btn = document.createElement('button');
                                btn.className = 'btn-mvp-star' + (isMvp ? ' active' : '');
                                btn.setAttribute('onclick', 'toggleMvp('+id+', this)');
                                btn.setAttribute('title', 'Daftarkan MVP');
                                btn.innerHTML = '<i class="fas fa-star"></i>';
                                rightDiv.insertBefore(btn, rightDiv.firstChild);
                            } else if(!isMvpOpen && mvpBtn) {
                                mvpBtn.remove();
                            }
                        }
                    });
                }

                let mvpCount = 0;
                let mvpListHtml = '';
                let hasResetIkan = false;
                let listContainer = document.getElementById('ikanListContainer');
                let emptyState = document.querySelector('.ikan-empty-state');

                if (!listContainer && data.length > 0) {
                    if (emptyState) emptyState.remove();
                    listContainer = document.createElement('div'); listContainer.className = 'ikan-list'; listContainer.id = 'ikanListContainer'; document.getElementById('ikanListWrapper').appendChild(listContainer);
                }

                data.forEach(ikan => {
                    if(ikan.is_mvp) {
                        mvpCount++;
                        const removeBtn = currentMvpSubmitted ? '' : `<button class="mvp-remove" onclick="toggleMvp(${ikan.id}, document.querySelector('#ikan-item-${ikan.id} .btn-mvp-star'))"><i class="fas fa-xmark"></i></button>`;
                        mvpListHtml += `<div class="mvp-list-item ${currentMvpSubmitted ? 'locked' : ''}">
                            <span><i class="fas fa-star" style="color:#f59e0b; margin-right:6px;"></i>${ikan.kategori} - Kelas ${ikan.kelas} (Tank ${ikan.nomor_tank ?? '--'})</span>
                            ${removeBtn}
                        </div>`;
                    }
                    
                    let existingEl = document.getElementById(`ikan-item-${ikan.id}`);
                    if (!existingEl) {
                        if (!listContainer) return;
                        const count = listContainer.children.length + 1;
                        const badge = ikan.dibuat_oleh === 'admin' ? '<span class="badge-admin"><i class="fas fa-shield-halved"></i> Ditambah Admin</span>' : '';
                        const mvpBtnHtml = (isMvpOpen && !currentMvpSubmitted) ? `<button class="btn-mvp-star ${ikan.is_mvp ? 'active' : ''}" onclick="toggleMvp(${ikan.id}, this)" title="Daftarkan MVP"><i class="fas fa-star"></i></button>` : (currentMvpSubmitted && ikan.is_mvp ? `<button class="btn-mvp-star active" disabled style="opacity:0.5; cursor:not-allowed;"><i class="fas fa-star"></i></button>` : '');
                        const newEl = document.createElement('div'); newEl.className = 'ikan-item'; newEl.id = `ikan-item-${ikan.id}`; newEl.style.animation = 'cardEntry 0.5s ease both';
                        newEl.innerHTML = `<div class="ikan-item-info"><h4><i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>Ikan #${count} ${badge}</h4><p>${ikan.kategori} - Kelas ${ikan.kelas}</p></div><div class="ikan-item-right">${mvpBtnHtml}<div class="tank-num ${ikan.nomor_tank ? 'filled' : 'empty'}" id="tank-num-${ikan.id}">${ikan.nomor_tank ?? '--'}</div>${!ikan.nomor_tank ? `<button class="btn-acak-kecil" onclick="mulaiAcak(${ikan.id}, this)"><i class="fas fa-shuffle"></i> ACAK</button>` : `<span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>`}</div>`;
                        listContainer.prepend(newEl);
                        currentIkans[ikan.id] = { kategori: `${ikan.kategori} - Kelas ${ikan.kelas}`, nomor_tank: ikan.nomor_tank ?? '--', is_mvp: ikan.is_mvp };
                    } else {
                        if (!currentIkans[ikan.id]) currentIkans[ikan.id] = { kategori: '', nomor_tank: '--', is_mvp: false };
                        const currentKat = `${ikan.kategori} - Kelas ${ikan.kelas}`;
                        const currentTank = ikan.nomor_tank ?? '--';
                        
                        if (currentIkans[ikan.id].kategori !== currentKat) { existingEl.querySelector('.ikan-item-info p').textContent = currentKat; currentIkans[ikan.id].kategori = currentKat; }

                        if (currentIkans[ikan.id].is_mvp !== ikan.is_mvp && isMvpOpen) {
                            let mvpBtn = existingEl.querySelector('.btn-mvp-star');
                            if(mvpBtn) {
                                if(ikan.is_mvp) mvpBtn.classList.add('active'); else mvpBtn.classList.remove('active');
                            }
                            currentIkans[ikan.id].is_mvp = ikan.is_mvp;
                        }

                        if (currentIkans[ikan.id].nomor_tank !== '--' && currentTank === '--') {
                            hasResetIkan = true;
                            const tankEl = document.getElementById(`tank-num-${ikan.id}`);
                            if (tankEl) { tankEl.textContent = '--'; tankEl.classList.remove('filled'); tankEl.classList.add('empty'); let checkmark = existingEl.querySelector('.fa-circle-check'); if (checkmark) { const parent = checkmark.closest('span') || checkmark.parentElement; if (parent) parent.outerHTML = `<button class="btn-acak-kecil" onclick="mulaiAcak(${ikan.id}, this)"><i class="fas fa-shuffle"></i> ACAK</button>`; } }
                            currentIkans[ikan.id].nomor_tank = '--';
                        } else if (currentIkans[ikan.id].nomor_tank !== currentTank && currentTank !== '--') {
                            const tankEl = document.getElementById(`tank-num-${ikan.id}`);
                            if (tankEl) { tankEl.textContent = currentTank; tankEl.classList.remove('empty'); tankEl.classList.add('filled'); let btn = existingEl.querySelector('.btn-acak-kecil'); if (btn) btn.outerHTML = '<span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>'; }
                            currentIkans[ikan.id].nomor_tank = currentTank;
                        }
                    }
                });

                document.getElementById('mvpListContainer').innerHTML = mvpListHtml;
                document.getElementById('mvpCountBadge').textContent = `${mvpCount}/30 MVP`;

                if(isMvpOpen) {
                    document.getElementById('mvpEmptyList').style.display = mvpCount > 0 ? 'none' : 'block';
                    document.getElementById('btnSubmitMvp').style.display = mvpCount > 0 && !currentMvpSubmitted ? 'flex' : 'none';
                    document.getElementById('mvpSubmittedBadge').style.display = currentMvpSubmitted ? 'block' : 'none';
                }

                const banner = document.getElementById('resetBanner');
                if (hasResetIkan && resetInfo && resetInfo.reason) { banner.style.display = 'flex'; document.getElementById('resetBannerText').innerHTML = `Nomor tank Anda telah direset oleh panitia. Alasan: <strong style="color:#fff;">${resetInfo.reason}</strong>`; }

                const total = data.length; const undi = data.filter(i => i.nomor_tank).length;
                const statusBadge = document.querySelector('.status-badge');
                if (statusBadge) { statusBadge.textContent = total > 0 ? `${undi}/${total} DIUNDI` : 'MENUNGGU IKAN'; statusBadge.className = 'status-badge ' + (total > 0 ? 'success' : ''); }
            })
            .catch(function(err) {
                console.error('Polling error:', err);
            });
        }

        // ★ TUNGGU 3 DETIK SEBELUM MULAI POLLING
        setTimeout(function() {
            console.log('Starting polling...');
            pollIkans();
            pollingInterval = setInterval(pollIkans, 5000);
        }, 3000);

        // ★ STOP SAAT TAB TIDAK AKTIF
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (pollingInterval) { clearInterval(pollingInterval); pollingInterval = null; }
            } else {
                if (auth401Count < MAX_401_RETRY && !pollingInterval) {
                    pollIkans();
                    pollingInterval = setInterval(pollIkans, 5000);
                }
            }
        });

        // --- LOGIC TOGGLE MVP ---
        function toggleMvp(ikanId, btnElement) {
            if (btnElement.disabled) return;
            btnElement.disabled = true;
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('ikan_id', ikanId);

            apiFetch('/api/toggle-mvp-ikan', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if(data.is_mvp) { btnElement.classList.add('active'); } else { btnElement.classList.remove('active'); }
                    currentIkans[ikanId].is_mvp = data.is_mvp;
                    pollIkans(); 
                } else {
                    alert(data.message || 'Gagal mengubah status MVP.');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'))
            .finally(() => { btnElement.disabled = false; });
        }

        // --- RANGE UNDIAN (DEFAULT) ---
        let tankDrawMax = 1000;

        // --- LOGIC MESIN UNDIAN ---
        const numberDisplay = document.getElementById('numberDisplay');
        const lcdInfo = document.getElementById('lcdInfo');

        function mulaiAcak(ikanId, btnElement) {
            if (btnElement.disabled) return;
            btnElement.disabled = true; btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            lcdInfo.textContent = 'Sedang mengundi...'; numberDisplay.classList.add('rolling'); numberDisplay.classList.remove('final');
            var maxForAnim = tankDrawMax || 1000; var rolling = true;
            var rollTimer = setInterval(function() { numberDisplay.textContent = Math.floor(Math.random() * maxForAnim) + 1; }, 40);
            var formData = new FormData(); formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content')); formData.append('ikan_id', ikanId);
            apiFetch('{{ route("api.acak.tank.user") }}', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) throw new Error(data.message || 'Gagal mengacak nomor.');
                var finalNumber = data.nomor_tank; rolling = false; clearInterval(rollTimer); var slowSteps = 8; var slowIndex = 0;
                function slowRoll() {
                    slowIndex++; var progress = slowIndex / slowSteps; var spread = Math.max(0, Math.round(50 * (1 - progress)));
                    var minN = Math.max(1, finalNumber - spread); var maxN = finalNumber + spread;
                    var shown = Math.floor(Math.random() * (maxN - minN + 1)) + minN;
                    if (slowIndex >= slowSteps) shown = finalNumber;
                    numberDisplay.textContent = shown;
                    if (slowIndex >= slowSteps) {
                        numberDisplay.classList.remove('rolling'); numberDisplay.classList.add('final'); lcdInfo.textContent = 'Berhasil!';
                        var tankNumEl = document.getElementById('tank-num-' + ikanId);
                        if (tankNumEl) { tankNumEl.textContent = finalNumber; tankNumEl.classList.remove('empty'); tankNumEl.classList.add('filled'); }
                        btnElement.outerHTML = '<span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>';
                        setTimeout(function() { numberDisplay.textContent = '--'; numberDisplay.classList.remove('final'); lcdInfo.textContent = 'Klik ACAK pada daftar ikan'; }, 2500);
                    } else { var delay = 100 + (progress * progress * 250); setTimeout(slowRoll, delay); }
                }
                slowRoll();
            })
            .catch(function(err) {
                rolling = false; clearInterval(rollTimer); numberDisplay.textContent = '--'; numberDisplay.classList.remove('rolling');
                var errorMsg = err.message || 'Terjadi kesalahan';
                if (errorMsg.indexOf('NOMOR TANK PENUH') !== -1) { lcdInfo.textContent = 'Nomor tank penuh'; alert('⚠️ ' + errorMsg); } else { lcdInfo.textContent = 'Gagal'; alert('Gagal mengacak: ' + errorMsg); }
                pollIkans();
                setTimeout(function() { var checkBtn = document.querySelector('#ikan-item-' + ikanId + ' .btn-acak-kecil'); if (checkBtn) { checkBtn.disabled = false; checkBtn.innerHTML = '<i class="fas fa-shuffle"></i> ACAK'; } }, 600);
            });
        }

        document.getElementById('mvpAgree').addEventListener('change', function() {
            document.getElementById('btnConfirmSubmitMvp').disabled = !this.checked;
        });

        function confirmSubmitMvp() {
            document.getElementById('mvpAgree').checked = false;
            document.getElementById('btnConfirmSubmitMvp').disabled = true;
            document.getElementById('modalConfirmMvp').classList.add('show');
        }

        function submitMvpIkan() {
            var btn = document.getElementById('btnConfirmSubmitMvp');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            apiFetch('/api/submit-mvp-ikan', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('modalConfirmMvp').classList.remove('show');
                    var modalTitle = document.getElementById('successModalTitle');
                    var modalDesc = document.getElementById('successModalDesc');
                    if(modalTitle) modalTitle.textContent = 'Data MVP Terkirim!';
                    if(modalDesc) modalDesc.innerHTML = 'Pilihan ikan MVP Anda berhasil dikirim dan sudah <b style="color:#b45309;">TERKUNCI</b>. Data tidak dapat diubah lagi.';
                    document.getElementById('successModal').classList.add('show');
                    pollIkans(); 
                } else {
                    alert(data.message || 'Gagal mengirim data MVP.');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'))
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Ya, Kirim MVP'; });
        }
    </script>
</body>
</html>