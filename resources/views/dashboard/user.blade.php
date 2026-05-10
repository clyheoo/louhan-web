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

        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(59,130,246,0.08); border-radius: 24px; box-shadow: 0 20px 40px -8px rgba(59,130,246,0.08), 0 0 0 1px rgba(255,255,255,0.8) inset; position: relative; overflow: hidden; animation: cardEntry 0.8s 0.3s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes cardEntry { from { opacity:0; transform: translateY(30px) scale(0.97); } to { opacity:1; transform: translateY(0) scale(1); } }
        @keyframes textFadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
        
        .card-header { padding: 24px 32px 0; }
        .card-title { font-size: 18px; font-weight: 800; color: var(--gray-900); }
        .card-subtitle { font-size: 12px; color: var(--gray-400); margin-top: 4px; }
        .card-body { padding: 24px 32px 32px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-grid.full { grid-template-columns: 1fr; }
        .form-group { display: flex; flex-direction: column; animation: textFadeIn 0.6s 0.4s both; }
        .form-label { font-size: 11px; font-weight: 700; color: var(--gray-600); margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase; }
        .input-wrapper { position: relative; }
        .input-wrapper i.input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--gray-400); pointer-events: none; z-index: 1; transition: color 0.3s; }
        .form-input, .form-select { width: 100%; padding: 11px 14px 11px 38px; border: 1.5px solid var(--gray-200); border-radius: 12px; background: var(--white); font-family: inherit; font-size: 13px; color: var(--gray-800); outline: none; transition: all 0.3s ease; appearance: none; }
        .form-select { cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
        .form-input:focus, .form-select:focus { border-color: var(--blue-400); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .form-input:focus ~ i.input-icon, .form-select:focus ~ i.input-icon { color: var(--blue-500); }
        .form-input.input-error, .form-select.input-error { border-color: var(--red-500); }
        .input-error-msg { font-size: 11px; color: var(--red-500); margin-top: 4px; font-weight: 500; display: none; }
        
        .toggle-group { display: flex; background: var(--gray-100); border-radius: 12px; padding: 4px; border: 1px solid var(--gray-200); }
        .toggle-option { flex: 1; text-align: center; }
        .toggle-option input { display: none; }
        .toggle-option label { display: block; padding: 8px; border-radius: 10px; font-size: 12px; font-weight: 600; color: var(--gray-500); cursor: pointer; transition: all 0.3s ease; }
        .toggle-option input:checked + label { background: var(--white); color: var(--blue-700); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        .submit-btn { width: 100%; padding: 12px; border: none; border-radius: 14px; background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%); color: var(--white); font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); box-shadow: 0 4px 15px rgba(37,99,235,0.3); margin-top: 8px; animation: textFadeIn 0.6s 0.5s both; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37,99,235,0.4); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; }

        /* MESIN UNDIAN TANK */
        .machine-card { background: var(--dark-bg); border: 1px solid rgba(255,255,255,0.05); }
        .machine-card .card-header { border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
        .machine-card .card-title { color: var(--white); display: flex; align-items: center; gap: 8px; }
        .machine-card .card-title i { color: var(--blue-400); }
        
        .status-badge { font-size: 10px; font-weight: 700; background: rgba(148,163,184,0.2); color: var(--gray-400); padding: 4px 10px; border-radius: 20px; letter-spacing: 1px; }
        .status-badge.success { background: rgba(34,197,94,0.2); color: var(--green-500); }

        .machine-body { padding: 32px; display: flex; flex-direction: column; align-items: center; }
        .lcd-screen { width: 100%; background: var(--dark-surface); border-radius: 16px; padding: 30px; margin-bottom: 24px; border: 2px solid rgba(255,255,255,0.05); position: relative; overflow: hidden; text-align: center; }
        .lcd-screen::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, transparent 50%); pointer-events: none; }
        .lcd-label { font-size: 11px; color: var(--gray-500); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
        .number-display { font-size: 80px; font-weight: 900; color: var(--white); line-height: 1; text-shadow: 0 0 20px rgba(59,130,246,0.5); transition: all 0.1s; font-variant-numeric: tabular-nums; }
        .number-display.rolling { color: var(--blue-400); animation: glitch 0.1s infinite; }
        .number-display.final { color: var(--blue-400); text-shadow: 0 0 30px rgba(59,130,246,0.8); transform: scale(1.1); }
        @keyframes glitch { 0% { opacity: 0.8; } 50% { opacity: 1; } 100% { opacity: 0.8; } }

        .btn-acak { width: 100%; padding: 14px; border: none; border-radius: 14px; background: linear-gradient(135deg, var(--blue-600), var(--blue-800)); color: white; font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 20px rgba(37,99,235,0.4); letter-spacing: 0.5px; }
        .btn-acak:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(37,99,235,0.6); }
        .btn-acak:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }

        .machine-error { color: var(--red-500); font-size: 12px; margin-top: 12px; font-weight: 600; display: none; text-align: center; width: 100%; }
        .machine-success { color: var(--green-500); font-size: 12px; margin-top: 12px; font-weight: 600; display: none; text-align: center; width: 100%; }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; }
        .modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-card { background: var(--white); border-radius: 24px; padding: 48px 40px; text-align: center; max-width: 380px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.2); transform: scale(0.8); transition: transform 0.4s cubic-bezier(0.16,1,0.3,1); }
        .modal-overlay.show .modal-card { transform: scale(1); }
        .modal-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--green-500), var(--green-600)); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(34,197,94,0.3); }
        .modal-icon i { font-size: 36px; color: white; }
        .modal-title { font-size: 20px; font-weight: 800; color: var(--gray-900); margin-bottom: 8px; }
        .modal-desc { font-size: 13.5px; color: var(--gray-500); margin-bottom: 28px; line-height: 1.6; }
        .modal-close-btn { padding: 12px 28px; border: none; border-radius: 14px; background: var(--gray-100); color: var(--gray-700); font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .modal-close-btn:hover { background: var(--gray-200); }

        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
        @media (max-width: 640px) {
            .navbar { padding: 16px; }
            .main-content { padding: 16px; }
            .form-grid { grid-template-columns: 1fr; }
            .card-body, .card-header { padding-left: 20px; padding-right: 20px; }
            .number-display { font-size: 60px; }
        }
    </style>
</head>
<body>
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
                
                <!-- KOLOM KIRI: FORM REGISTRASI USER -->
                <div class="glass-card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><i class="fas fa-clipboard-list" style="color:var(--blue-500); margin-right:8px;"></i>Registrasi Saya</h2>
                            <p class="card-subtitle">Daftarkan diri Anda untuk mengikuti kontes.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="regForm">
                            @csrf
                            <div class="form-grid full" style="margin-top:16px;">
                                <div class="form-group">
                                    <label class="form-label">Nama Peserta</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="nama_peserta" id="namaPeserta" class="form-input" placeholder="Masukkan nama lengkap" value="{{ $user->name }}" required>
                                        <i class="fas fa-user input-icon"></i>
                                    </div>
                                    <div class="input-error-msg" id="errNama"></div>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Kategori</label>
                                    <div class="input-wrapper">
                                        <select name="kategori" class="form-select" required>
                                            <option value="" disabled selected>Pilih Kategori</option>
                                            <option value="Cencu">Cencu</option><option value="Chginwa">Chginwa</option><option value="Freemarking">Freemarking</option><option value="Goldenbase">Goldenbase</option><option value="Klasik">Klasik</option><option value="Bonsai">Bonsai</option><option value="Jumbo">Jumbo</option>
                                        </select>
                                        <i class="fas fa-fish input-icon"></i>
                                    </div>
                                    <div class="input-error-msg" id="errKategori"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kelas</label>
                                    <div class="input-wrapper">
                                        <select name="kelas" class="form-select" required>
                                            <option value="" disabled selected>Pilih Kelas</option>
                                            <option value="A">Kelas A</option><option value="B">Kelas B</option><option value="C">Kelas C</option><option value="D">Kelas D</option><option value="E">Kelas E</option>
                                        </select>
                                        <i class="fas fa-layer-group input-icon"></i>
                                    </div>
                                    <div class="input-error-msg" id="errKelas"></div>
                                </div>
                            </div>
                            <div class="form-grid full">
                                <div class="form-group">
                                    <label class="form-label">Jenis Keanggotaan</label>
                                    <div class="toggle-group">
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="perorangan" value="perorangan" checked>
                                            <label for="perorangan"><i class="fas fa-user" style="margin-right:4px"></i>Perorangan</label>
                                        </div>
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="team" value="team">
                                            <label for="team"><i class="fas fa-users" style="margin-right:4px"></i>Team / Club</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-grid full">
                                <div class="form-group">
                                    <label class="form-label" id="labelDetail">Kota Asal</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="detail_anggota" id="inputDetail" class="form-input" placeholder="Contoh: Jakarta" required>
                                        <i class="fas fa-city input-icon" id="iconDetail"></i>
                                    </div>
                                    <div class="input-error-msg" id="errDetail"></div>
                                </div>
                            </div>
                            <button type="submit" class="submit-btn" id="submitBtn"><i class="fas fa-paper-plane" style="margin-right:8px;"></i>DAFTARKAN DIRI SAYA</button>
                        </form>
                    </div>
                </div>

                <!-- KOLOM KANAN: MESIN UNDIAN USER -->
                <div class="glass-card machine-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-dice"></i>Undian Tank Saya</h2>
                        
                        {{-- LOGIKA: Badge berubah sesuai status --}}
                        @if($pesertaSaya)
                            <div class="status-badge success">SUDAH TERDAFTAR</div>
                        @else
                            <div class="status-badge" id="statusBadge">MENUNGGU UNDIAN</div>
                        @endif
                    </div>
                    
                    <div class="machine-body">
                        <div class="lcd-screen">
                            <div class="lcd-label">Nomor Aquarium Anda</div>
                            
                            {{-- LOGIKA: Jika sudah ada nomor, tampilkan langsung. Jika belum, tampilkan "--" --}}
                            @if($pesertaSaya)
                                <div class="number-display final">{{ $pesertaSaya->nomor_tank }}</div>
                            @else
                                <div class="number-display" id="numberDisplay">--</div>
                            @endif
                        </div>

                        {{-- LOGIKA: Jika sudah ada nomor, tampilkan pesan sukses. Jika belum, tampilkan error (untuk JS nanti) --}}
                        @if($pesertaSaya)
                            <div class="machine-success" style="display: block;">
                                <i class="fas fa-circle-check" style="margin-right:4px;"></i> 
                                Anda terdaftar pada Tank Nomor <b>{{ $pesertaSaya->nomor_tank }}</b> ({{ $pesertaSaya->kategori }} - Kelas {{ $pesertaSaya->kelas }})
                            </div>
                        @else
                            <div class="machine-error" id="machineError"></div>
                            <div class="machine-success" id="machineSuccess"></div>
                            
                            {{-- Tombol hanya muncul jika BELUM dapat nomor --}}
                            <button class="btn-acak" id="btnAcak">
                                <i class="fas fa-shuffle"></i> ACAK NOMOR SAYA
                            </button>
                        @endif
                    </div>
                </div>

    <!-- Modal Sukses Registrasi -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-card">
            <div class="modal-icon"><i class="fas fa-check"></i></div>
            <h2 class="modal-title">Berhasil Terdaftar!</h2>
            <p class="modal-desc">Data Anda sudah masuk. Sekarang silakan tekan tombol di sebelah kanan untuk mengacak nomor tank.</p>
            <button class="modal-close-btn" onclick="document.getElementById('successModal').classList.remove('show')">Mengerti</button>
        </div>
    </div>

    <script>
        // --- LOGIC REGISTRASI ---
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
            inputDetail.value = '';
        }
        radioPerorangan.addEventListener('change', updateToggleUI);
        radioTeam.addEventListener('change', updateToggleUI);

        function clearErrors() { document.querySelectorAll('.input-error-msg').forEach(el => el.style.display = 'none'); document.querySelectorAll('.form-input, .form-select').forEach(el => el.classList.remove('input-error')); }
        function showError(id, msg) { const el = document.getElementById(id); el.textContent = msg; el.style.display = 'block'; el.previousElementSibling.querySelector('input, select').classList.add('input-error'); }

        const regForm = document.getElementById('regForm');
        const submitBtn = document.getElementById('submitBtn');

        regForm.addEventListener('submit', function(e) {
            e.preventDefault(); clearErrors();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>MEMPROSES...';
            
            // CSRF TOKEN DITAMBAHKAN SECARA MANUAL
            const formData = new FormData(regForm);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch('{{ route("store.registrasi") }}', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: formData })
            .then(res => { if (!res.ok) return res.json().then(data => { throw data; }); return res.json(); })
            .then(data => { 
                if (data.success) { 
                    regForm.reset(); 
                    updateToggleUI(); 
                    document.getElementById('namaPeserta').value = "{{ $user->name }}"; // Isi kembali nama user
                    document.getElementById('successModal').classList.add('show'); 
                } 
            })
            .catch(err => { if (err.errors) { if (err.errors.nama_peserta) showError('errNama', err.errors.nama_peserta[0]); if (err.errors.kategori) showError('errKategori', err.errors.kategori[0]); if (err.errors.kelas) showError('errKelas', err.errors.kelas[0]); if (err.errors.detail_anggota) showError('errDetail', err.errors.detail_anggota[0]); } else { alert('Terjadi kesalahan pada server.'); } })
            .finally(() => { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:8px;"></i>DAFTARKAN DIRI SAYA'; });
        });

        // --- LOGIC MESIN UNDIAN USER ---
        const btnAcak = document.getElementById('btnAcak');
        const numberDisplay = document.getElementById('numberDisplay');
        const statusBadge = document.getElementById('statusBadge');
        const machineError = document.getElementById('machineError');
        const machineSuccess = document.getElementById('machineSuccess');

        btnAcak.addEventListener('click', function() {
            machineError.style.display = 'none';
            machineSuccess.style.display = 'none';
            btnAcak.disabled = true;
            statusBadge.textContent = 'ROLLING...';
            statusBadge.className = 'status-badge';
            numberDisplay.classList.add('rolling');
            numberDisplay.classList.remove('final');

            let rollCount = 0;
            const maxRolls = 15;
            const rollInterval = setInterval(() => {
                numberDisplay.textContent = Math.floor(Math.random() * 100) + 1;
                rollCount++;

                if (rollCount >= maxRolls) {
                    clearInterval(rollInterval);
                    
                    // CSRF TOKEN DITAMBAHKAN SECARA MANUAL
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    fetch('{{ route("api.acak.tank.user") }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            numberDisplay.textContent = data.nomor_tank;
                            numberDisplay.classList.remove('rolling');
                            numberDisplay.classList.add('final');
                            statusBadge.textContent = 'SUDAH TERDAFTAR';
                            statusBadge.classList.add('success');
                            machineSuccess.textContent = 'Selamat! Anda mendapat Tank Nomor ' + data.nomor_tank;
                            machineSuccess.style.display = 'block';
                            
                            // Disable tombol jika sudah mendapat nomor
                            btnAcak.disabled = true;
                            btnAcak.innerHTML = '<i class="fas fa-check-circle"></i> ANDA SUDAH MENDAPAT NOMOR';
                            btnAcak.style.background = 'var(--green-500)';
                            btnAcak.style.boxShadow = '0 4px 20px rgba(34,197,94,0.4)';
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(err => {
                        numberDisplay.textContent = '--';
                        numberDisplay.classList.remove('rolling');
                        statusBadge.textContent = 'GAGAL';
                        machineError.textContent = err.message;
                        machineError.style.display = 'block';
                        btnAcak.disabled = false;
                    });
                }
            }, 60);
        });
    </script>
</body>
</html>