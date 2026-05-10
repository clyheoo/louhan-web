<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --bg:#f1f5f9;--card:#fff;--primary:#2563eb;--primary-dk:#1d4ed8;--primary-lt:#eff6ff;
            --text:#1e293b;--muted:#64748b;--light:#94a3b8;--border:#e2e8f0;
            --success:#10b981;--success-lt:#dcfce7;--danger:#ef4444;--danger-lt:#fee2e2;
            --warning:#f59e0b;--warning-lt:#fef3c7;
            --purple:#7c3aed;--purple-lt:#f5f3ff;--purple-dk:#6d28d9;
            --shadow-sm:0 1px 2px rgba(0,0,0,.05);--shadow:0 4px 6px -1px rgba(0,0,0,.07);--shadow-lg:0 10px 25px -5px rgba(0,0,0,.1);
        }
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        /* ── NAV ── */
        .top-nav{background:var(--card);border-bottom:1px solid var(--border);padding:10px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:50;box-shadow:var(--shadow-sm);}
        .brand h1{font-size:17px;font-weight:900;color:var(--primary);display:flex;align-items:center;gap:8px;letter-spacing:-.3px;}
        .brand span{font-size:10px;color:var(--light);display:block;margin-top:1px;}
        .nav-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
        .nav-btn{padding:7px 14px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--text);display:inline-flex;align-items:center;gap:6px;transition:all .2s;}
        .nav-btn:hover{border-color:var(--primary);color:var(--primary);}
        .nav-btn.accent{background:var(--primary);color:#fff;border-color:var(--primary);}
        .nav-btn.accent:hover{background:var(--primary-dk);}
        .nav-btn.green{background:var(--success-lt);color:#16a34a;border-color:#bbf7d0;}
        .nav-btn.green:hover{background:var(--success);color:#fff;}
        .nav-user{text-align:right;margin-left:8px;}
        .nav-user b{font-size:12px;display:block;}
        .nav-user small{font-size:9px;color:var(--light);}
        .btn-logout{padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:#fff;font-size:11px;font-weight:600;cursor:pointer;color:var(--muted);display:inline-flex;align-items:center;gap:5px;transition:all .2s;}
        .btn-logout:hover{border-color:var(--danger);color:var(--danger);}

        /* ── CONTAINER ── */
        .admin-wrap{padding:20px;max-width:1500px;margin:0 auto;display:flex;flex-direction:column;gap:20px;}

        /* ── STATS ── */
        .stats-row{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;}
        .stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px 16px;position:relative;overflow:hidden;transition:all .25s;}
        .stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
        .stat-card.c-blue::before{background:var(--primary);}
        .stat-card.c-green::before{background:var(--success);}
        .stat-card.c-purple::before{background:var(--purple);}
        .stat-card.c-red::before{background:var(--danger);}
        .stat-card.c-amber::before{background:var(--warning);}
        .stat-card.c-teal::before{background:#14b8a6;}
        .stat-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;margin-bottom:10px;}
        .stat-icon.blue{background:var(--primary-lt);color:var(--primary);}
        .stat-icon.green{background:var(--success-lt);color:var(--success);}
        .stat-icon.purple{background:var(--purple-lt);color:var(--purple);}
        .stat-icon.red{background:var(--danger-lt);color:var(--danger);}
        .stat-icon.amber{background:var(--warning-lt);color:var(--warning);}
        .stat-icon.teal{background:#ccfbf1;color:#14b8a6;}
        .stat-num{font-size:26px;font-weight:900;line-height:1;margin-bottom:3px;}
        .stat-lbl{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;}

        /* ── SECTION CARD ── */
        .section-card{background:var(--card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow-sm);overflow:hidden;}
        .section-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;}
        .section-title{font-size:14px;font-weight:800;display:flex;align-items:center;gap:7px;}
        .section-title i{color:var(--primary);font-size:13px;}
        .section-body{padding:20px;}

        /* ── CHARTS ── */
        .charts-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
        .chart-box{position:relative;height:260px;}
        .chart-box canvas{width:100%!important;height:100%!important;}

        /* ── MAIN GRID ── */
        .main-row{display:grid;grid-template-columns:1fr 360px;gap:16px;}

        /* ── FILTER BAR ── */
        .filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;}
        .filter-bar .search-box{flex:1;min-width:180px;position:relative;}
        .filter-bar .search-box input{width:100%;padding:9px 12px 9px 36px;border:1px solid var(--border);border-radius:9px;font-family:inherit;font-size:12px;outline:none;background:var(--bg);transition:all .2s;}
        .filter-bar .search-box input:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.08);}
        .filter-bar .search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--light);font-size:13px;}
        .filter-select{padding:9px 12px;border:1px solid var(--border);border-radius:9px;font-family:inherit;font-size:12px;outline:none;background:var(--bg);color:var(--text);cursor:pointer;min-width:130px;}
        .filter-select:focus{border-color:var(--primary);}

        /* ── DATA TABLE ── */
        .table-wrap{overflow-x:auto;max-height:520px;overflow-y:auto;border:1px solid var(--border);border-radius:12px;}
        .table-wrap::-webkit-scrollbar{width:5px;height:5px;}
        .table-wrap::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px;}
        .data-table{width:100%;border-collapse:collapse;font-size:12px;min-width:900px;}
        .data-table th{background:#f8fafc;padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:2;}
        .data-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
        .data-table tr:hover td{background:#fafbfd;}
        .role-badge{padding:3px 8px;border-radius:5px;font-size:9px;font-weight:800;letter-spacing:.3px;white-space:nowrap;}
        .role-admin{background:var(--primary);color:#fff;}
        .role-juri{background:var(--success-lt);color:#16a34a;}
        .role-grand{background:var(--purple-lt);color:var(--purple);}
        .role-user{background:#f1f5f9;color:var(--muted);}
        .status-badge{padding:3px 8px;border-radius:5px;font-size:9px;font-weight:800;white-space:nowrap;}
        .s-dinilai{background:var(--success-lt);color:#16a34a;}
        .s-grand{background:var(--purple-lt);color:var(--purple);}
        .s-belum{background:var(--warning-lt);color:#d97706;}
        .total-val{font-size:14px;font-weight:900;color:var(--primary);}
        .total-val.zero{color:var(--light);font-size:12px;font-weight:600;}
        .juri-info{font-size:11px;line-height:1.5;}
        .juri-info .j-name{font-weight:700;color:var(--primary);}
        .juri-info .g-name{color:var(--purple);font-weight:700;font-size:10px;}
        .btn-xs{padding:5px 9px;border:none;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:4px;transition:all .15s;}
        .btn-xs.blue{background:var(--primary-lt);color:var(--primary);}
        .btn-xs.blue:hover{background:var(--primary);color:#fff;}
        .btn-xs.green{background:var(--success-lt);color:#16a34a;}
        .btn-xs.green:hover{background:var(--success);color:#fff;}
        .btn-xs.red{background:var(--danger-lt);color:var(--danger);}
        .btn-xs.red:hover{background:var(--danger);color:#fff;}
        .btn-xs.purple{background:var(--purple-lt);color:var(--purple);}
        .btn-xs.purple:hover{background:var(--purple);color:#fff;}

        /* ── USER PANEL ── */
        .user-panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;}
        .user-list{max-height:460px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;}
        .user-list::-webkit-scrollbar{width:4px;}
        .user-list::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px;}
        .user-card{display:flex;align-items:center;justify-content:space-between;padding:12px;border:1px solid var(--border);border-radius:10px;transition:all .2s;background:#fff;}
        .user-card:hover{border-color:#c7d2fe;box-shadow:0 2px 8px rgba(37,99,235,.06);}
        .user-avatar{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;margin-right:10px;flex-shrink:0;}
        .user-card-body{flex:1;min-width:0;}
        .user-card-body h4{font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .user-card-body span{font-size:10px;color:var(--light);display:block;}
        .user-card-actions{display:flex;gap:4px;flex-shrink:0;}

        /* ── MODALS ── */
        .modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);z-index:99;display:none;place-items:center;}
        .modal-bg.show{display:grid;}
        .modal-box{background:#fff;border-radius:20px;width:92%;max-width:var(--mw,500px);max-height:88vh;overflow:hidden;box-shadow:var(--shadow-lg);display:grid;grid-template-rows:auto 1fr auto;}
        .modal-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f8fafc,#f1f5f9);}
        .modal-head h3{font-size:14px;font-weight:800;display:flex;align-items:center;gap:7px;}
        .modal-head h3 i{color:var(--primary);}
        .modal-close{background:none;border:none;font-size:18px;cursor:pointer;color:var(--muted);width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:all .15s;}
        .modal-close:hover{background:rgba(0,0,0,.06);}
        .modal-body{padding:20px;overflow-y:auto;}
        .modal-foot{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;background:var(--bg);}
        .form-group{margin-bottom:14px;}
        .form-label{display:block;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
        .form-control{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:9px;font-family:inherit;font-size:13px;color:var(--text);outline:none;background:#f8fafc;transition:all .2s;}
        .form-control:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.08);}
        select.form-control{cursor:pointer;}
        .btn-primary{padding:10px 20px;background:var(--primary);color:#fff;border:none;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;box-shadow:0 3px 10px rgba(37,99,235,.2);}
        .btn-primary:hover{background:var(--primary-dk);transform:translateY(-1px);}
        .btn-cancel{padding:10px 18px;background:#fff;color:var(--muted);border:1px solid var(--border);border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;}
        .btn-cancel:hover{border-color:var(--muted);color:var(--text);}

        /* ── DETAIL NILAI MODAL ── */
        .detail-banner{background:linear-gradient(135deg,var(--primary-lt),#dbeafe);border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;}
        .detail-banner h4{font-size:14px;font-weight:800;color:var(--primary-dk);}
        .detail-banner .meta{font-size:11px;color:#3b82f6;margin-top:4px;display:flex;gap:12px;flex-wrap:wrap;}
        .detail-banner .meta span{display:flex;align-items:center;gap:4px;}
        .detail-total-chip{background:var(--primary);color:#fff;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:800;white-space:nowrap;}
        .detail-kat{margin-bottom:12px;}
        .detail-kat-head{display:flex;justify-content:space-between;align-items:center;padding:7px 12px;background:var(--primary-lt);border:1px solid #bfdbfe;border-radius:8px 8px 0 0;}
        .detail-kat-title{font-size:11px;font-weight:800;color:var(--primary);text-transform:uppercase;letter-spacing:.3px;}
        .detail-kat-sub{font-size:11px;font-weight:700;color:#3b82f6;}
        .detail-kat-body{border:1px solid #bfdbfe;border-top:none;border-radius:0 0 8px 8px;overflow:hidden;}
        .detail-row{display:grid;grid-template-columns:1fr 80px;align-items:center;padding:8px 12px;border-bottom:1px solid #f1f5f9;}
        .detail-row:last-child{border-bottom:none;}
        .detail-row .label{font-size:12px;font-weight:600;}
        .detail-row .meta{font-size:10px;color:var(--light);}
        .val-chip{padding:3px 10px;border-radius:5px;font-size:12px;font-weight:800;text-align:center;}
        .val-chip.has{background:#dbeafe;color:var(--primary);}
        .val-chip.no{background:#f1f5f9;color:var(--light);font-size:10px;}

        /* ── OLD FEATURES MODAL ── */
        .old-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
        .old-card{border:1px solid var(--border);border-radius:12px;overflow:hidden;background:#fff;}
        .old-card .section-head{padding:12px 16px;}
        .old-card .section-body{padding:16px;}

        /* ── EMPTY ── */
        .empty-state{text-align:center;padding:40px 20px;color:var(--light);}
        .empty-state i{font-size:32px;margin-bottom:8px;display:block;opacity:.4;}
        .empty-state p{font-size:12px;}

        /* ── RESPONSIVE ── */
        @media(max-width:1200px){.stats-row{grid-template-columns:repeat(3,1fr);}.charts-row{grid-template-columns:1fr 1fr;}.main-row{grid-template-columns:1fr;}}
        @media(max-width:768px){.stats-row{grid-template-columns:1fr 1fr;}.charts-row{grid-template-columns:1fr;}.old-grid{grid-template-columns:1fr;}.nav-actions{gap:5px;}.nav-btn span{display:none;}}
    </style>
</head>
<body>

<!-- ═══ NAVBAR ═══ -->
<nav class="top-nav">
    <div class="brand">
        <h1><i class="fas fa-shield-halved"></i> ADMIN DASHBOARD</h1>
        <span>Pusat Kontrol Sistem Kontes LCI</span>
    </div>
    <div class="nav-actions">
        <button class="nav-btn green" onclick="openModal('modalOld')"><i class="fas fa-database"></i> <span>Undian & Registrasi</span></button>
        <button class="nav-btn accent" onclick="exportCSV()"><i class="fas fa-download"></i> <span>Export CSV</span></button>
        <div class="nav-user"><b>{{ $user->name }}</b><small>Administrator</small></div>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf<button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i></button></form>
    </div>
</nav>

<!-- ═══ MAIN CONTENT ═══ -->
<div class="admin-wrap">

    <!-- ── STATISTIK ── -->
    <div class="stats-row">
        <div class="stat-card c-blue"><div class="stat-icon blue"><i class="fas fa-fish"></i></div><div class="stat-num" id="sTotal">0</div><div class="stat-lbl">Total Peserta</div></div>
        <div class="stat-card c-green"><div class="stat-icon green"><i class="fas fa-check-double"></i></div><div class="stat-num" id="sDinilai">0</div><div class="stat-lbl">Sudah Dinilai</div></div>
        <div class="stat-card c-purple"><div class="stat-icon purple"><i class="fas fa-crown"></i></div><div class="stat-num" id="sGrand">0</div><div class="stat-lbl">Grand Juri Edit</div></div>
        <div class="stat-card c-red"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div class="stat-num" id="sBelum">0</div><div class="stat-lbl">Belum Dinilai</div></div>
        <div class="stat-card c-amber"><div class="stat-icon amber"><i class="fas fa-user-pen"></i></div><div class="stat-num" id="sJuri">0</div><div class="stat-lbl">Juri Aktif</div></div>
        <div class="stat-card c-teal"><div class="stat-icon teal"><i class="fas fa-chart-line"></i></div><div class="stat-num" id="sAvg">0</div><div class="stat-lbl">Rata-rata Nilai</div></div>
    </div>

    <!-- ── GRAFIK ── -->
    <div class="charts-row">
        <div class="section-card">
            <div class="section-head"><div class="section-title"><i class="fas fa-chart-bar"></i> Peserta per Kategori</div></div>
            <div class="section-body"><div class="chart-box"><canvas id="chartKategori"></canvas></div></div>
        </div>
        <div class="section-card">
            <div class="section-head"><div class="section-title"><i class="fas fa-chart-pie"></i> Status Penilaian</div></div>
            <div class="section-body"><div class="chart-box"><canvas id="chartStatus"></canvas></div></div>
        </div>
        <div class="section-card">
            <div class="section-head"><div class="section-title"><i class="fas fa-ranking-star"></i> Top 10 Nilai Tertinggi</div></div>
            <div class="section-body"><div class="chart-box"><canvas id="chartTop"></canvas></div></div>
        </div>
    </div>

    <!-- ── KONTEN UTAMA ── -->
    <div class="main-row">

        <!-- KOLOM KIRI: DATA PENILAIAN -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-title"><i class="fas fa-table-list"></i> Data Penilaian Keseluruhan</div>
                <span style="font-size:10px;color:var(--light);">Menampilkan semua input dari Juri & Grand Juri</span>
            </div>
            <div class="section-body">
                <div class="filter-bar">
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" id="filterSearch" placeholder="Cari nama peserta..."></div>
                    <select class="filter-select" id="filterKategori">
                        <option value="">Semua Kategori</option>
                        <option>Overall</option><option>Head</option><option>Face</option><option>Body</option><option>Marking</option><option>Pearl</option><option>Color</option><option>Finnage</option>
                    </select>
                    <select class="filter-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="dinilai">Sudah Dinilai</option>
                        <option value="grand">Grand Juri Edit</option>
                        <option value="belum">Belum Dinilai</option>
                    </select>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th><th>PESERTA</th><th>KATEGORI</th><th>KELAS</th><th>TANK</th>
                                <th>DINILAI OLEH</th><th>TOTAL NILAI</th><th>STATUS</th><th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tBody"><tr><td colspan="9"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div></td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: KELOLA USER -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-title"><i class="fas fa-users-gear"></i> Kelola User</div>
            </div>
            <div class="section-body">
                <div class="user-panel-head">
                    <span style="font-size:11px;color:var(--muted);font-weight:600;" id="userCount">0 user</span>
                    <button class="btn-xs blue" onclick="openModal('modalCreate')"><i class="fas fa-plus"></i> Tambah User</button>
                </div>
                <div class="user-list" id="userList">
                    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL: DETAIL NILAI ═══ -->
<div class="modal-bg" id="modalDetail" style="--mw:750px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-eye"></i> Detail Nilai Peserta</h3><button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body" id="detailBody"></div>
        <div class="modal-foot"><button class="btn-cancel" onclick="closeModal('modalDetail')">Tutup</button></div>
    </div>
</div>

<!-- ═══ MODAL: TAMBAH USER ═══ -->
<div class="modal-bg" id="modalCreate">
    <div class="modal-box" style="--mw:440px;">
        <div class="modal-head"><h3><i class="fas fa-user-plus"></i> Tambah User Baru</h3><button class="modal-close" onclick="closeModal('modalCreate')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body">
            <form id="formCreateUser">
                <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Password</label><input type="text" name="password" class="form-control" placeholder="Min. 8 karakter" required></div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="">— Pilih Role —</option>
                        <option value="juri">Juri</option>
                        <option value="grand_juri">Grand Juri</option>
                        <option value="admin">Admin</option>
                        <option value="user">User Biasa</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalCreate')">Batal</button>
            <button class="btn-primary" onclick="submitCreateUser()"><i class="fas fa-save"></i> Simpan User</button>
        </div>
    </div>
</div>

<!-- ═══ MODAL: GANTI PASSWORD ═══ -->
<div class="modal-bg" id="modalPwd">
    <div class="modal-box" style="--mw:380px;">
        <div class="modal-head"><h3><i class="fas fa-key"></i> Ganti Password</h3><button class="modal-close" onclick="closeModal('modalPwd')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body">
            <p style="font-size:12px;margin-bottom:14px;">User: <b id="pwdTarget"></b></p>
            <input type="hidden" id="pwdUserId">
            <div class="form-group"><label class="form-label">Password Baru</label><input type="text" id="pwdNew" class="form-control" placeholder="Min. 8 karakter"></div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalPwd')">Batal</button>
            <button class="btn-primary" onclick="submitPwd()"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </div>
</div>

<!-- ═══ MODAL: MODUL LAMA ═══ -->
<div class="modal-bg" id="modalOld" style="--mw:1100px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-box-archive"></i> Modul Registrasi & Undian Tank</h3><button class="modal-close" onclick="closeModal('modalOld')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body">
            <p style="text-align:center;color:var(--light);margin-bottom:16px;font-size:12px;">Fitur pendukung kontes</p>
            <div class="old-grid">
                <div class="old-card">
                    <div class="section-head"><div class="section-title" style="font-size:13px;"><i class="fas fa-user-plus"></i> Registrasi Peserta</div></div>
                    <div class="section-body">
                        <form id="regFormOld">
                            @csrf
                            <div class="form-group"><label class="form-label">Nama Peserta</label><input type="text" name="nama_peserta" class="form-control" required></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div class="form-group"><label class="form-label">Kategori</label><select name="kategori" class="form-control" required><option value="" disabled selected>Pilih</option><option>Cencu</option><option>Chginwa</option><option>Freemarking</option><option>Goldenbase</option><option>Klasik</option><option>Bonsai</option><option>Jumbo</option></select></div>
                                <div class="form-group"><label class="form-label">Kelas</label><select name="kelas" class="form-control" required><option value="" disabled selected>Pilih</option><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option></select></div>
                            </div>
                            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-plus"></i> Daftar</button>
                        </form>
                    </div>
                </div>
                <div class="old-card" style="background:#1e293b;color:#fff;">
                    <div class="section-head" style="border-color:rgba(255,255,255,.1);"><div class="section-title" style="color:#fff;font-size:13px;"><i class="fas fa-dice"></i> Undian Tank</div></div>
                    <div class="section-body" style="text-align:center;">
                        <select id="pesertaSelectOld" class="form-control" style="background:rgba(0,0,0,.3);color:#fff;border-color:rgba(255,255,255,.1);margin-bottom:14px;"></select>
                        <div style="font-size:48px;font-weight:900;margin:16px 0;" id="numberDisplayOld">--</div>
                        <button class="btn-primary" id="btnAcakOld" style="width:100%;justify-content:center;background:#3b82f6;"><i class="fas fa-shuffle"></i> Acak Nomor</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════
   HELPERS & STATE
   ═══════════════════════════════════════════════ */
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
function esc(s){if(!s)return '';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function getCsrf(){return document.querySelector('meta[name="csrf-token"]').getAttribute('content');}

document.querySelectorAll('.modal-bg').forEach(function(m){
    m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});
});

var allScoringData=[];
var chartKat,chartStat,chartTop;

var formFields={
    overall:[{id:'impression',label:'Impression',max:100}],
    head:[{id:'size',label:'Size',max:60},{id:'bentuk',label:'Bentuk Kepala',max:40}],
    face:[{id:'pipi',label:'Pipi',max:25},{id:'mata',label:'Mata',max:25},{id:'bibir',label:'Bibir',max:25},{id:'kondisi',label:'Kondisi Mata & Insang',max:25}],
    body:[{id:'bentuk',label:'Bentuk Badan',max:50},{id:'proporsi',label:'Proporsional',max:40},{id:'pangkal',label:'Pangkal',max:10}],
    marking:[{id:'fullness',label:'Fullness',max:40},{id:'contrast',label:'Contrast',max:40},{id:'bentuk',label:'Bentuk',max:20}],
    pearl:[{id:'shining',label:'Shining',max:45},{id:'fullness',label:'Fullness',max:35},{id:'bentuk',label:'Bentuk',max:20}],
    color:[{id:'komposisi',label:'Komposisi',max:45},{id:'kecerahan',label:'Kecerahan',max:35},{id:'fullness',label:'Fullness',max:20}],
    finnage:[{id:'bentuk',label:'Bentuk Sirip & Ekor',max:75},{id:'kecerahan',label:'Kecerahan',max:25}]
};

/* ═══════════════════════════════════════════════
   LOAD STATISTIK & CHARTS
   ═══════════════════════════════════════════════ */
function loadDashboard(){
    fetch('/api/admin/dashboard-stats',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        document.getElementById('sTotal').innerText=d.total_peserta||0;
        document.getElementById('sDinilai').innerText=d.sudah_dinilai||0;
        document.getElementById('sGrand').innerText=d.grand_edited||0;
        document.getElementById('sBelum').innerText=d.belum_dinilai||0;
        document.getElementById('sJuri').innerText=d.juri_aktif||0;
        document.getElementById('sAvg').innerText=d.rata_rata||0;
        renderChartKategori(d.per_kategori||{});
        renderChartStatus(d.sudah_dinilai||0,d.grand_edited||0,d.belum_dinilai||0);
        renderChartTop(d.top_10||[]);
    })
    .catch(function(){});
}

function renderChartKategori(data){
    var labels=Object.keys(data),vals=Object.values(data);
    var colors=['#2563eb','#7c3aed','#10b981','#f59e0b','#ef4444','#14b8a6','#f97316','#6366f1'];
    if(chartKat)chartKat.destroy();
    chartKat=new Chart(document.getElementById('chartKategori'),{
        type:'bar',
        data:{labels:labels,datasets:[{data:vals,backgroundColor:colors.slice(0,labels.length),borderRadius:6,borderSkipped:false}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{font:{size:10}}},x:{ticks:{font:{size:10}}}}}
    });
}

function renderChartStatus(dinilai,grand,belum){
    if(chartStat)chartStat.destroy();
    chartStat=new Chart(document.getElementById('chartStatus'),{
        type:'doughnut',
        data:{labels:['Sudah Dinilai','Grand Juri Edit','Belum Dinilai'],datasets:[{data:[dinilai,grand,belum],backgroundColor:['#10b981','#7c3aed','#f59e0b'],borderWidth:0,spacing:2}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{font:{size:10},padding:12,usePointStyle:true,pointStyleWidth:8}}}}
    });
}

function renderChartTop(data){
    var labels=[],vals=[];
    for(var i=0;i<data.length;i++){labels.push(data[i].nama);vals.push(data[i].total);}
    if(chartTop)chartTop.destroy();
    chartTop=new Chart(document.getElementById('chartTop'),{
        type:'bar',
        data:{labels:labels,datasets:[{data:vals,backgroundColor:'#2563eb',borderRadius:4,borderSkipped:false}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{font:{size:10}}},y:{ticks:{font:{size:9,family:'Plus Jakarta Sans'}}}}}
    });
}

/* ═══════════════════════════════════════════════
   LOAD DATA PENILAIAN
   ═══════════════════════════════════════════════ */
function loadScoringData(){
    var params=new URLSearchParams();
    var s=document.getElementById('filterSearch').value;
    var k=document.getElementById('filterKategori').value;
    var st=document.getElementById('filterStatus').value;
    if(s)params.set('search',s);
    if(k)params.set('kategori',k);
    if(st)params.set('status',st);

    fetch('/api/admin/scoring-data?'+params.toString(),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        allScoringData=data;
        renderTable(data);
    })
    .catch(function(){});
}

function renderTable(data){
    var tb=document.getElementById('tBody');
    tb.innerHTML='';
    if(!data||data.length===0){
        tb.innerHTML='<tr><td colspan="9"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data ditemukan.</p></div></td></tr>';
        return;
    }
    for(var i=0;i<data.length;i++){
        var p=data[i];
        var tr=document.createElement('tr');
        var juriHtml='<span style="color:var(--light);font-size:11px;">—</span>';
        if(p.juri_nama&&p.juri_nama!=='—'){
            juriHtml='<div class="juri-info"><i class="fas fa-user-pen" style="font-size:9px;color:var(--primary);"></i> <span class="j-name">'+esc(p.juri_nama)+'</span>';
            if(p.grand_juri_nama) juriHtml+='<br><i class="fas fa-crown" style="font-size:9px;"></i> <span class="g-name">'+esc(p.grand_juri_nama)+'</span>';
            juriHtml+='</div>';
        }
        var statusCls=p.grand_juri_nama?'s-grand':(p.status==='Sudah Dinilai'?'s-dinilai':'s-belum');
        var statusTxt=p.grand_juri_nama?'GRAND EDIT':(p.status==='Sudah Dinilai'?'DINILAI':'BELUM');
        var totalHtml=p.total_nilai>0?'<span class="total-val">'+p.total_nilai+'</span>':'<span class="total-val zero">—</span>';
        tr.innerHTML=
            '<td style="font-weight:700;color:var(--light);font-size:11px;">'+(i+1)+'</td>'+
            '<td style="font-weight:700;">'+esc(p.nama_peserta)+'</td>'+
            '<td style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;">'+esc(p.kategori)+'</td>'+
            '<td style="font-size:11px;color:var(--muted);">'+esc(p.kelas)+'</td>'+
            '<td style="font-weight:700;color:var(--primary);">Tank '+(p.nomor_tank||'—')+'</td>'+
            '<td>'+juriHtml+'</td>'+
            '<td>'+totalHtml+'</td>'+
            '<td><span class="status-badge '+statusCls+'">'+statusTxt+'</span></td>'+
            '<td><button class="btn-xs blue" onclick="openDetail('+p.id+')"><i class="fas fa-eye"></i></button></td>';
        tb.appendChild(tr);
    }
}

var filterT;
document.getElementById('filterSearch').addEventListener('input',function(){
    clearTimeout(filterT);filterT=setTimeout(loadScoringData,300);
});
document.getElementById('filterKategori').addEventListener('change',loadScoringData);
document.getElementById('filterStatus').addEventListener('change',loadScoringData);

/* ═══════════════════════════════════════════════
   DETAIL NILAI MODAL
   ═══════════════════════════════════════════════ */
function openDetail(id){
    document.getElementById('detailBody').innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>';
    openModal('modalDetail');
    fetch('/api/grand-juri/peserta?id='+id,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        var p=Array.isArray(data)?data[0]:data;
        if(!p){document.getElementById('detailBody').innerHTML='<div class="empty-state">Tidak ditemukan.</div>';return;}
        renderDetailView(p);
    });
}

function renderDetailView(p){
    var nd=p.nilai_detail;
    var html='<div class="detail-banner"><div><h4>'+esc(p.nama_peserta)+'</h4><div class="meta">'+
        '<span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank||'—')+'</span>'+
        '<span><i class="fas fa-tag"></i> '+esc(p.kategori)+' - '+esc(p.kelas)+'</span>';
    if(p.juri_nama&&p.juri_nama!=='—') html+='<span><i class="fas fa-user-pen"></i> '+esc(p.juri_nama)+'</span>';
    if(p.grand_juri_nama) html+='<span style="color:var(--purple);"><i class="fas fa-crown"></i> '+esc(p.grand_juri_nama)+'</span>';
    html+='</div></div><div class="detail-total-chip"><i class="fas fa-star" style="margin-right:4px;"></i>'+p.total_nilai+'</div></div>';

    if(p.grand_juri_nama){
        html+='<div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;margin-bottom:14px;display:flex;gap:6px;align-items:flex-start;"><i class="fas fa-circle-info" style="margin-top:1px;"></i><span>Nilai final setelah diperbarui oleh <b>'+esc(p.grand_juri_nama)+'</b>.</span></div>';
    }

    if(!nd||typeof nd!=='object'){
        html+='<div class="empty-state" style="padding:30px;"><i class="fas fa-clipboard-list"></i><p>Belum ada nilai.</p></div>';
        document.getElementById('detailBody').innerHTML=html;return;
    }

    var kats=Object.keys(formFields);
    for(var ki=0;ki<kats.length;ki++){
        var kat=kats[ki];
        var fields=formFields[kat],katNilai=nd[kat]||{},sub=0;
        for(var fi=0;fi<fields.length;fi++){
            if(katNilai[fields[fi].id]!=null&&katNilai[fields[fi].id]!=='') sub+=parseInt(katNilai[fields[fi].id])||0;
        }
        html+='<div class="detail-kat"><div class="detail-kat-head"><span class="detail-kat-title"><i class="fas fa-layer-group" style="margin-right:4px;"></i>'+kat.charAt(0).toUpperCase()+kat.slice(1)+'</span><span class="detail-kat-sub">Subtotal: '+sub+'</span></div><div class="detail-kat-body">';
        for(var fj=0;fj<fields.length;fj++){
            var f=fields[fj],v=katNilai[f.id],has=(v!=null&&v!=='');
            html+='<div class="detail-row"><div><div class="label">'+f.label+'</div><div class="meta">Maks '+f.max+'</div></div><span class="val-chip '+(has?'has':'no')+'">'+(has?v:'N/A')+'</span></div>';
        }
        html+='</div></div>';
    }
    document.getElementById('detailBody').innerHTML=html;
}

/* ═══════════════════════════════════════════════
   KELOLA USER
   ═══════════════════════════════════════════════ */
var roleColors={admin:'#2563eb',juri:'#16a34a',grand_juri:'#7c3aed',user:'#94a3b8'};
var roleLabels={admin:'ADMIN',juri:'JURI',grand_juri:'GRAND JURI',user:'USER'};
var roleBadgeCls={admin:'role-admin',juri:'role-juri',grand_juri:'role-grand',user:'role-user'};

function loadUsers(){
    var c=document.getElementById('userList');
    c.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>';
    fetch('{{ route("api.list.users") }}',{headers:{'Accept':'application/json'}})
    .then(function(r){
        if(!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(data){
        c.innerHTML='';
        /* Cek apakah response benar-benar array */
        if(!Array.isArray(data)){
            console.error('API list-users bukan array:', data);
            c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);font-weight:600;">Error: Response bukan array</p><p style="font-size:10px;margin-top:4px;">Cek Console (F12) untuk detail.</p></div>';
            document.getElementById('userCount').textContent='Error';
            return;
        }
        document.getElementById('userCount').textContent=data.length+' user';
        if(!data.length){
            c.innerHTML='<div class="empty-state"><i class="fas fa-user-slash"></i><p>Belum ada user.</p></div>';
            return;
        }
        for(var i=0;i<data.length;i++){
            var u=data[i];
            var role=u.role||'user';
            var isMe={{ auth()->id() }}===u.id;
            var div=document.createElement('div');
            div.className='user-card';
            div.innerHTML=
                '<div style="display:flex;align-items:center;flex:1;min-width:0;">'+
                    '<div class="user-avatar" style="background:'+roleColors[role]+';">'+esc(u.name.charAt(0).toUpperCase())+'</div>'+
                    '<div class="user-card-body"><h4>'+esc(u.name)+'</h4><span>'+esc(u.email)+'</span></div>'+
                '</div>'+
                '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">'+
                    '<span class="role-badge '+roleBadgeCls[role]+'">'+roleLabels[role]+'</span>'+
                    '<div class="user-card-actions">'+
                        (!isMe?'<button class="btn-xs green" onclick="openRoleMenu(event,'+u.id+',\''+esc(u.name).replace(/'/g,"\\'")+'\',\''+role+'\')" title="Ubah Role"><i class="fas fa-arrows-rotate"></i></button>':'')+
                        '<button class="btn-xs blue" onclick="openPwdModal('+u.id+',\''+esc(u.name).replace(/'/g,"\\'")+'\')" title="Ganti Password"><i class="fas fa-key"></i></button>'+
                    '</div>'+
                '</div>';
            c.appendChild(div);
        }
    })
    .catch(function(err){
        console.error('Fetch list-users error:', err);
        c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);font-weight:600;">Gagal memuat user</p><p style="font-size:10px;margin-top:4px;">'+esc(err.message)+'</p></div>';
        document.getElementById('userCount').textContent='Error';
    });
}

function submitCreateUser(){
    var form=document.getElementById('formCreateUser');
    var fd=new FormData(form);
    fd.append('_token',getCsrf());
    var name=fd.get('name'),email=fd.get('email'),pw=fd.get('password'),role=fd.get('role');
    if(!name||!email||!pw||!role){alert('Semua field wajib diisi!');return;}
    if(pw.length<8){alert('Password minimal 8 karakter!');return;}
    fetch('/api/admin/create-user',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){if(!r.ok)return r.json().then(function(d){throw d;});return r.json();})
    .then(function(d){
        if(d.success){closeModal('modalCreate');form.reset();loadUsers();alert('User berhasil ditambahkan!');}
        else alert(d.message||'Gagal.');
    })
    .catch(function(e){if(e.errors)alert(Object.values(e.errors).join('\n'));else alert('Gagal menyimpan.');});
}

function openPwdModal(id,name){
    document.getElementById('pwdUserId').value=id;
    document.getElementById('pwdTarget').textContent=name;
    document.getElementById('pwdNew').value='';
    openModal('modalPwd');
}

function submitPwd(){
    var pw=document.getElementById('pwdNew').value;
    if(pw.length<8){alert('Min 8 karakter!');return;}
    var fd=new FormData();
    fd.append('_token',getCsrf());
    fd.append('user_id',document.getElementById('pwdUserId').value);
    fd.append('new_password',pw);
    fetch('{{ route("api.update.password") }}',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){if(d.success){closeModal('modalPwd');loadUsers();alert(d.message);}else alert('Gagal.');})
    .catch(function(){alert('Error.');});
}

var activeRoleMenu=null;
function openRoleMenu(e,uid,name,currentRole){
    e.stopPropagation();
    closeRoleMenu();
    var menu=document.createElement('div');
    menu.id='roleMenuDropdown';
    menu.style.cssText='position:fixed;z-index:9999;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.15);padding:6px;min-width:150px;';
    menu.style.left=e.clientX+'px';
    menu.style.top=e.clientY+'px';
    var roles=[
        {key:'admin',label:'Admin',color:'#2563eb'},
        {key:'juri',label:'Juri',color:'#16a34a'},
        {key:'grand_juri',label:'Grand Juri',color:'#7c3aed'},
        {key:'user',label:'User Biasa',color:'#94a3b8'}
    ];
    for(var i=0;i<roles.length;i++){
        (function(r){
            var isActive=r.key===currentRole;
            var btn=document.createElement('button');
            btn.style.cssText='display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border:none;border-radius:6px;font-family:inherit;font-size:12px;font-weight:'+(isActive?'800':'600')+';cursor:pointer;background:'+(isActive?'#f1f5f9':'transparent')+';color:var(--text);';
            btn.innerHTML='<span style="width:8px;height:8px;border-radius:50%;background:'+r.color+';"></span>'+r.label+(isActive?' <i class="fas fa-check" style="margin-left:auto;font-size:10px;color:var(--primary);"></i>':'');
            btn.onmouseover=function(){if(!isActive)this.style.background='#f8fafc';};
            btn.onmouseout=function(){if(!isActive)this.style.background='transparent';};
            btn.onclick=function(ev){ev.stopPropagation();changeRole(uid,name,r.key);closeRoleMenu();};
            menu.appendChild(btn);
        })(roles[i]);
    }
    document.body.appendChild(menu);
    activeRoleMenu=menu;
    setTimeout(function(){document.addEventListener('click',closeRoleMenu,{once:true});},10);
}

function closeRoleMenu(){var m=document.getElementById('roleMenuDropdown');if(m)m.remove();activeRoleMenu=null;}

function changeRole(uid,name,newRole){
    if(!confirm('Ubah role "'+name+'" menjadi '+roleLabels[newRole]+'?'))return;
    var fd=new FormData();
    fd.append('_token',getCsrf());
    fd.append('user_id',uid);
    fd.append('new_role',newRole);
    fetch('/api/admin/change-role',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){if(d.success){loadUsers();}else alert(d.message||'Gagal.');})
    .catch(function(){alert('Error.');});
}

/* ═══════════════════════════════════════════════
   EXPORT CSV
   ═══════════════════════════════════════════════ */
function exportCSV(){
    if(!allScoringData.length){alert('Tidak ada data untuk diexport.');return;}
    var header='No,Nama Peserta,Kategori,Kelas,No Tank,Juri,Grand Juri,Total Nilai,Status\n';
    var rows='';
    for(var i=0;i<allScoringData.length;i++){
        var p=allScoringData[i];
        rows+=(i+1)+','+
            '"'+(p.nama_peserta||'')+'",'+
            '"'+(p.kategori||'')+'",'+
            '"'+(p.kelas||'')+'",'+
            '"'+(p.nomor_tank||'')+'",'+
            '"'+(p.juri_nama||'')+'",'+
            '"'+(p.grand_juri_nama||'')+'",'+
            (p.total_nilai||0)+','+
            '"'+(p.grand_juri_nama?'Grand Juri Edit':p.status)+'"\n';
    }
    var blob=new Blob(['\uFEFF'+header+rows],{type:'text/csv;charset=utf-8;'});
    var url=URL.createObjectURL(blob);
    var a=document.createElement('a');a.href=url;a.download='LCI_Penilaian_'+new Date().toISOString().slice(0,10)+'.csv';a.click();
    URL.revokeObjectURL(url);
}

/* ═══════════════════════════════════════════════
   MODUL LAMA (UNDIAN & REGISTRASI)
   ═══════════════════════════════════════════════ */
function loadPesertaOld(){
    fetch('{{ route("api.peserta.belum.tank") }}',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        var sel=document.getElementById('pesertaSelectOld');
        sel.innerHTML='<option value="" disabled selected>Pilih Peserta</option>';
        if(!data.length){sel.innerHTML+='<option disabled>Kosong</option>';return;}
        for(var i=0;i<data.length;i++){
            var o=document.createElement('option');
            o.value=data[i].id;
            o.textContent=data[i].nama_peserta+' - '+data[i].kategori;
            sel.appendChild(o);
        }
    });
}
loadPesertaOld();

document.getElementById('btnAcakOld').addEventListener('click',function(){
    if(!document.getElementById('pesertaSelectOld').value)return;
    var display=document.getElementById('numberDisplayOld');
    display.style.color='#60a5fa';
    this.disabled=true;
    var c=0;
    var iv=setInterval(function(){
        display.textContent=Math.floor(Math.random()*100)+1;
        if(c++>15){
            clearInterval(iv);
            var fd=new FormData();
            fd.append('_token',getCsrf());
            fd.append('peserta_id',document.getElementById('pesertaSelectOld').value);
            fd.append('range_min',1);
            fd.append('range_max',100);
            fetch('{{ route("api.acak.tank.admin") }}',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.success){display.textContent=d.nomor_tank;display.style.color='#22c55e';setTimeout(loadPesertaOld,2000);}
                else throw new Error(d.message);
            })
            .catch(function(e){display.textContent='--';display.style.color='#fff';alert(e.message);document.getElementById('btnAcakOld').disabled=false;});
        }
    },60);
});

document.getElementById('regFormOld').addEventListener('submit',function(e){
    e.preventDefault();
    var fd=new FormData(this);
    fd.append('_token',getCsrf());
    fetch('{{ route("store.registrasi") }}',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){if(!r.ok)return r.json().then(function(d){throw d;});return r.json();})
    .then(function(d){if(d.success){document.getElementById('regFormOld').reset();alert('Berhasil didaftarkan!');loadPesertaOld();}})
    .catch(function(e){if(e.errors)alert(Object.values(e.errors).join('\n'));else alert('Gagal menyimpan.');});
});

/* ═══════════════════════════════════════════════
   INIT
   ═══════════════════════════════════════════════ */
loadDashboard();
loadScoringData();
loadUsers();
</script>
</body>
</html>