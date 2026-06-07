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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Grand Juri — Review Nominasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0;}
        input[type=number]{-moz-appearance:textfield;}

        :root{
            --ocean-950:#04070F;--ocean-900:#0B1220;--ocean-850:#0E1729;--ocean-800:#111E36;--ocean-700:#182947;
            --cyan-400:#22D3EE;--cyan-500:#06B6D4;
            --purple:#A78BFA;--purple-light:rgba(124,58,237,0.10);--purple-lt:rgba(124,58,237,0.12);
            --gold-300:#FCD34D;--gold-400:#FBBF24;--gold-500:#F59E0B;--gold-600:#D97706;
            --text-hi:#F8FAFC;--text:#E2E8F0;--text-muted:#94A3B8;--text-low:#64748B;--text-faint:#475569;
            --success:#10B981;--success-lt:rgba(16,185,129,0.12);
            --danger:#EF4444;--danger-lt:rgba(239,68,68,0.12);
            --warning:#F59E0B;--warning-lt:rgba(245,158,11,0.12);
            --glass-1:rgba(255,255,255,0.03);--glass-2:rgba(255,255,255,0.05);--glass-3:rgba(255,255,255,0.08);--glass-strong:rgba(255,255,255,0.12);
            --bd-1:rgba(255,255,255,0.06);--bd-2:rgba(255,255,255,0.10);--bd-3:rgba(255,255,255,0.16);
        }
        html{color-scheme:dark;scroll-behavior:smooth;}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--ocean-900);color:var(--text);min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased;letter-spacing:-0.01em;}

        /* ═══ Ocean Background ═══ */
        .ocean-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(37,99,235,.14) 0%,transparent 55%),radial-gradient(ellipse 50% 50% at 100% 100%,rgba(6,182,212,.08) 0%,transparent 60%),radial-gradient(ellipse 40% 40% at 0% 70%,rgba(124,58,237,.06) 0%,transparent 60%),linear-gradient(180deg,var(--ocean-950) 0%,var(--ocean-900) 45%,var(--ocean-850) 100%);}
        .bubbles{position:fixed;inset:0;z-index:1;pointer-events:none;overflow:hidden;}
        .bubbles span{position:absolute;display:block;border-radius:50%;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,.45),rgba(167,139,250,.15) 60%,transparent 70%);box-shadow:0 0 6px rgba(167,139,250,.15);bottom:-20px;will-change:transform,opacity;animation:bubbleRise linear infinite;opacity:0;}
        .bubbles span:nth-child(1){left:15%;width:7px;height:7px;animation-duration:26s;animation-delay:0s;}
        .bubbles span:nth-child(2){left:45%;width:5px;height:5px;animation-duration:30s;animation-delay:8s;}
        .bubbles span:nth-child(3){left:75%;width:8px;height:8px;animation-duration:24s;animation-delay:4s;}
        @keyframes bubbleRise{0%{transform:translate3d(0,0,0) scale(.9);opacity:0;}10%{opacity:.35;}90%{opacity:.15;}100%{transform:translate3d(12px,-110vh,0) scale(.85);opacity:0;}}

        /* ═══ Top Nav ═══ */
        .top-nav{position:sticky;top:0;z-index:100;background:rgba(11,18,32,.78);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid var(--bd-1);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 0 rgba(255,255,255,.04) inset;}
        .brand{display:flex;align-items:center;gap:10px;min-width:0;}
        .brand h1{font-size:16px;font-weight:800;color:var(--purple);display:flex;align-items:center;gap:8px;letter-spacing:-.01em;white-space:nowrap;}
        .brand span{font-size:10px;color:var(--text-muted);letter-spacing:.04em;text-transform:uppercase;font-weight:600;white-space:nowrap;}
        .nav-right{display:flex;align-items:center;gap:12px;flex-shrink:0;}
        .nav-right .info{text-align:right;}
        .nav-right .info h4{font-size:13px;font-weight:700;color:var(--text-hi);}
        .nav-right .info span{font-size:9px;color:var(--purple);background:var(--purple-light);padding:2px 7px;border-radius:4px;font-weight:700;letter-spacing:.04em;}
        .btn-logout{padding:8px 14px;border-radius:10px;border:1px solid var(--bd-2);background:var(--glass-2);font-size:12px;font-weight:600;cursor:pointer;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;}
        .btn-logout:hover{background:var(--danger-lt);color:#fca5a5;border-color:rgba(239,68,68,.25);}
        .btn-back{padding:8px 14px;border-radius:10px;border:1px solid var(--bd-2);background:var(--glass-2);font-size:12px;font-weight:600;cursor:pointer;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;}
        .btn-back:hover{background:var(--purple-light);color:var(--purple);border-color:rgba(124,58,237,.25);}

        /* ═══ Main Container ═══ */
        .main-wrap{position:relative;z-index:10;max-width:1280px;margin:0 auto;padding:20px 24px 40px;}

        /* ═══ Glass Card ═══ */
        .g-card{background:linear-gradient(180deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.02) 100%);border-radius:20px;border:1px solid var(--bd-1);box-shadow:0 1px 0 rgba(255,255,255,.04) inset,0 16px 32px -16px rgba(0,0,0,.4),0 6px 12px -8px rgba(124,58,237,.05);overflow:hidden;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);animation:cardIn .45s .05s cubic-bezier(.16,1,.3,1) both;}
        @keyframes cardIn{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
        .g-card-head{padding:16px 20px;border-bottom:1px solid var(--bd-1);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;background:rgba(255,255,255,.02);}
        .g-card-head-left{display:flex;align-items:center;gap:12px;min-width:0;}
        .g-card-head-icon{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0;}
        .g-card-head-icon.purple{background:var(--purple-light);color:var(--purple);}
        .g-card-head-icon.gold{background:var(--warning-lt);color:var(--gold-400);}
        .g-card-head-icon.green{background:var(--success-lt);color:#34D399;}
        .g-card-head-icon i{font-size:16px;}
        .g-card-head h3{font-size:14px;font-weight:800;color:var(--text-hi);letter-spacing:-.01em;}
        .g-card-head .sub{font-size:10px;font-weight:700;color:var(--text-muted);}
        .g-card-body{padding:20px;}

        /* ═══ Stats Grid ═══ */
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;}
        .stat-card{padding:20px;border-radius:16px;border:1px solid var(--bd-2);background:var(--glass-2);text-align:center;position:relative;overflow:hidden;transition:all .25s;}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
        .stat-card.purple::before{background:linear-gradient(90deg,var(--purple),#7c3aed);}
        .stat-card.gold::before{background:linear-gradient(90deg,var(--gold-400),var(--gold-600));}
        .stat-card.green::before{background:linear-gradient(90deg,#34D399,var(--success));}
        .stat-card .stat-num{font-family:'Fraunces',serif;font-size:32px;font-weight:500;line-height:1;margin-bottom:4px;color:var(--text-hi);letter-spacing:-.02em;}
        .stat-card .stat-lbl{font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.1em;}
        .stat-card.clickable{cursor:pointer;}
        .stat-card.clickable:hover{transform:translateY(-3px);box-shadow:0 8px 24px -8px rgba(124,58,237,.2);border-color:var(--bd-3);background:var(--glass-3);}

        /* ═══ Nomination Group ═══ */
        .nom-group{margin-bottom:20px;}
        .nom-group:last-child{margin-bottom:0;}
        .nom-group-head{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;background:rgba(255,255,255,.02);border-bottom:1px solid var(--bd-1);}
        .nom-group-info{display:flex;align-items:center;gap:12px;min-width:0;}
        .nom-group-avatar{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:var(--purple-light);flex-shrink:0;}
        .nom-group-avatar i{color:var(--purple);font-size:15px;}
        .nom-group-info h3{font-size:13px;font-weight:800;color:var(--text-hi);}
        .nom-group-info p{font-size:10px;font-weight:700;color:var(--text-muted);}
        .btn-acc-all{padding:8px 16px;border-radius:10px;border:1px solid rgba(16,185,129,.30);background:rgba(16,185,129,.10);font-size:11px;font-weight:700;cursor:pointer;color:#34D399;display:inline-flex;align-items:center;gap:6px;font-family:inherit;transition:all .2s;}
        .btn-acc-all:hover{background:rgba(16,185,129,.20);border-color:rgba(16,185,129,.50);transform:translateY(-1px);}
        .nom-group-body{padding:16px 20px;display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}

        /* ═══ Tank Card ═══ */
        .tank-card{padding:14px;border-radius:14px;border:1px solid var(--bd-2);background:var(--glass-2);transition:all .25s;}
        .tank-card:hover{border-color:var(--bd-3);box-shadow:0 4px 16px -4px rgba(124,58,237,.1);transform:translateY(-2px);background:var(--glass-3);}
        .tank-num{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-weight:800;font-size:18px;color:white;background:linear-gradient(135deg,#7c3aed,var(--purple));box-shadow:0 4px 12px -3px rgba(124,58,237,.4);margin-bottom:12px;}
        .tank-badges{display:flex;flex-direction:column;gap:6px;margin-bottom:10px;}
        .tank-badge{font-size:10px;font-weight:700;padding:5px 8px;border-radius:8px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .tank-badge.kat{background:var(--purple-light);color:var(--purple);border:1px solid rgba(124,58,237,.18);}
        .tank-badge.kelas{background:var(--success-lt);color:#34D399;border:1px solid rgba(16,185,129,.18);}
        .tank-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
        .tank-btn{padding:8px;border-radius:8px;border:none;font-size:10px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;font-family:inherit;transition:all .15s;color:white;}
        .tank-btn.acc{background:rgba(16,185,129,.20);color:#34D399;border:1px solid rgba(16,185,129,.25);}
        .tank-btn.acc:hover{background:var(--success);color:white;border-color:var(--success);}
        .tank-btn.rej{background:var(--danger-lt);color:#fca5a5;border:1px solid rgba(239,68,68,.25);}
        .tank-btn.rej:hover{background:var(--danger);color:white;border-color:var(--danger);}

        /* ═══ Empty State ═══ */
        .empty-state{text-align:center;padding:48px 20px;color:var(--text-muted);}
        .empty-state i{font-size:48px;color:var(--text-faint);margin-bottom:12px;display:block;}
        .empty-state p{font-size:13px;font-weight:600;}
        .empty-state .sub{font-size:11px;color:var(--text-faint);margin-top:4px;}

        /* ═══ History Section ═══ */
        .hist-head{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;background:rgba(255,255,255,.02);border-bottom:1px solid var(--bd-1);}
        .hist-head h2{font-size:13px;font-weight:800;color:var(--text-hi);display:flex;align-items:center;gap:8px;}
        .hist-head h2 i{color:var(--purple);font-size:13px;}
        .hist-tabs{display:flex;gap:4px;background:var(--glass-2);padding:3px;border-radius:10px;border:1px solid var(--bd-1);}
        .hist-tab{padding:6px 14px;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer;transition:all .2s;border:none;font-family:inherit;background:transparent;color:var(--text-muted);}
        .hist-tab:hover{background:var(--glass-3);color:var(--text-hi);}
        /* ✅ MENJADI INI */
        .hist-tab.active-app{background:rgba(52,211,153,.22);color:#6EE7B7;font-weight:800;}
        .hist-tab.active-rej{background:rgba(239,68,68,.18);color:#FCA5A5;font-weight:800;}
        .hist-body{padding:16px 20px;}
        .hist-group{margin-bottom:16px;border-radius:14px;overflow:hidden;border:1px solid;}
        /* ✅ MENJADI INI */
        .hist-group.app{border-color:rgba(52,211,153,.40);background:rgba(52,211,153,.07);}
        .hist-group.rej{border-color:rgba(252,165,165,.40);background:rgba(239,68,68,.08);}
        .hist-group-head{padding:10px 14px;display:flex;align-items:center;gap:10px;}
        .hist-group-icon{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;flex-shrink:0;}
        /* ✅ MENJADI INI */
        .hist-group.app .hist-group-icon{background:rgba(52,211,153,.25);color:#6EE7B7;}
        .hist-group.rej .hist-group-icon{background:rgba(239,68,68,.25);color:#FCA5A5;}
        .hist-group-icon i{font-size:14px;}
        .hist-group-icon i{font-size:12px;}
        /* ✅ MENJADI INI */
        .hist-group-head h3{font-size:14px;font-weight:800;color:var(--text-hi);}
        .hist-group-head p{font-size:11px;font-weight:700;color:var(--text-muted);}
        .hist-tanks{padding:10px 14px;display:grid;grid-template-columns:repeat(5,1fr);gap:8px;}
        .hist-tank{padding:10px;border-radius:10px;border:1px solid;transition:all .2s;}
        /* ✅ MENJADI INI */
        .hist-group.app .hist-tank{border-color:rgba(52,211,153,.25);background:rgba(52,211,153,.04);}
        .hist-group.app .hist-tank:hover{border-color:rgba(52,211,153,.50);background:rgba(52,211,153,.08);}
        .hist-group.rej .hist-tank{border-color:rgba(252,165,165,.25);background:rgba(239,68,68,.04);}
        .hist-group.rej .hist-tank:hover{border-color:rgba(252,165,165,.50);background:rgba(239,68,68,.08);}
        /* ✅ MENJADI INI */
        .hist-tank-num{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;font-weight:800;font-size:15px;color:white;background:linear-gradient(135deg,#7c3aed,var(--purple));margin-bottom:10px;box-shadow:0 4px 12px -3px rgba(124,58,237,.35);}
        .hist-tank-kat{font-size:11px;font-weight:800;padding:5px 8px;border-radius:7px;text-align:center;background:rgba(124,58,237,.20);color:#F8FAFC;margin-bottom:6px;border:1px solid rgba(124,58,237,.25);letter-spacing:.02em;}
        .hist-tank-kelas{font-size:11px;font-weight:800;padding:5px 8px;border-radius:7px;text-align:center;background:rgba(52,211,153,.15);color:#F8FAFC;margin-bottom:8px;border:1px solid rgba(52,211,153,.25);letter-spacing:.02em;}
        .hist-tank-note{font-size:10px;color:#FCA5A5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.4;}
        .hist-tank-note i{margin-right:3px;}
        .hist-tank-time{font-size:10px;color:#F8FAFC;text-align:center;margin-top:8px;font-weight:600;}

        /* ═══ Modals ═══ */
        .modal-overlay{position:fixed;inset:0;background:rgba(2,6,14,.88);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .35s;padding:16px;}
        .modal-overlay.show{opacity:1;pointer-events:all;}
        .modal-card{background:linear-gradient(180deg,var(--ocean-800) 0%,var(--ocean-900) 100%);border:1px solid var(--bd-2);border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.06);transform:translateY(30px) scale(.95);opacity:0;transition:all .4s cubic-bezier(.16,1,.3,1);overflow:hidden;}
        .modal-overlay.show .modal-card{transform:translateY(0) scale(1);opacity:1;}

        /* Warning Modal */
        .warning-card{width:90%;max-width:440px;}
        .warning-header{background:linear-gradient(135deg,rgba(245,158,11,.12),rgba(245,158,11,.05));padding:28px 28px 20px;text-align:center;border-bottom:1px solid rgba(245,158,11,.15);}
        .warning-icon{width:60px;height:60px;border-radius:50%;background:var(--warning-lt);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 8px 20px rgba(245,158,11,.2);}
        .warning-icon i{font-size:26px;color:var(--gold-400);}
        .warning-title{font-family:'Fraunces',serif;font-size:20px;font-weight:500;color:var(--gold-300);letter-spacing:-.02em;}
        .warning-body{padding:20px 28px 24px;}
        .error-list{list-style:none;display:flex;flex-direction:column;gap:8px;}
        .error-item{display:flex;align-items:flex-start;gap:10px;padding:10px 12px;background:var(--danger-lt);border:1px solid rgba(239,68,68,.20);border-radius:10px;}
        .error-item i{color:#fca5a5;font-size:14px;margin-top:2px;flex-shrink:0;}
        .error-item .err-desc{font-size:12px;color:var(--text-hi);font-weight:600;line-height:1.4;}
        .warning-footer{padding:0 28px 28px;}
        .btn-close-warning{width:100%;padding:13px;border:none;border-radius:14px;background:linear-gradient(135deg,var(--gold-600),var(--gold-500));color:var(--ocean-900);font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;transition:all .2s;box-shadow:0 4px 14px rgba(245,158,11,.3);}
        .btn-close-warning:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(245,158,11,.4);}

        /* Success/Confirm Popup */
        .popup-card{padding:44px 36px 32px;text-align:center;max-width:380px;width:100%;}
        .popup-icon{width:76px;height:76px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 22px;}
        .popup-icon i{font-size:34px;color:white;animation:iconPop .5s .25s cubic-bezier(.16,1,.3,1) both;}
        @keyframes iconPop{0%{transform:scale(0) rotate(-30deg);opacity:0}100%{transform:scale(1) rotate(0);opacity:1}}
        .popup-icon.success{background:linear-gradient(135deg,var(--purple),#7c3aed);box-shadow:0 8px 24px rgba(124,58,237,.4);}
        .popup-icon.danger{background:linear-gradient(135deg,var(--danger),#dc2626);box-shadow:0 8px 24px rgba(239,68,68,.4);}
        .popup-title{font-family:'Fraunces',serif;font-weight:500;font-size:20px;color:var(--text-hi);margin-bottom:8px;letter-spacing:-.02em;}
        .popup-desc{font-size:13px;color:var(--text-muted);line-height:1.6;margin-bottom:24px;}
        .popup-desc b,.popup-desc strong{color:var(--text-hi);}
        .popup-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border:none;border-radius:14px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;transition:all .25s;color:white;}
        .popup-btn.success{background:linear-gradient(135deg,var(--purple),#7c3aed);box-shadow:0 4px 14px -4px rgba(124,58,237,.5),inset 0 1px 0 rgba(255,255,255,.2);}
        .popup-btn.success:hover{transform:translateY(-1px);}
        .popup-btn.cancel{background:var(--glass-2);color:var(--text-muted);border:1px solid var(--bd-2);box-shadow:none;}
        .popup-btn.cancel:hover{background:var(--glass-3);color:var(--text-hi);}
        .popup-btn.danger{background:linear-gradient(135deg,var(--danger),#dc2626);box-shadow:0 4px 14px -4px rgba(239,68,68,.5),inset 0 1px 0 rgba(255,255,255,.15);}
        .popup-btn.danger:hover{transform:translateY(-1px);}
        .popup-btn-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
        .confirm-input{width:100%;padding:12px 16px;border:1px solid var(--bd-2);border-radius:12px;font-family:inherit;font-size:13px;outline:none;background:var(--glass-2);color:var(--text-hi);transition:all .2s;color-scheme:dark;margin-bottom:20px;}
        .confirm-input::placeholder{color:var(--text-faint);}
        .confirm-input:focus{border-color:var(--danger);box-shadow:0 0 0 3px rgba(239,68,68,.1);}

        /* ═══ Animations ═══ */
        @keyframes fadeIn{from{opacity:0;transform:scale(.97);}to{opacity:1;transform:scale(1);}}
        .fade-in{animation:fadeIn .2s ease-out forwards;}

        /* ═══ Scrollbar ═══ */
        ::-webkit-scrollbar{width:6px;height:6px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--glass-strong);border-radius:10px;}
        ::-webkit-scrollbar-thumb:hover{background:var(--bd-3);}

        /* ═══ Responsive ═══ */
        @media(max-width:1200px){.nom-group-body,.hist-tanks{grid-template-columns:repeat(4,1fr);}}
        @media(max-width:1024px){.nom-group-body,.hist-tanks{grid-template-columns:repeat(3,1fr);}}
        @media(max-width:768px){
            .top-nav{flex-direction:column;align-items:stretch;gap:10px;padding:12px 16px;}
            .brand{display:flex;align-items:center;justify-content:space-between;}
            .brand span{display:none;}
            .nav-right{display:flex;justify-content:flex-end;align-items:center;flex-wrap:wrap;gap:8px;}
            .main-wrap{padding:14px 14px 32px;}
            .stats-grid{grid-template-columns:repeat(3,1fr);gap:10px;}
            .stat-card{padding:16px 12px;}
            .stat-card .stat-num{font-size:26px;}
            .nom-group-body,.hist-tanks{grid-template-columns:repeat(2,1fr);}
            .nom-group-body{padding:12px 14px;}
            .hist-body{padding:12px 14px;}
            .bubbles{display:none;}
        }
        @media(max-width:540px){
            .stats-grid{grid-template-columns:1fr 1fr;gap:8px;}
            .stat-card{padding:14px 10px;}
            .stat-card .stat-num{font-size:22px;}
            .stat-card .stat-lbl{font-size:9px;}
            .nom-group-body,.hist-tanks{grid-template-columns:repeat(2,1fr);gap:8px;}
            .tank-card{padding:12px;}
            .tank-num{width:38px;height:38px;font-size:16px;border-radius:10px;margin-bottom:10px;}
            .tank-badges{gap:4px;margin-bottom:8px;}
            .tank-badge{font-size:9px;padding:4px 6px;}
            .tank-actions{gap:6px;}
            .tank-btn{padding:7px;font-size:9px;}
            .nom-group-head{padding:12px 14px;}
            .hist-group-head{padding:8px 12px;}
            .hist-tanks{padding:8px 12px;}
            .hist-tank{padding:10px;}
            /* ✅ MENJADI INI */
            .hist-tank-num{width:36px;height:36px;font-size:13px;border-radius:9px;}
            .btn-acc-all{padding:7px 12px;font-size:10px;}
            .btn-back,.btn-logout{padding:7px 10px;font-size:11px;}
            .nav-right .info h4{font-size:12px;}
            .nav-right .info span{font-size:8px;}
        }
        @media(max-width:380px){
            .nom-group-body,.hist-tanks{grid-template-columns:1fr 1fr;gap:6px;}
            .nav-right{gap:6px;}
            .nav-right .info{display:none;}
        }
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.1s!important;}.bubbles{display:none;}}
    </style>
</head>
<body class="min-h-screen">

<!-- Ocean Background -->
<div class="ocean-bg"></div>
<div class="bubbles" aria-hidden="true"><span></span><span></span><span></span></div>

<!-- ═══ TOP NAV ═══ -->
<nav class="top-nav">
    <div class="brand">
        <h1><i class="fas fa-gavel"></i> Pilih Nominasi LCI</h1>
        <span>Grand Juri — Review Nominasi</span>
    </div>
    <div class="nav-right">
        <a href="{{ route('grand-juri.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> <span class="hide-sm">Kembali</span></a>
        <div class="info">
            <h4>{{ Auth::user()->name }}</h4>
            <span>GRAND JURI</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> <span class="hide-sm">Keluar</span></button>
        </form>
    </div>
</nav>

<!-- ═══ MAIN ═══ -->
<main class="main-wrap">

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-num" id="stat-juri">0</div>
            <div class="stat-lbl">Juri Menunggu</div>
        </div>
        <div class="stat-card gold">
            <div class="stat-num" id="stat-tank">0</div>
            <div class="stat-lbl">Tank Pending</div>
        </div>
        <div class="stat-card green clickable" onclick="loadNominasi()">
            <div class="stat-num" style="position:relative;display:inline-flex;align-items:center;justify-content:center;min-width:50px;">
                <i id="gj-refresh-icon" class="fas fa-sync-alt" style="font-size:22px;color:#34D399;"></i>
            </div>
            <div class="stat-lbl">Refresh Data</div>
        </div>
    </div>

    {{-- Daftar Nominasi --}}
    <div id="nom-list" class="space-y-5"></div>
    <div id="nom-empty" class="g-card" style="display:none;">
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Tidak ada nominasi yang menunggu review.</p>
            <p class="sub">Semua sudah ditinjau atau belum ada pengiriman.</p>
        </div>
    </div>

    {{-- ★ MODUL LATE IKAN (Peserta Terlambat Daftar) --}}
    <div id="late-section" class="g-card" style="margin-top:24px;display:none;">
        <div class="g-card-head">
            <div class="g-card-head-left">
                <div class="g-card-head-icon gold"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>Ikan Terlambat Daftar</h3>
                    <p class="sub" id="late-sub">Menunggu keputusan Grand Juri</p>
                </div>
            </div>
            <span id="late-count-badge" style="font-size:11px;font-weight:800;padding:5px 12px;border-radius:999px;background:var(--warning-lt);color:var(--gold-300);border:1px solid rgba(245,158,11,.25);">0 tank</span>
        </div>
        <div id="late-body" class="g-card-body">
            <div class="nom-group-body" id="late-grid" style="padding:0;"></div>
        </div>
    </div>
    <div id="late-disabled-info" class="g-card" style="margin-top:24px;display:none;">
        <div class="empty-state" style="padding:24px 20px;">
            <i class="fas fa-lock" style="color:var(--text-faint);"></i>
            <p style="margin-top:6px;" id="late-disabled-text">Modul ikan terlambat akan aktif setelah semua juri selesai nominasi.</p>
        </div>
    </div>

    {{-- Riwayat Review --}}
    <div class="g-card" style="margin-top:24px;">
        <div class="hist-head">
            <h2><i class="fas fa-clock-rotate-left"></i> Riwayat Review Nominasi</h2>
            <div class="hist-tabs">
                <button onclick="switchHistTab('approved')" id="hist-tab-approved" class="hist-tab active-app">
                    <i class="fas fa-check-circle" style="margin-right:3px;"></i>Diterima (<span id="hist-cnt-app">0</span>)
                </button>
                <button onclick="switchHistTab('rejected')" id="hist-tab-rejected" class="hist-tab">
                    <i class="fas fa-times-circle" style="margin-right:3px;"></i>Ditolak (<span id="hist-cnt-rej">0</span>)
                </button>
            </div>
        </div>
        <div id="hist-content" class="hist-body">
            <div class="empty-state" style="padding:28px 20px;">
                <i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--text-faint);"></i>
                <p style="margin-top:8px;">Memuat data...</p>
            </div>
        </div>
    </div>

</main>

<!-- ═══ Warning Modal ═══ -->
<div class="modal-overlay" id="warningModal">
    <div class="modal-card warning-card">
        <div class="warning-header">
            <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2 class="warning-title">Perhatian</h2>
        </div>
        <div class="warning-body">
            <ul class="error-list" id="errorListContainer"></ul>
        </div>
        <div class="warning-footer">
            <button class="btn-close-warning" onclick="document.getElementById('warningModal').classList.remove('show')">OK, Saya Mengerti</button>
        </div>
    </div>
</div>

<!-- ═══ Success Popup ═══ -->
<div class="modal-overlay" id="successPopup">
    <div class="modal-card popup-card">
        <div class="popup-icon success"><i class="fas fa-check"></i></div>
        <h2 class="popup-title" id="popupTitle">Berhasil!</h2>
        <p class="popup-desc" id="popupDesc">Data telah tersimpan.</p>
        <button class="popup-btn success" onclick="document.getElementById('successPopup').classList.remove('show')">
            <i class="fas fa-circle-check"></i> OK, Tutup
        </button>
    </div>
</div>

<!-- ═══ Confirm Modal (Reject) ═══ -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-card popup-card" style="max-width:420px;">
        <div class="popup-icon danger"><i class="fas fa-times"></i></div>
        <h2 class="popup-title">Tolak Nominasi?</h2>
        <p class="popup-desc" id="confirmMessage">Anda yakin ingin menolak tank ini?</p>
        <input type="text" id="rejectReason" placeholder="Alasan penolakan (opsional)" class="confirm-input">
        <div class="popup-btn-row">
            <button class="popup-btn cancel" onclick="document.getElementById('confirmModal').classList.remove('show'); pendingRejectId=null;">
                <i class="fas fa-xmark"></i> Batal
            </button>
            <button class="popup-btn danger" id="confirmOkBtn" onclick="executeReject()">
                <i class="fas fa-ban"></i> Ya, Tolak
            </button>
        </div>
    </div>
</div>

<style>@media(max-width:540px){.hide-sm{display:none;}}</style>

<script>
var NO_KELAS_KAT = ['Bonsai', 'Jumbo'];
function isNoKelasGJ(kat) { return NO_KELAS_KAT.indexOf(kat) !== -1; }

var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var pendingRejectId = null;

async function apiFetch(url, opts) {
    opts = opts || {};
    var defaults = { headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
    try {
        var res = await fetch(url, Object.assign({}, defaults, opts, { headers: Object.assign({}, defaults.headers, opts.headers || {}) }));
        if (!res.ok) return { error: true, message: 'Server error (HTTP ' + res.status + ')' };
        var text = await res.text();
        if (!text || text.trim() === '') return { error: true, message: 'Server mengembalikan respons kosong.' };
        try {
            var json = JSON.parse(text);
            if (json.error) return json;
            return json;
        } catch(e) { return { error: true, message: 'Respons tidak valid dari server.' }; }
    } catch(e) { return { error: true, message: 'Gagal terhubung ke server.' }; }
}

function showWarningModal(errors) {
    var c = document.getElementById('errorListContainer');
    c.innerHTML = '';
    (errors || []).forEach(function(err) {
        var li = document.createElement('li');
        li.className = 'error-item';
        li.innerHTML = '<i class="fas fa-circle-xmark"></i><div><span class="err-desc">' + (err.msg || err) + '</span></div>';
        c.appendChild(li);
    });
    document.getElementById('warningModal').classList.add('show');
}

function showSuccessPopup(title, desc) {
    document.getElementById('popupTitle').innerText = title;
    document.getElementById('popupDesc').innerHTML = desc;
    document.getElementById('successPopup').classList.add('show');
}

function showRejectConfirm(nominasiId, nomorTank) {
    pendingRejectId = nominasiId;
    document.getElementById('confirmMessage').textContent = 'Anda yakin ingin menolak Tank ' + nomorTank + '? Juri akan diminta mengirim ulang nominasi.';
    document.getElementById('rejectReason').value = '';
    document.getElementById('confirmModal').classList.add('show');
}

function removeTankCard(nominasiId) {
    var card = document.getElementById('tank-card-' + nominasiId);
    if (!card) return;
    card.style.transition = 'all .3s ease';
    card.style.opacity = '0';
    card.style.transform = 'scale(.92)';
    card.style.pointerEvents = 'none';
    setTimeout(function() {
        card.remove();
        var group = card.closest('.nom-group');
        if (group && group.querySelectorAll('[id^="tank-card-"]').length === 0) {
            group.style.transition = 'all .3s ease';
            group.style.opacity = '0';
            group.style.transform = 'scale(.97)';
            setTimeout(function() {
                group.remove();
                if (document.getElementById('nom-list').children.length === 0) {
                    document.getElementById('nom-empty').style.display = '';
                }
            }, 300);
        }
    }, 300);
}

function decrementStatTank(count) {
    var el = document.getElementById('stat-tank');
    el.textContent = Math.max(0, (parseInt(el.textContent) || 0) - count);
}

async function executeReject() {
    if (!pendingRejectId) return;
    var catatan = document.getElementById('rejectReason').value.trim();
    document.getElementById('confirmModal').classList.remove('show');
    var targetId = pendingRejectId;
    pendingRejectId = null;
    try {
        var res = await apiFetch('/api/grand-juri/nominasi-review', {
            method: 'POST',
            body: JSON.stringify({ nominasi_id: targetId, action: 'reject', catatan: catatan })
        });
        if (res.success) {
            removeTankCard(targetId);
            decrementStatTank(1);
            showSuccessPopup('Ditolak', res.message);
            loadNominasi(true);
            loadHistory();
        } else { showWarningModal([{ msg: res.message }]); }
    } catch(e) { showWarningModal([{ msg: 'Gagal memproses. Periksa koneksi.' }]); }
}

async function approveNominasi(btn, nominasiId) {
    var card = btn.closest('[id^="tank-card-"]');
    if (card) card.style.pointerEvents = 'none';
    btn.disabled = true;
    btn.innerHTML = '<div style="width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;"></div>';
    try {
        var res = await apiFetch('/api/grand-juri/nominasi-review', {
            method: 'POST',
            body: JSON.stringify({ nominasi_id: nominasiId, action: 'approve' })
        });
        if (res.success) {
            removeTankCard(nominasiId);
            decrementStatTank(1);
            showSuccessPopup('Disetujui', res.message);
            loadNominasi(true);
            loadHistory();
        } else {
            showWarningModal([{ msg: res.message }]);
            btn.disabled = false;
            if (card) card.style.pointerEvents = '';
            btn.innerHTML = '<i class="fas fa-check"></i> ACC';
        }
    } catch(e) {
        showWarningModal([{ msg: 'Gagal memproses. Periksa koneksi.' }]);
        btn.disabled = false;
        if (card) card.style.pointerEvents = '';
        btn.innerHTML = '<i class="fas fa-check"></i> ACC';
    }
}

async function approveAllInGroup(btn, ids) {
    btn.disabled = true;
    btn.innerHTML = '<div style="width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;display:inline-block;margin-right:6px;vertical-align:middle;"></div>Memproses...';
    var success = 0, fail = 0;
    for (var i = 0; i < ids.length; i++) {
        if (!document.getElementById('tank-card-' + ids[i])) continue;
        removeTankCard(ids[i]);
        try {
            var res = await apiFetch('/api/grand-juri/nominasi-review', {
                method: 'POST',
                body: JSON.stringify({ nominasi_id: ids[i], action: 'approve' })
            });
            if (res.success) success++; else fail++;
        } catch(e) { fail++; }
    }
    decrementStatTank(success);
    if (fail === 0) {
        showSuccessPopup('Berhasil', 'Semua ' + success + ' nominasi disetujui.');
    } else {
        showWarningModal([{ msg: success + ' berhasil, ' + fail + ' gagal.' }]);
    }
    loadNominasi(true);
    loadHistory();
}

/* ═══ Spin keyframe (injected) ═══ */
(function(){var s=document.createElement('style');s.textContent='@keyframes spin{to{transform:rotate(360deg)}}';document.head.appendChild(s);})();

async function loadNominasi(silent) {
    var icon = document.getElementById('gj-refresh-icon');
    if (icon && !silent) { icon.style.transition = 'transform .6s'; icon.style.transform = 'rotate(360deg)'; setTimeout(function(){ icon.style.transform = ''; }, 650); }
    try {
        var res = await apiFetch('/api/grand-juri/nominasi');
        if (!res || res.error) {
            if (!silent) showWarningModal([{ msg: (res && res.message) ? res.message : 'Gagal memuat data nominasi.' }]);
            return;
        }
        document.getElementById('stat-juri').textContent = res.total_juri || 0;
        document.getElementById('stat-tank').textContent = res.total_pending || 0;
        var list = document.getElementById('nom-list');
        var empty = document.getElementById('nom-empty');
        if (!res.grouped || !Array.isArray(res.grouped) || res.grouped.length === 0) {
            list.innerHTML = '';
            empty.style.display = '';
            return;
        }
        empty.style.display = 'none';
        var html = '';
        for (var g = 0; g < res.grouped.length; g++) {
            try {
                var group = res.grouped[g];
                if (!group || !group.tanks || !Array.isArray(group.tanks)) continue;
                var tankIds = [];
                for (var ti = 0; ti < group.tanks.length; ti++) {
                    if (group.tanks[ti] && group.tanks[ti].nominasi_id) tankIds.push(group.tanks[ti].nominasi_id);
                }
                html += '<div class="g-card nom-group fade-in">';
                html += '<div class="nom-group-head"><div class="nom-group-info"><div class="nom-group-avatar"><i class="fas fa-user"></i></div><div><h3>' + escH(group.juri_name || 'Unknown') + '</h3><p>' + group.tanks.length + ' tank dinominasikan</p></div></div>';
                html += '<button onclick="approveAllInGroup(this, ' + JSON.stringify(tankIds) + ')" class="btn-acc-all"><i class="fas fa-check-double"></i> ACC Semua</button>';
                html += '</div><div class="nom-group-body">';
                for (var t = 0; t < group.tanks.length; t++) {
                    try {
                        var tank = group.tanks[t];
                        if (!tank || !tank.nominasi_id) continue;
                        var kelasHtml = '';
                        if (tank.kelas && !isNoKelasGJ(tank.kategori)) {
                            kelasHtml = '<div class="tank-badge kelas">Kelas ' + escH(tank.kelas) + '</div>';
                        }
                        html += '<div id="tank-card-' + tank.nominasi_id + '" class="tank-card">';
                        html += '<div class="tank-num">' + (tank.nomor_tank || '?') + '</div>';
                        html += '<div class="tank-badges"><div class="tank-badge kat">' + escH(tank.kategori || '-') + '</div>' + kelasHtml + '</div>';
                        html += '<div class="tank-actions">';
                        html += '<button onclick="approveNominasi(this, ' + tank.nominasi_id + ')" class="tank-btn acc"><i class="fas fa-check"></i> ACC</button>';
                        html += '<button onclick="showRejectConfirm(' + tank.nominasi_id + ', ' + (tank.nomor_tank || 0) + ')" class="tank-btn rej"><i class="fas fa-times"></i> Tolak</button>';
                        html += '</div></div>';
                    } catch(e2) { /* skip tank */ }
                }
                html += '</div></div>';
            } catch(e1) { /* skip group */ }
        }
        list.innerHTML = html;
        loadLateIkan(); // ★ refresh late-ikan module setiap refresh nominasi
    } catch(e) {
        if (!silent) showWarningModal([{ msg: 'Gagal memuat data nominasi.' }]);
    }
}

function escH(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ═══ History ═══ */
var histData = { approved: [], rejected: [] };
var histActiveTab = 'approved';

function switchHistTab(tab) {
    histActiveTab = tab;
    document.querySelectorAll('.hist-tab').forEach(function(b) { b.className = 'hist-tab'; });
    var btn = document.getElementById('hist-tab-' + tab);
    btn.className = 'hist-tab ' + (tab === 'approved' ? 'active-app' : 'active-rej');
    renderHistory();
}

function renderHistory() {
    var groups = histData[histActiveTab];
    var container = document.getElementById('hist-content');
    if (!groups || groups.length === 0) {
        var label = histActiveTab === 'approved' ? 'yang diterima' : 'yang ditolak';
        container.innerHTML = '<div class="empty-state" style="padding:28px 20px;"><i class="fas fa-inbox" style="font-size:32px;"></i><p>Tidak ada data nominasi ' + label + '.</p></div>';
        return;
    }
    var isApp = histActiveTab === 'approved';
    var cls = isApp ? 'app' : 'rej';
    var html = '';
    groups.forEach(function(group) {
        html += '<div class="hist-group ' + cls + ' fade-in">';
        html += '<div class="hist-group-head"><div class="hist-group-icon"><i class="fas ' + (isApp ? 'fa-check' : 'fa-times') + '"></i></div><div><h3>' + escH(group.juri_name) + '</h3><p>' + group.tanks.length + ' tank</p></div></div>';
        html += '<div class="hist-tanks">';
        group.tanks.forEach(function(tank) {
            html += '<div class="hist-tank">';
            html += '<div class="hist-tank-num">' + tank.nomor_tank + '</div>';
            html += '<div class="hist-tank-kat">' + escH(tank.kategori) + '</div>';
            if (tank.kelas && !isNoKelasGJ(tank.kategori)) html += '<div class="hist-tank-kelas">Kelas ' + escH(tank.kelas) + '</div>';
            if (!isApp && tank.catatan) html += '<div class="hist-tank-note" title="' + escH(tank.catatan) + '"><i class="fas fa-comment-dots"></i>' + escH(tank.catatan) + '</div>';
            html += '<div class="hist-tank-time">' + (tank.reviewed_at || '') + '</div>';
            html += '</div>';
        });
        html += '</div></div>';
    });
    container.innerHTML = html;
}

async function loadHistory() {
    try {
        var res = await apiFetch('/api/grand-juri/nominasi-history');
        histData.approved = res.approved || [];
        histData.rejected = res.rejected || [];
        document.getElementById('hist-cnt-app').textContent = res.total_approved || 0;
        document.getElementById('hist-cnt-rej').textContent = res.total_rejected || 0;
        renderHistory();
    } catch(e) {
        document.getElementById('hist-content').innerHTML = '<div class="empty-state" style="padding:20px;"><p style="color:#fca5a5;">Gagal memuat riwayat.</p></div>';
    }
}

/* ═══ LATE IKAN MODULE ═══ */
async function loadLateIkan() {
    try {
        var res = await apiFetch('/api/grand-juri/late-ikan');
        if (!res || res.error) return;

        var section = document.getElementById('late-section');
        var disabled = document.getElementById('late-disabled-info');

        if (!res.enabled) {
            section.style.display = 'none';
            // Tampilkan info saja kalau ada peserta yang belum selesai
            if (res.juri_done < res.total_juri && res.total_juri > 0) {
                document.getElementById('late-disabled-text').textContent =
                    'Modul ikan terlambat aktif setelah semua juri selesai nominasi (' +
                    res.juri_done + '/' + res.total_juri + ' juri sudah selesai).';
                disabled.style.display = '';
            } else {
                disabled.style.display = 'none';
            }
            return;
        }

        disabled.style.display = 'none';

        if (!res.ikans || res.ikans.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = '';
        document.getElementById('late-count-badge').textContent = res.ikans.length + ' tank';
        document.getElementById('late-sub').textContent = 'Semua juri sudah selesai — review ikan yang baru saja didaftarkan';

        var html = '';
        res.ikans.forEach(function(ikan) {
            var kelasHtml = '';
            if (ikan.kelas && !isNoKelasGJ(ikan.kategori)) {
                kelasHtml = '<div class="tank-badge kelas">Kelas ' + escH(ikan.kelas) + '</div>';
            }
            html += '<div id="late-card-' + ikan.ikan_id + '" class="tank-card">';
            html += '<div class="tank-num" style="background:linear-gradient(135deg,var(--gold-600),var(--gold-500));box-shadow:0 4px 12px -3px rgba(245,158,11,.4);">' + (ikan.nomor_tank || '?') + '</div>';
            html += '<div class="tank-badges"><div class="tank-badge kat">' + escH(ikan.kategori || '-') + '</div>' + kelasHtml + '</div>';
            html += '<div class="tank-actions">';
            html += '<button onclick="approveLateIkan(this, ' + ikan.ikan_id + ')" class="tank-btn acc"><i class="fas fa-check"></i> ACC</button>';
            html += '<button onclick="rejectLateIkan(' + ikan.ikan_id + ', ' + (ikan.nomor_tank || 0) + ')" class="tank-btn rej"><i class="fas fa-times"></i> Tolak</button>';
            html += '</div></div>';
        });
        document.getElementById('late-grid').innerHTML = html;
    } catch(e) { /* silent */ }
}

async function approveLateIkan(btn, ikanId) {
    btn.disabled = true;
    btn.innerHTML = '<div style="width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;"></div>';
    try {
        var res = await apiFetch('/api/grand-juri/late-ikan-review', {
            method: 'POST',
            body: JSON.stringify({ ikan_id: ikanId, action: 'approve' })
        });
        if (res.success) {
            var card = document.getElementById('late-card-' + ikanId);
            if (card) {
                card.style.transition = 'all .3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(.92)';
                setTimeout(function(){ card.remove(); loadLateIkan(); }, 300);
            }
            showSuccessPopup('Disetujui', res.message);
            loadHistory();
        } else {
            showWarningModal([{ msg: res.message }]);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> ACC';
        }
    } catch(e) {
        showWarningModal([{ msg: 'Gagal memproses. Periksa koneksi.' }]);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> ACC';
    }
}

var pendingLateRejectId = null;
function rejectLateIkan(ikanId, nomorTank) {
    pendingLateRejectId = ikanId;
    document.getElementById('confirmMessage').textContent = 'Tolak ikan terlambat Tank ' + nomorTank + '?';
    document.getElementById('rejectReason').value = '';
    // Override tombol konfirmasi
    var okBtn = document.getElementById('confirmOkBtn');
    okBtn.setAttribute('onclick', 'executeLateReject()');
    document.getElementById('confirmModal').classList.add('show');
}

async function executeLateReject() {
    if (!pendingLateRejectId) return;
    var catatan = document.getElementById('rejectReason').value.trim();
    document.getElementById('confirmModal').classList.remove('show');
    var targetId = pendingLateRejectId;
    pendingLateRejectId = null;
    // Restore tombol untuk regular reject lagi
    document.getElementById('confirmOkBtn').setAttribute('onclick', 'executeReject()');

    try {
        var res = await apiFetch('/api/grand-juri/late-ikan-review', {
            method: 'POST',
            body: JSON.stringify({ ikan_id: targetId, action: 'reject', catatan: catatan })
        });
        if (res.success) {
            var card = document.getElementById('late-card-' + targetId);
            if (card) {
                card.style.transition = 'all .3s ease';
                card.style.opacity = '0';
                setTimeout(function(){ card.remove(); loadLateIkan(); }, 300);
            }
            showSuccessPopup('Ditolak', res.message);
            loadHistory();
        } else { showWarningModal([{ msg: res.message }]); }
    } catch(e) { showWarningModal([{ msg: 'Gagal memproses.' }]); }
}

document.addEventListener('DOMContentLoaded', function() {
    loadNominasi();
    loadHistory();
    loadLateIkan();
});

window.addEventListener('unhandledrejection', function(e) { e.preventDefault(); });
</script>

</body>
</html>