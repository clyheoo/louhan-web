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
    <meta name="theme-color" content="#04070F">

    <title>Login User - LCI Suite</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --ocean-950:#04070F;
            --ocean-900:#0B1220;
            --ocean-850:#0E1729;
            --ocean-800:#111E36;
            --ocean-700:#182947;
            --royal-700:#1D4ED8;
            --royal-600:#2563EB;
            --royal-500:#3B82F6;
            --cyan-500:#06B6D4;
            --cyan-400:#22D3EE;
            --cyan-300:#67E8F9;
            --cyan-200:#A5F3FC;
            --gold-700:#B45309;
            --gold-600:#D97706;
            --gold-500:#F59E0B;
            --gold-400:#FBBF24;
            --gold-300:#FCD34D;
            --text-hi:#F8FAFC;
            --text:#E2E8F0;
            --text-mid:#94A3B8;
            --text-low:#64748B;
            --text-faint:#475569;
            --danger:#EF4444;
            --success:#10B981;
            --glass-1:rgba(255,255,255,.03);
            --glass-2:rgba(255,255,255,.05);
            --glass-3:rgba(255,255,255,.08);
            --glass-strong:rgba(255,255,255,.12);
            --bd-1:rgba(255,255,255,.06);
            --bd-2:rgba(255,255,255,.10);
            --bd-3:rgba(255,255,255,.16);
            --bd-cyan:rgba(34,211,238,.25);
            --bd-gold:rgba(245,158,11,.30);
        }

        html, body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            background: var(--ocean-900);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(ellipse 72% 48% at 50% 0%, rgba(37,99,235,.17) 0%, transparent 58%),
                radial-gradient(ellipse 45% 45% at 100% 80%, rgba(6,182,212,.10) 0%, transparent 62%),
                radial-gradient(ellipse 40% 36% at 0% 70%, rgba(245,158,11,.08) 0%, transparent 62%),
                linear-gradient(180deg, var(--ocean-950) 0%, var(--ocean-900) 48%, var(--ocean-850) 100%);
        }

        .auth-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 26px 14px;
            overflow: hidden;
        }

        .water-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            opacity: .42;
            background-image:
                linear-gradient(rgba(34,211,238,.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34,211,238,.055) 1px, transparent 1px);
            background-size: 46px 46px;
            mask-image: radial-gradient(ellipse 78% 68% at 50% 48%, black 20%, transparent 78%);
            -webkit-mask-image: radial-gradient(ellipse 78% 68% at 50% 48%, black 20%, transparent 78%);
            animation: gridDrift 28s linear infinite;
            will-change: background-position;
        }

        @keyframes gridDrift {
            from { background-position: 0 0, 0 0; }
            to { background-position: 46px 46px, -46px 46px; }
        }

        .aqua-orb {
            position: fixed;
            z-index: 0;
            pointer-events: none;
            border-radius: 999px;
            filter: blur(42px);
            opacity: .8;
            animation: orbFloat 9s ease-in-out infinite;
        }

        .orb-one {
            width: 340px;
            height: 340px;
            left: -95px;
            top: 6%;
            background: rgba(37,99,235,.26);
        }

        .orb-two {
            width: 300px;
            height: 300px;
            right: -85px;
            bottom: 10%;
            background: rgba(6,182,212,.20);
            animation-delay: -3s;
            animation-duration: 11s;
        }

        .orb-three {
            width: 190px;
            height: 190px;
            right: 18%;
            top: 18%;
            background: rgba(245,158,11,.16);
            animation-delay: -5s;
            animation-duration: 10s;
        }

        @keyframes orbFloat {
            0%,100% { transform: translate3d(0,0,0) scale(1); opacity: .68; }
            50% { transform: translate3d(0,-28px,0) scale(1.08); opacity: 1; }
        }

        .bubble-field {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .bubble {
            position: absolute;
            bottom: -60px;
            width: var(--s);
            height: var(--s);
            left: var(--x);
            border-radius: 999px;
            border: 1px solid rgba(165,243,252,.35);
            background: radial-gradient(circle at 35% 30%, rgba(255,255,255,.52), rgba(34,211,238,.12) 42%, rgba(34,211,238,.02) 72%);
            box-shadow: 0 0 18px rgba(34,211,238,.16);
            opacity: 0;
            animation: bubbleRise var(--d) linear infinite;
            animation-delay: var(--delay);
        }

        @keyframes bubbleRise {
            0% { transform: translate3d(0,0,0) scale(.7); opacity: 0; }
            12% { opacity: .65; }
            82% { opacity: .45; }
            100% { transform: translate3d(var(--drift), -112vh, 0) scale(1.15); opacity: 0; }
        }

        .shape-field {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .shape-field::before,
        .shape-field::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            border: 1px solid rgba(34,211,238,.11);
            opacity: .45;
            animation: haloMove 18s ease-in-out infinite;
            will-change: transform;
        }

        .shape-field::before {
            width: 360px;
            height: 360px;
            left: -120px;
            bottom: -110px;
        }

        .shape-field::after {
            width: 300px;
            height: 300px;
            right: -92px;
            top: 7%;
            border-color: rgba(245,158,11,.12);
            animation-delay: -7s;
            animation-duration: 22s;
        }

        .ui-shape {
            position: absolute;
            opacity: .66;
            border: 1px solid rgba(255,255,255,.08);
            background: linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.012));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.055), 0 10px 30px rgba(0,0,0,.12);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            animation:
                shapeFloat var(--t, 12s) ease-in-out infinite,
                shapeBreath calc(var(--t, 12s) * 1.25) ease-in-out infinite;
            animation-delay: var(--delay, 0s), var(--delay, 0s);
            will-change: transform, opacity;
        }

        .ui-shape::before {
            content: '';
            position: absolute;
            width: 28%;
            height: 28%;
            top: 18%;
            left: 18%;
            border-radius: inherit;
            background: rgba(255,255,255,.16);
            opacity: .38;
        }

        .ui-shape.circle { border-radius: 999px; }
        .ui-shape.square { border-radius: 18px; }
        .ui-shape.tiny { border-radius: 10px; }
        .ui-shape.outline { background: transparent; border: 1px solid rgba(34,211,238,.18); box-shadow: none; }
        .ui-shape.outline::before { display: none; }
        .ui-shape.gold { border-color: rgba(245,158,11,.19); }
        .ui-shape.cyan { border-color: rgba(34,211,238,.23); }
        .ui-shape.blue { border-color: rgba(59,130,246,.20); }

        .shape-s1 { width: 88px; height: 88px; top: 11%; left: 8%; --t: 16s; --delay: -1s; }
        .shape-s2 { width: 26px; height: 26px; top: 17%; left: 40%; --t: 10s; --delay: -2s; }
        .shape-s3 { width: 58px; height: 58px; top: 28%; right: 14%; --t: 14s; --delay: -4s; }
        .shape-s4 { width: 18px; height: 18px; bottom: 22%; left: 18%; --t: 9s; --delay: -3s; }
        .shape-s5 { width: 110px; height: 110px; bottom: 14%; right: 9%; --t: 18s; --delay: -6s; }
        .shape-s6 { width: 36px; height: 36px; top: 56%; right: 31%; --t: 11s; --delay: -1s; }
        .shape-s7 { width: 64px; height: 64px; bottom: 29%; left: 7%; --t: 13s; --delay: -5s; }
        .shape-s8 { width: 22px; height: 22px; top: 63%; left: 49%; --t: 9s; --delay: -7s; }
        .shape-s9 { width: 44px; height: 44px; top: 9%; right: 31%; --t: 12s; --delay: -2s; }
        .shape-s10 { width: 72px; height: 72px; bottom: 10%; left: 38%; --t: 15s; --delay: -4s; }

        .shape-s11 { width: 14px; height: 14px; top: 34%; left: 23%; --t: 8s; --delay: -3s; }
        .shape-s12 { width: 96px; height: 96px; top: 49%; left: -22px; --t: 20s; --delay: -10s; }
        .shape-s13 { width: 30px; height: 30px; bottom: 35%; right: 21%; --t: 10s; --delay: -5s; }
        .shape-s14 { width: 16px; height: 16px; top: 73%; right: 11%; --t: 8s; --delay: -2s; }
        .shape-s15 { width: 76px; height: 76px; top: 39%; right: -18px; --t: 19s; --delay: -8s; }
        .shape-s16 { width: 20px; height: 20px; bottom: 8%; left: 25%; --t: 9s; --delay: -6s; }

        @keyframes shapeFloat {
            0%,100% { transform: translate3d(0,0,0) rotate(0deg); }
            35% { transform: translate3d(10px,-18px,0) rotate(7deg); }
            70% { transform: translate3d(-7px,8px,0) rotate(-5deg); }
        }

        @keyframes shapeBreath {
            0%,100% { opacity: .48; }
            50% { opacity: .82; }
        }

        @keyframes haloMove {
            0%,100% { transform: translate3d(0,0,0) scale(1); }
            50% { transform: translate3d(22px,-18px,0) scale(1.06); }
        }

        .auth-shell {
            width: min(100%, 392px);
            position: relative;
            z-index: 2;
            animation: cardIn .56s cubic-bezier(.16,1,.3,1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(18px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--bd-1);
            border-radius: 22px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.060), rgba(255,255,255,.025)),
                rgba(11,18,32,.74);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow:
                0 30px 65px -35px rgba(0,0,0,.82),
                0 0 0 1px rgba(255,255,255,.025) inset,
                inset 0 1px 0 rgba(255,255,255,.06);
            padding: 26px 24px 22px;
        }

        .login-card::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse at 50% 0%, rgba(34,211,238,.14), transparent 44%),
                linear-gradient(90deg, transparent, rgba(255,255,255,.045), transparent);
            opacity: .85;
        }

        .login-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -80%;
            width: 50%;
            height: 100%;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.09), transparent);
            transform: skewX(-16deg);
            animation: cardShine 6s ease-in-out infinite;
        }

        @keyframes cardShine {
            0%, 55% { left: -85%; opacity: 0; }
            62% { opacity: 1; }
            82% { left: 135%; opacity: 0; }
            100% { left: 135%; opacity: 0; }
        }

        .card-content {
            position: relative;
            z-index: 2;
        }

        .brand-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 18px;
        }

        .brand-mark {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 24px;
            background:
                radial-gradient(circle at 35% 28%, rgba(255,255,255,.26), transparent 26%),
                linear-gradient(135deg, var(--gold-500), var(--gold-700) 42%, var(--royal-600) 100%);
            border: 1px solid rgba(255,255,255,.18);
            box-shadow:
                0 14px 30px -14px rgba(245,158,11,.74),
                0 12px 28px -18px rgba(34,211,238,.88),
                inset 0 1px 0 rgba(255,255,255,.24);
            margin-bottom: 13px;
            animation: markPulse 3.8s ease-in-out infinite;
        }

        @keyframes markPulse {
            0%,100% { transform: translateY(0); box-shadow: 0 14px 30px -14px rgba(245,158,11,.74), 0 12px 28px -18px rgba(34,211,238,.88), inset 0 1px 0 rgba(255,255,255,.24); }
            50% { transform: translateY(-3px); box-shadow: 0 18px 36px -13px rgba(245,158,11,.88), 0 18px 34px -15px rgba(34,211,238,.94), inset 0 1px 0 rgba(255,255,255,.24); }
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 9.5px;
            font-weight: 900;
            color: var(--cyan-300);
            text-transform: uppercase;
            letter-spacing: .16em;
            padding: 5px 10px;
            border: 1px solid var(--bd-cyan);
            border-radius: 999px;
            background: rgba(34,211,238,.08);
            margin-bottom: 10px;
        }

        .login-title {
            color: var(--text-hi);
            font-size: 22px;
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: -.03em;
            margin-bottom: 7px;
        }

        .login-subtitle {
            color: var(--text-mid);
            font-size: 12.5px;
            line-height: 1.6;
            max-width: 300px;
        }

        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 14px;
            margin: 0 0 15px;
            animation: alertShake .42s ease both;
        }

        .login-alert.error {
            background: rgba(239,68,68,.105);
            border: 1px solid rgba(239,68,68,.28);
            color: #FCA5A5;
        }

        .login-alert.success {
            background: rgba(16,185,129,.105);
            border: 1px solid rgba(16,185,129,.28);
            color: #6EE7B7;
            animation: none;
        }

        .login-alert i {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: rgba(255,255,255,.06);
        }

        .login-alert strong {
            display: block;
            font-size: 12px;
            color: var(--text-hi);
            margin-bottom: 2px;
        }

        .login-alert span {
            display: block;
            font-size: 11.5px;
            line-height: 1.45;
            color: currentColor;
        }

        @keyframes alertShake {
            0%,100% { transform: translateX(0); }
            20% { transform: translateX(-5px); }
            40% { transform: translateX(5px); }
            60% { transform: translateX(-3px); }
            80% { transform: translateX(3px); }
        }

        .form-group {
            margin-bottom: 13px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 900;
            color: var(--text-mid);
            text-transform: uppercase;
            letter-spacing: .14em;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            height: 45px;
            padding: 11px 42px 11px 42px;
            border: 1px solid var(--bd-2);
            border-radius: 13px;
            background: rgba(255,255,255,.045);
            color: var(--text-hi);
            font: 600 13px/1.2 'Plus Jakarta Sans', sans-serif;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s, transform .2s;
        }

        .form-input::placeholder {
            color: var(--text-faint);
            font-weight: 600;
        }

        .form-input:focus {
            border-color: var(--cyan-400);
            background: rgba(255,255,255,.07);
            box-shadow: 0 0 0 3px rgba(34,211,238,.10);
        }

        .form-input.is-invalid {
            border-color: rgba(239,68,68,.72);
            background: rgba(239,68,68,.06);
            box-shadow: 0 0 0 3px rgba(239,68,68,.10);
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-low);
            font-size: 13px;
            pointer-events: none;
            transition: color .2s;
        }

        .form-input:focus ~ .input-icon {
            color: var(--cyan-400);
        }

        .password-toggle {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: var(--text-low);
            cursor: pointer;
            transition: color .2s, background .2s;
        }

        .password-toggle:hover {
            color: var(--cyan-300);
            background: rgba(255,255,255,.06);
        }

        .input-error {
            display: none;
            align-items: center;
            gap: 5px;
            color: #FCA5A5;
            font-size: 11px;
            line-height: 1.35;
            margin-top: 6px;
        }

        .input-error.show {
            display: flex;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 8px 0 16px;
        }

        .remember-me {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            color: var(--text-mid);
            font-size: 11.5px;
            font-weight: 700;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: var(--cyan-500);
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 14px;
            color: #fff;
            font: 900 13px/1 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            letter-spacing: .02em;
            background:
                linear-gradient(135deg, rgba(255,255,255,.16), transparent 28%),
                linear-gradient(135deg, var(--royal-600), var(--cyan-500));
            box-shadow:
                0 10px 24px -12px rgba(6,182,212,.86),
                inset 0 1px 0 rgba(255,255,255,.18);
            transition: transform .2s, box-shadow .2s, filter .2s;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
            transform: translateX(-110%);
            transition: transform .7s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow:
                0 14px 30px -14px rgba(6,182,212,.95),
                inset 0 1px 0 rgba(255,255,255,.18);
            filter: saturate(1.08);
        }

        .submit-btn:hover::before {
            transform: translateX(110%);
        }

        .submit-btn:disabled {
            cursor: not-allowed;
            opacity: .72;
            transform: none;
        }

        .mini-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 7px;
            margin-top: 15px;
        }

        .mini-feature {
            min-height: 58px;
            border: 1px solid var(--bd-1);
            border-radius: 13px;
            background: rgba(255,255,255,.035);
            display: grid;
            place-items: center;
            text-align: center;
            padding: 9px 6px;
            transition: border-color .2s, background .2s, transform .2s;
        }

        .mini-feature:hover {
            transform: translateY(-2px);
            border-color: var(--bd-cyan);
            background: rgba(34,211,238,.075);
        }

        .mini-feature i {
            color: var(--cyan-300);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .mini-feature span {
            display: block;
            color: var(--text-mid);
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .09em;
        }

        .wa-card {
            display: block;
            text-decoration: none;
            margin-top: 14px;
            border-radius: 16px;
            border: 1px solid rgba(16,185,129,.28);
            background:
                radial-gradient(circle at 10% 0%, rgba(16,185,129,.18), transparent 34%),
                linear-gradient(135deg, rgba(16,185,129,.11), rgba(34,211,238,.055));
            padding: 12px 13px;
            transition: transform .2s, border-color .2s, background .2s, box-shadow .2s;
        }

        .wa-card:hover {
            transform: translateY(-2px);
            border-color: rgba(16,185,129,.48);
            box-shadow: 0 14px 28px -18px rgba(16,185,129,.82);
            background:
                radial-gradient(circle at 10% 0%, rgba(16,185,129,.22), transparent 34%),
                linear-gradient(135deg, rgba(16,185,129,.14), rgba(34,211,238,.075));
        }

        .wa-content {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .wa-icon {
            width: 39px;
            height: 39px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 19px;
            background: linear-gradient(135deg, #10B981, #059669);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.22), 0 10px 18px -12px rgba(16,185,129,.95);
            flex-shrink: 0;
        }

        .wa-text {
            min-width: 0;
            flex: 1;
        }

        .wa-text strong {
            display: block;
            color: var(--text-hi);
            font-size: 11.7px;
            line-height: 1.35;
            margin-bottom: 2px;
        }

        .wa-text span {
            display: block;
            color: #6EE7B7;
            font-size: 10.5px;
            font-weight: 800;
        }

        .wa-arrow {
            color: #6EE7B7;
            font-size: 13px;
            flex-shrink: 0;
        }

        .login-footer {
            text-align: center;
            margin-top: 17px;
            padding-top: 14px;
            border-top: 1px solid var(--bd-1);
        }

        .footer-text {
            color: var(--text-low);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .17em;
        }

        .footer-text span {
            color: var(--cyan-300);
        }

        @media (max-width: 520px) {
            .auth-page {
                align-items: center;
                padding: 14px 11px;
            }

            .auth-shell {
                width: min(100%, 356px);
            }

            .login-card {
                border-radius: 20px;
                padding: 22px 18px 18px;
            }

            .brand-mark {
                width: 56px;
                height: 56px;
                border-radius: 16px;
                font-size: 22px;
                margin-bottom: 11px;
            }

            .brand-kicker {
                font-size: 8.8px;
                padding: 5px 9px;
            }

            .login-title {
                font-size: 19px;
            }

            .login-subtitle {
                font-size: 11.6px;
                max-width: 270px;
            }

            .form-input {
                height: 43px;
                font-size: 12.5px;
            }

            .submit-btn {
                height: 44px;
                font-size: 12.5px;
            }

            .mini-features {
                gap: 6px;
            }

            .mini-feature {
                min-height: 53px;
            }

            .mini-feature span {
                font-size: 8.2px;
            }

            .wa-card {
                padding: 11px;
            }

            .wa-icon {
                width: 36px;
                height: 36px;
                border-radius: 12px;
            }

            .wa-text strong {
                font-size: 11px;
            }

            .shape-field::before,
            .shape-field::after {
                opacity: .24;
            }

            .shape-s5,
            .shape-s7,
            .shape-s10,
            .shape-s12,
            .shape-s15 {
                display: none;
            }

            .ui-shape {
                opacity: .42;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }

        }

        @media (max-height: 690px) and (min-width: 521px) {
            .auth-page {
                padding-top: 16px;
                padding-bottom: 16px;
            }

            .brand-mark {
                width: 56px;
                height: 56px;
                margin-bottom: 10px;
            }

            .brand-head {
                margin-bottom: 14px;
            }

            .login-card {
                padding-top: 22px;
                padding-bottom: 18px;
            }

            .mini-features {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .1s !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="water-grid"></div>
        <div class="aqua-orb orb-one"></div>
        <div class="aqua-orb orb-two"></div>
        <div class="aqua-orb orb-three"></div>

        <div class="shape-field" aria-hidden="true">
            <span class="ui-shape square cyan shape-s1"></span>
            <span class="ui-shape circle outline shape-s2"></span>
            <span class="ui-shape square blue outline shape-s3"></span>
            <span class="ui-shape circle cyan shape-s4"></span>
            <span class="ui-shape circle gold outline shape-s5"></span>
            <span class="ui-shape square cyan shape-s6"></span>
            <span class="ui-shape square outline shape-s7"></span>
            <span class="ui-shape circle blue outline shape-s8"></span>
            <span class="ui-shape square gold shape-s9"></span>
            <span class="ui-shape circle cyan outline shape-s10"></span>
            <span class="ui-shape circle cyan shape-s11"></span>
            <span class="ui-shape square outline shape-s12"></span>
            <span class="ui-shape square gold tiny shape-s13"></span>
            <span class="ui-shape circle blue shape-s14"></span>
            <span class="ui-shape circle outline shape-s15"></span>
            <span class="ui-shape square cyan tiny shape-s16"></span>
        </div>

        <div class="bubble-field" aria-hidden="true">
            <span class="bubble" style="--x:8%;--s:10px;--d:9s;--delay:-1s;--drift:32px;"></span>
            <span class="bubble" style="--x:16%;--s:16px;--d:12s;--delay:-5s;--drift:-20px;"></span>
            <span class="bubble" style="--x:27%;--s:8px;--d:10s;--delay:-3s;--drift:24px;"></span>
            <span class="bubble" style="--x:41%;--s:13px;--d:14s;--delay:-9s;--drift:-34px;"></span>
            <span class="bubble" style="--x:53%;--s:7px;--d:9s;--delay:-6s;--drift:18px;"></span>
            <span class="bubble" style="--x:66%;--s:15px;--d:13s;--delay:-2s;--drift:-25px;"></span>
            <span class="bubble" style="--x:78%;--s:9px;--d:11s;--delay:-7s;--drift:30px;"></span>
            <span class="bubble" style="--x:90%;--s:18px;--d:15s;--delay:-10s;--drift:-22px;"></span>
        </div>

        <main class="auth-shell">
            <section class="login-card" id="loginCard">
                <div class="card-content">
                    <div class="brand-head">
                        <div class="brand-mark">
                            <i class="fas fa-fish"></i>
                        </div>
                        <div class="brand-kicker">
                            <i class="fas fa-water"></i>
                            Louhan Club Indonesia
                        </div>
                        <h1 class="login-title">Masuk ke Halaman LCI</h1>
                        <p class="login-subtitle">Masuk dengan akun louhan club indonesia</p>
                    </div>

                    @if (session('status'))
                        <div class="login-alert success" role="status">
                            <i class="fas fa-circle-check"></i>
                            <div>
                                <strong>Berhasil</strong>
                                <span>{{ session('status') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="login-alert error" role="alert">
                            <i class="fas fa-circle-exclamation"></i>
                            <div>
                                <strong>Login belum berhasil</strong>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @elseif ($errors->any())
                        <div class="login-alert error" role="alert">
                            <i class="fas fa-triangle-exclamation"></i>
                            <div>
                                <strong>Gmail atau password salah</strong>
                                <span>
                                    @if ($errors->has('email'))
                                        {{ $errors->first('email') }}
                                    @elseif ($errors->has('password'))
                                        {{ $errors->first('password') }}
                                    @else
                                        Gmail atau password yang kamu masukkan belum sesuai. Cek kembali data login kamu.
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="fas fa-envelope"></i>
                                Gmail
                            </label>
                            <div class="input-wrapper">
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-input @error('email') is-invalid @enderror"
                                    placeholder="Masukkan Gmail kamu"
                                    value="{{ old('email') }}"
                                    autocomplete="username"
                                    required
                                    autofocus
                                >
                                <i class="fas fa-at input-icon"></i>
                            </div>
                            <div class="input-error @error('email') show @enderror" id="emailError">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>
                                    @error('email')
                                        {{ $message }}
                                    @else
                                        Gmail yang kamu masukkan belum valid.
                                    @enderror
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="input-wrapper">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-input @error('password') is-invalid @enderror"
                                    placeholder="Masukkan password"
                                    autocomplete="current-password"
                                    required
                                >
                                <i class="fas fa-key input-icon"></i>
                                <button type="button" class="password-toggle" id="togglePasswordBtn" aria-label="Tampilkan password">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="input-error @error('password') show @enderror" id="passwordError">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>
                                    @error('password')
                                        {{ $message }}
                                    @else
                                        Password wajib diisi.
                                    @enderror
                                </span>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me" for="remember">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <span>Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit" class="submit-btn" id="loginBtn">
                            <i class="fas fa-right-to-bracket"></i>
                            <span>Masuk Sekarang</span>
                        </button>
                    </form>

                    <a
                        class="wa-card"
                        href="https://wa.me/6281222299816?text=Halo%20kak%2C%20saya%20tertarik%20membuat%20website%20keren%20seperti%20LCI%20Suite.%20Boleh%20konsultasi%3F"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <div class="wa-content">
                            <div class="wa-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="wa-text">
                                <strong>Mau website keren?</strong>
                                <span>Klik di sini</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square wa-arrow"></i>
                        </div>
                    </a>

                    <footer class="login-footer">
                        <p class="footer-text">Powered by <span>Leo X Nandhog Digital</span></p>
                    </footer>
                </div>
            </section>
        </main>
    </div>

    <script>
        (function () {
            const form = document.getElementById('loginForm');
            const card = document.getElementById('loginCard');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            const loginBtn = document.getElementById('loginBtn');
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const eyeIcon = document.getElementById('eyeIcon');

            function setError(input, errorBox, message) {
                input.classList.add('is-invalid');
                errorBox.querySelector('span').textContent = message;
                errorBox.classList.add('show');
            }

            function clearError(input, errorBox) {
                input.classList.remove('is-invalid');
                errorBox.classList.remove('show');
            }

            function isValidEmail(value) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
            }

            togglePasswordBtn.addEventListener('click', function () {
                const isPassword = password.type === 'password';
                password.type = isPassword ? 'text' : 'password';
                eyeIcon.classList.toggle('fa-eye', !isPassword);
                eyeIcon.classList.toggle('fa-eye-slash', isPassword);
                togglePasswordBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            });

            email.addEventListener('input', function () {
                clearError(email, emailError);
            });

            password.addEventListener('input', function () {
                clearError(password, passwordError);
            });

            form.addEventListener('submit', function (event) {
                let valid = true;
                const emailValue = email.value.trim();
                const passwordValue = password.value.trim();

                clearError(email, emailError);
                clearError(password, passwordError);

                if (!emailValue) {
                    setError(email, emailError, 'Gmail wajib diisi sebelum login.');
                    valid = false;
                } else if (!isValidEmail(emailValue)) {
                    setError(email, emailError, 'Format Gmail belum valid. Contoh: nama@gmail.com');
                    valid = false;
                }

                if (!passwordValue) {
                    setError(password, passwordError, 'Password wajib diisi sebelum login.');
                    valid = false;
                }

                if (!valid) {
                    event.preventDefault();
                    card.style.animation = 'none';
                    card.offsetHeight;
                    card.style.animation = 'alertShake .42s ease both';
                    if (!emailValue || !isValidEmail(emailValue)) {
                        email.focus();
                    } else {
                        password.focus();
                    }
                    return;
                }

                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Memeriksa Login...</span>';
            });
        })();
    </script>
</body>
</html>
