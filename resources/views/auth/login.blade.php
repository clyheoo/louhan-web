<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ═══ FAVICON ═══ -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <meta name="theme-color" content="#2563eb">
    
    <title>Security Access - LCI Suite</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ============================================
           RESET & VARIABLES
           ============================================ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue-50:  #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-300: #93c5fd;
            --blue-400: #60a5fa;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            --white:    #ffffff;
            --gray-50:  #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        /* ============================================
           BODY & LAYOUT
           ============================================ */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            background: var(--white);
            padding: 40px 20px;
        }

        /* ============================================
           BACKGROUND
           ============================================ */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-gradient {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 80%, rgba(37,99,235,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 70% 50% at 80% 20%, rgba(59,130,246,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(219,234,254,0.3) 0%, transparent 70%),
                linear-gradient(180deg, var(--blue-50) 0%, var(--white) 40%, var(--white) 60%, var(--blue-50) 100%);
        }

        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.07) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 80%);
        }

        /* Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0;
            animation: orbFloat 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: rgba(59,130,246,0.25); top: 5%; left: -8%; animation-duration: 10s; }
        .orb-2 { width: 350px; height: 350px; background: rgba(37,99,235,0.2); bottom: 5%; right: -8%; animation-delay: -3s; animation-duration: 12s; }
        .orb-3 { width: 250px; height: 250px; background: rgba(96,165,250,0.2); top: 40%; left: 55%; animation-delay: -5s; animation-duration: 9s; }
        @keyframes orbFloat {
            0%, 100% { opacity: 0.6; transform: translateY(0) scale(1); }
            50%      { opacity: 1; transform: translateY(-40px) scale(1.08); }
        }

        /* Particles */
        .particles { position: absolute; inset: 0; }
        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--blue-400);
            border-radius: 50%;
            opacity: 0;
            box-shadow: 0 0 6px rgba(59,130,246,0.5), 0 0 12px rgba(59,130,246,0.2);
            animation: particleRise linear infinite;
        }
        @keyframes particleRise {
            0%   { opacity: 0; transform: translateY(100vh) scale(0); }
            10%  { opacity: 0.9; }
            50%  { opacity: 0.7; }
            90%  { opacity: 0.3; }
            100% { opacity: 0; transform: translateY(-10vh) scale(1.2); }
        }

        /* Geometric Shapes */
        .geo-shape {
            position: absolute;
            border: 2px solid rgba(59,130,246,0.2);
            opacity: 0;
            animation: geoFloat 12s ease-in-out infinite;
        }
        .geo-1 { width: 100px; height: 100px; top: 12%; right: 10%; border-radius: 20px; transform: rotate(45deg); animation-delay: -2s; }
        .geo-2 { width: 70px; height: 70px; bottom: 18%; left: 8%; border-radius: 50%; animation-delay: -6s; }
        .geo-3 { width: 40px; height: 40px; top: 65%; right: 25%; border-radius: 8px; animation-delay: -8s; border-color: rgba(37,99,235,0.15); }
        @keyframes geoFloat {
            0%, 100% { opacity: 0; transform: translateY(0) rotate(0deg); }
            20%      { opacity: 0.7; }
            50%      { opacity: 0.5; transform: translateY(-25px) rotate(180deg); }
            80%      { opacity: 0.7; }
        }

        /* ============================================
           MAIN CARD
           ============================================ */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(59,130,246,0.08);
            border-radius: 24px;
            padding: 48px 36px 36px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.8) inset,
                0 4px 6px -1px rgba(59,130,246,0.05),
                0 20px 40px -8px rgba(59,130,246,0.08);
            position: relative;
            overflow: hidden;
            animation: cardEntry 0.8s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0;
            transform: translateY(30px) scale(0.97);
        }
        @keyframes cardEntry {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .card-inner-glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 50% 0%, rgba(59,130,246,0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        /* ============================================
           LOCK ICON
           ============================================ */
        .lock-container {
            display: flex;
            justify-content: center;
            margin-bottom: 28px;
        }

        .lock-icon-wrapper {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 4px 12px rgba(37,99,235,0.3),
                0 8px 24px rgba(37,99,235,0.15),
                inset 0 1px 0 rgba(255,255,255,0.15);
            animation: lockPulse 3s ease-in-out infinite;
        }
        @keyframes lockPulse {
            0%, 100% { box-shadow: 0 4px 12px rgba(37,99,235,0.3), 0 8px 24px rgba(37,99,235,0.15), inset 0 1px 0 rgba(255,255,255,0.15); }
            50%      { box-shadow: 0 4px 16px rgba(37,99,235,0.4), 0 12px 32px rgba(37,99,235,0.2), inset 0 1px 0 rgba(255,255,255,0.15); }
        }

        .lock-icon-wrapper i {
            font-size: 28px;
            color: var(--white);
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.15));
        }

        /* ============================================
           TEXT
           ============================================ */
        .login-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 6px;
            letter-spacing: -0.3px;
            animation: textFadeIn 0.6s 0.2s both;
        }

        .login-subtitle {
            text-align: center;
            font-size: 13.5px;
            color: var(--gray-500);
            margin-bottom: 28px;
            line-height: 1.5;
            animation: textFadeIn 0.6s 0.3s both;
        }
        @keyframes textFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ============================================
           FORM ELEMENTS
           ============================================ */
        .form-group {
            margin-bottom: 16px;
            animation: textFadeIn 0.6s 0.35s both;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--gray-400);
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            background: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13.5px;
            color: var(--gray-800);
            outline: none;
            transition: all 0.3s ease;
        }
        .form-input::placeholder { color: var(--gray-400); }
        .form-input:focus {
            border-color: var(--blue-400);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .form-input:focus ~ i.input-icon { color: var(--blue-500); }
        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }

        .input-error {
            font-size: 11px;
            color: #ef4444;
            margin-top: 4px;
            font-weight: 500;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-400);
            font-size: 14px;
            padding: 4px;
            transition: color 0.3s ease;
        }
        .password-toggle:hover { color: var(--blue-500); }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            animation: textFadeIn 0.6s 0.4s both;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--blue-600);
            cursor: pointer;
        }
        .remember-me span {
            font-size: 12px;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* ============================================
           BUTTONS
           ============================================ */
        .submit-btn {
            width: 100%;
            padding: 13px 24px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            color: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
            box-shadow: 0 4px 12px rgba(37,99,235,0.25);
            margin-bottom: 16px;
            animation: textFadeIn 0.6s 0.42s both;
        }
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37,99,235,0.35);
        }
        .submit-btn:active { transform: translateY(0); }

        /* ============================================
           LINKS & DIVIDER
           ============================================ */
        .register-link {
            text-align: center;
            font-size: 13px;
            color: var(--gray-500);
            margin-bottom: 20px;
            animation: textFadeIn 0.6s 0.44s both;
        }
        .register-link a {
            color: var(--blue-600);
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .register-link a:hover { color: var(--blue-800); }

        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
            animation: textFadeIn 0.6s 0.48s both;
        }
        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-200), transparent);
        }
        .divider-text {
            font-size: 11.5px;
            color: var(--gray-400);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* ============================================
           GOOGLE BUTTON
           ============================================ */
        .google-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px 24px;
            border: 1.5px solid var(--gray-200);
            border-radius: 14px;
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            animation: textFadeIn 0.6s 0.5s both;
        }
        .google-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--blue-50), rgba(59,130,246,0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .google-btn:hover {
            border-color: var(--blue-300);
            box-shadow: 0 4px 16px rgba(59,130,246,0.1);
            transform: translateY(-1px);
        }
        .google-btn:hover::before { opacity: 1; }
        .google-btn:hover .google-btn-text { color: var(--blue-700); }

        .google-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .google-btn-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            position: relative;
            z-index: 1;
        }

        /* ============================================
           FEATURES
           ============================================ */
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 20px;
            animation: textFadeIn 0.6s 0.55s both;
        }
        .feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 12px 8px;
            background: var(--gray-50);
            border: 1px solid var(--gray-100);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            background: var(--blue-50);
            border-color: var(--blue-100);
        }
        .feature-item i { font-size: 14px; color: var(--blue-500); }
        .feature-item span {
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .login-footer {
            margin-top: 24px;
            text-align: center;
            animation: textFadeIn 0.6s 0.58s both;
        }
        .footer-divider {
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, var(--blue-300), var(--blue-500));
            border-radius: 2px;
            margin: 0 auto 14px;
        }
        .footer-text {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--gray-400);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .footer-text .brand {
            color: var(--blue-500);
            font-weight: 800;
        }

        /* ============================================
           RESPONSIVE & ACCESSIBILITY
           ============================================ */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 28px 28px;
                border-radius: 20px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="bg-layer">
        <div class="bg-gradient"></div>
        <div class="bg-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="geo-shape geo-1"></div>
        <div class="geo-shape geo-2"></div>
        <div class="geo-shape geo-3"></div>
        <div class="particles" id="particles"></div>
    </div>

    <main class="login-wrapper">
        <div class="login-card">
            <div class="card-inner-glow"></div>

            <div class="lock-container">
                <div class="lock-icon-wrapper">
                    <i class="fas fa-shield-halved"></i>
                </div>
            </div>

            <h1 class="login-title">Security Access</h1>
            <p class="login-subtitle">Masuk dengan akun untuk mengakses LCI Suite</p>

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" id="email" class="form-input" placeholder="Masukan email anda" value="{{ old('email') }}" required autofocus>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div id="emailError" class="input-error" style="display:none"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Masukkan password" required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div id="passwordError" class="input-error" style="display:none"></div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="submit-btn" id="loginBtn">
                    <i class="fas fa-arrow-right-to-bracket" style="margin-right:8px"></i>Masuk
                </button>   
            </form>

            <p class="register-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
            </p>

            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">atau</span>
                <div class="divider-line"></div>
            </div>

            <a href="#" class="google-btn">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span class="google-btn-text">Masuk dengan Google</span>
            </a>

            <div class="features">
                <div class="feature-item"><i class="fas fa-bolt"></i><span>Cepat</span></div>
                <div class="feature-item"><i class="fas fa-fingerprint"></i><span>Aman</span></div>
                <div class="feature-item"><i class="fas fa-cloud-arrow-up"></i><span>Cloud</span></div>
            </div>

            <footer class="login-footer">
                <div class="footer-divider"></div>
                <p class="footer-text">Support by <span class="brand">Nandhog Digital</span></p>
            </footer>
        </div>
    </main>

    <script>
        /* Particles */
        (function(){
            var c = document.getElementById('particles');
            for(var i = 0; i < 20; i++){
                var p = document.createElement('div');
                p.classList.add('particle');
                p.style.left = Math.random() * 100 + '%';
                p.style.width = p.style.height = (3 + Math.random() * 5) + 'px';
                p.style.animationDuration = (8 + Math.random() * 10) + 's';
                p.style.animationDelay = (Math.random() * 10) + 's';
                c.appendChild(p);
            }
        })();

        /* Toggle password */
        function togglePassword(){
            var p = document.getElementById('password');
            var i = document.getElementById('eyeIcon');
            if(p.type === 'password'){
                p.type = 'text';
                i.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                p.type = 'password';
                i.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        /* Submit via AJAX */
        var loginForm = document.getElementById('loginForm');
        var loginBtn  = document.getElementById('loginBtn');
        var emailInput    = document.getElementById('email');
        var passwordInput = document.getElementById('password');
        var emailError    = document.getElementById('emailError');
        var passwordError = document.getElementById('passwordError');

        // Tambahkan elemen untuk pesan error umum (kalau bukan error kolom spesifik)
        var generalErrorDiv = document.createElement('div');
        generalErrorDiv.id = 'generalError';
        generalErrorDiv.style.cssText = 'display:none; background:#fef2f2; color:#ef4444; padding:10px 14px; border-radius:10px; font-size:12px; font-weight:600; margin-bottom:16px; border:1px solid #fecaca; text-align:center;';
        loginForm.insertBefore(generalErrorDiv, loginForm.firstChild);

        loginForm.addEventListener('submit', function(e){
            e.preventDefault();

            // Reset semua error state
            emailError.style.display = 'none';
            passwordError.style.display = 'none';
            generalErrorDiv.style.display = 'none';
            emailInput.classList.remove('error');
            passwordInput.classList.remove('error');

            // Ubah tombol jadi loading
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px"></i>Memproses...';

            var formData = new FormData(loginForm);

            fetch('{{ route("login") }}', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(res){
                // Jika login sukses, Laravel akan redirect
                if(res.redirected){
                    window.location.href = res.url;
                    return; 
                }
                // Jika gagal validasi (422)
                if(!res.ok){
                    return res.json().then(function(data){
                        throw data;
                    });
                }
                return res.json();
            })
            .then(function(data){
                // Handle sukses lainnya jika ada
            })
            .catch(function(err){
                // Kembalikan tombol ke semula
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-arrow-right-to-bracket" style="margin-right:8px"></i>Masuk';

                // Tampilkan error validasi dari Laravel
                if(err.errors) {
                    // Cek error email (Termasuk pesan "Email atau password salah" bawaan Laravel)
                    if(err.errors.email){
                        var msg = err.errors.email[0];
                        // Ubah pesan default Laravel ke Bahasa Indonesia yang lebih enak
                        if(msg.includes('do not match')) {
                            msg = 'Email atau password yang Anda masukkan salah.';
                        }
                        emailInput.classList.add('error');
                        emailError.textContent = msg;
                        emailError.style.display = 'block';
                    }
                    if(err.errors.password){
                        passwordInput.classList.add('error');
                        passwordError.textContent = err.errors.password[0];
                        passwordError.style.display = 'block';
                    }
                } else {
                    // Fallback jika error tidak terduga (misal jaringan mati)
                    generalErrorDiv.innerHTML = '<i class="fas fa-circle-exclamation" style="margin-right:6px"></i>Terjadi kesalahan server. Coba lagi.';
                    generalErrorDiv.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>