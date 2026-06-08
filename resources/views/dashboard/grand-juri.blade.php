<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <meta name="theme-color" content="#0B1220">
    <title>Grand Juri — LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

        /* ═══ GANTI BLOK :root INI ═══ */
        :root{
            --bg-main:#0B1220;--card:rgba(17,30,54,0.85);--primary:#22D3EE;--primary-light:rgba(34,211,238,0.10);--primary-lt:rgba(34,211,238,0.12);
            --text:#E2E8F0;--text-main:#F8FAFC;--text-muted:#94A3B8;--border:rgba(255,255,255,0.08);
            --success:#10B981;--success-lt:rgba(16,185,129,0.12);--danger:#EF4444;--danger-lt:rgba(239,68,68,0.12);
            --warning:#F59E0B;--warning-lt:rgba(245,158,11,0.12);
            --purple:#A78BFA;--purple-light:rgba(124,58,237,0.10);--purple-lt:rgba(124,58,237,0.12);
            --ocean-950:#04070F;--ocean-900:#0B1220;--ocean-850:#0E1729;--ocean-800:#111E36;--ocean-700:#182947;
            --cyan-500:#06B6D4;--cyan-400:#22D3EE;--cyan-300:#67E8F9;--cyan-200:#A5F3FC;
            --gold-700:#B45309;--gold-600:#D97706;--gold-500:#F59E0B;--gold-400:#FBBF24;--gold-300:#FCD34D;
            --glass-1:rgba(255,255,255,0.03);--glass-2:rgba(255,255,255,0.05);--glass-3:rgba(255,255,255,0.08);--glass-strong:rgba(255,255,255,0.12);
            --bd-1:rgba(255,255,255,0.06);--bd-2:rgba(255,255,255,0.10);--bd-3:rgba(255,255,255,0.16);
            --text-hi:#F8FAFC;--text-low:#64748B;--text-faint:#475569;
            --sidebar-w:260px;
        }

        /* ═══ TAMBAHKAN INI tepat di bawah blok :root ═══ */
        html { color-scheme: dark; }

        /* Safety net: pastikan SEMUA container punya warna teks terang */
        .popup-card,
        .popup-overlay,
        .modal-box,
        .modal-content,
        .modal-body,
        .modal-footer,
        .card,
        .card-body,
        .card-header,
        .detail-info-banner,
        .edit-info-banner,
        .gen-table,
        .result-table,
        .gj-defect-options,
        .empty-state { color: var(--text-hi); }

        /* Override spesifik yang tetap perlu muted */
        .popup-desc,
        .card-subtitle,
        .detail-field-meta,
        .detail-kat-mini,
        .g-juri,
        .gen-table .g-juri,
        .result-table td[style*="text-muted"],
        .result-table td[style*="color:var(--text-muted)"] { color: var(--text-muted) !important; }

        /* Pastikan semua td tabel inherit warna terang */
        .result-table td,
        .gen-table td { color: var(--text-hi); }

        /* Tabel header tetap muted */
        .result-table th,
        .gen-table th { color: var(--text-muted); }

        /* Error list item span */
        .err-item span { color: var(--text-hi) !important; }

        /* Defect option text */
        .gj-defect-option span { color: var(--text-hi); }

        /* Popup title dan desc override pasti terang */
        .popup-title { color: var(--text-hi) !important; }
        .popup-desc { color: var(--text-muted) !important; }

        /* Confirm popup desc */
        #popupConfirmDesc { color: var(--text-muted) !important; }
        #popupConfirmDesc strong,
        #popupConfirmDesc b { color: var(--text-hi) !important; }

        /* Success/error popup desc — innerHTML sering pakai <b> tanpa warna */
        #popupSuccessDesc,
        #popupErrorDesc { color: var(--text-muted) !important; }
        #popupSuccessDesc b,
        #popupSuccessDesc strong,
        #popupErrorDesc b,
        #popupErrorDesc strong { color: var(--text-hi) !important; }
        #popupSuccessDesc span,
        #popupErrorDesc span { color: var(--text-muted) !important; }

        /* Detail modal — nama peserta dan meta */
        .detail-info-banner h4 { color: var(--text-hi) !important; }
        .detail-info-banner .detail-meta span { color: var(--purple) !important; }
        .detail-field-name { color: var(--text-hi) !important; }
        .detail-field-meta { color: var(--text-muted) !important; }

        /* Juri accordion toggle */
        .dj-name { color: var(--text-hi) !important; }
        .dj-total { color: var(--purple) !important; }

        /* Score chip filled — pasti terang */
        .score-chip.filled { color: var(--purple) !important; }
        .score-chip.empty { color: var(--text-faint) !important; }

        /* Subtotal bar */
        .subtotal-bar { color: var(--purple) !important; }

        /* Kat button label */
        .kat-btn { color: var(--text-muted); }
        .kat-btn.active { color: var(--purple); }

        /* Score label */
        .score-label h4 { color: var(--text-hi) !important; }
        .score-label p { color: var(--text-muted) !important; }

        /* Gen table specific overrides */
        .gen-table .g-total { color: var(--purple) !important; }
        .gen-table .g-tank { color: var(--purple) !important; }

        /* Result table — total cell dan juri cell */
        .total-cell { color: var(--purple) !important; }
        .total-cell.zero { color: var(--text-faint) !important; }
        .juri-cell { color: var(--primary) !important; }
        .juri-cell div { color: var(--primary) !important; }
        .juri-cell .grand-line { color: var(--purple) !important; }

        /* Badge overrides — pasti terbaca */
        .badge-success { color: #34D399 !important; background: var(--success-lt) !important; }
        .badge-warning { color: var(--gold-300) !important; background: var(--warning-lt) !important; }
        .badge-purple { color: var(--purple) !important; background: var(--purple-light) !important; }
        .juri-submit-chip.ok { color: #34D399 !important; background: var(--success-lt) !important; }
        .juri-submit-chip.not-ok { color: var(--gold-300) !important; background: var(--warning-lt) !important; }

        /* Rincian card */
        .rincian-cat { color: var(--text-hi) !important; }
        .rincian-ekor { color: var(--text-hi) !important; }

        /* Note hint */
        .note-hint { color: var(--gold-300) !important; }
        .detail-note { color: var(--gold-300) !important; }
        .detail-note.purple-note { color: var(--purple) !important; }
        .detail-note i { color: inherit !important; }

        /* Defect group title */
        .gj-defect-group-title { color: var(--text-muted) !important; }

        /* Gen count badge */
        .gen-count-badge.green { color: #34D399 !important; background: var(--success-lt) !important; }
        .gen-count-badge.red { color: #fca5a5 !important; background: var(--danger-lt) !important; }

        /* Ranking table inner styles (generated by JS inline) */
        .result-table tr td[style*="font-weight:700"],
        .result-table tr td[style*="font-weight:800"] { color: var(--text-hi); }

        /* ═══ FIX: Pastikan tombol Buka Kunci bisa diklik ═══ */
        .action-group { position: relative; z-index: 1; }
        .action-group .btn-sm,
        .action-group button { position: relative; z-index: 2; cursor: pointer; }
        .btn-lock { cursor: pointer !important; pointer-events: auto !important; }
        .btn-lock:disabled { cursor: not-allowed !important; pointer-events: none !important; }

        /* ═══ FIX: Popup z-index pasti di atas sidebar ═══ */
        .popup-overlay { z-index: 10000 !important; }
        .modal-bg { z-index: 5000 !important; }

        html{scroll-behavior:smooth;}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--ocean-900);color:var(--text);min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased;letter-spacing:-0.01em;}

        /* ═══════ OCEAN BACKGROUND ═══════ */
        .ocean-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(37,99,235,.14) 0%,transparent 55%),radial-gradient(ellipse 50% 50% at 100% 100%,rgba(6,182,212,.08) 0%,transparent 60%),radial-gradient(ellipse 40% 40% at 0% 70%,rgba(124,58,237,.06) 0%,transparent 60%),linear-gradient(180deg,var(--ocean-950) 0%,var(--ocean-900) 45%,var(--ocean-850) 100%);}
        .bubbles{position:fixed;inset:0;z-index:1;pointer-events:none;overflow:hidden;}
        .bubbles span{position:absolute;display:block;border-radius:50%;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,.55),rgba(167,139,250,.20) 60%,transparent 70%);box-shadow:0 0 8px rgba(167,139,250,.20);bottom:-20px;will-change:transform,opacity;animation:bubbleRise linear infinite;opacity:0;}
        .bubbles span:nth-child(1){left:10%;width:8px;height:8px;animation-duration:22s;animation-delay:0s;}
        .bubbles span:nth-child(2){left:25%;width:5px;height:5px;animation-duration:28s;animation-delay:6s;}
        .bubbles span:nth-child(3){left:42%;width:10px;height:10px;animation-duration:24s;animation-delay:3s;}
        .bubbles span:nth-child(4){left:60%;width:6px;height:6px;animation-duration:26s;animation-delay:9s;}
        .bubbles span:nth-child(5){left:75%;width:9px;height:9px;animation-duration:21s;animation-delay:2s;}
        .bubbles span:nth-child(6){left:90%;width:5px;height:5px;animation-duration:27s;animation-delay:11s;}
        @keyframes bubbleRise{0%{transform:translate3d(0,0,0) scale(.9);opacity:0;}10%{opacity:.5;}90%{opacity:.2;}100%{transform:translate3d(15px,-110vh,0) scale(.85);opacity:0;}}

        /* ═══════ APP SHELL ═══════ */
        .app-shell{position:relative;z-index:10;min-height:100vh;display:flex;}

        /* ═══════ SIDEBAR ═══════ */
        .sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);background:linear-gradient(180deg,rgba(11,18,32,.92) 0%,rgba(14,23,41,.96) 100%);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-right:1px solid var(--bd-1);z-index:200;display:flex;flex-direction:column;transition:transform .35s cubic-bezier(.16,1,.3,1);overflow:hidden;}
        .sidebar-head{padding:20px 20px 16px;border-bottom:1px solid var(--bd-1);flex-shrink:0;}
        .sidebar-brand{display:flex;align-items:center;gap:12px;}
        .sidebar-mark{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#7c3aed,#A78BFA);box-shadow:0 6px 18px -6px rgba(124,58,237,.5),inset 0 1px 0 rgba(255,255,255,.25);flex-shrink:0;}
        .sidebar-mark i{color:white;font-size:16px;}
        .sidebar-brand h2{font-family:'Fraunces',serif;font-weight:600;font-size:16px;color:var(--text-hi);line-height:1.05;letter-spacing:-.02em;}
        .sidebar-brand h2 em{font-style:italic;font-weight:400;color:var(--cyan-400);}
        .sidebar-brand p{font-size:9.5px;color:var(--text-muted);margin-top:2px;letter-spacing:.1em;text-transform:uppercase;font-weight:700;}
        .sidebar-nav{flex:1;overflow-y:auto;padding:12px 10px;scrollbar-width:thin;scrollbar-color:var(--glass-strong) transparent;}
        .sidebar-nav::-webkit-scrollbar{width:4px;}
        .sidebar-nav::-webkit-scrollbar-track{background:transparent;}
        .sidebar-nav::-webkit-scrollbar-thumb{background:var(--glass-strong);border-radius:10px;}
        .sidebar-section-label{font-size:9.5px;font-weight:700;color:var(--text-faint);letter-spacing:.14em;text-transform:uppercase;padding:14px 12px 6px;}
        .sidebar-link{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:12px;color:var(--text-muted);font-size:12.5px;font-weight:600;text-decoration:none;transition:all .2s;cursor:pointer;border:1px solid transparent;margin-bottom:2px;position:relative;overflow:hidden;}
        .sidebar-link i{width:18px;text-align:center;font-size:13px;flex-shrink:0;transition:color .2s;}
        .sidebar-link:hover{background:var(--glass-2);color:var(--text-hi);border-color:var(--bd-1);}
        .sidebar-link.active{background:rgba(124,58,237,.12);color:var(--purple);border-color:rgba(124,58,237,.25);font-weight:700;}
        .sidebar-link.active i{color:var(--purple);}
        .sidebar-link .link-badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:6px;background:var(--purple-lt);color:var(--purple);min-width:20px;text-align:center;}
        .sidebar-link .link-badge.gold{background:var(--warning-lt);color:var(--gold-400);}
        .sidebar-footer{padding:14px 16px;border-top:1px solid var(--bd-1);flex-shrink:0;}
        .sidebar-user{display:flex;align-items:center;gap:10px;padding:8px;border-radius:12px;background:var(--glass-2);border:1px solid var(--bd-1);margin-bottom:10px;}
        .sidebar-avatar{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,var(--purple),#7c3aed);color:white;font-weight:800;font-size:12px;box-shadow:0 4px 10px -2px rgba(124,58,237,.4);flex-shrink:0;}
        .sidebar-user-info{min-width:0;flex:1;}
        .sidebar-user-info h4{font-size:12px;font-weight:700;color:var(--text-hi);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .sidebar-user-info span{font-size:9px;color:var(--purple);background:var(--purple-lt);padding:1px 6px;border-radius:4px;font-weight:700;letter-spacing:.04em;}
        .sidebar-logout{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:9px;border-radius:10px;background:var(--glass-2);border:1px solid var(--bd-1);color:var(--text-muted);font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;font-family:inherit;}
        .sidebar-logout:hover{background:rgba(239,68,68,.10);color:#fca5a5;border-color:rgba(239,68,68,.25);}

        /* Sidebar overlay (mobile) */
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(2,6,14,.7);z-index:199;backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);opacity:0;transition:opacity .3s;}
        .sidebar-overlay.show{opacity:1;}

        /* ═══════ MAIN AREA ═══════ */
        .main-area{flex:1;margin-left:var(--sidebar-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;}

        /* ═══════ TOP NAV ═══════ */
        .top-nav{background:rgba(11,18,32,.72);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid var(--bd-1);padding:14px 28px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;box-shadow:0 1px 0 rgba(255,255,255,.04) inset;}
        .brand{display:flex;align-items:center;gap:10px;min-width:0;}
        .brand h1{font-size:15px;font-weight:800;color:var(--purple);display:flex;align-items:center;gap:8px;letter-spacing:-.01em;}
        .brand span{font-size:10px;color:var(--text-muted);letter-spacing:.04em;text-transform:uppercase;font-weight:600;}
        .nav-right{display:flex;align-items:center;gap:12px;}
        .nav-right .info{display:none;}
        .nav-right .info h4{font-size:13px;font-weight:700;}
        .nav-right .info span{font-size:10px;color:var(--purple);background:var(--purple-light);padding:2px 6px;border-radius:4px;font-weight:700;}
        .btn-logout-nav{display:none;}

        /* Hamburger */
        .hamburger{display:none;width:40px;height:40px;border-radius:12px;background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text);font-size:16px;cursor:pointer;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0;}
        .hamburger:hover{background:var(--glass-3);border-color:var(--bd-3);}

        /* Export in nav */
        .export-wrap{position:relative;}
        .export-btn{padding:8px 14px;border-radius:10px;border:1px solid rgba(16,185,129,.30);background:rgba(16,185,129,.10);font-size:12px;font-weight:700;cursor:pointer;color:#34D399;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;}
        .export-btn:hover{background:rgba(16,185,129,.20);border-color:rgba(16,185,129,.50);transform:translateY(-1px);box-shadow:0 4px 12px rgba(16,185,129,.2);}
        .export-dd{position:absolute;top:calc(100% + 6px);right:0;background:var(--ocean-800);border:1px solid var(--bd-2);border-radius:14px;box-shadow:0 12px 32px rgba(0,0,0,.5);min-width:240px;z-index:200;display:none;overflow:hidden;}
        .export-dd.show{display:block;}
        .export-dd-item{padding:10px 16px;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background .12s;font-weight:600;color:var(--text);}
        .export-dd-item:hover{background:var(--purple-light);color:var(--purple);}
        .export-dd-item i{width:16px;text-align:center;font-size:12px;}
        .export-dd-sep{height:1px;background:var(--bd-1);margin:4px 0;}

        /* ═══════ MAIN CONTAINER ═══════ */
        .main-container{padding:24px 28px;max-width:1400px;margin:0 auto;width:100%;display:flex;flex-direction:column;gap:20px;}

        /* ═══════ GLASS CARD BASE ═══════ */
        .card{background:linear-gradient(180deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.02) 100%);border-radius:20px;border:1px solid var(--bd-1);box-shadow:0 1px 0 rgba(255,255,255,.04) inset,0 20px 40px -20px rgba(0,0,0,.4),0 8px 16px -12px rgba(124,58,237,.06);overflow:hidden;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);animation:cardEntry .5s .1s cubic-bezier(.16,1,.3,1) both;}
        .card::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(180deg,rgba(255,255,255,.04) 0%,transparent 25%);pointer-events:none;z-index:0;}
        @keyframes cardEntry{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
        .card-header{padding:18px 22px;border-bottom:1px solid var(--bd-1);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;position:relative;z-index:1;}
        .card-title{font-size:14px;font-weight:800;color:var(--text-hi);display:flex;align-items:center;gap:9px;letter-spacing:-.01em;}
        .card-title i{color:var(--purple);font-size:14px;}
        .card-subtitle{font-size:11px;color:var(--text-muted);font-weight:500;}
        .card-body{padding:20px 22px;position:relative;z-index:1;}

        /* ═══════ STATS GRID ═══════ */
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
        .stat-card{padding:18px;border-radius:16px;border:1px solid var(--bd-2);background:var(--glass-2);text-align:center;position:relative;overflow:hidden;transition:all .25s;}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
        .stat-card.blue::before{background:linear-gradient(90deg,var(--cyan-400),var(--cyan-500));}
        .stat-card.green::before{background:linear-gradient(90deg,#34D399,var(--success));}
        .stat-card.orange::before{background:linear-gradient(90deg,var(--gold-400),var(--gold-600));}
        .stat-card.red::before{background:linear-gradient(90deg,#fca5a5,var(--danger));}
        .stat-card.purple::before{background:linear-gradient(90deg,var(--purple),#7c3aed);}
        .stat-number{font-family:'Fraunces',serif;font-size:30px;font-weight:500;line-height:1;margin-bottom:4px;color:var(--text-hi);letter-spacing:-.02em;}
        .stat-label{font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.1em;}
        .stat-click-hint{font-size:9px;color:var(--text-muted);margin-top:6px;opacity:0;transition:opacity .25s;display:flex;align-items:center;justify-content:center;gap:3px;}
        .stat-card.clickable{cursor:pointer;}
        .stat-card.clickable:hover{transform:translateY(-3px);box-shadow:0 8px 24px -8px rgba(124,58,237,.2);border-color:var(--bd-3);background:var(--glass-3);}
        .stat-card.clickable:hover .stat-click-hint{opacity:1;}

        /* ═══════ RINCIAN GRID ═══════ */
        .rincian-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;}
        .rincian-card{background:var(--glass-2);border:1px solid var(--bd-2);border-radius:12px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;cursor:pointer;transition:all .2s;}
        .rincian-card:hover{border-color:rgba(124,58,237,.30);box-shadow:0 4px 16px -4px rgba(124,58,237,.12);transform:translateY(-2px);background:var(--glass-3);}
        .rincian-cat{font-size:12.5px;font-weight:700;color:var(--text-hi);display:flex;align-items:center;gap:6px;}
        .rincian-cat i{font-size:10px;color:var(--purple);opacity:0;transition:opacity .2s;}
        .rincian-card:hover .rincian-cat i{opacity:1;}
        .rincian-data{text-align:right;}
        .rincian-ekor{font-family:'Fraunces',serif;font-size:20px;font-weight:500;color:var(--text-hi);letter-spacing:-.02em;}

        /* ═══════ JURI CHIPS ═══════ */
        .juri-chip-list{display:flex;flex-wrap:wrap;gap:14px;}
        .juri-chip{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;font-size:12px;font-weight:700;border:1px solid;cursor:pointer;transition:all .2s;}
        .juri-chip:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.2);}
        .juri-chip.juri-awal{background:rgba(34,211,238,.08);color:var(--cyan-300);border-color:rgba(34,211,238,.20);}
        .juri-chip.juri-awal:hover{background:rgba(34,211,238,.14);}
        .juri-chip.juri-awal i{color:var(--cyan-400);}
        .juri-chip.juri-grand{background:var(--purple-light);color:var(--purple);border-color:rgba(124,58,237,.25);}
        .juri-chip.juri-grand:hover{background:rgba(124,58,237,.16);}
        .juri-chip.juri-grand i{color:var(--purple);}
        .juri-chip .chip-role{font-size:9px;opacity:.7;text-transform:uppercase;letter-spacing:.3px;font-weight:800;}
        .juri-chip .chip-count{opacity:.5;font-size:11px;}
        .juri-chip .chip-arrow{font-size:9px;opacity:0;transition:opacity .2s;margin-left:2px;}
        .juri-chip:hover .chip-arrow{opacity:.6;}

        /* ═══════ TOOLBAR & SEARCH ═══════ */
        .toolbar{display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;}
        .search-box{flex:1;min-width:200px;position:relative;}
        .search-box input{width:100%;padding:10px 14px 10px 40px;border:1px solid var(--bd-2);border-radius:12px;font-family:inherit;font-size:13px;outline:none;background:var(--glass-2);color:var(--text-hi);transition:all .2s;}
        .search-box input::placeholder{color:var(--text-faint);}
        .search-box input:focus{border-color:var(--purple);background:var(--glass-3);box-shadow:0 0 0 3px rgba(124,58,237,.1);}
        .search-box i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-low);font-size:14px;transition:color .2s;}
        .search-box input:focus~i,.search-box:focus-within i{color:var(--purple);}

        /* ═══════ TABLES ═══════ */
        .table-wrap{overflow-x:auto;border-radius:12px;}
        .table-wrap::-webkit-scrollbar{height:6px;}
        .table-wrap::-webkit-scrollbar-track{background:transparent;}
        .table-wrap::-webkit-scrollbar-thumb{background:var(--glass-strong);border-radius:10px;}
        .result-table{width:100%;border-collapse:collapse;font-size:13px;min-width:1000px;}
        .result-table th{background:rgba(255,255,255,.04);padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--bd-2);white-space:nowrap;letter-spacing:.06em;}
        .result-table td{padding:12px;border-bottom:1px solid var(--bd-1);vertical-align:middle;color:var(--text);}
        .result-table tr:hover td{background:rgba(124,58,237,.04);}
        .result-table tr:last-child td{border-bottom:none;}

        .gen-table{width:100%;border-collapse:collapse;font-size:12px;}
        .gen-table th{background:rgba(255,255,255,.04);padding:9px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--bd-2);white-space:nowrap;}
        .gen-table td{padding:10px 12px;border-bottom:1px solid var(--bd-1);vertical-align:middle;color:var(--text);}
        .gen-table tbody tr:hover td{background:rgba(124,58,237,.04);}
        .gen-table .g-total{font-weight:900;color:var(--purple);font-size:14px;}
        .gen-table .g-tank{font-weight:800;color:var(--purple);}
        .gen-table .g-juri{font-size:11px;color:var(--text-muted);}
        .gen-count-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:800;}
        .gen-count-badge.green{background:var(--success-lt);color:#34D399;}
        .gen-count-badge.red{background:var(--danger-lt);color:#fca5a5;}

        /* ═══════ BADGES ═══════ */
        .badge{padding:4px 8px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;}
        .badge-success{background:var(--success-lt);color:#34D399;}
        .badge-warning{background:var(--warning-lt);color:var(--gold-300);}
        .badge-purple{background:var(--purple-light);color:var(--purple);}
        .juri-submit-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700;}
        .juri-submit-chip.ok{background:var(--success-lt);color:#34D399;}
        .juri-submit-chip.not-ok{background:var(--warning-lt);color:var(--gold-300);}

        /* ═══════ BUTTONS ═══════ */
        .btn-xs{padding:5px 9px;border:none;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:4px;transition:all .15s;}
        .btn-xs.blue{background:var(--primary-lt);color:var(--primary);}
        .btn-xs.blue:hover{background:var(--primary);color:#0B1220;}
        .btn-xs.green{background:var(--success-lt);color:#34D399;}
        .btn-xs.green:hover{background:var(--success);color:white;}
        .btn-xs.red{background:var(--danger-lt);color:#fca5a5;}
        .btn-xs.red:hover{background:var(--danger);color:white;}
        .btn-xs.purple{background:var(--purple-lt);color:var(--purple);}
        .btn-xs.purple:hover{background:var(--purple);color:white;}

        .btn-sm{padding:6px 10px;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:4px;transition:all .2s;white-space:nowrap;}
        .btn-edit{background:var(--purple-light);color:var(--purple);border:1px solid rgba(124,58,237,.20);}
        .btn-edit:hover{background:var(--purple);color:white;border-color:var(--purple);}
        .btn-detail{background:var(--primary-light);color:var(--primary);border:1px solid rgba(34,211,238,.20);}
        .btn-detail:hover{background:var(--primary);color:#0B1220;border-color:var(--primary);}
        .action-group{display:flex;gap:6px;flex-wrap:wrap;}
        .btn-lock{background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);}
        .btn-lock:hover{background:var(--warning);color:white;border-color:var(--warning);}
        .btn-lock.locked{background:var(--primary-light);color:var(--primary);border-color:rgba(34,211,238,.20);}
        .btn-lock.locked:hover{background:var(--primary);color:#0B1220;border-color:var(--primary);}

        .btn-cancel{padding:10px 18px;border:1px solid var(--bd-2);border-radius:11px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;color:var(--text-muted);background:var(--glass-2);transition:all .2s;}
        .btn-cancel:hover{border-color:var(--bd-3);color:var(--text-hi);background:var(--glass-3);}
        .btn-primary{padding:10px 22px;background:linear-gradient(135deg,#7c3aed,var(--purple));color:white;border:none;border-radius:11px;font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:7px;font-family:inherit;transition:all .2s;box-shadow:0 4px 14px -4px rgba(124,58,237,.5),inset 0 1px 0 rgba(255,255,255,.2);}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 20px -6px rgba(124,58,237,.6),inset 0 1px 0 rgba(255,255,255,.2);}
        .btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none;}
        .btn-blue{padding:10px 20px;background:linear-gradient(135deg,var(--cyan-500),var(--primary));color:white;border:none;border-radius:11px;font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:7px;font-family:inherit;transition:all .2s;box-shadow:0 4px 14px -4px rgba(6,182,212,.5),inset 0 1px 0 rgba(255,255,255,.2);}
        .btn-blue:hover{transform:translateY(-1px);box-shadow:0 8px 20px -6px rgba(6,182,212,.6),inset 0 1px 0 rgba(255,255,255,.2);}

        /* ═══════ JURI CELL & TOTAL CELL ═══════ */
        .juri-cell{font-size:12px;font-weight:700;color:var(--primary);line-height:1.5;}
        .juri-cell div{font-size:12px;font-weight:600;color:var(--primary);line-height:1.6;}
        .juri-cell .grand-line{color:var(--purple);font-size:11px;}
        .juri-cell .grand-line i{font-size:9px;}
        .juri-count-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;background:var(--primary-light);color:var(--primary);margin-top:4px;}
        .total-cell{font-family:'Fraunces',serif;font-size:18px;font-weight:500;color:var(--purple);letter-spacing:-.02em;}
        .total-cell.zero{color:var(--text-faint);font-size:13px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;}

        /* ═══════ FILTER SELECT ═══════ */
        .filter-select{padding:9px 34px 9px 12px;border:1px solid var(--bd-2);border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;font-weight:700;color:var(--text-hi);background-color:var(--glass-2);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23A78BFA' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:13px;appearance:none;-webkit-appearance:none;-moz-appearance:none;cursor:pointer;transition:all .2s;outline:none;min-width:140px;}
        .filter-select:hover{border-color:var(--purple);background-color:var(--purple-light);}
        .filter-select:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(124,58,237,.12);background-color:var(--glass-3);}
        .filter-select option{background-color:var(--ocean-800);color:var(--text-hi);font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;font-weight:500;padding:8px 12px;}
        .filter-select option:disabled{color:var(--text-low);}
        .filter-select option:checked{background-color:var(--ocean-700);color:var(--purple);}

        /* ═══════ EMPTY STATE ═══════ */
        .empty-state{text-align:center;padding:30px;color:var(--text-muted);font-size:13px;}

        /* ═══════ MODALS ═══════ */
        .modal-bg{position:fixed;inset:0;background:rgba(2,6,14,.88);backdrop-filter:blur(6px);z-index:500;display:none;place-items:center;padding:16px;}
        .modal-bg.show{display:grid;}
        .modal-box{background:linear-gradient(180deg,var(--ocean-800) 0%,var(--ocean-900) 100%);border:1px solid var(--bd-2);border-radius:24px;width:90%;max-width:860px;max-height:90vh;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.06);display:grid;grid-template-rows:auto 1fr auto;}
        .modal-head{padding:18px 24px;border-bottom:1px solid var(--bd-1);display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,rgba(124,58,237,.08),rgba(124,58,237,.04));}
        .modal-head h3{font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px;color:var(--text-hi);}
        .modal-head h3 i{color:var(--purple);}
        .modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted);width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:all .2s;}
        .modal-close:hover{background:var(--glass-3);color:var(--text-hi);}
        .modal-content,.modal-body{padding:20px;overflow-y:auto;color:var(--text);}
        .modal-content::-webkit-scrollbar,.modal-body::-webkit-scrollbar{width:6px;}
        .modal-content::-webkit-scrollbar-track,.modal-body::-webkit-scrollbar-track{background:transparent;}
        .modal-content::-webkit-scrollbar-thumb,.modal-body::-webkit-scrollbar-thumb{background:var(--glass-strong);border-radius:10px;}
        .modal-footer{padding:14px 24px;border-top:1px solid var(--bd-1);display:flex;justify-content:flex-end;gap:10px;background:rgba(255,255,255,.02);}

        /* ═══════ DETAIL MODAL ═══════ */
        .detail-info-banner{background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(124,58,237,.05));border:1px solid rgba(124,58,237,.25);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;}
        .detail-info-banner h4{font-size:15px;font-weight:800;color:var(--text-hi);}
        .detail-meta{font-size:12px;color:var(--purple);margin-top:5px;display:flex;gap:14px;flex-wrap:wrap;}
        .detail-meta span{display:flex;align-items:center;gap:5px;}
        .detail-total-badge{background:linear-gradient(135deg,#7c3aed,var(--purple));color:white;padding:7px 16px;border-radius:10px;font-size:14px;font-weight:800;display:flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 4px 14px -4px rgba(124,58,237,.5);}
        .detail-note{background:var(--warning-lt);border:1px solid rgba(245,158,11,.25);border-radius:10px;padding:10px 14px;font-size:12px;color:var(--gold-300);margin-bottom:14px;display:flex;gap:8px;align-items:flex-start;}
        .detail-note i{color:var(--gold-500);margin-top:1px;flex-shrink:0;}
        .detail-note.purple-note{background:var(--purple-light);border-color:rgba(124,58,237,.25);color:var(--purple);}
        .detail-note.purple-note i{color:var(--purple);}
        .detail-kat-section{margin-bottom:14px;}
        .detail-kat-header{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--purple-light);border:1px solid rgba(124,58,237,.25);border-radius:8px 8px 0 0;}
        .detail-kat-title{font-size:12px;font-weight:800;color:var(--purple);text-transform:uppercase;letter-spacing:.5px;}
        .detail-kat-sub{font-size:12px;font-weight:700;color:var(--purple);}
        .detail-kat-body{border:1px solid rgba(124,58,237,.20);border-top:none;border-radius:0 0 8px 8px;overflow:hidden;}
        .detail-kat-mini{background:rgba(255,255,255,.03);padding:7px 16px;font-size:10px;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--bd-1);display:flex;justify-content:space-between;align-items:center;}
        .detail-kat-mini span{color:var(--purple);font-weight:900;font-size:11px;}
        .detail-field-row{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-bottom:1px solid var(--bd-1);gap:12px;transition:background .15s;}
        .detail-field-row:last-child{border-bottom:none;}
        .detail-field-row:hover{background:rgba(124,58,237,.04);}
        .detail-field-left{flex:1;}
        .detail-field-name{font-size:12px;font-weight:700;color:var(--text-hi);}
        .detail-field-meta{font-size:10px;color:var(--text-muted);margin-top:1px;}
        .score-chip{padding:5px 16px;border-radius:8px;font-size:13px;font-weight:800;min-width:48px;text-align:center;}
        .score-chip.filled{background:var(--purple-light);color:var(--purple);border:1px solid rgba(124,58,237,.20);}
        .score-chip.empty{background:var(--glass-2);color:var(--text-faint);font-size:11px;font-weight:600;border:1px solid var(--bd-1);}

        /* Detail juri accordion */
        .detail-juri-accordion{border:1px solid var(--bd-2);border-radius:12px;overflow:hidden;margin-bottom:10px;background:var(--glass-1);}
        .detail-juri-toggle{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;cursor:pointer;transition:background .2s;user-select:none;}
        .detail-juri-toggle:hover{background:rgba(124,58,237,.06);}
        .detail-juri-toggle.open{background:var(--purple-light);border-bottom:1px solid rgba(124,58,237,.20);}
        .detail-juri-toggle .dj-name{font-size:13px;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--text-hi);}
        .detail-juri-toggle .dj-total{font-family:'Fraunces',serif;font-size:16px;font-weight:500;color:var(--purple);letter-spacing:-.02em;}
        .detail-juri-toggle .dj-arrow{font-size:12px;color:var(--text-muted);transition:transform .2s;}
        .detail-juri-toggle.open .dj-arrow{transform:rotate(180deg);}
        .detail-juri-scores{display:none;}
        .detail-juri-scores.open{display:block;}

        /* ═══════ EDIT MODAL ═══════ */
        .edit-info-banner{margin-bottom:12px;font-size:13px;background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(124,58,237,.05));padding:12px 16px;border-radius:12px;border:1px solid rgba(124,58,237,.25);color:var(--text);}
        .note-hint{font-size:11px;color:var(--gold-300);background:var(--warning-lt);border:1px solid rgba(245,158,11,.25);padding:8px 12px;border-radius:10px;margin-bottom:14px;display:flex;align-items:center;gap:6px;}
        .note-hint i{color:var(--gold-500);}
        .content-grid{display:grid;grid-template-columns:175px 1fr;gap:16px;}
        .kat-list{display:flex;flex-direction:column;gap:5px;}
        .kat-btn{padding:9px 11px;background:var(--glass-2);border:1px solid var(--bd-2);border-radius:10px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:space-between;}
        .kat-btn:hover,.kat-btn.active{border-color:var(--purple);color:var(--purple);background:var(--purple-light);}
        .kat-badge{font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:var(--purple-lt);color:var(--purple);min-width:22px;text-align:center;}
        .kat-badge.has-changes{background:var(--success);color:white;}
        .score-row{display:grid;grid-template-columns:1fr 115px;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--bd-1);}
        .score-row:last-of-type{border-bottom:none;}
        .score-label h4{font-size:13px;font-weight:700;color:var(--text-hi);}
        .score-label p{font-size:11px;color:var(--text-muted);margin-top:2px;}
        .score-label .orig-val{color:var(--text-muted);font-weight:600;}
        .score-label .orig-val strong{color:var(--purple);}
        .score-input{width:100%;padding:9px;text-align:center;border:2px solid var(--bd-2);border-radius:10px;font-size:15px;font-weight:800;color:var(--purple);outline:none;font-family:inherit;transition:all .2s;background:var(--glass-2);color-scheme:dark;}
        .score-input:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(124,58,237,.1);}
        .score-input.changed{border-color:var(--success);background:rgba(16,185,129,.06);color:#34D399;}
        .subtotal-bar{text-align:right;font-size:13px;font-weight:900;color:var(--purple);padding:10px 0 0;border-top:2px solid rgba(124,58,237,.25);margin-top:8px;}

        /* GJ score select */
        .gj-score-select{width:100%;padding:9px 28px 9px 8px;text-align:center;border:2px solid var(--bd-2);border-radius:10px;font-size:15px;font-weight:800;color:var(--purple);outline:none;transition:.2s;background:var(--glass-2);cursor:pointer;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23A78BFA' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;font-family:inherit;color-scheme:dark;}
        .gj-score-select:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(124,58,237,.1);}
        .gj-score-select.changed{border-color:var(--success);background:rgba(16,185,129,.06);color:#34D399;}
        .gj-score-select option{background:var(--ocean-800);color:var(--text-hi);}

        /* GJ defect button */
        .gj-defect-btn{width:100%;padding:9px;text-align:center;border:2px solid var(--bd-2);border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;transition:.2s;background:var(--glass-2);color:var(--text-muted);font-family:inherit;}
        .gj-defect-btn:hover{border-color:var(--purple);color:var(--purple);}
        .gj-defect-btn.minor{background:rgba(245,158,11,.08);color:var(--gold-300);border-color:rgba(245,158,11,.30);}
        .gj-defect-btn.minor:hover{background:rgba(245,158,11,.14);}
        .gj-defect-btn.mayor{background:var(--danger-lt);color:#fca5a5;border-color:rgba(239,68,68,.30);}
        .gj-defect-btn.mayor:hover{background:rgba(239,68,68,.16);}

        /* Defect modal */
        .gj-defect-modal-box{max-width:450px;}
        .gj-defect-group{margin-bottom:20px;}
        .gj-defect-group-title{font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:10px;letter-spacing:.5px;}
        .gj-defect-options{display:flex;flex-direction:column;gap:8px;}
        .gj-defect-option{display:flex;align-items:center;gap:12px;padding:12px;border:2px solid var(--bd-2);border-radius:10px;cursor:pointer;background:var(--glass-2);transition:.2s;}
        .gj-defect-option:hover{border-color:var(--purple);}
        .gj-defect-option.selected{border-color:var(--purple);background:var(--purple-light);}
        .gj-defect-option input[type="checkbox"]{width:18px;height:18px;accent-color:var(--purple);cursor:pointer;}
        .gj-defect-option span{font-size:13px;font-weight:600;color:var(--text);}

        /* ═══════ POPUPS ═══════ */
        .popup-overlay{position:fixed;inset:0;background:rgba(2,6,14,.88);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .4s;padding:16px;}
        .popup-overlay.show{opacity:1;pointer-events:all;}
        .popup-card{background:linear-gradient(180deg,var(--ocean-800) 0%,var(--ocean-900) 100%);border:1px solid var(--bd-2);border-radius:24px;padding:48px 40px 36px;text-align:center;max-width:380px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.06);transform:scale(.8) translateY(20px);transition:transform .4s cubic-bezier(.16,1,.3,1);}
        .popup-overlay.show .popup-card{transform:scale(1) translateY(0);}
        .popup-icon{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;}
        .popup-icon i{font-size:36px;color:white;animation:iconPop .5s .3s cubic-bezier(.16,1,.3,1) both;}
        @keyframes iconPop{0%{transform:scale(0) rotate(-45deg);opacity:0}100%{transform:scale(1) rotate(0);opacity:1}}
        .popup-icon.success{background:linear-gradient(135deg,var(--purple),#7c3aed);box-shadow:0 8px 24px rgba(124,58,237,.4);}
        .popup-icon.danger{background:linear-gradient(135deg,var(--danger),#dc2626);box-shadow:0 8px 24px rgba(239,68,68,.4);}
        .popup-icon.warning{background:linear-gradient(135deg,var(--warning),#d97706);box-shadow:0 8px 24px rgba(245,158,11,.4);}
        .popup-title{font-family:'Fraunces',serif;font-weight:500;font-size:22px;color:var(--text-hi);margin-bottom:8px;letter-spacing:-.02em;}
        .popup-desc{font-size:13.5px;color:var(--text-muted);line-height:1.6;margin-bottom:24px;}
        .popup-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border:none;border-radius:14px;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;transition:all .3s;color:white;}
        .popup-btn.success{background:linear-gradient(135deg,var(--purple),#7c3aed);box-shadow:0 4px 14px -4px rgba(124,58,237,.5),inset 0 1px 0 rgba(255,255,255,.2);}
        .popup-btn.success:hover{transform:translateY(-1px);}
        .popup-btn.danger{background:linear-gradient(135deg,var(--danger),#dc2626);box-shadow:0 4px 14px -4px rgba(239,68,68,.5),inset 0 1px 0 rgba(255,255,255,.2);}
        .popup-btn.danger:hover{transform:translateY(-1px);}
        .popup-btn.warning{background:linear-gradient(135deg,var(--warning),#d97706);box-shadow:0 4px 14px -4px rgba(245,158,11,.5),inset 0 1px 0 rgba(255,255,255,.2);}
        .popup-btn.warning:hover{transform:translateY(-1px);}
        .popup-btn.cancel{background:var(--glass-2);color:var(--text-muted);border:1px solid var(--bd-2);box-shadow:none;}
        .popup-btn.cancel:hover{background:var(--glass-3);color:var(--text-hi);}
        .err-list{list-style:none;text-align:left;margin-bottom:16px;display:flex;flex-direction:column;gap:7px;max-height:170px;overflow-y:auto;}
        .err-item{display:flex;align-items:flex-start;gap:9px;padding:9px 11px;background:var(--purple-light);border:1px solid rgba(124,58,237,.20);border-radius:9px;font-size:12px;}
        .err-item i{color:var(--purple);margin-top:1px;flex-shrink:0;}
        .err-item span{color:var(--text);font-weight:600;line-height:1.4;}
        .popup-btn-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}

        /* Status actions */
        .status-actions{margin-top:8px;}

        /* ═══════ RANKING SECTION SPECIFICS ═══════ */
        .result-table .g-tank{text-align:center;font-weight:700;color:var(--purple);}

        /* ═══════ RESPONSIVE ═══════ */
        @media(max-width:1280px){.stats-grid{grid-template-columns:repeat(3,1fr);}}
        @media(max-width:1024px){
            .sidebar{transform:translateX(-100%);}
            .sidebar.open{transform:translateX(0);}
            .sidebar-overlay{display:block;}
            .main-area{margin-left:0;}
            .hamburger{display:flex;}
            .content-grid{grid-template-columns:1fr;}
            .stats-grid{grid-template-columns:repeat(3,1fr);}
        }
        @media(max-width:768px){
            .top-nav{padding:12px 16px;}
            .main-container{padding:16px 14px;gap:16px;}
            .stats-grid{grid-template-columns:1fr 1fr;gap:10px;}
            .rincian-grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));}
            .card-header{padding:16px 18px;flex-direction:column;align-items:flex-start;gap:6px;}
            .card-body{padding:16px 18px;}
            .toolbar{flex-direction:column;}
            .search-box{min-width:0;width:100%;}
            .filter-select{min-width:0;width:100%;}
            .export-wrap{width:100%;}
            .export-btn{width:100%;justify-content:center;}
            .bubbles{display:none;}
        }
        @media(max-width:480px){
            .stats-grid{grid-template-columns:1fr 1fr;gap:8px;}
            .stat-card{padding:14px 10px;}
            .stat-number{font-size:24px;}
            .stat-label{font-size:9px;}
            .main-container{padding:12px 10px;gap:12px;}
            .card-header{padding:14px 14px;}
            .card-body{padding:14px;}
            .modal-box{border-radius:18px;width:96%;max-height:95vh;}
            .popup-card{padding:32px 24px 28px;border-radius:20px;}
            .popup-title{font-size:18px;}
        }
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.1s!important;}.bubbles{display:none;}}
    </style>
</head>
<body>

<!-- ATMOSPHERIC BACKGROUND -->
<div class="ocean-bg"></div>
<div class="bubbles" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>

<div class="app-shell">

    <!-- ═══════ SIDEBAR ═══════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-head">
            <div class="sidebar-brand">
                <div class="sidebar-mark"><i class="fas fa-crown"></i></div>
                <div>
                    <h2>LCI <em>Suite</em></h2>
                    <p>Grand Jury Panel</p>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Navigasi</div>
            <a class="sidebar-link active" href="#sectionStats" data-section="sectionStats">
                <i class="fas fa-chart-pie"></i> Dashboard Overview
            </a>
            <a class="sidebar-link" href="#sectionRincian" data-section="sectionRincian">
                <i class="fas fa-chart-bar"></i> Rincian Kategori
            </a>
            <a class="sidebar-link" href="#sectionJuri" data-section="sectionJuri">
                <i class="fas fa-users-gear"></i> Daftar Juri
            </a>

            <div class="sidebar-section-label">Penilaian</div>
            <a class="sidebar-link" href="#sectionRanking" data-section="sectionRanking">
                <i class="fas fa-trophy" style="color:var(--gold-500);"></i> Point Ranking
            </a>
            <a class="sidebar-link" href="#sectionManajemen" data-section="sectionManajemen">
                <i class="fas fa-database"></i> Manajemen Nilai
            </a>

            <div class="sidebar-section-label">Aksi</div>
            <a class="sidebar-link" href="{{ route('grand-juri.nominasi') }}" data-nolink>
                <i class="fas fa-clipboard-check"></i> Review Nominasi
                <span class="link-badge gold" id="sidebarNominasiBadge">0</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ strtoupper(mb_substr(trim($user->name), 0, 1)) }}</div>
                <div class="sidebar-user-info">
                    <h4>{{ $user->name }}</h4>
                    <span>GRAND JURI</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="sidebar-logout"><i class="fas fa-right-from-bracket"></i> Keluar</button>
            </form>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ═══════ MAIN AREA ═══════ -->
    <div class="main-area">

        <!-- TOP NAV (simplified — sidebar handles most nav) -->
        <nav class="top-nav">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="hamburger" id="hamburgerBtn" onclick="openSidebar()" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="brand">
                    <h1><i class="fas fa-crown"></i> GRAND JURY</h1>
                    <span>Panel Otoritas Tertinggi</span>
                </div>
            </div>
            <div class="nav-right">
                <a href="{{ route('grand-juri.nominasi') }}" class="btn-sm btn-edit" style="padding:8px 14px;border-radius:10px;text-decoration:none;">
                    <i class="fas fa-clipboard-check"></i> <span class="hide-mobile">Review </span>Nominasi
                </a>
            </div>
        </nav>

        <div class="main-container">

            <!-- ═══ STATS ═══ -->
            <div id="sectionStats">
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
                    <div class="stat-card purple clickable" onclick="window.location.href='/grand-juri/nominasi'">
                        <div class="stat-number" id="statNominasi">0</div>
                        <div class="stat-label">Nominasi Masuk</div>
                        <div class="stat-click-hint"><i class="fas fa-arrow-up-right-from-square"></i> Review Sekarang</div>
                    </div>
                </div>
            </div>

            <!-- ═══ RINCIAN ═══ -->
            <div id="sectionRincian">
                <div class="card">
                    <div class="card-header"><div class="card-title"><i class="fas fa-chart-bar"></i> Rincian Kategori</div></div>
                    <div class="card-body"><div class="rincian-grid" id="rincianGrid"><div class="empty-state">Memuat...</div></div></div>
                </div>
            </div>

            <!-- ═══ DAFTAR JURI ═══ -->
            <div id="sectionJuri">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="fas fa-users-gear"></i> Daftar Juri yang Menilai</div>
                            <div class="card-subtitle">Klik nama juri untuk melihat peserta yang dinilai</div>
                        </div>
                    </div>
                    <div class="card-body" id="juriSummaryBody">
                        <div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data juri...</div>
                    </div>
                </div>
            </div>

            <!-- ═══ SISTEM POINT RANKING ═══ -->
            <div id="sectionRanking">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="fas fa-trophy" style="color:var(--gold-500);"></i> Sistem Point Ranking</div>
                            <div class="card-subtitle">Peringkat berdasarkan nilai point (hanya ikan yang sudah DIKUNCI Grand Juri)</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button class="btn-sm btn-edit" id="btnScopeKelas" onclick="setPointScope('per_kategori_kelas')" style="font-size:11px;padding:7px 14px;">
                                    <i class="fas fa-layer-group" style="font-size:10px;"></i> Per Kat + Kelas
                                </button>
                                <button class="btn-sm btn-detail" id="btnScopeKat" onclick="setPointScope('per_kategori')" style="font-size:11px;padding:7px 14px;">
                                    <i class="fas fa-tags" style="font-size:10px;"></i> Per Kategori
                                </button>
                                <button class="btn-sm" id="btnScopeGlobal" onclick="setPointScope('global')" style="font-size:11px;padding:7px 14px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);">
                                    <i class="fas fa-globe" style="font-size:10px;"></i> Rank Global
                                </button>
                            </div>
                            <select class="filter-select" id="pointFilterKategori" onchange="loadPointRanking()">
                                <option value="">🏷️ Semua Kategori</option>
                                <option value="Cencu">Cencu</option><option value="Chingwa">Chingwa</option><option value="Freemarking">Freemarking</option><option value="Goldenbase">Goldenbase</option><option value="Klasik">Klasik</option><option value="Bonsai">Bonsai</option><option value="Jumbo">Jumbo</option>
                            </select>
                            <select class="filter-select" id="pointFilterKelas" onchange="loadPointRanking()" style="min-width:120px;">
                                <option value="">📐 Semua Kelas</option>
                                <option value="A">Kelas A</option><option value="B">Kelas B</option><option value="C">Kelas C</option><option value="D">Kelas D</option><option value="E">Kelas E</option>
                            </select>
                            <div id="globalTopNWrap" style="display:none;align-items:center;gap:6px;">
                                <span style="font-size:11px;font-weight:700;color:var(--text-muted);white-space:nowrap;">Tampilkan Top</span>
                                <input type="number" id="globalTopN" value="10" min="1" max="100" style="width:60px;padding:6px 6px;border:1px solid var(--bd-2);border-radius:8px;font-family:inherit;font-size:13px;font-weight:800;color:var(--text-hi);outline:none;text-align:center;background:var(--glass-2);color-scheme:dark;" oninput="loadPointRanking()">
                                <div style="display:flex;gap:3px;">
                                    <button class="btn-sm" onclick="setGlobalTopN(10)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);min-width:auto;">10</button>
                                    <button class="btn-sm" onclick="setGlobalTopN(20)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);min-width:auto;">20</button>
                                    <button class="btn-sm" onclick="setGlobalTopN(50)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);min-width:auto;">50</button>
                                    <button class="btn-sm" onclick="setGlobalTopN(100)" style="font-size:10px;padding:4px 8px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);min-width:auto;">100</button>
                                </div>
                            </div>
                        </div>
                        <div id="pointRankingContent"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div></div>
                    </div>
                </div>
            </div>

            <!-- ═══ DATA MANAJEMEN ═══ -->
            <div id="sectionManajemen">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="fas fa-database"></i> Data Manajemen Nilai</div>
                            <div class="card-subtitle">Grand Juri dapat melihat &amp; mengubah nilai dari setiap juri</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                            <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Cari Nama/Tim Peserta..."></div>
                        </div>
                        <div class="table-wrap">
                            <table class="result-table">
                                <thead>
                                    <tr><th>KATEGORI - KELAS</th><th>NO. TANK</th><th>ASAL/TEAM</th><th>DINILAI OLEH</th><th>TOTAL NILAI</th><th>POINT</th><th>STATUS</th><th>AKSI</th></tr>
                                </thead>
                                <tbody id="tbodyPeserta"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /main-container -->
    </div><!-- /main-area -->
</div><!-- /app-shell -->

<!-- ═══════ MODAL DETAIL ═══════ -->
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

<!-- ═══════ MODAL EDIT ═══════ -->
<div class="modal-bg" id="modalEdit">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-pen-to-square"></i> Edit Nilai — Grand Juri</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-content">
            <div class="edit-info-banner" id="editInfo"></div>
            <div class="note-hint"><i class="fas fa-circle-info"></i> Nilai dari juri sudah terisi di setiap input. <strong>Ubah hanya komponen yang ingin diperbarui</strong> lalu simpan. Input yang tidak diubah akan tetap menggunakan nilai juri.</div>
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

<!-- ═══════ MODAL GENERIC ═══════ -->
<div class="modal-bg" id="modalGeneric">
    <div class="modal-box" style="max-width:780px;">
        <div class="modal-head">
            <h3 id="genericTitle"><i class="fas fa-list"></i> Detail</h3>
            <button class="modal-close" onclick="closeModal('modalGeneric')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-content" id="genericContent"><div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div></div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('modalGeneric')"><i class="fas fa-xmark"></i> Tutup</button>
        </div>
    </div>
</div>

<!-- ═══════ MODAL DEFECT ═══════ -->
<div class="modal-bg" id="modalDefectGJ">
    <div class="modal-box gj-defect-modal-box">
        <div class="modal-head">
            <h3 id="defectModalTitleGJ">Pilih Defect</h3>
            <button class="modal-close" onclick="closeDefectModalGJ()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-content" id="defectModalBodyGJ"></div>
    </div>
</div>

<!-- ═══════ POPUP SUKSES ═══════ -->
<div class="popup-overlay" id="popupSuccess">
    <div class="popup-card">
        <div class="popup-icon success"><i class="fas fa-check"></i></div>
        <h2 class="popup-title">Nilai Berhasil Diperbarui!</h2>
        <p class="popup-desc" id="popupSuccessDesc">Perubahan telah tersimpan.</p>
        <button class="popup-btn success" onclick="hidePopup('popupSuccess')"><i class="fas fa-circle-check"></i> OK, Tutup</button>
    </div>
</div>

<!-- ═══════ POPUP ERROR ═══════ -->
<div class="popup-overlay" id="popupError">
    <div class="popup-card">
        <div class="popup-icon danger"><i class="fas fa-triangle-exclamation"></i></div>
        <h2 class="popup-title">Gagal Menyimpan</h2>
        <p class="popup-desc" id="popupErrorDesc">Terjadi kesalahan pada server.</p>
        <button class="popup-btn danger" onclick="hidePopup('popupError')"><i class="fas fa-rotate-right"></i> Tutup</button>
    </div>
</div>

<!-- ═══════ POPUP KOSONG ═══════ -->
<div class="popup-overlay" id="popupEmpty">
    <div class="popup-card">
        <div class="popup-icon warning"><i class="fas fa-exclamation"></i></div>
        <h2 class="popup-title">Tidak Ada Perubahan</h2>
        <p class="popup-desc">Ubah setidaknya satu komponen nilai sebelum menyimpan.</p>
        <button class="popup-btn warning" onclick="hidePopup('popupEmpty')"><i class="fas fa-pen"></i> Ubah Nilai</button>
    </div>
</div>

<!-- ═══════ POPUP LIMIT ═══════ -->
<div class="popup-overlay" id="popupLimit">
    <div class="popup-card">
        <div class="popup-icon warning"><i class="fas fa-exclamation"></i></div>
        <h2 class="popup-title">Nilai Tidak Valid</h2>
        <p class="popup-desc">Perbaiki nilai berikut sebelum menyimpan:</p>
        <ul class="err-list" id="limitList"></ul>
        <button class="popup-btn danger" onclick="hidePopup('popupLimit')"><i class="fas fa-pen"></i> Perbaiki</button>
    </div>
</div>

<!-- ═══════ POPUP KONFIRMASI ═══════ -->
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
   SIDEBAR LOGIC (UI only — tidak menyentuh business logic)
   ================================================================ */
function openSidebar(){
    document.getElementById('sidebar').classList.add('open');
    var ov=document.getElementById('sidebarOverlay');
    ov.style.display='block';
    requestAnimationFrame(function(){ov.classList.add('show');});
    document.body.style.overflow='hidden';
}
function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    var ov=document.getElementById('sidebarOverlay');
    ov.classList.remove('show');
    setTimeout(function(){ov.style.display='none';},300);
    document.body.style.overflow='';
}

/* Active section tracking via IntersectionObserver */
(function(){
    var links=document.querySelectorAll('.sidebar-link[data-section]');
    if(!links.length)return;
    var observer=new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(e.isIntersecting){
                links.forEach(function(l){l.classList.remove('active');});
                var active=document.querySelector('.sidebar-link[data-section="'+e.target.id+'"]');
                if(active)active.classList.add('active');
            }
        });
    },{rootMargin:'-20% 0px -60% 0px',threshold:0});
    links.forEach(function(l){
        var sec=document.getElementById(l.getAttribute('data-section'));
        if(sec)observer.observe(sec);
    });
    /* Close sidebar on link click (mobile) */
    links.forEach(function(l){
        l.addEventListener('click',function(){
            if(window.innerWidth<=1024)closeSidebar();
        });
    });
})();

/* Hide "Export" and "Review" text on small nav buttons */
(function(){
    var style=document.createElement('style');
    style.textContent='@media(max-width:900px){.hide-mobile{display:none;}}';
    document.head.appendChild(style);
})();
</script>

<script>
var formFields = {
    overall:[{id:'impression',label:'Impression',desc:'Kelipatan 5 (10-90)'}],
    head:[{id:'size',label:'Size (Ukuran)',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk Kepala',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_head_penalty'}],
    face:[{id:'face',label:'Face',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_face_penalty'}],
    body:[{id:'bentuk',label:'Bentuk Badan',desc:'Kelipatan 5 (10-90)'},{id:'proporsi',label:'Proporsional',desc:'Kelipatan 5 (10-90)'},{id:'pangkal',label:'Pangkal',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_body_penalty'}],
    marking:[{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'},{id:'contrast',label:'Contrast',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk',desc:'Kelipatan 5 (10-90)'}],
    pearl:[{id:'shining',label:'Shining',desc:'Kelipatan 5 (10-90)'},{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk',desc:'Kelipatan 5 (10-90)'}],
    color:[{id:'komposisi',label:'Komposisi',desc:'Kelipatan 5 (10-90)'},{id:'kecerahan',label:'Kecerahan',desc:'Kelipatan 5 (10-90)'},{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'}],
    finnage:[{id:'bentuk',label:'Bentuk Sirip & Ekor',desc:'Kelipatan 5 (10-90)'},{id:'kecerahan',label:'Kecerahan',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_finnage_penalty'}]
};
var formFieldsLegacy = {
    face:[{id:'pipi',label:'Pipi'},{id:'mata',label:'Mata'},{id:'bibir',label:'Bibir'},{id:'kondisi',label:'Kondisi Mata & Insang'}]
};
var MINOR_DEFECTS=['Kutil','Bibir Miring','Bibir Miring (kasat mata)','Katarak','Abses / Luka','Fintail Bleaching','Fintail Bleaching / Transparan','Pangkal Ekor Naik/Trn','Pangkal Ekor Naik atau Turun','Dayung Tdk Seimbang','Sirip Dayung Tidak Seimbang'];
var MAYOR_DEFECTS=['Bagian Bibir Hilang','Mulut Terbuka Terus','Bibir Tidak Menutup Sempurna & Selaput Bergerak','Muka Miring','Pangkal Bengkok/Patah','Pangkal Bengkok / Melintir','Fin/Tulang Hilang 1 Ruas'];
var DEFECT_OPTIONS={
    raw_head_penalty:[{label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},{label:'--- MINOR ---',options:[{value:'Kutil',label:'Kutil'}]}],
    raw_face_penalty:[{label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},{label:'--- MINOR ---',options:[{value:'Bibir Miring (kasat mata)',label:'Bibir Miring (kasat mata)'},{value:'Katarak',label:'Katarak'}]},{label:'--- MAYOR ---',options:[{value:'Bagian Bibir Hilang',label:'Bagian Bibir Hilang'},{value:'Bibir Tidak Menutup Sempurna & Selaput Bergerak',label:'Bibir Tidak Menutup Sempurna & Selaput Bergerak'},{value:'Muka Miring',label:'Muka Miring'}]}],
    raw_body_penalty:[{label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},{label:'--- MINOR ---',options:[{value:'Kutil',label:'Kutil'},{value:'Abses / Luka',label:'Abses / Luka'}]}],
    raw_finnage_penalty:[{label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},{label:'--- MINOR ---',options:[{value:'Kutil',label:'Kutil'},{value:'Fintail Bleaching / Transparan',label:'Fintail Bleaching / Transparan'},{value:'Pangkal Ekor Naik atau Turun',label:'Pangkal Ekor Naik atau Turun'},{value:'Sirip Dayung Tidak Seimbang',label:'Sirip Dayung Tidak Seimbang'}]},{label:'--- MAYOR ---',options:[{value:'Fin/Tulang Hilang 1 Ruas',label:'Fin/Tulang Hilang 1 Ruas'},{value:'Pangkal Bengkok / Melintir',label:'Pangkal Bengkok / Melintir'}]}]
};
function getStandardOptionsGJ(){var o=[];for(var i=90;i>=10;i-=5)o.push({value:i.toString(),label:i.toString()});return o;}
var DEFECT_LEGACY_MAP={'Bibir Miring':'Bibir Miring (kasat mata)','Fintail Bleaching':'Fintail Bleaching / Transparan','Pangkal Ekor Naik/Trn':'Pangkal Ekor Naik atau Turun','Dayung Tdk Seimbang':'Sirip Dayung Tidak Seimbang','Mulut Terbuka Terus':'Bibir Tidak Menutup Sempurna & Selaput Bergerak','Pangkal Bengkok/Patah':'Pangkal Bengkok / Melintir'};
function normalizeDefectLegacy(arr){return arr.map(function(v){return DEFECT_LEGACY_MAP[v]||v;});}
function normDefArr(v){if(!v)return['0'];if(typeof v==='string')return[v];if(Array.isArray(v))return v;return['0'];}
var editDefectData={raw_head_penalty:['0'],raw_face_penalty:['0'],raw_body_penalty:['0'],raw_finnage_penalty:['0']};
var originalDefectData={raw_head_penalty:['0'],raw_face_penalty:['0'],raw_body_penalty:['0'],raw_finnage_penalty:['0']};
var activeDefectKeyGJ=null;
function evaluateDefectsGJ(){var parts=['head','face','body','finnage'];var partStatus={};parts.forEach(function(p){partStatus[p]={minor:false,mayor:false,items:[]};});parts.forEach(function(p){var defs=normDefArr(editDefectData['raw_'+p+'_penalty']);defs.forEach(function(d){if(d&&d!=='0'){partStatus[p].items.push(d);if(MINOR_DEFECTS.indexOf(d)!==-1)partStatus[p].minor=true;if(MAYOR_DEFECTS.indexOf(d)!==-1)partStatus[p].mayor=true;}});});var componentsWithMinor=0;parts.forEach(function(p){if(partStatus[p].minor)componentsWithMinor++;});var isGlobalMayor=componentsWithMinor>=3;var results={};parts.forEach(function(p){if(partStatus[p].items.length>0){var isM=partStatus[p].mayor||(partStatus[p].minor&&isGlobalMayor);results[p+'_penalty']=isM?'30%':'10%';}else{results[p+'_penalty']='';}});return results;}
function openDefectModalGJ(defectKey){activeDefectKeyGJ=defectKey;var partName=defectKey.replace('raw_','').replace('_penalty','').toUpperCase();document.getElementById('defectModalTitleGJ').innerText='Pilih Defect - '+partName;var options=DEFECT_OPTIONS[defectKey];var currentValues=editDefectData[defectKey]||['0'];var html='';options.forEach(function(group){html+='<div class="gj-defect-group"><div class="gj-defect-group-title">'+group.label+'</div><div class="gj-defect-options">';group.options.forEach(function(opt){var isChecked=currentValues.indexOf(opt.value)!==-1;html+='<label class="gj-defect-option '+(isChecked?'selected':'')+'" onclick="toggleDefectGJ(\''+defectKey+'\',\''+opt.value.replace(/'/g,"\\'")+'\')"><input type="checkbox" '+(isChecked?'checked':'')+' onclick="event.stopPropagation();toggleDefectGJ(\''+defectKey+'\',\''+opt.value.replace(/'/g,"\\'")+'\')"><span>'+opt.label+'</span></label>';});html+='</div></div>';});document.getElementById('defectModalBodyGJ').innerHTML=html;openModal('modalDefectGJ');}
function closeDefectModalGJ(){closeModal('modalDefectGJ');activeDefectKeyGJ=null;renderEditInputs(currentEditKat);}
function toggleDefectGJ(defectKey,value){var current=editDefectData[defectKey]||['0'];if(value==='0'){editDefectData[defectKey]=['0'];}else{current=current.filter(function(v){return v!=='0';});if(current.indexOf(value)!==-1){current=current.filter(function(v){return v!==value;});}else{current.push(value);}if(current.length===0)current=['0'];editDefectData[defectKey]=current;}openDefectModalGJ(defectKey);}

/* ================================================================ STATE ================================================================ */
var currentId=null;var currentPData=null;var editMemory={};var originalValues={};var currentEditKat='overall';var allIkanDataGJ={};var _confirmCallback=null;
function popupConfirm(title,desc,btnText,callback){document.getElementById('popupConfirmTitle').textContent=title||'Konfirmasi';document.getElementById('popupConfirmDesc').innerHTML=desc||'';document.getElementById('popupConfirmBtn').innerHTML='<i class="fas fa-check"></i> '+(btnText||'Ya, Lanjutkan');_confirmCallback=callback;showPopup('popupConfirm');}
function executeConfirm(){hidePopup('popupConfirm');if(typeof _confirmCallback==='function')_confirmCallback();_confirmCallback=null;}
function cancelConfirm(){hidePopup('popupConfirm');_confirmCallback=null;}

/* ================================================================ HELPERS ================================================================ */
function showPopup(id){document.getElementById(id).classList.add('show');}
function hidePopup(id){document.getElementById(id).classList.remove('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
function getCsrf(){return document.querySelector('meta[name="csrf-token"]').getAttribute('content');}
function esc(s){if(!s)return '';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function openModal(id){document.getElementById(id).classList.add('show');}
['modalDetail','modalEdit','modalGeneric'].forEach(function(id){document.getElementById(id).addEventListener('click',function(e){if(e.target===this)closeModal(id);});});
document.getElementById('modalDefectGJ').addEventListener('click',function(e){if(e.target===this)closeDefectModalGJ();});
function freshMemory(){var m={};Object.keys(formFields).forEach(function(k){m[k]={};});return m;}
function cloneValues(source){var m={};Object.keys(formFields).forEach(function(k){m[k]={};formFields[k].forEach(function(f){if(f.type==='defect')return;var v=(source&&source[k]&&source[k][f.id]);if(v===undefined&&f.id==='shining'&&source&&source[k]&&source[k].shinning!==undefined)v=source[k].shinning;m[k][f.id]=(v!==undefined&&v!==null&&v!=='')?String(v):'';});});return m;}

function kunciNilai(id){
    fetch('/api/grand-juri/kunci-nilai',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({_token:getCsrf(),ikan_id:id})})
    .then(function(r){return r.json();}).then(function(d){if(d.success){document.getElementById('popupSuccessDesc').textContent=d.message;showPopup('popupSuccess');loadPeserta(document.getElementById('searchInput').value);}else{document.getElementById('popupErrorDesc').textContent=d.message||'Gagal.';showPopup('popupError');}})
    .catch(function(){document.getElementById('popupErrorDesc').textContent='Kesalahan jaringan.';showPopup('popupError');});
}

/* ================================================================ LOAD STATS ================================================================ */
function loadStats(){
    fetch('/api/grand-juri/stats',{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(d){
        if(!d.total_tank&&d.total_tank!==0)return;
        document.getElementById('statTank').innerText=d.total_tank;document.getElementById('statPeserta').innerText=d.total_peserta;document.getElementById('statSudah').innerText=d.sudah_plot;document.getElementById('statBelum').innerText=d.belum_plot;document.getElementById('statSisa').innerText=d.sisa_tank;document.getElementById('statSisaLabel').innerText='Sisa Tank (Max '+d.max_tank+')';
        var grid=document.getElementById('rincianGrid');grid.innerHTML='';
        if(d.rincian)d.rincian.forEach(function(r){grid.innerHTML+='<div class="rincian-card" onclick="openRincianDetail(\''+esc(r.kategori)+'\')"><div class="rincian-cat"><i class="fas fa-arrow-up-right-from-square"></i>'+esc(r.kategori)+'</div><div class="rincian-data"><div class="rincian-ekor">'+r.ekor+' Ekor</div></div></div>';});
    }).catch(function(err){console.error('Gagal load stats:',err);});
}

/* ================================================================ LOAD DAFTAR JURI ================================================================ */
function loadJuriSummary(){
    fetch('/api/grand-juri/juri-summary',{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){
        var body=document.getElementById('juriSummaryBody');
        if(!data||data.length===0){body.innerHTML='<div class="empty-state"><i class="fas fa-user-slash" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada juri yang memberikan penilaian.</div>';return;}
        body.innerHTML='<div class="juri-chip-list">';data.forEach(function(j){var isGrand=j.role==='grand_juri';body.innerHTML+='<div class="juri-chip '+(isGrand?'juri-grand':'juri-awal')+'" onclick="openJuriPeserta('+j.juri_id+',\''+esc(j.name)+'\',\''+j.role+'\')"><i class="fas '+(isGrand?'fa-crown':'fa-user-pen')+'"></i><span class="chip-role">'+(isGrand?'Grand':'Juri')+'</span><span>'+esc(j.name)+'</span><span class="chip-count">('+j.total_peserta+')</span><i class="fas fa-chevron-right chip-arrow"></i></div>';});body.innerHTML+='</div>';
    }).catch(function(){document.getElementById('juriSummaryBody').innerHTML='<div class="empty-state">Gagal memuat data juri.</div>';});
}

/* ================================================================ LOAD TABLE ================================================================ */
function loadPeserta(search){
    search=search||'';var url='/api/grand-juri/peserta'+(search?'?search='+encodeURIComponent(search):'');
    fetch(url,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){
        var tbody=document.getElementById('tbodyPeserta');tbody.innerHTML='';
        if(!data||data.length===0){tbody.innerHTML='<tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Tidak ada data ditemukan.</div></td></tr>';return;}
        data.forEach(function(p){
            allIkanDataGJ[p.id]=p;var tr=document.createElement('tr');
            var td2=document.createElement('td');td2.style.cssText='font-size:12px;font-weight:600;color:var(--text-muted);';td2.innerText=(p.kategori||'—')+' - '+(p.kelas||'—');tr.appendChild(td2);
            var td3=document.createElement('td');td3.style.cssText='font-weight:700;color:var(--purple);';td3.innerText=p.nomor_tank?'Tank '+p.nomor_tank:'—';tr.appendChild(td3);
            var td4=document.createElement('td');td4.style.cssText='font-size:12px;color:var(--text-muted);';td4.innerText=p.detail_anggota||'—';tr.appendChild(td4);
            var td5=document.createElement('td');
            if(p.juri_list&&p.juri_list.length>0){var jHtml='<div class="juri-cell">';p.juri_list.forEach(function(j){if(j.is_grand&&j.is_editor){jHtml+='<div class="grand-line"><i class="fas fa-pen-to-square"></i> '+esc(j.name)+' <span style="font-size:9px;opacity:.7;">(edit)</span></div>';}else if(j.is_grand){jHtml+='<div class="grand-line"><i class="fas fa-crown"></i> '+esc(j.name)+'</div>';}else{jHtml+='<div><i class="fas fa-user-pen" style="font-size:10px;margin-right:3px;"></i>'+esc(j.name)+'</div>';}});jHtml+='</div>';td5.innerHTML=jHtml;}else{td5.innerHTML='<span style="font-size:12px;color:var(--text-muted);">—</span>';}tr.appendChild(td5);
            var td6=document.createElement('td');var totalHtml=p.total_nilai_semua>0?'<span class="total-cell">'+p.total_nilai_semua+'</span>':'<span class="total-cell zero">—</span>';if(p.jumlah_juri_yang_nilai>1)totalHtml+='<div style="font-size:9px;color:var(--text-muted);font-weight:600;"><i class="fas fa-users" style="font-size:8px;margin-right:2px;"></i>'+p.jumlah_juri_yang_nilai+' juri</div>';td6.innerHTML=totalHtml;tr.appendChild(td6);
            var td6b=document.createElement('td');var finalPt=p.final_point||p.total_point||0;var bonusPt=p.total_bonus||0;if(finalPt>0){var ptHtml='<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:500;color:'+(bonusPt>0?'#34D399':'var(--primary)')+';letter-spacing:-.02em;">'+finalPt+'</div>';if(bonusPt>0)ptHtml+='<div style="font-size:9px;color:#34D399;font-weight:800;"><i class="fas fa-trophy" style="font-size:7px;"></i> +'+bonusPt+' bonus</div>';td6b.innerHTML=ptHtml;}else{td6b.innerHTML='<span style="font-size:12px;color:var(--text-muted);">—</span>';}tr.appendChild(td6b);
            var td7=document.createElement('td');if(p.is_locked){td7.innerHTML='<span class="badge badge-success"><i class="fas fa-lock" style="margin-right:3px;font-size:9px;"></i>NILAI FINAL</span>';}else if(p.grand_juri_nama){td7.innerHTML='<span class="badge badge-purple"><i class="fas fa-crown" style="margin-right:3px;font-size:9px;"></i>GRAND EDITED</span>';}else{td7.innerHTML='<span class="badge '+(p.status_class||'badge-success')+'">'+(p.status||'—').toUpperCase()+'</span>';}tr.appendChild(td7);
            var td8=document.createElement('td');if(p.is_locked){td8.innerHTML='<div class="action-group"><button class="btn-sm btn-detail" onclick="openDetail('+p.id+')"><i class="fas fa-eye"></i> Detail</button><button class="btn-sm btn-lock" style="background:var(--primary-light);color:var(--primary);border-color:rgba(34,211,238,.20);" onclick="kunciNilai('+p.id+')" title="Buka kunci nilai"><i class="fas fa-lock-open"></i> Buka</button></div>';}else{var lockHtml='<button class="btn-sm btn-lock" onclick="kunciNilai('+p.id+')" title="Kunci nilai (final)"><i class="fas fa-lock-open"></i> Kunci</button>';
                td8.innerHTML='<div class="action-group"><button class="btn-sm btn-detail" onclick="openDetail('+p.id+')"><i class="fas fa-eye"></i> Detail</button><button class="btn-sm btn-edit" onclick="openEdit('+p.id+')"><i class="fas fa-pen-to-square"></i> Edit</button>'+lockHtml+'</div>';}tr.appendChild(td8);tbody.appendChild(tr);
        });
    }).catch(function(){document.getElementById('tbodyPeserta').innerHTML='<tr><td colspan="8"><div class="empty-state">Gagal memuat data.</div></td></tr>';});
}
var searchT;document.getElementById('searchInput').addEventListener('input',function(){var q=this.value;clearTimeout(searchT);searchT=setTimeout(function(){loadPeserta(q);},300);});

/* ================================================================ FETCH SINGLE ================================================================ */
function fetchSingle(id,cb){fetch('/api/grand-juri/peserta?id='+id,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){cb(data&&data[0]?data[0]:null);}).catch(function(){cb(null);});}

/* ================================================================ MODAL DETAIL ================================================================ */
function openDetail(id){currentId=id;document.getElementById('detailContent').innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="font-size:20px;display:block;margin-bottom:8px;"></i>Memuat...</div>';document.getElementById('modalDetail').classList.add('show');fetchSingle(id,function(p){if(!p){document.getElementById('detailContent').innerHTML='<div class="empty-state">Data tidak ditemukan.</div>';return;}currentPData=p;var editBtn=document.getElementById('btnToEdit');if(p.is_locked){editBtn.style.display='none';}else{editBtn.style.display='';editBtn.onclick=function(){closeModal('modalDetail');openEdit(id);};}renderDetail(p);});}

function renderDetail(p){var html='';html+='<div class="detail-info-banner"><div><div class="detail-meta">';html+='<span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank||'—')+'</span>';html+='<span><i class="fas fa-tag"></i> '+(p.kategori||'—')+' - '+(p.kelas||'—')+'</span>';if(p.detail_anggota&&p.detail_anggota!=='—')html+='<span><i class="fas fa-users"></i> '+esc(p.detail_anggota)+'</span>';html+='</div></div>';var allIn=p.submitted_juri_count>=p.total_juri_all;html+='<span class="juri-submit-chip '+(allIn?'ok':'not-ok')+'"><i class="fas '+(allIn?'fa-check-circle':'fa-clock')+'"></i> '+p.submitted_juri_count+' dari '+p.total_juri_all+' juri kirim</span></div>';
if(p.is_locked){html+='<div class="detail-note purple-note"><i class="fas fa-lock"></i><span>Nilai ini sudah <strong>TERKUNCI (FINAL)</strong> dan tidak dapat diubah.</span></div>';}else if(!allIn){html+='<div class="detail-note"><i class="fas fa-info-circle"></i><span>Masih ada <strong>'+(p.total_juri_all-p.submitted_juri_count)+'</strong> juri yang belum mengirim. Tombol <strong>Kunci</strong> aktif setelah semua juri mengirim.</span></div>';}
if(!p.all_scorings||p.all_scorings.length===0){html+='<div class="empty-state" style="padding:40px;"><i class="fas fa-clipboard-list" style="font-size:36px;display:block;margin-bottom:10px;color:var(--text-faint);"></i>Belum ada nilai yang dikirim juri.</div>';document.getElementById('detailContent').innerHTML=html;return;}
p.all_scorings.forEach(function(sc,idx){var uid='dj-'+idx;var iconCls='fas fa-user-pen';var label='Juri: '+esc(sc.juri_name);if(sc.edited_by_grand&&sc.grand_juri_name){label+=' <span style="color:var(--purple);font-size:11px;font-weight:600;"><i class="fas fa-pen-to-square" style="font-size:9px;"></i> diedit: '+esc(sc.grand_juri_name)+'</span>';}html+='<div class="detail-juri-accordion"><div class="detail-juri-toggle" id="'+uid+'-toggle" onclick="toggleJuriDetail(\''+uid+'\')"><span class="dj-name"><i class="'+iconCls+'" style="font-size:11px;"></i> '+label+'</span><span style="display:flex;align-items:center;gap:10px;"><span class="dj-total">'+sc.total_nilai+'</span><i class="fas fa-chevron-down dj-arrow"></i></span></div><div class="detail-juri-scores" id="'+uid+'-scores">';var nd=sc.nilai_detail;if(!nd||typeof nd!=='object'){html+='<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada data nilai.</div>';}else{Object.keys(formFields).forEach(function(kat){var fields=formFields[kat];if(kat==='face'&&nd.face){if(nd.face.face===undefined&&(nd.face.pipi!==undefined||nd.face.mata!==undefined))fields=formFieldsLegacy.face;}html+='<div style="margin-bottom:10px;border:1px solid var(--bd-2);border-radius:10px;overflow:hidden;">';var katNilai=nd[kat]||{};var sub=0;fields.forEach(function(f){if(f.type==='defect')return;var fv=katNilai[f.id];if(fv===undefined&&f.id==='shining'&&katNilai.shinning!==undefined)fv=katNilai.shinning;if(fv!==undefined&&fv!==null)sub+=parseInt(fv)||0;});var defectEval=sc.defect_eval||{};var penaltyKey=kat+'_penalty';var penaltyStr=defectEval[penaltyKey]||'';var defectPersen=0;var hasDefect=false;var defectNames=[];if(penaltyStr&&penaltyStr!==''){hasDefect=true;defectPersen=parseInt(penaltyStr)||0;var rawKey='raw_'+kat+'_penalty';var rawDefs=sc[rawKey];if(rawDefs){if(!Array.isArray(rawDefs))rawDefs=[rawDefs];defectNames=rawDefs.filter(function(v){return v&&v!=='0';});}}var displaySub=sub;if(hasDefect&&defectPersen>0)displaySub=Math.round(sub*(1-defectPersen/100)*10)/10;if(hasDefect&&defectPersen>0){html+='<div class="detail-kat-mini"><span>'+kat.toUpperCase()+'</span><span>Subtotal: <s style="color:var(--text-faint);font-size:10px;">'+sub+'</s> → <strong style="color:var(--purple);">'+displaySub+'</strong> <span style="color:#fca5a5;font-weight:700;">(-'+defectPersen+'%)</span></span></div>';}else{html+='<div class="detail-kat-mini"><span>'+kat.toUpperCase()+'</span><span>Subtotal: '+sub+'</span></div>';}var hasDefectField=fields.some(function(f){return f.type==='defect';});fields.forEach(function(f){if(f.type==='defect')return;var val=katNilai[f.id];if(val===undefined&&f.id==='shining'&&katNilai.shinning!==undefined)val=katNilai.shinning;var has=(val!==undefined&&val!==null&&val!=='');html+='<div class="detail-field-row"><div class="detail-field-left"><div class="detail-field-name">'+f.label+'</div><div class="detail-field-meta">'+f.desc+'</div></div><span class="score-chip '+(has?'filled':'empty')+'">'+(has?val:'N/A')+'</span></div>';});if(hasDefectField){if(hasDefect&&defectPersen>0&&defectNames.length>0){var isMayor=defectPersen>=30;html+='<div class="detail-field-row" style="background:'+(isMayor?'var(--danger-lt)':'var(--warning-lt)')+';"><div class="detail-field-left"><div class="detail-field-name" style="color:'+(isMayor?'#fca5a5':'var(--gold-300)')+';"><i class="fas fa-exclamation-triangle" style="margin-right:4px;font-size:10px;"></i>Defect '+(isMayor?'(MAYOR)':'(MINOR)')+'</div><div class="detail-field-meta" style="color:'+(isMayor?'#fca5a5':'var(--gold-300)')+';font-weight:600;">'+defectNames.join(', ')+'</div></div><span class="score-chip" style="background:'+(isMayor?'var(--danger-lt)':'var(--warning-lt)')+';color:'+(isMayor?'#fca5a5':'var(--gold-300)')+';font-weight:800;">-'+defectPersen+'%</span></div>';}else{html+='<div class="detail-field-row" style="background:var(--success-lt);"><div class="detail-field-left"><div class="detail-field-name" style="color:#34D399;"><i class="fas fa-check-circle" style="margin-right:4px;font-size:10px;"></i>Defect</div><div class="detail-field-meta" style="color:#059669;font-weight:600;">Tidak ada defect</div></div><span class="score-chip" style="background:var(--success-lt);color:#34D399;font-weight:800;">AMAN</span></div>';}}html+='</div>';});}html+='</div></div>';});
if(p.detail_list_per_juri&&p.detail_list_per_juri.length>0){html+='<div style="margin-top:16px;border:2px solid rgba(124,58,237,.25);border-radius:14px;overflow:hidden;">';html+='<div style="padding:12px 16px;background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(124,58,237,.05));border-bottom:2px solid rgba(124,58,237,.25);display:flex;justify-content:space-between;align-items:center;">';html+='<span style="font-size:13px;font-weight:800;color:var(--text-hi);"><i class="fas fa-calculator" style="margin-right:6px;color:var(--purple);"></i>Ringkasan Nilai & Point</span>';html+='<span style="font-size:11px;color:var(--purple);font-weight:700;">'+p.jumlah_juri_yang_nilai+' juri</span></div>';html+='<table style="width:100%;border-collapse:collapse;font-size:12px;"><thead><tr style="background:rgba(124,58,237,.06);"><th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid rgba(124,58,237,.20);">JURI</th><th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid rgba(124,58,237,.20);">TOTAL NILAI</th></tr></thead><tbody>';var grandTotalNilai=0;p.detail_list_per_juri.forEach(function(j){if(!j.is_grand){grandTotalNilai+=j.total_nilai;html+='<tr style="background:transparent;"><td style="padding:10px 16px;font-weight:600;border-bottom:1px solid var(--bd-1);color:var(--text);">'+esc(j.juri_name)+'</td><td style="padding:10px 16px;font-weight:800;text-align:right;border-bottom:1px solid var(--bd-1);">'+j.total_nilai+'</td></tr>';}});html+='<tr style="background:rgba(124,58,237,.08);border-top:2px solid rgba(124,58,237,.25);"><td style="padding:12px 16px;font-weight:800;color:var(--purple);font-size:11px;text-transform:uppercase;letter-spacing:.3px;">Total Semua Juri</td><td style="padding:12px 16px;font-family:\'Fraunces\',serif;font-weight:500;text-align:right;color:var(--purple);font-size:16px;letter-spacing:-.02em;">'+grandTotalNilai+'</td></tr></tbody></table>';var basePoint=p.total_point??0;var bonusTotal=p.total_bonus||0;var finalPoint=p.final_point||basePoint;html+='<div style="display:grid;grid-template-columns:1fr auto;border-top:2px solid rgba(124,58,237,.25);"><div style="padding:14px 16px;background:transparent;display:flex;flex-direction:column;justify-content:center;gap:2px;"><div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Total Point</div><div style="font-size:10px;color:var(--text-muted);">Dihitung dari rata-rata '+p.jumlah_juri_yang_nilai+' juri</div></div><div style="padding:14px 20px;background:transparent;display:flex;align-items:center;justify-content:flex-end;min-width:160px;"><div style="text-align:right;"><div style="font-family:\'Fraunces\',serif;font-size:22px;font-weight:500;color:'+(bonusTotal>0?'#34D399':'var(--gold-300)')+';line-height:1;letter-spacing:-.02em;">'+finalPoint+'</div>';if(bonusTotal>0)html+='<div style="font-size:9px;color:#34D399;font-weight:700;margin-top:3px;">Dasar '+basePoint+' + Bonus +'+bonusTotal+'</div>';html+='</div></div></div></div>';}document.getElementById('detailContent').innerHTML=html;}

function toggleJuriDetail(uid){var t=document.getElementById(uid+'-toggle');var s=document.getElementById(uid+'-scores');if(t.classList.contains('open')){t.classList.remove('open');s.classList.remove('open');}else{t.classList.add('open');s.classList.add('open');}}

/* ================================================================ MODAL EDIT ================================================================ */
function openEdit(id){currentId=id;currentEditKat='overall';fetchSingle(id,function(p){if(!p){document.getElementById('popupErrorDesc').textContent='Data peserta tidak ditemukan.';showPopup('popupError');return;}if(p.is_locked){document.getElementById('popupErrorDesc').innerHTML='Nilai ini sudah <b>TERKUNCI (FINAL)</b> dan tidak dapat diubah.';showPopup('popupError');return;}currentPData=p;if(p.nilai_detail&&typeof p.nilai_detail==='object'){editMemory=cloneValues(p.nilai_detail);}else{editMemory=freshMemory();}originalValues=cloneValues(editMemory);editDefectData={raw_head_penalty:['0'],raw_face_penalty:['0'],raw_body_penalty:['0'],raw_finnage_penalty:['0']};if(p.all_scorings&&p.all_scorings.length>0){var targetSc=p.all_scorings.find(function(s){return s.is_grand;})||p.all_scorings[0];if(targetSc){editDefectData.raw_head_penalty=normalizeDefectLegacy(normDefArr(targetSc.raw_head_penalty));editDefectData.raw_face_penalty=normalizeDefectLegacy(normDefArr(targetSc.raw_face_penalty));editDefectData.raw_body_penalty=normalizeDefectLegacy(normDefArr(targetSc.raw_body_penalty));editDefectData.raw_finnage_penalty=normalizeDefectLegacy(normDefArr(targetSc.raw_finnage_penalty));}}originalDefectData=JSON.parse(JSON.stringify(editDefectData));var info='Tank '+(p.nomor_tank||'—');info+='<br><span style="font-size:11px;color:var(--purple);">'+esc(p.kategori)+' - '+(p.kelas||'—')+' | '+esc(p.detail_anggota||'—')+'</span>';if(p.juri_nama&&p.juri_nama!=='—'){info+='<br><span style="font-size:11px;"><i class="fas fa-user-pen" style="margin-right:3px;"></i> Nilai asli dari: <b>'+esc(p.juri_nama)+'</b></span>';}else{info+='<br><span style="font-size:11px;color:var(--warning);"><i class="fas fa-info-circle" style="margin-right:3px;"></i> Belum ada nilai dari juri — Grand Juri menginput baru</span>';}if(p.grand_juri_nama){info+='<br><span style="font-size:11px;color:var(--purple);"><i class="fas fa-crown" style="margin-right:3px;"></i> Terakhir diedit oleh: <b>'+esc(p.grand_juri_nama)+'</b></span>';}document.getElementById('editInfo').innerHTML=info;renderEditList();renderEditInputs('overall');document.getElementById('modalEdit').classList.add('show');});}

function renderEditList(){var c=document.getElementById('editKatList');c.innerHTML='';Object.keys(formFields).forEach(function(kat){var changes=countChanges(kat);var btn=document.createElement('button');btn.className='kat-btn'+(kat===currentEditKat?' active':'');btn.innerHTML='<span>'+kat.charAt(0).toUpperCase()+kat.slice(1)+'</span><span class="kat-badge'+(changes>0?' has-changes':'')+'">'+(changes>0?changes:'—')+'</span>';btn.onclick=function(){switchEditKat(kat);};c.appendChild(btn);});}
function countChanges(kat){if(!editMemory[kat]||!originalValues[kat])return 0;var n=0;formFields[kat].forEach(function(f){var cur=String(editMemory[kat][f.id]||'');var ori=String(originalValues[kat][f.id]||'');if(cur!==''&&cur!==ori)n++;});return n;}
function switchEditKat(kat){saveCurrentTab();currentEditKat=kat;renderEditList();renderEditInputs(kat);}

function renderEditInputs(kat){if(!editMemory[kat])editMemory[kat]={};if(!originalValues[kat])originalValues[kat]={};var html='';formFields[kat].forEach(function(f){if(f.type==='defect'){var defectKey=f.defectKey;var currentValues=editDefectData[defectKey]||['0'];var isAman=currentValues.indexOf('0')!==-1||currentValues.length===0;var evaluated=evaluateDefectsGJ();var evalKey=defectKey.substring(4);var evalString=evaluated[evalKey];var btnLabel='AMAN';var btnClass='gj-defect-btn';if(!isAman&&evalString&&evalString!==''){var isMayor=evalString==='30%';var persen=isMayor?30:10;var defectNames=currentValues.filter(function(v){return v!=='0';}).join(', ');btnLabel=defectNames+' (-'+persen+'%)';btnClass='gj-defect-btn '+(isMayor?'mayor':'minor');}html+='<div class="score-row"><div class="score-label"><h4>'+f.label+'</h4><p>'+f.desc+'</p></div><button type="button" class="'+btnClass+'" onclick="openDefectModalGJ(\''+defectKey+'\')">'+btnLabel+'</button></div>';}else{var options=getStandardOptionsGJ();var currentVal=editMemory[kat][f.id]||'';var origVal=originalValues[kat][f.id]||'';var isChanged=(currentVal!==''&&currentVal!==origVal);html+='<div class="score-row"><div class="score-label"><h4>'+f.label+'</h4><p>'+f.desc;if(origVal!=='')html+=' &nbsp;|&nbsp; <span class="orig-val">Nilai juri: <strong>'+origVal+'</strong></span>';html+='</p></div><select class="gj-score-select'+(isChanged?' changed':'')+'" id="edit-'+f.id+'" onchange="onEditInput(this,\''+kat+'\',\''+f.id+'\')"><option value="">-</option>';options.forEach(function(opt){html+='<option value="'+opt.value+'"'+(currentVal==opt.value?' selected':'')+'>'+opt.label+'</option>';});html+='</select></div>';}});html+='<div class="subtotal-bar">Subtotal <em>'+kat+'</em>: <span id="subVal">0</span></div>';document.getElementById('editFormArea').innerHTML=html;updateSub(kat);}

function onEditInput(el,kat,fid){var cur=el.value;var ori=originalValues[kat]?String(originalValues[kat][fid]||''):'';el.classList.remove('changed');if(cur!==''&&cur!==ori)el.classList.add('changed');if(!editMemory[kat])editMemory[kat]={};editMemory[kat][fid]=el.value;updateSub(kat);renderEditList();}
function updateSub(kat){var t=0;formFields[kat].forEach(function(f){var el=document.getElementById('edit-'+f.id);if(el&&el.value!=='')t+=parseInt(el.value)||0;});var s=document.getElementById('subVal');if(s)s.textContent=t;}
function saveCurrentTab(){if(!formFields[currentEditKat])return;if(!editMemory[currentEditKat])editMemory[currentEditKat]={};formFields[currentEditKat].forEach(function(f){var el=document.getElementById('edit-'+f.id);if(el)editMemory[currentEditKat][f.id]=el.value;});}

/* ================================================================ SUBMIT ================================================================ */
document.getElementById('btnSaveGrand').addEventListener('click',function(){submitEdit();});
function submitEdit(){saveCurrentTab();var payload={};var totalChanged=0;Object.keys(formFields).forEach(function(kat){formFields[kat].forEach(function(f){if(f.type==='defect')return;var cur=editMemory[kat]?editMemory[kat][f.id]:'';var ori=originalValues[kat]?originalValues[kat][f.id]:'';if(cur===''&&ori==='')return;if(String(cur)===String(ori))return;var val=parseInt(cur);if(isNaN(val))return;if(val<0)return;if(!payload[kat])payload[kat]={};payload[kat][f.id]=val;totalChanged++;});});var defectChanged=false;['raw_head_penalty','raw_face_penalty','raw_body_penalty','raw_finnage_penalty'].forEach(function(key){var cur=JSON.stringify(editDefectData[key]||['0']);var ori=JSON.stringify(originalDefectData[key]||['0']);if(cur!==ori)defectChanged=true;});if(totalChanged===0&&!defectChanged){showPopup('popupEmpty');return;}var btn=document.getElementById('btnSaveGrand');btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MENYIMPAN...';fetch('/api/grand-juri/edit-nilai',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({_token:getCsrf(),ikan_id:currentId,changed_fields:payload,defect_data:defectChanged?editDefectData:null})}).then(function(res){if(!res.ok)return res.json().then(function(d){d._err=true;return d;});return res.json();}).then(function(d){if(d._err||!d.success){document.getElementById('popupErrorDesc').textContent=d.message||'Terjadi kesalahan pada server.';showPopup('popupError');return;}closeModal('modalEdit');document.getElementById('popupSuccessDesc').innerHTML='Total nilai akhir: <strong style="color:var(--purple);font-size:18px;">'+d.total+'</strong><br><span style="font-size:12px;color:var(--text-muted);">'+totalChanged+' komponen diperbarui'+(defectChanged?' + defect data diperbarui':'')+'</span>';showPopup('popupSuccess');loadPeserta(document.getElementById('searchInput').value);loadStats();loadJuriSummary();}).catch(function(){document.getElementById('popupErrorDesc').textContent='Kesalahan jaringan.';showPopup('popupError');}).finally(function(){btn.disabled=false;btn.innerHTML='<i class="fas fa-save"></i> SIMPAN PERUBAHAN';});}

/* ================================================================ GENERIC MODAL — JURI PESERTA ================================================================ */
function openJuriPeserta(juriId,juriName,role){var isGrand=role==='grand_juri';document.getElementById('genericTitle').innerHTML='<i class="fas '+(isGrand?'fa-crown':'fa-user-pen')+'"></i> Peserta yang Dinilai — '+esc(juriName);var content=document.getElementById('genericContent');content.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>';document.getElementById('modalGeneric').classList.add('show');fetch('/api/grand-juri/juri-peserta?juri_id='+juriId+'&role='+role,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){if(!data||data.length===0){content.innerHTML='<div class="empty-state"><i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></>Tidak ada data ditemukan.</div>';return;}
        var html='<div class="detail-note purple-note"><i class="fas fa-circle-info"></i><span>'+(isGrand?'Grand Juri':'Juri')+' <strong>'+esc(juriName)+'</strong> menilai <strong>'+data.length+'</strong> peserta.</span></div>';
        html+='<div class="table-wrap"><table class="gen-table"><thead><tr><th>NO</th><th>NO. TANK</th><th>KATEGORI</th></tr></thead><tbody>';
        data.forEach(function(item,i){html+='<tr><td style="color:var(--text-muted);font-weight:700;">'+(i+1)+'</td><td class="g-tank">'+esc(item.nomor_tank)+'</td><td style="font-size:12px;color:var(--text-muted);">'+esc(item.kategori)+'</td></tr>';});
        html+='</tbody></table></div>';content.innerHTML=html;
    }).catch(function(){content.innerHTML='<div class="empty-state">Gagal memuat data.</div>';});}

/* ================================================================ GENERIC MODAL — RINCIAN DETAIL ================================================================ */
function openRincianDetail(kategori){
    document.getElementById('genericTitle').innerHTML='<i class="fas fa-chart-bar"></i> Detail Kategori: '+esc(kategori);
    var content=document.getElementById('genericContent');content.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>';document.getElementById('modalGeneric').classList.add('show');
    fetch('/api/grand-juri/rincian-detail?kategori='+encodeURIComponent(kategori),{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){
        if(!data){content.innerHTML='<div class="empty-state">Data tidak ditemukan.</div>';return;}
        var html='<div class="detail-note purple-note"><i class="fas fa-circle-info"></i><span>Kategori <strong>'+esc(kategori)+'</strong> — Total <strong>'+data.total_ekor+'</strong> ekor sudah dinilai.</span></div>';
        html+='<div class="table-wrap"><table class="gen-table"><thead><tr><th>NO</th><th>NO. TANK</th><th>DINILAI OLEH</th></tr></thead><tbody>';
        if(!data.data||data.data.length===0){html+='<tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">Tidak ada data ditemukan.</td></tr>';}else{data.data.forEach(function(item,i){html+='<tr><td style="color:var(--text-muted);font-weight:700;">'+(i+1)+'</td><td class="g-tank">'+esc(item.nomor_tank)+'</td><td class="g-juri">'+esc(item.juri_nama)+'</td></tr>';});}
        html+='</tbody></table></div>';content.innerHTML=html;
    }).catch(function(){content.innerHTML='<div class="empty-state">Gagal memuat data.</div>';});}

/* ================================================================ GENERIC MODAL — PLOT STATUS ================================================================ */
function openPlotStatus(status){
    var isSudah=status==='sudah_plot';var label=isSudah?'Sudah Plot':'Belum Plot';var icon=isSudah?'fa-check-double':'fa-clock';
    document.getElementById('genericTitle').innerHTML='<i class="fas '+icon+'"></i> Daftar Peserta '+label;
    var content=document.getElementById('genericContent');content.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;"></i>Memuat data...</div>';document.getElementById('modalGeneric').classList.add('show');
    fetch('/api/grand-juri/plot-status?status='+status,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){
        if(!data||data.length===0){content.innerHTML='<div class="empty-state"><i class="fas '+(isSudah?'fa-check-double':'fa-inbox')+'" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>'+(isSudah?'Belum ada peserta yang sudah di-plot.':'Tidak ada peserta yang belum di-plot — semua sudah!')+'</div>';return;}
        var html='<div class="detail-note purple-note"><i class="fas '+(isSudah?'fa-check-circle':'fa-info-circle')+'"></i><span>Menampilkan <strong>'+data.length+'</strong> peserta yang <strong>'+label.toLowerCase()+'</strong>.</span></div>';
        html+='<div class="table-wrap"><table class="gen-table"><thead><tr><th>NO</th><th>NO. TANK</th><th>KATEGORI</th><th>KELAS</th><th>ASAL</th>';
        if(isSudah)html+='<th>DINILAI OLEH</th><th>TOTAL</th>';
        html+='</tr></thead><tbody>';
        data.forEach(function(item,i){html+='<tr><td style="color:var(--text-muted);font-weight:700;">'+(i+1)+'</td><td class="g-tank">'+esc(item.nomor_tank)+'</td><td style="font-size:12px;color:var(--text-muted);">'+esc(item.kategori)+'</td><td style="font-size:12px;">'+esc(item.kelas)+'</td><td style="font-size:11px;color:var(--text-muted);">'+esc(item.detail_anggota)+'</td>';
        if(isSudah){html+='<td class="g-juri">'+esc(item.juri_nama)+'</td><td class="g-total">'+item.total_nilai+'</td>';}html+='</tr>';});
        html+='</tbody></table></div>';content.innerHTML=html;
    }).catch(function(){content.innerHTML='<div class="empty-state">Gagal memuat data.</div>';});}

/* ================================================================ POINT RANKING ================================================================ */
var pointScope='per_kategori_kelas';

function setPointScope(s){
    pointScope=s;
    document.getElementById('btnScopeKelas').className='btn-sm '+(s==='per_kategori_kelas'?'btn-edit':'btn-detail');
    document.getElementById('btnScopeKat').className='btn-sm '+(s==='per_kategori'?'btn-edit':'btn-detail');
    document.getElementById('btnScopeGlobal').className='btn-sm '+(s==='global'?'btn-edit':'btn-detail');
    if(s!=='global'){document.getElementById('btnScopeGlobal').style.cssText='font-size:11px;padding:7px 14px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);';}
    document.getElementById('pointFilterKategori').style.display=(s==='global')?'none':'';
    document.getElementById('pointFilterKelas').style.display=(s==='global')?'none':'';
    document.getElementById('globalTopNWrap').style.display='none'; // ★ Force Top 10 — selector tidak perlu lagi
    loadPointRanking();
}

function setGlobalTopN(n){document.getElementById('globalTopN').value=n;loadPointRanking();}

function loadPointRanking(){
    var kat=document.getElementById('pointFilterKategori').value;var kelas=document.getElementById('pointFilterKelas').value;
    var params='?scope='+pointScope;if(kat)params+='&kategori='+encodeURIComponent(kat);if(kelas)params+='&kelas='+kelas;
    if(pointScope==='global'){var topN=parseInt(document.getElementById('globalTopN').value)||10;topN=Math.max(1,Math.min(100,topN));document.getElementById('globalTopN').value=topN;params+='&limit='+topN;}
    var el=document.getElementById('pointRankingContent');el.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat ranking...</p></div>';
    fetch('/api/grand-juri/point-ranking'+params,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(groups){
        if(!groups||!groups.length){el.innerHTML='<div class="empty-state"><i class="fas fa-trophy" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada data ikan yang dikunci.</div>';return;}
        var isGlobal=(pointScope==='global');var html='';
        groups.forEach(function(g){
            html+='<div style="margin-bottom:20px;">';
            html+='<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:linear-gradient(135deg,rgba(245,158,11,.10),rgba(245,158,11,.04));border:1px solid rgba(245,158,11,.25);border-radius:10px 10px 0 0;">';
            html+='<div class="card-title" style="font-size:13px;margin:0;"><i class="fas '+(isGlobal?'fa-globe':'fa-layer-group')+'" style="color:var(--gold-500);"></i> '+esc(g.group_name)+'</div>';
            html+='<span style="font-size:11px;color:var(--gold-300);font-weight:700;">'+g.total+' peserta</span></div>';
            html+='<div class="table-wrap" style="border-radius:0 0 10px 10px;border:1px solid rgba(245,158,11,.20);border-top:none;"><table class="result-table" style="min-width:760px;">';
            html+='<thead><tr><th style="width:60px;text-align:center;">#</th>'+(isGlobal?'<th>KATEGORI</th>':'')+'<th style="width:70px;">TANK</th><th style="width:50px;">KELAS</th><th>ASAL/TEAM</th><th style="width:90px;text-align:center;">TOTAL NILAI</th><th style="width:80px;text-align:center;">POINT</th><th style="width:100px;text-align:center;">RANK POINT</th></tr></thead><tbody>';
            g.data.forEach(function(d,i){
                var rankPt=d.rank_point??0;var frp=d.final_rank_point??rankPt;var basePt=d.total_point??0;var bonus=d.total_bonus||0;var posisi=i+1;
                // ★ Warna & medal pakai POSISI (post-bonus), bukan rank base, supaya AER (160) bisa dapat warna Juara 1
                var rankBg,rankColor,rankBorder;
                if(posisi===1){rankBg='rgba(16,185,129,.14)';rankColor='#34D399';rankBorder='rgba(16,185,129,.35)';}
                else if(posisi<=3){rankBg='var(--warning-lt)';rankColor='var(--gold-300)';rankBorder='rgba(245,158,11,.30)';}
                else if(posisi<=6){rankBg='rgba(34,211,238,.08)';rankColor='var(--cyan-300)';rankBorder='rgba(34,211,238,.25)';}
                else if(posisi<=10){rankBg='var(--purple-light)';rankColor='var(--purple)';rankBorder='rgba(124,58,237,.25)';}
                else{rankBg='var(--glass-2)';rankColor='var(--text-faint)';rankBorder='var(--bd-2)';}
                var medalHtml='';
                if(posisi===1)medalHtml='<i class="fas fa-medal" style="color:var(--gold-500);font-size:12px;margin-right:4px;" title="Juara 1"></i>';
                else if(posisi===2)medalHtml='<i class="fas fa-medal" style="color:#C0C0C0;font-size:12px;margin-right:4px;" title="Juara 2"></i>';
                else if(posisi===3)medalHtml='<i class="fas fa-medal" style="color:#CD7F32;font-size:12px;margin-right:4px;" title="Juara 3"></i>';
                html+='<tr><td style="text-align:center;font-weight:800;color:var(--text-muted);white-space:nowrap;">'+medalHtml+posisi+'</td>';
                if(isGlobal)html+='<td style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">'+esc(d.kategori)+'</td>';
                html+='<td style="font-weight:700;color:var(--purple);text-align:center;">'+(d.nomor_tank||'—')+'</td><td style="text-align:center;">'+esc(d.kelas)+'</td><td style="font-size:11px;color:var(--text-muted);">'+esc(d.detail_anggota)+'</td>';
                html+='<td style="text-align:center;"><div style="font-weight:800;">'+(d.total_nilai_semua??0)+'</div>';if(d.jumlah_juri>0)html+='<div style="font-size:9px;color:var(--text-muted);font-weight:600;">'+d.jumlah_juri+' juri</div>';html+='</td>';
                html+='<td style="text-align:center;"><div style="font-family:\'Fraunces\',serif;font-weight:500;font-size:15px;color:var(--primary);letter-spacing:-.02em;">'+basePt+'</div></td>';
                html+='<td style="text-align:center;"><span style="display:inline-block;padding:5px 14px;border-radius:8px;font-size:14px;font-weight:900;background:'+rankBg+';color:'+rankColor+';border:1px solid '+rankBorder+';">'+frp+'</span>';
                if(bonus>0)html+='<div style="font-size:9px;color:#34D399;font-weight:800;margin-top:3px;"><i class="fas fa-trophy" style="font-size:7px;"></i> '+rankPt+' + '+bonus+'</div>';
                html+='</td></tr>';
            });
            html+='</tbody></table></div></div>';
        });el.innerHTML=html;
    }).catch(function(){el.innerHTML='<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation" style="font-size:28px;display:block;margin-bottom:8px;"></i>Gagal memuat data ranking.</div>';});}

/* ================================================================ INIT ================================================================ */
loadStats();loadPeserta();loadJuriSummary();loadPointRanking();

function loadNominasiBadge(){
    fetch('/api/grand-juri/nominasi',{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(d){
        var el=document.getElementById('statNominasi');if(el)el.innerText=d.total_pending||0;
        var sb=document.getElementById('sidebarNominasiBadge');if(sb)sb.innerText=d.total_pending||0;
    }).catch(function(){});
}
loadNominasiBadge();
</script>
</body>
</html>