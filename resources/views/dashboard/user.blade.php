@php
    $profilLengkap = $pesertaSaya && !empty($pesertaSaya->detail_anggota);
    $totalIkan = $ikansSaya->count();
    $totalDiundi = $ikansSaya->whereNotNull('nomor_tank')->count();
    $totalBelumDiundi = $totalIkan - $totalDiundi;
    $progressUndian = $totalIkan > 0 ? round(($totalDiundi / $totalIkan) * 100) : 0;
    $mvpCount = 0;
    try { $mvpCount = $ikansSaya->where('is_mvp', true)->count(); } catch (\Throwable $e) { $mvpCount = 0; }
    $initial = strtoupper(mb_substr(trim($user->name), 0, 1));
    $undianOpen = $undianOpen ?? true; // ★ Dari controller, default true
@endphp
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
    <meta name="theme-color" content="#0B1220">
    <title>My Contest — LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ============================================================
           LCI SUITE — PREMIUM AQUATIC DASHBOARD
           Deep Ocean × Electric Cyan × Gold Accent
           ============================================================ */

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            /* Deep Ocean */
            --ocean-950: #04070F;
            --ocean-900: #0B1220;
            --ocean-850: #0E1729;
            --ocean-800: #111E36;
            --ocean-700: #182947;
            --ocean-600: #1F3358;

            /* Royal Blue */
            --royal-700: #1D4ED8;
            --royal-600: #2563EB;
            --royal-500: #3B82F6;
            --royal-400: #60A5FA;

            /* Electric Cyan / Aqua */
            --cyan-500: #06B6D4;
            --cyan-400: #22D3EE;
            --cyan-300: #67E8F9;
            --cyan-200: #A5F3FC;

            /* Gold */
            --gold-700: #B45309;
            --gold-600: #D97706;
            --gold-500: #F59E0B;
            --gold-400: #FBBF24;
            --gold-300: #FCD34D;

            /* Glass surfaces */
            --glass-1: rgba(255,255,255,0.03);
            --glass-2: rgba(255,255,255,0.05);
            --glass-3: rgba(255,255,255,0.08);
            --glass-strong: rgba(255,255,255,0.12);

            /* Borders */
            --bd-1: rgba(255,255,255,0.06);
            --bd-2: rgba(255,255,255,0.10);
            --bd-3: rgba(255,255,255,0.16);
            --bd-cyan: rgba(34,211,238,0.25);
            --bd-gold: rgba(245,158,11,0.30);

            /* Text */
            --text-hi: #F8FAFC;
            --text: #E2E8F0;
            --text-mid: #94A3B8;
            --text-low: #64748B;
            --text-faint: #475569;

            /* Status */
            --success: #10B981;
            --success-glow: rgba(16,185,129,0.35);
            --danger: #EF4444;
            --warning: #F59E0B;

            /* Legacy aliases (BACKWARD COMPAT untuk JS inline style yang sudah ada) */
            --blue-50: rgba(34,211,238,0.08);
            --blue-100: rgba(34,211,238,0.12);
            --blue-200: rgba(34,211,238,0.20);
            --blue-300: var(--cyan-300);
            --blue-400: var(--cyan-400);
            --blue-500: var(--royal-500);
            --blue-600: var(--royal-600);
            --blue-700: var(--royal-700);
            --blue-800: #1E40AF;
            --white: #FFFFFF;
            --gray-50: var(--ocean-850);
            --gray-100: rgba(255,255,255,0.05);
            --gray-200: rgba(255,255,255,0.08);
            --gray-300: rgba(255,255,255,0.14);
            --gray-400: var(--text-mid);
            --gray-500: var(--text-low);
            --gray-600: var(--text-faint);
            --gray-700: #334155;
            --gray-800: var(--text-hi);
            --gray-900: var(--text-hi);
            --red-500: var(--danger);
            --green-500: var(--success);
            --green-600: #059669;
            --dark-bg: var(--ocean-900);
            --dark-surface: var(--ocean-800);

            /* Shadows */
            --shadow-glow-cyan: 0 0 0 1px var(--bd-cyan), 0 20px 50px -20px rgba(34,211,238,0.35);
            --shadow-glow-gold: 0 0 0 1px var(--bd-gold), 0 20px 50px -20px rgba(245,158,11,0.35);
            --shadow-card: 0 1px 0 rgba(255,255,255,0.04) inset, 0 30px 60px -30px rgba(0,0,0,0.6), 0 18px 36px -24px rgba(6,182,212,0.10);
            --shadow-press: 0 1px 0 rgba(255,255,255,0.04) inset, 0 8px 20px -12px rgba(0,0,0,0.7);
        }

        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--ocean-900);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.01em;
        }

        /* ====================================================
           ATMOSPHERIC BACKGROUND — Static & Lightweight
           ==================================================== */
        .ocean-bg {
            position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none;
            background:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(37,99,235,0.14) 0%, transparent 55%),
                radial-gradient(ellipse 50% 50% at 100% 100%, rgba(6,182,212,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 0% 70%, rgba(29,78,216,0.08) 0%, transparent 60%),
                linear-gradient(180deg, var(--ocean-950) 0%, var(--ocean-900) 45%, var(--ocean-850) 100%);
        }

        /* Floating bubbles — ringan (6 bubble saja, GPU-accelerated) */
        .bubbles { position: fixed; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }
        .bubbles span {
            position: absolute;
            display: block;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.55), rgba(34,211,238,0.20) 60%, transparent 70%);
            box-shadow: 0 0 8px rgba(34,211,238,0.20);
            bottom: -20px;
            will-change: transform, opacity;
            animation: bubbleRise linear infinite;
            opacity: 0;
        }
        .bubbles span:nth-child(1) { left: 10%; width: 8px;  height: 8px;  animation-duration: 22s; animation-delay: 0s;  }
        .bubbles span:nth-child(2) { left: 25%; width: 5px;  height: 5px;  animation-duration: 28s; animation-delay: 6s;  }
        .bubbles span:nth-child(3) { left: 42%; width: 10px; height: 10px; animation-duration: 24s; animation-delay: 3s;  }
        .bubbles span:nth-child(4) { left: 60%; width: 6px;  height: 6px;  animation-duration: 26s; animation-delay: 9s;  }
        .bubbles span:nth-child(5) { left: 75%; width: 9px;  height: 9px;  animation-duration: 21s; animation-delay: 2s;  }
        .bubbles span:nth-child(6) { left: 90%; width: 5px;  height: 5px;  animation-duration: 27s; animation-delay: 11s; }
        @keyframes bubbleRise {
            0%   { transform: translate3d(0, 0, 0) scale(0.9); opacity: 0; }
            10%  { opacity: 0.55; }
            90%  { opacity: 0.25; }
            100% { transform: translate3d(15px, -110vh, 0) scale(0.85); opacity: 0; }
        }

        /* ====================================================
           APP SHELL
           ==================================================== */
        .app-shell { position: relative; z-index: 10; min-height: 100vh; display: flex; flex-direction: column; }

        /* ====================================================
           TOP NAVIGATION
           ==================================================== */
        .topnav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 32px;
            background: rgba(11,18,32,0.72);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--bd-1);
            position: sticky; top: 0; z-index: 100;
            animation: navIn 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes navIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        .brand { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .brand-mark {
            width: 44px; height: 44px; border-radius: 14px;
            display: grid; place-items: center;
            background:
                radial-gradient(circle at 30% 30%, rgba(34,211,238,0.5), transparent 60%),
                linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%);
            box-shadow: 0 6px 18px -6px rgba(6,182,212,0.55), inset 0 1px 0 rgba(255,255,255,0.25);
            flex-shrink: 0;
        }
        .brand-mark svg { width: 24px; height: 24px; color: white; }

        .brand-text h1 {
            font-family: 'Fraunces', serif;
            font-weight: 600; font-size: 19px; letter-spacing: -0.02em;
            color: var(--text-hi); line-height: 1.05;
        }
        .brand-text h1 em { font-style: italic; font-weight: 400; color: var(--cyan-400); }
        .brand-text p { font-size: 11px; color: var(--text-mid); margin-top: 2px; letter-spacing: 0.04em; text-transform: uppercase; font-weight: 600; }

        .nav-center { display: flex; align-items: center; gap: 10px; }
        .nav-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 14px;
            border: 1px solid var(--bd-2);
            border-radius: 999px;
            background: var(--glass-2);
            font-size: 11.5px; font-weight: 600;
            color: var(--text);
            letter-spacing: 0.02em;
        }
        .nav-pill .live-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 8px var(--success-glow);
            animation: livePulse 2.4s ease-in-out infinite;
        }
        @keyframes livePulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.55; } }

        .nav-user { display: flex; align-items: center; gap: 14px; }
        .user-card { display: flex; align-items: center; gap: 12px; padding: 6px 10px 6px 6px; border: 1px solid var(--bd-2); border-radius: 999px; background: var(--glass-2); }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: grid; place-items: center;
            background: linear-gradient(135deg, var(--gold-500), var(--gold-700));
            color: white; font-weight: 800; font-size: 14px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.3), 0 4px 10px -2px rgba(245,158,11,0.4);
            letter-spacing: 0;
        }
        .user-info { text-align: left; line-height: 1.1; padding-right: 4px; }
        .user-info h4 { font-size: 13px; font-weight: 700; color: var(--text-hi); }
        .user-info span { font-size: 10.5px; color: var(--text-mid); letter-spacing: 0.04em; text-transform: uppercase; font-weight: 600; }

        .btn-logout {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 14px;
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            color: var(--text-mid);
            border-radius: 12px;
            font-size: 12px; font-weight: 700; letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-logout:hover { background: rgba(239,68,68,0.12); color: #fca5a5; border-color: rgba(239,68,68,0.35); }

        /* ====================================================
           MAIN CONTENT
           ==================================================== */
        .main-wrap { flex: 1; padding: 32px; max-width: 1480px; margin: 0 auto; width: 100%; }

        /* HERO */
        .hero {
            display: grid;
            grid-template-columns: 1.2fr 2fr;
            gap: 24px;
            margin-bottom: 28px;
            animation: heroIn 0.8s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes heroIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .hero-welcome {
            position: relative;
            padding: 28px 28px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(37,99,235,0.20) 0%, rgba(6,182,212,0.12) 100%);
            border: 1px solid var(--bd-cyan);
            overflow: hidden;
        }
        .hero-welcome::before {
            content: ''; position: absolute; right: -40px; top: -40px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(34,211,238,0.28) 0%, transparent 70%);
        }
        .hero-welcome::after {
            content: ''; position: absolute; right: 18px; bottom: -10px;
            width: 110px; height: 110px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2367E8F9' stroke-width='0.5' opacity='0.35'%3E%3Cpath d='M16.5 8c-1.4 0-2.7.3-3.9.9-1.7-2.4-4.4-3.9-7.6-3.9-.6 0-1.1.1-1.6.2.4 1 .6 2.1.6 3.3 0 1.1-.2 2.2-.6 3.1-.4 1.1-.6 2.2-.6 3.4 0 1.2.2 2.3.6 3.4.9-.6 1.9-.9 3-.9 3.2 0 5.9-1.5 7.6-3.9 1.2.6 2.5.9 3.9.9 4.1 0 7.5-3.4 7.5-7.5S20.6 8 16.5 8zm-.5 9c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            pointer-events: none;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 10.5px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;
            color: var(--cyan-300);
            padding: 5px 12px;
            background: rgba(34,211,238,0.10);
            border: 1px solid rgba(34,211,238,0.25);
            border-radius: 999px;
            position: relative; z-index: 1;
        }
        .hero-h2 {
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-size: 38px; line-height: 1.05;
            color: var(--text-hi);
            margin-top: 14px;
            letter-spacing: -0.025em;
            position: relative; z-index: 1;
        }
        .hero-h2 em { font-style: italic; color: var(--cyan-300); font-weight: 400; }
        .hero-sub {
            margin-top: 10px;
            font-size: 13.5px; line-height: 1.6;
            color: var(--text-mid); max-width: 95%;
            position: relative; z-index: 1;
        }

        /* Stats row */
        .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .stat-card {
            position: relative;
            padding: 18px 18px;
            border-radius: 20px;
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            overflow: hidden;
            transition: transform 0.25s ease, border-color 0.25s ease, background 0.25s ease;
        }
        .stat-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
            background: linear-gradient(180deg, var(--cyan-400), var(--royal-500));
            opacity: 0.8;
        }
        .stat-card:hover { transform: translateY(-3px); border-color: var(--bd-3); background: var(--glass-3); }
        .stat-card.is-gold::before { background: linear-gradient(180deg, var(--gold-400), var(--gold-600)); }
        .stat-card.is-success::before { background: linear-gradient(180deg, #34D399, var(--success)); }
        .stat-icon {
            width: 34px; height: 34px; border-radius: 10px;
            display: grid; place-items: center;
            background: rgba(34,211,238,0.10);
            color: var(--cyan-400);
            font-size: 14px;
            margin-bottom: 14px;
            border: 1px solid rgba(34,211,238,0.20);
        }
        .stat-card.is-gold .stat-icon { background: rgba(245,158,11,0.10); color: var(--gold-400); border-color: rgba(245,158,11,0.25); }
        .stat-card.is-success .stat-icon { background: rgba(16,185,129,0.10); color: #34D399; border-color: rgba(16,185,129,0.25); }

        .stat-label { font-size: 10.5px; font-weight: 700; color: var(--text-mid); letter-spacing: 0.12em; text-transform: uppercase; }
        .stat-value {
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-size: 34px; line-height: 1;
            color: var(--text-hi);
            margin-top: 8px;
            letter-spacing: -0.02em;
            display: flex; align-items: baseline; gap: 6px;
        }
        .stat-value .stat-sub { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; color: var(--text-low); font-weight: 600; }
        .stat-meta { font-size: 11px; color: var(--text-low); margin-top: 8px; font-weight: 500; }
        .stat-meta i { color: var(--cyan-400); font-size: 9px; margin-right: 4px; }

        /* WORKFLOW STEPPER */
        .stepper {
            display: flex; align-items: center; gap: 0;
            padding: 12px 20px;
            background: rgba(11,18,32,0.55);
            border: 1px solid var(--bd-1);
            border-radius: 18px;
            margin-bottom: 28px;
            overflow-x: auto;
            backdrop-filter: blur(12px);
            animation: heroIn 0.9s 0.1s cubic-bezier(0.16,1,0.3,1) both;
        }
        .stepper-item {
            display: flex; align-items: center; gap: 10px;
            flex: 1; min-width: 140px;
            position: relative;
        }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%;
            display: grid; place-items: center;
            font-size: 12px; font-weight: 800;
            background: var(--glass-3);
            color: var(--text-mid);
            border: 1px solid var(--bd-2);
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .stepper-item.is-done .step-num {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white; border-color: transparent;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.10);
        }
        .stepper-item.is-active .step-num {
            background: linear-gradient(135deg, var(--cyan-500), var(--royal-600));
            color: white; border-color: transparent;
            box-shadow: 0 0 0 4px rgba(34,211,238,0.15), 0 0 20px rgba(34,211,238,0.3);
        }
        .step-label { font-size: 12.5px; font-weight: 700; color: var(--text-mid); white-space: nowrap; }
        .stepper-item.is-done .step-label, .stepper-item.is-active .step-label { color: var(--text-hi); }
        .step-arrow { flex: 1; height: 1px; background: var(--bd-2); margin: 0 12px; min-width: 16px; position: relative; }
        .step-arrow::after { content: '>'; position: absolute; right: -2px; top: -7px; color: var(--text-faint); font-family: serif; font-size: 12px; }

        /* DASHBOARD GRID */
        .dash-grid { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: 24px; }
        .col-stack { display: flex; flex-direction: column; gap: 24px; min-width: 0; }

        /* ====================================================
           PREMIUM GLASS CARD BASE
           ==================================================== */
        .glass-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid var(--bd-1);
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            animation: cardEntry 0.6s 0.15s cubic-bezier(0.16,1,0.3,1) both;
        }
        .glass-card::before {
            content: ''; position: absolute; inset: 0;
            border-radius: inherit;
            background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, transparent 30%);
            pointer-events: none;
        }
        @keyframes cardEntry { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes textFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        .card-header {
            padding: 22px 26px 0; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
            position: relative;
        }
        .card-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 16px; font-weight: 800; color: var(--text-hi); letter-spacing: -0.01em; display: flex; align-items: center; gap: 10px; }
        .card-title .title-icon {
            width: 32px; height: 32px; border-radius: 10px;
            display: grid; place-items: center;
            background: rgba(34,211,238,0.10);
            border: 1px solid rgba(34,211,238,0.20);
            color: var(--cyan-400); font-size: 14px;
        }
        .card-subtitle { font-size: 11.5px; color: var(--text-mid); margin-top: 6px; font-weight: 500; max-width: 90%; line-height: 1.5; }
        .card-body { padding: 22px 26px 26px; position: relative; }

        /* ====================================================
           FORM
           ==================================================== */
        .form-group { display: flex; flex-direction: column; margin-bottom: 16px; animation: textFadeIn 0.6s 0.4s both; }
        .form-label {
            font-size: 10.5px; font-weight: 700; color: var(--text-mid); margin-bottom: 7px;
            letter-spacing: 0.12em; text-transform: uppercase;
        }
        .input-wrapper { position: relative; }
        .input-wrapper i.input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            font-size: 13px; color: var(--text-low); pointer-events: none; z-index: 1;
            transition: color 0.3s;
        }
        .form-input, .form-select {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border: 1px solid var(--bd-2);
            border-radius: 12px;
            background: var(--glass-2);
            font-family: inherit; font-size: 13.5px;
            color: var(--text-hi);
            outline: none;
            transition: all 0.25s ease;
            appearance: none;
        }
        .form-select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        /* FIX: Native <option> di browser tidak meng-inherit warna dari <select>.
           Style eksplisit ini wajib agar list dropdown TERBACA di tema gelap. */
        .form-select option,
        .fish-filter option {
            background-color: #111E36;
            color: #F8FAFC;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 12px;
        }
        .form-select option:disabled,
        .fish-filter option:disabled {
            color: #64748B;
        }
        .form-select option:checked,
        .form-select option:hover,
        .fish-filter option:checked,
        .fish-filter option:hover {
            background-color: #1F3358;
            color: #67E8F9;
        }
        .form-input::placeholder { color: var(--text-faint); }
        .form-input:focus, .form-select:focus {
            border-color: var(--cyan-400);
            background: var(--glass-3);
            box-shadow: 0 0 0 4px rgba(34,211,238,0.08);
        }
        .form-input:focus + .input-icon, .input-wrapper:focus-within i.input-icon { color: var(--cyan-400); }
        .form-input:read-only {
            background: rgba(255,255,255,0.02);
            color: var(--text-mid);
            cursor: not-allowed;
        }

        .toggle-group {
            display: flex;
            background: var(--glass-2);
            border-radius: 14px;
            padding: 4px;
            border: 1px solid var(--bd-2);
        }
        .toggle-option { flex: 1; text-align: center; }
        .toggle-option input { display: none; }
        .toggle-option label {
            display: block;
            padding: 10px 8px;
            border-radius: 11px;
            font-size: 12.5px; font-weight: 700;
            color: var(--text-mid);
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.01em;
        }
        .toggle-option input:checked + label {
            background: linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%);
            color: white;
            box-shadow: 0 4px 14px -4px rgba(6,182,212,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        /* BUTTONS */
        .submit-btn {
            width: 100%;
            padding: 13px 18px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%);
            color: white;
            font-family: inherit;
            font-size: 13.5px; font-weight: 800;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 6px 16px -6px rgba(6,182,212,0.5), inset 0 1px 0 rgba(255,255,255,0.18);
            margin-top: 8px;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px -8px rgba(6,182,212,0.6), inset 0 1px 0 rgba(255,255,255,0.18); }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none !important; }

        .btn-green {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            box-shadow: 0 8px 20px -8px rgba(16,185,129,0.5), inset 0 1px 0 rgba(255,255,255,0.18);
        }
        .btn-green:hover { box-shadow: 0 14px 30px -10px rgba(16,185,129,0.6), inset 0 1px 0 rgba(255,255,255,0.18); }

        .input-error-msg { font-size: 11.5px; color: #fca5a5; margin-top: 6px; font-weight: 600; display: none; }

        /* ====================================================
           LOTTERY MACHINE — LUXURY DIGITAL DRAW
           ==================================================== */
        .machine-card { background: linear-gradient(180deg, #050B17 0%, #0A1426 100%); border: 1px solid var(--bd-2); }
        .machine-card::before {
            background:
                radial-gradient(ellipse at 50% 0%, rgba(34,211,238,0.18) 0%, transparent 60%),
                linear-gradient(180deg, rgba(255,255,255,0.04) 0%, transparent 30%);
        }
        .machine-card .card-header { border-bottom: 1px solid var(--bd-1); padding-bottom: 18px; }
        .machine-card .card-title .title-icon { background: rgba(34,211,238,0.15); border-color: rgba(34,211,238,0.30); color: var(--cyan-300); }

        .status-badge {
            font-size: 10px; font-weight: 700; letter-spacing: 0.14em;
            background: var(--glass-3);
            color: var(--text-mid);
            padding: 6px 12px;
            border-radius: 999px;
            white-space: nowrap;
            border: 1px solid var(--bd-2);
            display: inline-flex; align-items: center; gap: 6px;
        }
        .status-badge.success {
            background: rgba(16,185,129,0.12);
            color: #6EE7B7;
            border-color: rgba(16,185,129,0.30);
        }
        .status-badge::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor;
        }

        .machine-body { padding: 28px 26px; display: flex; flex-direction: column; align-items: center; }

        .lcd-screen {
            width: 100%;
            background:
                radial-gradient(ellipse at 50% 0%, rgba(34,211,238,0.12) 0%, transparent 60%),
                linear-gradient(180deg, #030712 0%, #0A1426 100%);
            border-radius: 20px;
            padding: 32px 28px;
            margin-bottom: 22px;
            border: 1px solid rgba(34,211,238,0.18);
            position: relative;
            overflow: hidden;
            text-align: center;
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.06),
                inset 0 0 40px rgba(34,211,238,0.04),
                0 16px 32px -16px rgba(0,0,0,0.5);
        }
        .lcd-screen::before {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(0deg, rgba(34,211,238,0.03) 0px, rgba(34,211,238,0.03) 1px, transparent 1px, transparent 4px);
            pointer-events: none;
            opacity: 0.5;
        }
        .lcd-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px; color: var(--cyan-300); font-weight: 600;
            letter-spacing: 0.4em;
            text-transform: uppercase;
            margin-bottom: 12px;
            opacity: 0.9;
            position: relative; z-index: 2;
        }
        .number-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 96px; font-weight: 800;
            color: var(--cyan-300);
            line-height: 1;
            text-shadow:
                0 0 20px rgba(34,211,238,0.6),
                0 0 40px rgba(34,211,238,0.3),
                0 0 80px rgba(34,211,238,0.15);
            transition: all 0.1s;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.04em;
            position: relative; z-index: 2;
        }
        .number-display.rolling {
            color: var(--cyan-400);
            animation: glitch 0.08s infinite;
        }
        .number-display.final {
            color: var(--gold-400);
            text-shadow:
                0 0 30px rgba(251,191,36,0.8),
                0 0 60px rgba(251,191,36,0.4),
                0 0 100px rgba(251,191,36,0.2);
            transform: scale(1.08);
            animation: finalPulse 0.6s ease-out;
        }
        @keyframes glitch { 0% { opacity: 0.85; filter: blur(0.3px); } 50% { opacity: 1; filter: blur(0); } 100% { opacity: 0.85; filter: blur(0.3px); } }
        @keyframes finalPulse { 0% { transform: scale(0.85); opacity: 0.5; } 60% { transform: scale(1.15); } 100% { transform: scale(1.08); opacity: 1; } }

        .lcd-info-text { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-low); margin-top: 12px; letter-spacing: 0.08em; position: relative; z-index: 2; }

        #resetBanner {
            display: none;
            width: 100%;
            background: linear-gradient(135deg, rgba(245,158,11,0.10), rgba(245,158,11,0.05));
            border: 1px solid rgba(245,158,11,0.25);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 18px;
            align-items: center; gap: 12px;
        }
        #resetBannerText { font-size: 12px; color: var(--gold-300); line-height: 1.55; flex: 1; }
        #resetBanner .fa-triangle-exclamation { color: var(--gold-400); font-size: 18px; flex-shrink: 0; }

        /* ====================================================
           FISH LIST — PREMIUM CARDS
           ==================================================== */
        .fish-toolbar {
            display: flex; gap: 10px; align-items: center;
            padding: 0 26px 16px;
            flex-wrap: wrap;
        }
        .fish-search {
            flex: 1; min-width: 180px;
            position: relative;
        }
        .fish-search input {
            width: 100%;
            padding: 10px 14px 10px 36px;
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            border-radius: 12px;
            color: var(--text-hi);
            font-family: inherit;
            font-size: 12.5px;
            outline: none;
            transition: all 0.2s;
        }
        .fish-search input:focus { border-color: var(--cyan-400); background: var(--glass-3); }
        .fish-search input::placeholder { color: var(--text-faint); }
        .fish-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-low); font-size: 12px; }

        .fish-filter {
            padding: 9px 14px 9px 14px;
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            border-radius: 12px;
            color: var(--text);
            font-family: inherit;
            font-size: 12px; font-weight: 600;
            cursor: pointer;
            appearance: none;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            outline: none;
        }
        .fish-filter:hover { border-color: var(--bd-3); background-color: var(--glass-3); }

        .ikan-list-wrapper {
            width: 100%;
            max-height: 480px;
            overflow-y: auto;
            padding: 4px 26px 26px;
            scroll-behavior: smooth;
        }
        .ikan-list-wrapper::-webkit-scrollbar { width: 6px; }
        .ikan-list-wrapper::-webkit-scrollbar-track { background: transparent; }
        .ikan-list-wrapper::-webkit-scrollbar-thumb { background: var(--glass-strong); border-radius: 10px; }
        .ikan-list-wrapper::-webkit-scrollbar-thumb:hover { background: var(--cyan-500); }

        .ikan-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .ikan-item {
            position: relative;
            background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.015) 100%);
            border: 1px solid var(--bd-2);
            border-radius: 16px;
            padding: 14px 16px;
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
            overflow: hidden;
            min-height: 70px;
        }
        .ikan-item::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px;
            background: linear-gradient(180deg, var(--cyan-400), var(--royal-500));
            opacity: 0;
            transition: opacity 0.3s;
        }
        .ikan-item:hover {
            background: linear-gradient(135deg, rgba(34,211,238,0.06) 0%, rgba(255,255,255,0.02) 100%);
            border-color: rgba(34,211,238,0.20);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -10px rgba(6,182,212,0.20);
        }
        .ikan-item:hover::before { opacity: 1; }

        .ikan-item-info { flex: 1; min-width: 0; }
        .ikan-item-info h4 {
            font-size: 13px; font-weight: 700; color: var(--text-hi);
            letter-spacing: -0.005em;
            display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        }
        .ikan-item-info h4 .fa-fish {
            color: var(--cyan-400) !important;
            text-shadow: 0 0 8px rgba(34,211,238,0.4);
        }
        .ikan-item-info p {
            font-size: 10.5px;
            color: var(--text-mid);
            margin-top: 4px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .ikan-item-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .tank-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 18px; font-weight: 800;
            min-width: 36px; text-align: right;
            letter-spacing: -0.02em;
        }
        .tank-num.empty { color: var(--text-faint); }
        .tank-num.filled {
            color: var(--cyan-300);
            text-shadow: 0 0 12px rgba(34,211,238,0.5);
        }

        .btn-acak-kecil {
            background: linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%);
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 10px;
            font-size: 10.5px; font-weight: 800;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 5px;
            font-family: inherit;
            box-shadow: 0 4px 12px -4px rgba(6,182,212,0.5), inset 0 1px 0 rgba(255,255,255,0.18);
        }
        .btn-acak-kecil:hover { transform: translateY(-1px) scale(1.03); box-shadow: 0 8px 18px -6px rgba(6,182,212,0.65), inset 0 1px 0 rgba(255,255,255,0.18); }
        .btn-acak-kecil:disabled { background: var(--glass-strong); color: var(--text-low); cursor: not-allowed; transform: none; box-shadow: none; }

        /* Green success checkmark */
        .ikan-item-right > span > .fa-circle-check {
            color: var(--success) !important;
            font-size: 18px !important;
            filter: drop-shadow(0 0 8px var(--success-glow));
        }

        .ikan-empty-state {
            text-align: center;
            color: var(--text-low);
            font-size: 13px;
            padding: 40px 20px;
            width: 100%;
            grid-column: 1 / -1;
        }
        .ikan-empty-state .empty-icon {
            width: 64px; height: 64px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            display: grid; place-items: center;
            color: var(--cyan-400);
            font-size: 24px;
        }

        /* ====================================================
           MVP CARD — GOLD ACCENT
           ==================================================== */
        .mvp-card {
            border: 1px solid rgba(245,158,11,0.25) !important;
            background: linear-gradient(180deg, rgba(245,158,11,0.06) 0%, rgba(245,158,11,0.02) 100%);
        }
        .mvp-card::before {
            background: linear-gradient(180deg, rgba(251,191,36,0.10) 0%, transparent 30%);
        }
        .mvp-card .card-title { color: var(--gold-300); }
        .mvp-card .card-title .title-icon {
            background: rgba(245,158,11,0.15);
            border-color: rgba(245,158,11,0.30);
            color: var(--gold-400);
            box-shadow: 0 0 20px -8px rgba(245,158,11,0.5);
        }

        .mvp-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px; font-weight: 700;
            background: rgba(245,158,11,0.12);
            color: var(--gold-300);
            padding: 6px 12px;
            border-radius: 999px;
            letter-spacing: 0.08em;
            border: 1px solid rgba(245,158,11,0.30);
        }

        .mvp-progress-bar {
            height: 6px;
            background: var(--glass-2);
            border-radius: 999px;
            overflow: hidden;
            margin: 4px 0 14px;
            border: 1px solid var(--bd-1);
        }
        .mvp-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold-500), var(--gold-300));
            border-radius: 999px;
            box-shadow: 0 0 10px rgba(245,158,11,0.5);
            transition: width 0.5s cubic-bezier(0.16,1,0.3,1);
            width: 0%;
        }

        .btn-mvp-star {
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            color: var(--text-low);
            width: 32px; height: 32px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16,1,0.3,1);
            display: grid; place-items: center;
            font-size: 13px;
        }
        .btn-mvp-star:hover {
            border-color: var(--gold-500);
            color: var(--gold-400);
            background: rgba(245,158,11,0.10);
            transform: scale(1.08);
        }
        .btn-mvp-star.active {
            background: linear-gradient(135deg, var(--gold-500), var(--gold-600));
            border-color: var(--gold-400);
            color: white;
            box-shadow: 0 0 18px -4px rgba(245,158,11,0.7), inset 0 1px 0 rgba(255,255,255,0.25);
        }
        .btn-mvp-star:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }

        .mvp-list-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 14px;
            background: rgba(245,158,11,0.06);
            border: 1px solid rgba(245,158,11,0.18);
            border-radius: 12px;
            margin-bottom: 7px;
            font-size: 12.5px;
            color: var(--gold-300);
            font-weight: 600;
            transition: all 0.2s;
        }
        .mvp-list-item:hover { background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.30); }
        .mvp-list-item .mvp-remove {
            background: rgba(239,68,68,0.10);
            border: 1px solid rgba(239,68,68,0.25);
            color: #fca5a5;
            cursor: pointer;
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .mvp-list-item .mvp-remove:hover { background: rgba(239,68,68,0.20); color: #ef4444; }
        .mvp-list-item.locked { opacity: 0.85; }
        .mvp-list-item.locked::after {
            content: '\f023'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            margin-left: 8px; color: var(--gold-500);
        }

        .btn-submit-mvp {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--gold-500), var(--gold-700));
            color: white;
            font-family: inherit;
            font-size: 13px; font-weight: 800;
            letter-spacing: 0.06em;
            cursor: pointer;
            margin-top: 12px;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            transition: all 0.25s;
            box-shadow: 0 8px 22px -8px rgba(245,158,11,0.55), inset 0 1px 0 rgba(255,255,255,0.18);
        }
        .btn-submit-mvp:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -10px rgba(245,158,11,0.65), inset 0 1px 0 rgba(255,255,255,0.18); }
        .btn-submit-mvp:disabled { background: var(--glass-strong); color: var(--text-low); cursor: not-allowed; transform: none; box-shadow: none; }

        /* ====================================================
           MODALS
           ==================================================== */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(2,6,14,0.88);
            z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
            padding: 16px;
            contain: layout style paint;
        }
        .modal-overlay.show { opacity: 1; pointer-events: all; }
        /* Saat modal aktif, hentikan animasi bubble — hemat GPU */
        body:has(.modal-overlay.show) .bubbles span { animation-play-state: paused; }

        .modal-card {
            background: linear-gradient(180deg, var(--ocean-800) 0%, var(--ocean-900) 100%);
            border: 1px solid var(--bd-2);
            border-radius: 24px;
            padding: 36px 32px;
            text-align: center;
            max-width: 440px; width: 100%;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.06);
            transform: translateY(12px);
            opacity: 0;
            transition: transform 0.25s ease, opacity 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .modal-overlay.show .modal-card { transform: translateY(0); opacity: 1; }

        .modal-icon {
            width: 72px; height: 72px;
            border-radius: 22px;
            display: grid; place-items: center;
            margin: 0 auto 20px;
            position: relative; z-index: 1;
        }
        .modal-icon i { font-size: 28px; color: white; }
        .modal-icon.blue {
            background: linear-gradient(135deg, var(--royal-600), var(--cyan-500));
            box-shadow: 0 12px 30px -8px rgba(6,182,212,0.55), inset 0 1px 0 rgba(255,255,255,0.25);
        }
        .modal-icon.green {
            background: linear-gradient(135deg, var(--success), #059669);
            box-shadow: 0 12px 30px -8px rgba(16,185,129,0.55), inset 0 1px 0 rgba(255,255,255,0.25);
        }
        .modal-title {
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-size: 24px;
            color: var(--text-hi);
            margin-bottom: 10px;
            letter-spacing: -0.02em;
            position: relative; z-index: 1;
        }
        .modal-desc { font-size: 13.5px; color: var(--text-mid); margin-bottom: 24px; line-height: 1.6; position: relative; z-index: 1; }
        .modal-form { text-align: left; margin-bottom: 22px; position: relative; z-index: 1; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; position: relative; z-index: 1; }
        .modal-close-btn {
            padding: 12px 24px;
            border: 1px solid var(--bd-2);
            border-radius: 14px;
            background: var(--glass-2);
            color: var(--text);
            font-family: inherit;
            font-size: 13px; font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .modal-close-btn:hover { background: var(--glass-3); border-color: var(--bd-3); }

        /* Admin badge */
        .badge-admin {
            font-size: 9px; font-weight: 700;
            background: rgba(245,158,11,0.10);
            color: var(--gold-300);
            padding: 3px 8px;
            border-radius: 6px;
            margin-left: 4px;
            vertical-align: middle;
            display: inline-flex; align-items: center; gap: 3px;
            border: 1px solid rgba(245,158,11,0.25);
            letter-spacing: 0.04em;
        }

        /* Custom checkbox area inside MVP confirm */
        #modalConfirmMvp .agree-box {
            background: rgba(245,158,11,0.06);
            border: 1px solid rgba(245,158,11,0.20);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 22px;
            display: flex; align-items: center; gap: 12px;
        }
        #modalConfirmMvp .agree-box label {
            font-size: 12.5px; font-weight: 600;
            color: var(--gold-300);
            cursor: pointer;
            line-height: 1.5;
        }
        #modalConfirmMvp .agree-box input[type=checkbox] {
            width: 20px; height: 20px;
            accent-color: var(--gold-500);
            cursor: pointer;
            flex-shrink: 0;
        }

        /* MVP locked / unlocked state inside card */
        #mvpLockedState, #mvpEmptyList {
            text-align: center;
            padding: 28px 16px;
            color: var(--text-mid);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.55;
        }
        #mvpLockedState .lock-icon, #mvpEmptyList .unlock-icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            display: grid; place-items: center;
            margin: 0 auto 12px;
            background: var(--glass-2);
            border: 1px solid var(--bd-2);
            font-size: 22px;
        }
        #mvpLockedState .lock-icon { color: var(--text-low); }
        #mvpEmptyList .unlock-icon {
            color: var(--gold-400);
            background: rgba(245,158,11,0.10);
            border-color: rgba(245,158,11,0.25);
            box-shadow: 0 0 24px -8px rgba(245,158,11,0.5);
        }
        #mvpEmptyList { color: var(--gold-300); }
        #mvpEmptyList .fa-star { color: var(--gold-400) !important; }

        #mvpSubmittedBadge {
            display: none;
            text-align: center;
            margin-top: 14px;
            background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(16,185,129,0.06));
            border: 1px solid rgba(16,185,129,0.30);
            border-radius: 14px;
            padding: 12px;
            color: #6EE7B7;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        #mvpSubmittedBadge i { margin-right: 6px; }

        /* Aquatic separator (decorative wave) */
        .wave-divider {
            height: 1px; width: 100%;
            background: linear-gradient(90deg, transparent, var(--bd-2) 20%, var(--bd-2) 80%, transparent);
            margin: 4px 0;
        }

        /* ====================================================
           RESPONSIVE
           ==================================================== */
        @media (max-width: 1180px) {
            .hero { grid-template-columns: 1fr; }
            .stat-row { grid-template-columns: repeat(4, 1fr); }
        }
        @media (max-width: 1024px) {
            .dash-grid { grid-template-columns: 1fr; }
            .ikan-list { grid-template-columns: 1fr; }
        }

        /* === TABLET / MOBILE — Navbar profile vertical stack di kanan === */
        @media (max-width: 768px) {
            .topnav {
                padding: 12px 16px;
                gap: 10px;
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .brand { gap: 10px; flex: 1; min-width: 0; }
            .brand-mark { width: 40px; height: 40px; border-radius: 12px; }
            .brand-mark svg { width: 22px; height: 22px; }
            .brand-text h1 { font-size: 16px; }
            .brand-text p { font-size: 9.5px; letter-spacing: 0.05em; }

            /* Profile + Logout = vertical stack di kanan (TIDAK turun ke bawah) */
            .nav-user {
                flex-direction: column;
                align-items: flex-end;
                gap: 7px;
                flex-shrink: 0;
            }
            .user-card {
                padding: 5px 12px 5px 5px;
                gap: 9px;
                min-width: 0;
                max-width: 200px;
            }
            .avatar {
                width: 30px; height: 30px;
                font-size: 12px;
                border-radius: 50%;
            }
            /* Nama & keterangan TETAP TAMPIL di mobile */
            .user-info { display: block; text-align: left; line-height: 1.15; min-width: 0; }
            .user-info h4 {
                font-size: 11.5px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 110px;
            }
            .user-info span { font-size: 9px; letter-spacing: 0.05em; }
            .btn-logout {
                padding: 6px 12px;
                font-size: 10.5px;
                width: 100%;
                justify-content: center;
            }

            /* Pills "Kontes Aktif" + "Sistem Online" turun ke bawah, full-width row */
            .nav-center {
                order: 3;
                flex-basis: 100%;
                justify-content: flex-start;
                gap: 8px;
                margin-top: 2px;
            }
            .nav-pill { padding: 5px 10px; font-size: 10.5px; }

            /* Padding & sizing umum */
            .main-wrap { padding: 18px 16px; }
            .stat-row { grid-template-columns: 1fr 1fr; gap: 10px; }
            .hero-welcome { padding: 22px 22px; }
            .hero-h2 { font-size: 26px; }
            .hero-sub { font-size: 12.5px; }
            .number-display { font-size: 64px; }
            .card-header { padding: 20px 20px 0; }
            .card-body { padding: 18px 20px 22px; }
            .ikan-list-wrapper { padding: 4px 20px 22px; }
            .fish-toolbar { padding: 0 20px 14px; flex-direction: column; align-items: stretch; }
            .fish-search { width: 100%; }
            .fish-filter { width: 100%; }
            .stepper { padding: 10px 14px; gap: 0; }
            .step-label { font-size: 10.5px; }
            .step-arrow { min-width: 8px; margin: 0 6px; }
            .step-num { width: 26px; height: 26px; font-size: 11px; }

            /* MATIKAN animasi background berat di mobile - hemat baterai & GPU */
            .bubbles { display: none; }
        }

        @media (max-width: 480px) {
            .topnav { padding: 10px 14px; }
            .brand-text h1 { font-size: 15px; }
            .brand-text p { display: none; }
            .stat-row { grid-template-columns: 1fr; }
            .modal-card { padding: 26px 20px; }
            .number-display { font-size: 52px; }
            .machine-body { padding: 20px 16px; }
            .lcd-screen { padding: 20px 14px; }
            .main-wrap { padding: 14px 12px; }
            .hero-h2 { font-size: 22px; }
            .user-info h4 { max-width: 90px; font-size: 11px; }
            .user-info span { font-size: 8.5px; }
            .nav-center { display: none; } /* di layar sangat sempit, sembunyikan status pill */
            /* Compact stepper di layar sempit */
            .step-label { display: none; }
            .stepper-item { flex: 0 1 auto; }
            .step-arrow { flex: 1; }
        }

                /* ====================================================
           UNDIAN LOCK OVERLAY — GEMBOK VISUAL
           ==================================================== */
        .undian-lock-overlay {
            position: absolute;
            inset: 0;
            z-index: 50;
            background: rgba(4, 7, 15, 0.90);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
            padding: 32px 20px;
        }
        .undian-lock-overlay.show {
            opacity: 1;
            pointer-events: all;
        }
        .undian-lock-overlay .lock-visual {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.18), rgba(239, 68, 68, 0.06));
            border: 2px solid rgba(239, 68, 68, 0.45);
            display: grid;
            place-items: center;
            animation: lockPulse 2.5s ease-in-out infinite;
            box-shadow: 0 0 35px rgba(239, 68, 68, 0.25);
        }
        .undian-lock-overlay .lock-visual i {
            font-size: 34px;
            color: #FCA5A5;
            filter: drop-shadow(0 0 12px rgba(239, 68, 68, 0.5));
        }
        .undian-lock-overlay .lock-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #FCA5A5;
            letter-spacing: 0.02em;
            text-align: center;
        }
        .undian-lock-overlay .lock-desc {
            font-size: 12px;
            color: var(--text-mid);
            text-align: center;
            max-width: 300px;
            line-height: 1.6;
        }
        .undian-lock-overlay .lock-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.30);
            font-size: 11px;
            font-weight: 700;
            color: #FCA5A5;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .undian-lock-overlay .lock-badge i {
            font-size: 10px;
        }
        @keyframes lockPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 35px rgba(239, 68, 68, 0.25); }
            50% { transform: scale(1.06); box-shadow: 0 0 55px rgba(239, 68, 68, 0.40); }
        }
        @media (max-width: 768px) {
            .undian-lock-overlay .lock-visual { width: 68px; height: 68px; }
            .undian-lock-overlay .lock-visual i { font-size: 28px; }
            .undian-lock-overlay .lock-title { font-size: 14px; }
            .undian-lock-overlay .lock-desc { font-size: 11px; max-width: 240px; }
        }

        /* Reduced motion + Low-end device fallback */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.1s !important; }
            .bubbles { display: none; }
        }
    </style>
</head>
<body>

    <!-- ATMOSPHERIC BACKGROUND -->
    <div class="ocean-bg"></div>
    <div class="bubbles" aria-hidden="true">
        <span></span><span></span><span></span><span></span><span></span><span></span>
    </div>

    <div class="app-shell">

        <!-- ==================== TOP NAVIGATION ==================== -->
        <nav class="topnav">
            <div class="brand">
                <div class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 12c0-3 4-7 10-7 5 0 7.5 3 8.5 5l1.5-1v6l-1.5-1c-1 2-3.5 5-8.5 5-6 0-10-4-10-7z" stroke="white" stroke-width="1.5" stroke-linejoin="round" fill="rgba(255,255,255,0.15)"/>
                        <circle cx="16" cy="10.5" r="1" fill="white"/>
                        <path d="M2 12c1.5-1 3-1 4 0M2 12c1.5 1 3 1 4 0" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <h1>LCI <em>Suite</em></h1>
                    <p>Louhan Club Indonesia</p>
                </div>
            </div>

            <div class="nav-center">
                <div class="nav-pill"><span class="live-dot"></span>Kontes Aktif</div>
                <div class="nav-pill" style="color: var(--cyan-300);"><i class="fas fa-water" style="font-size:10px;"></i>Sistem Online</div>
            </div>

            <div class="nav-user">
                <div class="user-card">
                    <div class="avatar">{{ $initial ?: 'P' }}</div>
                    <div class="user-info">
                        <h4>{{ $user->name }}</h4>
                        <span>Peserta Kontes</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout"><i class="fas fa-right-from-bracket"></i> Keluar</button>
                </form>
            </div>
        </nav>

        <main class="main-wrap">

            <!-- ==================== HERO + STATS ==================== -->
            <section class="hero">
                <div class="hero-welcome">
                    <span class="hero-eyebrow"><i class="fas fa-sparkles" style="font-size:9px;"></i> Dashboard Peserta</span>
                    <h2 class="hero-h2">Selamat datang, <em>{{ explode(' ', trim($user->name))[0] }}</em></h2>
                    <p class="hero-sub">Pantau status ikan kontes Anda, lakukan undian nomor tank, dan daftarkan ikan-ikan terbaik Anda ke kompetisi <strong style="color:var(--gold-300);">MVP Louhan Club Indonesia</strong>.</p>
                </div>

                <div class="stat-row">
                    <div class="stat-card {{ $profilLengkap ? 'is-success' : '' }}">
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                        <div class="stat-label">Profil</div>
                        <div class="stat-value">
                            {{ $profilLengkap ? 'Lengkap' : '—' }}
                        </div>
                        <div class="stat-meta">
                            <i class="fas {{ $profilLengkap ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
                            {{ $profilLengkap ? 'Terverifikasi' : 'Lengkapi data Anda' }}
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-fish"></i></div>
                        <div class="stat-label">Total Ikan</div>
                        <div class="stat-value">{{ $totalIkan }}<span class="stat-sub">ekor</span></div>
                        <div class="stat-meta"><i class="fas fa-arrow-trend-up"></i>Terdaftar di kontes</div>
                    </div>

                    <div class="stat-card is-success">
                        <div class="stat-icon"><i class="fas fa-dice"></i></div>
                        <div class="stat-label">Sudah Diundi</div>
                        <div class="stat-value">{{ $totalDiundi }}<span class="stat-sub">/ {{ $totalIkan }}</span></div>
                        <div class="stat-meta"><i class="fas fa-bolt"></i>{{ $progressUndian }}% selesai</div>
                    </div>

                    <div class="stat-card is-gold">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-label">MVP Terdaftar</div>
                        <div class="stat-value">{{ $mvpCount }}<span class="stat-sub">/ 30</span></div>
                        <div class="stat-meta" id="heroMvpStatus"><i class="fas fa-crown"></i>Status MVP</div>
                    </div>
                </div>
            </section>

            <!-- ==================== WORKFLOW STEPPER ==================== -->
            <section class="stepper" aria-label="Alur pendaftaran">
                <div class="stepper-item {{ $profilLengkap ? 'is-done' : 'is-active' }}">
                    <div class="step-num">{!! $profilLengkap ? '<i class="fas fa-check" style="font-size:10px;"></i>' : '1' !!}</div>
                    <div class="step-label">Profil</div>
                </div>
                <div class="step-arrow"></div>
                <div class="stepper-item {{ $profilLengkap && $totalIkan > 0 ? 'is-done' : ($profilLengkap ? 'is-active' : '') }}">
                    <div class="step-num">{!! $profilLengkap && $totalIkan > 0 ? '<i class="fas fa-check" style="font-size:10px;"></i>' : '2' !!}</div>
                    <div class="step-label">Daftar Ikan</div>
                </div>
                <div class="step-arrow"></div>
                <div class="stepper-item {{ $totalIkan > 0 && $totalDiundi === $totalIkan ? 'is-done' : ($totalIkan > 0 ? 'is-active' : '') }}">
                    <div class="step-num">{!! $totalIkan > 0 && $totalDiundi === $totalIkan ? '<i class="fas fa-check" style="font-size:10px;"></i>' : '3' !!}</div>
                    <div class="step-label">Undian Tank</div>
                </div>
                <div class="step-arrow"></div>
                <div class="stepper-item {{ $mvpCount > 0 ? 'is-active' : '' }}">
                    <div class="step-num">4</div>
                    <div class="step-label">Pilih MVP</div>
                </div>
            </section>

            <!-- ==================== DASHBOARD GRID ==================== -->
            <div class="dash-grid">

                <!-- ========== KOLOM KIRI: PROFIL + MVP ========== -->
                <div class="col-stack">

                    <!-- CARD: PROFIL PESERTA -->
                    <div class="glass-card">
                        <div class="card-header">
                            <div>
                                <h2 class="card-title">
                                    <span class="title-icon"><i class="fas fa-user-circle"></i></span>
                                    Profil Peserta
                                </h2>
                                <p class="card-subtitle">Lengkapi data diri Anda terlebih dahulu untuk mengakses fitur kontes.</p>
                            </div>
                            @if($profilLengkap)
                                <span class="status-badge success" style="margin-top: 4px;">Terverifikasi</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <form id="formIkan">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Nama Peserta</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="nama_peserta" id="namaPeserta" class="form-input" value="{{ $user->name }}">
                                        <i class="fas fa-user input-icon" style="font-size:12px;"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Jenis Keanggotaan</label>
                                    <div class="toggle-group">
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="perorangan" value="perorangan" {{ !$pesertaSaya || $pesertaSaya->jenis_keanggotaan == 'perorangan' ? 'checked' : '' }}>
                                            <label for="perorangan"><i class="fas fa-user" style="margin-right:5px"></i>Perorangan</label>
                                        </div>
                                        <div class="toggle-option">
                                            <input type="radio" name="jenis_keanggotaan" id="team" value="team" {{ $pesertaSaya && $pesertaSaya->jenis_keanggotaan == 'team' ? 'checked' : '' }}>
                                            <label for="team"><i class="fas fa-users" style="margin-right:5px"></i>Team / Club</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" id="labelDetail">{{ $pesertaSaya && $pesertaSaya->jenis_keanggotaan == 'team' ? 'Nama Team / Club' : 'Kota Asal' }}</label>
                                    <div class="input-wrapper">
                                        <input type="text" name="detail_anggota" id="inputDetail" class="form-input" placeholder="Contoh: Jakarta" value="{{ $pesertaSaya->detail_anggota ?? '' }}" required>
                                        <i class="fas {{ $pesertaSaya && $pesertaSaya->jenis_keanggotaan == 'team' ? 'fa-shield-halved' : 'fa-city' }} input-icon" id="iconDetail"></i>
                                    </div>
                                    <div class="input-error-msg" id="errDetail"></div>
                                </div>

                                <!-- FORM TAMBAH IKAN (GABUNG) -->
                                <div id="inlineFormIkan" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--bd-2);">
                                    <h3 style="font-size:15px; font-weight:800; color:var(--text-hi); margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                                        <span style="width:30px; height:30px; border-radius:10px; display:grid; place-items:center; background:rgba(34,211,238,0.10); border:1px solid rgba(34,211,238,0.20); color:var(--cyan-400); font-size:13px;"><i class="fas fa-fish"></i></span>
                                        Masukkan Data Ikan
                                    </h3>
                                    <div class="form-group" style="margin-bottom:14px;">
                                        <label class="form-label">Kategori</label>
                                        <div class="input-wrapper">
                                            <select name="kategori" id="ikanKategoriSelect" class="form-select" required style="padding-left:14px;">
                                                <option value="" disabled selected>Pilih Kategori Ikan</option>
                                                <option value="Cencu">Cencu</option>
                                                <option value="Chginwa">Chginwa</option>
                                                <option value="Freemarking">Freemarking</option>
                                                <option value="Goldenbase">Goldenbase</option>
                                                <option value="Klasik">Klasik</option>
                                                <option value="Bonsai">Bonsai</option>
                                                <option value="Jumbo">Jumbo</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group" id="ikanKelasWrap" style="margin-bottom:0;">
                                        <label class="form-label">Kelas</label>
                                        <div class="input-wrapper">
                                            <select name="kelas" id="ikanKelasSelect" class="form-select" style="padding-left:14px;">
                                                <option value="" disabled selected>Pilih Kelas</option>
                                                <option value="A">Kelas A</option>
                                                <option value="B">Kelas B</option>
                                                <option value="C">Kelas C</option>
                                                <option value="D">Kelas D</option>
                                                <option value="E">Kelas E</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:10px; margin-top:18px;">
                                        <button type="button" class="modal-close-btn" style="flex:1; padding:12px 14px;" onclick="resetIkanFields()">Reset</button>
                                        <button type="submit" class="submit-btn" style="flex:1; margin-top:0; font-size:13px;"><i class="fas fa-save" style="margin-right:8px;"></i>SIMPAN</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- CARD: MVP REGISTRATION -->
                    <div class="glass-card mvp-card" id="mvpCard">
                        <div class="card-header">
                            <div>
                                <h2 class="card-title">
                                    <span class="title-icon"><i class="fas fa-star"></i></span>
                                    Pendaftaran MVP
                                </h2>
                                <p class="card-subtitle">Pilih maksimal 30 ikan terbaik Anda untuk kompetisi MVP.</p>
                            </div>
                            <div class="mvp-badge" id="mvpCountBadge">{{ $mvpCount }}/30 MVP</div>
                        </div>
                        <div class="card-body" id="mvpCardBody" style="padding-top:12px;">

                            <div class="mvp-progress-bar" aria-hidden="true">
                                <div class="mvp-progress-fill" id="mvpProgressFill" style="width: {{ min(100, ($mvpCount / 30) * 100) }}%"></div>
                            </div>

                            <div id="mvpLockedState">
                                <div class="lock-icon"><i class="fas fa-lock"></i></div>
                                <strong style="color:var(--text);">Pendaftaran MVP Belum Dibuka</strong><br>
                                <span style="font-size:12px;">Tunggu panitia membuka periode pendaftaran MVP.</span>
                            </div>

                            <div id="mvpUnlockedState" style="display:none;">
                                <div id="mvpListContainer" style="max-height:240px; overflow-y:auto; margin-bottom:8px; padding-right:4px;"></div>

                                <div id="mvpEmptyList">
                                    <div class="unlock-icon"><i class="fas fa-lock-open"></i></div>
                                    <strong style="color:var(--gold-300);">Pendaftaran MVP DIBUKA!</strong><br>
                                    <span style="font-size:12px;">Klik <i class="fas fa-star" style="color:var(--gold-400);"></i> pada ikan Anda di kolom kanan.</span>
                                </div>

                                <button class="btn-submit-mvp" id="btnSubmitMvp" onclick="confirmSubmitMvp()" style="display:none;">
                                    <i class="fas fa-paper-plane"></i> KIRIM IKAN MVP
                                </button>

                                <div id="mvpSubmittedBadge">
                                    <i class="fas fa-circle-check"></i> Data MVP telah dikirim & terkunci permanen
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- ========== END KOLOM KIRI ========== -->

                <!-- ========== KOLOM KANAN: MESIN + DAFTAR IKAN ========== -->
                <div class="col-stack">

                    <!-- CARD: MESIN UNDIAN -->
                    <div class="glass-card machine-card" id="cardMesinUndian">
                        <div class="undian-lock-overlay {{ !$undianOpen ? 'show' : '' }}" id="lockMesinUndian">
                            <div class="lock-visual"><i class="fas fa-lock"></i></div>
                            <div class="lock-title">Mesin Undian Dikunci</div>
                            <div class="lock-desc">Pengundian nomor tank saat ini belum dibuka oleh panitia. Anda tetap bisa mendaftarkan ikan, namun belum bisa melakukan undian.</div>
                            <div class="lock-badge"><i class="fas fa-hourglass-half"></i> Menunggu Panitia Membuka</div>
                        </div>
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="title-icon"><i class="fas fa-dice"></i></span>
                                Mesin Undian Tank
                            </h2>
                            <span class="status-badge"><span style="font-family:'JetBrains Mono',monospace;">DIGITAL DRAW</span></span>
                        </div>
                        <div class="machine-body">
                            <div class="lcd-screen">
                                <div class="lcd-label">Nomor Aquarium</div>
                                <div class="number-display" id="numberDisplay">--</div>
                                <div class="lcd-info-text" id="lcdInfo">Klik ACAK pada daftar ikan</div>
                            </div>

                            <div id="resetBanner">
                                <i class="fas fa-triangle-exclamation"></i>
                                <span id="resetBannerText"></span>
                            </div>

                            <div style="display:flex; gap:14px; width:100%; padding: 4px 4px 0;">
                                <div style="flex:1; text-align:center; padding:14px 10px; background:var(--glass-2); border:1px solid var(--bd-2); border-radius:14px;">
                                    <div style="font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:800; color:var(--cyan-300); text-shadow:0 0 12px rgba(34,211,238,0.4);">{{ $totalDiundi }}</div>
                                    <div style="font-size:10px; font-weight:700; color:var(--text-mid); letter-spacing:0.14em; text-transform:uppercase; margin-top:4px;">Diundi</div>
                                </div>
                                <div style="flex:1; text-align:center; padding:14px 10px; background:var(--glass-2); border:1px solid var(--bd-2); border-radius:14px;">
                                    <div style="font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:800; color:var(--gold-300); text-shadow:0 0 12px rgba(245,158,11,0.4);">{{ $totalBelumDiundi }}</div>
                                    <div style="font-size:10px; font-weight:700; color:var(--text-mid); letter-spacing:0.14em; text-transform:uppercase; margin-top:4px;">Tersisa</div>
                                </div>
                                <div style="flex:1; text-align:center; padding:14px 10px; background:var(--glass-2); border:1px solid var(--bd-2); border-radius:14px;">
                                    <div style="font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:800; color:#6EE7B7; text-shadow:0 0 12px rgba(16,185,129,0.4);">{{ $progressUndian }}<span style="font-size:14px;">%</span></div>
                                    <div style="font-size:10px; font-weight:700; color:var(--text-mid); letter-spacing:0.14em; text-transform:uppercase; margin-top:4px;">Selesai</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD: DAFTAR IKAN -->
                    <div class="glass-card machine-card" id="cardDaftarIkan">
                        <div class="undian-lock-overlay {{ !$undianOpen ? 'show' : '' }}" id="lockDaftarIkan">
                            <div class="lock-visual"><i class="fas fa-lock"></i></div>
                            <div class="lock-title">Undian Dikunci</div>
                            <div class="lock-desc">Daftar ikan Anda sudah tercatat. Nomor tank akan bisa diundi setelah panitia membuka mesin undian.</div>
                            <div class="lock-badge"><i class="fas fa-hourglass-half"></i> Menunggu Pembukaan Undian</div>
                        </div>
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="title-icon"><i class="fas fa-list"></i></span>
                                Daftar Ikan Saya
                            </h2>
                            <div class="status-badge {{ $totalIkan > 0 ? 'success' : '' }}">
                                {{ $totalIkan > 0 ? $totalDiundi . '/' . $totalIkan . ' DIUNDI' : 'MENUNGGU IKAN' }}
                            </div>
                        </div>

                        <div class="fish-toolbar" style="padding-top:18px;">
                            <div class="fish-search">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" id="fishSearchInput" placeholder="Cari kategori atau kelas ikan...">
                            </div>
                            <select class="fish-filter" id="fishFilterKategori" aria-label="Filter kategori">
                                <option value="">Semua Kategori</option>
                                <option value="Cencu">Cencu</option>
                                <option value="Chginwa">Chginwa</option>
                                <option value="Freemarking">Freemarking</option>
                                <option value="Goldenbase">Goldenbase</option>
                                <option value="Klasik">Klasik</option>
                                <option value="Bonsai">Bonsai</option>
                                <option value="Jumbo">Jumbo</option>
                            </select>
                            <select class="fish-filter" id="fishFilterStatus" aria-label="Filter status">
                                <option value="">Semua Status</option>
                                <option value="diundi">Sudah Diundi</option>
                                <option value="belum">Belum Diundi</option>
                            </select>
                        </div>

                        <div class="ikan-list-wrapper" id="ikanListWrapper">
                            @if($ikansSaya->count() > 0)
                                <div class="ikan-list" id="ikanListContainer">
                                    @foreach($ikansSaya as $index => $ikan)
                                        <div class="ikan-item" id="ikan-item-{{ $ikan->id }}">
                                            <div class="ikan-item-info">
                                                <h4><i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>{{ $ikan->nama_peserta ?? $user->name }}@if($ikan->dibuat_oleh === 'admin')<span class="badge-admin"><i class="fas fa-shield-halved"></i> Admin</span>@endif</h4>
                                                @php
                                                    $katKelasText = $ikan->kategori;
                                                    if ($ikan->kelas && !in_array($ikan->kategori, ['Bonsai', 'Jumbo'])) {
                                                        $katKelasText = $ikan->kategori . ' - Kelas ' . $ikan->kelas;
                                                    }
                                                @endphp
                                                <p>{{ $katKelasText }}</p>
                                            </div>
                                            <div class="ikan-item-right">
                                                <div class="tank-num {{ $ikan->nomor_tank ? 'filled' : 'empty' }}" id="tank-num-{{ $ikan->id }}">
                                                    {{ $ikan->nomor_tank ?? '--' }}
                                                </div>
                                                @if(!$ikan->nomor_tank)
                                                    @if($undianOpen)
                                                        <button class="btn-acak-kecil" onclick="mulaiAcak({{ $ikan->id }}, this)">
                                                            <i class="fas fa-shuffle"></i> ACAK
                                                        </button>
                                                    @else
                                                        <button class="btn-acak-kecil" disabled style="opacity:0.4; cursor:not-allowed;">
                                                            <i class="fas fa-lock"></i> DIKUNCI
                                                        </button>
                                                    @endif
                                                @else
                                                    <span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="ikan-empty-state">
                                    <div class="empty-icon"><i class="fas fa-fish"></i></div>
                                    <strong style="color:var(--text); display:block; margin-bottom:4px;">Belum ada ikan terdaftar</strong>
                                    Isi form <em style="color:var(--cyan-300); font-style:normal; font-weight:700;">"Masukkan Data Ikan"</em> di kolom kiri untuk menambahkan ikan kontes Anda.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                <!-- ========== END KOLOM KANAN ========== -->

            </div>
        </main>
    </div>

    <!-- ==================== MODAL: SUKSES ==================== -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-card">
            <div class="modal-icon green"><i class="fas fa-check"></i></div>
            <h2 class="modal-title" id="successModalTitle">Berhasil Disimpan!</h2>
            <p class="modal-desc" id="successModalDesc">Profil Anda sudah diperbarui. Sekarang silakan masukkan data ikan yang akan dilombakan.</p>
            <button class="modal-close-btn" onclick="document.getElementById('successModal').classList.remove('show')">Mengerti</button>
        </div>
    </div>

    <!-- ==================== MODAL: KONFIRMASI MVP ==================== -->
    <div class="modal-overlay" id="modalConfirmMvp">
        <div class="modal-card">
            <div class="modal-icon" style="background:linear-gradient(135deg,var(--gold-500),var(--gold-700)); box-shadow:0 12px 30px -8px rgba(245,158,11,.55), inset 0 1px 0 rgba(255,255,255,0.25);">
                <i class="fas fa-paper-plane"></i>
            </div>
            <h2 class="modal-title">Kirim Data MVP?</h2>
            <p class="modal-desc">Pastikan pilihan Anda sudah benar. Ikan yang terdaftar sebagai MVP <b style="color:var(--gold-300);">TIDAK DAPAT DIUBAH ATAU DIHAPUS</b> setelah dikirim.</p>
            <div class="agree-box">
                <input type="checkbox" id="mvpAgree">
                <label for="mvpAgree">Saya mengerti dan menyetujui bahwa data tidak dapat diubah setelah dikirim.</label>
            </div>
            <div class="modal-actions">
                <button class="modal-close-btn" onclick="document.getElementById('modalConfirmMvp').classList.remove('show')">Batal</button>
                <button class="btn-submit-mvp" id="btnConfirmSubmitMvp" onclick="submitMvpIkan()" disabled style="width:auto; margin-top:0; padding: 13px 24px;">
                    <i class="fas fa-paper-plane"></i> Ya, Kirim MVP
                </button>
            </div>
        </div>
    </div>

    <script>
        /* ============================================================
           BUSINESS LOGIC — TIDAK DIUBAH dari versi asli.
           Hanya ditambah enhancement untuk:
           - Update progress bar MVP visual
           - Search/filter ikan (pure frontend, tidak ubah backend)
           ============================================================ */

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
            var tg = document.querySelector('.toggle-group');
            if (tg) { tg.style.opacity = '0.55'; tg.style.pointerEvents = 'none'; }
            var inp = document.getElementById('inputDetail');
            inp.readOnly = true;
            var btn = document.getElementById('submitBtn');
            if (btn) btn.style.display = 'none';
        }



        // ★ HELPER: Reset hanya field ikan (profil tetap terisi untuk tambah ikan berikutnya)
        function resetIkanFields() {
            if (ikanKategoriSelect) ikanKategoriSelect.value = '';
            if (ikanKelasSelectEl) ikanKelasSelectEl.value = '';
            resetIkanFormState();
        }

        const formIkan = document.getElementById('formIkan');
        formIkan.addEventListener('submit', function(e) {
            e.preventDefault();

            // ★ VALIDASI PROFIL
            var namaPeserta = document.getElementById('namaPeserta').value.trim();
            var jenisKeanggotaan = document.querySelector('input[name="jenis_keanggotaan"]:checked');
            var detailAnggota = document.getElementById('inputDetail').value.trim();

            if(!namaPeserta){ alert('Isi Nama Peserta terlebih dahulu.'); return; }
            if(!detailAnggota){ alert(document.getElementById('labelDetail').textContent + ' wajib diisi.'); return; }

            // ★ VALIDASI IKAN
            var selectedKategori = ikanKategoriSelect ? ikanKategoriSelect.value : '';
            if(!selectedKategori){ alert('Pilih kategori ikan terlebih dahulu.'); return; }
            var selectedKelas = ikanKelasSelectEl ? ikanKelasSelectEl.value : '';
            if(noKelasKategori.indexOf(selectedKategori) === -1 && !selectedKelas){ alert('Pilih kelas ikan terlebih dahulu.'); return; }

            const btnSubmit = formIkan.querySelector('.submit-btn');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // ★ STEP 1: SIMPAN PROFIL DULU
            const profilFormData = new FormData();
            profilFormData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            profilFormData.append('nama_peserta', namaPeserta);
            profilFormData.append('jenis_keanggotaan', jenisKeanggotaan ? jenisKeanggotaan.value : 'perorangan');
            profilFormData.append('detail_anggota', detailAnggota);

            apiFetch('{{ route("store.registrasi") }}', { method: 'POST', body: profilFormData })
            .then(res => { if (!res.ok) return res.json().then(data => { throw data; }); return res.json(); })
            .then(profilRes => {
                if (!profilRes.success) throw new Error('Profil gagal disimpan');

                // ★ STEP 2: SIMPAN IKAN
                const ikanFormData = new FormData();
                ikanFormData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                ikanFormData.append('kategori', selectedKategori);
                if (noKelasKategori.indexOf(selectedKategori) === -1) {
                    ikanFormData.append('kelas', selectedKelas);
                }

                return apiFetch('{{ route("store.ikan") }}', { method: 'POST', body: ikanFormData })
                .then(res => { if (!res.ok) return res.json().then(data => { throw data; }); return res.json(); });
            })
            .then(data => {
                if (data.success) {
                    // Reset HANYA field ikan, profil tetap terisi untuk tambah ikan berikutnya
                    resetIkanFields();

                    let listContainer = document.getElementById('ikanListContainer');
                    let emptyState = document.querySelector('.ikan-empty-state');
                    if (emptyState) emptyState.remove();
                    if (!listContainer) {
                        listContainer = document.createElement('div');
                        listContainer.className = 'ikan-list';
                        listContainer.id = 'ikanListContainer';
                        document.getElementById('ikanListWrapper').appendChild(listContainer);
                    }
                    const newEl = document.createElement('div');
                    newEl.className = 'ikan-item';
                    newEl.id = `ikan-item-${data.ikan.id}`;
                    newEl.innerHTML = `<div class="ikan-item-info"><h4><i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>${data.ikan.nama_peserta || namaPeserta}</h4>${kategoriKelasLineHtml(data.ikan.kategori, data.ikan.kelas)}</div><div class="ikan-item-right"><div class="tank-num empty" id="tank-num-${data.ikan.id}">--</div><button class="btn-acak-kecil" onclick="mulaiAcak(${data.ikan.id}, this)"><i class="fas fa-shuffle"></i> ACAK</button></div>`;
                    listContainer.prepend(newEl);
                    currentIkans[data.ikan.id] = { kategori: data.ikan.kelas ? 'Kelas ' + data.ikan.kelas : '', nomor_tank: '--', is_mvp: false };

                    // Tampilkan modal sukses
                    document.getElementById('successModalTitle').textContent = 'Berhasil Disimpan!';
                    document.getElementById('successModalDesc').innerHTML = 'Profil dan data ikan <strong>' + formatKategoriKelas(data.ikan.kategori, data.ikan.kelas) + '</strong> berhasil disimpan.';
                    document.getElementById('successModal').classList.add('show');
                }
            })
            .catch(err => {
                if (err.errors) { 
                    let msg = ''; 
                    Object.values(err.errors).forEach(function(e) { msg += e[0] + '\n'; }); 
                    alert(msg); 
                } else { 
                    alert(err.message || 'Gagal menyimpan data.'); 
                }
            })
            .finally(() => { 
                btnSubmit.disabled = false; 
                btnSubmit.innerHTML = '<i class="fas fa-save" style="margin-right:8px;"></i>SIMPAN'; 
            });
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
        let isUndianOpen = {{ $undianOpen ? 'true' : 'false' }}; // ★ Dari server, tanpa delay

        // ★ FUNGSI: Update visual lock pada card Mesin Undian & Daftar Ikan
        function updateUndianLockUI(isOpen) {
            var lockMesin = document.getElementById('lockMesinUndian');
            var lockDaftar = document.getElementById('lockDaftarIkan');

            if (lockMesin) {
                if (isOpen) { lockMesin.classList.remove('show'); }
                else { lockMesin.classList.add('show'); }
            }
            if (lockDaftar) {
                if (isOpen) { lockDaftar.classList.remove('show'); }
                else { lockDaftar.classList.add('show'); }
            }

            // ★ Disable/enable semua tombol ACAK di daftar ikan
            document.querySelectorAll('.btn-acak-kecil').forEach(function(btn) {
                if (!isOpen) {
                    btn.disabled = true;
                    btn.style.opacity = '0.4';
                    btn.style.cursor = 'not-allowed';
                } else {
                    btn.disabled = false;
                    btn.style.opacity = '';
                    btn.style.cursor = '';
                }
            });
        }
        let pollingInterval = null;
        let auth401Count = 0;
        const MAX_401_RETRY = 5;

        document.querySelectorAll('.ikan-item').forEach(el => {
            const id = el.id.replace('ikan-item-', '');
            const pEl = el.querySelector('.ikan-item-info p');
            const tankEl = el.querySelector('.tank-num');
            if (id && tankEl) {
                currentIkans[id] = { kategori: pEl ? pEl.textContent.trim() : '', nomor_tank: tankEl.textContent.trim(), is_mvp: false };
            }
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
                auth401Count = 0;
                if (r.status === 419) {
                    console.warn('CSRF token mismatch - reloading page');
                    window.location.reload();
                    return null;
                }
                if (!r.ok) { throw new Error('Server error: ' + r.status); }
                return r.json();
            })
            .then(function(response) {
                if (!response) return;

                const data = response.ikans || [];
                const resetInfo = response.reset_info;
                const mvpOpen = response.mvp_open || false;
                const mvpSubmitted = response.mvp_submitted || false;

                if (response.tank_range_max) tankDrawMax = response.tank_range_max;

                // ★ UPDATE STATUS MESIN UNDIAN
                const undianOpen = response.undian_open ?? true;
                if (undianOpen !== isUndianOpen) {
                    isUndianOpen = undianOpen;
                    updateUndianLockUI(isUndianOpen);
                }

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
                    listContainer = document.createElement('div');
                    listContainer.className = 'ikan-list';
                    listContainer.id = 'ikanListContainer';
                    document.getElementById('ikanListWrapper').appendChild(listContainer);
                }

                data.forEach(ikan => {
                    if(ikan.is_mvp) {
                        mvpCount++;
                        const removeBtn = currentMvpSubmitted ? '' : `<button class="mvp-remove" onclick="toggleMvp(${ikan.id}, document.querySelector('#ikan-item-${ikan.id} .btn-mvp-star'))"><i class="fas fa-xmark"></i></button>`;
                        mvpListHtml += `<div class="mvp-list-item ${currentMvpSubmitted ? 'locked' : ''}">
                            <span><i class="fas fa-star" style="color:var(--gold-400); margin-right:6px;"></i>${formatKategoriKelas(ikan.kategori, ikan.kelas)} (Tank ${ikan.nomor_tank ?? '--'})</span>
                            ${removeBtn}
                        </div>`;
                    }

                    let existingEl = document.getElementById(`ikan-item-${ikan.id}`);
                    if (!existingEl) {
                        if (!listContainer) return;
                        const badge = ikan.dibuat_oleh === 'admin' ? '<span class="badge-admin"><i class="fas fa-shield-halved"></i> Admin</span>' : '';
                        const mvpBtnHtml = (isMvpOpen && !currentMvpSubmitted) ? `<button class="btn-mvp-star ${ikan.is_mvp ? 'active' : ''}" onclick="toggleMvp(${ikan.id}, this)" title="Daftarkan MVP"><i class="fas fa-star"></i></button>` : (currentMvpSubmitted && ikan.is_mvp ? `<button class="btn-mvp-star active" disabled style="opacity:0.5; cursor:not-allowed;"><i class="fas fa-star"></i></button>` : '');
                        const newEl = document.createElement('div');
                        newEl.className = 'ikan-item';
                        newEl.id = `ikan-item-${ikan.id}`;
                        newEl.style.animation = 'cardEntry 0.5s ease both';
                        const acakBtnHtml = !ikan.nomor_tank ? (isUndianOpen ? `<button class="btn-acak-kecil" onclick="mulaiAcak(${ikan.id}, this)"><i class="fas fa-shuffle"></i> ACAK</button>` : `<button class="btn-acak-kecil" disabled style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-lock"></i> DIKUNCI</button>`) : `<span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>`;
                        newEl.innerHTML = `<div class="ikan-item-info"><h4><i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>${ikan.nama_peserta || document.getElementById('namaPeserta').value} ${badge}</h4>${kategoriKelasLineHtml(ikan.kategori, ikan.kelas)}</div><div class="ikan-item-right">${mvpBtnHtml}<div class="tank-num ${ikan.nomor_tank ? 'filled' : 'empty'}" id="tank-num-${ikan.id}">${ikan.nomor_tank ?? '--'}</div>${acakBtnHtml}</div>`;
                        listContainer.prepend(newEl);
                        currentIkans[ikan.id] = { kategori: ikan.kelas ? 'Kelas ' + ikan.kelas : '', nomor_tank: ikan.nomor_tank ?? '--', is_mvp: ikan.is_mvp };
                    } else {
                        if (!currentIkans[ikan.id]) currentIkans[ikan.id] = { kategori: '', nomor_tank: '--', is_mvp: false };
                        const currentTank = ikan.nomor_tank ?? '--';

                        var infoDiv = existingEl.querySelector('.ikan-item-info');
                        var existingH4 = infoDiv ? infoDiv.querySelector('h4') : null;
                        var existingP = infoDiv ? infoDiv.querySelector('p') : null;

                        if (existingH4) {
                            var badgeHtml = ikan.dibuat_oleh === 'admin' ? ' <span class="badge-admin"><i class="fas fa-shield-halved"></i> Admin</span>' : '';
                            existingH4.innerHTML = '<i class="fas fa-fish" style="color:var(--blue-400); margin-right:6px;"></i>' + (ikan.nama_peserta || document.getElementById('namaPeserta').value) + badgeHtml;
                        }

                        var kategoriKelasText = formatKategoriKelas(ikan.kategori, ikan.kelas);
                        if (existingP) {
                            existingP.textContent = kategoriKelasText;
                        } else if (infoDiv) {
                            var newP = document.createElement('p');
                            newP.textContent = kategoriKelasText;
                            infoDiv.appendChild(newP);
                        }
                        currentIkans[ikan.id].kategori = kategoriKelasText;

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
                            if (tankEl) {
                                tankEl.textContent = '--';
                                tankEl.classList.remove('filled');
                                tankEl.classList.add('empty');
                                let checkmark = existingEl.querySelector('.fa-circle-check');
                                if (checkmark) {
                                    const parent = checkmark.closest('span') || checkmark.parentElement;
                                    if (parent) parent.outerHTML = `<button class="btn-acak-kecil" onclick="mulaiAcak(${ikan.id}, this)"><i class="fas fa-shuffle"></i> ACAK</button>`;
                                }
                            }
                            currentIkans[ikan.id].nomor_tank = '--';
                        } else if (currentIkans[ikan.id].nomor_tank !== currentTank && currentTank !== '--') {
                            const tankEl = document.getElementById(`tank-num-${ikan.id}`);
                            if (tankEl) {
                                tankEl.textContent = currentTank;
                                tankEl.classList.remove('empty');
                                tankEl.classList.add('filled');
                                let btn = existingEl.querySelector('.btn-acak-kecil');
                                if (btn) btn.outerHTML = '<span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>';
                            }
                            currentIkans[ikan.id].nomor_tank = currentTank;
                        }
                    }
                });

                document.getElementById('mvpListContainer').innerHTML = mvpListHtml;
                document.getElementById('mvpCountBadge').textContent = `${mvpCount}/30 MVP`;
                // Update progress bar
                var progFill = document.getElementById('mvpProgressFill');
                if (progFill) progFill.style.width = Math.min(100, (mvpCount / 30) * 100) + '%';

                if(isMvpOpen) {
                    document.getElementById('mvpEmptyList').style.display = mvpCount > 0 ? 'none' : 'block';
                    document.getElementById('btnSubmitMvp').style.display = mvpCount > 0 && !currentMvpSubmitted ? 'flex' : 'none';
                    document.getElementById('mvpSubmittedBadge').style.display = currentMvpSubmitted ? 'block' : 'none';
                }

                const banner = document.getElementById('resetBanner');
                if (hasResetIkan && resetInfo && resetInfo.reason) {
                    banner.style.display = 'flex';
                    document.getElementById('resetBannerText').innerHTML = `Nomor tank Anda telah direset oleh panitia. Alasan: <strong style="color:#fff;">${resetInfo.reason}</strong>`;
                }

                const total = data.length;
                const undi = data.filter(i => i.nomor_tank).length;
                const statusBadge = document.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.textContent = total > 0 ? `${undi}/${total} DIUNDI` : 'MENUNGGU IKAN';
                    statusBadge.className = 'status-badge ' + (total > 0 ? 'success' : '');
                }

                // Re-apply filter setelah polling update
                applyFishFilter();
            })
            .catch(function(err) {
                console.error('Polling error:', err);
            });
        }

        // ★ INIT LOCK UI SEGERA (tanpa tunggu polling)
        updateUndianLockUI(isUndianOpen);

        setTimeout(function() {
            console.log('Starting polling...');
            pollIkans();
            pollingInterval = setInterval(pollIkans, 5000);
        }, 3000);

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
            if (!btnElement || btnElement.disabled) return;
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

        // --- HIDE KELAS UNTUK BONSAI/JUMBO ---
        var noKelasKategori = ['Bonsai', 'Jumbo'];
        var ikanKategoriSelect = document.getElementById('ikanKategoriSelect');
        var ikanKelasWrap = document.getElementById('ikanKelasWrap');
        var ikanKelasSelectEl = document.getElementById('ikanKelasSelect');

        function resetIkanFormState() {
            if(ikanKelasWrap) ikanKelasWrap.style.display = '';
            if(ikanKelasSelectEl) ikanKelasSelectEl.value = '';
        }

        function openModalIkan() {
            resetIkanFormState();
            var el = document.getElementById('inlineFormIkan');
            el.style.display = el.style.display === 'block' ? 'none' : 'block';
        }

        function kategoriKelasLineHtml(kategori, kelas) {
            if (noKelasKategori.indexOf(kategori) !== -1 || !kelas) return '<p>' + kategori + '</p>';
            return '<p>' + kategori + ' - Kelas ' + kelas + '</p>';
        }

        function formatKategoriKelas(kategori, kelas) {
            if (noKelasKategori.indexOf(kategori) !== -1 || !kelas) return kategori;
            return kategori + ' - Kelas ' + kelas;
        }

        if(ikanKategoriSelect && ikanKelasWrap){
            ikanKategoriSelect.addEventListener('change', function(){
                if(noKelasKategori.indexOf(this.value) !== -1){
                    if(ikanKelasSelectEl) ikanKelasSelectEl.value = '';
                    ikanKelasWrap.style.display = 'none';
                } else {
                    ikanKelasWrap.style.display = '';
                }
            });
        }

        // --- LOGIC MESIN UNDIAN ---
        const numberDisplay = document.getElementById('numberDisplay');
        const lcdInfo = document.getElementById('lcdInfo');

        function mulaiAcak(ikanId, btnElement) {
            if (btnElement.disabled) return;
            if (!isUndianOpen) {
                alert('Mesin undian belum dibuka oleh panitia. Silakan tunggu.');
                return;
            }
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            lcdInfo.textContent = 'Sedang mengundi...';
            numberDisplay.classList.add('rolling');
            numberDisplay.classList.remove('final');
            var maxForAnim = tankDrawMax || 1000;
            var rolling = true;
            var rollTimer = setInterval(function() {
                numberDisplay.textContent = Math.floor(Math.random() * maxForAnim) + 1;
            }, 40);
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('ikan_id', ikanId);
            apiFetch('{{ route("api.acak.tank.user") }}', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) throw new Error(data.message || 'Gagal mengacak nomor.');
                var finalNumber = data.nomor_tank;
                rolling = false;
                clearInterval(rollTimer);
                var slowSteps = 8;
                var slowIndex = 0;
                function slowRoll() {
                    slowIndex++;
                    var progress = slowIndex / slowSteps;
                    var spread = Math.max(0, Math.round(50 * (1 - progress)));
                    var minN = Math.max(1, finalNumber - spread);
                    var maxN = finalNumber + spread;
                    var shown = Math.floor(Math.random() * (maxN - minN + 1)) + minN;
                    if (slowIndex >= slowSteps) shown = finalNumber;
                    numberDisplay.textContent = shown;
                    if (slowIndex >= slowSteps) {
                        numberDisplay.classList.remove('rolling');
                        numberDisplay.classList.add('final');
                        lcdInfo.textContent = 'Berhasil!';
                        var tankNumEl = document.getElementById('tank-num-' + ikanId);
                        if (tankNumEl) {
                            tankNumEl.textContent = finalNumber;
                            tankNumEl.classList.remove('empty');
                            tankNumEl.classList.add('filled');
                        }
                        btnElement.outerHTML = '<span style="color:var(--green-500); font-size:14px;"><i class="fas fa-circle-check"></i></span>';
                        setTimeout(function() {
                            numberDisplay.textContent = '--';
                            numberDisplay.classList.remove('final');
                            lcdInfo.textContent = 'Klik ACAK pada daftar ikan';
                        }, 2500);
                    } else {
                        var delay = 100 + (progress * progress * 250);
                        setTimeout(slowRoll, delay);
                    }
                }
                slowRoll();
            })
            .catch(function(err) {
                rolling = false;
                clearInterval(rollTimer);
                numberDisplay.textContent = '--';
                numberDisplay.classList.remove('rolling');
                var errorMsg = err.message || 'Terjadi kesalahan';
                if (errorMsg.indexOf('NOMOR TANK PENUH') !== -1) {
                    lcdInfo.textContent = 'Nomor tank penuh';
                    alert('⚠️ ' + errorMsg);
                } else {
                    lcdInfo.textContent = 'Gagal';
                    alert('Gagal mengacak: ' + errorMsg);
                }
                pollIkans();
                setTimeout(function() {
                    var checkBtn = document.querySelector('#ikan-item-' + ikanId + ' .btn-acak-kecil');
                    if (checkBtn) {
                        checkBtn.disabled = false;
                        checkBtn.innerHTML = '<i class="fas fa-shuffle"></i> ACAK';
                    }
                }, 600);
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
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

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
                    if(modalDesc) modalDesc.innerHTML = 'Pilihan ikan MVP Anda berhasil dikirim dan sudah <b style="color:var(--gold-300);">TERKUNCI</b>. Data tidak dapat diubah lagi.';
                    document.getElementById('successModal').classList.add('show');
                    pollIkans();
                } else {
                    alert(data.message || 'Gagal mengirim data MVP.');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'))
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Ya, Kirim MVP'; });
        }

        /* ============================================================
           ENHANCEMENT: Pure-frontend search & filter pada daftar ikan
           (Tidak mengubah API atau business logic - hanya CSS display)
           ============================================================ */
        function applyFishFilter() {
            var q = (document.getElementById('fishSearchInput') || {}).value || '';
            var katFilter = (document.getElementById('fishFilterKategori') || {}).value || '';
            var stsFilter = (document.getElementById('fishFilterStatus') || {}).value || '';
            q = q.toLowerCase().trim();

            document.querySelectorAll('.ikan-item').forEach(function(el) {
                var h4 = el.querySelector('.ikan-item-info h4');
                var tank = el.querySelector('.tank-num');
                var text = (h4 ? h4.textContent : '').toLowerCase() + ' ' + ((el.querySelector('.ikan-item-info p') || {}).textContent || '').toLowerCase();
                var matchSearch = !q || text.indexOf(q) !== -1;
                var matchKat = !katFilter || (h4 && h4.textContent.toLowerCase().indexOf(katFilter.toLowerCase()) !== -1);
                var isDiundi = tank && !tank.classList.contains('empty');
                var matchSts = !stsFilter || (stsFilter === 'diundi' && isDiundi) || (stsFilter === 'belum' && !isDiundi);

                el.style.display = (matchSearch && matchKat && matchSts) ? '' : 'none';
            });
        }
        (function initFishFilter(){
            var s = document.getElementById('fishSearchInput');
            var k = document.getElementById('fishFilterKategori');
            var st = document.getElementById('fishFilterStatus');
            if (s) s.addEventListener('input', applyFishFilter);
            if (k) k.addEventListener('change', applyFishFilter);
            if (st) st.addEventListener('change', applyFishFilter);
        })();
    </script>
</body>
</html>