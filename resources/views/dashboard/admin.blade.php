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
    @media(max-width:768px){
    #kelasRangeInputs{ grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media(max-width:480px){
        #kelasRangeInputs{ grid-template-columns: 1fr !important; }
    }
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
        <div class="stat-card c-blue"><div class="stat-icon blue"><i class="fas fa-fish"></i></div><div class="stat-num" id="sTotal">0</div><div class="stat-lbl">Total Ikan Terdaftar</div></div>
        <div class="stat-card c-green"><div class="stat-icon green"><i class="fas fa-check-double"></i></div><div class="stat-num" id="sDinilai">0</div><div class="stat-lbl">Sudah Dinilai</div></div>
        <div class="stat-card c-purple"><div class="stat-icon purple"><i class="fas fa-crown"></i></div><div class="stat-num" id="sGrand">0</div><div class="stat-lbl">Grand Juri Edit</div></div>
        <div class="stat-card c-red"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div class="stat-num" id="sBelum">0</div><div class="stat-lbl">Belum Dinilai</div></div>
        <div class="stat-card c-amber"><div class="stat-icon amber"><i class="fas fa-user-pen"></i></div><div class="stat-num" id="sJuri">0</div><div class="stat-lbl">Juri Aktif</div></div>
        <div class="stat-card c-teal"><div class="stat-icon teal"><i class="fas fa-chart-line"></i></div><div class="stat-num" id="sAvg">0</div><div class="stat-lbl">Rata-rata Nilai</div></div>
            <!-- CARD SISA TANK (DINAMIS) -->
        <div style="display:flex;justify-content:center;margin-top:-6px;">
            <div class="stat-card c-teal" style="width:220px;">
                <div class="stat-icon teal"><i class="fas fa-boxes-stacked"></i></div>
                <div class="stat-num" id="sSisaTank">0</div>
                <div class="stat-lbl" id="sSisaTankLabel">Sisa Tank (Max 1000)</div>
            </div>
        </div>
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
                            <th>DINILAI OLEH</th><th>TOTAL NILAI</th><th>STATUS</th><th>AKSI</th>
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
                    <div style="font-size:11px; color:var(--muted); margin-top:4px;">Digunakan sebagai fallback jika kelas tidak memiliki rentang khusus</div>
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
            
            <!-- ★ PENGATURAN RENTANG PER KELAS (DI ATAS) -->
            <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1px solid #bfdbfe; border-radius:16px; margin-bottom:20px;">
                <div style="padding:14px 20px; border-bottom:1px solid #bfdbfe;">
                    <div style="font-size:14px; font-weight:800; color:#1e40af; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-sliders"></i> Pengaturan Rentang Nomor Tank per Kelas
                    </div>
                </div>
                <div style="padding:18px 20px;">
                    <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:12px; margin-bottom:16px;" id="kelasRangeInputs">
                        <div style="text-align:center; padding:20px; color:#64748b; grid-column: 1/-1;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat pengaturan...
                        </div>
                    </div>
                    <div id="kelasRangeSavedInfo" style="display:none; background:#dcfce7; border:1px solid #86efac; border-radius:10px; padding:10px 14px; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-circle-check" style="color:#16a34a; font-size:14px;"></i>
                        <span style="font-size:12px; font-weight:700; color:#166534;">Rentang nomor per kelas sudah disimpan</span>
                    </div>
                    <div id="kelasRangeBtnWrap">
                        <button type="button" id="btnSaveTankRange" onclick="saveTankRange()" class="btn-primary" style="width:100%; justify-content:center; background:#1e40af;">
                            <i class="fas fa-save"></i> Simpan Rentang Nomor
                        </button>
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
                            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; margin-top:18px; padding:13px;">
                                <i class="fas fa-fish" style="margin-right:6px;"></i> DAFTARKAN PESERTA & IKAN
                            </button>
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
/* ═══════════════════════════════════════════════
   HELPERS & STATE
   ═══════════════════════════════════════════════ */
function closeModal(id){document.getElementById(id).classList.remove('show');}

/* ═══ FUNGSI MODAL BERSIH (TANPA OVERRIDE BERANTAI) ═══ */
function openModal(id){
    var el = document.getElementById(id);
    if(!el) return; // Cegah error jika elemen tidak ditemukan
    el.classList.add('show');

    // Trigger khusus saat modal spesifik dibuka
    if(id === 'modalCreate'){
        var form = document.getElementById('formCreateUser');
        if(form) form.reset();
        var cPwdEl = document.getElementById('createPwd');
        var cConfEl = document.getElementById('createPwdConf');
        if(cPwdEl) cPwdEl.classList.remove('input-error','input-success');
        if(cConfEl) cConfEl.classList.remove('input-error','input-success');
        var errPwd = document.getElementById('createPwdErr');
        var barPwd = document.getElementById('createStrBar');
        var txtPwd = document.getElementById('createStrText');
        var errEmail = document.getElementById('createEmailErr');
        var matchNo = document.getElementById('createMatchNo');
        var matchOk = document.getElementById('createMatchOk');
        if(errPwd) errPwd.style.display='none';
        if(barPwd) barPwd.style.display='none';
        if(txtPwd) txtPwd.style.display='none';
        if(errEmail) errEmail.style.display='none';
        if(matchNo) matchNo.style.display='none';
        if(matchOk) matchOk.style.display='none';
        var cSegs = [document.getElementById('cSeg1'),document.getElementById('cSeg2'),document.getElementById('cSeg3'),document.getElementById('cSeg4'),document.getElementById('cSeg5')];
        for(var i=0;i<cSegs.length;i++){ if(cSegs[i]) cSegs[i].className='str-seg'; }
    }

    if(id === 'modalOld'){
        loadPesertaOld();
        loadTankRange();
    }
}

function esc(s){if(!s)return '';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function getCsrf(){return document.querySelector('meta[name="csrf-token"]').getAttribute('content');}

document.querySelectorAll('.modal-bg').forEach(function(m){
    m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});
});


var currentTankMax = 1000;
var kelasList = ['A', 'B', 'C', 'D', 'E'];
var allScoringData=[];
var chartKat,chartStat,chartTop;
var _confirmCallback=null;

/* ═══════════════════════════════════════════════
   POPUP SYSTEM
   ═══════════════════════════════════════════════ */
function showPopup(id){document.getElementById(id).classList.add('show');}
function hidePopup(id){document.getElementById(id).classList.remove('show');}
function popupSuccess(title,desc){
    document.getElementById('popupSuccessTitle').textContent=title||'Berhasil!';
    document.getElementById('popupSuccessDesc').innerHTML=desc||'';
    showPopup('popupSuccess');
}
function popupError(title,desc){
    document.getElementById('popupErrorTitle').textContent=title||'Gagal!';
    document.getElementById('popupErrorDesc').innerHTML=desc||'';
    showPopup('popupError');
}
function popupInfo(title,desc){
    document.getElementById('popupInfoTitle').textContent=title||'Informasi';
    document.getElementById('popupInfoDesc').innerHTML=desc||'';
    showPopup('popupInfo');
}
function popupConfirm(title,desc,btnText,callback){
    document.getElementById('popupConfirmTitle').textContent=title||'Konfirmasi';
    document.getElementById('popupConfirmDesc').innerHTML=desc||'';
    document.getElementById('popupConfirmBtn').innerHTML='<i class="fas fa-check"></i> '+(btnText||'Ya, Lanjutkan');
    _confirmCallback=callback;
    showPopup('popupConfirm');
}
function executeConfirm(){hidePopup('popupConfirm');if(typeof _confirmCallback==='function')_confirmCallback();_confirmCallback=null;}
function cancelConfirm(){hidePopup('popupConfirm');_confirmCallback=null;}

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
   PASSWORD VALIDATION (CREATE USER MODAL)
   ═══════════════════════════════════════════════ */
var cPwd=document.getElementById('createPwd');
var cConf=document.getElementById('createPwdConf');
var cSegs=[document.getElementById('cSeg1'),document.getElementById('cSeg2'),document.getElementById('cSeg3'),document.getElementById('cSeg4'),document.getElementById('cSeg5')];

function validateCreatePwd(){
    var val=cPwd.value;
    var errEl=document.getElementById('createPwdErr');
    var barEl=document.getElementById('createStrBar');
    var txtEl=document.getElementById('createStrText');
    for(var i=0;i<cSegs.length;i++)cSegs[i].className='str-seg';
    txtEl.className='str-text';txtEl.style.display='none';
    barEl.style.display='none';errEl.style.display='none';
    cPwd.classList.remove('input-error','input-success');
    if(val.length===0){checkCreateMatch();return;}
    barEl.style.display='flex';txtEl.style.display='block';
    var hasL=/[a-z]/.test(val),hasU=/[A-Z]/.test(val),hasN=/[0-9]/.test(val),hasS=/[^A-Za-z0-9]/.test(val);
    var str=0;if(val.length>=8)str++;if(hasL)str++;if(hasU)str++;if(hasN)str++;if(hasS)str++;
    if(val.length<8||!hasL||!hasU||!hasN||!hasS){
        errEl.style.display='flex';cPwd.classList.add('input-error');
        txtEl.textContent='Belum memenuhi syarat';txtEl.classList.add('w');
        if(str>0)cSegs[0].classList.add('w');
    } else {
        cPwd.classList.add('input-success');
        if(str<=3){cSegs[0].classList.add('m');cSegs[1].classList.add('m');txtEl.textContent='Cukup';txtEl.classList.add('m');cPwd.classList.remove('input-success');}
        else if(str===4){for(var a=0;a<4;a++)cSegs[a].classList.add('s');txtEl.textContent='Kuat';txtEl.classList.add('s');}
        else{for(var b=0;b<5;b++)cSegs[b].classList.add('s');txtEl.textContent='Sangat kuat';txtEl.classList.add('s');}
    }
    checkCreateMatch();
}

function checkCreateMatch(){
    var p=cPwd.value,c=cConf.value;
    var noEl=document.getElementById('createMatchNo'),okEl=document.getElementById('createMatchOk');
    noEl.style.display='none';okEl.style.display='none';
    cConf.classList.remove('input-error','input-success');
    if(c.length===0)return;
    if(p!==c){noEl.style.display='flex';cConf.classList.add('input-error');}
    else{okEl.style.display='flex';cConf.classList.add('input-success');}
}

cPwd.addEventListener('input',validateCreatePwd);
cConf.addEventListener('input',checkCreateMatch);

/* Toggle password visibility */
document.getElementById('toggleCreatePwd').addEventListener('click',function(){
    var ic=this.querySelector('i');
    if(cPwd.type==='password'){cPwd.type='text';ic.classList.replace('fa-eye','fa-eye-slash');}
    else{cPwd.type='password';ic.classList.replace('fa-eye-slash','fa-eye');}
});
document.getElementById('toggleCreatePwdConf').addEventListener('click',function(){
    var ic=this.querySelector('i');
    if(cConf.type==='password'){cConf.type='text';ic.classList.replace('fa-eye','fa-eye-slash');}
    else{cConf.type='password';ic.classList.replace('fa-eye-slash','fa-eye');}
});

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
        document.getElementById('sSisaTank').innerText=d.sisa_tank||0;
        document.getElementById('sSisaTankLabel').innerText='Sisa Tank (Max '+(d.max_tank||1000)+')';
        renderChartKategori(d.per_kategori||{});
        renderChartStatus(d.sudah_dinilai||0,d.grand_edited||0,d.belum_dinilai||0);
        renderChartTop(d.top_10||[]);
    }).catch(function(){});
}

function renderChartKategori(data){
    var labels=Object.keys(data),vals=Object.values(data);
    var colors=['#2563eb','#7c3aed','#10b981','#f59e0b','#ef4444','#14b8a6','#f97316','#6366f1'];
    if(chartKat)chartKat.destroy();
    chartKat=new Chart(document.getElementById('chartKategori'),{
        type:'bar',data:{labels:labels,datasets:[{data:vals,backgroundColor:colors.slice(0,labels.length),borderRadius:6,borderSkipped:false}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{font:{size:10}}},x:{ticks:{font:{size:10}}}}}
    });
}

function renderChartStatus(dinilai,grand,belum){
    if(chartStat)chartStat.destroy();
    chartStat=new Chart(document.getElementById('chartStatus'),{
        type:'doughnut',data:{labels:['Sudah Dinilai','Grand Juri Edit','Belum Dinilai'],datasets:[{data:[dinilai,grand,belum],backgroundColor:['#10b981','#7c3aed','#f59e0b'],borderWidth:0,spacing:2}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{font:{size:10},padding:12,usePointStyle:true,pointStyleWidth:8}}}}
    });
}

function renderChartTop(data){
    var labels=[],vals=[],extras=[];
    for(var i=0;i<data.length;i++){
        labels.push(data[i].nama);
        vals.push(data[i].total);
        extras.push({
            kategori: data[i].kategori || '—',
            kelas: data[i].kelas || '—',
            tank: data[i].nomor_tank || '—'
        });
    }

    /* Warna gradien per bar */
    var barColors = [
        '#2563eb','#3b82f6','#6366f1','#7c3aed','#8b5cf6',
        '#2563eb','#3b82f6','#6366f1','#7c3aed','#8b5cf6'
    ];

    if(chartTop)chartTop.destroy();
    chartTop=new Chart(document.getElementById('chartTop'),{
        type:'bar',
        data:{
            labels:labels,
            datasets:[{
                data:vals,
                backgroundColor:barColors,
                borderRadius:4,
                borderSkipped:false,
                barThickness:22
            }]
        },
        options:{
            indexAxis:'y',
            responsive:true,
            maintainAspectRatio:false,
            layout:{padding:{right:10}},
            plugins:{
                legend:{display:false},
                tooltip:{
                    backgroundColor:'#1e293b',
                    titleFont:{family:'Plus Jakarta Sans',size:13,weight:'800'},
                    bodyFont:{family:'Plus Jakarta Sans',size:12,weight:'600'},
                    padding:14,
                    cornerRadius:10,
                    displayColors:false,
                    callbacks:{
                        title:function(items){
                            return items[0].label;
                        },
                        label:function(item){
                            var e=extras[item.dataIndex];
                            return 'Total Nilai: '+item.raw;
                        },
                        afterLabel:function(item){
                            var e=extras[item.dataIndex];
                            return [
                                'Kategori: '+e.kategori,
                                'Kelas: '+e.kelas,
                                'No. Tank: '+e.tank
                            ];
                        }
                    }
                }
            },
            scales:{
                x:{
                    beginAtZero:true,
                    ticks:{font:{size:10,family:'Plus Jakarta Sans'}},
                    grid:{color:'#f1f5f9'}
                },
                y:{
                    ticks:{font:{size:9,family:'Plus Jakarta Sans',weight:'600'}},
                    grid:{display:false}
                }
            }
        }
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
    if(s)params.set('search',s);if(k)params.set('kategori',k);if(st)params.set('status',st);
    fetch('/api/admin/scoring-data?'+params.toString(),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){allScoringData=data;renderTable(data);})
    .catch(function(){});
}

function renderTable(data){
    var tb=document.getElementById('tBody');tb.innerHTML='';
    if(!data||data.length===0){tb.innerHTML='<tr><td colspan="10"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data.</p></div></td></tr>';return;}
    for(var i=0;i<data.length;i++){
        var p=data[i],tr=document.createElement('tr');

        /* Kolom: Juri */
        var jh='<span style="color:var(--light);font-size:11px;">—</span>';
        if(p.juri_nama&&p.juri_nama!=='—'){
            jh='<div class="juri-info"><i class="fas fa-user-pen" style="font-size:9px;color:var(--primary);"></i> <span class="j-name">'+esc(p.juri_nama)+'</span>';
            if(p.grand_juri_nama)jh+='<br><i class="fas fa-crown" style="font-size:9px;"></i> <span class="g-name">'+esc(p.grand_juri_nama)+'</span>';
            jh+='</div>';
        }

        /* Kolom: Status & Total */
        var sc=p.grand_juri_nama?'s-grand':(p.status==='Sudah Dinilai'?'s-dinilai':'s-belum');
        var st=p.grand_juri_nama?'GRAND EDIT':(p.status==='Sudah Dinilai'?'DINILAI':'BELUM');
        var tv=p.total_nilai>0?'<span class="total-val">'+p.total_nilai+'</span>':'<span class="total-val zero">—</span>';

        /* Kolom: ASAL/TEAM */
        var asalHtml='<span style="color:var(--light);font-size:11px;">—</span>';
        if(p.detail_anggota&&p.detail_anggota!=='—'){
            asalHtml='<div style="font-size:11px;color:var(--text-muted);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="'+esc(p.detail_anggota)+'"><i class="fas fa-building" style="font-size:9px;color:var(--primary);margin-right:3px;"></i>'+esc(p.detail_anggota)+'</div>';
        }

        tr.innerHTML=
            '<td style="font-weight:700;color:var(--light);font-size:11px;">'+(i+1)+'</td>'+
            '<td style="font-weight:700;">'+esc(p.nama_peserta)+'</td>'+
            '<td style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;">'+esc(p.kategori)+'</td>'+
            '<td style="font-size:11px;color:var(--muted);">'+esc(p.kelas)+'</td>'+
            '<td style="font-weight:700;color:var(--primary);">Tank '+(p.nomor_tank||'—')+'</td>'+
            '<td>'+asalHtml+'</td>'+
            '<td>'+jh+'</td>'+
            '<td>'+tv+'</td>'+
            '<td><span class="status-badge '+sc+'">'+st+'</span></td>'+
            '<td><button class="btn-xs blue" onclick="openDetail('+i+')"><i class="fas fa-eye"></i></button> <button class="btn-xs red" onclick="deleteIkan('+p.id+',\''+esc(p.nama_peserta).replace(/'/g,"\\'")+'\')" title="Hapus Data"><i class="fas fa-trash-can"></i></button></td>';        tb.appendChild(tr);
    }
}

var filterT;
document.getElementById('filterSearch').addEventListener('input',function(){clearTimeout(filterT);filterT=setTimeout(loadScoringData,300);});
document.getElementById('filterKategori').addEventListener('change',loadScoringData);
document.getElementById('filterStatus').addEventListener('change',loadScoringData);

/* ═══════════════════════════════════════════════
   DETAIL NILAI MODAL (UPDATE: LANGSUNG DARI DATA TABEL)
   ═══════════════════════════════════════════════ */
function openDetail(idx){
    openModal('modalDetail');
    var p = allScoringData[idx];
    if(!p){document.getElementById('detailBody').innerHTML='<div class="empty-state">Data tidak ditemukan.</div>';return;}
    renderDetailView(p);
}

function renderDetailView(p){
    var nd=p.nilai_detail;
    var html='<div class="detail-banner"><div><h4>'+esc(p.nama_peserta)+'</h4><div class="meta"><span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank||'—')+'</span><span><i class="fas fa-tag"></i> '+esc(p.kategori)+' - Kelas '+esc(p.kelas)+'</span>';
    
    if(p.juri_nama&&p.juri_nama!=='—')html+='<span><i class="fas fa-user-pen"></i> '+esc(p.juri_nama)+'</span>';
    if(p.grand_juri_nama)html+='<span style="color:var(--purple);"><i class="fas fa-crown"></i> '+esc(p.grand_juri_nama)+'</span>';
    
    html+='</div></div><div class="detail-total-chip"><i class="fas fa-star" style="margin-right:4px;"></i>'+p.total_nilai+'</div></div>';
    
    if(p.grand_juri_nama)html+='<div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;margin-bottom:14px;display:flex;gap:6px;align-items:flex-start;"><i class="fas fa-circle-info" style="margin-top:1px;"></i><span>Nilai final oleh <b>'+esc(p.grand_juri_nama)+'</b>.</span></div>';
    
    if(!nd||typeof nd!=='object'){html+='<div class="empty-state" style="padding:30px;"><i class="fas fa-clipboard-list"></i><p>Belum ada nilai.</p></div>';document.getElementById('detailBody').innerHTML=html;return;}
    
    var kats=Object.keys(formFields);
    for(var ki=0;ki<kats.length;ki++){
        var kat=kats[ki],fields=formFields[kat],kn=nd[kat]||{},sub=0;
        for(var fi=0;fi<fields.length;fi++){if(kn[fields[fi].id]!=null&&kn[fields[fi].id]!=='')sub+=parseInt(kn[fields[fi].id])||0;}
        html+='<div class="detail-kat"><div class="detail-kat-head"><span class="detail-kat-title"><i class="fas fa-layer-group" style="margin-right:4px;"></i>'+kat.charAt(0).toUpperCase()+kat.slice(1)+'</span><span class="detail-kat-sub">Subtotal: '+sub+'</span></div><div class="detail-kat-body">';
        for(var fj=0;fj<fields.length;fj++){var f=fields[fj],v=kn[f.id],has=(v!=null&&v!=='');html+='<div class="detail-row"><div><div class="label">'+f.label+'</div><div class="meta">Maks '+f.max+'</div></div><span class="val-chip '+(has?'has':'no')+'">'+(has?v:'N/A')+'</span></div>';}
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
    .then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(function(data){
        if(!Array.isArray(data)){
            c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">Error</p></div>';
            document.getElementById('userCount').textContent='Error';return;
        }
        allUsersCache=data;
        document.getElementById('searchUser').value='';
        filterUsers('');
    })
    .catch(function(err){
        c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">'+esc(err.message)+'</p></div>';
        document.getElementById('userCount').textContent='Error';
    });
}

/* ★ CREATE USER — validasi password lengkap */
function submitCreateUser(){
    var form=document.getElementById('formCreateUser');
    var fd=new FormData(form);fd.append('_token',getCsrf());
    var name=fd.get('name'),email=fd.get('email'),pw=cPwd.value,conf=cConf.value,role=fd.get('role');

    if(!name||!email||!pw||!conf||!role){popupError('Form Tidak Lengkap','Semua field wajib diisi.');return;}

    /* Validasi password sama seperti register */
    var hasL=/[a-z]/.test(pw),hasU=/[A-Z]/.test(pw),hasN=/[0-9]/.test(pw),hasS=/[^A-Za-z0-9]/.test(pw);
    if(pw.length<8||!hasL||!hasU||!hasN||!hasS){
        document.getElementById('createPwdErr').style.display='flex';
        cPwd.classList.add('input-error');cPwd.focus();
        popupError('Password Tidak Valid','Password wajib mengandung:<br><div style="text-align:left;margin-top:6px;line-height:1.8;">• Min. <strong>8 karakter</strong><br>• Huruf <strong>kecil</strong> (a-z)<br>• Huruf <strong>besar</strong> (A-Z)<br>• <strong>Angka</strong> (0-9)<br>• <strong>Simbol</strong> (!@#$% dll)</div>');
        return;
    }
    if(pw!==conf){
        document.getElementById('createMatchNo').style.display='flex';
        cConf.classList.add('input-error');cConf.focus();
        popupError('Password Tidak Cocok','Konfirmasi password tidak sesuai dengan password utama.');return;
    }

    fetch('/api/admin/create-user',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){if(!r.ok)return r.json().then(function(d){throw d;});return r.json();})
    .then(function(d){
        if(d.success){closeModal('modalCreate');form.reset();loadUsers();popupSuccess('User Berhasil Ditambahkan!','User <strong>'+esc(name)+'</strong> didaftarkan sebagai <strong>'+esc(roleLabels[role])+'</strong>.');}
        else popupError('Gagal',d.message||'Terjadi kesalahan.');
    })
    .catch(function(e){
        if(e.errors){var msg='';var keys=Object.keys(e.errors);for(var i=0;i<keys.length;i++)msg+='<div style="margin-bottom:4px;">• '+esc(e.errors[keys[i]][0])+'</div>';popupError('Validasi Gagal',msg);}
        else popupError('Kesalahan Jaringan','Gagal menyimpan.');
    });
}

/* ★ DELETE USER */
function deleteUser(uid,name){
    popupConfirm(
        'Hapus User',
        'Yakin ingin menghapus <strong>'+esc(name)+'</strong>?<br><span style="font-size:11px;color:var(--danger);">Tindakan ini tidak dapat dibatalkan.</span>',
        'Ya, Hapus',
        function(){
            var fd=new FormData();fd.append('_token',getCsrf());fd.append('user_id',uid);
            fetch('/api/admin/delete-user',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.success){loadUsers();popupSuccess('User Dihapus','<strong>'+esc(name)+'</strong> berhasil dihapus dari sistem.');}
                else popupError('Gagal Menghapus',d.message||'Terjadi kesalahan.');
            })
            .catch(function(){popupError('Kesalahan Jaringan','Gagal menghubungi server.');});
        }
    );
}

var currentPwdVisible = false;

function openPwdModal(id,name){
    document.getElementById('pwdUserId').value=id;
    document.getElementById('pwdTarget').textContent=name;
    document.getElementById('pwdNew').value='';

    /* Reset toggle ke posisi TUTUP */
    currentPwdVisible = false;
    document.getElementById('togglePwdIcon').className = 'fas fa-eye-slash';
    document.getElementById('togglePwdLabel').textContent = 'TUTUP';

    var plainPwd = plainPwdMap[id] || '';
    var display = document.getElementById('pwdCurrentDisplay');
    var noData = document.getElementById('pwdNoData');
    var toggleBtn = document.getElementById('togglePwdView');

    if(plainPwd !== ''){
        display.textContent = '••••••••';
        display.style.display = 'block';
        noData.style.display = 'none';
        toggleBtn.style.display = 'flex';
    } else {
        display.textContent = '—';
        display.style.display = 'block';
        noData.style.display = 'block';
        toggleBtn.style.display = 'none';
    }

    /* Reset toggle input baru */
    var newInput = document.getElementById('pwdNew');
    newInput.type = 'password';
    document.getElementById('toggleNewPwd').querySelector('i').className = 'fas fa-eye';

    openModal('modalPwd');
}

function toggleCurrentPwd(){
    var id = document.getElementById('pwdUserId').value;
    var plainPwd = plainPwdMap[id] || '';
    var display = document.getElementById('pwdCurrentDisplay');

    currentPwdVisible = !currentPwdVisible;

    if(currentPwdVisible){
        display.textContent = plainPwd;
        document.getElementById('togglePwdIcon').className = 'fas fa-eye';
        document.getElementById('togglePwdLabel').textContent = 'LIHAT';
    } else {
        display.textContent = '••••••••';
        document.getElementById('togglePwdIcon').className = 'fas fa-eye-slash';
        document.getElementById('togglePwdLabel').textContent = 'TUTUP';
    }
}

function toggleNewPwdInput(){
    var input = document.getElementById('pwdNew');
    var icon = document.getElementById('toggleNewPwd').querySelector('i');
    if(input.type === 'password'){
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function submitPwd(){
    var pw = document.getElementById('pwdNew').value;

    if(!pw){
        popupError('Password Kosong','Masukkan password baru terlebih dahulu.');
        return;
    }

    var hasL = /[a-z]/.test(pw);
    var hasU = /[A-Z]/.test(pw);
    var hasN = /[0-9]/.test(pw);
    var hasS = /[^A-Za-z0-9]/.test(pw);

    if(pw.length < 8 || !hasL || !hasU || !hasN || !hasS){
        var missing = [];
        if(pw.length < 8) missing.push('Min. <strong>8 karakter</strong>');
        if(!hasL) missing.push('Huruf <strong>kecil</strong> (a-z)');
        if(!hasU) missing.push('Huruf <strong>besar</strong> (A-Z)');
        if(!hasN) missing.push('<strong>Angka</strong> (0-9)');
        if(!hasS) missing.push('<strong>Simbol</strong> (!@#$% dll)');

        var detail = '';
        for(var i = 0; i < missing.length; i++){
            detail += '<div style="margin-bottom:3px;">• ' + missing[i] + '</div>';
        }

        popupError(
            'Password Tidak Valid',
            'Password baru tidak memenuhi syarat:<br><div style="text-align:left;margin-top:6px;line-height:1.8;">' + detail + '</div>'
        );
        return;
    }

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('user_id', document.getElementById('pwdUserId').value);
    fd.append('new_password', pw);

    var btn = document.querySelector('#modalPwd .btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    fetch('{{ route("api.update.password") }}', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
        body: fd
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            closeModal('modalPwd');
            
            // UPDATE CACHE LANGSUNG agar tidak perlu menunggu loadUsers selesai
            var uid = document.getElementById('pwdUserId').value;
            var newPw = document.getElementById('pwdNew').value;
            for(var i=0; i<allUsersCache.length; i++){
                if(allUsersCache[i].id == uid){
                    allUsersCache[i].plain_password = newPw;
                    break;
                }
            }
            plainPwdMap[uid] = newPw;
            
            loadUsers(); // Tetap jalankan untuk sync ulang data user
            popupSuccess('Password Diubah', 'Password user berhasil diperbarui.');
        } else {
            popupError('Gagal', d.message || 'Tidak dapat mengubah password.');
        }
    })
    .catch(function(){
        popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
    })
    .finally(function(){
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan Password Baru';
    });
}

/* ★ CHANGE ROLE — dropdown tidak terpotong layar */
var activeRoleMenu=null;
function openRoleMenu(e,uid,name,currentRole){
    e.stopPropagation();closeRoleMenu();
    var menu=document.createElement('div');menu.id='roleMenuDropdown';
    menu.style.cssText='position:fixed;z-index:99999;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.15);padding:6px;min-width:160px;visibility:hidden;';
    var roles=[{key:'admin',label:'Admin',color:'#2563eb'},{key:'juri',label:'Juri',color:'#16a34a'},{key:'grand_juri',label:'Grand Juri',color:'#7c3aed'},{key:'user',label:'User Biasa',color:'#94a3b8'}];
    for(var i=0;i<roles.length;i++){
        (function(r){
            var isActive=r.key===currentRole;
            var btn=document.createElement('button');
            btn.style.cssText='display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border:none;border-radius:6px;font-family:inherit;font-size:12px;font-weight:'+(isActive?'800':'600')+';cursor:pointer;background:'+(isActive?'#f1f5f9':'transparent')+';color:var(--text);white-space:nowrap;';
            btn.innerHTML='<span style="width:8px;height:8px;border-radius:50%;background:'+r.color+';flex-shrink:0;"></span>'+r.label+(isActive?' <i class="fas fa-check" style="margin-left:auto;font-size:10px;color:var(--primary);"></i>':'');
            btn.onmouseover=function(){if(!isActive)this.style.background='#f8fafc';};
            btn.onmouseout=function(){if(!isActive)this.style.background='transparent';};
            btn.onclick=function(ev){
                ev.stopPropagation();closeRoleMenu();
                if(r.key===currentRole){popupInfo('Tidak Ada Perubahan','User sudah memiliki role <strong>'+roleLabels[r.key]+'</strong>.');return;}
                changeRole(uid,name,r.key);
            };
            menu.appendChild(btn);
        })(roles[i]);
    }
    document.body.appendChild(menu);

    /* ★ SMART POSITIONING — tidak terpotong layar */
    menu.style.visibility='hidden';
    menu.style.left='0px';menu.style.top='0px';
    var mRect=menu.getBoundingClientRect();
    var vw=window.innerWidth,vh=window.innerHeight;
    var left=e.clientX,top=e.clientY;
    if(left+mRect.width>vw-12)left=vw-mRect.width-12;
    if(top+mRect.height>vh-12)top=vh-mRect.height-12;
    if(left<12)left=12;if(top<12)top=12;
    menu.style.left=left+'px';menu.style.top=top+'px';
    menu.style.visibility='visible';

    activeRoleMenu=menu;
    setTimeout(function(){document.addEventListener('click',closeRoleMenu,{once:true});},10);
}
function closeRoleMenu(){var m=document.getElementById('roleMenuDropdown');if(m)m.remove();activeRoleMenu=null;}

function changeRole(uid,name,newRole){
    popupConfirm(
        'Ubah Role User',
        'Ubah role <strong>'+esc(name)+'</strong> menjadi <strong style="color:'+roleColors[newRole]+';">'+roleLabels[newRole]+'</strong>?',
        'Ya, Ubah Role',
        function(){
            var fd=new FormData();fd.append('_token',getCsrf());fd.append('user_id',uid);fd.append('new_role',newRole);
            fetch('/api/admin/change-role',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){if(d.success){loadUsers();popupSuccess('Role Diubah','<strong>'+esc(name)+'</strong> → <strong>'+roleLabels[newRole]+'</strong>');}else popupError('Gagal',d.message||'Terjadi kesalahan.');})
            .catch(function(){popupError('Kesalahan Jaringan','Gagal menghubungi server.');});
        }
    );
}

function deleteIkan(ikanId, nama){
    popupConfirm(
        'Hapus Data Penilaian',
        'Yakin ingin menghapus data ikan milik <strong>'+esc(nama)+'</strong>?<br><span style="font-size:11px;color:var(--danger);">Semua nilai penilaian terkait juga akan dihapus permanen.</span>',
        'Ya, Hapus Permanen',
        function(){
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('ikan_id', ikanId);
            fetch('/api/admin/delete-ikan', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: fd
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    loadScoringData();
                    loadDashboard();
                    popupSuccess('Berhasil Dihapus', 'Data milik <strong>'+esc(nama)+'</strong> berhasil dihapus dari sistem.');
                } else {
                    popupError('Gagal Menghapus', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){
                popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
            });
        }
    );
}

/* ═══════════════════════════════════════════════
   EXPORT CSV
   ═══════════════════════════════════════════════ */
function exportCSV(){
    if(!allScoringData.length){popupInfo('Tidak Ada Data','Tidak ada data penilaian untuk diexport.');return;}
    var header='No,Nama Peserta,Kategori,Kelas,No Tank,Juri,Grand Juri,Total Nilai,Status\n',rows='';
    for(var i=0;i<allScoringData.length;i++){
        var p=allScoringData[i];
        rows+=(i+1)+',"'+(p.nama_peserta||'')+'","'+(p.kategori||'')+'","'+(p.kelas||'')+'","'+(p.nomor_tank||'')+'","'+(p.detail_anggota||'')+'","'+(p.juri_nama||'')+'","'+(p.grand_juri_nama||'')+'",'+(p.total_nilai||0)+',"'+(p.grand_juri_nama?'Grand Juri Edit':p.status)+'"\n';
    }
    var blob=new Blob(['\uFEFF'+header+rows],{type:'text/csv;charset=utf-8;'});
    var url=URL.createObjectURL(blob);
    var a=document.createElement('a');a.href=url;a.download='LCI_Penilaian_'+new Date().toISOString().slice(0,10)+'.csv';a.click();
    URL.revokeObjectURL(url);
    popupSuccess('Export Berhasil','File CSV (<strong>'+allScoringData.length+' data</strong>) berhasil didownload.');
}

// 2. Load Dropdown Ikan yang belum dapat tank
function loadPesertaOld(){
    var sel=document.getElementById('pesertaSelectOld');
    var counter=document.getElementById('tankCounter');
    sel.innerHTML='<option value="" disabled selected>Memuat...</option>';
    if(counter) counter.textContent='Memuat...';

    fetch('{{ route("api.peserta.belum.tank") }}',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        sel.innerHTML='';
        if(!data.length){
            sel.innerHTML='<option disabled>Semua ikan sudah mendapat nomor tank</option>';
            if(counter) counter.innerHTML='<i class="fas fa-check-circle" style="color:#22c55e;"></i> Semua ikan sudah diundi';
            sel.disabled=true;
            document.getElementById('btnAcakOld').disabled=true;
            return;
        }

        sel.disabled=false;
        document.getElementById('btnAcakOld').disabled=false;
        if(counter) counter.innerHTML=data.length+' ikan belum diundi';

        sel.innerHTML='<option value="" disabled selected>Pilih ikan yang belum diundi ('+data.length+')</option>';
        for(var i=0;i<data.length;i++){
            var o=document.createElement('option');
            o.value=data[i].id;
            o.textContent=data[i].nama_peserta+' — '+data[i].kategori+' ('+data[i].kelas+')';
            sel.appendChild(o);
        }

        /* Reset display ke -- */
        document.getElementById('numberDisplayOld').textContent='--';
        document.getElementById('numberDisplayOld').style.color='#fff';
    })
    .catch(function(){
        sel.innerHTML='<option disabled>Gagal memuat data</option>';
        if(counter) counter.textContent='Error';
    });
}

document.getElementById('btnAcakOld').addEventListener('click',function(){
    var sel=document.getElementById('pesertaSelectOld');
    if(!sel.value)return;

    var display=document.getElementById('numberDisplayOld');
    var btn=this;
    display.style.color='#60a5fa';
    btn.disabled=true;
    display.textContent='...';

    var fd=new FormData();
    fd.append('_token',getCsrf());
    fd.append('ikan_id',sel.value);

    // Panggil API dulu, baru animasi berakhir tepat di nomor hasil
    fetch('{{ route("api.acak.tank.admin") }}',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        if(!d.success) throw new Error(d.message);

        var finalNumber=d.nomor_tank;
        var maxForAnim=currentTankMax||1000;
        var totalSteps=18,step=0;

        var iv=setInterval(function(){
            step++;
            if(step<totalSteps){
                if(step>totalSteps-5){
                    var spread=Math.floor((totalSteps-step)*3)+5;
                    var minA=Math.max(1,finalNumber-spread),maxA=finalNumber+spread;
                    display.textContent=Math.floor(Math.random()*(maxA-minA+1))+minA;
                } else {
                    display.textContent=Math.floor(Math.random()*maxForAnim)+1;
                }
            } else {
                display.textContent=finalNumber;
                display.style.color='#22c55e';
                clearInterval(iv);
                setTimeout(function(){
                    display.textContent='--';
                    display.style.color='#fff';
                    btn.disabled=false;
                    loadPesertaOld();
                    loadDashboard();
                },2000);
            }
        },60);
    })
    .catch(function(e){
        display.textContent='--';
        display.style.color='#fff';
        btn.disabled=false;
        popupError('Undian Gagal',esc(e.message));
    });
});

/* ═══════════════════════════════════════════════
   RESET NOMOR TANK (JS)
   ═══════════════════════════════════════════════ */
function openResetTankModal() {
    document.getElementById('resetReason').value = '';
    openModal('modalResetTank');
}

function submitResetTank() {
    var reason = document.getElementById('resetReason').value.trim();
    if (!reason) {
        popupError('Alasan Wajib Diisi', 'Anda harus mencantumkan alasan mengapa nomor tank direset.');
        return;
    }
    
    popupConfirm(
        'Konfirmasi Reset',
        'Anda yakin ingin menghapus <b>SEMUA</b> nomor tank?<br><span style="font-size:11px;color:var(--danger);">Tindakan ini tidak dapat dibatalkan.</span>',
        'Ya, Reset Sekarang',
        function() {
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('reason', reason);
            
            var btn = document.getElementById('btnSubmitReset');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            fetch('/api/admin/reset-tank', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: fd
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.success) {
                    closeModal('modalResetTank');
                    loadPesertaOld();
                    loadDashboard();
                    document.getElementById('numberDisplayOld').textContent = '--';
                    popupSuccess('Berhasil Direset', 'Semua nomor tank telah dihapus. Peserta akan mendapatkan notifikasi.');
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); })
            .finally(function(){
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-rotate-left"></i> Ya, Reset Semua';
            });
        }
    );
}

// ── SEARCHABLE DROPDOWN PESERTA (modalOld) ──
var admRegUserCache = [];
var admRegSelected = false;

var admRegSearchEl = document.getElementById('admRegSearch');
var admRegListEl = document.getElementById('admRegList');
var admRegClearEl = document.getElementById('admRegClear');
var admRegHiddenName = document.getElementById('admRegNama');

if(admRegSearchEl){
    admRegSearchEl.addEventListener('focus', function(){
        if(admRegUserCache.length===0) loadAdmRegUsers();
        admRegListEl.classList.add('show');
    });
    admRegSearchEl.addEventListener('input', function(){
        var q = this.value.toLowerCase().trim();
        admRegClearEl.style.display = q ? 'block' : 'none';
        if(!q){ renderAdmRegList(admRegUserCache); return; }
        var filtered = [];
        for(var i=0;i<admRegUserCache.length;i++){
            var u=admRegUserCache[i];
            if(u.name.toLowerCase().indexOf(q)!==-1 || u.email.toLowerCase().indexOf(q)!==-1) filtered.push(u);
        }
        renderAdmRegList(filtered);
    });
    admRegClearEl.addEventListener('click', function(){
        admRegSearchEl.value='';
        admRegClearEl.style.display='none';
        admRegHiddenName.value='';
        admRegUserIdEl.value='';
        admRegSelected=false;
        admRegSearchEl.classList.remove('input-success');
        document.getElementById('admPerorangan').checked = true;
        updateAdmToggleUI();
        document.getElementById('admInputDetail').value = '';
        renderAdmRegList(admRegUserCache);
        admRegSearchEl.focus();
    });
    document.addEventListener('click', function(e){
        if(!e.target.closest('#admRegDropdown')) admRegListEl.classList.remove('show');
    });
}

function loadAdmRegUsers(){
    fetch('{{ route("api.list.users") }}',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        admRegUserCache = data.filter(function(u){ return u.role==='user'; });
        renderAdmRegList(admRegUserCache);
    })
    .catch(function(){});
}

// Tambahkan variabel di atas (dekat var admRegUserCache)
var admRegUserIdEl = document.getElementById('admRegUserId');

// Toggle jenis keanggotaan admin
var admRadioP = document.getElementById('admPerorangan');
var admRadioT = document.getElementById('admTeam');
function updateAdmToggleUI() {
    if (admRadioT.checked) {
        document.getElementById('admLabelDetail').textContent = 'Nama Team / Club';
        document.getElementById('admInputDetail').placeholder = 'Contoh: Louhan Fanatic Jakarta';
        document.getElementById('admIconDetail').classList.replace('fa-city', 'fa-shield-halved');
    } else {
        document.getElementById('admLabelDetail').textContent = 'Kota Asal';
        document.getElementById('admInputDetail').placeholder = 'Contoh: Jakarta';
        document.getElementById('admIconDetail').classList.replace('fa-shield-halved', 'fa-city');
    }
}
if(admRadioP) admRadioP.addEventListener('change', updateAdmToggleUI);
if(admRadioT) admRadioT.addEventListener('change', updateAdmToggleUI);

function loadPesertaDetail(userId) {
    fetch('/api/admin/get-peserta-by-user?user_id=' + userId, { headers: {'Accept': 'application/json'} })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.found) {
            if (d.jenis_keanggotaan === 'team') { document.getElementById('admTeam').checked = true; }
            else { document.getElementById('admPerorangan').checked = true; }
            updateAdmToggleUI();
            document.getElementById('admInputDetail').value = d.detail_anggota || '';
        } else {
            document.getElementById('admPerorangan').checked = true;
            updateAdmToggleUI();
            document.getElementById('admInputDetail').value = '';
        }
    })
    .catch(function() {});
}

function renderAdmRegList(list){
    if(!admRegListEl) return;
    admRegListEl.innerHTML='';
    if(!list.length){
        admRegListEl.innerHTML='<div class="dropdown-empty"><i class="fas fa-user-slash" style="font-size:16px;display:block;margin-bottom:4px;opacity:.4;"></i>Tidak ditemukan</div>';
        return;
    }
    for(var i=0;i<list.length;i++){
        (function(u){
            var div=document.createElement('div');
            div.className='dropdown-item';
            div.innerHTML=
                '<div class="di-avatar" style="background:#94a3b8;">'+esc(u.name.charAt(0).toUpperCase())+'</div>'+
                '<div class="di-info"><div class="di-name">'+esc(u.name)+'</div><div class="di-email">'+esc(u.email)+'</div></div>'+
                '<span class="di-role role-user">USER</span>';
            div.addEventListener('click',function(){
                admRegSearchEl.value=u.name;
                admRegHiddenName.value=u.name;
                admRegUserIdEl.value=u.id;
                admRegSelected=true;
                admRegSearchEl.classList.add('input-success');
                admRegListEl.classList.remove('show');
                loadPesertaDetail(u.id);
            });
            admRegListEl.appendChild(div);
        })(list[i]);
    }
}

// ── SUBMIT REGISTRASI PESERTA & IKAN ──
var _regForm=document.getElementById('regPesertaIkanForm');
if(_regForm) _regForm.addEventListener('submit',function(e){
    e.preventDefault();
    var form=this;
    var btn=form.querySelector('.btn-primary');

    if(!admRegSelected){
        popupError('Peserta Belum Dipilih','Silakan pilih nama peserta dari dropdown terlebihkan dahulu.');
        return;
    }

    btn.disabled=true;
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MEMPROSES...';

    var fd=new FormData(form);
    fd.append('_token',getCsrf());

    fetch('/api/admin/register-peserta-ikan',{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:fd
    })
    .then(function(r){if(!r.ok) return r.json().then(function(d){throw d;}); return r.json();})
    .then(function(d){
        if(d.success){
            form.reset();
            admRegSearchEl.value='';
            admRegSearchEl.classList.remove('input-success');
            admRegClearEl.style.display='none';
            admRegNama.value='';
            admRegUserIdEl.value='';
            admRegSelected=false;
            loadPesertaOld();
            loadDashboard();
            popupSuccess('Berhasil Didaftarkan!','Peserta baru beserta ikan berhasil ditambahkan ke sistem.');
        } else {
            popupError('Gagal Mendaftar',d.message||'Terjadi kesalahan saat mendaftarkan peserta.');
        }
    })
    .catch(function(e){
        if(e.errors){
            var msg='';var keys=Object.keys(e.errors);
            for(var i=0;i<keys.length;i++) msg+='<div style="margin-bottom:4px;">• '+esc(e.errors[keys[i]][0])+'</div>';
            popupError('Validasi Gagal',msg);
        } else {
            popupError('Gagal',e.message||'Terjadi kesalahan.');
        }
    })
    .finally(function(){
        btn.disabled=false;
        btn.innerHTML='<i class="fas fa-fish" style="margin-right:6px;"></i> DAFTARKAN PESERTA & IKAN';
    });
});

/* ═══ PENGATURAN RANGE NOMOR UNDIAN (GLOBAL + PER KELAS) ═══ */
function loadTankRange(){
    // Load Global Range
    fetch('/api/tank-range-global',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        var min=d.min||1,max=d.max||1000;
        currentTankMax=max;
        var displayEl=document.getElementById('globalRangeDisplayText');
        if(displayEl)displayEl.textContent=min+' - '+max;
        var minInput=document.getElementById('inputGlobalRangeMin'),maxInput=document.getElementById('inputGlobalRangeMax');
        if(minInput)minInput.value=min;if(maxInput)maxInput.value=max;
    }).catch(function(){});

    // Load Per-Kelas Range
    fetch('/api/tank-range',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        var container=document.getElementById('kelasRangeInputs');if(!container)return;container.innerHTML='';
        var hasAny=false;
        for(var i=0;i<kelasList.length;i++){
            var k=kelasList[i],minVal=(d[k]&&d[k].min)?d[k].min:'',maxVal=(d[k]&&d[k].max)?d[k].max:'';
            if(minVal!==''&&maxVal!=='') hasAny=true;
            var card=document.createElement('div');
            card.style.cssText='background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px;text-align:center;';
            card.innerHTML='<div style="font-size:11px;font-weight:800;color:#1e40af;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Kelas '+k+'</div><div style="display:flex;gap:6px;align-items:center;"><input type="number" id="range_'+k+'_min" value="'+minVal+'" placeholder="Min" class="form-control" style="text-align:center;font-weight:700;padding:8px;font-size:13px;"><span style="font-weight:600;color:#94a3b8;">-</span><input type="number" id="range_'+k+'_max" value="'+maxVal+'" placeholder="Max" class="form-control" style="text-align:center;font-weight:700;padding:8px;font-size:13px;"></div>';
            container.appendChild(card);
        }
        // Jika sudah ada data tersimpan, tampilkan indikator
        if(hasAny){
            kelasRangeSaved=true;
            showKelasRangeSaved();
        } else {
            resetKelasRangeUI();
        }
    }).catch(function(){});
}

function toggleGlobalRangeEdit(show){
    var viewEl=document.getElementById('globalRangeViewMode'),editEl=document.getElementById('globalRangeEditMode');
    if(viewEl)viewEl.style.display=show?'none':'flex';if(editEl)editEl.style.display=show?'block':'none';
}

function saveGlobalTankRange(){
    var min=parseInt(document.getElementById('inputGlobalRangeMin').value),max=parseInt(document.getElementById('inputGlobalRangeMax').value);
    if(isNaN(min)||isNaN(max)||min<1||max<1){popupError('Tidak Valid','Nomor harus lebih dari 0.');return;}
    if(max<=min){popupError('Tidak Valid','Nomor akhir harus lebih besar.');return;}
    
    var btn=event.target.closest('button');
    var originalHtml=btn.innerHTML;
    btn.disabled=true;
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Memproses...';
    
    var fd=new FormData();fd.append('_token',getCsrf());fd.append('min',min);fd.append('max',max);
    fetch('/api/admin/tank-range-global',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){currentTankMax=max;document.getElementById('globalRangeDisplayText').textContent=min+' - '+max;toggleGlobalRangeEdit(false);loadDashboard();popupSuccess('Berhasil','Rentang global: <b>'+min+' - '+max+'</b>');}
        else popupError('Gagal',d.message||'Error');
    }).catch(function(){popupError('Error','Gagal menyimpan.');})
    .finally(function(){btn.disabled=false;btn.innerHTML=originalHtml;});
}

var kelasRangeSaved = false;

function saveTankRange(){
    var ranges={},isValid=true,errorMsg='';
    for(var i=0;i<kelasList.length;i++){
        var k=kelasList[i],minEl=document.getElementById('range_'+k+'_min'),maxEl=document.getElementById('range_'+k+'_max');
        if(!minEl||!maxEl)continue;var min=parseInt(minEl.value),max=parseInt(maxEl.value);
        if(minEl.value===''&&maxEl.value==='')continue;
        if(isNaN(min)||isNaN(max)||min<1||max<1){isValid=false;errorMsg='Rentang Kelas '+k+' tidak valid.';break;}
        if(max<min){isValid=false;errorMsg='Kelas '+k+': Max < Min.';break;}
        ranges[k]={min:min,max:max};
    }
    if(!isValid){popupError('Validasi Gagal',errorMsg);return;}
    if(Object.keys(ranges).length===0){popupError('Kosong','Isi setidaknya satu kelas.');return;}
    
    var btn=document.getElementById('btnSaveTankRange');
    var originalHtml=btn.innerHTML;
    btn.disabled=true;
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Memproses...';
    
    var fd=new FormData();fd.append('_token',getCsrf());fd.append('ranges',JSON.stringify(ranges));
    fetch('/api/admin/tank-range',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){
            kelasRangeSaved=true;
            loadDashboard();
            showKelasRangeSaved();
        } else {
            popupError('Gagal',d.message||'Error');
        }
    })
    .catch(function(){popupError('Error','Gagal menyimpan.');})
    .finally(function(){
        if(!kelasRangeSaved){
            btn.disabled=false;
            btn.innerHTML=originalHtml;
        }
    });
}

function showKelasRangeSaved(){
    var info=document.getElementById('kelasRangeSavedInfo');
    var wrap=document.getElementById('kelasRangeBtnWrap');
    if(info) info.style.display='flex';
    if(wrap){
        wrap.innerHTML='<button type="button" onclick="resetKelasRangeUI()" style="width:100%; padding:10px 20px; border-radius:10px; border:1px solid #86efac; background:#f0fdf4; color:#166534; font-family:inherit; font-size:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all .2s;" onmouseover="this.style.background=\'#dcfce7\'" onmouseout="this.style.background=\'#f0fdf4\'"><i class="fas fa-pen-to-square"></i> Atur Ulang Rentang Nomor</button>';
    }
}

function resetKelasRangeUI(){
    kelasRangeSaved=false;
    var info=document.getElementById('kelasRangeSavedInfo');
    var wrap=document.getElementById('kelasRangeBtnWrap');
    if(info) info.style.display='none';
    if(wrap){
        wrap.innerHTML='<button type="button" id="btnSaveTankRange" onclick="saveTankRange()" class="btn-primary" style="width:100%; justify-content:center; background:#1e40af;"><i class="fas fa-save"></i> Simpan Rentang Nomor</button>';
    }
}

/* ═══════════════════════════════════════════════
   SEARCH USER
   ═══════════════════════════════════════════════ */
var allUsersCache=[];
var plainPwdMap={};

var searchUserT;
document.getElementById('searchUser').addEventListener('input',function(){
    clearTimeout(searchUserT);
    var q=this.value;
    searchUserT=setTimeout(function(){filterUsers(q);},200);
});

function filterUsers(q){
    q=q.toLowerCase().trim();
    var c=document.getElementById('userList');c.innerHTML='';
    var filtered=[];

    plainPwdMap={};
    for(var i=0;i<allUsersCache.length;i++){
        plainPwdMap[allUsersCache[i].id]=allUsersCache[i].plain_password||'';
    }

    if(!q){filtered=allUsersCache;}
    else{
        for(var i=0;i<allUsersCache.length;i++){
            var u=allUsersCache[i];
            if(u.name.toLowerCase().indexOf(q)!==-1||u.email.toLowerCase().indexOf(q)!==-1||(roleLabels[u.role]||'').toLowerCase().indexOf(q)!==-1){
                filtered.push(u);
            }
        }
    }
    document.getElementById('userCount').textContent=filtered.length+' user';
    if(!filtered.length){c.innerHTML='<div class="empty-state"><i class="fas fa-user-slash"></i><p>Tidak ada user ditemukan.</p></div>';return;}
    renderUserList(filtered);
}

function renderUserList(data){
    var c=document.getElementById('userList');c.innerHTML='';
    var myId={{ auth()->id() }};
    for(var i=0;i<data.length;i++){
        var u=data[i],role=u.role||'user',isMe=myId===u.id,isOtherAdmin=(role==='admin'&&!isMe);
        var div=document.createElement('div');div.className='user-card';
        var safeName=esc(u.name).replace(/'/g,"\\");

        var topHtml=
            '<div class="user-card-top">'+
                '<div class="user-avatar" style="background:'+roleColors[role]+';">'+esc(u.name.charAt(0).toUpperCase())+'</div>'+
                '<div class="user-card-body"><h4>'+esc(u.name)+'</h4><span>'+esc(u.email)+'</span></div>'+
                '<span class="role-badge '+roleBadgeCls[role]+'" style="flex-shrink:0;">'+roleLabels[role]+'</span>'+
            '</div>';

        var actions='';
        if(!isMe&&!isOtherAdmin){
            actions+='<button class="btn-xs blue" onclick="openPwdModal('+u.id+',\''+safeName+'\')" title="Password"><i class="fas fa-key"></i></button>';
        }
        if(!isMe){
            actions+='<button class="btn-xs green" onclick="openRoleMenu(event,'+u.id+',\''+safeName+'\',\''+role+'\')" title="Ubah Role"><i class="fas fa-arrows-rotate"></i></button>';
            actions+='<button class="btn-xs red" onclick="deleteUser('+u.id+',\''+safeName+'\')" title="Hapus User"><i class="fas fa-trash-can"></i></button>';
        }

        var bottomHtml='';
        if(actions){
            bottomHtml='<div class="user-card-bottom">'+actions+'</div>';
        }

        div.innerHTML=topHtml+bottomHtml;
        c.appendChild(div);
    }
}

/* ═══════════════════════════════════════════════
   INIT
   ═══════════════════════════════════════════════ */
loadDashboard();
loadScoringData();
loadUsers();
</script>
</body>
</html>