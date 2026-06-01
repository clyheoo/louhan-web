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
        .data-table{width:100%;border-collapse:collapse;font-size:12px;min-width:1050px;}
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
        .user-card{display:flex;flex-direction:column;align-items:stretch;padding:12px;border:1px solid var(--border);border-radius:10px;transition:all .2s;background:#fff;}
        .user-card:hover{border-color:#c7d2fe;box-shadow:0 2px 8px rgba(37,99,235,.06);}
        .user-card-top{display:flex;align-items:center;gap:10px;min-width:0;}
        .user-avatar{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;}
        .user-card-body{flex:1;min-width:0;}
        .user-card-body h4{font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .user-card-body span{font-size:10px;color:var(--light);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .user-card-bottom{display:flex;justify-content:flex-end;align-items:center;gap:4px;margin-top:8px;padding-left:44px;flex-wrap:wrap;}

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
        @media(max-width:768px){.stats-row{grid-template-columns:1fr 1fr;}.charts-row{grid-template-columns:1fr;}.old-grid{grid-template-columns:1fr;}.nav-actions{gap:5px;}.nav-btn span{display:none;}
        }
            /* ═══════════════════════════════════════════════
        POPUP ANIMASI (SAMA DENGAN REGISTER)
        ═══════════════════════════════════════════════ */
        .popup-overlay {
            position:fixed;inset:0;background:rgba(15,23,42,.4);
            backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
            z-index:99999;display:flex;align-items:center;justify-content:center;
            opacity:0;pointer-events:none;transition:opacity .4s ease;
        }
        .popup-overlay.show{opacity:1;pointer-events:all;}
        .popup-card{
            background:var(--card);border-radius:24px;padding:48px 40px 36px;
            text-align:center;max-width:380px;width:90%;
            box-shadow:0 25px 60px rgba(0,0,0,.15);
            transform:scale(.8) translateY(20px);
            transition:transform .4s cubic-bezier(.16,1,.3,1);
        }
        .popup-overlay.show .popup-card{transform:scale(1) translateY(0);}
        .popup-icon{
            width:80px;height:80px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 24px;
        }
        .popup-icon i{font-size:36px;color:#fff;animation:iconPop .5s .3s cubic-bezier(.16,1,.3,1) both;}
        @keyframes iconPop{0%{transform:scale(0) rotate(-45deg);opacity:0}100%{transform:scale(1) rotate(0);opacity:1}}
        .popup-icon.success{background:linear-gradient(135deg,#22c55e,#16a34a);box-shadow:0 8px 24px rgba(34,197,94,.3);}
        .popup-icon.danger{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 8px 24px rgba(239,68,68,.3);}
        .popup-icon.warning{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 8px 24px rgba(245,158,11,.3);}
        .popup-icon.info{background:linear-gradient(135deg,var(--primary),var(--primary-dk));box-shadow:0 8px 24px rgba(37,99,235,.3);}
        .popup-title{font-size:20px;font-weight:800;color:var(--text);margin-bottom:8px;}
        .popup-desc{font-size:13.5px;color:var(--muted);line-height:1.6;margin-bottom:24px;}
        .popup-btn{
            display:inline-flex;align-items:center;gap:8px;
            padding:12px 28px;border:none;border-radius:14px;
            font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;
            cursor:pointer;transition:all .3s ease;text-decoration:none;color:#fff;
        }
        .popup-btn:hover{transform:translateY(-1px);}
        .popup-btn.success{background:linear-gradient(135deg,#22c55e,#16a34a);box-shadow:0 4px 12px rgba(34,197,94,.25);}
        .popup-btn.danger{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 4px 12px rgba(239,68,68,.25);}
        .popup-btn.warning{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 12px rgba(245,158,11,.25);}
        .popup-btn.info{background:linear-gradient(135deg,var(--primary),var(--primary-dk));box-shadow:0 4px 12px rgba(37,99,235,.25);}
        .popup-btn.cancel{background:var(--bg);color:var(--muted);border:1px solid var(--border);box-shadow:none;}
        .popup-btn.cancel:hover{background:var(--border);color:var(--text);}
        .popup-btn-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
        .popup-detail{font-size:12px;color:var(--muted);text-align:left;background:var(--bg);padding:12px;border-radius:10px;margin-bottom:16px;line-height:1.6;max-height:120px;overflow-y:auto;
        }
            /* ═══════════════════════════════════════════════
        PASSWORD VALIDATION (MODAL CREATE USER)
        ═══════════════════════════════════════════════ */
        .input-wrapper{position:relative;}
        .input-wrapper i.input-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--light);transition:color .3s;pointer-events:none;z-index:1;}
        .form-input-modal{width:100%;padding:10px 40px 10px 38px;border:1.5px solid var(--border);border-radius:10px;background:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:var(--text);outline:none;transition:all .3s;}
        .form-input-modal::placeholder{color:var(--light);}
        .form-input-modal:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.1);}
        .form-input-modal:focus~i.input-icon{color:var(--primary);}
        .form-input-modal.input-error{border-color:var(--danger);box-shadow:0 0 0 3px rgba(239,68,68,.1);}
        .form-input-modal.input-error~i.input-icon{color:var(--danger);}
        .form-input-modal.input-success{border-color:var(--success);box-shadow:0 0 0 3px rgba(34,197,94,.1);}
        .form-input-modal.input-success~i.input-icon{color:var(--success);}
        .pwd-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--light);font-size:13px;padding:4px 5px;z-index:2;transition:color .2s;}
        .pwd-toggle:hover{color:var(--primary);}
        .pwd-error-msg{font-size:11px;color:var(--danger);margin-top:4px;display:none;align-items:center;gap:4px;}
        .pwd-error-msg i{font-size:10px;}
        .str-bar{display:flex;gap:3px;margin-top:6px;}
        .str-seg{flex:1;height:3px;border-radius:3px;background:var(--border);transition:background .3s;}
        .str-seg.w{background:var(--danger);}
        .str-seg.m{background:var(--warning);}
        .str-seg.s{background:var(--success);}
        .str-text{font-size:10px;font-weight:700;margin-top:3px;text-transform:uppercase;letter-spacing:.5px;transition:color .3s;}
        .str-text.w{color:var(--danger);}
        .str-text.m{color:var(--warning);}
        .str-text.s{color:var(--success);}
        .match-ind{font-size:11px;font-weight:600;margin-top:4px;display:none;align-items:center;gap:4px;}
        .match-ind.ok{color:var(--success);display:flex;}
        .match-ind.no{color:var(--danger);display:flex;
        }
        /* ── TOGGLE GROUP (dari user.blade) ── */
        .toggle-group{display:flex;background:var(--bg);border-radius:12px;padding:4px;border:1px solid var(--border);}
        .toggle-option{flex:1;text-align:center;}
        .toggle-option input{display:none;}
        .toggle-option label{display:block;padding:9px;border-radius:10px;font-size:12px;font-weight:600;color:var(--muted);cursor:pointer;transition:all .3s;}
        .toggle-option input:checked+label{background:var(--card);color:var(--primary-dk);box-shadow:0 2px 8px rgba(0,0,0,.05);
        }
        .search-dropdown{position:relative;}
        .dropdown-list{position:absolute;top:100%;left:0;right:0;background:var(--card);border:1px solid var(--border);border-radius:10px;margin-top:4px;max-height:200px;overflow-y:auto;display:none;z-index:100;box-shadow:0 10px 25px rgba(0,0,0,.1);}
        .dropdown-list::-webkit-scrollbar{width:4px;}
        .dropdown-list::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:10px;}
        .dropdown-list.show{display:block;}
        .dropdown-item{padding:10px 14px;cursor:pointer;font-size:13px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:10px;transition:background .15s;border-bottom:1px solid #f1f5f9;}
        .dropdown-item:last-child{border-bottom:none;}
        .dropdown-item:hover{background:var(--primary-lt);}
        .dropdown-item.active{background:var(--primary-lt);color:var(--primary-dk);}
        .dropdown-item .di-avatar{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
        .dropdown-item .di-info{flex:1;min-width:0;}
        .dropdown-item .di-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .dropdown-item .di-email{font-size:10px;color:var(--light);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .dropdown-item .di-role{font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;flex-shrink:0;}
        .dropdown-empty{padding:20px;text-align:center;font-size:12px;color:var(--light);
        }
        /* ═══════════════════════════════════════════════
        FORCE WHITE TEXT UNTUK AREA HITAM (UNDIAN)
        ═══════════════════════════════════════════════ */
        .dark-input-area .form-control,
        .dark-input-area input[type="number"] {
            background: rgba(0,0,0,.3) !important;
            color: #ffffff !important;
            border-color: rgba(255,255,255,.15) !important;
            font-weight: 700;
        }
        .dark-input-area input[type="number"]::placeholder {
            color: rgba(255,255,255,.4);
        }
        .dark-input-area input[type="number"]::-webkit-inner-spin-button,
        .dark-input-area input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
            filter: invert(1);
        }
        .dark-input-area .btn-acak-kecil {
            color: #ffffff !important;
            border-color: rgba(255,255,255,.25) !important;
        }
        .dark-input-area .btn-acak-kecil:hover {
            background: rgba(255,255,255,.1) !important;
        }
        .export-wrap{position:relative;}
        .export-btn{padding:7px 14px;border-radius:8px;border:1px solid #16a34a;background:#dcfce7;font-size:11px;font-weight:700;cursor:pointer;color:#16a34a;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;}
        .export-btn:hover{background:#16a34a;color:#fff;border-color:#16a34a;transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.25);}
        .export-dd{position:absolute;top:calc(100% + 6px);right:0;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.15);min-width:240px;z-index:200;display:none;overflow:hidden;}
        .export-dd.show{display:block;}
        .export-dd-item{padding:10px 16px;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background .12s;font-weight:600;color:var(--text);}
        .export-dd-item:hover{background:var(--primary-lt);color:var(--primary);}
        .export-dd-item i{width:16px;text-align:center;font-size:12px;}
        .export-dd-sep{height:1px;background:var(--border);margin:4px 0;
        }
        @media(max-width:768px){
        #kelasRangeInputs{ grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media(max-width:480px){
            #kelasRangeInputs{ grid-template-columns: 1fr !important; }
        }
        /* ── JURI ACCORDION (DETAIL MODAL ADMIN) ── */
        .detail-juri-accordion{border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:10px;}
        .detail-juri-toggle{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;cursor:pointer;transition:background .2s;user-select:none;}
        .detail-juri-toggle:hover{background:#f8fafc;}
        .detail-juri-toggle.open{background:var(--primary-lt);border-bottom:1px solid #bfdbfe;}
        .detail-juri-toggle .dj-name{font-size:13px;font-weight:700;display:flex;align-items:center;gap:8px;}
        .detail-juri-toggle .dj-total{font-size:14px;font-weight:900;color:var(--primary);}
        .detail-juri-toggle .dj-arrow{font-size:12px;color:var(--muted);transition:transform .2s;}
        .detail-juri-toggle.open .dj-arrow{transform:rotate(180deg);}
        .detail-juri-scores{display:none;}
        .detail-juri-scores.open{display:block;}
        .detail-kat-mini-admin{background:#f8fafc;padding:7px 16px;font-size:10px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;}
        .detail-kat-mini-admin span{color:var(--primary);font-weight:900;font-size:11px;}
        .detail-field-row-admin{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-bottom:1px solid #f8fafc;gap:12px;}
        .detail-field-row-admin:last-child{border-bottom:none;}
        .detail-field-row-admin:hover{background:#fafbfd;}
        .detail-field-admin-name{font-size:12px;font-weight:700;color:var(--text);}
        .detail-field-admin-meta{font-size:10px;color:var(--light);margin-top:1px;}
        .score-chip-admin{padding:5px 16px;border-radius:6px;font-size:13px;font-weight:800;min-width:48px;text-align:center;}
        .score-chip-admin.filled{background:#dbeafe;color:var(--primary);}
        .score-chip-admin.empty{background:#f1f5f9;color:var(--light);font-size:11px;font-weight:600;
        }
        .stat-detail-popup{background:#fff;border-radius:20px;width:92%;max-width:740px;max-height:82vh;overflow:hidden;display:grid;grid-template-rows:auto 1fr;box-shadow:0 25px 60px rgba(0,0,0,.15);transform:scale(.9) translateY(20px);transition:transform .4s cubic-bezier(.16,1,.3,1);}
        .popup-overlay.show .stat-detail-popup{transform:scale(1) translateY(0);}
        .stat-detail-head{padding:16px 20px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:12px;}
        .stat-detail-body{overflow-y:auto;padding:16px 20px;display:flex;justify-content:center;}
        .sd-table-wrap{overflow-x:auto;width:100%;max-width:680px;}
        .stat-detail-body::-webkit-scrollbar{width:5px;}
        .stat-detail-body::-webkit-scrollbar-track{background:transparent;}
        .stat-detail-body::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px;}
        .stat-detail-body::-webkit-scrollbar-thumb:hover{background:#94a3b8;}
        .sd-table-wrap{overflow-x:auto;}
        .sd-table{width:100%;border-collapse:collapse;font-size:12px;min-width:100%;}
        .sd-table thead{position:sticky;top:0;z-index:2;}
        .sd-table th{padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid var(--border);background:#fff;white-space:nowrap;}
        .sd-table th.num{text-align:center;width:44px;}
        .sd-table th.right{text-align:right;}
        .sd-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
        .sd-table tbody tr:hover td{background:#f8fafc;}
        .sd-table tbody tr:last-child td{border-bottom:none;}
        .td-num{color:var(--light);font-weight:700;font-size:11px;text-align:center;}
        .td-name{font-weight:700;color:var(--text);}
        .td-val{font-weight:800;text-align:right;color:var(--text);}
        .td-val.primary{color:var(--primary);}
        .td-val.purple{color:var(--purple);}
        .td-val.success{color:var(--success);}
        .td-val.danger{color:var(--danger);}
        .td-val.amber{color:var(--warning);}
        .td-val.teal{color:#14b8a6;}
        .sd-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:800;white-space:nowrap;}
        .sd-badge.blue{background:var(--primary);color:#fff;}
        .sd-badge.green{background:var(--success);color:#fff;}
        .sd-badge.purple{background:var(--purple);color:#fff;}
        .sd-badge.amber{background:var(--warning);color:#fff;}
        .sd-empty{text-align:center;padding:40px 20px;color:var(--light);}
        .sd-empty i{font-size:28px;margin-bottom:8px;display:block;opacity:.3;}
        .sd-empty p{font-size:12px;
        }
        .kat-gap-info{display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:11px;color:#78350f;line-height:1.6;margin-bottom:12px;}
        .kat-gap-info i{margin-right:4px;}
        @media(max-width:900px){#katGrid{grid-template-columns:repeat(2,1fr)!important;}}
        @media(max-width:500px){#katGrid{grid-template-columns:1fr!important;}}
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
                <div class="export-wrap">
            <button class="export-btn" onclick="document.getElementById('exportDD').classList.toggle('show')">
                <i class="fas fa-file-excel"></i> <span>Export Excel</span>
            </button>
            <div class="export-dd" id="exportDD">
                <div class="export-dd-item" onclick="doExport('all')">
                    <i class="fas fa-layer-group" style="color:var(--primary);"></i> Export Semua Data
                </div>
                <div class="export-dd-sep"></div>
                <div class="export-dd-item" onclick="doExport('daftar')">
                    <i class="fas fa-list" style="color:var(--primary);"></i> Daftar Ikan
                </div>
                <div class="export-dd-item" onclick="doExport('mvp')">
                    <i class="fas fa-star" style="color:#f59e0b;"></i> Data Ikan MVP
                </div>
                <div class="export-dd-sep"></div>
                <div class="export-dd-item" onclick="doExport('ranking_kk')">
                    <i class="fas fa-layer-group" style="color:var(--success);"></i> Ranking: Per Kat + Kelas
                </div>
                <div class="export-dd-item" onclick="doExport('ranking_k')">
                    <i class="fas fa-tags" style="color:var(--warning);"></i> Ranking: Per Kategori
                </div>
                <div class="export-dd-item" onclick="doExport('ranking_global')">
                    <i class="fas fa-globe" style="color:var(--danger);"></i> Ranking: Global
                </div>
                <div class="export-dd-sep"></div>
                <div class="export-dd-item" onclick="doExport('users')">
                    <i class="fas fa-users" style="color:var(--purple);"></i> Detail Pengguna
                </div>
            </div>
        </div>
        <div class="nav-user"><b>{{ $user->name }}</b><small>Administrator</small></div>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf<button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i></button></form>
    </div>
</nav>

<!-- ═══ MAIN CONTENT ═══ -->
<div class="admin-wrap">

    <!-- ── STATISTIK ── -->
    <div class="stats-row">
        <div class="stat-card c-blue" style="cursor:pointer;" onclick="openStatPopup('total_ikan','Total Ikan Terdaftar')"><div class="stat-icon blue"><i class="fas fa-fish"></i></div><div class="stat-num" id="sTotal">0</div><div class="stat-lbl">Total Ikan Terdaftar</div></div>
        <div class="stat-card c-teal" style="cursor:pointer;" onclick="openStatPopup('total_peserta','Total Peserta')"><div class="stat-icon teal"><i class="fas fa-users"></i></div><div class="stat-num" id="sPesertaUnik">0</div><div class="stat-lbl">Total Peserta</div></div>
        <div class="stat-card c-green" style="cursor:pointer;" onclick="openStatPopup('sudah_dinilai','Sudah Dinilai Juri')"><div class="stat-icon green"><i class="fas fa-check-double"></i></div><div class="stat-num" id="sDinilai">0</div><div class="stat-lbl">Sudah Dinilai Juri</div></div>
        <div class="stat-card c-purple" style="cursor:pointer;" onclick="openStatPopup('grand_edit','Grand Juri Edit')"><div class="stat-icon purple"><i class="fas fa-crown"></i></div><div class="stat-num" id="sGrand">0</div><div class="stat-lbl">Grand Juri Edit</div></div>
        <div class="stat-card c-red" style="cursor:pointer;" onclick="openStatPopup('belum_dinilai','Belum Dinilai')"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div class="stat-num" id="sBelum">0</div><div class="stat-lbl">Belum Dinilai</div></div>
        <div class="stat-card c-amber" style="cursor:pointer;" onclick="openStatPopup('juri_aktif','Juri Aktif')"><div class="stat-icon amber"><i class="fas fa-user-pen"></i></div><div class="stat-num" id="sJuri">0</div><div class="stat-lbl">Juri Aktif</div></div>
    </div>
    <div class="stats-row" style="grid-template-columns:1fr 1fr 1fr 1fr;">
        <div class="stat-card" style="cursor:pointer;border-color:var(--success);border-width:2px;" onclick="openModal('modalOld')">
            <div class="stat-icon green"><i class="fas fa-database"></i></div>
            <div class="stat-num" style="font-size:16px;color:var(--success);">Undian &<br>Registrasi</div>
            <div class="stat-lbl" style="color:var(--success);">Klik untuk buka modul</div>
        </div>
        <div class="stat-card" style="cursor:pointer;border-color:#f59e0b;border-width:2px;" onclick="openModal('modalMvp')">
            <div class="stat-icon amber"><i class="fas fa-star"></i></div>
            <div class="stat-num" style="font-size:16px;color:#d97706;">Kelola<br>MVP</div>
            <div class="stat-lbl" style="color:#d97706;">Klik untuk buka modul</div>
        </div>
        <div class="stat-card c-teal"><div class="stat-icon teal"><i class="fas fa-chart-line"></i></div><div class="stat-num" id="sAvg">0</div><div class="stat-lbl">Rata-rata Nilai</div></div>
        <div class="stat-card c-teal"><div class="stat-icon teal"><i class="fas fa-boxes-stacked"></i></div><div class="stat-num" id="sSisaTank">0</div><div class="stat-lbl" id="sSisaTankLabel">Sisa Tank (Max 100)</div></div>
    </div>

    <!-- ── GRAFIK ── -->
    <div class="charts-row">
        <div class="section-card">
            <div class="section-head"><div class="section-title"><i class="fas fa-chart-bar"></i> Peserta per Kategori</div></div>
            <div class="section-body"><div class="chart-box"><canvas id="chartKategori"></canvas></div></div>
        </div>
        <div class="section-card">
            <div class="section-head"><div class="section-title"><i class="fas fa-chart-pie"></i> Diagram Status Penilaian</div></div>
            <div class="section-body"><div class="chart-box"><canvas id="chartStatus"></canvas></div></div>
        </div>
        <div class="section-card">
            <div class="section-head"><div class="section-title"><i class="fas fa-ranking-star"></i> Top 10 Point Tertinggi</div></div>
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
                        <option>Cencu</option>
                        <option>Chginwa</option>
                        <option>Freemarking</option>
                        <option>Goldenbase</option>
                        <option>Klasik</option>
                        <option>Bonsai</option>
                        <option>Jumbo</option>
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
                            <th>#</th><th>PESERTA</th><th>KATEGORI</th><th>KELAS</th><th>TANK</th><th>ASAL / TEAM</th>
                            <th>DINILAI OLEH</th><th>TOTAL NILAI</th><th>POINT</th><th>STATUS</th><th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tBody"><tr><td colspan="10"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div></td></tr></tbody>
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
                <div style="display:flex;gap:6px;">
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:10px;color:var(--light);pointer-events:none;"></i>
                        <input type="text" id="searchUser" placeholder="Cari user..." autocomplete="off" readonly style="padding:5px 8px 5px 26px;border:1px solid var(--border);border-radius:6px;font-family:inherit;font-size:11px;outline:none;width:140px;background:var(--bg);transition:all .2s;cursor:text;" onfocus="this.removeAttribute('readonly');this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">                    </div>
                    <button class="btn-xs blue" onclick="openModal('modalCreate')"><i class="fas fa-plus"></i> Tambah User</button>
                </div>
            </div>
            <div class="user-list" id="userList">
                <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════
     SEMUA MODAL — TARUH DI LUAR main-row
     ═══════════════════════════════════════════════ -->

<!-- MODAL: DETAIL NILAI -->
    <div class="modal-bg" id="modalDetail" style="--mw:750px;">
        <div class="modal-box">
            <div class="modal-head"><h3><i class="fas fa-eye"></i> Detail Nilai Peserta</h3><button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-xmark"></i></button></div>
            <div class="modal-body" id="detailBody"></div>
            <div class="modal-foot"><button class="btn-cancel" onclick="closeModal('modalDetail')">Tutup</button></div>
        </div>
    </div>
            <!-- MODAL: TAMBAH USER -->
            <div class="modal-bg" id="modalCreate">
                <div class="modal-box" style="--mw:460px;">
                    <div class="modal-head">
                        <h3><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
                        <button class="modal-close" onclick="closeModal('modalCreate')"><i class="fas fa-xmark"></i></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCreateUser">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <div class="input-wrapper">
                                    <input type="text" name="name" id="createName" class="form-input-modal" placeholder="Masukkan nama lengkap" required>
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <div class="input-wrapper">
                                    <input type="email" name="email" id="createEmail" class="form-input-modal" placeholder="nama@email.com" required>
                                    <i class="fas fa-envelope input-icon"></i>
                                </div>
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
                                    <div class="str-seg" id="cSeg1"></div><div class="str-seg" id="cSeg2"></div>
                                    <div class="str-seg" id="cSeg3"></div><div class="str-seg" id="cSeg4"></div><div class="str-seg" id="cSeg5"></div>
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
                            <div class="form-group">
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

            <!-- MODAL: LIHAT & GANTI PASSWORD -->
            <div class="modal-bg" id="modalPwd">
                <div class="modal-box" style="--mw:420px;">
                    <div class="modal-head">
                        <h3><i class="fas fa-key"></i> Password User</h3>
                        <button class="modal-close" onclick="closeModal('modalPwd')"><i class="fas fa-xmark"></i></button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:12px;margin-bottom:14px;">User: <b id="pwdTarget"></b></p>
                        <input type="hidden" id="pwdUserId">

                        <!-- CURRENT PASSWORD -->
                        <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;border-radius:12px;padding:16px;margin-bottom:18px;">
                            <div style="font-size:10px;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
                                <span><i class="fas fa-eye"></i> Password Saat Ini</span>
                                <button type="button" id="togglePwdView" onclick="toggleCurrentPwd()" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:13px;padding:2px 4px;display:flex;align-items:center;gap:4px;">
                                    <span id="togglePwdLabel" style="font-size:9px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;">TUTUP</span>
                                    <i id="togglePwdIcon" class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                            <div id="pwdCurrentDisplay" style="font-size:20px;font-weight:900;color:#166534;letter-spacing:1px;word-break:break-all;user-select:none;">
                                ••••••••
                            </div>
                            <div id="pwdNoData" style="display:none;font-size:12px;color:#16a34a;margin-top:4px;">
                                <i class="fas fa-info-circle"></i> Belum pernah diset oleh admin
                            </div>
                        </div>

                        <!-- CHANGE PASSWORD -->
                        <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;display:flex;align-items:center;gap:5px;">
                            <i class="fas fa-pen"></i> Ganti Password Baru
                        </div>
                        <div class="form-group" style="position:relative;margin-bottom:0;">
                            <input type="password" id="pwdNew" class="form-control" placeholder="Masukkan password baru (min. 8 karakter)" style="font-size:14px;font-weight:700;letter-spacing:.5px;padding-right:42px;">
                            <button type="button" class="pwd-toggle" id="toggleNewPwd" onclick="toggleNewPwdInput()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button class="btn-cancel" onclick="closeModal('modalPwd')">Tutup</button>
                        <button class="btn-primary" onclick="submitPwd()"><i class="fas fa-save"></i> Simpan Password Baru</button>
                    </div>
                </div>
            </div>

            <!-- MODAL: MODUL LAMA -->
            <div class="modal-bg" id="modalOld" style="--mw:1100px;">
                <div class="modal-box">
                    <div class="modal-head">
                        <h3><i class="fas fa-box-archive"></i> Modul Registrasi Ikan & Undian Tank</h3>
                        <button class="modal-close" onclick="closeModal('modalOld')"><i class="fas fa-xmark"></i></button>
                    </div>
                    <div class="modal-body" style="max-height:80vh; overflow-y:auto;">
                        <!-- ★ PENGATURAN RENTANG GLOBAL (FALLBACK) -->
            <div style="background:linear-gradient(135deg,#f8fafc,#f1f5f9); border:1px solid var(--border); border-radius:16px; margin-bottom:20px;">
                <div style="padding:14px 20px; border-bottom:1px solid var(--border);">
                    <div style="font-size:14px; font-weight:800; color:var(--text); display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-globe"></i> Rentang Nomor Undian Global
                    </div>
                       <div style="font-size:11px; color:var(--muted); margin-top:4px;" id="globalRangeDesc">Memuat...</div>
                </div>
                <div style="padding:18px 20px;">
                    <div id="globalRangeViewMode" style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-size:24px; font-weight:900; color:var(--primary);" id="globalRangeDisplayText">1 - 1000</div>
                        <button type="button" onclick="toggleGlobalRangeEdit(true)" style="padding:8px 14px; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; background:var(--primary-lt); border:1px solid #bfdbfe; color:var(--primary); display:flex; align-items:center; gap:5px; transition:all .2s;">
                            <i class="fas fa-pen"></i> Ubah
                        </button>
                    </div>
                    <div id="globalRangeEditMode" style="display:none;">
                        <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px;">
                            <input type="number" id="inputGlobalRangeMin" value="1" min="1" class="form-control" style="text-align:center; font-weight:700; padding:10px; font-size:14px;">
                            <span style="font-weight:600; color:var(--muted); font-size:13px;">s/d</span>
                            <input type="number" id="inputGlobalRangeMax" value="1000" min="1" class="form-control" style="text-align:center; font-weight:700; padding:10px; font-size:14px;">
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" onclick="toggleGlobalRangeEdit(false)" style="flex:1; padding:9px; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; background:var(--bg); border:1px solid var(--border); color:var(--muted); font-family:inherit; transition:all .2s;">Batal</button>
                            <button type="button" onclick="saveGlobalTankRange()" style="flex:1; padding:9px; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; background:var(--primary); border:none; color:#fff; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:4px; transition:all .2s;"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ★ PENGATURAN SUB-RENTANG NOMOR PER KELAS + KATEGORI -->
            <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border:1px solid #fde68a; border-radius:16px; margin-bottom:20px;">
                <div style="padding:14px 20px; border-bottom:1px solid #fde68a;">
                    <div style="font-size:14px; font-weight:800; color:#92400e; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-tags"></i> Pengaturan Rentang Nomor Tank
                    </div>
                    <div style="font-size:11px; color:#b45309; margin-top:4px;">Tentukan rentang nomor unik untuk setiap Kategori di setiap Kelas. Sistem akan memastikan tidak ada nomor yang dobel.</div>
                </div>
                <div style="padding:18px 20px;">

                    <!-- LOADING STATE -->
                    <div id="katLoading" style="text-align:center; padding:30px;">
                        <i class="fas fa-spinner fa-spin" style="font-size:22px; color:#d97706;"></i>
                        <div style="font-size:12px; color:#b45309; margin-top:10px;">Memuat pengaturan rentang...</div>
                    </div>

                    <!-- KONTEN UTAMA (sembunyi sampai data selesai dimuat) -->
                    <div id="katContent" style="display:none;">

                        <!-- RINGKASAN: kelas mana saja yang sudah dikonfigurasi -->
                        <div id="katSummaryWrap" style="background:#fff; border:1px solid #fde68a; border-radius:10px; padding:14px 16px; margin-bottom:14px;">
                            <div style="font-size:10px; font-weight:800; color:#92400e; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                                <i class="fas fa-clipboard-list"></i> Rentang yang Sudah Dikonfigurasi
                            </div>
                            <div id="katSummaryContent" style="font-size:11px; color:#78350f; line-height:1.8;"></div>
                        </div>

                        <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px; margin-bottom:14px;">
                            <div style="font-size:10px; font-weight:800; color:#1e40af; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                                <i class="fas fa-book-open"></i> Cara Kerja Sistem Rentang
                            </div>
                            <div style="font-size:11px; color:#1e3a8a; line-height:1.8;">
                                <div style="margin-bottom:5px;"><b style="color:#1e40af;">1. Semua rentang tidak boleh menyentuh batas</b> — Jika Cencu A = 1–30, maka rentang lain (kategori apa pun, kelas apa pun) <span style="color:#dc2626;font-weight:800;">tidak boleh</span> menggunakan angka 1 atau 30. Contoh: Cencu B = 2–29 <span style="color:#16a34a;font-weight:800;">✅</span> (ketat di dalam), Cencu B = 31–60 <span style="color:#16a34a;font-weight:800;">✅</span> (di luar), Cencu B = 1–29 <span style="color:#dc2626;font-weight:800;">❌</span> (menyentuh 1).</div>
                                <div style="margin-bottom:5px;"><b style="color:#1e40af;">2. Keunikan nomor dijamin saat undian</b> — Saat acak nomor tank, sistem memastikan nomor <b>tidak duplikat</b> dengan ikan lain di seluruh kategori dan kelas.</div>
                                <div style="margin-bottom:5px;"><b style="color:#1e40af;">3.</b> Rentang <b>kosong</b> = otomatis pakai Rentang Global saat undian.</div>
                                <div><b style="color:#1e40af;">4.</b> Semua rentang harus <b>di dalam Rentang Global</b>.</div>
                            </div>
                        </div>

                        <!-- PILIH KELAS -->
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
                            <label style="font-size:11px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap;">Pilih Kelas</label>
                            <select id="katKelasSelect" class="form-control" style="max-width:200px; font-weight:700;" onchange="onKatKelasChange()">
                                <option value="">-- Pilih Kelas --</option>
                            </select>
                            <div id="katGlobalInfo" style="background:#fff; border:1px solid #fde68a; border-radius:8px; padding:5px 12px;">
                                <span style="font-size:11px; color:#78350f; font-weight:700;">Rentang Global: <span id="katGlobalRangeText" style="color:#1e40af; font-weight:900;">—</span></span>
                            </div>
                        </div>

                        <div id="katGridWrap" style="display:none;">
                            <!-- Info rentang yang sudah ada di kelas ini -->
                            <div id="katExistingInfo" style="display:none; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:10px 14px; margin-bottom:12px; font-size:11px; color:#15803d; line-height:1.6;">
                                <i class="fas fa-check-circle" style="margin-right:4px;"></i>
                                <span id="katExistingText"></span>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; margin-bottom:14px;" id="katGrid"></div>

                            <!-- ERROR VALIDASI (menggantikan info overlap lama) -->
                            <div id="katErrorBox" style="display:none; background:var(--danger-lt); border:1px solid #fca5a5; border-radius:8px; padding:10px 14px; margin-bottom:12px; font-size:11px; color:#991b1b; line-height:1.7;">
                                <div style="font-weight:800; margin-bottom:4px; display:flex; align-items:center; gap:5px;">
                                    <i class="fas fa-triangle-exclamation"></i> Tidak Dapat Disimpan
                                </div>
                                <div id="katErrorText"></div>
                            </div>

                            <button type="button" id="btnSaveKatRange" onclick="saveKategoriRange()" class="btn-primary" style="width:100%; justify-content:center; background:#92400e; padding:12px; border-radius:12px;">
                                <i class="fas fa-save"></i> Simpan Pengaturan Rentang
                            </button>
                        </div>
                        <div id="katEmptyState" style="text-align:center; padding:20px; color:#d97706;">
                            <i class="fas fa-hand-pointer" style="font-size:20px; display:block; margin-bottom:8px; opacity:.4;"></i>
                            <span style="font-size:12px;">Pilih kelas di atas untuk mengatur rentang nomor per kategori</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="old-grid">
                <!-- REGISTRASI PESERTA & IKAN -->
                <div class="old-card" style="background:linear-gradient(135deg,#f8fafc,#fff); border:none; border-radius:20px; box-shadow:0 4px 20px rgba(0,0,0,.04);">
                    <div class="section-head" style="border-bottom:1px solid var(--border); padding:18px 24px;">
                        <div class="section-title" style="font-size:15px; color:var(--text);">
                            <i class="fas fa-user-plus" style="color:var(--primary); margin-right:8px;"></i>Registrasi Peserta & Ikan Baru
                        </div>
                    </div>
                    <div class="section-body" style="padding:24px;">
                        <form id="regPesertaIkanForm">
                            <div class="form-group">
                                <label class="form-label">Nama Peserta</label>
                                <input type="hidden" name="user_id" id="admRegUserId" required>
                                <input type="hidden" name="nama_peserta" id="admRegNama" required>
                                <div class="search-dropdown" id="admRegDropdown">
                                    <div class="input-wrapper">
                                        <input type="text" id="admRegSearch" class="form-input-modal" placeholder="Ketik nama untuk mencari..." autocomplete="off">
                                        <i class="fas fa-search input-icon"></i>
                                        <i class="fas fa-xmark" id="admRegClear" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--light);font-size:13px;display:none;padding:4px;"></i>
                                    </div>
                                    <div class="dropdown-list" id="admRegList"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jenis Keanggotaan</label>
                                <div class="toggle-group" id="admRegToggleGroup">
                                    <div class="toggle-option">
                                        <input type="radio" name="jenis_keanggotaan" id="admPerorangan" value="perorangan" checked>
                                        <label for="admPerorangan"><i class="fas fa-user" style="margin-right:4px"></i>Perorangan</label>
                                    </div>
                                    <div class="toggle-option">
                                        <input type="radio" name="jenis_keanggotaan" id="admTeam" value="team">
                                        <label for="admTeam"><i class="fas fa-users" style="margin-right:4px"></i>Team / Club</label>
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
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Kategori</label>
                                    <div class="input-wrapper">
                                        <select name="kategori" class="form-input-modal" required style="padding-left:14px; cursor:pointer;">
                                            <option value="" disabled selected>Pilih Kategori</option>
                                            <option>Cencu</option><option>Chginwa</option><option>Freemarking</option>
                                            <option>Goldenbase</option><option>Klasik</option><option>Bonsai</option><option>Jumbo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Kelas</label>
                                    <div class="input-wrapper">
                                        <select name="kelas" class="form-input-modal" required style="padding-left:14px; cursor:pointer;">
                                            <option value="" disabled selected>Pilih Kelas</option>
                                            <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; gap:10px; margin-top:18px;">
                                <button type="button" id="btnSavePesertaOnly" onclick="submitSavePeserta()" style="flex:1; padding:13px; border-radius:12px; border:2px solid #14b8a6; background:linear-gradient(135deg,#f0fdfa,#ccfbf1); color:#0d9488; font-family:'Plus Jakarta Sans',sans-serif; font-size:12px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:7px; transition:all .25s; letter-spacing:.2px;">
                                    <i class="fas fa-user-check"></i> SIMPAN DATA PESERTA
                                </button>
                                <button type="submit" class="btn-primary" id="btnRegPesertaIkan" style="flex:1; justify-content:center; padding:13px; border-radius:12px; font-size:12px; letter-spacing:.2px;">
                                    <i class="fas fa-fish" style="margin-right:6px;"></i> DAFTARKAN IKAN
                                </button>
                            </div>
                            <div style="margin-top:8px; padding:8px 12px; background:var(--bg); border:1px solid var(--border); border-radius:8px; display:flex; gap:16px; font-size:10px; color:var(--muted);">
                                <span><i class="fas fa-user-check" style="color:#14b8a6; margin-right:3px;"></i> Simpan data peserta saja (tanpa ikan baru)</span>
                                <span><i class="fas fa-fish" style="color:var(--primary); margin-right:3px;"></i> Simpan data + daftarkan 1 ikan baru</span>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- UNDIAN TANK -->
                <div class="old-card" style="background:#1e293b; color:#fff;">
                    <div class="section-head" style="border-color:rgba(255,255,255,.1);">
                        <div class="section-title" style="color:#fff; font-size:13px;"><i class="fas fa-dice"></i> Undian Nomor Tank</div>
                    </div>
                    <div class="section-body" style="text-align:center;">
                        <select id="pesertaSelectOld" class="form-control" style="background:rgba(0,0,0,.3); color:#fff; border-color:rgba(255,255,255,.1); margin-bottom:14px;"></select>
                        <div id="tankCounter" style="font-size:11px; color:rgba(255,255,255,.5); margin-bottom:8px;">Memuat...</div>
                        <div style="font-size:56px; font-weight:900; margin:12px 0; letter-spacing:2px; transition:color .3s;" id="numberDisplayOld">--</div>
                        <button class="btn-primary" id="btnAcakOld" style="width:100%; justify-content:center; background:#3b82f6;">
                            <i class="fas fa-shuffle"></i> Acak Nomor Tank
                        </button>
                        <button type="button" onclick="openResetTankModal()" style="width:100%; margin-top:10px; padding:10px; border-radius:10px; border:1px solid rgba(239,68,68,.3); background:rgba(239,68,68,.1); color:#fca5a5; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:6px; transition:all .2s;">
                            <i class="fas fa-rotate-left"></i> Reset Semua Nomor Tank
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL: BONUS POINT -->
<div class="modal-bg" id="modalBonus" style="--mw:520px;">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-trophy" style="color:#f59e0b;"></i> Kelola Bonus Point</h3>
            <button class="modal-close" onclick="closeModal('modalBonus')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="bonusModalBody"></div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalBonus')">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL: RESET NOMOR TANK -->
<div class="modal-bg" id="modalResetTank" style="--mw:450px;">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-rotate-left" style="color:var(--danger);"></i> Reset Nomor Tank</h3>
            <button class="modal-close" onclick="closeModal('modalResetTank')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--danger-lt);border:1px solid #fca5a5;border-radius:10px;padding:14px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start;">
                <i class="fas fa-triangle-exclamation" style="color:var(--danger);margin-top:2px;"></i>
                <div style="font-size:12px;color:#991b1b;line-height:1.5;">
                    <b>Peringatan!</b> Tindakan ini akan menghapus <b>SEMUA</b> nomor tank yang sudah terundi. Peserta harus mengundi ulang nomornya dari awal.
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Alasan Reset <span style="color:var(--danger);">*</span></label>
                <textarea id="resetReason" class="form-control" rows="3" placeholder="Contoh: Event lomba sebelumnya telah selesai, persiapan event baru..." style="resize:none;"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('modalResetTank')">Batal</button>
            <button class="btn-primary" id="btnSubmitReset" style="background:var(--danger);box-shadow:0 3px 10px rgba(239,68,68,.2);" onclick="submitResetTank()"><i class="fas fa-rotate-left"></i> Ya, Reset Semua</button>
        </div>
    </div>
</div>

<!-- MODAL: KELOLA MVP -->
<div class="modal-bg" id="modalMvp" style="--mw:900px;">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-star" style="color:#f59e0b;"></i> Manajemen Pendaftaran MVP</h3>
            <button class="modal-close" onclick="closeModal('modalMvp')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg); border:1px solid var(--border); border-radius:12px; padding:16px 20px; margin-bottom:20px;">
                <div>
                    <div style="font-size:14px; font-weight:800;">Status Pendaftaran MVP</div>
                    <div style="font-size:11px; color:var(--muted);" id="mvpStatusText">Memuat status...</div>
                </div>
                <button class="btn-primary" id="btnToggleMvp" onclick="toggleMvpRegistration()" style="padding:8px 18px; font-size:12px;"><i class="fas fa-spinner fa-spin"></i></button>
            </div>

            <!-- ★ PERHATIAN: Info penting untuk user -->
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;margin-bottom:14px;display:flex;gap:8px;align-items:flex-start;">
                <i class="fas fa-circle-info" style="color:#d97706;margin-top:2px;"></i>
                <span style="font-size:11px;color:#92400e;line-height:1.5;">Menghapus ikan dari daftar MVP <b>tidak menghapus data ikan</b>. Peserta dapat mendaftarkan ulang ikan tersebut ke MVP jika pendaftaran masih dibuka.</span>
            </div>

            
            <!-- ★ PESERTA YANG SUDAH KIRIM MVP (UNLOCK) -->
            <div style="background:linear-gradient(135deg,#f8fafc,#f1f5f9);border:1px solid var(--border);border-radius:14px;margin-bottom:16px;overflow:hidden;">
                <div style="padding:12px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                    <div class="section-title" style="font-size:13px;margin:0;"><i class="fas fa-user-lock" style="color:var(--purple);"></i> Peserta yang Sudah Mengirim MVP</div>
                    <span style="font-size:10px;color:var(--light);font-weight:700;" id="mvpPesertaCount">0 peserta</span>
                </div>
                <div class="table-wrap" style="max-height:220px;border:none;border-radius:0;">
                    <table class="data-table" style="min-width:auto;">
                        <thead>
                            <tr>
                                <th style="width:30px;">#</th>
                                <th>PESERTA</th>
                                <th>ASAL / TEAM</th>
                                <th style="text-align:center;width:80px;">IKAN MVP</th>
                                <th style="text-align:center;width:100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="mvpPesertaBody">
                            <tr><td colspan="5" style="text-align:center;color:var(--light);padding:16px;">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section-title" style="margin-bottom:12px; font-size:13px;"><i class="fas fa-list" style="color:#f59e0b;"></i> Daftar Ikan Terdaftar MVP</div>

            <!-- ★ PERUBAHAN: min-width:auto agar kolom tidak terlalu lebar, tambah kolom AKSI -->
            <div class="table-wrap" style="max-height:400px;">
                <table class="data-table" style="min-width:auto;">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>PESERTA</th>
                            <th>ASAL / TEAM</th>
                            <th>KATEGORI</th>
                            <th>KELAS</th>
                            <th>NO. TANK</th>
                            <th style="text-align:center;width:80px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="mvpTableBody">
                        <tr><td colspan="7" style="text-align:center; color:var(--light); padding:20px;">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-foot"><button class="btn-cancel" onclick="closeModal('modalMvp')">Tutup</button></div>
    </div>
</div>

<!-- POPUP: SUKSES -->
<div class="popup-overlay" id="popupSuccess">
    <div class="popup-card">
        <div class="popup-icon success"><i class="fas fa-check"></i></div>
        <h2 class="popup-title" id="popupSuccessTitle">Berhasil!</h2>
        <p class="popup-desc" id="popupSuccessDesc">Aksi berhasil dilakukan.</p>
        <button class="popup-btn success" onclick="hidePopup('popupSuccess')"><i class="fas fa-circle-check"></i> OK</button>
    </div>
</div>

<!-- POPUP: ERROR -->
<div class="popup-overlay" id="popupError">
    <div class="popup-card">
        <div class="popup-icon danger"><i class="fas fa-triangle-exclamation"></i></div>
        <h2 class="popup-title" id="popupErrorTitle">Gagal!</h2>
        <p class="popup-desc" id="popupErrorDesc">Terjadi kesalahan.</p>
        <button class="popup-btn danger" onclick="hidePopup('popupError')"><i class="fas fa-rotate-right"></i> Tutup</button>
    </div>
</div>

<!-- POPUP: KONFIRMASI -->
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

<!-- POPUP: STAT DETAIL -->
<div class="popup-overlay" id="popupStatDetail">
    <div class="stat-detail-popup">
        <div class="stat-detail-head">
            <div style="display:flex;align-items:center;gap:14px;min-width:0;">
                <div id="statDetailIcon" style="width:40px;height:40px;border-radius:12px;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-fish" style="color:#fff;font-size:16px;" id="statDetailIconI"></i>
                </div>
                <div style="min-width:0;">
                    <h3 id="statDetailTitle" style="font-size:15px;font-weight:800;color:var(--text);margin:0;line-height:1.3;">Detail</h3>
                    <p id="statDetailCount" style="font-size:11px;color:var(--muted);margin:2px 0 0;">Memuat...</p>
                </div>
            </div>
            <button onclick="hidePopup('popupStatDetail')" style="width:32px;height:32px;border-radius:10px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:14px;transition:all .2s;flex-shrink:0;" onmouseover="this.style.background='#f1f5f9';this.style.color='var(--text)'" onmouseout="this.style.background='white';this.style.color='var(--muted)'"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="stat-detail-body" id="statDetailBody">
            <div class="empty-state" style="padding:30px;"><i class="fas fa-spinner fa-spin" style="font-size:18px;"></i></div>
        </div>
    </div>
</div>

<!-- POPUP: INFO -->
<div class="popup-overlay" id="popupInfo">
    <div class="popup-card">
        <div class="popup-icon info"><i class="fas fa-circle-info"></i></div>
        <h2 class="popup-title" id="popupInfoTitle">Informasi</h2>
        <p class="popup-desc" id="popupInfoDesc">Detail informasi.</p>
        <button class="popup-btn info" onclick="hidePopup('popupInfo')"><i class="fas fa-check"></i> OK</button>
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
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
</body>
</html>