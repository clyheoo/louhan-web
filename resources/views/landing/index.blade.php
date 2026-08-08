{{-- ============================================================
     LOUHAN CLUB INDONESIA — LANDING (SPA + Long Scroll on Beranda)
     ============================================================ --}}
@php
    $lines = function ($val) {
        return collect(preg_split('/\r\n|\r|\n/', (string) $val))
            ->map(fn ($l) => trim($l))->filter()->values();
    };
    $pipe = fn ($line, $n = 2) => array_pad(array_map('trim', explode('|', $line)), $n, '');

    $heroEyebrow  = $contents['hero_eyebrow']  ?? 'International Aquatic Championship';
    $heroTitle    = $contents['hero_title']    ?? 'NUSATIC WORLD FLOWERHORN CHAMPIONSHIP 2026';
    $heroSubtitle = $contents['hero_subtitle'] ?? 'Panggung penjurian louhan bertaraf internasional — mempertemukan aquarist terbaik Asia Tenggara di satu meja juri.';
    $heroPrize    = $contents['hero_prize']    ?? 'Rp 100.000.000';
    $heroDate     = $contents['hero_date']     ?? '2026-08-17T08:00:00';
    $heroMedia    = $contents['hero_media']    ?? 'https://images.unsplash.com/photo-1520302630591-fd1c66edc19d?q=80&w=1600';
    $heroShowPrize = ($contents['hero_show_prize'] ?? '1') !== '0';   // ← TAMBAH: tampilkan overlay "Total Hadiah"?

    $aboutTitle   = $contents['about_title']       ?? 'Tentang Kejuaraan';
    $aboutDesc    = $contents['about_description'] ?? 'Louhan Club Indonesia menghadirkan sistem penjurian transparan berbasis skor realtime. Setiap ikan dinilai panel juri bersertifikat dengan standar internasional — dari kualitas mutiara, bentuk kepala, hingga proporsi tubuh.';

    // MAP: <iframe>, URL embed, URL Maps (@lat,lng / /place/), atau ALAMAT teks.
    $mapRaw   = trim((string) ($contents['about_map_url'] ?? ''));
    $aboutMap = '';
    if ($mapRaw !== '') {
        if (preg_match('/<iframe[^>]*src=["\']([^"\']+)["\']/i', $mapRaw, $mm)) {
            $aboutMap = $mm[1];                                                                       // kode <iframe ...>
        } elseif (stripos($mapRaw, '/maps/embed') !== false) {
            $aboutMap = $mapRaw;                                                                      // URL embed resmi
        } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapRaw, $cc)) {
            $aboutMap = 'https://maps.google.com/maps?q=' . $cc[1] . ',' . $cc[2] . '&z=16&output=embed';   // koordinat dari URL Maps
        } elseif (preg_match('#/place/([^/@]+)#', $mapRaw, $pl)) {
            $aboutMap = 'https://maps.google.com/maps?q=' . rawurlencode(str_replace('+', ' ', urldecode($pl[1]))) . '&z=16&output=embed'; // nama tempat dari URL
        } elseif (!preg_match('#^https?://#i', $mapRaw)) {
            $aboutMap = 'https://maps.google.com/maps?q=' . rawurlencode($mapRaw) . '&z=16&output=embed';   // ALAMAT teks langsung
        } else {
            $aboutMap = $mapRaw;                                                                      // URL lain: tampilkan apa adanya
        }
    }

    // FITUR "Tentang" (4 baris, format: Judul | Deskripsi)
    $aboutFeatRaw = $lines($contents['about_features'] ?? '');
    $aboutFeatures = $aboutFeatRaw->count()
        ? $aboutFeatRaw->map(fn ($l) => $pipe($l, 2))
        : collect([
            ['Standar Internasional', 'Penilaian mutiara, kepala, warna & proporsi.'],
            ['Komunitas Nasional',    'Aquarist & klub dari seluruh Indonesia.'],
            ['Sistem Realtime',       'Skor & ranking diperbarui langsung.'],
            ['Apresiasi Layak',       'Total hadiah ' . ($contents['hero_prize'] ?? 'Rp 100.000.000') . ' untuk para juara.'],
          ]);

    // Sub-judul & keterangan (halaman Kategori) — editable dari admin
    $kategoriSubtitle = $contents['kategori_subtitle']       ?? 'Setiap kelas dinilai dengan rubrik internasional yang berbeda.';
    $criteriaNote     = $contents['kategori_criteria_note']  ?? 'Empat aspek utama yang menjadi fokus penilaian juri pada setiap ikan.';

    // KRITERIA penilaian — format tiap baris: "Label | fa-icon"
    $criteriaRaw = $lines($contents['kategori_criteria'] ?? '');
    $criteria = $criteriaRaw->count()
        ? $criteriaRaw->map(fn ($l) => $pipe($l, 2))
        : collect([
            ['Mutiara', 'fa-gem'],
            ['Kepala (Kok)', 'fa-circle-dot'],
            ['Warna', 'fa-palette'],
            ['Proporsi', 'fa-ruler-combined'],
          ]);

    $kategoriRaw = $lines($contents['kategori_list'] ?? '');
    $kategori = $kategoriRaw->count()
        ? $kategoriRaw->map(fn ($l) => $pipe($l, 3))
        : collect([
            ['Golden Base', 'Standar mutu mutiara, kepala, dan warna dasar keemasan yang sempurna.', ''],
            ['Kamfa',       'Bentuk tubuh kokoh, sirip lebar, dan intensitas warna yang kuat.', ''],
            ['Jumbo',       'Kelas raksasa di atas 20 inci dengan penilaian proporsi spesial.', ''],
          ]);

    $jadwal  = $lines($contents['jadwal_timeline'] ?? '')->map(fn ($l) => $pipe($l, 3));
    $galeri  = $lines($contents['gallery_images'] ?? '');
    if ($galeri->isEmpty()) {
        $galeri = collect([
            'https://images.unsplash.com/photo-1535591273668-578e31182c4f?q=80&w=1200',
            'https://images.unsplash.com/photo-1571752726703-5e7d1f6a986d?q=80&w=1200',
            'https://images.unsplash.com/photo-1524704654690-b56c05c78a00?q=80&w=1200',
            'https://images.unsplash.com/photo-1520302630591-fd1c66edc19d?q=80&w=1200',
            'https://images.unsplash.com/photo-1522069169874-c58ec4b76be5?q=80&w=1200',
            'https://images.unsplash.com/photo-1544551763-46a013bb70d5?q=80&w=1200',
        ]);
    }
    $sponsor = $lines($contents['sponsor_list'] ?? '')->map(fn ($l) => $pipe($l, 2));
    $faqRaw  = $lines($contents['faq_list'] ?? '');
    $faq = $faqRaw->count()
        ? $faqRaw->map(fn ($l) => $pipe($l, 2))
        : collect([
            ['Bagaimana cara mendaftar?', 'Pendaftaran online melalui panitia. Peserta membuat akun terlebih dahulu untuk mengakses form pendaftaran.'],
            ['Berapa biaya pendaftaran?', 'Biaya bervariasi menurut kategori. Hubungi panitia untuk rincian biaya terkini.'],
            ['Kapan hasil diumumkan?',    'Skor tampil realtime di menu Ranking, dan hasil final diumumkan pada hari penjurian.'],
          ]);

    $cEmail = $contents['contact_email']     ?? 'info@louhanclub.id';
    $cPhone = $contents['contact_phone']     ?? '+62 812 3456 7890';
    $cIg    = $contents['contact_instagram'] ?? '#';
    $cFb    = $contents['contact_facebook']  ?? '#';
    $cYt    = $contents['contact_youtube']   ?? '#';

    // Bentuk link WhatsApp dari nomor telepon (0812... → 62812...)
    $waDigits = preg_replace('/\D+/', '', (string) $cPhone);
    if (\Illuminate\Support\Str::startsWith($waDigits, '0')) { $waDigits = '62' . substr($waDigits, 1); }
    $waLink = $waDigits !== '' ? 'https://wa.me/' . $waDigits : 'tel:' . preg_replace('/\s+/', '', (string) $cPhone);

    $isVideo = \Illuminate\Support\Str::contains($heroMedia, ['.mp4', '.webm']);

    // Slide hero: pakai daftar foto (hero_images) bila ada; jika kosong & bukan video, pakai hero_media tunggal
    $heroImages = $lines($contents['hero_images'] ?? '');
    $heroSlides = $heroImages->count()
        ? $heroImages
        : (($isVideo || $heroMedia === '') ? collect() : collect([$heroMedia]));
    $heroZoom   = $heroSlides->first() ?: $heroMedia;

    $logo    = asset('img/logo_lci.png');

    $nav = [
        'beranda'  => ['Beranda',  'fa-house'],
        'about'    => ['Tentang',  'fa-circle-info'],
        'kategori' => ['Kategori', 'fa-layer-group'],
        'gallery'  => ['Galeri',   'fa-images'],
        'sponsor'  => ['Sponsor',  'fa-handshake'],
        'faq'      => ['FAQ',      'fa-circle-question'],
        'kontak'   => ['Kontak',   'fa-address-book'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heroTitle }} — Louhan Club Indonesia</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $heroSubtitle }}">
    <link rel="icon" href="{{ $logo }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    /* ===================== TOKENS ===================== */
    :root{
        --ocean-950:#04070F; --ocean-900:#0B1220; --ocean-850:#0E1729; --ocean-800:#111E36; --ocean-700:#182947;
        --royal-600:#2563EB; --royal-500:#3B82F6;
        --cyan-500:#06B6D4; --cyan-400:#22D3EE; --cyan-300:#67E8F9;
        --gold:#E4B24C;
        --text-hi:#F8FAFC; --text:#E2E8F0; --text-mid:#94A3B8; --text-low:#64748B;
        --bd-1:rgba(255,255,255,.06); --bd-2:rgba(255,255,255,.10);
        --glass:rgba(255,255,255,.03); --glass-2:rgba(255,255,255,.05);
        --grad:linear-gradient(135deg,var(--royal-600),var(--cyan-500));
        --shadow-soft:0 30px 70px -35px rgba(0,0,0,.8);
        --ease:cubic-bezier(.16,1,.3,1); --wrap:1160px;
    }
    *,*::before,*::after{ margin:0; padding:0; box-sizing:border-box; }
    html{ scroll-behavior:smooth; }
    body{ font-family:'Plus Jakarta Sans',sans-serif; background:var(--ocean-900); color:var(--text); line-height:1.6; overflow-x:hidden; -webkit-font-smoothing:antialiased; }
    ::selection{ background:var(--cyan-500); color:#04070F; }
    a{ text-decoration:none; color:inherit; }
    img{ max-width:100%; display:block; }
    h1, h2, h3, h4, .font-display { font-family: 'Space Grotesk', sans-serif; letter-spacing: -.02em; }

    /* ===================== BACKGROUND ===================== */
    .bg{ position:fixed; inset:0; z-index:-3; background:
            radial-gradient(ellipse 70% 50% at 50% -8%, rgba(37,99,235,.16), transparent 55%),
            linear-gradient(180deg, var(--ocean-950) 0%, var(--ocean-900) 45%, var(--ocean-850) 100%); }
    .glow{ position:fixed; inset:0; z-index:-2; pointer-events:none; opacity:.5; }
    .glow span{ position:absolute; border-radius:50%; filter:blur(70px); }
    .glow .g1{ width:42vw; height:42vw; left:-8vw; top:-6vw; background:rgba(6,182,212,.16); animation:drift 22s var(--ease) infinite alternate; }
    .glow .g2{ width:38vw; height:38vw; right:-6vw; top:22vh; background:rgba(37,99,235,.16); animation:drift 26s var(--ease) infinite alternate-reverse; }
    @keyframes drift{ from{ transform:translate(0,0) scale(1); } to{ transform:translate(40px,-30px) scale(1.15); } }
    #bubbles{ position:fixed; inset:0; z-index:-1; pointer-events:none; overflow:hidden; }
    .bubble{ position:absolute; bottom:-60px; border-radius:50%; border:1px solid rgba(103,232,249,.14);
        background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(6,182,212,.04)); animation:rise linear infinite; }
    @keyframes rise{ 0%{ transform:translateY(0) scale(.6); opacity:0; } 15%{ opacity:.7; } 100%{ transform:translateY(-108vh) scale(1.2); opacity:0; } }

    .wrap{ max-width:var(--wrap); margin:0 auto; padding:0 26px; }
    .num{ font-variant-numeric:tabular-nums; }
    .grad-text{ background:var(--grad); -webkit-background-clip:text; background-clip:text; color:transparent; }

    .eyebrow{ display:inline-flex; align-items:center; gap:10px; font-size:11px; letter-spacing:.24em; text-transform:uppercase; font-weight:700; color:var(--cyan-300); }
    .eyebrow::before{ content:''; width:22px; height:2px; background:var(--grad); border-radius:2px; }

    /* ===================== INTRO CINEMATIC (LIQUID REVEAL) ===================== */
    #intro{ position:fixed; inset:0; z-index:200; display:grid; place-items:center; background:var(--ocean-950); overflow:hidden; transition:transform 1s cubic-bezier(.7,0,.3,1); }
    #intro.hide{ transform:translateY(-100%); }
    #intro .box{ text-align:center; position:relative; z-index:3; }
    #intro .logo-wrap{ position:relative; width:80px; height:80px; margin:0 auto 24px; display:grid; place-items:center; }
    #intro .logo-wrap img{ width:100%; animation:introReveal 1.6s var(--ease) both, introBreathe 3.4s ease-in-out .8s infinite; }
    html.intro-seen #intro{ display:none !important; }   /* skip intro saat refresh (anti-flash) */
    #intro .t{ font-family: 'Space Grotesk', sans-serif; font-weight:600; letter-spacing:.4em; text-transform:uppercase; font-size:11px; color:var(--cyan-300); opacity:0; animation:fadeUp 1s .5s both, textGlow 3.4s ease-in-out .8s infinite; }
    
    /* Light Sweep */
    #intro .sweep{ position:absolute; inset:0; z-index:1; background:linear-gradient(120deg, transparent 30%, rgba(34,211,238,0.15) 50%, transparent 70%); transform:translateX(-100%); animation:sweep 1.5s ease-in-out forwards; }
    /* Ripple Water Effect */
    #intro .ripple{ position:absolute; top:50%; left:50%; z-index:0; border:1px solid rgba(34,211,238,0.3); border-radius:50%; width:20px; height:20px; transform:translate(-50%,-50%); opacity:0; }
    #intro .r1{ animation:ripple 1.8s .2s ease-out forwards; }
    #intro .r2{ animation:ripple 1.8s .5s ease-out forwards; }
    #intro .r3{ animation:ripple 1.8s .8s ease-out forwards; }

    @keyframes introScale{ 0%{ transform:scale(.5) translateY(20px); opacity:0; filter:blur(10px); } 100%{ transform:scale(1) translateY(0); opacity:1; filter:blur(0); } }
    @keyframes introReveal{
        0%{ opacity:0; transform:scale(.82) translateY(10px); filter:blur(6px) drop-shadow(0 0 0 rgba(6,182,212,0)); }
        60%{ opacity:1; filter:blur(0) drop-shadow(0 0 22px rgba(34,211,238,.55)); }
        100%{ opacity:1; transform:scale(1) translateY(0); filter:blur(0) drop-shadow(0 0 18px rgba(6,182,212,.5)); }
    }
    @keyframes introBreathe{
        0%,100%{ filter:drop-shadow(0 0 12px rgba(6,182,212,.30)); transform:scale(1); }
        50%{ filter:drop-shadow(0 0 26px rgba(34,211,238,.65)); transform:scale(1.015); }
    }
    @keyframes textGlow{
        0%,100%{ text-shadow:0 0 6px rgba(34,211,238,.10); }
        50%{ text-shadow:0 0 15px rgba(34,211,238,.45); }
    }
    @keyframes fadeUp{ from{ opacity:0; transform:translateY(10px); letter-spacing:.2em; } to{ opacity:1; transform:none; letter-spacing:.4em; } }
    @keyframes sweep{ 0%{ transform:translateX(-100%); } 100%{ transform:translateX(100%); } }
    @keyframes ripple{ 0%{ width:20px; height:20px; opacity:0.8; } 100%{ width:800px; height:800px; opacity:0; } }

    /* ===================== LUX BORDER ===================== */
    .lux-border { position: relative; background: rgba(14, 23, 41, 0.5); border-radius: 20px; z-index: 1; }
    .lux-border::before { content: ''; position: absolute; inset: 0; border-radius: inherit; padding: 1px; background: linear-gradient(135deg, rgba(34,211,238,0.4), rgba(255,255,255,0.05), rgba(37,99,235,0.2)); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none; }

    /* ===================== NAV ===================== */
    header.nav{ position:fixed; inset:0 0 auto 0; z-index:80; transition:background .4s var(--ease), border-color .4s; border-bottom:1px solid transparent; }
    header.nav.scr{ background:rgba(11,18,32,.82); backdrop-filter:blur(16px); border-color:var(--bd-1); }
    .nav-in{ max-width:var(--wrap); margin:0 auto; padding:14px 26px; display:flex; align-items:center; justify-content:space-between; gap:18px; }
    .brand{ display:flex; align-items:center; gap:12px; cursor:pointer; }
    .brand img{ height:38px; width:auto; filter:drop-shadow(0 6px 14px rgba(6,182,212,.25)); }
    .brand .bt b{ font-size:15px; font-weight:700; color:var(--text-hi); line-height:1; display:block; font-family: 'Space Grotesk', sans-serif; }
    .brand .bt span{ font-size:9px; letter-spacing:.24em; text-transform:uppercase; color:var(--cyan-300); font-weight:700; }
    .menu{ display:flex; align-items:center; gap:4px; }
    .menu a{ position:relative; font-size:13px; font-weight:500; color:var(--text-mid); padding:9px 14px; border-radius:10px; cursor:pointer; transition:color .25s, background .25s; }
    .menu a:hover{ color:var(--text-hi); background:var(--glass-2); }
    .menu a.on{ color:var(--text-hi); background:linear-gradient(135deg, rgba(34,211,238,.16), rgba(37,99,235,.12)); box-shadow:inset 0 0 0 1px rgba(34,211,238,.22); }
    .btn{ display:inline-flex; align-items:center; gap:9px; font-weight:600; font-size:13px; padding:11px 20px; border-radius:11px; cursor:pointer; border:0; transition:transform .25s var(--ease), box-shadow .25s, background .25s, color .25s; }
    .btn-p{ background:var(--grad); color:#fff; box-shadow:0 10px 26px -12px rgba(6,182,212,.6); }
    .btn-p:hover{ transform:translateY(-2px); box-shadow:0 16px 34px -14px rgba(6,182,212,.75); }
    .btn-o{ background:var(--glass-2); color:var(--text); border:1px solid var(--bd-2); }
    .btn-o:hover{ border-color:var(--cyan-400); color:var(--cyan-300); transform:translateY(-2px); }
    .navtoggle{ display:none; background:var(--glass-2); border:1px solid var(--bd-2); color:var(--text-hi); width:42px; height:42px; border-radius:11px; cursor:pointer; font-size:16px; }

    /* ===================== PAGE / SPA ===================== */
    main{ padding-top:80px; min-height:100vh; }
    .page{ display:none; }
    .page.active{ display:block; animation:pageIn .6s var(--ease); }
    @keyframes pageIn{ from{ opacity:0; transform:translateY(16px); } to{ opacity:1; transform:none; } }
    .sec{ padding:60px 0 90px; }
    .phead{ margin-bottom:44px; max-width:44rem; }
    .phead h1,.phead h2{ font-size:clamp(1.9rem,4vw,3rem); font-weight:700; color:var(--text-hi); margin-top:12px; line-height:1.1; }
    .phead p{ color:var(--text-mid); margin-top:12px; }

    .rv{ opacity:0; transform:translateY(24px); transition:opacity .8s var(--ease), transform .8s var(--ease); }
    .rv.in{ opacity:1; transform:none; }

    /* ===================== BERANDA ===================== */
    .hero{ display:grid; grid-template-columns:1.12fr .88fr; gap:52px; align-items:center; padding:40px 0 20px; }
    .hero h1{ font-size:clamp(2.3rem,5vw,4.2rem); font-weight:700; color:var(--text-hi); line-height:1.06; margin:18px 0 18px; }
    .hero h1 span { color: var(--cyan-400); }
    .hero .lead{ color:var(--text-mid); font-size:1.05rem; max-width:33rem; }
    .hero-cta{ display:flex; gap:12px; flex-wrap:wrap; margin:30px 0 40px; }
    .stats{ display:grid; grid-template-columns:repeat(4,1fr); gap:16px; max-width:34rem; }
    .stat{ background:var(--glass); border:1px solid var(--bd-1); border-radius:16px; padding:18px 14px; transition: border-color .3s; }
    .stat:hover{ border-color: rgba(34,211,238,.3); }
    .stat b{ font-size:1.9rem; font-weight:700; color:var(--text-hi); display:block; line-height:1; font-family: 'Space Grotesk', sans-serif; }
    .stat small{ display:block; margin-top:7px; font-size:9.5px; letter-spacing:.14em; text-transform:uppercase; color:var(--text-low); font-weight:700; }
    .hero-fig{ position:relative; }
    .hero-media{ position:relative; border-radius:24px; overflow:hidden; aspect-ratio:4/5; box-shadow:var(--shadow-soft); }
    .hero-media img,.hero-media video{ width:100%; height:100%; object-fit:cover; transform:scale(1.05); transition: transform 10s linear; }
    .hero-media:hover img, .hero-media:hover video { transform: scale(1.1); }
    .hero-media::after{ content:''; position:absolute; inset:0; background:linear-gradient(180deg,transparent 45%,rgba(4,7,15,.85)); }
    .prize{ position:absolute; left:16px; right:16px; bottom:16px; z-index:2; display:flex; align-items:center; gap:13px; padding:14px 16px; border-radius:16px; background:rgba(11,18,32,.72); backdrop-filter:blur(12px); border:1px solid var(--bd-2); }
    .prize .ic{ width:42px; height:42px; border-radius:11px; display:grid; place-items:center; flex:none; background:linear-gradient(135deg,var(--gold),#caa03d); color:#2a1e04; }
    .prize small{ font-size:9.5px; letter-spacing:.14em; text-transform:uppercase; color:var(--text-mid); font-weight:700; }
    .prize b{ font-size:1.25rem; color:var(--gold); font-weight:700; font-family: 'Space Grotesk', sans-serif; }
    .badge-float{ position:absolute; top:-12px; right:-10px; z-index:2; background:var(--grad); color:#fff; font-size:11px; font-weight:700; letter-spacing:.06em; padding:7px 13px; border-radius:999px; box-shadow:0 12px 26px -12px rgba(6,182,212,.7); }

    .highlights{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-top:64px; }
    .hl{ padding:22px; border-radius:16px; background:var(--glass); border:1px solid var(--bd-1); display:flex; gap:14px; align-items:flex-start; transition: transform .3s, border-color .3s; }
    .hl:hover { transform: translateY(-4px); border-color: rgba(34,211,238,.2); }
    .hl .ic{ width:40px; height:40px; border-radius:11px; flex:none; display:grid; place-items:center; background:rgba(34,211,238,.12); color:var(--cyan-400); border:1px solid rgba(34,211,238,.22); }
    .hl h4{ color:var(--text-hi); font-size:1rem; font-weight:600; }
    .hl p{ color:var(--text-mid); font-size:.88rem; margin-top:3px; }

    .count{ display:flex; gap:14px; flex-wrap:wrap; }
    .cbox{ flex:1; min-width:96px; text-align:center; padding:22px 14px; border-radius:16px; background:var(--glass); border:1px solid var(--bd-1); position: relative; overflow: hidden; }
    .cbox::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--grad); opacity: .5; }
    .cbox b{ font-size:2.4rem; font-weight:700; color:var(--text-hi); display:block; line-height:1; font-family: 'Space Grotesk', sans-serif; }
    .cbox small{ display:block; margin-top:8px; font-size:9.5px; letter-spacing:.16em; text-transform:uppercase; color:var(--text-low); font-weight:700; }

    /* ===================== KATEGORI ===================== */
    .cats{ display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
    .cat{ position:relative; padding:30px 26px; border-radius:20px; background:var(--glass); border:1px solid var(--bd-1); overflow:hidden; transition:transform .4s var(--ease), border-color .4s, background .4s; }
    .cat::before{ content:''; position:absolute; inset:0 0 auto 0; height:3px; background:var(--grad); transform:scaleX(0); transform-origin:left; transition:transform .5s var(--ease); }
    .cat:hover{ transform:translateY(-8px); border-color:rgba(34,211,238,.3); background:var(--glass-2); }
    .cat:hover::before{ transform:scaleX(1); }
    .cat .ic{ width:52px; height:52px; border-radius:14px; display:grid; place-items:center; margin-bottom:16px; background:rgba(37,99,235,.14); border:1px solid rgba(59,130,246,.28); color:var(--cyan-300); font-size:20px; }
    .cat .idx{ position:absolute; top:22px; right:24px; font-size:2.4rem; font-weight:700; color:rgba(255,255,255,.05); font-family: 'Space Grotesk', sans-serif; }
    .cat h3{ font-size:1.35rem; font-weight:600; color:var(--text-hi); margin-bottom:8px; }
    .cat p{ color:var(--text-mid); font-size:.94rem; }
    .cat.has-bg{ background-size:cover; background-position:center; border-color:rgba(34,211,238,.22); aspect-ratio:1/1; display:flex; flex-direction:column; justify-content:flex-end; }
    /* Hero image: tombol Daftar + gelap lembut saat hover */
    .hero-media::before{ content:''; position:absolute; inset:0; z-index:1; background:rgba(4,7,15,.30); opacity:0; transition:opacity .35s var(--ease); }
    .hero-media:hover::before{ opacity:1; }
    .hero-media .join-btn{ position:absolute; left:50%; top:auto; bottom:9%; transform:translate(-50%,10px); z-index:4; opacity:0; pointer-events:none; box-shadow:0 14px 30px -12px rgba(6,182,212,.85); transition:opacity .35s var(--ease), transform .35s var(--ease); }
    .hero-media:hover .join-btn{ opacity:1; transform:translate(-50%,0); pointer-events:auto; }

    /* Slider hero — geser PENUH ke kiri tiap 5 detik (push, mulus) */
    .hero-media .hero-slider{ position:absolute; inset:0; z-index:0; overflow:hidden; }
    .hero-media .hero-slide{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transform:translateX(100%); opacity:0; will-change:transform; }
    .hero-media .hero-slide.active{ transform:translateX(0); opacity:1; z-index:2; transition:transform 1s var(--ease), opacity 1s var(--ease); }
    .hero-media .hero-slide.leaving{ transform:translateX(-100%); opacity:1; z-index:1; transition:transform 1s var(--ease), opacity 1s var(--ease); }

    /* Kategori: foto bisa diklik */
    .cat.has-bg{ cursor:zoom-in; }

    /* Sponsor: foto sebagai background di belakang teks */
    .sp span{ position:relative; z-index:1; }
    .sp.has-bg{ position:relative; overflow:hidden; color:#fff; border-color:rgba(34,211,238,.25); min-width:180px; min-height:96px; padding:18px 26px; display:flex; align-items:center; justify-content:center; background-size:cover; background-position:center; }
    .sp.has-bg::before{ content:''; position:absolute; inset:0; background:linear-gradient(180deg, rgba(4,7,15,.35), rgba(4,7,15,.82)); opacity:0; transition:opacity .3s var(--ease); }
    .sp.has-bg:hover::before{ opacity:1; }
    .sp.has-bg span{ text-shadow:0 2px 8px rgba(0,0,0,.8); font-weight:700; opacity:0; transition:opacity .3s var(--ease); }
    .sp.has-bg:hover span{ opacity:1; }
    .cat.has-bg::after{ content:''; position:absolute; inset:0; z-index:0; background:linear-gradient(180deg, rgba(4,7,15,.35), rgba(4,7,15,.86)); }
    .cat.has-bg > *{ position:relative; z-index:1; }
    .cat.has-bg h3, .cat.has-bg p{ color:#fff; text-shadow:0 2px 12px rgba(0,0,0,.6); }
    .criteria{ margin-top:40px; display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
    .crit{ padding:18px; border-radius:14px; background:var(--glass); border:1px solid var(--bd-1); text-align:center; transition: border-color .3s; }
    .crit:hover{ border-color: rgba(34,211,238,.2); }
    .crit i{ color:var(--cyan-400); font-size:18px; } .crit span{ display:block; margin-top:8px; font-size:.85rem; color:var(--text-mid); font-weight:600; }

    /* ===================== ABOUT ===================== */
    .about-grid{ display:grid; grid-template-columns:1fr 1fr; gap:44px; align-items:center; }
    .vlist{ display:grid; gap:14px; }
    .vrow{ display:flex; gap:14px; align-items:center; padding:16px 18px; border-radius:14px; background:var(--glass); border:1px solid var(--bd-1); transition: border-color .3s; }
    .vrow:hover{ border-color: rgba(34,211,238,.2); }
    .vrow i{ width:38px; height:38px; border-radius:10px; display:grid; place-items:center; background:rgba(34,211,238,.12); color:var(--cyan-400); flex:none; }
    .vrow b{ color:var(--text-hi); font-weight:600; font-size:.96rem; } .vrow p{ color:var(--text-mid); font-size:.85rem; }
    .mapbox{ border-radius:20px; overflow:hidden; border:1px solid var(--bd-1); aspect-ratio:16/11; }
    .mapbox iframe{ width:100%; height:100%; border:0; filter: saturate(1.05) brightness(1.06); }

    /* ===================== RANKING ===================== */
    .podium{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:26px; }
    .pod{ text-align:center; padding:26px 18px; border-radius:18px; background:var(--glass); border:1px solid var(--bd-1); position:relative; transition: transform .3s; }
    .pod:hover{ transform: translateY(-4px); }
    .pod .m{ font-size:1.8rem; } .pod .r{ font-weight:600; color:var(--text-hi); margin-top:8px; } .pod .t{ font-size:.8rem; color:var(--text-low); } .pod .s{ margin-top:8px; font-size:1.5rem; font-weight:700; color:var(--cyan-400); font-family: 'Space Grotesk', sans-serif; }
    .pod.p1{ border-color:rgba(228,178,76,.4); box-shadow: 0 0 30px -10px rgba(228,178,76,.3); } .pod.p1 .s{ color:var(--gold); }
    .rankwrap{ border-radius:20px; overflow:hidden; border:1px solid var(--bd-1); background:var(--glass); }
    .rank-h{ display:flex; align-items:center; justify-content:space-between; padding:16px 22px; border-bottom:1px solid var(--bd-1); }
    .rank-h b{ color:var(--text-hi); font-weight:600; }
    .live-dot{ display:inline-flex; align-items:center; gap:8px; font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--cyan-300); font-weight:700; }
    .live-dot i{ width:7px; height:7px; border-radius:50%; background:var(--cyan-400); box-shadow:0 0 0 0 rgba(34,211,238,.6); animation:pulse 2s infinite; }
    @keyframes pulse{ 70%{ box-shadow:0 0 0 8px rgba(34,211,238,0); } 100%{ box-shadow:0 0 0 0 rgba(34,211,238,0); } }
    table{ width:100%; border-collapse:collapse; }
    thead th{ text-align:left; padding:14px 22px; font-size:9.5px; letter-spacing:.14em; text-transform:uppercase; color:var(--text-low); font-weight:800; }
    tbody td{ padding:16px 22px; border-top:1px solid var(--bd-1); }
    tbody tr{ transition:background .25s; } tbody tr:hover{ background:rgba(255,255,255,.03); }
    .rk{ font-size:1.3rem; font-weight:700; width:34px; height:34px; display:grid; place-items:center; border-radius:9px; font-family: 'Space Grotesk', sans-serif; }
    .rk1{ color:var(--gold); background:rgba(228,178,76,.12); } .rk2{ color:#cfd8d6; background:rgba(207,216,214,.1); } .rk3{ color:var(--cyan-400); background:rgba(34,211,238,.12); }
    .fname{ font-weight:600; color:var(--text-hi); } .fteam{ color:var(--text-low); font-size:12px; margin-top:2px; }
    .score{ font-weight:700; font-size:1.2rem; color:var(--cyan-400); text-align:right; font-family: 'Space Grotesk', sans-serif; } .tcell{ color:var(--text-mid); font-size:13px; }
    .rank-note{ margin-top:16px; font-size:12.5px; color:var(--text-low); display:flex; gap:8px; align-items:center; }

    /* ===================== GALERI ===================== */
    .grid-g{ columns:3; column-gap:16px; }
    .gitem{ break-inside:avoid; margin-bottom:16px; border-radius:16px; overflow:hidden; position:relative; cursor:pointer; border:1px solid var(--bd-1); opacity:0; transform:translateY(22px); }
    .gitem.in{ opacity:1; transform:none; transition:opacity .7s var(--ease), transform .7s var(--ease); }
    .gitem img{ width:100%; transition:transform .7s var(--ease); }
    .gitem::after{ content:'\f00e'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; inset:0; display:grid; place-items:center; color:#fff; font-size:20px; background:rgba(4,7,15,.5); opacity:0; transition:opacity .35s; }
    .gitem:hover img{ transform:scale(1.06); } .gitem:hover::after{ opacity:1; }
    .lb{ position:fixed; inset:0; z-index:150; background:rgba(3,6,12,.94); backdrop-filter:blur(6px); display:none; place-items:center; padding:30px; }
    .lb.show{ display:grid; } .lb img{ max-width:min(1000px,92vw); max-height:86vh; border-radius:14px; box-shadow:var(--shadow-soft); animation:pop .4s var(--ease); }
    @keyframes pop{ from{ transform:scale(.94); opacity:0; } to{ transform:none; opacity:1; } }
    .lb-a{ position:absolute; color:var(--text-hi); background:rgba(255,255,255,.06); border:1px solid var(--bd-2); width:46px; height:46px; border-radius:50%; display:grid; place-items:center; cursor:pointer; font-size:16px; transition:all .25s; }
    .lb-a:hover{ background:var(--cyan-500); border-color:var(--cyan-500); color:#04070F; }
    .lb-x{ top:24px; right:24px; } .lb-prev{ left:24px; top:50%; transform:translateY(-50%); } .lb-next{ right:24px; top:50%; transform:translateY(-50%); }

    /* ===================== JADWAL ===================== */
    .tl{ position:relative; padding-left:30px; max-width:720px; }
    .tl::before{ content:''; position:absolute; left:7px; top:6px; bottom:6px; width:2px; background:linear-gradient(180deg,var(--cyan-400),transparent); }
    .tl-item{ position:relative; padding:0 0 30px; }
    .tl-item::before{ content:''; position:absolute; left:-27px; top:5px; width:12px; height:12px; border-radius:50%; background:var(--ocean-900); border:2px solid var(--cyan-400); box-shadow:0 0 0 4px rgba(34,211,238,.12); }
    .tl-item .d{ font-size:12px; letter-spacing:.1em; text-transform:uppercase; color:var(--cyan-300); font-weight:700; }
    .tl-item h3{ font-size:1.18rem; color:var(--text-hi); font-weight:600; margin:5px 0 4px; }
    .tl-item p{ color:var(--text-mid); font-size:.92rem; }

    .sponsors{ display:flex; flex-wrap:wrap; gap:14px; margin-top:44px; }
    .sp{ padding:16px 24px; border-radius:14px; background:var(--glass); border:1px solid var(--bd-1); color:var(--text-mid); font-weight:600; font-size:14px; transition:all .3s; }
    .sp:hover{ color:var(--text-hi); border-color:var(--cyan-400); transform:translateY(-3px); } .sp img{ height:26px; }

    /* ===================== FAQ ===================== */
    .faq{ max-width:800px; }
    .faq details{ border:1px solid var(--bd-1); border-radius:14px; margin-bottom:12px; background:var(--glass); overflow:hidden; transition: border-color .3s; }
    .faq details:hover { border-color: rgba(34,211,238,.2); }
    .faq summary{ list-style:none; cursor:pointer; padding:18px 22px; display:flex; align-items:center; justify-content:space-between; gap:16px; font-weight:600; color:var(--text-hi); }
    .faq summary::-webkit-details-marker{ display:none; }
    .faq summary i{ color:var(--cyan-400); transition:transform .35s var(--ease); flex:none; }
    .faq details[open] summary i{ transform:rotate(45deg); }
    .faq .a{ padding:0 22px 20px; color:var(--text-mid); font-size:.95rem; }

    /* ===================== KONTAK ===================== */
    .kgrid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
    .kcard{ padding:26px; border-radius:18px; background:var(--glass); border:1px solid var(--bd-1); text-align:center; transition:transform .35s var(--ease), border-color .35s; }
    .kcard:hover{ transform:translateY(-6px); border-color:rgba(34,211,238,.3); }
    .kcard .ic{ width:56px; height:56px; border-radius:16px; display:grid; place-items:center; margin:0 auto 16px; background:var(--grad); color:#fff; font-size:22px; box-shadow: 0 10px 20px -10px rgba(6,182,212,.5); }
    .kcard b{ color:var(--text-hi); display:block; font-weight:600; } .kcard a,.kcard span{ color:var(--text-mid); font-size:.92rem; }
    .socials{ display:flex; gap:12px; justify-content:center; margin-top:14px; }
    .socials a{ width:42px; height:42px; border-radius:12px; display:grid; place-items:center; background:var(--glass-2); border:1px solid var(--bd-1); color:var(--text-mid); transition:all .3s; }
    .socials a:hover{ color:var(--cyan-300); border-color:var(--cyan-400); transform:translateY(-3px); }
    .cta-band{ margin-top:40px; padding:40px; border-radius:22px; background:linear-gradient(135deg, rgba(37,99,235,.18), rgba(6,182,212,.12)); border:1px solid rgba(34,211,238,.2); text-align:center; }
    .cta-band h3{ font-size:1.6rem; font-weight:700; color:var(--text-hi); } .cta-band p{ color:var(--text-mid); margin:8px 0 20px; }

    /* ===================== FOOTER ===================== */
    footer{ border-top:1px solid var(--bd-1); margin-top:40px; padding:26px 0; }
    .foot{ display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
    .foot img{ height:34px; } .foot .c{ color:var(--text-low); font-size:13px; }
    .foot .fnav{ display:flex; gap:16px; flex-wrap:wrap; } .foot .fnav a{ color:var(--text-mid); font-size:13px; cursor:pointer; transition:color .25s; } .foot .fnav a:hover{ color:var(--cyan-300); }

    /* ===================== RESPONSIVE ===================== */
    @media (max-width:960px){
        .hero{ grid-template-columns:1fr; gap:36px; } .hero-fig{ max-width:400px; }
        .cats,.highlights,.kgrid{ grid-template-columns:1fr; } .criteria{ grid-template-columns:repeat(2,1fr); }
        .about-grid{ grid-template-columns:1fr; } .grid-g{ columns:2; } .podium{ grid-template-columns:1fr; }
        .menu{ position:fixed; inset:70px 14px auto 14px; flex-direction:column; align-items:stretch; gap:4px; padding:10px; background:rgba(14,23,41,.97); backdrop-filter:blur(16px); border:1px solid var(--bd-1); border-radius:16px; transform:translateY(-12px); opacity:0; pointer-events:none; transition:.3s var(--ease); }
        .menu.open{ transform:none; opacity:1; pointer-events:auto; } .navtoggle{ display:block; } .nav-cta{ display:none; }
    }
    @media (max-width:600px){ .stats{ grid-template-columns:repeat(2,1fr); } .grid-g{ columns:1; } .rank-hide{ display:none; } .criteria{ grid-template-columns:1fr; } }
    @media (prefers-reduced-motion:reduce){ *{ animation-duration:.001ms !important; transition:none !important; } .rv,.gitem{ opacity:1; transform:none; } }
    </style>
    <script>try{if(sessionStorage.getItem('lci_intro_seen')==='1'){document.documentElement.classList.add('intro-seen');}}catch(e){}</script>
</head>
<body>
    <div class="bg"></div>
    <div class="glow"><span class="g1"></span><span class="g2"></span></div>
    <div id="bubbles"></div>

    {{-- INTRO CINEMATIC --}}
    <div id="intro">
        <div class="ripple r1"></div>
        <div class="ripple r2"></div>
        <div class="ripple r3"></div>
        <div class="sweep"></div>
        <div class="box">
            <div class="logo-wrap">
                <img src="{{ $logo }}" alt="Louhan Club Indonesia">
                <div class="t">Louhan Club Indonesia</div>
            </div>
        </div>
    </div>

    {{-- NAV --}}
    <header class="nav" id="nav">
        <div class="nav-in">
            <div class="brand" data-go="beranda">
                <img src="{{ $logo }}" alt="Louhan Club Indonesia">
                <div class="bt"><b>Louhan Club</b><span>Indonesia</span></div>
            </div>
            <nav class="menu" id="menu">
                @foreach($nav as $key => $m)
                    <a data-go="{{ $key }}">{{ $m[0] }}</a>
                @endforeach
            </nav>
            <div style="display:flex;align-items:center;gap:10px">
                <a href="{{ route('login') }}" class="btn btn-p nav-cta"><i class="fas fa-gavel"></i> Login Penjurian</a>
                <button class="navtoggle" id="navtoggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </header>

    <main>
    {{-- ================= BERANDA ( HERO ) ================= --}}
    <section class="page" id="page-beranda">
        <div class="wrap">
            <div class="hero">
                <div>
                    <span class="eyebrow rv">{{ $heroEyebrow }}</span>
                    <h1 class="rv">{!! nl2br(e($heroTitle)) !!}</h1>
                    <p class="lead rv">{{ $heroSubtitle }}</p>
                    <div class="hero-cta rv">
                        <a class="btn btn-p" data-go="kategori"><i class="fas fa-layer-group"></i> Lihat Kategori</a>
                        <a class="btn btn-o" data-go="gallery"><i class="fas fa-images"></i> Lihat Galeri</a>
                    </div>
                    <div class="stats rv">
                        <div class="stat"><b class="num" id="stat-peserta">{{ $stats['peserta'] ?? 0 }}</b><small>Peserta</small></div>
                        <div class="stat"><b class="num" id="stat-tank">{{ $stats['tank'] ?? 0 }}</b><small>Tank</small></div>
                        <div class="stat"><b class="num" id="stat-kategori">{{ $stats['kategori'] ?? 0 }}</b><small>Kategori</small></div>
                        <div class="stat"><b class="num" id="stat-juri">{{ $stats['juri'] ?? 0 }}</b><small>Juri</small></div>
                    </div>
                </div>
                <div class="hero-fig rv">
                    <span class="badge-float">2026</span>
                    <div class="hero-media lux-border" @if($heroSlides->count()) data-zoom="{{ $heroZoom }}" data-hero-slider @endif>
                        @if($heroSlides->count())
                            <div class="hero-slider">
                                @foreach($heroSlides as $si => $img)
                                    <img src="{{ $img }}" class="hero-slide @if($si === 0) active @endif" alt="Louhan championship" @if($si > 0) loading="lazy" @endif>
                                @endforeach
                            </div>
                        @elseif($isVideo)
                            <video autoplay loop muted playsinline><source src="{{ $heroMedia }}" type="video/mp4"></video>
                        @else
                            <img src="{{ $heroMedia }}" alt="Louhan championship">
                        @endif
                        <a class="btn btn-p join-btn" href="{{ $waLink }}" target="_blank" rel="noopener"><i class="fas fa-user-plus"></i> Daftar</a>
                    </div>
                    @if($heroShowPrize)
                    <div class="prize"><div class="ic"><i class="fas fa-trophy"></i></div><div><small>Total Hadiah</small><b class="num">{{ $heroPrize }}</b></div></div>
                    @endif
                </div>
            </div>

            <div class="highlights">
                <div class="hl rv"><div class="ic"><i class="fas fa-certificate"></i></div><div><h4>Juri Bersertifikat</h4><p>Panel penilai berstandar internasional.</p></div></div>
                <div class="hl rv"><div class="ic"><i class="fas fa-bolt"></i></div><div><h4>Skor Realtime</h4><p>Peringkat langsung dari meja juri.</p></div></div>
                <div class="hl rv"><div class="ic"><i class="fas fa-shield-halved"></i></div><div><h4>Penilaian Transparan</h4><p>Rubrik terbuka &amp; konsisten tiap kelas.</p></div></div>
            </div>
        </div>
    </section>

    {{-- ================= TENTANG ================= --}}
    <section class="page sec" id="page-about">
        <div class="wrap">
            <div class="phead rv"><span class="eyebrow">Tentang</span><h2>{{ $aboutTitle }}</h2><p>{{ $aboutDesc }}</p></div>
            <div class="about-grid">
                <div class="vlist rv">
                    @php $vicons = ['fa-certificate','fa-users','fa-bolt','fa-award','fa-star','fa-gem']; @endphp
                    @foreach($aboutFeatures as $fi => $vf)
                        <div class="vrow"><i class="fas {{ $vicons[$fi % count($vicons)] }}"></i><div><b>{{ $vf[0] }}</b><p>{{ $vf[1] }}</p></div></div>
                    @endforeach
                </div>
                <div class="rv">
                    @if($aboutMap)
                        <div class="mapbox lux-border"><iframe src="{{ $aboutMap }}" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="Lokasi acara"></iframe></div>
                    @else
                        <div class="lux-border" style="padding:34px;display:grid;gap:18px">
                            <div style="font-size:2.4rem;font-weight:700" class="grad-text num">{{ $stats['peserta'] ?? 0 }}+</div>
                            <p style="color:var(--text-mid)">peserta terdaftar bersaing di panggung penjurian louhan paling bergengsi tahun ini.</p>
                            <div style="height:1px;background:var(--bd-1)"></div>
                            <div style="display:flex;gap:14px;align-items:center;color:var(--text-mid)"><i class="fas fa-location-dot" style="color:var(--cyan-400)"></i> Lokasi &amp; rundown lengkap di menu Jadwal.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ================= KATEGORI ================= --}}
    <section class="page sec" id="page-kategori">
        <div class="wrap">
            <div class="phead rv"><span class="eyebrow">Klasifikasi</span><h2>Kategori Lomba</h2><p>{{ $kategoriSubtitle }}</p></div>
            <div class="cats">
                @foreach($kategori as $k)
                    <div class="cat rv @if(!empty($k[2])) has-bg @endif" @if(!empty($k[2])) style="background-image:url('{{ $k[2] }}')" data-zoom="{{ $k[2] }}" @endif>
                        <h3>{{ $k[0] }}</h3><p>{{ $k[1] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="phead rv" style="margin-top:56px;margin-bottom:22px">
                <span class="eyebrow">Aspek Penilaian</span>
                <h2 style="font-size:1.7rem">Kriteria Penilaian</h2>
                <p>{{ $criteriaNote }}</p>
            </div>
            <div class="criteria rv">
                @foreach($criteria as $c)
                    <div class="crit"><i class="fas {{ $c[1] ?: 'fa-check' }}"></i><span>{{ $c[0] }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= GALERI ================= --}}
    <section class="page sec" id="page-gallery">
        <div class="wrap">
            <div class="phead rv"><span class="eyebrow">Dokumentasi</span><h2>Galeri</h2><p>Momen terbaik dari arena kompetisi.</p></div>
            <div class="grid-g" id="gallery-grid">
                @foreach($galeri as $g)
                    <figure class="gitem" data-src="{{ $g }}"><img src="{{ $g }}" alt="Galeri louhan" loading="lazy"></figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= SPONSOR ================= --}}
    <section class="page sec" id="page-sponsor">
        <div class="wrap">
            <div class="phead rv"><span class="eyebrow">Didukung Oleh</span><h2>Sponsor &amp; Partner</h2><p>Terima kasih kepada mitra yang mendukung terselenggaranya acara ini.</p></div>
            @if($sponsor->count())
                <div class="sponsors rv" style="margin-top:0">
                    @foreach($sponsor as $s)
                        <div class="sp @if(!empty($s[1])) has-bg @endif" @if(!empty($s[1])) style="background-image:url('{{ $s[1] }}')" @endif><span>{{ $s[0] ?: 'Sponsor' }}</span></div>
                    @endforeach
                </div>
            @else
                <p class="rv" style="color:var(--text-mid)">Belum ada sponsor yang ditambahkan.</p>
            @endif
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section class="page sec" id="page-faq">
        <div class="wrap">
            <div class="phead rv"><span class="eyebrow">Bantuan</span><h2>Pertanyaan Umum</h2><p>Hal yang paling sering ditanyakan peserta.</p></div>
            <div class="faq rv">
                @foreach($faq as $f)
                    <details><summary><span>{{ $f[0] }}</span><i class="fas fa-plus"></i></summary><div class="a">{{ $f[1] }}</div></details>
                @endforeach
            </div>
            <div class="rv" style="margin-top:24px;color:var(--text-mid);font-size:.95rem">Masih ada pertanyaan? <a data-go="kontak" style="color:var(--cyan-300);cursor:pointer;font-weight:700">Hubungi panitia →</a></div>
        </div>
    </section>

    {{-- ================= KONTAK ================= --}}
    <section class="page sec" id="page-kontak">
        <div class="wrap">
            <div class="phead rv"><span class="eyebrow">Kontak</span><h2>Hubungi Kami</h2><p>Panitia siap membantu pertanyaan seputar kompetisi.</p></div>
            <div class="kgrid">
                <a class="kcard rv" href="mailto:{{ $cEmail }}"><div class="ic"><i class="fas fa-envelope"></i></div><b>Email</b><span>{{ $cEmail }}</span></a>
                <a class="kcard rv" href="{{ $waLink }}" target="_blank" rel="noopener"><div class="ic"><i class="fab fa-whatsapp"></i></div><b>Telepon / WA</b><span>{{ $cPhone }}</span></a>
                <div class="kcard rv"><div class="ic"><i class="fas fa-share-nodes"></i></div><b>Sosial Media</b>
                    <div class="socials"><a href="{{ $cIg }}" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><a href="{{ $cFb }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><a href="{{ $cYt }}" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a></div>
                </div>
            </div>
        </div>
    </section>
    </main>

    <footer><div class="wrap foot">
        <div style="display:flex;align-items:center;gap:12px"><img src="{{ $logo }}" alt="Louhan Club Indonesia"><span class="c">© {{ date('Y') }} Louhan Club Indonesia.</span></div>
        <div class="fnav"><a data-go="about">Tentang</a><a data-go="kategori">Kategori</a><a data-go="gallery">Galeri</a><a data-go="sponsor">Sponsor</a><a data-go="kontak">Kontak</a></div>
    </div></footer>

    {{-- LIGHTBOX --}}
    <div class="lb" id="lb">
        <button class="lb-a lb-x" id="lb-x" aria-label="Tutup"><i class="fas fa-xmark"></i></button>
        <button class="lb-a lb-prev" id="lb-prev" aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i></button>
        <img id="lb-img" src="" alt="">
        <button class="lb-a lb-next" id="lb-next" aria-label="Berikutnya"><i class="fas fa-chevron-right"></i></button>
    </div>

    <script>
    (function(){
        'use strict';
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---- Bubbles (ringan, dibatasi) ---- */
        var bc = document.getElementById('bubbles');
        function bubble(){
            if(reduce || document.hidden || bc.childElementCount > 14) return;
            var b = document.createElement('div'); b.className = 'bubble';
            var s = Math.random()*32 + 8;
            b.style.width = b.style.height = s + 'px';
            b.style.left = Math.random()*100 + '%';
            var dur = Math.random()*8 + 12; b.style.animationDuration = dur + 's';
            bc.appendChild(b); setTimeout(function(){ b.remove(); }, dur*1000);
        }
        setInterval(bubble, 1300);

        /* ---- NAV scroll + mobile ---- */
        var nav = document.getElementById('nav'), menu = document.getElementById('menu'), tgl = document.getElementById('navtoggle');
        window.addEventListener('scroll', function(){ nav.classList.toggle('scr', window.scrollY > 30); }, { passive:true });
        tgl.addEventListener('click', function(){ menu.classList.toggle('open'); });

        /* ---- SPA ROUTER + LONG SCROLL ON BERANDA ---- */
        var links = document.querySelectorAll('.menu a');
        var pages = document.querySelectorAll('.page');
        
        function reveal(page){
            var els = page.querySelectorAll('.rv');
            els.forEach(function(el){ el.classList.remove('in'); });
            requestAnimationFrame(function(){
                els.forEach(function(el,i){ setTimeout(function(){ el.classList.add('in'); }, 60 + i*70); });
            });
            page.querySelectorAll('.gitem').forEach(function(el,i){ el.classList.remove('in'); setTimeout(function(){ el.classList.add('in'); }, 120 + (i%9)*70); });
        }
        
        function go(id){
            if(!document.getElementById('page-'+id)) id = 'beranda';
            
            // Jika di Beranda, tampilkan SEMUA page (Long Scroll)
            // Jika di page lain, sembunyikan yang lain (SPA murni)
            if (id === 'beranda') {
                pages.forEach(function(p){ p.classList.add('active'); });
            } else {
                pages.forEach(function(p){ p.classList.remove('active'); });
                document.getElementById('page-'+id).classList.add('active');
            }
            
            links.forEach(function(a){ a.classList.toggle('on', a.dataset.go === id); });
            menu.classList.remove('open');
            window.scrollTo({ top:0, behavior: reduce ? 'auto' : 'smooth' });
            
            // Reveal semua halaman yang aktif
            document.querySelectorAll('.page.active').forEach(reveal);
        }
        
        document.addEventListener('click', function(e){
            var t = e.target.closest('[data-go]');
            if(!t) return;
            e.preventDefault();
            var id = t.dataset.go;
            if(('#'+id) === location.hash){ go(id); } else { location.hash = id; }
        });
        window.addEventListener('hashchange', function(){ go(location.hash.slice(1)); });

        /* ---- COUNTDOWN ---- */
        var cd = document.getElementById('countdown');
        if(cd){
            var target = new Date(cd.dataset.target).getTime();
            var pad = function(n){ return (n<10?'0':'')+n; };
            function tick(){
                if(isNaN(target)) return;
                var d = target - Date.now(); if(d<0) d=0;
                document.getElementById('cd-days').textContent  = pad(Math.floor(d/864e5));
                document.getElementById('cd-hours').textContent = pad(Math.floor(d%864e5/36e5));
                document.getElementById('cd-mins').textContent  = pad(Math.floor(d%36e5/6e4));
                document.getElementById('cd-secs').textContent  = pad(Math.floor(d%6e4/1e3));
            }
            tick(); setInterval(tick, 1000);
        }

        /* ---- LIVE STATS ---- */
        function setStat(id,v){ var el=document.getElementById(id); if(el && v!=null) el.textContent = v; }
        function loadStats(){
            fetch('/api/landing/live-stats').then(function(r){ return r.json(); }).then(function(d){
                setStat('stat-peserta', d.peserta); setStat('stat-tank', d.tank);
                setStat('stat-kategori', d.kategori); setStat('stat-juri', d.juri);
            }).catch(function(){});
        }
        loadStats(); setInterval(loadStats, 15000);

        /* ---- LIGHTBOX + HERO SLIDER ---- */
        var lb = document.getElementById('lb'), lbImg = document.getElementById('lb-img');
        var lbPrev = document.getElementById('lb-prev'), lbNext = document.getElementById('lb-next');
        var lbList = [], cur = 0;

        function lbShow(){ lbImg.src = lbList[cur]; }
        function openList(list, i){
            if(!list || !list.length) return;
            lbList = list; cur = (i || 0);
            lbShow();
            var multi = list.length > 1;
            lbPrev.style.display = multi ? '' : 'none';
            lbNext.style.display = multi ? '' : 'none';
            lb.classList.add('show'); document.body.style.overflow='hidden';
        }
        function close(){ lb.classList.remove('show'); document.body.style.overflow=''; }
        function step(n){ if(lbList.length < 2) return; cur=(cur+n+lbList.length)%lbList.length; lbShow(); }

        // Galeri
        var gitems = Array.prototype.slice.call(document.querySelectorAll('.gitem'));
        var galleryList = gitems.map(function(el){ return el.dataset.src; });
        gitems.forEach(function(el,i){ el.addEventListener('click', function(){ openList(galleryList, i); }); });

        // Hero: kumpulan foto slider (untuk dibrowse dengan panah)
        var heroSlides = Array.prototype.slice.call(document.querySelectorAll('.hero-slide'));
        var heroList = heroSlides.map(function(im){ return im.getAttribute('src'); });

        // Zoom hero & kategori
        document.querySelectorAll('[data-zoom]').forEach(function(el){
            el.style.cursor = 'zoom-in';
            el.addEventListener('click', function(e){
                if(e.target.closest('a,button')) return;
                if(el.hasAttribute('data-hero-slider') && heroList.length){
                    var act = el.querySelector('.hero-slide.active');
                    var idx = act ? heroSlides.indexOf(act) : 0;
                    openList(heroList, idx < 0 ? 0 : idx);
                } else {
                    openList([el.getAttribute('data-zoom')], 0);
                }
            });
        });

        // Hero slider: geser ke kanan tiap 5 detik (berhenti saat lightbox dibuka)
        if(heroSlides.length > 1 && !reduce){
            var hsi = 0;
            setInterval(function(){
                if(lb.classList.contains('show')) return;
                var prev = heroSlides[hsi];
                hsi = (hsi + 1) % heroSlides.length;
                var next = heroSlides[hsi];
                prev.classList.remove('active');
                prev.classList.add('leaving');
                next.classList.add('active');
                setTimeout(function(){ prev.classList.remove('leaving'); }, 1000);
            }, 5000);
        }

        document.getElementById('lb-x').addEventListener('click', close);
        lbPrev.addEventListener('click', function(e){ e.stopPropagation(); step(-1); });
        lbNext.addEventListener('click', function(e){ e.stopPropagation(); step(1); });
        lb.addEventListener('click', function(e){ if(e.target===lb) close(); });
        document.addEventListener('keydown', function(e){
            if(!lb.classList.contains('show')) return;
            if(e.key==='Escape') close(); if(e.key==='ArrowLeft') step(-1); if(e.key==='ArrowRight') step(1);
        });

        /* ---- INIT: intro sekali per sesi tab + halaman awal ---- */
        function start(){ go(location.hash.slice(1) || 'beranda'); }
        var intro = document.getElementById('intro');
        var seen  = document.documentElement.classList.contains('intro-seen');

        if (seen) {
            start(); // intro sudah di-skip via CSS (display:none) → langsung mulai
        } else {
            window.addEventListener('load', function(){
                setTimeout(function(){
                    intro.classList.add('hide');
                    try { sessionStorage.setItem('lci_intro_seen', '1'); } catch(e){}
                    start();
                }, reduce ? 0 : 1500);
            });
            // Fallback bila event load tertahan
            setTimeout(function(){
                if(!intro.classList.contains('hide')){
                    intro.classList.add('hide');
                    try { sessionStorage.setItem('lci_intro_seen', '1'); } catch(e){}
                    start();
                }
            }, 3500);
        }
    })();
    </script>
</body>
</html>