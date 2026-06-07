<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FAVICON -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <meta name="theme-color" content="#2563eb">
    <title>Admin Dashboard - LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    :root{
        --ocean-950:#04070F; --ocean-900:#0B1220; --ocean-850:#0E1729; --ocean-800:#111E36; --ocean-700:#182947; --ocean-600:#1F3358;
        --royal-700:#1D4ED8; --royal-600:#2563EB; --royal-500:#3B82F6; --royal-400:#60A5FA;
        --cyan-500:#06B6D4; --cyan-400:#22D3EE; --cyan-300:#67E8F9; --cyan-200:#A5F3FC;
        --gold-700:#B45309; --gold-600:#D97706; --gold-500:#F59E0B; --gold-400:#FBBF24; --gold-300:#FCD34D;
        --glass-1:rgba(255,255,255,.03); --glass-2:rgba(255,255,255,.05); --glass-3:rgba(255,255,255,.08); --glass-strong:rgba(255,255,255,.12);
        --bd-1:rgba(255,255,255,.06); --bd-2:rgba(255,255,255,.10); --bd-3:rgba(255,255,255,.16);
        --bd-cyan:rgba(34,211,238,.25); --bd-gold:rgba(245,158,11,.30);
        --text-hi:#F8FAFC; --text:#E2E8F0; --text-mid:#94A3B8; --text-low:#64748B; --text-faint:#475569;
        --success:#10B981; --danger:#EF4444; --warning:#F59E0B;
        --purple:#A855F7; --purple-dk:#9333EA;

        /* LEGACY ALIASES (agar inline style JS lama tetap kebaca) */
        --bg:var(--ocean-900); --card:var(--ocean-800);
        --primary:var(--cyan-400); --primary-dk:var(--royal-700); --primary-lt:rgba(34,211,238,.12);
        --muted:var(--text-mid); --light:var(--text-low); --border:var(--bd-2);
        --success-lt:rgba(16,185,129,.15); --danger-lt:rgba(239,68,68,.15); --warning-lt:rgba(245,158,11,.15); --purple-lt:rgba(168,85,247,.15);
        --shadow-sm:0 1px 2px rgba(0,0,0,.4); --shadow:0 4px 12px rgba(0,0,0,.4); --shadow-lg:0 10px 25px rgba(0,0,0,.5);
    }

    html,body{ height:100%; }
    body{
        font-family:'Plus Jakarta Sans',sans-serif;
        background:var(--ocean-900); color:var(--text);
        min-height:100vh; overflow-x:hidden;
        -webkit-font-smoothing:antialiased;
    }

    /* Background atmosfer */
    body::before{
        content:''; position:fixed; inset:0; z-index:0; pointer-events:none;
        background:
            radial-gradient(ellipse 70% 50% at 50% 0%, rgba(37,99,235,.14) 0%, transparent 55%),
            radial-gradient(ellipse 50% 50% at 100% 100%, rgba(6,182,212,.08) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 0% 70%, rgba(29,78,216,.08) 0%, transparent 60%),
            linear-gradient(180deg, var(--ocean-950) 0%, var(--ocean-900) 45%, var(--ocean-850) 100%);
    }

    /* ===== LAYOUT SHELL ===== */
    .admin-shell{ position:relative; z-index:1; display:grid; grid-template-columns:240px 1fr; min-height:100vh; }

    /* ===== SIDEBAR ===== */
    .sidebar{
        background:rgba(11,18,32,.85); backdrop-filter:blur(14px);
        border-right:1px solid var(--bd-1);
        display:flex; flex-direction:column;
        position:sticky; top:0; height:100vh;
        z-index:90;
    }
    .sidebar-brand{ padding:18px 18px 16px; border-bottom:1px solid var(--bd-1); display:flex; align-items:center; gap:12px; }
    .sb-mark{
        width:40px; height:40px; border-radius:12px; display:grid; place-items:center; flex-shrink:0;
        background:linear-gradient(135deg, var(--royal-600), var(--cyan-500));
        box-shadow:0 6px 18px -6px rgba(6,182,212,.55), inset 0 1px 0 rgba(255,255,255,.25);
        color:#fff; font-size:16px;
    }
    .sb-brand-text h1{ font-family:'Plus Jakarta Sans',sans-serif; font-size:14px; font-weight:800; color:var(--text-hi); letter-spacing:-.01em; }
    .sb-brand-text p{ font-size:9.5px; color:var(--cyan-300); letter-spacing:.12em; text-transform:uppercase; font-weight:700; margin-top:2px; }

    .sidebar-nav{ flex:1; overflow-y:auto; padding:14px 12px; display:flex; flex-direction:column; gap:4px; }
    .sidebar-nav::-webkit-scrollbar{ width:4px; }
    .sidebar-nav::-webkit-scrollbar-thumb{ background:var(--glass-strong); border-radius:10px; }

    .sb-section-label{ font-size:9.5px; font-weight:800; color:var(--text-faint); letter-spacing:.18em; text-transform:uppercase; padding:8px 12px 4px; }
    .sidebar-item{
        display:flex; align-items:center; gap:11px;
        padding:10px 12px; border-radius:11px;
        color:var(--text-mid); font-size:13px; font-weight:600;
        cursor:pointer; transition:all .2s; text-decoration:none;
        border:1px solid transparent;
    }
    .sidebar-item i{ width:18px; text-align:center; font-size:13px; color:var(--text-low); transition:color .2s; }
    .sidebar-item:hover{ background:var(--glass-2); color:var(--text-hi); }
    .sidebar-item:hover i{ color:var(--cyan-400); }
    .sidebar-item.active{
        background:linear-gradient(135deg, rgba(34,211,238,.18), rgba(37,99,235,.12));
        color:var(--text-hi);
        border-color:var(--bd-cyan);
        box-shadow:0 4px 16px -6px rgba(6,182,212,.35), inset 0 1px 0 rgba(255,255,255,.05);
    }
    .sidebar-item.active i{ color:var(--cyan-300); }

    .sidebar-foot{ padding:14px 14px 18px; border-top:1px solid var(--bd-1); }
    .sb-user{ display:flex; align-items:center; gap:10px; padding:8px; border:1px solid var(--bd-2); border-radius:12px; background:var(--glass-2); margin-bottom:10px; }
    .sb-user .avatar{
        width:34px; height:34px; border-radius:10px; display:grid; place-items:center;
        background:linear-gradient(135deg, var(--gold-500), var(--gold-700));
        color:#fff; font-weight:800; font-size:13px; flex-shrink:0;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.3);
    }
    .sb-user .info{ flex:1; min-width:0; line-height:1.1; }
    .sb-user .info b{ font-size:12px; color:var(--text-hi); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sb-user .info span{ font-size:9.5px; color:var(--cyan-300); letter-spacing:.05em; text-transform:uppercase; font-weight:700; }
    .btn-logout{
        display:flex; align-items:center; justify-content:center; gap:6px;
        width:100%; padding:9px; border-radius:11px;
        background:var(--glass-2); border:1px solid var(--bd-2); color:var(--text-mid);
        font-family:inherit; font-size:11.5px; font-weight:700; cursor:pointer;
        transition:all .2s;
    }
    .btn-logout:hover{ background:rgba(239,68,68,.12); color:#fca5a5; border-color:rgba(239,68,68,.35); }

    .sb-overlay{ position:fixed; inset:0; background:rgba(2,6,14,.7); backdrop-filter:blur(4px); z-index:80; opacity:0; pointer-events:none; transition:opacity .25s; }
    body.sidebar-open .sb-overlay{ opacity:1; pointer-events:all; }

    /* ===== MAIN AREA ===== */
    .main-area{ display:flex; flex-direction:column; min-width:0; }

    /* TOPBAR */
    .topbar{
        position:sticky; top:0; z-index:50;
        background:rgba(11,18,32,.78); backdrop-filter:blur(14px);
        border-bottom:1px solid var(--bd-1);
        padding:14px 24px;
        display:flex; align-items:center; justify-content:space-between; gap:14px;
        flex-wrap:wrap;
    }
    .topbar-left{ display:flex; align-items:center; gap:14px; min-width:0; flex:1; }
    .menu-toggle{
        display:none; width:38px; height:38px; border-radius:10px;
        background:var(--glass-2); border:1px solid var(--bd-2); color:var(--text-hi);
        cursor:pointer; font-size:14px;
    }
    .page-title-wrap h2{
        font-family:'Plus Jakarta Sans',sans-serif; font-size:18px; font-weight:800;
        color:var(--text-hi); letter-spacing:-.01em; line-height:1.1;
        display:flex; align-items:center; gap:9px;
    }
    .page-title-wrap h2 i{ color:var(--cyan-400); font-size:15px; }
    .page-title-wrap p{ font-size:11px; color:var(--text-mid); margin-top:3px; }

    .topbar-right{ display:flex; align-items:center; gap:8px; flex-shrink:0; }
    .tb-pill{ display:inline-flex; align-items:center; gap:7px; padding:7px 13px; border:1px solid var(--bd-2); border-radius:999px; background:var(--glass-2); font-size:11px; font-weight:700; color:var(--text); }
    .tb-pill .live-dot{ width:7px; height:7px; border-radius:50%; background:var(--success); box-shadow:0 0 8px var(--success); animation:livePulse 2.4s ease-in-out infinite; }
    @keyframes livePulse{ 0%,100%{opacity:1} 50%{opacity:.55} }

    /* CONTENT */
    .content-wrap{ padding:24px; max-width:1500px; margin:0 auto; width:100%; }
    .page-section{ animation:pageIn .35s cubic-bezier(.16,1,.3,1) both; }
    @keyframes pageIn{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }

    /* ===== GLASS CARD ===== */
    .glass-card{
        background:linear-gradient(180deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,.02) 100%);
        border:1px solid var(--bd-1); border-radius:20px;
        position:relative; overflow:hidden;
        box-shadow:0 30px 60px -30px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.04);
        backdrop-filter:blur(12px);
    }
    .glass-card::before{ content:''; position:absolute; inset:0; border-radius:inherit; background:linear-gradient(180deg, rgba(255,255,255,.04) 0%, transparent 30%); pointer-events:none; }
    .card-head{ padding:16px 22px; border-bottom:1px solid var(--bd-1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; position:relative; }
    .card-head h3{ font-size:14px; font-weight:800; color:var(--text-hi); display:flex; align-items:center; gap:9px; letter-spacing:-.01em; }
    .card-head h3 .ti{ width:30px; height:30px; border-radius:9px; display:grid; place-items:center; background:rgba(34,211,238,.12); border:1px solid var(--bd-cyan); color:var(--cyan-400); font-size:13px; }
    .card-body{ padding:20px 22px; position:relative; }
    .card-sub{ font-size:11px; color:var(--text-mid); margin-top:4px; }

    /* ===== STATS ===== */
    .stats-row{ display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:18px; }
    .stat-card{
        position:relative; padding:16px 16px; border-radius:18px;
        background:var(--glass-2); border:1px solid var(--bd-2); overflow:hidden;
        transition:all .25s ease; cursor:pointer;
    }
    .stat-card::before{ content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:linear-gradient(180deg, var(--cyan-400), var(--royal-500)); opacity:.85; }
    .stat-card.c-gold::before{ background:linear-gradient(180deg, var(--gold-400), var(--gold-600)); }
    .stat-card.c-green::before{ background:linear-gradient(180deg, #34D399, var(--success)); }
    .stat-card.c-purple::before{ background:linear-gradient(180deg, #C084FC, var(--purple)); }
    .stat-card.c-red::before{ background:linear-gradient(180deg, #FCA5A5, var(--danger)); }
    .stat-card.c-teal::before{ background:linear-gradient(180deg, #5EEAD4, #14B8A6); }
    .stat-card:hover{ transform:translateY(-3px); border-color:var(--bd-3); background:var(--glass-3); }
    .stat-icon{ width:32px; height:32px; border-radius:10px; display:grid; place-items:center; font-size:13px; margin-bottom:12px; }
    .stat-icon.blue{ background:rgba(34,211,238,.12); color:var(--cyan-400); border:1px solid var(--bd-cyan); }
    .stat-icon.green{ background:rgba(16,185,129,.12); color:#34D399; border:1px solid rgba(16,185,129,.25); }
    .stat-icon.purple{ background:rgba(168,85,247,.12); color:#C084FC; border:1px solid rgba(168,85,247,.25); }
    .stat-icon.red{ background:rgba(239,68,68,.12); color:#FCA5A5; border:1px solid rgba(239,68,68,.25); }
    .stat-icon.amber{ background:rgba(245,158,11,.12); color:var(--gold-400); border:1px solid var(--bd-gold); }
    .stat-icon.teal{ background:rgba(20,184,166,.12); color:#5EEAD4; border:1px solid rgba(20,184,166,.25); }
    .stat-num{ font-size:24px; font-weight:900; line-height:1; color:var(--text-hi); margin-bottom:4px; letter-spacing:-.02em; }
    .stat-lbl{ font-size:9.5px; font-weight:700; color:var(--text-mid); text-transform:uppercase; letter-spacing:.12em; }

    /* ===== CHARTS ===== */
    .charts-row{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:18px; }
    .chart-box{ position:relative; height:260px; }
    .chart-box canvas{ width:100%!important; height:100%!important; }

    /* ===== FILTER BAR ===== */
    .filter-bar{ display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; align-items:center; }
    .filter-bar .search-box{ flex:1; min-width:180px; position:relative; }
    .filter-bar .search-box input{
        width:100%; padding:10px 12px 10px 36px;
        border:1px solid var(--bd-2); border-radius:11px;
        font-family:inherit; font-size:12px; outline:none;
        background:var(--glass-2); color:var(--text-hi); transition:all .2s;
    }
    .filter-bar .search-box input::placeholder{ color:var(--text-faint); }
    .filter-bar .search-box input:focus{ border-color:var(--cyan-400); background:var(--glass-3); box-shadow:0 0 0 3px rgba(34,211,238,.1); }
    .filter-bar .search-box i{ position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--text-low); font-size:12px; }
    .filter-select{
        padding:10px 32px 10px 12px; border:1px solid var(--bd-2); border-radius:11px;
        font-family:inherit; font-size:12px; outline:none;
        background:var(--glass-2); color:var(--text); cursor:pointer; min-width:140px;
        appearance:none;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right 12px center;
    }
    .filter-select:focus{ border-color:var(--cyan-400); background-color:var(--glass-3); }
    .filter-select option{ background:#111E36; color:#F8FAFC; }

    /* ===== DATA TABLE ===== */
    .table-wrap{ overflow-x:auto; max-height:560px; overflow-y:auto; border:1px solid var(--bd-2); border-radius:14px; background:var(--glass-1); }
    .table-wrap::-webkit-scrollbar{ width:6px; height:6px; }
    .table-wrap::-webkit-scrollbar-thumb{ background:var(--glass-strong); border-radius:10px; }
    .data-table{ width:100%; border-collapse:collapse; font-size:12px; min-width:1050px; }
    .data-table th{
        background:rgba(11,18,32,.95); padding:11px 12px; text-align:left;
        font-size:10px; font-weight:800; color:var(--cyan-300); text-transform:uppercase; letter-spacing:.5px;
        border-bottom:1px solid var(--bd-2); position:sticky; top:0; z-index:2;
    }
    .data-table td{ padding:11px 12px; border-bottom:1px solid var(--bd-1); vertical-align:middle; color:var(--text); }
    .data-table tr:hover td{ background:var(--glass-2); }
    .role-badge,.status-badge{ padding:3px 8px; border-radius:6px; font-size:9px; font-weight:800; letter-spacing:.3px; white-space:nowrap; display:inline-block; }
    .role-admin{ background:rgba(34,211,238,.15); color:var(--cyan-300); border:1px solid var(--bd-cyan); }
    .role-juri{ background:rgba(16,185,129,.15); color:#6EE7B7; border:1px solid rgba(16,185,129,.3); }
    .role-grand{ background:rgba(168,85,247,.15); color:#D8B4FE; border:1px solid rgba(168,85,247,.3); }
    .role-user{ background:var(--glass-3); color:var(--text-mid); border:1px solid var(--bd-2); }
    .s-dinilai{ background:rgba(16,185,129,.15); color:#6EE7B7; border:1px solid rgba(16,185,129,.3); }
    .s-grand{ background:rgba(168,85,247,.15); color:#D8B4FE; border:1px solid rgba(168,85,247,.3); }
    .s-belum{ background:rgba(245,158,11,.15); color:var(--gold-300); border:1px solid var(--bd-gold); }
    .total-val{ font-size:14px; font-weight:900; color:var(--cyan-300); }
    .total-val.zero{ color:var(--text-low); font-size:12px; font-weight:600; }
    .juri-info{ font-size:11px; line-height:1.5; color:var(--text); }
    .juri-info .j-name{ font-weight:700; color:var(--cyan-300); }
    .juri-info .g-name{ color:#D8B4FE; font-weight:700; font-size:10px; }

    /* ===== BUTTONS ===== */
    .btn-xs{ padding:5px 9px; border:none; border-radius:7px; font-size:10px; font-weight:800; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:4px; transition:all .15s; }
    .btn-xs.blue{ background:rgba(34,211,238,.12); color:var(--cyan-300); border:1px solid var(--bd-cyan); }
    .btn-xs.blue:hover{ background:var(--cyan-500); color:#fff; }
    .btn-xs.green{ background:rgba(16,185,129,.12); color:#6EE7B7; border:1px solid rgba(16,185,129,.3); }
    .btn-xs.green:hover{ background:var(--success); color:#fff; }
    .btn-xs.red{ background:rgba(239,68,68,.12); color:#FCA5A5; border:1px solid rgba(239,68,68,.3); }
    .btn-xs.red:hover{ background:var(--danger); color:#fff; }
    .btn-xs.purple{ background:rgba(168,85,247,.12); color:#D8B4FE; border:1px solid rgba(168,85,247,.3); }
    .btn-xs.purple:hover{ background:var(--purple); color:#fff; }
    .btn-xs.gold{ background:rgba(245,158,11,.12); color:var(--gold-300); border:1px solid var(--bd-gold); }
    .btn-xs.gold:hover{ background:var(--gold-500); color:#fff; }

    .btn-primary{
        padding:11px 20px;
        background:linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%);
        color:#fff; border:none; border-radius:12px;
        font-size:12px; font-weight:800; cursor:pointer;
        display:inline-flex; align-items:center; gap:7px; font-family:inherit;
        transition:all .2s; letter-spacing:.02em;
        box-shadow:0 6px 16px -6px rgba(6,182,212,.5), inset 0 1px 0 rgba(255,255,255,.18);
    }
    .btn-primary:hover{ transform:translateY(-1px); box-shadow:0 10px 24px -8px rgba(6,182,212,.6), inset 0 1px 0 rgba(255,255,255,.18); }
    .btn-primary:disabled{ opacity:.55; cursor:not-allowed; transform:none; }
    .btn-cancel{
        padding:11px 18px; background:var(--glass-2); color:var(--text); border:1px solid var(--bd-2); border-radius:12px;
        font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; transition:all .2s;
    }
    .btn-cancel:hover{ background:var(--glass-3); border-color:var(--bd-3); }

    /* ===== USER LIST ===== */
    .user-panel-head{ display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px; }
    .user-list{ max-height:520px; overflow-y:auto; display:flex; flex-direction:column; gap:8px; padding-right:4px; }
    .user-list::-webkit-scrollbar{ width:5px; }
    .user-list::-webkit-scrollbar-thumb{ background:var(--glass-strong); border-radius:10px; }
    .user-card{ display:flex; flex-direction:column; padding:12px; border:1px solid var(--bd-2); border-radius:13px; transition:all .2s; background:var(--glass-2); }
    .user-card:hover{ border-color:var(--bd-cyan); background:var(--glass-3); }
    .user-card-top{ display:flex; align-items:center; gap:10px; min-width:0; }
    .user-avatar{ width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; color:#fff; flex-shrink:0; box-shadow:inset 0 1px 0 rgba(255,255,255,.2); }
    .user-card-body{ flex:1; min-width:0; }
    .user-card-body h4{ font-size:12.5px; font-weight:700; color:var(--text-hi); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .user-card-body span{ font-size:10px; color:var(--text-mid); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .user-card-bottom{ display:flex; justify-content:flex-end; align-items:center; gap:4px; margin-top:10px; padding-left:46px; flex-wrap:wrap; }

    /* ===== FORM ===== */
    .form-group{ margin-bottom:14px; }
    .form-label{ display:block; font-size:10px; font-weight:800; color:var(--text-mid); text-transform:uppercase; letter-spacing:.14em; margin-bottom:6px; }
    .form-control{
        width:100%; padding:11px 13px; border:1px solid var(--bd-2); border-radius:11px;
        font-family:inherit; font-size:13px; color:var(--text-hi); outline:none;
        background:var(--glass-2); transition:all .2s;
    }
    .form-control::placeholder{ color:var(--text-faint); }
    .form-control:focus{ border-color:var(--cyan-400); background:var(--glass-3); box-shadow:0 0 0 3px rgba(34,211,238,.1); }
    select.form-control{ cursor:pointer; appearance:none; padding-right:32px;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right 12px center;
    }
    select.form-control option,
    .form-input-modal option{ background:#111E36; color:#F8FAFC; }
    textarea.form-control{ resize:none; min-height:80px; }
    .input-wrapper{ position:relative; }
    .input-wrapper i.input-icon{ position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-low); pointer-events:none; z-index:1; transition:color .2s; }
    .form-input-modal{
        width:100%; padding:11px 38px 11px 40px; border:1.5px solid var(--bd-2); border-radius:11px;
        background:var(--glass-2); font-family:inherit; font-size:13px; color:var(--text-hi); outline:none; transition:all .2s;
    }
    .form-input-modal::placeholder{ color:var(--text-faint); }
    .form-input-modal:focus{ border-color:var(--cyan-400); background:var(--glass-3); box-shadow:0 0 0 3px rgba(34,211,238,.1); }
    .form-input-modal:focus ~ i.input-icon{ color:var(--cyan-400); }
    .form-input-modal.input-error{ border-color:var(--danger); box-shadow:0 0 0 3px rgba(239,68,68,.1); }
    .form-input-modal.input-success{ border-color:var(--success); box-shadow:0 0 0 3px rgba(16,185,129,.1); }
    .pwd-toggle{ position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-low); font-size:13px; padding:4px 5px; z-index:2; }
    .pwd-toggle:hover{ color:var(--cyan-400); }
    .pwd-error-msg{ font-size:11px; color:#FCA5A5; margin-top:4px; display:none; align-items:center; gap:4px; }
    .str-bar{ display:flex; gap:3px; margin-top:6px; }
    .str-seg{ flex:1; height:3px; border-radius:3px; background:var(--glass-strong); transition:background .3s; }
    .str-seg.w{ background:var(--danger); }
    .str-seg.m{ background:var(--warning); }
    .str-seg.s{ background:var(--success); }
    .str-text{ font-size:10px; font-weight:800; margin-top:4px; text-transform:uppercase; letter-spacing:.5px; }
    .str-text.w{ color:#FCA5A5; }
    .str-text.m{ color:var(--gold-300); }
    .str-text.s{ color:#6EE7B7; }
    .match-ind{ font-size:11px; font-weight:600; margin-top:4px; display:none; align-items:center; gap:4px; }
    .match-ind.ok{ color:#6EE7B7; display:flex; }
    .match-ind.no{ color:#FCA5A5; display:flex; }

    /* TOGGLE GROUP */
    .toggle-group{ display:flex; background:var(--glass-2); border-radius:12px; padding:4px; border:1px solid var(--bd-2); }
    .toggle-option{ flex:1; text-align:center; }
    .toggle-option input{ display:none; }
    .toggle-option label{ display:block; padding:9px; border-radius:10px; font-size:12px; font-weight:700; color:var(--text-mid); cursor:pointer; transition:all .3s; }
    .toggle-option input:checked + label{ background:linear-gradient(135deg, var(--royal-600), var(--cyan-500)); color:#fff; box-shadow:0 4px 12px -4px rgba(6,182,212,.5); }

    /* SEARCHABLE DROPDOWN */
    .search-dropdown{ position:relative; }
    .dropdown-list{ position:absolute; top:100%; left:0; right:0; background:var(--ocean-800); border:1px solid var(--bd-2); border-radius:11px; margin-top:5px; max-height:220px; overflow-y:auto; display:none; z-index:100; box-shadow:0 10px 25px rgba(0,0,0,.4); }
    .dropdown-list::-webkit-scrollbar{ width:5px; }
    .dropdown-list::-webkit-scrollbar-thumb{ background:var(--glass-strong); border-radius:10px; }
    .dropdown-list.show{ display:block; }
    .dropdown-item{ padding:10px 14px; cursor:pointer; font-size:13px; font-weight:600; color:var(--text); display:flex; align-items:center; gap:10px; transition:background .15s; border-bottom:1px solid var(--bd-1); }
    .dropdown-item:last-child{ border-bottom:none; }
    .dropdown-item:hover{ background:var(--glass-3); }
    .dropdown-item .di-avatar{ width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; color:#fff; flex-shrink:0; }
    .dropdown-item .di-info{ flex:1; min-width:0; }
    .dropdown-item .di-name{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:var(--text-hi); }
    .dropdown-item .di-email{ font-size:10px; color:var(--text-mid); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .dropdown-item .di-role{ font-size:9px; font-weight:800; padding:2px 6px; border-radius:5px; flex-shrink:0; }
    .dropdown-empty{ padding:24px; text-align:center; font-size:12px; color:var(--text-low); }

    /* ===== MODALS ===== */
    .modal-bg{ position:fixed; inset:0; background:rgba(2,6,14,.92); z-index:99; display:none; place-items:center; padding:16px; }
    .modal-bg.show{ display:grid; }
    .modal-box{
        background:linear-gradient(180deg, var(--ocean-800) 0%, var(--ocean-900) 100%);
        border:1px solid var(--bd-2);
        border-radius:22px; width:100%; max-width:var(--mw,500px); max-height:88vh;
        overflow:hidden; box-shadow:0 25px 60px -15px rgba(0,0,0,.7), inset 0 1px 0 rgba(255,255,255,.06);
        display:grid; grid-template-rows:auto 1fr auto;
    }
    .modal-head{
        padding:16px 22px; border-bottom:1px solid var(--bd-1);
        display:flex; justify-content:space-between; align-items:center;
        background:linear-gradient(135deg, rgba(34,211,238,.06), rgba(37,99,235,.04));
    }
    .modal-head h3{ font-size:14px; font-weight:800; color:var(--text-hi); display:flex; align-items:center; gap:8px; }
    .modal-head h3 i{ color:var(--cyan-400); }
    .modal-close{ background:var(--glass-2); border:1px solid var(--bd-2); color:var(--text-mid); width:32px; height:32px; border-radius:10px; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; }
    .modal-close:hover{ background:rgba(239,68,68,.15); color:#FCA5A5; border-color:rgba(239,68,68,.3); }
    .modal-body{ padding:22px; overflow-y:auto; color:var(--text); }
    .modal-body::-webkit-scrollbar{ width:6px; }
    .modal-body::-webkit-scrollbar-thumb{ background:var(--glass-strong); border-radius:10px; }
    .modal-foot{ padding:14px 22px; border-top:1px solid var(--bd-1); display:flex; justify-content:flex-end; gap:9px; background:var(--ocean-900); }

    /* ===== POPUP ===== */
    .popup-overlay{ position:fixed; inset:0; background:rgba(2,6,14,.92); z-index:99999; display:flex; align-items:center; justify-content:center; opacity:0; pointer-events:none; transition:opacity .25s; padding:16px; }
    .popup-overlay.show{ opacity:1; pointer-events:all; }
    .popup-card{
        background:linear-gradient(180deg, var(--ocean-800), var(--ocean-900));
        border:1px solid var(--bd-2);
        border-radius:22px; padding:42px 32px 32px; text-align:center; max-width:400px; width:100%;
        box-shadow:0 25px 60px -15px rgba(0,0,0,.7), inset 0 1px 0 rgba(255,255,255,.06);
        transform:scale(.85) translateY(20px); transition:transform .35s cubic-bezier(.16,1,.3,1);
    }
    .popup-overlay.show .popup-card{ transform:scale(1) translateY(0); }
    .popup-icon{ width:78px; height:78px; border-radius:22px; display:flex; align-items:center; justify-content:center; margin:0 auto 22px; }
    .popup-icon i{ font-size:32px; color:#fff; }
    .popup-icon.success{ background:linear-gradient(135deg, var(--success), #059669); box-shadow:0 12px 30px -8px rgba(16,185,129,.5); }
    .popup-icon.danger{ background:linear-gradient(135deg, var(--danger), #DC2626); box-shadow:0 12px 30px -8px rgba(239,68,68,.5); }
    .popup-icon.warning{ background:linear-gradient(135deg, var(--gold-500), var(--gold-700)); box-shadow:0 12px 30px -8px rgba(245,158,11,.5); }
    .popup-icon.info{ background:linear-gradient(135deg, var(--royal-600), var(--cyan-500)); box-shadow:0 12px 30px -8px rgba(6,182,212,.5); }
    .popup-title{ font-size:19px; font-weight:800; color:var(--text-hi); margin-bottom:8px; }
    .popup-desc{ font-size:13px; color:var(--text-mid); line-height:1.6; margin-bottom:22px; }
    .popup-btn{ display:inline-flex; align-items:center; gap:8px; padding:11px 26px; border:none; border-radius:13px; font-family:inherit; font-size:13px; font-weight:800; cursor:pointer; transition:all .25s; color:#fff; }
    .popup-btn:hover{ transform:translateY(-1px); }
    .popup-btn.success{ background:linear-gradient(135deg, var(--success), #059669); }
    .popup-btn.danger{ background:linear-gradient(135deg, var(--danger), #DC2626); }
    .popup-btn.warning{ background:linear-gradient(135deg, var(--gold-500), var(--gold-700)); }
    .popup-btn.info{ background:linear-gradient(135deg, var(--royal-600), var(--cyan-500)); }
    .popup-btn.cancel{ background:var(--glass-2); color:var(--text); border:1px solid var(--bd-2); }
    .popup-btn-row{ display:flex; gap:9px; justify-content:center; flex-wrap:wrap; }

    /* ===== STAT DETAIL POPUP ===== */
    .stat-detail-popup{ background:linear-gradient(180deg, var(--ocean-800), var(--ocean-900)); border:1px solid var(--bd-2); border-radius:22px; width:100%; max-width:760px; max-height:84vh; overflow:hidden; display:grid; grid-template-rows:auto 1fr; box-shadow:0 25px 60px -15px rgba(0,0,0,.7); transform:scale(.9); transition:transform .3s; }
    .popup-overlay.show .stat-detail-popup{ transform:scale(1); }
    .stat-detail-head{ padding:16px 22px; background:linear-gradient(135deg, rgba(34,211,238,.06), rgba(37,99,235,.04)); border-bottom:1px solid var(--bd-1); display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .stat-detail-body{ overflow-y:auto; padding:16px 22px; color:var(--text); }
    .stat-detail-body::-webkit-scrollbar{ width:5px; }
    .stat-detail-body::-webkit-scrollbar-thumb{ background:var(--glass-strong); border-radius:10px; }
    .sd-table-wrap{ overflow-x:auto; width:100%; }
    .sd-table{ width:100%; border-collapse:collapse; font-size:12px; }
    .sd-table thead{ position:sticky; top:0; z-index:2; }
    .sd-table th{ padding:10px 14px; text-align:left; font-size:10px; font-weight:800; color:var(--cyan-300); text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid var(--bd-2); background:var(--ocean-850); white-space:nowrap; }
    .sd-table th.num{ text-align:center; width:44px; }
    .sd-table th.right{ text-align:right; }
    .sd-table td{ padding:10px 14px; border-bottom:1px solid var(--bd-1); vertical-align:middle; }
    .sd-table tbody tr:hover td{ background:var(--glass-2); }
    .td-num{ color:var(--text-low); font-weight:700; font-size:11px; text-align:center; }
    .td-name{ font-weight:700; color:var(--text-hi); }
    .td-val{ font-weight:800; text-align:right; color:var(--text); }
    .td-val.primary{ color:var(--cyan-300); }
    .td-val.purple{ color:#D8B4FE; }
    .td-val.success{ color:#6EE7B7; }
    .td-val.amber{ color:var(--gold-300); }
    .sd-badge{ display:inline-flex; align-items:center; padding:3px 10px; border-radius:6px; font-size:10px; font-weight:800; }
    .sd-badge.blue{ background:rgba(34,211,238,.15); color:var(--cyan-300); border:1px solid var(--bd-cyan); }
    .sd-badge.green{ background:rgba(16,185,129,.15); color:#6EE7B7; border:1px solid rgba(16,185,129,.3); }
    .sd-badge.purple{ background:rgba(168,85,247,.15); color:#D8B4FE; border:1px solid rgba(168,85,247,.3); }
    .sd-badge.amber{ background:rgba(245,158,11,.15); color:var(--gold-300); border:1px solid var(--bd-gold); }
    .sd-empty{ text-align:center; padding:40px 20px; color:var(--text-low); }
    .sd-empty i{ font-size:28px; margin-bottom:8px; display:block; opacity:.4; }

    /* ===== EMPTY STATE ===== */
    .empty-state{ text-align:center; padding:38px 20px; color:var(--text-low); }
    .empty-state i{ font-size:30px; margin-bottom:8px; display:block; opacity:.4; }
    .empty-state p{ font-size:12px; }

    /* ===== EXPORT DROPDOWN ===== */
    .export-wrap{ position:relative; }
    .export-btn{
        padding:8px 14px; border-radius:11px; border:1px solid rgba(16,185,129,.35);
        background:rgba(16,185,129,.12); font-size:11.5px; font-weight:700; cursor:pointer;
        color:#6EE7B7; display:inline-flex; align-items:center; gap:7px; font-family:inherit; transition:all .2s;
    }
    .export-btn:hover{ background:var(--success); color:#fff; border-color:var(--success); transform:translateY(-1px); }
    .export-dd{ position:absolute; top:calc(100% + 6px); right:0; background:var(--ocean-800); border:1px solid var(--bd-2); border-radius:13px; box-shadow:0 12px 32px rgba(0,0,0,.5); min-width:250px; z-index:200; display:none; overflow:hidden; }
    .export-dd.show{ display:block; }
    .export-dd-item{ padding:10px 16px; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:8px; transition:background .12s; font-weight:600; color:var(--text); }
    .export-dd-item:hover{ background:var(--glass-3); color:var(--cyan-300); }
    .export-dd-item i{ width:16px; text-align:center; }
    .export-dd-sep{ height:1px; background:var(--bd-1); margin:4px 0; }

    /* ===== DARK INPUT AREA (UNDIAN) ===== */
    .dark-input-area .form-control{ background:rgba(0,0,0,.4); color:#fff; border-color:rgba(255,255,255,.12); font-weight:700; }
    .dark-input-area .form-control:focus{ border-color:var(--cyan-400); background:rgba(0,0,0,.5); }

    /* ===== JURI ACCORDION ===== */
    .detail-banner{ background:linear-gradient(135deg, rgba(34,211,238,.10), rgba(37,99,235,.06)); border:1px solid var(--bd-cyan); border-radius:12px; padding:14px 16px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; }
    .detail-banner h4{ font-size:14px; font-weight:800; color:var(--cyan-200); }
    .detail-banner .meta{ font-size:11px; color:var(--cyan-300); margin-top:4px; display:flex; gap:12px; flex-wrap:wrap; }
    .detail-banner .meta span{ display:flex; align-items:center; gap:4px; }
    .detail-total-chip{ background:linear-gradient(135deg, var(--royal-600), var(--cyan-500)); color:#fff; padding:6px 14px; border-radius:10px; font-size:13px; font-weight:800; white-space:nowrap; box-shadow:0 4px 12px -4px rgba(6,182,212,.5); }
    .detail-juri-accordion{ border:1px solid var(--bd-2); border-radius:12px; overflow:hidden; margin-bottom:10px; background:var(--glass-1); }
    .detail-juri-toggle{ display:flex; align-items:center; justify-content:space-between; padding:12px 16px; cursor:pointer; transition:background .2s; user-select:none; }
    .detail-juri-toggle:hover{ background:var(--glass-2); }
    .detail-juri-toggle.open{ background:rgba(34,211,238,.08); border-bottom:1px solid var(--bd-cyan); }
    .detail-juri-toggle .dj-name{ font-size:13px; font-weight:700; color:var(--text-hi); display:flex; align-items:center; gap:8px; }
    .detail-juri-toggle .dj-total{ font-size:14px; font-weight:900; color:var(--cyan-300); }
    .detail-juri-toggle .dj-arrow{ font-size:12px; color:var(--text-mid); transition:transform .2s; }
    .detail-juri-toggle.open .dj-arrow{ transform:rotate(180deg); }
    .detail-juri-scores{ display:none; }
    .detail-juri-scores.open{ display:block; }
    .detail-kat-mini-admin{ background:var(--glass-2); padding:8px 16px; font-size:10px; font-weight:800; color:var(--text-mid); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--bd-1); display:flex; justify-content:space-between; align-items:center; }
    .detail-kat-mini-admin span{ color:var(--cyan-300); font-weight:900; font-size:11px; }
    .detail-field-row-admin{ display:flex; align-items:center; justify-content:space-between; padding:8px 16px; border-bottom:1px solid var(--bd-1); gap:12px; }
    .detail-field-row-admin:last-child{ border-bottom:none; }
    .detail-field-admin-name{ font-size:12px; font-weight:700; color:var(--text-hi); }
    .detail-field-admin-meta{ font-size:10px; color:var(--text-mid); margin-top:2px; }
    .score-chip-admin{ padding:5px 16px; border-radius:7px; font-size:13px; font-weight:800; min-width:48px; text-align:center; }
    .score-chip-admin.filled{ background:rgba(34,211,238,.15); color:var(--cyan-300); border:1px solid var(--bd-cyan); }
    .score-chip-admin.empty{ background:var(--glass-2); color:var(--text-low); font-size:11px; font-weight:600; }

    /* ===== GLOBAL LOADER ===== */
    .global-loader{ position:fixed; inset:0; background:rgba(2,6,14,.55); backdrop-filter:blur(4px); z-index:99998; display:none; align-items:center; justify-content:center; }
    .global-loader.show{ display:flex; }
    .global-loader-card{
        background:linear-gradient(180deg, var(--ocean-800), var(--ocean-900));
        border:1px solid var(--bd-cyan); border-radius:16px;
        padding:24px 32px; display:flex; align-items:center; gap:16px;
        box-shadow:0 20px 50px -10px rgba(0,0,0,.6);
    }
    .global-loader-card i{ font-size:22px; color:var(--cyan-400); }
    .global-loader-card span{ font-size:13px; font-weight:700; color:var(--text-hi); }

    /* ===== UTILITY ===== */
    .txt-cyan{ color:var(--cyan-300)!important; }
    .txt-gold{ color:var(--gold-300)!important; }
    .txt-success{ color:#6EE7B7!important; }

    /* ===== RESPONSIVE ===== */
    @media (max-width:1280px){
        .stats-row{ grid-template-columns:repeat(3,1fr); }
        .charts-row{ grid-template-columns:1fr 1fr; }
    }
    @media (max-width:1024px){
        .charts-row{ grid-template-columns:1fr; }
    }
    @media (max-width:900px){
        .admin-shell{ grid-template-columns:1fr; }
        .sidebar{
            position:fixed; top:0; left:0; width:260px; height:100vh;
            transform:translateX(-100%); transition:transform .3s cubic-bezier(.16,1,.3,1);
            box-shadow:0 0 40px rgba(0,0,0,.5);
        }
        body.sidebar-open .sidebar{ transform:translateX(0); }
        .menu-toggle{ display:flex; align-items:center; justify-content:center; }
        .topbar{ padding:12px 16px; }
        .content-wrap{ padding:16px; }
        .stats-row{ grid-template-columns:1fr 1fr; gap:10px; }
        .filter-bar .search-box{ flex-basis:100%; min-width:0; }
        .filter-select{ flex:1; min-width:0; }
    }
    @media (max-width:520px){
        .stats-row{ grid-template-columns:1fr 1fr; }
        .stat-num{ font-size:20px; }
        .page-title-wrap h2{ font-size:15px; }
        .page-title-wrap p{ display:none; }
        .tb-pill span:not(.live-dot){ display:none; }
        .modal-body{ padding:16px; }
        .modal-head{ padding:14px 16px; }
        .modal-foot{ padding:12px 16px; }
        .popup-card{ padding:32px 22px 24px; }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce){
        *, *::before, *::after { animation-duration:.01ms!important; transition-duration:.1s!important; }
    }
        /* ═══ PERFORMANCE: Hentikan render gradient body saat modal/popup tampil ═══ */
    body:has(.modal-bg.show)::before,
    body:has(.popup-overlay.show)::before{
        display:none;
    }

    /* Hindari shadow berat saat mengetik di modal */
    .modal-bg.show .form-input-modal:focus,
    .modal-bg.show .form-control:focus{
        box-shadow:0 0 0 2px rgba(34,211,238,.18) !important;
    }

    /* Optimasi animasi popup */
    .popup-card,
    .modal-box{ will-change:transform,opacity; }

    /* ═══ SCROLLBAR DI DALAM MODAL USER DETAIL ═══ */
    #userDetailBody ::-webkit-scrollbar{
        width:7px;
        height:7px;
    }
    #userDetailBody ::-webkit-scrollbar-track{
        background:rgba(255,255,255,.02);
        border-radius:10px;
    }
    #userDetailBody ::-webkit-scrollbar-thumb{
        background:var(--glass-strong);
        border-radius:10px;
        border:1px solid rgba(255,255,255,.04);
        transition:background .2s;
    }
    #userDetailBody ::-webkit-scrollbar-thumb:hover{
        background:var(--bd-cyan);
    }
    #userDetailBody ::-webkit-scrollbar-corner{
        background:transparent;
    }
    /* Firefox */
    #userDetailBody *{
        scrollbar-width:thin;
        scrollbar-color:var(--glass-strong) transparent;
    }
    /* ===== FIX: Alignment JUMLAH PESERTA di MVP Peserta Table ===== */
    #mvpPesertaBody td.jp-cell {
        text-align: center;
        vertical-align: middle;
    }
    #mvpPesertaBody .jp-num {
        font-size: 16px;
        font-weight: 900;
        color: var(--cyan-300);
        line-height: 1;
    }
    #mvpPesertaBody .jp-label {
        font-size: 9px;
        font-weight: 700;
        color: var(--text-mid);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 3px;
    }
    </style>
</head>

<body>

<div class="admin-shell">

    <!-- ═══════════ SIDEBAR ═══════════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sb-mark"><i class="fas fa-shield-halved"></i></div>
            <div class="sb-brand-text">
                <h1>LCI Admin</h1>
                <p>Control Panel</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sb-section-label">Utama</div>
            <a class="sidebar-item active" data-page="dashboard"><i class="fas fa-gauge-high"></i> Dashboard</a>
            <a class="sidebar-item" data-page="penilaian"><i class="fas fa-table-list"></i> Data Penilaian</a>

            <div class="sb-section-label" style="margin-top:8px;">Manajemen</div>
            <a class="sidebar-item" data-page="users"><i class="fas fa-users-gear"></i> Kelola User</a>
            <a class="sidebar-item" data-page="registrasi"><i class="fas fa-database"></i> Registrasi & Undian</a>
            <a class="sidebar-item" data-page="nominasi"><i class="fas fa-award"></i> Nominasi</a>
            <a class="sidebar-item" data-page="mvp"><i class="fas fa-star"></i> Kelola MVP</a>
            <a class="sidebar-item" data-page="ranking"><i class="fas fa-trophy"></i> Point Ranking</a>
            <a class="sidebar-item" data-page="undian"><i class="fas fa-dice"></i> Kelola Mesin Undian</a>
        </nav>

        <div class="sidebar-foot">
            <div class="sb-user">
                <div class="avatar">{{ strtoupper(mb_substr(trim($user->name),0,1)) }}</div>
                <div class="info">
                    <b>{{ $user->name }}</b>
                    <span>Administrator</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-right-from-bracket"></i> Keluar</button>
            </form>
        </div>
    </aside>
    <div class="sb-overlay" id="sidebarOverlay"></div>

    <!-- ═══════════ MAIN AREA ═══════════ -->
    <div class="main-area">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="page-title-wrap">
                    <h2 id="pageTitle"><i class="fas fa-gauge-high"></i> Dashboard</h2>
                    <p id="pageSubtitle">Ringkasan statistik & grafik kontes</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="tb-pill"><span class="live-dot"></span><span>Live</span></div>
                <div class="export-wrap">
                    <button class="export-btn" onclick="document.getElementById('exportDD').classList.toggle('show')">
                        <i class="fas fa-file-excel"></i> <span>Export</span>
                    </button>
                    <div class="export-dd" id="exportDD">
                        <div class="export-dd-item" onclick="doExport('all')"><i class="fas fa-layer-group txt-cyan"></i> Semua Data</div>
                        <div class="export-dd-sep"></div>
                        <div class="export-dd-item" onclick="doExport('daftar')"><i class="fas fa-list txt-cyan"></i> Daftar Ikan</div>
                        <div class="export-dd-item" onclick="doExport('mvp')"><i class="fas fa-star txt-gold"></i> Data Ikan MVP</div>
                        <div class="export-dd-sep"></div>
                        <div class="export-dd-item" onclick="doExport('ranking_kk')"><i class="fas fa-layer-group txt-success"></i> Ranking: Kat + Kelas</div>
                        <div class="export-dd-item" onclick="doExport('ranking_k')"><i class="fas fa-tags txt-gold"></i> Ranking: Kategori</div>
                        <div class="export-dd-item" onclick="doExport('ranking_global')"><i class="fas fa-globe" style="color:#FCA5A5"></i> Ranking: Global</div>
                        <div class="export-dd-sep"></div>
                        <div class="export-dd-item" onclick="doExport('users')"><i class="fas fa-users" style="color:#D8B4FE"></i> Detail Pengguna</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-wrap">

            <!-- ═══════════ PAGE: DASHBOARD ═══════════ -->
            <section class="page-section" data-page="dashboard">

                <!-- Stats clickable -->
                <div class="stats-row">
                    <div class="stat-card" onclick="openStatPopup('total_ikan','Total Ikan Terdaftar')">
                        <div class="stat-icon blue"><i class="fas fa-fish"></i></div>
                        <div class="stat-num" id="sTotal">0</div>
                        <div class="stat-lbl">Total Ikan</div>
                    </div>
                    <div class="stat-card c-teal" onclick="openStatPopup('total_peserta','Total Peserta')">
                        <div class="stat-icon teal"><i class="fas fa-users"></i></div>
                        <div class="stat-num" id="sPesertaUnik">0</div>
                        <div class="stat-lbl">Total Peserta</div>
                    </div>
                    <div class="stat-card c-green" onclick="openStatPopup('sudah_dinilai','Sudah Dinilai Juri')">
                        <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
                        <div class="stat-num" id="sDinilai">0</div>
                        <div class="stat-lbl">Sudah Dinilai</div>
                    </div>
                    <div class="stat-card c-purple" onclick="openStatPopup('grand_edit','Grand Juri Edit')">
                        <div class="stat-icon purple"><i class="fas fa-crown"></i></div>
                        <div class="stat-num" id="sGrand">0</div>
                        <div class="stat-lbl">Grand Edit</div>
                    </div>
                    <div class="stat-card c-red" onclick="openStatPopup('belum_dinilai','Belum Dinilai')">
                        <div class="stat-icon red"><i class="fas fa-clock"></i></div>
                        <div class="stat-num" id="sBelum">0</div>
                        <div class="stat-lbl">Belum Dinilai</div>
                    </div>
                    <div class="stat-card c-gold" onclick="openStatPopup('juri_aktif','Juri Aktif')">
                        <div class="stat-icon amber"><i class="fas fa-user-pen"></i></div>
                        <div class="stat-num" id="sJuri">0</div>
                        <div class="stat-lbl">Juri Aktif</div>
                    </div>
                </div>

                <div class="stats-row" style="grid-template-columns:1fr 1fr;">
                    <div class="stat-card c-teal" style="cursor:default;">
                        <div class="stat-icon teal"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-num" id="sAvg">0</div>
                        <div class="stat-lbl">Rata-rata Nilai</div>
                    </div>
                    <div class="stat-card c-gold" style="cursor:default;">
                        <div class="stat-icon amber"><i class="fas fa-boxes-stacked"></i></div>
                        <div class="stat-num" id="sSisaTank">0</div>
                        <div class="stat-lbl" id="sSisaTankLabel">Sisa Tank (1-1000)</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-row">
                    <div class="glass-card">
                        <div class="card-head"><h3><span class="ti"><i class="fas fa-chart-bar"></i></span>Peserta per Kategori</h3></div>
                        <div class="card-body"><div class="chart-box"><canvas id="chartKategori"></canvas></div></div>
                    </div>
                    <div class="glass-card">
                        <div class="card-head"><h3><span class="ti"><i class="fas fa-chart-pie"></i></span>Status Penilaian</h3></div>
                        <div class="card-body"><div class="chart-box"><canvas id="chartStatus"></canvas></div></div>
                    </div>
                    <div class="glass-card">
                        <div class="card-head"><h3><span class="ti"><i class="fas fa-ranking-star"></i></span>Top 10 Point</h3></div>
                        <div class="card-body"><div class="chart-box"><canvas id="chartTop"></canvas></div></div>
                    </div>
                </div>
            </section>

            <!-- ═══════════ PAGE: PENILAIAN ═══════════ -->
            <section class="page-section" data-page="penilaian" style="display:none;">
                <div class="glass-card">
                    <div class="card-head">
                        <h3><span class="ti"><i class="fas fa-table-list"></i></span>Data Penilaian Keseluruhan</h3>
                        <span style="font-size:10px;color:var(--text-low);">Semua input dari Juri & Grand Juri</span>
                    </div>
                    <div class="card-body">
                        <div class="filter-bar">
                            <div class="search-box"><i class="fas fa-search"></i><input type="text" id="filterSearch" placeholder="Cari nama peserta..."></div>
                            <select class="filter-select" id="filterKategori">
                                <option value="">Semua Kategori</option>
                                <option>Cencu</option><option>Chingwa</option><option>Freemarking</option>
                                <option>Goldenbase</option><option>Klasik</option><option>Bonsai</option><option>Jumbo</option>
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
                                        <th style="width:34px;text-align:center;padding-right:6px;">
                                            <input type="checkbox" id="checkAllRows" onchange="toggleAllRows(this)" style="cursor:pointer;width:15px;height:15px;accent-color:var(--cyan-400);vertical-align:middle;">
                                        </th>
                                        <th>#</th><th>PESERTA</th><th>KATEGORI</th><th>KELAS</th><th>TANK</th><th>ASAL / TEAM</th>
                                        <th>DINILAI OLEH</th><th>TOTAL NILAI</th><th>POINT</th><th>STATUS</th><th>AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="tBody"><tr><td colspan="12"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════ PAGE: USERS ═══════════ -->
            <section class="page-section" data-page="users" style="display:none;">
                <div class="glass-card">
                    <div class="card-head">
                        <h3><span class="ti"><i class="fas fa-users-gear"></i></span>Kelola User</h3>
                        <div style="display:flex;gap:6px;">
                            <button class="btn-xs gold" onclick="openModal('modalImport')"><i class="fas fa-file-excel"></i> Import Excel</button>
                            <button class="btn-xs blue" onclick="openModal('modalCreate')"><i class="fas fa-plus"></i> Tambah User</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="user-panel-head">
                            <span style="font-size:11px;color:var(--text-mid);font-weight:600;" id="userCount">0 user</span>
                            <div style="position:relative;flex:1;max-width:280px;">
                                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:11px;color:var(--text-low);"></i>
                                <input type="search"
                                    id="searchUser"
                                    name="user_filter_keyword_x9"
                                    placeholder="Cari user..."
                                    autocomplete="off"
                                    autocorrect="off"
                                    autocapitalize="off"
                                    spellcheck="false"
                                    data-lpignore="true"
                                    data-form-type="other"
                                    data-1p-ignore="true"
                                    readonly
                                    onfocus="this.removeAttribute('readonly');"
                                    style="width:100%;padding:8px 12px 8px 32px;border:1px solid var(--bd-2);border-radius:10px;font-family:inherit;font-size:11.5px;outline:none;background:var(--glass-2);color:var(--text-hi);">
                            </div>
                        </div>
                        <div class="user-list" id="userList">
                            <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════ PAGE: REGISTRASI & UNDIAN ═══════════ -->
            <section class="page-section" data-page="registrasi" style="display:none;">

                <!-- Rentang Global -->
                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head"><h3><span class="ti"><i class="fas fa-globe"></i></span>Rentang Nomor Undian Global</h3></div>
                    <div class="card-body">
                        <div style="font-size:11px; color:var(--text-mid); margin-bottom:14px;" id="globalRangeDesc">Memuat...</div>
                        <div id="globalRangeViewMode" style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="font-size:26px; font-weight:900; color:var(--cyan-300); letter-spacing:-.02em;" id="globalRangeDisplayText">1 – 1000</div>
                            <button type="button" onclick="toggleGlobalRangeEdit(true)" class="btn-xs blue" style="padding:8px 14px;font-size:11px;"><i class="fas fa-pen"></i> Ubah</button>
                        </div>
                        <div id="globalRangeEditMode" style="display:none;">
                            <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px;">
                                <input type="number" id="inputGlobalRangeMin" value="1" min="1" class="form-control" style="text-align:center; font-weight:700;">
                                <span style="font-weight:600; color:var(--text-mid); font-size:13px;">s/d</span>
                                <input type="number" id="inputGlobalRangeMax" value="1000" min="1" class="form-control" style="text-align:center; font-weight:700;">
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="button" onclick="toggleGlobalRangeEdit(false)" class="btn-cancel" style="flex:1;">Batal</button>
                                <button type="button" onclick="saveGlobalTankRange()" class="btn-primary" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rentang Per Kategori -->
                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head"><h3><span class="ti" style="background:rgba(245,158,11,.12);border-color:var(--bd-gold);color:var(--gold-400);"><i class="fas fa-tags"></i></span>Rentang Nomor Tank Per Kategori</h3></div>
                    <div class="card-body">
                        <div id="katLoading" style="text-align:center; padding:24px;">
                            <i class="fas fa-spinner fa-spin" style="font-size:22px; color:var(--cyan-400);"></i>
                            <div style="font-size:12px; color:var(--text-mid); margin-top:10px;">Memuat pengaturan rentang...</div>
                        </div>
                        <div id="katContent" style="display:none;">
                            <div id="katSummaryWrap" style="background:var(--glass-2); border:1px solid var(--bd-2); border-radius:12px; padding:14px 16px; margin-bottom:14px;">
                                <div style="font-size:10px; font-weight:800; color:var(--gold-300); text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;"><i class="fas fa-clipboard-list"></i> Rentang Dikonfigurasi</div>
                                <div id="katSummaryContent" style="font-size:11px; color:var(--text); line-height:1.8;"></div>
                            </div>
                            <div style="background:rgba(34,211,238,.06); border:1px solid var(--bd-cyan); border-radius:11px; padding:12px 14px; margin-bottom:14px; font-size:11px; color:var(--text); line-height:1.7;">
                                <div style="font-weight:800;color:var(--cyan-300);margin-bottom:5px;"><i class="fas fa-book-open"></i> Aturan Singkat</div>
                                Rentang tiap kategori tidak boleh menyentuh batas kategori lain. Kosongkan = pakai Rentang Global.
                            </div>
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
                                <label style="font-size:11px; font-weight:800; color:var(--gold-300); text-transform:uppercase; letter-spacing:.4px; white-space:nowrap;">Pilih Kelas</label>
                                <select id="katKelasSelect" class="form-control" style="max-width:240px; font-weight:700;" onchange="onKatKelasChange()">
                                    <option value="">-- Pilih Kelas --</option>
                                </select>
                                <div style="background:var(--glass-2); border:1px solid var(--bd-2); border-radius:10px; padding:6px 12px;">
                                    <span style="font-size:11px; color:var(--text-mid); font-weight:700;">Global: <span id="katGlobalRangeText" style="color:var(--cyan-300); font-weight:900;">—</span></span>
                                </div>
                            </div>
                            <div id="katGridWrap" style="display:none;">
                                <div id="katExistingInfo" style="display:none; background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.3); border-radius:10px; padding:10px 14px; margin-bottom:12px; font-size:11px; color:#6EE7B7;">
                                    <i class="fas fa-check-circle"></i> <span id="katExistingText"></span>
                                </div>
                                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:10px; margin-bottom:14px;" id="katGrid"></div>
                                <div id="katErrorBox" style="display:none; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:10px 14px; margin-bottom:12px; font-size:11px; color:#FCA5A5; line-height:1.7;">
                                    <div style="font-weight:800; margin-bottom:4px;"><i class="fas fa-triangle-exclamation"></i> Tidak Dapat Disimpan</div>
                                    <div id="katErrorText"></div>
                                </div>
                                <button type="button" id="btnSaveKatRange" onclick="saveKategoriRange()" class="btn-primary" style="width:100%;justify-content:center;background:linear-gradient(135deg,var(--gold-600),var(--gold-700));box-shadow:0 6px 16px -6px rgba(245,158,11,.5),inset 0 1px 0 rgba(255,255,255,.18);">
                                    <i class="fas fa-save"></i> Simpan Pengaturan Rentang
                                </button>
                            </div>
                            <div id="katEmptyState" style="text-align:center; padding:24px; color:var(--gold-300);">
                                <i class="fas fa-hand-pointer" style="font-size:20px; display:block; margin-bottom:8px; opacity:.5;"></i>
                                <span style="font-size:12px;">Pilih kelas di atas untuk mengatur rentang</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrasi & Undian -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" id="regUndianGrid">

                    <!-- Registrasi -->
                    <div class="glass-card">
                        <div class="card-head"><h3><span class="ti"><i class="fas fa-user-plus"></i></span>Registrasi Peserta & Ikan</h3></div>
                        <div class="card-body">
                            <form id="regPesertaIkanForm">
                                <div class="form-group">
                                    <label class="form-label">Nama Peserta</label>
                                    <input type="hidden" name="user_id" id="admRegUserId" required>
                                    <input type="hidden" name="nama_peserta" id="admRegNama" required>
                                    <div class="search-dropdown" id="admRegDropdown">
                                        <div class="input-wrapper">
                                            <input type="text" id="admRegSearch" class="form-input-modal" placeholder="Ketik nama untuk mencari..." autocomplete="off">
                                            <i class="fas fa-search input-icon"></i>
                                            <i class="fas fa-xmark" id="admRegClear" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-low);font-size:13px;display:none;padding:4px;z-index:2;"></i>
                                        </div>
                                        <div class="dropdown-list" id="admRegList"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Jenis Keanggotaan</label>
                                    <div class="toggle-group" id="admRegToggleGroup">
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="admPerorangan" value="perorangan" checked>
                                            <label for="admPerorangan"><i class="fas fa-user"></i> Perorangan</label>
                                        </div>
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="admTeam" value="team">
                                            <label for="admTeam"><i class="fas fa-users"></i> Team / Club</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" id="admLabelDetail">Kota Asal</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="detail_anggota" id="admInputDetail" class="form-input-modal" placeholder="Contoh: Jakarta" required>
                                        <i class="fas fa-city input-icon" id="admIconDetail"></i>
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label class="form-label">Kategori</label>
                                        <select name="kategori" class="form-control" required>
                                            <option value="" disabled selected>Pilih Kategori</option>
                                            <option>Cencu</option><option>Chingwa</option><option>Freemarking</option>
                                            <option>Goldenbase</option><option>Klasik</option><option>Bonsai</option><option>Jumbo</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label class="form-label">Kelas</label>
                                        <select name="kelas" class="form-control" required>
                                            <option value="" disabled selected>Pilih Kelas</option>
                                            <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
                                        </select>
                                    </div>
                                </div>
                                <div style="display:flex; gap:9px; margin-top:16px;">
                                    <button type="button" id="btnSavePesertaOnly" onclick="submitSavePeserta()" class="btn-cancel" style="flex:1;background:rgba(20,184,166,.12);border-color:rgba(20,184,166,.3);color:#5EEAD4;font-weight:800;">
                                        <i class="fas fa-user-check"></i> SIMPAN PROFIL
                                    </button>
                                    <button type="submit" class="btn-primary" id="btnRegPesertaIkan" style="flex:1;justify-content:center;">
                                        <i class="fas fa-fish"></i> DAFTAR IKAN
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Undian Tank -->
                    <div class="glass-card" style="background:linear-gradient(180deg,#050B17 0%,#0A1426 100%);">
                        <div class="card-head"><h3><span class="ti"><i class="fas fa-dice"></i></span>Undian Nomor Tank</h3></div>
                        <div class="card-body dark-input-area" style="text-align:center;">
                            <select id="pesertaSelectOld" class="form-control" style="margin-bottom:12px;"></select>
                            <div id="tankCounter" style="font-size:11px; color:var(--text-mid); margin-bottom:10px;">Memuat...</div>
                            <div style="background:radial-gradient(ellipse at 50% 0%,rgba(34,211,238,.15),transparent 60%),linear-gradient(180deg,#030712,#0A1426);border:1px solid var(--bd-cyan);border-radius:16px;padding:24px 18px;margin-bottom:16px;box-shadow:inset 0 1px 0 rgba(255,255,255,.06),inset 0 0 40px rgba(34,211,238,.04);">
                                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;color:var(--cyan-300);font-weight:700;letter-spacing:.4em;text-transform:uppercase;margin-bottom:10px;">Nomor Aquarium</div>
                                <div style="font-family:'JetBrains Mono',monospace;font-size:72px;font-weight:900;color:var(--cyan-300);text-shadow:0 0 20px rgba(34,211,238,.6),0 0 40px rgba(34,211,238,.3);line-height:1;transition:color .3s;letter-spacing:-.04em;" id="numberDisplayOld">--</div>
                            </div>
                            <button class="btn-primary" id="btnAcakOld" style="width:100%;justify-content:center;">
                                <i class="fas fa-shuffle"></i> Acak Nomor Tank
                            </button>
                            <button type="button" onclick="openResetTankModal()" style="width:100%; margin-top:10px; padding:10px; border-radius:11px; border:1px solid rgba(239,68,68,.3); background:rgba(239,68,68,.1); color:#FCA5A5; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:6px;">
                                <i class="fas fa-rotate-left"></i> Reset Semua Nomor Tank
                            </button>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ═══════════ PAGE: MVP ═══════════ -->
            <section class="page-section" data-page="mvp" style="display:none;">

                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head">
                        <h3><span class="ti" style="background:rgba(245,158,11,.12);border-color:var(--bd-gold);color:var(--gold-400);"><i class="fas fa-star"></i></span>Status Pendaftaran MVP</h3>
                        <button class="btn-primary" id="btnToggleMvp" onclick="toggleMvpRegistration()" style="padding:8px 16px; font-size:11px;"><i class="fas fa-spinner fa-spin"></i></button>
                    </div>
                    <div class="card-body">
                        <div style="font-size:13px; color:var(--text);" id="mvpStatusText">Memuat status...</div>
                        <div style="background:rgba(245,158,11,.08);border:1px solid var(--bd-gold);border-radius:11px;padding:10px 14px;margin-top:12px;display:flex;gap:8px;align-items:flex-start;">
                            <i class="fas fa-circle-info" style="color:var(--gold-400);margin-top:2px;"></i>
                            <span style="font-size:11px;color:var(--gold-300);line-height:1.5;">Menghapus ikan dari MVP tidak menghapus data ikan. Peserta dapat mendaftar ulang jika MVP masih dibuka.</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head">
                        <h3><span class="ti" style="background:rgba(168,85,247,.12);border-color:rgba(168,85,247,.3);color:#D8B4FE;"><i class="fas fa-user-lock"></i></span>Peserta Sudah Kirim MVP</h3>
                        <span style="font-size:10px;color:var(--text-low);font-weight:700;" id="mvpPesertaCount">0 peserta</span>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-wrap" style="max-height:260px;border:none;border-radius:0;">
                            <table class="data-table" style="min-width:auto;">
                                <thead><tr><th style="width:30px;">#</th><th>ASAL / TEAM</th><th style="text-align:center;width:130px;">JUMLAH PESERTA</th><th style="text-align:center;">IKAN MVP</th><th style="text-align:center;">AKSI</th></tr></thead>
                                <tbody id="mvpPesertaBody"><tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Data Ikan MVP (Rank Point & Bonus — sama seperti Grand Juri) -->
                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head">
                        <h3><span class="ti" style="background:rgba(168,85,247,.12);border-color:rgba(168,85,247,.3);color:#D8B4FE;"><i class="fas fa-star"></i></span>Data Ikan MVP</h3>
                        <span style="font-size:10px;color:var(--text-low);font-weight:700;">Rank Point & Bonus per Kota/Team</span>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-wrap" style="max-height:480px;border:none;border-radius:0;">
                            <table class="data-table" style="min-width:auto;">
                                <thead><tr><th>KOTA / TEAM / CLUB</th><th style="text-align:center;">JUMLAH PESERTA</th><th style="text-align:center;">JUMLAH IKAN MVP</th><th style="text-align:center;">STATUS</th><th style="text-align:center;">AKSI</th></tr></thead>
                                <tbody id="mvpDataBody"><tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-head"><h3><span class="ti" style="background:rgba(245,158,11,.12);border-color:var(--bd-gold);color:var(--gold-400);"><i class="fas fa-list"></i></span>Daftar Ikan Terdaftar MVP</h3></div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-wrap" style="max-height:480px;border:none;border-radius:0;">
                            <table class="data-table" style="min-width:auto;">
                                <thead><tr><th style="width:30px;">#</th><th>PESERTA</th><th>ASAL / TEAM</th><th>KATEGORI</th><th>KELAS</th><th>NO. TANK</th><th style="text-align:center;">AKSI</th></tr></thead>
                                <tbody id="mvpTableBody"><tr><td colspan="7"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ═══════════ PAGE: POINT RANKING ═══════════ -->
            <section class="page-section" data-page="ranking" style="display:none;">
                <div class="glass-card">
                    <div class="card-head">
                        <div>
                            <h3><span class="ti" style="background:rgba(245,158,11,.12);border-color:var(--bd-gold);color:var(--gold-400);"><i class="fas fa-trophy"></i></span>Sistem Point Ranking</h3>
                            <div class="card-sub">Peringkat berdasarkan nilai point (hanya ikan yang sudah DIKUNCI Grand Juri)</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button id="admBtnScopeKelas" onclick="setAdminPointScope('per_kategori_kelas')" style="font-size:11px;padding:7px 14px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-weight:700;display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:#fff;transition:all .2s;">
                                    <i class="fas fa-layer-group" style="font-size:10px;"></i> Per Kat + Kelas
                                </button>
                                <button id="admBtnScopeKat" onclick="setAdminPointScope('per_kategori')" style="font-size:11px;padding:7px 14px;border-radius:8px;border:1px solid rgba(245,158,11,.25);cursor:pointer;font-family:inherit;font-weight:700;display:inline-flex;align-items:center;gap:5px;background:var(--warning-lt);color:var(--gold-300);transition:all .2s;">
                                    <i class="fas fa-tags" style="font-size:10px;"></i> Per Kategori
                                </button>
                                <button id="admBtnScopeGlobal" onclick="setAdminPointScope('global')" style="font-size:11px;padding:7px 14px;border-radius:8px;border:1px solid rgba(245,158,11,.25);cursor:pointer;font-family:inherit;font-weight:700;display:inline-flex;align-items:center;gap:5px;background:var(--warning-lt);color:var(--gold-300);transition:all .2s;">
                                    <i class="fas fa-globe" style="font-size:10px;"></i> Rank Global
                                </button>
                            </div>
                            <select class="filter-select" id="admPointFilterKategori" onchange="loadAdminPointRanking()">
                                <option value="">Semua Kategori</option>
                                <option value="Cencu">Cencu</option>
                                <option value="Chingwa">Chingwa</option>
                                <option value="Freemarking">Freemarking</option>
                                <option value="Goldenbase">Goldenbase</option>
                                <option value="Klasik">Klasik</option>
                                <option value="Bonsai">Bonsai</option>
                                <option value="Jumbo">Jumbo</option>
                            </select>
                            <select class="filter-select" id="admPointFilterKelas" onchange="loadAdminPointRanking()" style="min-width:120px;">
                                <option value="">Semua Kelas</option>
                                <option value="A">Kelas A</option>
                                <option value="B">Kelas B</option>
                                <option value="C">Kelas C</option>
                                <option value="D">Kelas D</option>
                                <option value="E">Kelas E</option>
                            </select>
                        </div>
                        <div id="adminPointRankingContent">
                            <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat ranking...</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════ PAGE: NOMINASI ═══════════ -->
            <section class="page-section" data-page="nominasi" style="display:none;">

                {{-- Section A: Pilih Tank --}}
                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head">
                        <h3><span class="ti"><i class="fas fa-hand-pointer"></i></span>Pilih Tank untuk Dinominasikan</h3>
                        <button class="btn-xs blue" onclick="loadAdminNomTanks()"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                    <div class="card-body">
                        <div class="filter-bar">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="adminNomSearch" placeholder="Cari no tank / kategori...">
                            </div>
                            <select class="filter-select" id="adminNomFilterKat" onchange="renderAdminNomGrid()">
                                <option value="">Semua Kategori</option>
                                <option>Cencu</option><option>Chingwa</option><option>Freemarking</option>
                                <option>Goldenbase</option><option>Klasik</option><option>Bonsai</option><option>Jumbo</option>
                            </select>
                            <select class="filter-select" id="adminNomFilterKelas" onchange="renderAdminNomGrid()" style="min-width:120px;">
                                <option value="">Semua Kelas</option>
                                <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
                            </select>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
                            <span style="font-size:11px;color:var(--text-mid);font-weight:600;" id="adminNomCounter">0 tank tersedia</span>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-size:11px;font-weight:700;color:var(--cyan-300);">Terpilih: <span id="adminNomSelectedCount" style="font-weight:900;">0</span></span>
                                <button class="btn-primary" id="adminNomSubmitBtn" onclick="adminNomSubmit()" disabled style="padding:9px 18px;font-size:11.5px;opacity:.5;cursor:not-allowed;">
                                    <i class="fas fa-paper-plane"></i> Submit Nominasi
                                </button>
                            </div>
                        </div>
                        <div id="adminNomGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;max-height:380px;overflow-y:auto;padding:4px;">
                            <div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-spinner fa-spin"></i><p>Memuat tank...</p></div>
                        </div>
                    </div>
                </div>

                {{-- Section B: Pending Review --}}
                <div class="glass-card" style="margin-bottom:16px;">
                    <div class="card-head">
                        <h3><span class="ti" style="background:rgba(245,158,11,.12);border-color:var(--bd-gold);color:var(--gold-400);"><i class="fas fa-hourglass-half"></i></span>Review Nominasi Pending</h3>
                        <span style="font-size:10px;font-weight:700;color:var(--gold-300);" id="adminPendingCount">0 pending</span>
                    </div>
                    <div class="card-body" id="adminPendingBody">
                        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
                    </div>
                </div>

                {{-- Section C: Late Ikan --}}
                <div class="glass-card" style="margin-bottom:16px;" id="adminLateSection">
                    <div class="card-head">
                        <h3><span class="ti" style="background:rgba(245,158,11,.12);border-color:var(--bd-gold);color:var(--gold-400);"><i class="fas fa-clock"></i></span>Ikan Terlambat Daftar</h3>
                        <span style="font-size:10px;font-weight:700;color:var(--gold-300);" id="adminLateCount">—</span>
                    </div>
                    <div class="card-body" id="adminLateBody">
                        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
                    </div>
                </div>

                {{-- Section D: History --}}
                <div class="glass-card">
                    <div class="card-head">
                        <h3><span class="ti"><i class="fas fa-clock-rotate-left"></i></span>Riwayat Review</h3>
                        <div style="display:flex;gap:5px;background:var(--glass-2);padding:3px;border-radius:9px;border:1px solid var(--bd-2);">
                            <button onclick="adminSwitchHistTab('approved')" id="adminHistTabApp" class="btn-xs" style="padding:6px 12px;background:rgba(16,185,129,.20);color:#6EE7B7;border:none;">
                                <i class="fas fa-check-circle"></i> Diterima (<span id="adminHistAppCount">0</span>)
                            </button>
                            <button onclick="adminSwitchHistTab('rejected')" id="adminHistTabRej" class="btn-xs" style="padding:6px 12px;background:transparent;color:var(--text-mid);border:none;">
                                <i class="fas fa-times-circle"></i> Ditolak (<span id="adminHistRejCount">0</span>)
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="adminHistBody">
                        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
                    </div>
                </div>

            </section>

            <!-- ═══════════ PAGE: UNDIAN ═══════════ -->
            <section class="page-section" data-page="undian" style="display:none;">
                <div class="glass-card">
                    <div class="card-head">
                        <h3><span class="ti"><i class="fas fa-dice"></i></span>Status Mesin Undian Tank</h3>
                        <button class="btn-primary" id="btnToggleUndian" onclick="toggleUndianRegistration()" style="padding:8px 16px; font-size:11px;"><i class="fas fa-spinner fa-spin"></i></button>
                    </div>
                    <div class="card-body">
                        <div style="font-size:13px; color:var(--text);" id="undianStatusText">Memuat status...</div>
                        <div style="background:rgba(34,211,238,.08);border:1px solid var(--bd-cyan);border-radius:11px;padding:10px 14px;margin-top:12px;display:flex;gap:8px;align-items:flex-start;">
                            <i class="fas fa-circle-info" style="color:var(--cyan-400);margin-top:2px;"></i>
                            <span style="font-size:11px;color:var(--cyan-300);line-height:1.5;">Jika dikunci, peserta tetap bisa mendaftarkan ikan, namun tidak bisa mengacak nomor tank. Gunakan ini untuk mengatur jadwal pengundian.</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<!-- ═══════════ MODAL: DETAIL NILAI ═══════════ -->
<div class="modal-bg" id="modalDetail" style="--mw:760px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-eye"></i> Detail Nilai Peserta</h3><button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body" id="detailBody"></div>
        <div class="modal-foot"><button class="btn-cancel" onclick="closeModal('modalDetail')">Tutup</button></div>
    </div>
</div>

<!-- ═══════════ MODAL: TAMBAH USER ═══════════ -->
<div class="modal-bg" id="modalCreate" style="--mw:460px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-user-plus"></i> Tambah User Baru</h3><button class="modal-close" onclick="closeModal('modalCreate')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body">
            <form id="formCreateUser">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-wrapper"><input type="text" name="name" id="createName" class="form-input-modal" placeholder="Nama lengkap" required><i class="fas fa-user input-icon"></i></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrapper"><input type="email" name="email" id="createEmail" class="form-input-modal" placeholder="nama@email.com" required><i class="fas fa-envelope input-icon"></i></div>
                    <div class="pwd-error-msg" id="createEmailErr"><i class="fas fa-circle-exclamation"></i><span></span></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="createPwd" class="form-input-modal" placeholder="Min.8: besar, kecil, angka, simbol" required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="pwd-toggle" id="toggleCreatePwd"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="pwd-error-msg" id="createPwdErr"><i class="fas fa-circle-exclamation"></i><span>Wajib: min.8 karakter, huruf besar, kecil, angka, simbol</span></div>
                    <div class="str-bar" id="createStrBar" style="display:none;">
                        <div class="str-seg" id="cSeg1"></div><div class="str-seg" id="cSeg2"></div><div class="str-seg" id="cSeg3"></div><div class="str-seg" id="cSeg4"></div><div class="str-seg" id="cSeg5"></div>
                    </div>
                    <div class="str-text" id="createStrText" style="display:none;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="createPwdConf" class="form-input-modal" placeholder="Ulangi password" required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="pwd-toggle" id="toggleCreatePwdConf"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="match-ind no" id="createMatchNo"><i class="fas fa-circle-exclamation"></i><span>Password tidak cocok</span></div>
                    <div class="match-ind ok" id="createMatchOk"><i class="fas fa-circle-check"></i><span>Password cocok</span></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Role</label>
                    <select name="role" id="createRole" class="form-control" required>
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

<!-- ═══════════ MODAL: PASSWORD ═══════════ -->
<div class="modal-bg" id="modalPwd" style="--mw:440px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-key"></i> Password User</h3><button class="modal-close" onclick="closeModal('modalPwd')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body">
            <p style="font-size:12px;margin-bottom:14px;color:var(--text);">User: <b style="color:var(--text-hi);" id="pwdTarget"></b></p>
            <input type="hidden" id="pwdUserId">
            <div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);border-radius:12px;padding:14px;margin-bottom:18px;">
                <div style="font-size:10px;font-weight:800;color:#6EE7B7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-eye"></i> Password Saat Ini</span>
                    <button type="button" id="togglePwdView" onclick="toggleCurrentPwd()" style="background:none;border:none;cursor:pointer;color:#6EE7B7;font-size:13px;padding:2px 4px;display:flex;align-items:center;gap:4px;">
                        <span id="togglePwdLabel" style="font-size:9px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;">TUTUP</span>
                        <i id="togglePwdIcon" class="fas fa-eye-slash"></i>
                    </button>
                </div>
                <div id="pwdCurrentDisplay" style="font-size:20px;font-weight:900;color:#6EE7B7;letter-spacing:1px;word-break:break-all;user-select:none;">••••••••</div>
                <div id="pwdNoData" style="display:none;font-size:11px;color:#6EE7B7;margin-top:4px;"><i class="fas fa-info-circle"></i> Belum pernah diset oleh admin</div>
            </div>
            <div style="font-size:10px;font-weight:800;color:var(--text-mid);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;"><i class="fas fa-pen"></i> Ganti Password Baru</div>
            <div class="form-group" style="position:relative;margin-bottom:0;">
                <input type="password" id="pwdNew" class="form-control" placeholder="Min. 8 karakter" style="padding-right:42px;font-weight:700;">
                <button type="button" class="pwd-toggle" id="toggleNewPwd" onclick="toggleNewPwdInput()"><i class="fas fa-eye"></i></button>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalPwd')">Tutup</button>
            <button class="btn-primary" onclick="submitPwd()"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: BONUS POINT ═══════════ -->
<div class="modal-bg" id="modalBonus" style="--mw:520px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-trophy" style="color:var(--gold-400);"></i> Kelola Bonus Point</h3><button class="modal-close" onclick="closeModal('modalBonus')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body" id="bonusModalBody"></div>
        <div class="modal-foot"><button class="btn-cancel" onclick="closeModal('modalBonus')">Tutup</button></div>
    </div>
</div>

<!-- ═══════════ MODAL: RESET TANK ═══════════ -->
<div class="modal-bg" id="modalResetTank" style="--mw:460px;">
    <div class="modal-box">
        <div class="modal-head"><h3><i class="fas fa-rotate-left" style="color:#FCA5A5;"></i> Reset Nomor Tank</h3><button class="modal-close" onclick="closeModal('modalResetTank')"><i class="fas fa-xmark"></i></button></div>
        <div class="modal-body">
            <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:14px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start;">
                <i class="fas fa-triangle-exclamation" style="color:#FCA5A5;margin-top:2px;font-size:16px;"></i>
                <div style="font-size:12px;color:#FCA5A5;line-height:1.5;"><b>Peringatan!</b> Akan menghapus <b>SEMUA</b> nomor tank. Peserta harus mengundi ulang.</div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Alasan Reset *</label>
                <textarea id="resetReason" class="form-control" rows="3" placeholder="Contoh: persiapan event baru..."></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalResetTank')">Batal</button>
            <button class="btn-primary" id="btnSubmitReset" style="background:linear-gradient(135deg,var(--danger),#DC2626);box-shadow:0 6px 16px -6px rgba(239,68,68,.5),inset 0 1px 0 rgba(255,255,255,.18);" onclick="submitResetTank()"><i class="fas fa-rotate-left"></i> Ya, Reset</button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: USER PESERTA DETAIL ═══════════ -->
<div class="modal-bg" id="modalUserDetail" style="--mw:820px;">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-id-card" style="color:#D8B4FE;"></i> Detail Peserta &amp; Riwayat Identitas</h3>
            <button class="modal-close" onclick="closeModal('modalUserDetail')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="userDetailBody">
            <div class="empty-state" style="padding:30px;"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalUserDetail')">Tutup</button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: IMPORT EXCEL ═══════════ -->
<div class="modal-bg" id="modalImport" style="--mw:600px;">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-file-excel" style="color:var(--gold-400);"></i> Import Data dari Excel</h3>
            <button class="modal-close" onclick="closeModal('modalImport')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="importModalBody">
            <div style="background:rgba(245,158,11,.08);border:1px solid var(--bd-gold);border-radius:11px;padding:12px 14px;margin-bottom:16px;font-size:11px;color:var(--gold-300);line-height:1.6;">
                <div style="font-weight:800;margin-bottom:6px;"><i class="fas fa-circle-info"></i> Format Excel</div>
                <div>Header wajib: <b>Email, Nama Peserta, Jenis Keanggotaan, Detail Anggota, Kategori, Kelas</b></div>
                <div style="margin-top:4px;">Kategori valid: Cencu, Chingwa, Freemarking, Goldenbase, Klasik, Bonsai, Jumbo</div>
                <div>Kelas: A–E (kosongkan untuk Bonsai/Jumbo)</div>
                <div style="margin-top:6px;"><a href="/api/admin/import-template" style="color:var(--cyan-300);font-weight:700;text-decoration:underline;" onclick="event.stopPropagation();"><i class="fas fa-download"></i> Download Template</a></div>
            </div>
            <div class="form-group">
                <label class="form-label">File Excel (.xlsx / .xls / .csv)</label>
                <div id="importDropZone" style="border:2px dashed var(--bd-2);border-radius:12px;padding:28px 16px;text-align:center;cursor:pointer;transition:all .2s;background:var(--glass-1);" onclick="document.getElementById('importFileInput').click()" ondragover="event.preventDefault();this.style.borderColor='var(--cyan-400)';this.style.background='rgba(34,211,238,.06)'" ondragleave="this.style.borderColor='var(--bd-2)';this.style.background='var(--glass-1)'" ondrop="event.preventDefault();handleImportDrop(event)">
                    <i class="fas fa-cloud-arrow-up" style="font-size:28px;color:var(--text-low);display:block;margin-bottom:8px;"></i>
                    <div style="font-size:12px;color:var(--text-mid);font-weight:600;" id="importFileLabel">Klik atau seret file ke sini</div>
                    <input type="file" id="importFileInput" accept=".xlsx,.xls,.csv" style="display:none;" onchange="handleImportFileSelect(this)">
                </div>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;color:var(--text);font-weight:600;">
                    <input type="checkbox" id="importAutoCreate" onchange="toggleImportAutoCreate()" style="accent-color:var(--cyan-400);width:16px;height:16px;cursor:pointer;">
                    Buat akun user baru jika email belum terdaftar
                </label>
            </div>
            <div class="form-group" id="importPasswordWrap" style="display:none;">
                <label class="form-label">Password Default untuk User Baru</label>
                <input type="text" id="importDefaultPassword" class="form-control" value="LCI_2024!" placeholder="Min. 8 karakter">
                <div style="font-size:10px;color:var(--text-low);margin-top:4px;">Wajib: huruf besar, kecil, angka, simbol</div>
            </div>
            <div id="importResultBox" style="display:none;"></div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalImport')">Tutup</button>
            <button class="btn-primary" id="btnSubmitImport" onclick="submitImport()" style="background:linear-gradient(135deg,var(--gold-600),var(--gold-700));box-shadow:0 6px 16px -6px rgba(245,158,11,.5),inset 0 1px 0 rgba(255,255,255,.18);">
                <i class="fas fa-upload"></i> Mulai Import
            </button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: EDIT NILAI ADMIN ═══════════ -->
<div class="modal-bg" id="modalEditAdmin" style="--mw:860px;">
    <div class="modal-box">
        <div class="modal-head" style="background:linear-gradient(135deg,rgba(168,85,247,.08),rgba(168,85,247,.04));">
            <h3><i class="fas fa-pen-to-square" style="color:#D8B4FE;"></i> Edit Nilai — Admin</h3>
            <button class="modal-close" onclick="closeModal('modalEditAdmin')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:linear-gradient(135deg,rgba(168,85,247,.10),rgba(168,85,247,.05));border:1px solid rgba(168,85,247,.25);border-radius:12px;padding:14px 16px;margin-bottom:12px;font-size:13px;color:var(--text);" id="editAdminInfo"></div>
            <div style="font-size:11px;color:var(--gold-300);background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);padding:8px 12px;border-radius:10px;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-circle-info" style="color:var(--gold-400);"></i>
                Nilai dari juri sudah terisi. <strong>Ubah hanya komponen yang ingin diperbarui</strong> lalu simpan.
            </div>
            <div style="display:grid;grid-template-columns:170px 1fr;gap:16px;">
                <div style="display:flex;flex-direction:column;gap:5px;" id="editAdminKatList"></div>
                <div id="editAdminFormArea"></div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalEditAdmin')"><i class="fas fa-xmark"></i> Batal</button>
            <button class="btn-primary" id="btnSaveEditAdmin" style="background:linear-gradient(135deg,#7c3aed,#A855F7);box-shadow:0 4px 14px -4px rgba(168,85,247,.5),inset 0 1px 0 rgba(255,255,255,.2);"><i class="fas fa-save"></i> SIMPAN PERUBAHAN</button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: DEFECT ADMIN ═══════════ -->
<div class="modal-bg" id="modalDefectAdmin" style="--mw:450px;">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="defectAdminTitle">Pilih Defect</h3>
            <button class="modal-close" onclick="closeDefectAdmin()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="defectAdminBody"></div>
    </div>
</div>

<!-- ═══════════ POPUP SUCCESS ═══════════ -->
<div class="popup-overlay" id="popupSuccess">
    <div class="popup-card">
        <div class="popup-icon success"><i class="fas fa-check"></i></div>
        <h2 class="popup-title" id="popupSuccessTitle">Berhasil!</h2>
        <p class="popup-desc" id="popupSuccessDesc">Aksi berhasil dilakukan.</p>
        <button class="popup-btn success" onclick="hidePopup('popupSuccess')"><i class="fas fa-circle-check"></i> OK</button>
    </div>
</div>

<!-- ═══════════ POPUP ERROR ═══════════ -->
<div class="popup-overlay" id="popupError">
    <div class="popup-card">
        <div class="popup-icon danger"><i class="fas fa-triangle-exclamation"></i></div>
        <h2 class="popup-title" id="popupErrorTitle">Gagal!</h2>
        <p class="popup-desc" id="popupErrorDesc">Terjadi kesalahan.</p>
        <button class="popup-btn danger" onclick="hidePopup('popupError')"><i class="fas fa-rotate-right"></i> Tutup</button>
    </div>
</div>

<!-- ═══════════ POPUP CONFIRM ═══════════ -->
<div class="popup-overlay" id="popupConfirm">
    <div class="popup-card">
        <div class="popup-icon warning"><i class="fas fa-question"></i></div>
        <h2 class="popup-title" id="popupConfirmTitle">Konfirmasi</h2>
        <p class="popup-desc" id="popupConfirmDesc">Apakah Anda yakin?</p>
        <div class="popup-btn-row">
            <button class="popup-btn cancel" onclick="cancelConfirm()"><i class="fas fa-xmark"></i> Batal</button>
            <button class="popup-btn warning" id="popupConfirmBtn" onclick="executeConfirm()"><i class="fas fa-check"></i> Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<!-- ═══════════ POPUP INFO ═══════════ -->
<div class="popup-overlay" id="popupInfo">
    <div class="popup-card">
        <div class="popup-icon info"><i class="fas fa-circle-info"></i></div>
        <h2 class="popup-title" id="popupInfoTitle">Informasi</h2>
        <p class="popup-desc" id="popupInfoDesc">Detail informasi.</p>
        <button class="popup-btn info" onclick="hidePopup('popupInfo')"><i class="fas fa-check"></i> OK</button>
    </div>
</div>

<!-- ═══════════ POPUP STAT DETAIL ═══════════ -->
<div class="popup-overlay" id="popupStatDetail">
    <div class="stat-detail-popup">
        <div class="stat-detail-head">
            <div style="display:flex;align-items:center;gap:14px;min-width:0;">
                <div id="statDetailIcon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 18px -6px rgba(6,182,212,.5);">
                    <i class="fas fa-fish" style="color:#fff;font-size:17px;" id="statDetailIconI"></i>
                </div>
                <div style="min-width:0;">
                    <h3 id="statDetailTitle" style="font-size:15px;font-weight:800;color:var(--text-hi);line-height:1.3;">Detail</h3>
                    <p id="statDetailCount" style="font-size:11px;color:var(--text-mid);margin:2px 0 0;">Memuat...</p>
                </div>
            </div>
            <button onclick="hidePopup('popupStatDetail')" class="modal-close"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="stat-detail-body" id="statDetailBody">
            <div class="empty-state" style="padding:30px;"><i class="fas fa-spinner fa-spin" style="font-size:18px;"></i></div>
        </div>
    </div>
</div>

<!-- ═══════════ GLOBAL LOADER ═══════════ -->
<div class="global-loader" id="globalLoader">
    <div class="global-loader-card">
        <i class="fas fa-spinner fa-spin"></i>
        <span class="loader-text">Memproses...</span>
    </div>
</div>

<script>
    window.ADMIN_ROUTES = {
        listUsers: '{{ route("api.list.users") }}',
        pesertaBelumTank: '{{ route("api.peserta.belum.tank") }}',
        acakTankAdmin: '{{ route("api.acak.tank.admin") }}',
        updatePassword: '{{ route("api.update.password") }}'
    };
    window.MY_AUTH_ID = {{ auth()->id() }};
</script>
<script src="{{ asset('js/admin-dashboard.js') }}?v={{ time() }}"></script>
</body>
</html>