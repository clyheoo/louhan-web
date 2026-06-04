<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <meta name="theme-color" content="#0B1220">
    <title>Penjurian LCI – Dashboard Juri</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'sans-serif'],
                serif: ['Fraunces', 'serif'],
                mono: ['JetBrains Mono', 'monospace'],
            }
        }
    }
    </script>

    <style>
    /* ============================================================
       LCI SUITE — JURI DARK OCEAN THEME
       ============================================================ */
    :root {
        --ocean-950: #04070F;
        --ocean-900: #0B1220;
        --ocean-850: #0E1729;
        --ocean-800: #111E36;
        --ocean-700: #182947;
        --ocean-600: #1F3358;
        --cyan-500: #06B6D4;
        --cyan-400: #22D3EE;
        --cyan-300: #67E8F9;
        --royal-700: #1D4ED8;
        --royal-600: #2563EB;
        --royal-500: #3B82F6;
        --gold-500: #F59E0B;
        --gold-400: #FBBF24;
        --gold-300: #FCD34D;
        --success: #10B981;
        --danger: #EF4444;
        --glass-2: rgba(255,255,255,0.05);
        --glass-3: rgba(255,255,255,0.08);
        --glass-strong: rgba(255,255,255,0.12);
        --bd-1: rgba(255,255,255,0.06);
        --bd-2: rgba(255,255,255,0.10);
        --bd-3: rgba(255,255,255,0.16);
        --bd-cyan: rgba(34,211,238,0.25);
        --text-hi: #F8FAFC;
        --text: #E2E8F0;
        --text-mid: #94A3B8;
        --text-low: #64748B;
        --text-faint: #475569;
        --shadow-card: 0 1px 0 rgba(255,255,255,0.04) inset, 0 30px 60px -30px rgba(0,0,0,0.6), 0 18px 36px -24px rgba(6,182,212,0.10);
    }

    *, *::before, *::after { box-sizing: border-box; }
    body { background: var(--ocean-900) !important; color: var(--text) !important; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    /* ── ATMOSPHERIC BACKGROUND ── */
    .ocean-bg {
        position: fixed; inset: 0; z-index: 0; pointer-events: none;
        background:
            radial-gradient(ellipse 70% 50% at 50% 0%, rgba(37,99,235,0.14) 0%, transparent 55%),
            radial-gradient(ellipse 50% 50% at 100% 100%, rgba(6,182,212,0.08) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 0% 70%, rgba(29,78,216,0.08) 0%, transparent 60%),
            linear-gradient(180deg, var(--ocean-950) 0%, var(--ocean-900) 45%, var(--ocean-850) 100%);
    }
    .bubbles { position: fixed; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }
    .bubbles span {
        position: absolute; display: block; border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.55), rgba(34,211,238,0.20) 60%, transparent 70%);
        box-shadow: 0 0 8px rgba(34,211,238,0.20);
        bottom: -20px; will-change: transform, opacity;
        animation: bubbleRise linear infinite; opacity: 0;
    }
    .bubbles span:nth-child(1) { left:10%; width:8px; height:8px; animation-duration:22s; animation-delay:0s; }
    .bubbles span:nth-child(2) { left:25%; width:5px; height:5px; animation-duration:28s; animation-delay:6s; }
    .bubbles span:nth-child(3) { left:42%; width:10px; height:10px; animation-duration:24s; animation-delay:3s; }
    .bubbles span:nth-child(4) { left:60%; width:6px; height:6px; animation-duration:26s; animation-delay:9s; }
    .bubbles span:nth-child(5) { left:75%; width:9px; height:9px; animation-duration:21s; animation-delay:2s; }
    .bubbles span:nth-child(6) { left:90%; width:5px; height:5px; animation-duration:27s; animation-delay:11s; }
    @keyframes bubbleRise {
        0% { transform: translate3d(0,0,0) scale(0.9); opacity:0; }
        10% { opacity:0.55; } 90% { opacity:0.25; }
        100% { transform: translate3d(15px,-110vh,0) scale(0.85); opacity:0; }
    }

    /* ── APP SHELL ── */
    .app-shell { position: relative; z-index: 10; min-height: 100vh; display: flex; flex-direction: column; }

    /* ── TOP NAV ── */
    .top-nav {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 28px;
        background: rgba(11,18,32,0.72);
        backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--bd-1);
        position: sticky; top: 0; z-index: 100;
        animation: navIn 0.5s cubic-bezier(0.16,1,0.3,1) both;
        box-shadow: none;
    }
    @keyframes navIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
    .brand { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .brand-mark {
        width: 42px; height: 42px; border-radius: 13px;
        display: grid; place-items: center; flex-shrink: 0;
        background: radial-gradient(circle at 30% 30%, rgba(34,211,238,0.5), transparent 60%),
                    linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%);
        box-shadow: 0 6px 18px -6px rgba(6,182,212,0.55), inset 0 1px 0 rgba(255,255,255,0.25);
    }
    .brand-mark i { font-size: 18px; color: white; }
    .brand-text h1 {
        font-family: 'Fraunces', serif; font-weight: 600;
        font-size: 18px; letter-spacing: -0.02em;
        color: var(--text-hi); line-height: 1.05;
    }
    .brand-text h1 em { font-style: italic; font-weight: 400; color: var(--cyan-400); }
    .brand-text p { font-size: 10px; color: var(--text-mid); margin-top: 2px; letter-spacing: 0.06em; text-transform: uppercase; font-weight: 700; }
    .nav-center { display: flex; align-items: center; gap: 10px; }
    .nav-pill {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 13px; border: 1px solid var(--bd-2); border-radius: 999px;
        background: var(--glass-2); font-size: 11px; font-weight: 600;
        color: var(--text); letter-spacing: 0.02em;
    }
    .nav-pill .live-dot {
        width: 7px; height: 7px; border-radius: 50%; background: var(--success);
        box-shadow: 0 0 8px rgba(16,185,129,0.35);
        animation: livePulse 2.4s ease-in-out infinite;
    }
    @keyframes livePulse { 0%,100% { opacity:1; } 50% { opacity:0.55; } }
    .nav-user { display: flex; align-items: center; gap: 14px; }
    .user-card {
        display: flex; align-items: center; gap: 10px;
        padding: 5px 12px 5px 5px; border: 1px solid var(--bd-2);
        border-radius: 999px; background: var(--glass-2);
    }
    .avatar {
        width: 34px; height: 34px; border-radius: 50%;
        display: grid; place-items: center;
        background: linear-gradient(135deg, var(--gold-500), #B45309);
        color: white; font-weight: 800; font-size: 13px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.3), 0 4px 10px -2px rgba(245,158,11,0.4);
    }
    .user-info { text-align: left; line-height: 1.1; }
    .user-info h4 { font-size: 12.5px; font-weight: 700; color: var(--text-hi); }
    .user-info span {
        font-size: 9.5px; color: var(--gold-300); background: rgba(245,158,11,0.10);
        padding: 2px 7px; border-radius: 6px; font-weight: 700;
        border: 1px solid rgba(245,158,11,0.20); letter-spacing: 0.04em; text-transform: uppercase;
    }
    .btn-logout {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 13px; background: var(--glass-2); border: 1px solid var(--bd-2);
        color: var(--text-mid); border-radius: 12px;
        font-size: 11.5px; font-weight: 700; cursor: pointer;
        transition: all 0.2s; text-decoration: none; font-family: inherit;
    }
    .btn-logout:hover { background: rgba(239,68,68,0.12); color: #fca5a5; border-color: rgba(239,68,68,0.35); }

    /* ============================================================
       TAILWIND COLOR OVERRIDES — Dark Ocean Theme
       ============================================================ */

    /* ── BACKGROUNDS ── */
    .bg-white { background: var(--glass-2) !important; }
    .bg-slate-50 { background: rgba(255,255,255,0.06) !important; }
    .bg-slate-100 { background: rgba(255,255,255,0.05) !important; }
    .bg-slate-200 { background: rgba(255,255,255,0.10) !important; }
    .bg-slate-300 { background: rgba(255,255,255,0.20) !important; }

    .bg-blue-50 { background: rgba(34,211,238,0.08) !important; }
    .bg-blue-100 { background: rgba(34,211,238,0.12) !important; }
    .bg-blue-600 { background: linear-gradient(135deg, var(--royal-600), var(--cyan-500)) !important; }

    .bg-red-50 { background: rgba(239,68,68,0.08) !important; }
    .bg-red-600 { background: #DC2626 !important; }

    .bg-emerald-50 { background: rgba(16,185,129,0.08) !important; }

    .bg-amber-50 { background: rgba(245,158,11,0.08) !important; }
    .bg-amber-100 { background: rgba(245,158,11,0.12) !important; }

    .bg-orange-100 { background: rgba(249,115,22,0.12) !important; }

    /* ── HOVER BACKGROUNDS ── */
    .hover\:bg-slate-200:hover { background: rgba(255,255,255,0.12) !important; }
    .hover\:bg-slate-50:hover { background: rgba(255,255,255,0.08) !important; }
    .hover\:bg-blue-700:hover { background: linear-gradient(135deg, var(--royal-700), var(--cyan-500)) !important; }
    .hover\:bg-amber-200:hover { background: rgba(245,158,11,0.18) !important; }
    .hover\:bg-orange-200:hover { background: rgba(249,115,22,0.18) !important; }
    .hover\:bg-red-700:hover { background: #B91C1C !important; }
    .hover\:bg-slate-800:hover { background: var(--ocean-800) !important; }

    /* ── TEXT COLORS ── */
    .text-slate-800 { color: var(--text-hi) !important; }
    .text-slate-700 { color: var(--text) !important; }
    .text-slate-600 { color: var(--text-mid) !important; }
    .text-slate-500 { color: var(--text-low) !important; }
    .text-slate-400 { color: var(--text-faint) !important; }

    .text-blue-600 { color: var(--cyan-400) !important; }
    .text-blue-700 { color: var(--cyan-300) !important; }
    .text-blue-800 { color: var(--cyan-300) !important; }

    .text-red-500 { color: var(--danger) !important; }
    .text-red-600 { color: #FCA5A5 !important; }
    .text-red-700 { color: #FCA5A5 !important; }
    .text-red-800 { color: #FECACA !important; }

    .text-emerald-600 { color: var(--success) !important; }
    .text-emerald-700 { color: #6EE7B7 !important; }

    .text-amber-500 { color: var(--gold-400) !important; }
    .text-amber-600 { color: var(--gold-400) !important; }
    .text-amber-700 { color: var(--gold-300) !important; }
    .text-amber-800 { color: var(--gold-300) !important; }

    .text-orange-800 { color: #FDBA74 !important; }

    /* ── HOVER TEXT ── */
    .hover\:text-blue-600:hover { color: var(--cyan-400) !important; }

    /* ── BORDERS ── */
    .border-slate-200 { border-color: var(--bd-2) !important; }
    .border-slate-300 { border-color: var(--bd-3) !important; }
    .border-slate-900 { border-color: var(--ocean-900) !important; }

    .border-blue-100 { border-color: rgba(34,211,238,0.15) !important; }
    .border-blue-200 { border-color: rgba(34,211,238,0.22) !important; }
    .border-blue-400 { border-color: var(--cyan-400) !important; }
    .border-blue-700 { border-color: var(--royal-600) !important; }

    .border-red-200 { border-color: rgba(239,68,68,0.25) !important; }

    .border-emerald-100 { border-color: rgba(16,185,129,0.15) !important; }

    .border-amber-100 { border-color: rgba(245,158,11,0.15) !important; }
    .border-amber-200 { border-color: rgba(245,158,11,0.25) !important; }
    .border-amber-400 { border-color: var(--gold-400) !important; }

    .border-orange-400 { border-color: #FB923C !important; }

    /* ── HOVER BORDERS ── */
    .hover\:border-slate-300:hover { border-color: var(--bd-3) !important; }
    .hover\:border-blue-400:hover { border-color: var(--cyan-400) !important; }

    /* ── OPACITY MODIFIER VARIANTS ── */
    .border-blue-100\/50 { border-color: rgba(34,211,238,0.12) !important; }
    .border-emerald-100\/50 { border-color: rgba(16,185,129,0.12) !important; }
    .border-slate-100\/50 { border-color: rgba(255,255,255,0.06) !important; }
    .bg-blue-50\/30 { background: rgba(34,211,238,0.06) !important; }

    /* ── DIVIDE ── */
    .divide-slate-200 > * + * { border-top-color: var(--bd-2) !important; }
    .divide-slate-100 > * + * { border-top-color: var(--bd-1) !important; }

    /* ── RING ── */
    .ring-blue-200 { --tw-ring-color: rgba(34,211,238,0.22) !important; }
    .focus\:ring-blue-500:focus { --tw-ring-color: var(--cyan-500) !important; }
    .focus\:ring-blue-200:focus { --tw-ring-color: rgba(34,211,238,0.22) !important; }

    /* ── SHADOWS ── */
    .shadow-lg { box-shadow: var(--shadow-card) !important; }
    .shadow-md { box-shadow: 0 4px 12px -4px rgba(0,0,0,0.4) !important; }
    .shadow-sm { box-shadow: 0 2px 6px -2px rgba(0,0,0,0.3) !important; }
    .shadow-xl { box-shadow: 0 20px 40px -12px rgba(0,0,0,0.5) !important; }
    .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6) !important; }

    /* ── GLASS CARD ── */
    .glass-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%) !important;
        border: 1px solid var(--bd-1) !important;
        border-radius: 24px !important;
        box-shadow: var(--shadow-card) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        position: relative; overflow: hidden;
    }
    .glass-card::before {
        content: ''; position: absolute; inset: 0; border-radius: inherit;
        background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, transparent 30%);
        pointer-events: none; z-index: 0;
    }
    .glass-card > * { position: relative; z-index: 1; }

    /* ── PRIMARY ACTION BUTTONS ── */
    #nom-btn-submit:not([disabled]) {
        background: linear-gradient(135deg, var(--royal-600), var(--cyan-500)) !important;
        box-shadow: 0 6px 16px -6px rgba(6,182,212,0.5), inset 0 1px 0 rgba(255,255,255,0.18) !important;
        color: white !important;
    }
    #nom-btn-submit:not([disabled]):hover {
        box-shadow: 0 10px 24px -8px rgba(6,182,212,0.6), inset 0 1px 0 rgba(255,255,255,0.18) !important;
    }
    #btn-batch-submit:not([disabled]) {
        background: linear-gradient(135deg, var(--royal-600), var(--cyan-500)) !important;
        box-shadow: 0 6px 16px -6px rgba(6,182,212,0.5), inset 0 1px 0 rgba(255,255,255,0.18) !important;
        color: white !important;
    }
    #btn-batch-submit:not([disabled]):hover {
        box-shadow: 0 10px 24px -8px rgba(6,182,212,0.6), inset 0 1px 0 rgba(255,255,255,0.18) !important;
    }

    /* ── FORM ELEMENTS ── */
    select, input[type="text"], input[type="number"] { color: var(--text-hi) !important; }
    select option, select optgroup {
        background-color: var(--ocean-800) !important;
        color: var(--text-hi) !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    select optgroup { font-weight: 700; color: var(--cyan-300) !important; }
    input::placeholder { color: var(--text-faint) !important; }
    input:disabled { color: var(--text-low) !important; background: rgba(255,255,255,0.02) !important; }
    select:disabled { color: var(--text-low) !important; background: rgba(255,255,255,0.02) !important; }

    /* ── SCROLLBAR ── */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--glass-strong); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--cyan-500); }
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: var(--ocean-900); }
    ::-webkit-scrollbar-thumb { background: var(--glass-strong); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--cyan-500); }

    /* ── TABLE STICKY COLUMN ── */
    thead .sticky { background: rgba(255,255,255,0.10) !important; }
    tbody .sticky { background: rgba(255,255,255,0.04) !important; }

    /* ── APPROVED ANIMATION ── */
    #nom-approved-anim {
        background: linear-gradient(135deg, var(--ocean-950) 0%, var(--ocean-900) 50%, var(--ocean-850) 100%) !important;
    }

    /* ── NOMINATION GRID CARD HOVER ── */
    [onclick*="nomToggle"]:hover { transform: translateY(-2px) !important; }

    /* ── DEFECT MODAL BUTTONS ── */
    .defect-cb { accent-color: var(--cyan-500) !important; }

    /* ============================================================
       MODALS — Dark Ocean Theme
       ============================================================ */
    .warning-overlay {
        position: fixed; inset: 0;
        background: rgba(2,6,14,0.88);
        backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        z-index: 9999; display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        padding: 16px;
    }
    .warning-overlay.show { opacity: 1; pointer-events: all; }
    body:has(.warning-overlay.show) .bubbles span,
    body:has(.popup-overlay.show) .bubbles span { animation-play-state: paused; }

    .warning-card {
        background: linear-gradient(180deg, var(--ocean-800) 0%, var(--ocean-900) 100%);
        border: 1px solid var(--bd-2); border-radius: 24px;
        width: 90%; max-width: 450px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.06);
        transform: translateY(40px) scale(0.95); opacity: 0;
        transition: all 0.4s cubic-bezier(0.16,1,0.3,1);
        overflow: hidden; position: relative;
    }
    .warning-overlay.show .warning-card { transform: translateY(0) scale(1); opacity: 1; }

    .warning-header {
        background: linear-gradient(135deg, rgba(245,158,11,0.15) 0%, rgba(245,158,11,0.05) 100%);
        padding: 30px 30px 20px; text-align: center;
        border-bottom: 1px solid rgba(245,158,11,0.15);
    }
    .warning-icon {
        width: 60px; height: 60px; border-radius: 50%;
        background: linear-gradient(135deg, var(--gold-500), #B45309);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 10px 15px -3px rgba(245,158,11,0.3);
        animation: iconBounce 0.6s cubic-bezier(0.68,-0.55,0.265,1.55) 0.3s both;
    }
    @keyframes iconBounce { 0%{transform:scale(0)} 50%{transform:scale(1.2)} 100%{transform:scale(1)} }
    .warning-icon i { font-size: 26px; color: white; }
    .warning-title { font-family: 'Fraunces', serif; font-size: 20px; font-weight: 500; color: var(--gold-300); letter-spacing: -0.02em; }
    .warning-subtitle { font-size: 13px; color: var(--gold-400); margin-top: 4px; }
    .warning-body { padding: 24px 30px 30px; max-height: 300px; overflow-y: auto; position: relative; z-index: 1; }
    .warning-body::-webkit-scrollbar { width: 4px; }
    .warning-body::-webkit-scrollbar-thumb { background: var(--glass-strong); border-radius: 10px; }
    .error-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .error-item {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 12px; background: rgba(239,68,68,0.08);
        border: 1px solid rgba(239,68,68,0.20); border-radius: 12px;
        transform: translateX(-20px); opacity: 0;
        animation: slideInError 0.4s ease forwards;
    }
    .error-item:nth-child(1){animation-delay:.1s} .error-item:nth-child(2){animation-delay:.15s}
    .error-item:nth-child(3){animation-delay:.2s} .error-item:nth-child(4){animation-delay:.25s}
    .error-item:nth-child(5){animation-delay:.3s} .error-item:nth-child(n+6){animation-delay:.35s}
    @keyframes slideInError { to { transform: translateX(0); opacity: 1; } }
    .error-item i { color: var(--danger); font-size: 16px; margin-top: 2px; flex-shrink: 0; }
    .error-item .err-title { font-size: 11px; font-weight: 800; color: #FCA5A5; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px; }
    .error-item .err-desc { font-size: 12px; color: #FECACA; font-weight: 500; }
    .warning-footer { padding: 0 30px 30px; position: relative; z-index: 1; }
    .btn-close-warning {
        width: 100%; padding: 14px; border: none; border-radius: 14px;
        background: linear-gradient(135deg, var(--gold-500), #B45309);
        color: white; font-family: inherit; font-size: 14px; font-weight: 800;
        cursor: pointer; transition: all 0.2s;
        box-shadow: 0 6px 16px -6px rgba(245,158,11,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
    }
    .btn-close-warning:hover { transform: translateY(-1px); box-shadow: 0 10px 20px -8px rgba(245,158,11,0.5), inset 0 1px 0 rgba(255,255,255,0.2); }

    /* ── SUCCESS POPUP ── */
    .popup-overlay {
        position: fixed; inset: 0;
        background: rgba(2,6,14,0.88);
        backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        z-index: 9999; display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: opacity 0.4s ease;
        padding: 16px;
    }
    .popup-overlay.show { opacity: 1; pointer-events: all; }
    .popup-card {
        background: linear-gradient(180deg, var(--ocean-800) 0%, var(--ocean-900) 100%);
        border: 1px solid var(--bd-2); border-radius: 24px;
        padding: 44px 36px 32px; text-align: center;
        max-width: 380px; width: 90%;
        box-shadow: 0 25px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.06);
        transform: scale(0.8) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
        position: relative; overflow: hidden;
    }
    .popup-overlay.show .popup-card { transform: scale(1) translateY(0); }
    .popup-check {
        width: 72px; height: 72px; border-radius: 50%;
        background: linear-gradient(135deg, var(--success), #059669);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 8px 24px rgba(16,185,129,0.3), inset 0 1px 0 rgba(255,255,255,0.2);
        position: relative; z-index: 1;
    }
    .popup-check i { font-size: 34px; color: white; animation: checkPop 0.5s 0.3s cubic-bezier(0.16,1,0.3,1) both; }
    @keyframes checkPop { 0%{transform:scale(0) rotate(-45deg);opacity:0} 100%{transform:scale(1) rotate(0deg);opacity:1} }
    .popup-title { font-family: 'Fraunces', serif; font-size: 20px; font-weight: 500; color: var(--text-hi); margin-bottom: 8px; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .popup-desc { font-size: 13px; color: var(--text-mid); line-height: 1.6; margin-bottom: 26px; position: relative; z-index: 1; }
    .popup-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 28px; border: none; border-radius: 14px;
        background: linear-gradient(135deg, var(--royal-600), var(--cyan-500));
        color: white; font-family: inherit; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: all 0.3s ease;
        box-shadow: 0 6px 16px -6px rgba(6,182,212,0.5), inset 0 1px 0 rgba(255,255,255,0.18);
        position: relative; z-index: 1;
    }
    .popup-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 24px -8px rgba(6,182,212,0.6), inset 0 1px 0 rgba(255,255,255,0.18); }

    /* ── CONFIRM POPUP ── */
    .popup-icon.confirm {
        width: 72px; height: 72px; border-radius: 50%;
        background: linear-gradient(135deg, var(--royal-600), var(--cyan-500));
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 8px 24px rgba(6,182,212,0.3), inset 0 1px 0 rgba(255,255,255,0.2);
        position: relative; z-index: 1;
    }
    .popup-icon.confirm i { font-size: 30px; color: white; }
    .popup-btn-outline {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 28px; border: 1px solid var(--bd-3); border-radius: 14px;
        background: var(--glass-2); font-family: inherit;
        font-size: 14px; font-weight: 700; cursor: pointer;
        transition: all 0.2s; color: var(--text-mid);
        position: relative; z-index: 1;
    }
    .popup-btn-outline:hover { background: var(--glass-3); border-color: rgba(255,255,255,0.25); color: var(--text-hi); }
    .popup-actions { display: flex; gap: 12px; justify-content: center; position: relative; z-index: 1; }

    /* ── CONTENT BLADE MODALS (nomination preview, defect) ── */
    .backdrop-blur-sm { backdrop-filter: blur(8px) !important; -webkit-backdrop-filter: blur(8px) !important; }
    .bg-black\/60 { background: rgba(2,6,14,0.88) !important; }

    /* ============================================================
       ANIMATIONS
       ============================================================ */
    @keyframes fadeIn { from { opacity:0; transform:scale(0.97); } to { opacity:1; transform:scale(1); } }
    .fade-in { animation: fadeIn 0.2s ease-out forwards; }
    @keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
    .slide-down { animation: slideDown 0.25s ease-out forwards; }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 768px) {
        .top-nav { padding: 12px 16px; gap: 10px; flex-wrap: wrap; }
        .brand-mark { width: 38px; height: 38px; border-radius: 12px; }
        .brand-mark i { font-size: 16px; }
        .brand-text h1 { font-size: 15px; }
        .brand-text p { font-size: 9px; }
        .nav-user { flex-direction: column; align-items: flex-end; gap: 7px; flex-shrink: 0; }
        .user-card { max-width: 200px; }
        .avatar { width: 30px; height: 30px; font-size: 11px; }
        .user-info h4 { font-size: 11.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px; }
        .user-info span { font-size: 8.5px; }
        .btn-logout { padding: 6px 12px; font-size: 10.5px; width: 100%; justify-content: center; }
        .nav-center { order: 3; flex-basis: 100%; justify-content: flex-start; gap: 8px; margin-top: 2px; }
        .nav-pill { padding: 5px 10px; font-size: 10.5px; }
        .warning-card { width: 93%; }
        .warning-header { padding: 22px 20px 14px; }
        .warning-icon { width: 50px; height: 50px; }
        .warning-icon i { font-size: 22px; }
        .warning-title { font-size: 17px; }
        .warning-body { padding: 14px 20px 20px; max-height: 260px; }
        .warning-footer { padding: 0 20px 20px; }
        .popup-card { padding: 32px 22px 26px; }
        .popup-title { font-size: 17px; }
        .popup-desc { font-size: 12.5px; margin-bottom: 20px; }
        .popup-btn, .popup-btn-outline { padding: 10px 22px; font-size: 13px; }
        .popup-actions { flex-direction: column; gap: 10px; }
        .popup-actions .popup-btn, .popup-actions .popup-btn-outline { width: 100%; justify-content: center; }
        .popup-check { width: 60px; height: 60px; }
        .popup-check i { font-size: 28px; }
        .popup-icon.confirm { width: 60px; height: 60px; }
        .popup-icon.confirm i { font-size: 26px; }
        .glass-card { border-radius: 18px !important; }
        .bubbles { display: none; }
    }
    @media (max-width: 480px) {
        .top-nav { padding: 10px 14px; }
        .brand-text h1 { font-size: 14px; }
        .brand-text p { display: none; }
        .nav-center { display: none; }
        .user-info h4 { max-width: 90px; font-size: 11px; }
        .glass-card { border-radius: 16px !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.1s !important; }
        .bubbles { display: none; }
    }
    </style>
</head>
<body class="min-h-screen font-sans">

    <!-- ATMOSPHERIC BACKGROUND -->
    <div class="ocean-bg" aria-hidden="true"></div>
    <div class="bubbles" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>

    <div class="app-shell">
        <nav class="top-nav">
            <div class="brand">
                <div class="brand-mark" aria-hidden="true"><i class="fas fa-gavel"></i></div>
                <div class="brand-text">
                    <h1>LCI <em>Suite</em></h1>
                    <p>Panel Penjurian</p>
                </div>
            </div>
            <div class="nav-center">
                <div class="nav-pill"><span class="live-dot"></span>Sesi Aktif</div>
                <div class="nav-pill" style="color:var(--cyan-300);"><i class="fas fa-water" style="font-size:10px;"></i>Online</div>
            </div>
            <div class="nav-user">
                <div class="user-card">
                    <div class="avatar">{{ strtoupper(mb_substr(trim(Auth::user()->name), 0, 1)) }}</div>
                    <div class="user-info">
                        <h4>{{ Auth::user()->name }}</h4>
                        <span>Juri Aktif</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout"><i class="fas fa-right-from-bracket"></i> Keluar</button>
                </form>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">
            @yield('content')
        </main>
    </div>

    <!-- Warning Modal -->
    <div class="warning-overlay" id="warningModal">
        <div class="warning-card">
            <div class="warning-header">
                <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="warning-title">Perhatian</h2>
                <p class="warning-subtitle">Silakan periksa kembali sebelum melanjutkan:</p>
            </div>
            <div class="warning-body">
                <ul class="error-list" id="errorListContainer"></ul>
            </div>
            <div class="warning-footer">
                <button class="btn-close-warning" onclick="closeWarningModal()">
                    <i class="fas fa-check" style="margin-right:6px;"></i> OK, Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div class="popup-overlay" id="successPopup">
        <div class="popup-card">
            <div class="popup-check"><i class="fas fa-check"></i></div>
            <h2 class="popup-title" id="popupTitle">Berhasil!</h2>
            <p class="popup-desc" id="popupDesc">Data telah tersimpan.</p>
            <button class="popup-btn" onclick="document.getElementById('successPopup').classList.remove('show')">
                <i class="fas fa-circle-check"></i> OK, Tutup
            </button>
        </div>
    </div>

    <!-- Confirm Popup -->
    <div class="popup-overlay" id="popupConfirm">
        <div class="popup-card">
            <div class="popup-icon confirm"><i class="fas fa-paper-plane"></i></div>
            <h2 class="popup-title">Kirim ke Grand Juri?</h2>
            <p class="popup-desc" id="confirm-message">Nilai yang sudah Anda simpan akan dikirim ke Grand Juri untuk ditinjau. Tindakan ini tidak dapat dibatalkan.</p>
            <div class="popup-actions">
                <button class="popup-btn-outline" id="confirm-cancel"><i class="fas fa-xmark"></i> Batal</button>
                <button class="popup-btn" id="confirm-ok" style="background:linear-gradient(135deg,#10B981,#059669);box-shadow:0 6px 16px -6px rgba(16,185,129,0.5),inset 0 1px 0 rgba(255,255,255,0.18);">
                    <i class="fas fa-paper-plane"></i> Ya, Kirim
                </button>
            </div>
        </div>
    </div>

    @yield('modals')

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function showWarningModal(errorsArray) {
    const container = document.getElementById('errorListContainer');
    container.innerHTML = '';
    (errorsArray || []).forEach(function(err) {
        let iconClass = 'fas fa-circle-xmark', errTitle = 'Kesalahan', errDesc = err.msg || err;
        if (err.type === 'minus') { iconClass = 'fas fa-arrow-down'; errTitle = 'Nilai Minus'; }
        else if (err.type === 'limit') { iconClass = 'fas fa-arrow-up'; errTitle = 'Melebihi Batas'; }
        else if (err.type === 'select') { iconClass = 'fas fa-hand-pointer'; errTitle = 'Aksi Diperlukan'; }
        const li = document.createElement('li');
        li.className = 'error-item';
        li.innerHTML = '<i class="' + iconClass + '"></i><div><span class="err-title">' + errTitle + '</span><span class="err-desc">' + errDesc + '</span></div>';
        container.appendChild(li);
    });
    document.getElementById('warningModal').classList.add('show');
}
function closeWarningModal() { document.getElementById('warningModal').classList.remove('show'); }
document.getElementById('warningModal')?.addEventListener('click', function(e) { if (e.target === this) closeWarningModal(); });
document.getElementById('successPopup')?.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });

document.getElementById('confirm-ok')?.addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (confirmResolve) confirmResolve(true);
});
document.getElementById('confirm-cancel')?.addEventListener('click', function() {
    document.getElementById('popupConfirm').classList.remove('show');
    if (confirmResolve) confirmResolve(false);
});
document.getElementById('popupConfirm')?.addEventListener('click', function(e) { if (e.target === this) { this.classList.remove('show'); if (confirmResolve) confirmResolve(false); } });

function showSuccessPopup(title, desc) {
    document.getElementById('popupTitle').innerText = title;
    document.getElementById('popupDesc').innerHTML = desc;
    document.getElementById('successPopup').classList.add('show');
}

let confirmResolve = null;
function showConfirm(message) {
    document.getElementById('confirm-message').textContent = message;
    document.getElementById('popupConfirm').classList.add('show');
    return new Promise(resolve => { confirmResolve = resolve; });
}

let toastTimer;
function showToast(msg, type = 'success') {
    if (type === 'error') { showWarningModal([{type:'select', msg: msg}]); return; }
    showSuccessPopup('Berhasil', msg);
}

async function apiFetch(url, opts = {}) {
    const defaults = { headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
    const res = await fetch(url, { ...defaults, ...opts, headers: { ...defaults.headers, ...(opts.headers || {}) } });
    return res.json();
}

if (typeof window.onFilterChange !== 'function') {
    window.onFilterChange = function() {};
}
</script>

@stack('scripts')

</body>
</html>