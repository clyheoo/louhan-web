<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Juri - LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-main:#f8fafc; --card:#ffffff; --primary:#2563eb; --primary-light:#eff6ff;
            --text-main:#1e293b; --text-muted:#64748b; --border:#e2e8f0;
            --success:#10b981; --danger:#ef4444; --warning:#f59e0b;
            --purple:#7c3aed; --purple-light:#f5f3ff;
        }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg-main); color:var(--text-main); min-height:100vh; }

        .top-nav { background:var(--card); border-bottom:1px solid var(--border); padding:12px 24px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:50; box-shadow:0 1px 3px rgba(0,0,0,.05); }
        .brand h1 { font-size:18px; font-weight:800; color:var(--purple); display:flex; align-items:center; gap:8px; }
        .brand span { font-size:11px; color:var(--text-muted); }
        .nav-right { display:flex; align-items:center; gap:15px; }
        .nav-right .info h4 { font-size:13px; font-weight:700; }
        .nav-right .info span { font-size:10px; color:#7c3aed; background:#f5f3ff; padding:2px 6px; border-radius:4px; font-weight:700; }
        .btn-logout { padding:8px 14px; border-radius:8px; border:1px solid var(--border); background:white; font-size:12px; font-weight:600; cursor:pointer; color:var(--text-main); text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        .btn-logout:hover { border-color:var(--danger); color:var(--danger); }

        .main-container { padding:20px; max-width:1400px; margin:0 auto; display:flex; flex-direction:column; gap:20px; }
        .card { background:var(--card); border-radius:16px; border:1px solid var(--border); box-shadow:0 1px 3px rgba(0,0,0,.05); overflow:hidden; }
        .card-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
        .card-title { font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px; }
        .card-title i { color:var(--purple); }
        .card-subtitle { font-size:11px; color:var(--text-muted); }
        .card-body { padding:20px; }

        .stats-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:15px; }
        .stat-card { padding:20px; border-radius:14px; border:2px solid var(--border); background:white; text-align:center; position:relative; overflow:hidden; transition:all .25s; }
        .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
        .stat-card.blue::before { background:var(--primary); }
        .stat-card.green::before { background:var(--success); }
        .stat-card.orange::before { background:var(--warning); }
        .stat-card.red::before { background:var(--danger); }
        .stat-card.purple::before { background:var(--purple); }
        .stat-number { font-size:28px; font-weight:900; line-height:1; margin-bottom:4px; }
        .stat-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; }
        .stat-click-hint { font-size:9px; color:var(--text-muted); margin-top:6px; opacity:0; transition:opacity .25s; display:flex; align-items:center; justify-content:center; gap:3px; }
        .stat-card.clickable { cursor:pointer; }
        .stat-card.clickable:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.1); }
        .stat-card.clickable:hover .stat-click-hint { opacity:1; }

        .rincian-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
        .rincian-card { background:white; border:1px solid var(--border); border-radius:10px; padding:16px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; transition:all .2s; }
        .rincian-card:hover { border-color:var(--purple); box-shadow:0 4px 12px rgba(124,58,237,.08); transform:translateY(-2px); }
        .rincian-cat { font-size:13px; font-weight:800; display:flex; align-items:center; gap:6px; }
        .rincian-cat i { font-size:10px; color:var(--purple); opacity:0; transition:opacity .2s; }
        .rincian-card:hover .rincian-cat i { opacity:1; }
        .rincian-data { text-align:right; }
        .rincian-ekor { font-size:18px; font-weight:900; }

        /* ── KOMPONEN JURI ── */
        .juri-chip-list { display:flex; flex-wrap:wrap; gap:8px; }
        .juri-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; font-size:12px; font-weight:700; border:1px solid; cursor:pointer; transition:all .2s; }
        .juri-chip:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(0,0,0,.08); }
        .juri-chip.juri-awal { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
        .juri-chip.juri-awal:hover { background:#dbeafe; }
        .juri-chip.juri-awal i { color:#2563eb; }
        .juri-chip.juri-grand { background:var(--purple-light); color:var(--purple); border-color:#c4b5fd; }
        .juri-chip.juri-grand:hover { background:#ede9fe; }
        .juri-chip.juri-grand i { color:var(--purple); }
        .juri-chip .chip-role { font-size:9px; opacity:.7; text-transform:uppercase; letter-spacing:.3px; font-weight:800; }
        .juri-chip .chip-count { opacity:.5; font-size:11px; }
        .juri-chip .chip-arrow { font-size:9px; opacity:0; transition:opacity .2s; margin-left:2px; }
        .juri-chip:hover .chip-arrow { opacity:.6; }

        /* ── TABLE ── */
        .toolbar { display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
        .search-box { flex:1; min-width:200px; position:relative; }
        .search-box input { width:100%; padding:10px 14px 10px 40px; border:1px solid var(--border); border-radius:10px; font-family:inherit; font-size:13px; outline:none; background:white; transition:all .2s; }
        .search-box input:focus { border-color:var(--purple); box-shadow:0 0 0 3px rgba(124,58,237,.1); }
        .search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:14px; }
        .table-wrap { overflow-x:auto; }
        .result-table { width:100%; border-collapse:collapse; font-size:13px; min-width:1000px; }
        .result-table th { background:var(--bg-main); padding:10px 12px; text-align:left; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; border-bottom:1px solid var(--border); white-space:nowrap; }
        .result-table td { padding:12px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .result-table tr:hover td { background:#fafafa; }
        .badge { padding:4px 8px; border-radius:6px; font-size:10px; font-weight:700; white-space:nowrap; }
        .badge-success { background:#dcfce7; color:#16a34a; }
        .badge-warning { background:#fef3c7; color:#d97706; }
        .badge-purple { background:var(--purple-light); color:var(--purple); }
        .btn-xs{padding:5px 9px;border:none;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:4px;transition:all .15s;}
        .btn-xs.blue{background:var(--primary-lt);color:var(--primary);}
        .btn-xs.blue:hover{background:var(--primary);color:#fff;}
        .btn-xs.green{background:var(--success-lt);color:#16a34a;}
        .btn-xs.green:hover{background:var(--success);color:#fff;}
        .btn-xs.red{background:var(--danger-lt);color:var(--danger);}
        .btn-xs.red:hover{background:var(--danger);color:#fff;}
        .btn-xs.purple{background:var(--purple-lt);color:var(--purple);}
        .btn-xs.purple:hover{background:var(--purple);color:#fff;}
        .btn-sm { padding:6px 10px; border:none; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:4px; transition:all .2s; white-space:nowrap; }
        .btn-edit { background:var(--purple-light); color:var(--purple); }
        .btn-edit:hover { background:var(--purple); color:white; }
        .btn-detail { background:var(--primary-light); color:var(--primary); }
        .btn-detail:hover { background:var(--primary); color:white; }
        .action-group { display:flex; gap:6px; }
        .juri-cell { font-size:12px; font-weight:700; color:var(--primary); line-height:1.5; }
        .juri-cell .grand-line { color:var(--purple); font-size:11px; }
        .juri-cell .grand-line i { font-size:9px; }
        .total-cell { font-size:16px; font-weight:900; color:var(--purple); }
        .total-cell.zero { color:var(--text-muted); font-size:13px; font-weight:600; }

        /* ── MODAL SHARED ── */
        .modal-bg { position:fixed; inset:0; background:rgba(15,23,42,.5); backdrop-filter:blur(6px); z-index:99; display:none; place-items:center; }
        .modal-bg.show { display:grid; }
        .modal-box { background:white; border-radius:20px; width:90%; max-width:860px; max-height:90vh; overflow:hidden; box-shadow:0 25px 50px rgba(0,0,0,.2); display:grid; grid-template-rows:auto 1fr auto; }
        .modal-head { padding:18px 24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#f5f3ff,#ede9fe); }
        .modal-head h3 { font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px; color:#4c1d95; }
        .modal-head h3 i { color:var(--purple); }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-muted); width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:all .2s; }
        .modal-close:hover { background:rgba(0,0,0,.08); }
        .modal-content, .modal-body { padding:20px; overflow-y:auto; }
        .modal-body{padding:20px;overflow-y:auto;}
        .modal-footer { padding:14px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; background:var(--bg-main); }

        /* ── DETAIL MODAL ── */
        .detail-info-banner { background:linear-gradient(135deg,#f5f3ff,#ede9fe); border:1px solid #ddd6fe; border-radius:10px; padding:14px 16px; margin-bottom:16px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:10px; }
        .detail-info-banner h4 { font-size:15px; font-weight:800; color:#4c1d95; }
        .detail-meta { font-size:12px; color:#6d28d9; margin-top:5px; display:flex; gap:14px; flex-wrap:wrap; }
        .detail-meta span { display:flex; align-items:center; gap:5px; }
        .detail-total-badge { background:var(--purple); color:white; padding:7px 16px; border-radius:8px; font-size:14px; font-weight:800; display:flex; align-items:center; gap:6px; white-space:nowrap; }
        .detail-note { background:#fef9c3; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; font-size:12px; color:#92400e; margin-bottom:14px; display:flex; gap:8px; align-items:flex-start; }
        .detail-note i { color:#d97706; margin-top:1px; flex-shrink:0; }
        .detail-note.purple-note { background:var(--purple-light); border-color:#ddd6fe; color:#4c1d95; }
        .detail-note.purple-note i { color:var(--purple); }
        .detail-kat-section { margin-bottom:14px; }
        .detail-kat-header { display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:var(--purple-light); border:1px solid #ddd6fe; border-radius:8px 8px 0 0; }
        .detail-kat-title { font-size:12px; font-weight:800; color:var(--purple); text-transform:uppercase; letter-spacing:.5px; }
        .detail-kat-sub { font-size:12px; font-weight:700; color:#6d28d9; }
        .detail-kat-body { border:1px solid #ddd6fe; border-top:none; border-radius:0 0 8px 8px; overflow:hidden; }
        .detail-kat-mini { background:#f8fafc; padding:7px 16px; font-size:10px; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
        .detail-kat-mini span { color:var(--purple); font-weight:900; font-size:11px; }
        .detail-field-row { display:flex; align-items:center; justify-content:space-between; padding:8px 16px; border-bottom:1px solid #f8fafc; gap:12px; }
        .detail-field-row:last-child { border-bottom:none; }
        .detail-field-row:hover { background:#faf5ff; }
        .detail-field-left { flex:1; }
        .detail-field-name { font-size:12px; font-weight:700; color:var(--text-main); }
        .detail-field-meta { font-size:10px; color:var(--text-muted); margin-top:1px; }
        .score-chip { padding:5px 16px; border-radius:6px; font-size:13px; font-weight:800; min-width:48px; text-align:center; }
        .score-chip.filled { background:#ede9fe; color:var(--purple); }
        .score-chip.empty { background:#f1f5f9; color:var(--text-muted); font-size:11px; font-weight:600; }

        /* ── EDIT MODAL ── */
        .edit-info-banner { margin-bottom:12px; font-size:13px; background:linear-gradient(135deg,#f5f3ff,#ede9fe); padding:12px 16px; border-radius:10px; border:1px solid #ddd6fe; }
        .note-hint { font-size:11px; color:#92400e; background:#fef9c3; border:1px solid #fde68a; padding:8px 12px; border-radius:8px; margin-bottom:14px; display:flex; align-items:center; gap:6px; }
        .note-hint i { color:#d97706; }
        .content-grid { display:grid; grid-template-columns:175px 1fr; gap:16px; }
        .kat-list { display:flex; flex-direction:column; gap:5px; }
        .kat-btn { padding:9px 11px; background:white; border:1px solid var(--border); border-radius:9px; text-align:left; font-size:12px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:space-between; }
        .kat-btn:hover,.kat-btn.active { border-color:var(--purple); color:var(--purple); background:var(--purple-light); }
        .kat-badge { font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:rgba(124,58,237,.12); color:var(--purple); min-width:22px; text-align:center; }
        .kat-badge.has-changes { background:var(--success); color:white; }
        .score-row { display:grid; grid-template-columns:1fr 115px; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9; }
        .score-row:last-of-type { border-bottom:none; }
        .score-label h4 { font-size:13px; font-weight:700; }
        .score-label p { font-size:11px; color:var(--text-muted); margin-top:2px; }
        .score-label .orig-val { color:var(--text-muted); font-weight:600; }
        .score-label .orig-val strong { color:var(--purple); }
        .score-input { width:100%; padding:9px; text-align:center; border:2px solid var(--border); border-radius:10px; font-size:15px; font-weight:800; color:var(--purple); outline:none; font-family:inherit; transition:all .2s; background:white; }
        .score-input:focus { border-color:var(--purple); box-shadow:0 0 0 3px rgba(124,58,237,.1); }
        .score-input.changed { border-color:var(--success); background:#f0fdf4; color:#16a34a; }
        .subtotal-bar { text-align:right; font-size:13px; font-weight:900; color:var(--purple); padding:10px 0 0; border-top:2px solid #ddd6fe; margin-top:8px; }

        .btn-cancel { padding:10px 18px; border:1px solid var(--border); border-radius:11px; font-size:13px; font-weight:700; cursor:pointer; font-family:inherit; color:var(--text-muted); background:white; transition:all .2s; }
        .btn-cancel:hover { border-color:var(--text-muted); color:var(--text-main); }
        .btn-primary { padding:10px 22px; background:var(--purple); color:white; border:none; border-radius:11px; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:7px; font-family:inherit; transition:all .2s; box-shadow:0 4px 12px rgba(124,58,237,.25); }
        .btn-primary:hover { background:#6d28d9; transform:translateY(-1px); }
        .btn-primary:disabled { opacity:.5; cursor:not-allowed; transform:none; box-shadow:none; }
        .btn-blue { padding:10px 20px; background:var(--primary); color:white; border:none; border-radius:11px; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:7px; font-family:inherit; transition:all .2s; box-shadow:0 4px 12px rgba(37,99,235,.2); }
        .btn-blue:hover { background:#1d4ed8; transform:translateY(-1px); }
        .empty-state { text-align:center; padding:30px; color:var(--text-muted); }

        /* ── GENERIC TABLE (inside modals) ── */
        .gen-table { width:100%; border-collapse:collapse; font-size:12px; }
        .gen-table th { background:var(--bg-main); padding:9px 12px; text-align:left; font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); white-space:nowrap; }
        .gen-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .gen-table tbody tr:hover td { background:#fafbfc; }
        .gen-table .g-total { font-weight:900; color:var(--purple); font-size:14px; }
        .gen-table .g-tank { font-weight:800; color:var(--purple); }
        .gen-table .g-juri { font-size:11px; color:var(--text-muted); }
        .gen-count-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:800; }
        .gen-count-badge.green { background:#dcfce7; color:#16a34a; }
        .gen-count-badge.red { background:#fee2e2; color:#dc2626; }

        /* ── POPUPS ── */
        .popup-overlay { position:fixed; inset:0; background:rgba(15,23,42,.4); backdrop-filter:blur(6px); z-index:9999; display:flex; align-items:center; justify-content:center; opacity:0; pointer-events:none; transition:opacity .4s; }
        .popup-overlay.show { opacity:1; pointer-events:all; }
        .popup-card { background:#fff; border-radius:24px; padding:48px 40px 36px; text-align:center; max-width:380px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,.15); transform:scale(.8) translateY(20px); transition:transform .4s cubic-bezier(.16,1,.3,1); }
        .popup-overlay.show .popup-card { transform:scale(1) translateY(0); }
        .popup-icon { width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; }
        .popup-icon i { font-size:36px; color:white; animation:iconPop .5s .3s cubic-bezier(.16,1,.3,1) both; }
        @keyframes iconPop { 0%{transform:scale(0) rotate(-45deg);opacity:0}100%{transform:scale(1) rotate(0);opacity:1} }
        .popup-icon.success { background:linear-gradient(135deg,var(--purple),#6d28d9); box-shadow:0 8px 24px rgba(124,58,237,.3); }
        .popup-icon.danger { background:linear-gradient(135deg,var(--danger),#dc2626); box-shadow:0 8px 24px rgba(239,68,68,.3); }
        .popup-icon.warning { background:linear-gradient(135deg,var(--warning),#d97706); box-shadow:0 8px 24px rgba(245,158,11,.3); }
        .popup-title { font-size:20px; font-weight:800; color:var(--text-main); margin-bottom:8px; }
        .popup-desc { font-size:13.5px; color:var(--text-muted); line-height:1.6; margin-bottom:24px; }
        .popup-btn { display:inline-flex; align-items:center; gap:8px; padding:12px 28px; border:none; border-radius:14px; font-family:inherit; font-size:14px; font-weight:700; cursor:pointer; transition:all .3s; color:white; }
        .popup-btn.success { background:linear-gradient(135deg,var(--purple),#6d28d9); box-shadow:0 4px 12px rgba(124,58,237,.25); }
        .popup-btn.success:hover { transform:translateY(-1px); }
        .popup-btn.danger { background:linear-gradient(135deg,var(--danger),#dc2626); box-shadow:0 4px 12px rgba(239,68,68,.25); }
        .popup-btn.danger:hover { transform:translateY(-1px); }
        .popup-btn.warning { background:linear-gradient(135deg,var(--warning),#d97706); box-shadow:0 4px 12px rgba(245,158,11,.25); }
        .popup-btn.warning:hover { transform:translateY(-1px); }
        .err-list { list-style:none; text-align:left; margin-bottom:16px; display:flex; flex-direction:column; gap:7px; max-height:170px; overflow-y:auto; }
        .err-item { display:flex; align-items:flex-start; gap:9px; padding:9px 11px; background:#faf5ff; border:1px solid #ede9fe; border-radius:9px; font-size:12px; }
        .err-item i { color:var(--purple); margin-top:1px; flex-shrink:0; }
        .err-item span { color:#4c1d95; font-weight:600; line-height:1.4; 
        }
        .popup-btn-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
        .popup-btn.cancel{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);box-shadow:none;}
        .popup-btn.cancel:hover{background:var(--border);color:var(--text-main);
        }
        @media (max-width:1024px) { .stats-grid{grid-template-columns:repeat(3,1fr);} .content-grid{grid-template-columns:1fr;} .split-view{grid-template-columns:1fr;} }
        @media (max-width:640px) { .stats-grid{grid-template-columns:1fr 1fr;} .main-container{padding:12px;} 
        }
        .btn-lock { background:#fef9c3; color:#d97706; border:1px solid #fde68a; }
        .btn-lock:hover { background:#d97706; color:white; border-color:#d97706; }
        .btn-lock.locked { background:#fee2e2; color:#dc2626; border-color:#fca5a5; cursor:not-allowed; }
        .btn-lock.locked:hover { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }
        .juri-cell div { font-size:12px; font-weight:600; color:var(--primary); line-height:1.6; }
        .juri-count-chip { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; background:var(--primary-light); color:var(--primary); margin-top:4px; 
        }
        .detail-juri-accordion { border:1px solid var(--border); border-radius:10px; overflow:hidden; margin-bottom:10px; }
        .detail-juri-toggle { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; cursor:pointer; transition:background .2s; user-select:none; }
        .detail-juri-toggle:hover { background:#faf5ff; }
        .detail-juri-toggle.open { background:var(--purple-light); border-bottom:1px solid #ddd6fe; }
        .detail-juri-toggle .dj-name { font-size:13px; font-weight:700; display:flex; align-items:center; gap:8px; }
        .detail-juri-toggle .dj-total { font-size:14px; font-weight:900; color:var(--purple); }
        .detail-juri-toggle .dj-arrow { font-size:12px; color:var(--text-muted); transition:transform .2s; }
        .detail-juri-toggle.open .dj-arrow { transform:rotate(180deg); }
        .detail-juri-scores { display:none; }
        .detail-juri-scores.open { display:block; }
        .status-actions { margin-top:8px; 
        }
        /* ── CUSTOM SELECT / DROPDOWN ── */
        .filter-select {
            padding: 9px 34px 9px 12px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
            background-color: white;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237c3aed' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 13px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            transition: all 0.2s;
            outline: none;
            min-width: 140px;
        }
        .filter-select:hover {
            border-color: var(--purple);
            background-color: var(--purple-light);
        }
        .filter-select:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(124,58,237,.12);
            background-color: white;
        }
        .filter-select option {
            padding: 8px;
            font-weight: 600;
            color: var(--text-main);
            background: white;
        }
        .filter-select option:first-child {
            color: var(--text-muted);
            font-style: italic;
        }
        .filter-select option:checked {
            background: var(--purple-light);
            color: var(--purple);
        }
        .export-wrap{position:relative;}
        .export-btn{padding:8px 16px;border-radius:8px;border:1px solid #16a34a;background:#dcfce7;font-size:12px;font-weight:700;cursor:pointer;color:#16a34a;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;}
        .export-btn:hover{background:#16a34a;color:#fff;border-color:#16a34a;transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.25);}
        .export-dd{position:absolute;top:calc(100% + 6px);right:0;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.15);min-width:240px;z-index:200;display:none;overflow:hidden;}
        .export-dd.show{display:block;}
        .export-dd-item{padding:10px 16px;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background .12s;font-weight:600;color:var(--text-main);}
        .export-dd-item:hover{background:var(--purple-light);color:var(--purple);}
        .export-dd-item i{width:16px;text-align:center;font-size:12px;}
        .export-dd-sep{height:1px;background:var(--border);margin:4px 0;
        }
    </style>
</head>
<body>
<nav class="top-nav">
    <div class="brand">
        <h1><i class="fas fa-crown"></i> GRAND JURY</h1>
        <span>Otoritas Tertinggi Sistem Penilaian Kontes LCI</span>
    </div>
        <div class="nav-right">
            <div class="info"><h4>{{ $user->name }}</h4><span>GRAND JURI</span></div>
            <div class="export-wrap">
        <button class="export-btn" onclick="document.getElementById('exportDD').classList.toggle('show')">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
        <div class="export-dd" id="exportDD">
            <div class="export-dd-item" onclick="doExport('all')">
                <i class="fas fa-layer-group" style="color:var(--purple);"></i> Export Semua Data
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
                <i class="fas fa-globe" style="color:#ef4444;"></i> Ranking: Global
            </div>
        </div>
    </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
        </form>
    </div>
</nav>

<div class="main-container">
    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-number" id="statTank">0</div>
            <div class="stat-label">Total Tank</div>
        </div>
        <div class="stat-card green">
            <div class="stat-number" id="statPeserta">0</div>
            <div class="stat-label">Total Peserta</div>
        </div>
        <div class="stat-card orange clickable" onclick="openPlotStatus('sudah_plot')">
            <div class="stat-number" id="statSudah">0</div>
            <div class="stat-label">Sudah Plot</div>
            <div class="stat-click-hint"><i class="fas fa-arrow-up-right-from-square"></i> Lihat Data</div>
        </div>
        <div class="stat-card red clickable" onclick="openPlotStatus('belum_plot')">
            <div class="stat-number" id="statBelum">0</div>
            <div class="stat-label">Belum Plot</div>
            <div class="stat-click-hint"><i class="fas fa-arrow-up-right-from-square"></i> Lihat Data</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-number" id="statSisa">0</div>
            <div class="stat-label" id="statSisaLabel">Sisa Tank</div>
        </div>
    </div>

    <!-- RINCIAN -->
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-chart-bar"></i> Rincian Kategori</div></div>
        <div class="card-body"><div class="rincian-grid" id="rincianGrid"><div class="empty-state">Memuat...</div></div></div>
    </div>

    <!-- DAFTAR JURI YANG MENILAI -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-users-gear"></i> Daftar Juri yang Menilai</div>
            <div class="card-subtitle">Klik nama juri untuk melihat peserta yang dinilai</div>
        </div>
        <div class="card-body" id="juriSummaryBody">
            <div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data juri...</div>
        </div>
    </div>

    <!-- DATA MVP -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-star" style="color:#f59e0b;"></i> Data Ikan MVP</div>
            <div class="card-subtitle">Daftar peserta yang sudah mengirimkan ikan untuk penilaian MVP</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>PESERTA</th>
                            <th>ASAL / TEAM</th>
                            <th>JUMLAH IKAN MVP</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMvp"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SISTEM POINT RANKING -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-trophy" style="color:#f59e0b;"></i> Sistem Point Ranking</div>
                <div class="card-subtitle">Peringkat berdasarkan nilai point (hanya ikan yang sudah DIKUNCI Grand Juri)</div>
            </div>
        </div>
        <div class="card-body">
            <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                <div style="display:flex;gap:6px;">
                    <button class="btn-sm btn-edit" id="btnScopeKelas" onclick="setPointScope('per_kategori_kelas')" style="font-size:11px;padding:7px 14px;">
                        <i class="fas fa-layer-group" style="font-size:10px;"></i> Per Kategori + Kelas
                    </button>
                    <button class="btn-sm btn-detail" id="btnScopeKat" onclick="setPointScope('per_kategori')" style="font-size:11px;padding:7px 14px;">
                        <i class="fas fa-tags" style="font-size:10px;"></i> Per Kategori
                    </button>
                    <button class="btn-sm" id="btnScopeGlobal" onclick="setPointScope('global')" style="font-size:11px;padding:7px 14px;background:var(--warning-lt);color:#d97706;border:1px solid #fde68a;">
                        <i class="fas fa-globe" style="font-size:10px;"></i> Rank Global
                    </button>
                </div>
                <select class="filter-select" id="pointFilterKategori" onchange="loadPointRanking()">
                    <option value="">🏷️ Semua Kategori</option>
                    <option value="Cencu">Cencu</option>
                    <option value="Chginwa">Chginwa</option>
                    <option value="Freemarking">Freemarking</option>
                    <option value="Goldenbase">Goldenbase</option>
                    <option value="Klasik">Klasik</option>
                    <option value="Bonsai">Bonsai</option>
                    <option value="Jumbo">Jumbo</option>
                </select>
                <select class="filter-select" id="pointFilterKelas" onchange="loadPointRanking()" style="min-width:120px;">
                    <option value="">📐 Semua Kelas</option>
                    <option value="A">Kelas A</option>
                    <option value="B">Kelas B</option>
                    <option value="C">Kelas C</option>
                    <option value="D">Kelas D</option>
                    <option value="E">Kelas E</option>
                </select>
                <!-- ★ RANK GLOBAL: Top N selector -->
                <div id="globalTopNWrap" style="display:none;align-items:center;gap:6px;">
                    <span style="font-size:11px;font-weight:700;color:var(--text-muted);white-space:nowrap;">Tampilkan Top</span>
                    <input type="number" id="globalTopN" value="10" min="1" max="100" style="width:60px;padding:6px 6px;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;font-weight:800;color:var(--text);outline:none;text-align:center;" oninput="loadPointRanking()">
                    <div style="display:flex;gap:3px;">
                        <button class="btn-sm" onclick="setGlobalTopN(10)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:#d97706;border:1px solid #fde68a;min-width:auto;">10</button>
                        <button class="btn-sm" onclick="setGlobalTopN(20)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:#d97706;border:1px solid #fde68a;min-width:auto;">20</button>
                        <button class="btn-sm" onclick="setGlobalTopN(50)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:#d97706;border:1px solid #fde68a;min-width:auto;">50</button>
                        <button class="btn-sm" onclick="setGlobalTopN(100)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:#d97706;border:1px solid #fde68a;min-width:auto;">100</button>
                    </div>
                </div>
            </div>
            <div id="pointRankingContent"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div></div>
        </div>
    </div>

    <!-- DATA MANAJEMEN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-database"></i> Data Manajemen Nilai</div>
            <div class="card-subtitle">Grand Juri dapat melihat &amp; mengubah nilai dari setiap juri</div>
        </div>
        <div class="card-body">
            <div class="toolbar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Cari Nama/Tim Peserta..."></div>
            </div>
            <div class="table-wrap">
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>PESERTA</th>
                            <th>KATEGORI - KELAS</th>
                            <th>NO. TANK</th>
                            <th>ASAL/TEAM</th>
                            <th>DINILAI OLEH</th>
                            <th>TOTAL NILAI</th>
                            <th>POINT</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPeserta"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal-bg" id="modalDetail">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-eye"></i> Detail Nilai Peserta</h3>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-content" id="detailContent"><div class="empty-state">Memuat...</div></div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('modalDetail')"><i class="fas fa-xmark"></i> Tutup</button>
            <button class="btn-blue" id="btnToEdit"><i class="fas fa-pen-to-square"></i> Edit Nilai Ini</button>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-bg" id="modalEdit">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-pen-to-square"></i> Edit Nilai — Grand Juri</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-content">
            <div class="edit-info-banner" id="editInfo"></div>
            <div class="note-hint">
                <i class="fas fa-circle-info"></i>
                Nilai dari juri sudah terisi di setiap input. <strong>Ubah hanya komponen yang ingin diperbarui</strong>, lalu simpan. Input yang tidak diubah akan tetap menggunakan nilai juri.
            </div>
            <div class="content-grid">
                <div class="kat-list" id="editKatList"></div>
                <div id="editFormArea"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i> Batal</button>
            <button class="btn-primary" id="btnSaveGrand"><i class="fas fa-save"></i> SIMPAN PERUBAHAN</button>
        </div>
    </div>
</div>

<!-- MODAL GENERIC (reusable untuk Juri Peserta, Rincian Detail, Plot Status) -->
<div class="modal-bg" id="modalGeneric">
    <div class="modal-box" style="max-width:780px;">
        <div class="modal-head">
            <h3 id="genericTitle"><i class="fas fa-list"></i> Detail</h3>
            <button class="modal-close" onclick="closeModal('modalGeneric')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-content" id="genericContent">
            <div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('modalGeneric')"><i class="fas fa-xmark"></i> Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL: BONUS POINT -->
<div class="modal-bg" id="modalBonus" style="--mw:520px;">
    <div class="modal-box" style="--mw:520px;">
        <div class="modal-head" style="background:linear-gradient(135deg,#f8fafc,#f1f9f9);">
            <h3><i class="fas fa-trophy" style="color:#f59e0b;"></i> Kelola Bonus Point</h3>
            <button class="modal-close" onclick="closeModal('modalBonus')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="bonusModalBody"></div>
    </div>
</div>

<!-- POPUP SUKSES -->
<div class="popup-overlay" id="popupSuccess">
    <div class="popup-card">
        <div class="popup-icon success"><i class="fas fa-check"></i></div>
        <h2 class="popup-title">Nilai Berhasil Diperbarui!</h2>
        <p class="popup-desc" id="popupSuccessDesc">Perubahan telah tersimpan.</p>
        <button class="popup-btn success" onclick="hidePopup('popupSuccess')"><i class="fas fa-circle-check"></i> OK, Tutup</button>
    </div>
</div>

<!-- POPUP ERROR -->
<div class="popup-overlay" id="popupError">
    <div class="popup-card">
        <div class="popup-icon danger"><i class="fas fa-triangle-exclamation"></i></div>
        <h2 class="popup-title">Gagal Menyimpan</h2>
        <p class="popup-desc" id="popupErrorDesc">Terjadi kesalahan pada server.</p>
        <button class="popup-btn danger" onclick="hidePopup('popupError')"><i class="fas fa-rotate-right"></i> Tutup</button>
    </div>
</div>

<!-- POPUP KOSONG -->
<div class="popup-overlay" id="popupEmpty">
    <div class="popup-card">
        <div class="popup-icon warning"><i class="fas fa-exclamation"></i></div>
        <h2 class="popup-title">Tidak Ada Perubahan</h2>
        <p class="popup-desc">Ubah setidaknya satu komponen nilai sebelum menyimpan.</p>
        <button class="popup-btn warning" onclick="hidePopup('popupEmpty')"><i class="fas fa-pen"></i> Ubah Nilai</button>
    </div>
</div>

<!-- POPUP LIMIT -->
<div class="popup-overlay" id="popupLimit">
    <div class="popup-card">
        <div class="popup-icon warning"><i class="fas fa-exclamation"></i></div>
        <h2 class="popup-title">Nilai Tidak Valid</h2>
        <p class="popup-desc">Perbaiki nilai berikut sebelum menyimpan:</p>
        <ul class="err-list" id="limitList"></ul>
        <button class="popup-btn danger" onclick="hidePopup('popupLimit')"><i class="fas fa-pen"></i> Perbaiki</button>
    </div>
</div>

<!-- POPUP KONFIRMASI -->
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

<script>
/* ================================================================
   FORM FIELDS
   ================================================================ */
var formFields = {
    overall:[{id:'impression',label:'Impression',desc:'Kelipatan 5 (10-90)'}],
    head:[{id:'size',label:'Size',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk Kepala',desc:'Kelipatan 5 (10-90)'}],
    face:[{id:'face',label:'Face',desc:'Kelipatan 5 (10-90)'}],
    body:[{id:'bentuk',label:'Bentuk Badan',desc:'Kelipatan 5 (10-90)'},{id:'proporsi',label:'Proporsional',desc:'Kelipatan 5 (10-90)'},{id:'pangkal',label:'Pangkal',desc:'Kelipatan 5 (10-90)'}],
    marking:[{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'},{id:'contrast',label:'Contrast',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk',desc:'Kelipatan 5 (10-90)'}],
    pearl:[{id:'shining',label:'Shining',desc:'Kelipatan 5 (10-90)'},{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk',desc:'Kelipatan 5 (10-90)'}],
    color:[{id:'komposisi',label:'Komposisi',desc:'Kelipatan 5 (10-90)'},{id:'kecerahan',label:'Kecerahan',desc:'Kelipatan 5 (10-90)'},{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'}],
    finnage:[{id:'bentuk',label:'Bentuk Sirip & Ekor',desc:'Kelipatan 5 (10-90)'},{id:'kecerahan',label:'Kecerahan',desc:'Kelipatan 5 (10-90)'}]
};

// ★ Legacy fields untuk data lama (backward compatibility di tampilan saja)
var formFieldsLegacy = {
    face:[{id:'pipi',label:'Pipi'},{id:'mata',label:'Mata'},{id:'bibir',label:'Bibir'},{id:'kondisi',label:'Kondisi Mata & Insang'}]
};

var currentBonusIkanId = null;

var bonusTypes = [
    {key:'best_of_the_best', label:'BEST OF THE BEST', icon:'fa-gem'},
    {key:'best_of_show',     label:'BEST OF SHOW',     icon:'fa-star'},
    {key:'grand_champion',   label:'GRAND CHAMPION',   icon:'fa-crown'},
    {key:'young_champion',   label:'YOUNG CHAMPION',   icon:'fa-medal'},
    {key:'junior',           label:'JUNIOR',           icon:'fa-award'},
    {key:'baby_champion',    label:'BABY CHAMPION',    icon:'fa-baby'},
    {key:'mini_champion',    label:'MINI CHAMPION',    icon:'fa-seedling'},
];

function openBonusModalGj(id){
    currentBonusIkanId=id;
    var p=allIkanDataGJ[id];
    if(!p){
        document.getElementById('popupErrorDesc').textContent='Data ikan tidak ditemukan. Muat ulang halaman.';
        showPopup('popupError');return;
    }

    var html='';
    html+='<div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">';
    html+='<div><h4 style="font-size:14px;font-weight:800;color:#92400e;">'+esc(p.nama_peserta)+'</h4>';
    html+='<div style="font-size:11px;color:#d97706;margin-top:3px;display:flex;gap:12px;"><span><i class="fas fa-tag"></i> '+esc(p.kategori)+' - '+esc(p.kelas)+'</span><span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank||'—')+'</span></div></div>';
    html+='<div style="text-align:center;flex-shrink:0;">';
    html+='<div style="font-size:9px;color:#92400e;font-weight:800;letter-spacing:.5px;">POINT DASAR</div>';
    html+='<div style="font-size:22px;font-weight:900;color:#92400e;">'+(p.total_point||0)+'</div>';
    if(p.total_bonus>0){
        html+='<div style="font-size:9px;color:#16a34a;font-weight:800;margin-top:2px;">+ '+p.total_bonus+' BONUS</div>';
        html+='<div style="font-size:26px;font-weight:900;color:#16a34a;">'+(p.final_point||0)+'</div>';
    }
    html+='</div></div>';

    html+='<div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Pilih Bonus Point (+100 per jenis)</div>';

    var blist=p.bonus_list||[];
    bonusTypes.forEach(function(bt){
        var applied=blist.indexOf(bt.key)!==-1;
        if(applied){
            html+='<div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border:1px solid #bbf7d0;border-radius:10px;margin-bottom:5px;background:#f0fdf4;">';
            html+='<div style="display:flex;align-items:center;gap:10px;">';
            html+='<i class="fas fa-check-circle" style="color:#16a34a;font-size:15px;"></i>';
            html+='<div><div style="font-size:12px;font-weight:800;color:#16a34a;">'+bt.label+'</div><div style="font-size:10px;color:#15803d;">+100 point</div></div></div>';
            html+='<button style="padding:5px 10px;border:none;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:4px;background:#fee2e2;color:#dc2626;" onclick="removeBonusGj(\''+bt.key+'\')" onmouseover="this.style.background=\'#fecaca\'" onmouseout="this.style.background=\'#fee2e2\'"><i class="fas fa-times"></i> Hapus</button>';
            html+='</div>';
        } else {
            html+='<div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:5px;cursor:pointer;transition:all .2s;" onclick="addBonusGj(\''+bt.key+'\',this)" onmouseover="this.style.borderColor=\'#fde68a\';this.style.background=\'#fffbeb\'" onmouseout="this.style.borderColor=\'#e2e8f0\';this.style.background=\'white\'">';
            html+='<div style="display:flex;align-items:center;gap:10px;">';
            html+='<i class="fas '+bt.icon+'" style="color:#94a3b8;font-size:15px;"></i>';
            html+='<div><div style="font-size:12px;font-weight:700;color:#1e293b;">'+bt.label+'</div><div style="font-size:10px;color:#64748b;">+100 point</div></div></div>';
            html+='<i class="fas fa-plus-circle" style="color:#94a3b8;font-size:17px;"></i>';
            html+='</div>';
        }
    });

    document.getElementById('bonusModalBody').innerHTML=html;
    openModal('modalBonus');
}

function addBonusGj(type, el){
    if(!currentBonusIkanId)return;
    var fd=new FormData();
    fd.append('_token',getCsrf());
    fd.append('ikan_id',currentBonusIkanId);
    fd.append('bonus_type',type);
    fetch('/api/grand-juri/add-bonus',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){if(!r.ok)return r.json().then(function(d){throw d;});return r.json();})
    .then(function(d){
        if(d.success){
            /* ★ LANGSUNG REFRESH ranking sebelum popup */
            loadPointRanking();
            loadPeserta(document.getElementById('searchInput').value);
            document.getElementById('popupSuccessDesc').innerHTML='<b>'+esc(bonusTypes.find(function(b){return b.key===type;}).label)+'</b> (+100) berhasil ditambahkan.<br><span style="font-size:11px;color:var(--text-muted);">Ranking sudah diperbarui otomatis.</span>';
            showPopup('popupSuccess');
            closeModal('modalBonus');
        } else {
            document.getElementById('popupErrorDesc').textContent=d.message||'Terjadi kesalahan.';
            showPopup('popupError');
        }
    })
    .catch(function(e){
        var msg = (e && e.message) ? e.message : 'Kesalahan jaringan.';
        document.getElementById('popupErrorDesc').textContent=msg;
        showPopup('popupError');
    });
}

function removeBonusGj(type){
    if(!currentBonusIkanId)return;
    popupConfirm(
        'Hapus Bonus',
        'Yakin ingin menghapus bonus ini? Point yang sudah ditambahkan akan dikurangi kembali dan ranking akan otomatis berubah.',
        'Ya, Hapus',
        function(){
            var fd=new FormData();
            fd.append('_token',getCsrf());
            fd.append('ikan_id',currentBonusIkanId);
            fd.append('bonus_type',type);
            fetch('/api/grand-juri/remove-bonus',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.success){
                    /* ★ LANGSUNG REFRESH ranking sebelum popup */
                    loadPointRanking();
                    loadPeserta(document.getElementById('searchInput').value);
                    document.getElementById('popupSuccessDesc').textContent='Bonus berhasil dihapus. Ranking sudah diperbarui otomatis.';
                    showPopup('popupSuccess');
                    closeModal('modalBonus');
                } else {
                    document.getElementById('popupErrorDesc').textContent=d.message||'Terjadi kesalahan.';
                    showPopup('popupError');
                }
            })
            .catch(function(){
                document.getElementById('popupErrorDesc').textContent='Kesalahan jaringan.';
                showPopup('popupError');
            });
        }
    );
}

/* ================================================================
   STATE
   ================================================================ */
var currentId       = null;
var currentPData    = null;
var editMemory      = {};
var originalValues  = {};
var currentEditKat  = 'overall';
var allIkanDataGJ   = {};
var _confirmCallback=null;

function popupConfirm(title,desc,btnText,callback){
    document.getElementById('popupConfirmTitle').textContent=title||'Konfirmasi';
    document.getElementById('popupConfirmDesc').innerHTML=desc||'';
    document.getElementById('popupConfirmBtn').innerHTML='<i class="fas fa-check"></i> '+(btnText||'Ya, Lanjutkan');
    _confirmCallback=callback;
    showPopup('popupConfirm');
}
function executeConfirm(){hidePopup('popupConfirm');if(typeof _confirmCallback==='function')_confirmCallback();_confirmCallback=null;}
function cancelConfirm(){hidePopup('popupConfirm');_confirmCallback=null;}

/* ================================================================
   HELPERS
   ================================================================ */
function showPopup(id){document.getElementById(id).classList.add('show');}
function hidePopup(id){document.getElementById(id).classList.remove('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
function getCsrf(){return document.querySelector('meta[name="csrf-token"]').getAttribute('content');}
function esc(s){if(!s)return '';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

function openModal(id){document.getElementById(id).classList.add('show');}

['modalDetail','modalEdit','modalGeneric','modalBonus'].forEach(function(id){
    document.getElementById(id).addEventListener('click',function(e){if(e.target===this)closeModal(id);});
});
function freshMemory(){
    var m={};
    Object.keys(formFields).forEach(function(k){m[k]={};});
    return m;
}
function cloneValues(source){
    var m={};
    Object.keys(formFields).forEach(function(k){
        m[k]={};
        formFields[k].forEach(function(f){
            var v=(source&&source[k]&&source[k][f.id]);
            m[k][f.id]=(v!==undefined&&v!==null&&v!=='')?String(v):'';
        });
    });
    return m;
}

function kunciNilai(id){
    fetch('/api/grand-juri/kunci-nilai',{
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        body:JSON.stringify({_token:getCsrf(),ikan_id:id})
    })
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){
            document.getElementById('popupSuccessDesc').textContent=d.message;
            showPopup('popupSuccess');
            loadPeserta(document.getElementById('searchInput').value);
        } else {
            document.getElementById('popupErrorDesc').textContent=d.message||'Gagal.';
            showPopup('popupError');
        }
    })
    .catch(function(){
        document.getElementById('popupErrorDesc').textContent='Kesalahan jaringan.';
        showPopup('popupError');
    });
}

/* ================================================================
   LOAD STATS (rincian cards now clickable)
   ================================================================ */
function loadStats(){
    fetch('/api/grand-juri/stats',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        if(!d.total_tank&&d.total_tank!==0)return;
        document.getElementById('statTank').innerText=d.total_tank;
        document.getElementById('statPeserta').innerText=d.total_peserta;
        document.getElementById('statSudah').innerText=d.sudah_plot;
        document.getElementById('statBelum').innerText=d.belum_plot;
        document.getElementById('statSisa').innerText=d.sisa_tank;
        document.getElementById('statSisaLabel').innerText='Sisa Tank (Max '+d.max_tank+')';

        var grid=document.getElementById('rincianGrid');
        grid.innerHTML='';
        if(d.rincian)d.rincian.forEach(function(r){
            // ★ FIX: Hapus baris "Belum Dinilai", hanya tampilkan jumlah ekor
            grid.innerHTML+='<div class="rincian-card" onclick="openRincianDetail(\''+esc(r.kategori)+'\')">'+
                '<div class="rincian-cat"><i class="fas fa-arrow-up-right-from-square"></i>'+esc(r.kategori)+'</div>'+
                '<div class="rincian-data"><div class="rincian-ekor">'+r.ekor+' Ekor</div></div></div>';
        });
    });
}

/* ================================================================
   LOAD DAFTAR JURI (chips now clickable)
   ================================================================ */
function loadJuriSummary(){
    fetch('/api/grand-juri/juri-summary',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        var body=document.getElementById('juriSummaryBody');
        if(!data||data.length===0){
            body.innerHTML='<div class="empty-state"><i class="fas fa-user-slash" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada juri yang memberikan penilaian.</div>';
            return;
        }
        body.innerHTML='<div class="juri-chip-list">';
        data.forEach(function(j){
            var isGrand=j.role==='grand_juri';
            body.innerHTML+='<div class="juri-chip '+(isGrand?'juri-grand':'juri-awal')+'" '+
                'onclick="openJuriPeserta('+j.juri_id+',\''+esc(j.name)+'\',\''+j.role+'\')">'+
                '<i class="fas '+(isGrand?'fa-crown':'fa-user-pen')+'"></i>'+
                '<span class="chip-role">'+(isGrand?'Grand':'Juri')+'</span>'+
                '<span>'+esc(j.name)+'</span>'+
                '<span class="chip-count">('+j.total_peserta+')</span>'+
                '<i class="fas fa-chevron-right chip-arrow"></i>'+
                '</div>';
        });
        body.innerHTML+='</div>';
    })
    .catch(function(){
        document.getElementById('juriSummaryBody').innerHTML='<div class="empty-state">Gagal memuat data juri.</div>';
    });
}

/* ================================================================
   LOAD TABLE
   ================================================================ */
function loadPeserta(search){
    search=search||'';
    var url='/api/grand-juri/peserta'+(search?'?search='+encodeURIComponent(search):'');
    fetch(url,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        var tbody=document.getElementById('tbodyPeserta');
        tbody.innerHTML='';
        if(!data||data.length===0){
            tbody.innerHTML='<tr><td colspan="9"><div class="empty-state"><i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Tidak ada data ditemukan.</div></td></tr>';
            return;
        }
        data.forEach(function(p){
            allIkanDataGJ[p.id]=p;
            var tr=document.createElement('tr');

            var td1=document.createElement('td');
            td1.style.fontWeight='700';td1.innerText=p.nama_peserta||'—';tr.appendChild(td1);

            var td2=document.createElement('td');
            td2.style.cssText='font-size:12px;font-weight:600;color:var(--text-muted);';
            td2.innerText=(p.kategori||'—')+' - '+(p.kelas||'—');tr.appendChild(td2);

            var td3=document.createElement('td');
            td3.style.cssText='font-weight:700;color:var(--purple);';
            td3.innerText=p.nomor_tank?'Tank '+p.nomor_tank:'—';tr.appendChild(td3);

            var td4=document.createElement('td');
            td4.style.cssText='font-size:12px;color:var(--text-muted);';
            td4.innerText=p.detail_anggota||'—';tr.appendChild(td4);

            /* td5 — DINILAI OLEH (nama juri langsung, tanpa label grand edit) */
            var td5=document.createElement('td');
            if(p.juri_list&&p.juri_list.length>0){
                var jHtml='<div class="juri-cell">';
                p.juri_list.forEach(function(j){
                    if(j.is_grand){
                        jHtml+='<div class="grand-line"><i class="fas fa-crown"></i> '+esc(j.name)+'</div>';
                    } else {
                        jHtml+='<div><i class="fas fa-user-pen" style="font-size:10px;margin-right:3px;"></i>'+esc(j.name)+'</div>';
                    }
                });
                jHtml+='</div>';td5.innerHTML=jHtml;
            } else { td5.innerHTML='<span style="font-size:12px;color:var(--text-muted);">—</span>'; }
            tr.appendChild(td5);

            /* td6 — TOTAL NILAI */
            var td6=document.createElement('td');
            var totalHtml=p.total_nilai_semua>0?'<span class="total-cell">'+p.total_nilai_semua+'</span>':'<span class="total-cell zero">—</span>';
            if(p.jumlah_juri_yang_nilai>1) totalHtml+='<div style="font-size:9px;color:var(--text-muted);font-weight:600;"><i class="fas fa-users" style="font-size:8px;margin-right:2px;"></i>'+p.jumlah_juri_yang_nilai+' juri</div>';
            td6.innerHTML=totalHtml;
            tr.appendChild(td6);

            /* td6b — POINT (termasuk bonus) */
            var td6b=document.createElement('td');
            var finalPt=p.final_point||p.total_point||0;
            var bonusPt=p.total_bonus||0;
            if(finalPt>0){
                var ptHtml='<div style="font-size:16px;font-weight:900;color:'+(bonusPt>0?'#16a34a':'var(--primary)')+';">'+finalPt+'</div>';
                if(bonusPt>0) ptHtml+='<div style="font-size:9px;color:#16a34a;font-weight:800;"><i class="fas fa-trophy" style="font-size:7px;"></i> +'+bonusPt+' bonus</div>';
                td6b.innerHTML=ptHtml;
            } else {
                td6b.innerHTML='<span style="font-size:12px;color:var(--text-muted);">—</span>';
            }
            tr.appendChild(td6b);

            /* td7 — STATUS (badge saja, tanpa aksi) */
            var td7=document.createElement('td');
            if(p.is_locked){
                td7.innerHTML='<span class="badge badge-success"><i class="fas fa-lock" style="margin-right:3px;font-size:9px;"></i>NILAI FINAL</span>';
            } else if(p.grand_juri_nama){
                td7.innerHTML='<span class="badge badge-purple"><i class="fas fa-crown" style="margin-right:3px;font-size:9px;"></i>GRAND EDITED</span>';
            } else {
                td7.innerHTML='<span class="badge '+(p.status_class||'badge-success')+'">'+(p.status||'—').toUpperCase()+'</span>';
            }
            tr.appendChild(td7);

            /* td8 — AKSI */
            var td8=document.createElement('td');
            if(p.is_locked){
            td8.innerHTML='<div class="action-group">'+
                '<button class="btn-sm btn-detail" onclick="openDetail('+p.id+')"><i class="fas fa-eye"></i> Detail</button>'+
                '<button class="btn-sm" style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;min-width:auto;" onclick="openBonusModalGj('+p.id+')" title="Kelola Bonus Point"><i class="fas fa-trophy"></i></button>'+
                '<button class="btn-sm btn-lock" style="background:#eff6ff;color:#2563eb;border-color:#bfdbfe;" onclick="kunciNilai('+p.id+')" title="Buka kunci nilai"><i class="fas fa-lock-open"></i> Buka Kunci</button>'+
                '</div>';
            } else {
                var canLock=p.submitted_juri_count>=p.total_juri_all;
                var lockHtml=canLock
                    ?'<button class="btn-sm btn-lock" onclick="kunciNilai('+p.id+')" title="Semua juri sudah kirim — siap dikunci"><i class="fas fa-lock-open"></i> Kunci</button>'
                    :'<button class="btn-sm btn-lock" style="opacity:.35;cursor:not-allowed;" disabled title="Masih ada juri yang belum mengirim"><i class="fas fa-lock-open"></i> Kunci</button>';
                td8.innerHTML='<div class="action-group">'+
                    '<button class="btn-sm btn-detail" onclick="openDetail('+p.id+')"><i class="fas fa-eye"></i> Detail</button>'+
                    '<button class="btn-sm btn-edit" onclick="openEdit('+p.id+')"><i class="fas fa-pen-to-square"></i> Edit</button>'+
                    '<button class="btn-sm" style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;min-width:auto;" onclick="openBonusModalGj('+p.id+')" title="Kelola Bonus Point"><i class="fas fa-trophy"></i></button>'+
                    lockHtml+
                    '</div>';
            }
            tr.appendChild(td8);
            tbody.appendChild(tr);
        });
    })
    .catch(function(){
        document.getElementById('tbodyPeserta').innerHTML='<tr><td colspan="9"><div class="empty-state">Gagal memuat data.</div></td></tr>';
    });
}

var searchT;
document.getElementById('searchInput').addEventListener('input',function(){
    var q=this.value;clearTimeout(searchT);searchT=setTimeout(function(){loadPeserta(q);},300);
});

/* ================================================================
   FETCH SINGLE
   ================================================================ */
function fetchSingle(id,cb){
    fetch('/api/grand-juri/peserta?id='+id,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){cb(data&&data[0]?data[0]:null);})
    .catch(function(){cb(null);});
}

/* ================================================================
   MODAL DETAIL
   ================================================================ */
function openDetail(id){
    currentId=id;
    document.getElementById('detailContent').innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="font-size:20px;display:block;margin-bottom:8px;"></i>Memuat...</div>';
    document.getElementById('modalDetail').classList.add('show');
    fetchSingle(id,function(p){
        if(!p){document.getElementById('detailContent').innerHTML='<div class="empty-state">Data tidak ditemukan.</div>';return;}
        currentPData=p;
        var editBtn=document.getElementById('btnToEdit');
        if(p.is_locked){
            editBtn.style.display='none';
        } else {
            editBtn.style.display='';
            editBtn.onclick=function(){closeModal('modalDetail');openEdit(id);};
        }
        renderDetail(p);
    });
}

function renderDetail(p){
    var html='';

    html+='<div class="detail-info-banner">';
    html+='<div><h4>'+esc(p.nama_peserta)+'</h4><div class="detail-meta">';
    html+='<span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank||'—')+'</span>';
    html+='<span><i class="fas fa-tag"></i> '+(p.kategori||'—')+' - '+(p.kelas||'—')+'</span>';
    if(p.detail_anggota&&p.detail_anggota!=='—') html+='<span><i class="fas fa-users"></i> '+esc(p.detail_anggota)+'</span>';
    html+='</div></div>';
    var allIn=p.submitted_juri_count>=p.total_juri_all;
    html+='<span class="juri-submit-chip '+(allIn?'ok':'not-ok')+'"><i class="fas '+(allIn?'fa-check-circle':'fa-clock')+'"></i> '+p.submitted_juri_count+' dari '+p.total_juri_all+' juri kirim</span>';
    html+='</div>';

    if(p.is_locked){
        html+='<div class="detail-note purple-note"><i class="fas fa-lock"></i><span>Nilai ini sudah <strong>TERKUNCI (FINAL)</strong> dan tidak dapat diubah.</span></div>';
    } else if(!allIn){
        html+='<div class="detail-note"><i class="fas fa-info-circle"></i><span>Masih ada <strong>'+(p.total_juri_all-p.submitted_juri_count)+'</strong> juri yang belum mengirim. Tombol <strong>Kunci</strong> aktif setelah semua juri mengirim.</span></div>';
    }

    if(!p.all_scorings||p.all_scorings.length===0){
        html+='<div class="empty-state" style="padding:40px;"><i class="fas fa-clipboard-list" style="font-size:36px;display:block;margin-bottom:10px;color:#cbd5e1;"></i>Belum ada nilai yang dikirim juri.</div>';
        document.getElementById('detailContent').innerHTML=html;return;
    }

    /* ★ Accordion: daftar juri yang bisa diklik */
    p.all_scorings.forEach(function(sc,idx){
        var isG=sc.is_grand;
        var uid='dj-'+idx;
        var iconCls=isG?'fas fa-crown':'fas fa-user-pen';
        var label=isG?'Grand Juri: '+esc(sc.juri_name):'Juri: '+esc(sc.juri_name);

        html+='<div class="detail-juri-accordion">';
        html+='<div class="detail-juri-toggle" id="'+uid+'-toggle" onclick="toggleJuriDetail(\''+uid+'\')">';
        html+='<span class="dj-name"><i class="'+iconCls+'" style="font-size:11px;"></i> '+label+'</span>';
        html+='<span style="display:flex;align-items:center;gap:10px;"><span class="dj-total">'+sc.total_nilai+'</span><i class="fas fa-chevron-down dj-arrow"></i></span>';
        html+='</div>';

        html+='<div class="detail-juri-scores" id="'+uid+'-scores">';
        var nd=sc.nilai_detail;
        if(!nd||typeof nd!=='object'){
            html+='<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada data nilai.</div>';
        } else {
                Object.keys(formFields).forEach(function(kat){
                    // ★ BACKWARD COMPATIBILITY: Cek format lama untuk face
                    var fields = formFields[kat];
                    if (kat === 'face' && nd.face) {
                        if (nd.face.face === undefined && (nd.face.pipi !== undefined || nd.face.mata !== undefined)) {
                            fields = formFieldsLegacy.face;
                        }
                    }

                    html += '<div style="margin-bottom:10px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">';
                    
                    var katNilai=nd[kat]||{};var sub=0;
                    fields.forEach(function(f){if(katNilai[f.id]!==undefined&&katNilai[f.id]!==null)sub+=parseInt(katNilai[f.id])||0;});

                    // ★ BACA HASIL EVALUASI DEFECT DARI BACKEND
                    var defectEval = sc.defect_eval || {};
                    var penaltyKey = kat + '_penalty';
                    var penaltyStr = defectEval[penaltyKey] || '';
                    var defectPersen = 0;
                    var hasDefect = false;
                    var defectNames = [];

                    if (penaltyStr && penaltyStr !== '') {
                        hasDefect = true;
                        defectPersen = parseInt(penaltyStr) || 0;
                        var rawKey = 'raw_' + kat + '_penalty';
                        var rawDefs = sc[rawKey];
                        if (rawDefs) {
                            if (!Array.isArray(rawDefs)) rawDefs = [rawDefs];
                            defectNames = rawDefs.filter(function(v) { return v && v !== '0'; });
                        }
                    }

                    // ★ HITUNG SUBTOTAL SETELAH DEFECT
                    var displaySub = sub;
                    if (hasDefect && defectPersen > 0) {
                        displaySub = Math.round(sub * (1 - defectPersen / 100) * 10) / 10;
                    }

                    // ★ TAMPILKAN SUBTOTAL (nama komponen tetap normal, defect hanya di nilai akhir)
                    if (hasDefect && defectPersen > 0) {
                        html += '<div class="detail-kat-mini"><span>' + kat.toUpperCase() + '</span><span>Subtotal: <s style="color:#94a3b8;font-size:10px;">' + sub + '</s> → <strong style="color:var(--purple);">' + displaySub + '</strong> <span style="color:#dc2626;font-weight:700;">(-' + defectPersen + '%)</span></span></div>';
                    } else {
                        html += '<div class="detail-kat-mini"><span>' + kat.toUpperCase() + '</span><span>Subtotal: ' + sub + '</span></div>';
                    }

                    fields.forEach(function(f){
                        var val=katNilai[f.id];var has=(val!==undefined&&val!==null&&val!=='');
                        html+='<div class="detail-field-row"><div class="detail-field-left"><div class="detail-field-name">'+f.label+'</div><div class="detail-field-meta">'+f.desc+'</div></div><span class="score-chip '+(has?'filled':'empty')+'">'+(has?val:'N/A')+'</span></div>';
                    });

                    // ★ TAMPILKAN BARIS DEFECT
                    if (hasDefect && defectPersen > 0 && defectNames.length > 0) {
                        html += '<div class="detail-field-row" style="background:#fef2f2;"><div class="detail-field-left"><div class="detail-field-name" style="color:#dc2626;"><i class="fas fa-exclamation-triangle" style="margin-right:4px;font-size:10px;"></i>Defect</div><div class="detail-field-meta" style="color:#b91c1c;font-weight:600;">' + defectNames.join(', ') + '</div></div><span class="score-chip" style="background:#fef2f2;color:#dc2626;font-weight:800;">-' + defectPersen + '%</span></div>';
                    }
                    html += '</div>';
                });
        }
        html+='</div></div>';
    });

        // ★ TAMBAHAN: Ringkasan Nilai dari Semua Juri + Total Point
    if(p.detail_list_per_juri && p.detail_list_per_juri.length > 0) {
        html += '<div style="margin-top:16px;border:2px solid #ddd6fe;border-radius:12px;overflow:hidden;">';
        html += '<div style="padding:12px 16px;background:linear-gradient(135deg,#f5f3ff,#ede9fe);border-bottom:1px solid #ddd6fe;display:flex;justify-content:space-between;align-items:center;">';
        html += '<div><span style="font-size:13px;font-weight:800;color:#4c1d95;"><i class="fas fa-calculator" style="margin-right:6px;"></i>Ringkasan Nilai & Point</span></div>';
        html += '<span style="font-size:11px;color:#7c3aed;font-weight:700;">'+p.jumlah_juri_yang_nilai+' juri</span>';
        html += '</div>';
        html += '<div style="padding:16px;">';

        html += '<table style="width:100%;border-collapse:collapse;font-size:12px;">';
        html += '<tr style="background:#f5f3ff;"><th style="padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#6d28d9;text-transform:uppercase;">JURI</th><th style="padding:8px 12px;text-align:right;font-size:10px;font-weight:700;color:#6d28d9;text-transform:uppercase;">TOTAL NILAI</th></tr>';
        
        var grandTotalNilai = 0;
        p.detail_list_per_juri.forEach(function(j) {
            if (!j.is_grand) {
                grandTotalNilai += j.total_nilai;
                html += '<tr><td style="font-weight:600;">' + esc(j.juri_name) + '</td>';
                html += '<td style="font-weight:800;text-align:right;">' + j.total_nilai + '</td></tr>';
            }
        });
        html += '<tr style="background:#ede9fe;"><td style="font-weight:800;color:#4c1d95;"><i class="fas fa-crown" style="margin-right:4px;font-size:10px;"></i> TOTAL SEMUA JURI</td>';
        html += '<td style="font-weight:900;text-align:right;color:#4c1d95;font-size:14px;">' + grandTotalNilai + '</td></tr>';
        html += '</table>';

        /* ★ Total Point dengan bonus */
        var basePoint = p.total_point ?? 0;
        var bonusTotal = p.total_bonus || 0;
        var finalPoint = p.final_point || basePoint;

        html += '<div style="margin-top:16px;background:linear-gradient(135deg,#fef3c7,#fef9c3);border:1px solid #fde68a;border-radius:10px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;">';
        html += '<div><div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;">Total Point</div><div style="font-size:10px;color:#d97706;">(dihitung dari rata-rata '+p.jumlah_juri_yang_nilai+' juri)</div>';
        if(bonusTotal > 0) html += '<div style="font-size:9px;color:#16a34a;font-weight:700;margin-top:2px;">+ '+bonusTotal+' bonus point</div>';
        html += '</div>';
        html += '<div style="text-align:right;"><div style="font-size:22px;font-weight:900;color:'+(bonusTotal>0?'#16a34a':'#d97706')+';">'+finalPoint+'</div>';
        if(bonusTotal > 0) html += '<div style="font-size:10px;color:#92400e;font-weight:600;">(dasar '+basePoint+' + '+bonusTotal+')</div>';
        html += '</div></div></div>';

        /* ★ Daftar bonus yang diberikan */
        if(p.bonus_list && p.bonus_list.length > 0){
            html += '<div style="margin-top:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;">';
            html += '<div style="font-size:11px;font-weight:800;color:#166534;text-transform:uppercase;margin-bottom:8px;"><i class="fas fa-trophy" style="color:#f59e0b;margin-right:4px;"></i>Bonus Point Diberikan</div>';
            p.bonus_list.forEach(function(btKey){
                var btInfo = bonusTypes.find(function(b){return b.key===btKey;});
                if(btInfo){
                    html += '<div style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:#dcfce7;border-radius:8px;margin:0 6px 6px 0;font-size:11px;font-weight:700;color:#16a34a;">';
                    html += '<i class="fas '+btInfo.icon+'" style="font-size:10px;"></i> '+btInfo.label+' (+100)';
                    html += '</div>';
                }
            });
            html += '</div>';
        }
    }

    document.getElementById('detailContent').innerHTML=html;
}

function toggleJuriDetail(uid){
    var t=document.getElementById(uid+'-toggle');
    var s=document.getElementById(uid+'-scores');
    if(t.classList.contains('open')){
        t.classList.remove('open');s.classList.remove('open');
    } else {
        t.classList.add('open');s.classList.add('open');
    }
}
/* ================================================================
   MODAL EDIT — PRE-FILL DARI NILAI JURI
   ================================================================ */
function openEdit(id){
    currentId=id;currentEditKat='overall';

    fetchSingle(id,function(p){
        if(!p){
            document.getElementById('popupErrorDesc').textContent='Data peserta tidak ditemukan.';
            showPopup('popupError');return;
        }
        if(p.is_locked){
            document.getElementById('popupErrorDesc').innerHTML='Nilai ini sudah <b>TERKUNCI (FINAL)</b> dan tidak dapat diubah.';
            showPopup('popupError');return;
        }
        currentPData=p;

        if(p.nilai_detail&&typeof p.nilai_detail==='object'){
            editMemory=cloneValues(p.nilai_detail);
        } else { editMemory=freshMemory(); }
        originalValues=cloneValues(editMemory);

        var info='<b>'+esc(p.nama_peserta)+'</b> — Tank '+(p.nomor_tank||'—');
        info+='<br><span style="font-size:11px;color:#6d28d9;">'+esc(p.kategori)+' - '+(p.kelas||'—')+' | '+esc(p.detail_anggota||'—')+'</span>';
        if(p.juri_nama&&p.juri_nama!=='—'){
            info+='<br><span style="font-size:11px;"><i class="fas fa-user-pen" style="margin-right:3px;"></i> Nilai asli dari: <b>'+esc(p.juri_nama)+'</b></span>';
        } else {
            info+='<br><span style="font-size:11px;color:var(--warning);"><i class="fas fa-info-circle" style="margin-right:3px;"></i> Belum ada nilai dari juri — Grand Juri menginput baru</span>';
        }
        if(p.grand_juri_nama){
            info+='<br><span style="font-size:11px;color:var(--purple);"><i class="fas fa-crown" style="margin-right:3px;"></i> Terakhir diedit oleh: <b>'+esc(p.grand_juri_nama)+'</b></span>';
        }
        document.getElementById('editInfo').innerHTML=info;

        renderEditList();renderEditInputs('overall');
        document.getElementById('modalEdit').classList.add('show');
    });
}

function renderEditList(){
    var c=document.getElementById('editKatList');c.innerHTML='';
    Object.keys(formFields).forEach(function(kat){
        var changes=countChanges(kat);
        var btn=document.createElement('button');
        btn.className='kat-btn'+(kat===currentEditKat?' active':'');
        btn.innerHTML='<span>'+kat.charAt(0).toUpperCase()+kat.slice(1)+'</span>'+
            '<span class="kat-badge'+(changes>0?' has-changes':'')+'">'+(changes>0?changes:'—')+'</span>';
        btn.onclick=function(){switchEditKat(kat);};c.appendChild(btn);
    });
}

function countChanges(kat){
    if(!editMemory[kat]||!originalValues[kat])return 0;var n=0;
    formFields[kat].forEach(function(f){
        var cur=String(editMemory[kat][f.id]||'');var ori=String(originalValues[kat][f.id]||'');
        if(cur!==''&&cur!==ori)n++;
    });return n;
}

function switchEditKat(kat){saveCurrentTab();currentEditKat=kat;renderEditList();renderEditInputs(kat);}

function renderEditInputs(kat){
    if(!editMemory[kat])editMemory[kat]={};if(!originalValues[kat])originalValues[kat]={};
    var html='';
    formFields[kat].forEach(function(f){
        var currentVal=editMemory[kat][f.id]||'';var origVal=originalValues[kat][f.id]||'';
        var isChanged=(currentVal!==''&&currentVal!==origVal);
        html+='<div class="score-row"><div class="score-label"><h4>'+f.label+'</h4>';
        html+='<p>'+f.desc;
        if(origVal!=='') html+=' &nbsp;|&nbsp; <span class="orig-val">Nilai juri: <strong>'+origVal+'</strong></span>';
        html+='</p></div>';
        html+='<input type="number" class="score-input'+(isChanged?' changed':'')+'" id="edit-'+f.id+'" '+
            'value="'+currentVal+'" min="0" max="'+f.max+'" '+
            'oninput="onInput(this,\''+kat+'\',\''+f.id+'\','+f.max+')"></div>';
    });
    html+='<div class="subtotal-bar">Subtotal <em>'+kat+'</em>: <span id="subVal">0</span></div>';
    document.getElementById('editFormArea').innerHTML=html;updateSub(kat);
}

function onInput(el,kat,fid){
    var cur=el.value;var ori=originalValues[kat]?String(originalValues[kat][fid]||''):'';
    el.classList.remove('changed');if(cur!==''&&cur!==ori)el.classList.add('changed');
    var v=parseInt(cur);
    if(!isNaN(v)&&v<0){v=0;el.value=v;}
    if(!editMemory[kat])editMemory[kat]={};editMemory[kat][fid]=el.value;updateSub(kat);renderEditList();
}

function updateSub(kat){
    var t=0;formFields[kat].forEach(function(f){var el=document.getElementById('edit-'+f.id);if(el&&el.value!=='')t+=parseInt(el.value)||0;});
    var s=document.getElementById('subVal');if(s)s.textContent=t;
}

function saveCurrentTab(){
    if(!formFields[currentEditKat])return;if(!editMemory[currentEditKat])editMemory[currentEditKat]={};
    formFields[currentEditKat].forEach(function(f){var el=document.getElementById('edit-'+f.id);if(el)editMemory[currentEditKat][f.id]=el.value;});
}

/* ================================================================
   SUBMIT — BUG FIX: kirim ikan_id bukan peserta_id
   ================================================================ */
document.getElementById('btnSaveGrand').addEventListener('click',function(){submitEdit();});

function submitEdit(){
    saveCurrentTab();
    var payload={};var limitErrors=[];var totalChanged=0;

    Object.keys(formFields).forEach(function(kat){
        formFields[kat].forEach(function(f){
            var cur=editMemory[kat]?editMemory[kat][f.id]:'';var ori=originalValues[kat]?originalValues[kat][f.id]:'';
            if(cur===''&&ori==='')return;if(String(cur)===String(ori))return;
            var val=parseInt(cur);if(isNaN(val)){return;}
            if(val<0){limitErrors.push(f.label+' ('+kat+'): tidak boleh negatif');return;}
            if(val>f.max){limitErrors.push(f.label+' ('+kat+'): maks '+f.max+', diisi '+val);return;}
            if(!payload[kat])payload[kat]={};payload[kat][f.id]=val;totalChanged++;
        });
    });

    if(totalChanged===0&&limitErrors.length===0){showPopup('popupEmpty');return;}
    if(limitErrors.length>0){
        var ul=document.getElementById('limitList');ul.innerHTML='';
        limitErrors.forEach(function(e){ul.innerHTML+='<li class="err-item"><i class="fas fa-circle-xmark"></i><span>'+e+'</span></li>';});
        showPopup('popupLimit');return;
    }

    var btn=document.getElementById('btnSaveGrand');
    btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MENYIMPAN...';

    /* ★ BUG FIX: ikan_id (bukan peserta_id) sesuai yang controller expect */
    fetch('/api/grand-juri/edit-nilai',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'Accept':'application/json',
            'X-Requested-With':'XMLHttpRequest'
        },
        body:JSON.stringify({
            _token:getCsrf(),
            ikan_id:currentId,
            changed_fields:payload
        })
    })
    .then(function(res){
        if(!res.ok)return res.json().then(function(d){d._err=true;return d;});
        return res.json();
    })
    .then(function(d){
        if(d._err||!d.success){
            document.getElementById('popupErrorDesc').textContent=d.message||'Terjadi kesalahan pada server.';
            showPopup('popupError');return;
        }
        closeModal('modalEdit');
        document.getElementById('popupSuccessDesc').innerHTML=
            'Total nilai akhir: <strong style="color:var(--purple);font-size:18px;">'+d.total+'</strong><br>'+
            '<span style="font-size:12px;color:var(--text-muted);">'+totalChanged+' komponen diperbarui</span>';
        showPopup('popupSuccess');
        loadPeserta(document.getElementById('searchInput').value);loadStats();loadJuriSummary();
    })
    .catch(function(){
        document.getElementById('popupErrorDesc').textContent='Kesalahan jaringan. Periksa koneksi Anda.';
        showPopup('popupError');
    })
    .finally(function(){btn.disabled=false;btn.innerHTML='<i class="fas fa-save"></i> SIMPAN PERUBAHAN';});
}

/* ================================================================
   GENERIC MODAL — JURI PESERTA
   ================================================================ */
function openJuriPeserta(juriId, juriName, role){
    var isGrand = role==='grand_juri';
    document.getElementById('genericTitle').innerHTML='<i class="fas '+(isGrand?'fa-crown':'fa-user-pen')+'"></i> Peserta yang Dinilai — '+esc(juriName);

    var content = document.getElementById('genericContent');
    content.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>';
    document.getElementById('modalGeneric').classList.add('show');

    fetch('/api/grand-juri/juri-peserta?juri_id='+juriId+'&role='+role,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        if(!data||data.length===0){
            content.innerHTML='<div class="empty-state"><i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Tidak ada data ditemukan.</div>';
            return;
        }
        var html='<div class="detail-note purple-note"><i class="fas fa-circle-info"></i><span>'+
            (isGrand?'Grand Juri':'Juri')+' <strong>'+esc(juriName)+'</strong> menilai <strong>'+data.length+'</strong> peserta.</span></div>';
        html+='<div class="table-wrap"><table class="gen-table"><thead><tr>'+
            '<th>NO</th><th>PESERTA</th><th>NO. TANK</th><th>KATEGORI</th><th>TOTAL NILAI</th>'+
            '</tr></thead><tbody>';
        data.forEach(function(item,i){
            html+='<tr><td style="color:var(--text-muted);font-weight:700;">'+(i+1)+'</td>'+
                '<td style="font-weight:700;">'+esc(item.nama_peserta)+'</td>'+
                '<td class="g-tank">'+esc(item.nomor_tank)+'</td>'+
                '<td style="font-size:12px;color:var(--text-muted);">'+esc(item.kategori)+'</td>'+
                '<td class="g-total">'+item.total_nilai+'</td></tr>';
        });
        html+='</tbody></table></div>';
        content.innerHTML=html;
    })
    .catch(function(){
        content.innerHTML='<div class="empty-state">Gagal memuat data.</div>';
    });
}

/* ================================================================
   GENERIC MODAL — RINCIAN DETAIL (Sudah / Belum Dinilai)
   ================================================================ */
function openRincianDetail(kategori){
    document.getElementById('genericTitle').innerHTML='<i class="fas fa-chart-bar"></i> Detail Kategori: '+esc(kategori);

    var content = document.getElementById('genericContent');
    content.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>';
    document.getElementById('modalGeneric').classList.add('show');

    fetch('/api/grand-juri/rincian-detail?kategori='+encodeURIComponent(kategori),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        if(!data){
            content.innerHTML='<div class="empty-state">Data tidak ditemukan.</div>';return;
        }

        // ★ FIX: Tanpa panel "Belum Dinilai", hanya tabel data yang sudah dinilai
        var html='<div class="detail-note purple-note"><i class="fas fa-circle-info"></i><span>'+
            'Kategori <strong>'+esc(kategori)+'</strong> — Total <strong>'+data.total_ekor+'</strong> ekor sudah dinilai.</span></div>';

        html+='<div class="table-wrap"><table class="gen-table"><thead><tr>'+
            '<th>NO</th><th>PESERTA</th><th>NO. TANK</th><th>DINILAI OLEH</th><th>TOTAL NILAI</th>'+
            '</tr></thead><tbody>';

        if(!data.data||data.data.length===0){
            html+='<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">Tidak ada data ditemukan.</td></tr>';
        } else {
            data.data.forEach(function(item,i){
                html+='<tr><td style="color:var(--text-muted);font-weight:700;">'+(i+1)+'</td>'+
                    '<td style="font-weight:700;">'+esc(item.nama_peserta)+'</td>'+
                    '<td class="g-tank">'+esc(item.nomor_tank)+'</td>'+
                    '<td class="g-juri">'+esc(item.juri_nama)+'</td>'+
                    '<td class="g-total">'+item.total_nilai+'</td></tr>';
            });
        }

        html+='</tbody></table></div>';
        content.innerHTML=html;
    })
    .catch(function(){
        content.innerHTML='<div class="empty-state">Gagal memuat data.</div>';
    });
}

/* ================================================================
   GENERIC MODAL — PLOT STATUS (Sudah / Belum Plot)
   ================================================================ */
function openPlotStatus(status){
    var isSudah = status==='sudah_plot';
    var label = isSudah ? 'Sudah Plot' : 'Belum Plot';
    var icon = isSudah ? 'fa-check-double' : 'fa-clock';
    var noteColor = isSudah ? 'purple-note' : '';

    document.getElementById('genericTitle').innerHTML='<i class="fas '+icon+'"></i> Daftar Peserta '+label;

    var content = document.getElementById('genericContent');
    content.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>';
    document.getElementById('modalGeneric').classList.add('show');

    fetch('/api/grand-juri/plot-status?status='+status,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        if(!data||data.length===0){
            content.innerHTML='<div class="empty-state"><i class="fas '+(isSudah?'fa-check-double':'fa-inbox')+'" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>'+
                (isSudah?'Belum ada peserta yang sudah di-plot.':'Tidak ada peserta yang belum di-plot — semua sudah!')+'</div>';
            return;
        }

        var html='<div class="detail-note '+noteColor+'"><i class="fas '+(isSudah?'fa-check-circle':'fa-info-circle')+'"></i><span>'+
            'Menampilkan <strong>'+data.length+'</strong> peserta yang <strong>'+label.toLowerCase()+'</strong>.'+
            '</span></div>';

        html+='<div class="table-wrap"><table class="gen-table"><thead><tr>'+
            '<th>NO</th><th>PESERTA</th><th>NO. TANK</th><th>KATEGORI</th><th>KELAS</th><th>ASAL</th>';
        if(isSudah) html+='<th>DINILAI OLEH</th><th>TOTAL</th>';
        html+='</tr></thead><tbody>';

        data.forEach(function(item,i){
            html+='<tr><td style="color:var(--text-muted);font-weight:700;">'+(i+1)+'</td>'+
                '<td style="font-weight:700;">'+esc(item.nama_peserta)+'</td>'+
                '<td class="g-tank">'+esc(item.nomor_tank)+'</td>'+
                '<td style="font-size:12px;color:var(--text-muted);">'+esc(item.kategori)+'</td>'+
                '<td style="font-size:12px;">'+esc(item.kelas)+'</td>'+
                '<td style="font-size:11px;color:var(--text-muted);">'+esc(item.detail_anggota)+'</td>';
            if(isSudah){
                html+='<td class="g-juri">'+esc(item.juri_nama)+'</td>'+
                    '<td class="g-total">'+item.total_nilai+'</td>';
            }
            html+='</tr>';
        });

        html+='</tbody></table></div>';
        content.innerHTML=html;
    })
    .catch(function(){
        content.innerHTML='<div class="empty-state">Gagal memuat data.</div>';
    });
}

function loadMvpGj() {
    fetch('/api/grand-juri/mvp-ikan', {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(data => {
        var tb = document.getElementById('tbodyMvp');
        if(!data || data.length === 0) {
            tb.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-star" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada peserta yang mengirimkan data MVP.</div></td></tr>';
            return;
        }
        tb.innerHTML = '';
        data.forEach(function(p, idx) {
            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:700;">${esc(p.nama_peserta)}</td>
                <td style="font-size:12px; color:var(--text-muted);">${esc(p.detail_anggota)}</td>
                <td><span class="badge badge-purple"><i class="fas fa-star" style="margin-right:3px; font-size:9px; color:#f59e0b;"></i>${p.total_mvp} Ikan MVP</span></td>
                <td><span class="badge badge-success"><i class="fas fa-paper-plane" style="margin-right:3px; font-size:9px;"></i>TERKIRIM</span></td>
                <td><button class="btn-sm btn-detail" onclick="openMvpDetail(${idx})"><i class="fas fa-eye"></i> Lihat Ikan</button></td>
            `;
            tb.appendChild(tr);
        });
        
        // Simpan data sementara untuk modal detail
        window._mvpData = data;
    })
    .catch(function(){
        document.getElementById('tbodyMvp').innerHTML = '<tr><td colspan="5"><div class="empty-state">Gagal memuat data MVP.</div></td></tr>';
    });
}

function openMvpDetail(idx) {
    var p = window._mvpData[idx];
    if(!p) return;
    
    var html = `<div class="detail-note purple-note"><i class="fas fa-circle-info"></i><span>Peserta <strong>${esc(p.nama_peserta)}</strong> (${esc(p.detail_anggota)}) mendaftarkan <strong>${p.total_mvp}</strong> ikan MVP.</span></div>`;
    html += '<table class="gen-table"><thead><tr><th>NO</th><th>KATEGORI</th><th>KELAS</th><th>NO. TANK</th></tr></thead><tbody>';
    
    p.ikans.forEach(function(ikan, i) {
        html += `<tr>
            <td style="color:var(--text-muted);font-weight:700;">${i+1}</td>
            <td style="font-weight:600;">${esc(ikan.kategori)}</td>
            <td>${esc(ikan.kelas)}</td>
            <td style="font-weight:700; color:var(--purple);">${ikan.nomor_tank ? 'Tank '+ikan.nomor_tank : '—'}</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    
    document.getElementById('genericTitle').innerHTML = '<i class="fas fa-star" style="color:#f59e0b;"></i> Detail Ikan MVP - ' + esc(p.nama_peserta);
    document.getElementById('genericContent').innerHTML = html;
    document.getElementById('modalGeneric').classList.add('show');
}

var pointScope = 'per_kategori_kelas';

function setPointScope(s) {
    pointScope = s;
    document.getElementById('btnScopeKelas').className = 'btn-sm ' + (s === 'per_kategori_kelas' ? 'btn-edit' : 'btn-detail');
    document.getElementById('btnScopeKat').className = 'btn-sm ' + (s === 'per_kategori' ? 'btn-edit' : 'btn-detail');
    document.getElementById('btnScopeGlobal').className = 'btn-sm ' + (s === 'global' ? 'btn-edit' : 'btn-detail');
    if (s !== 'global') {
        document.getElementById('btnScopeGlobal').style.cssText = 'font-size:11px;padding:7px 14px;background:var(--warning-lt);color:#d97706;border:1px solid #fde68a;';
    }
    document.getElementById('pointFilterKategori').style.display = (s === 'global') ? 'none' : '';
    document.getElementById('pointFilterKelas').style.display = (s === 'global') ? 'none' : '';
    document.getElementById('globalTopNWrap').style.display = (s === 'global') ? 'flex' : 'none';
    loadPointRanking();
}

function setGlobalTopN(n) {
    document.getElementById('globalTopN').value = n;
    loadPointRanking();
}

function loadPointRanking() {
    var kat = document.getElementById('pointFilterKategori').value;
    var kelas = document.getElementById('pointFilterKelas').value;
    var params = '?scope=' + pointScope;
    if (kat) params += '&kategori=' + encodeURIComponent(kat);
    if (kelas) params += '&kelas=' + kelas;
    if (pointScope === 'global') {
        var topN = parseInt(document.getElementById('globalTopN').value) || 10;
        topN = Math.max(1, Math.min(100, topN));
        document.getElementById('globalTopN').value = topN;
        params += '&limit=' + topN;
    }

    var el = document.getElementById('pointRankingContent');
    el.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat ranking...</p></div>';

    fetch('/api/grand-juri/point-ranking' + params, {headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(groups){
        if(!groups||!groups.length){
            el.innerHTML = '<div class="empty-state"><i class="fas fa-trophy" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada data ikan yang dikunci.</div>';
            return;
        }
        var isGlobal = (pointScope === 'global');
        var html = '';

        groups.forEach(function(g){
            html += '<div style="margin-bottom:20px;">';
            /* Header group */
            html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a;border-radius:10px 10px 0 0;">';
            html += '<div class="card-title" style="font-size:13px;margin:0;"><i class="fas ' + (isGlobal ? 'fa-globe' : 'fa-layer-group') + '" style="color:#f59e0b;"></i> ' + esc(g.group_name) + '</div>';
            html += '<span style="font-size:11px;color:#92400e;font-weight:700;">' + g.total + ' peserta</span>';
            html += '</div>';

            /* Tabel */
            html += '<div class="table-wrap" style="border-radius:0 0 10px 10px;border:1px solid #fde68a;border-top:none;"><table class="result-table" style="min-width:800px;">';
            html += '<thead><tr><th style="width:40px;text-align:center;">#</th><th>PESERTA</th>' + (isGlobal ? '<th>KATEGORI</th>' : '') + '<th style="width:70px;">TANK</th><th style="width:50px;">KELAS</th><th>ASAL/TEAM</th><th style="width:90px;text-align:center;">TOTAL NILAI</th><th style="width:90px;text-align:center;">POINT</th><th style="width:80px;text-align:center;">RANK</th></tr></thead>';
            html += '<tbody>';

            g.data.forEach(function(d, i){
                var rank = d.rank_point ?? 0; /* ★ Sudah bilangan bulat dari helper */
                var fp = d.final_point ?? 0;
                var bonus = d.total_bonus || 0;
                var posisi = i + 1;

                /* Warna badge rank */
                var rankBg, rankColor, rankBorder;
                if (rank >= 98) {
                    rankBg = 'linear-gradient(135deg,#dcfce7,#bbf7d0)';
                    rankColor = '#16a34a';
                    rankBorder = '#86efac';
                } else if (rank >= 90) {
                    rankBg = 'linear-gradient(135deg,#fef3c7,#fde68a)';
                    rankColor = '#d97706';
                    rankBorder = '#fcd34d';
                } else if (rank >= 75) {
                    rankBg = 'linear-gradient(135deg,#e0e7ff,#c7d2fe)';
                    rankColor = '#4f46e5';
                    rankBorder = '#a5b4fc';
                } else {
                    rankBg = '#f1f5f9';
                    rankColor = '#64748b';
                    rankBorder = '#e2e8f0';
                }

                /* Medal untuk rank 100, 99, 98 */
                var medalHtml = '';
                if (rank === 100) medalHtml = '<i class="fas fa-medal" style="color:#f59e0b;font-size:12px;margin-right:4px;" title="Rank 100"></i>';
                else if (rank === 99) medalHtml = '<i class="fas fa-medal" style="color:#94a3b8;font-size:12px;margin-right:4px;" title="Rank 99"></i>';
                else if (rank === 98) medalHtml = '<i class="fas fa-medal" style="color:#d97706;font-size:12px;margin-right:4px;" title="Rank 98"></i>';

                html += '<tr>';
                html += '<td style="text-align:center;font-weight:800;color:var(--text-muted);">' + posisi + '</td>';
                html += '<td style="font-weight:700;">' + medalHtml + esc(d.nama_peserta) + '</td>';
                if (isGlobal) html += '<td style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">' + esc(d.kategori) + '</td>';
                html += '<td style="font-weight:700;color:var(--purple);text-align:center;">' + (d.nomor_tank || '—') + '</td>';
                html += '<td style="text-align:center;">' + esc(d.kelas) + '</td>';
                html += '<td style="font-size:11px;color:var(--text-muted);">' + esc(d.detail_anggota) + '</td>';
                /* Total Nilai */
                html += '<td style="text-align:center;"><div style="font-weight:800;">' + (d.total_nilai_semua ?? 0) + '</div>';
                if (d.jumlah_juri > 0) {
                    html += '<div style="font-size:9px;color:var(--text-muted);font-weight:600;">' + d.jumlah_juri + ' juri</div>';
                }
                html += '</td>';
                /* Point */
                html += '<td style="text-align:center;">';
                html += '<div style="font-weight:900;font-size:15px;color:' + (bonus > 0 ? '#16a34a' : 'var(--primary)') + ';">' + fp + '</div>';
                if (bonus > 0) {
                    html += '<div style="font-size:9px;color:#16a34a;font-weight:800;"><i class="fas fa-trophy" style="font-size:7px;"></i> +' + bonus + '</div>';
                }
                html += '</td>';
                /* ★ RANK: bilangan bulat, bukan desimal */
                html += '<td style="text-align:center;"><span style="display:inline-block;padding:5px 14px;border-radius:8px;font-size:14px;font-weight:900;background:' + rankBg + ';color:' + rankColor + ';border:1px solid ' + rankBorder + ';">' + rank + '</span></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div></div>';
        });
        el.innerHTML = html;
    })
    .catch(function(){
        el.innerHTML = '<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation" style="font-size:28px;display:block;margin-bottom:8px;"></i>Gagal memuat data ranking.</div>';
    });
}

function doExport(sheets){
    document.getElementById('exportDD').classList.remove('show');
    window.location.href='/api/grand-juri/export?sheets='+sheets;
}
document.addEventListener('click',function(e){
    if(!e.target.closest('.export-wrap')){
        document.getElementById('exportDD').classList.remove('show');
    }
});

/* ================================================================
   INIT
   ================================================================ */
loadStats();
loadPeserta();
loadJuriSummary();
loadMvpGj(); // ★ TAMBAHKAN BARIS INI AGAR MVP TERLOAD SAAT BUKA HALAMAN
loadPointRanking();
</script>
</body>
</html>