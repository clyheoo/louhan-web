<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Management - LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        :root{
            --ocean-950:#04070F; --ocean-900:#0B1220; --ocean-850:#0E1729; --ocean-800:#111E36; --ocean-700:#182947;
            --royal-700:#1D4ED8; --royal-600:#2563EB;
            --cyan-500:#06B6D4; --cyan-400:#22D3EE; --cyan-300:#67E8F9;
            --glass-1:rgba(255,255,255,.03); --glass-2:rgba(255,255,255,.05); --glass-3:rgba(255,255,255,.08);
            --bd-1:rgba(255,255,255,.06); --bd-2:rgba(255,255,255,.10);
            --text-hi:#F8FAFC; --text:#E2E8F0; --text-mid:#94A3B8; --text-low:#64748B;
            --success:#10B981; --danger:#EF4444;
        }
        body{ font-family:'Plus Jakarta Sans',sans-serif; background:var(--ocean-900); color:var(--text); min-height:100vh; overflow-x:hidden; }
        body::before{ content:''; position:fixed; inset:0; z-index:0; pointer-events:none; background: radial-gradient(ellipse 70% 50% at 50% 0%, rgba(37,99,235,.14) 0%, transparent 55%), linear-gradient(180deg, var(--ocean-950) 0%, var(--ocean-900) 45%, var(--ocean-850) 100%); }

        .admin-shell{ position:relative; z-index:1; display:grid; grid-template-columns:260px 1fr; min-height:100vh; }
        .sidebar{ background:rgba(11,18,32,.85); backdrop-filter:blur(14px); border-right:1px solid var(--bd-1); display:flex; flex-direction:column; position:sticky; top:0; height:100vh; z-index:90; }
        .sidebar-brand{ padding:18px; border-bottom:1px solid var(--bd-1); display:flex; align-items:center; gap:12px; }
        .sb-mark{ width:40px; height:40px; border-radius:12px; display:grid; place-items:center; background:linear-gradient(135deg, var(--royal-600), var(--cyan-500)); color:#fff; font-size:16px; }
        .sb-brand-text h1{ font-size:14px; font-weight:800; color:var(--text-hi); }
        .sb-brand-text p{ font-size:9.5px; color:var(--cyan-300); letter-spacing:.12em; text-transform:uppercase; }
        .sidebar-nav{ flex:1; overflow-y:auto; padding:14px 12px; display:flex; flex-direction:column; gap:4px; }
        .sb-section-label{ font-size:9.5px; font-weight:800; color:var(--text-low); letter-spacing:.18em; text-transform:uppercase; padding:8px 12px 4px; }
        .sidebar-item{ display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:11px; color:var(--text-mid); font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; text-decoration:none; border:1px solid transparent; }
        .sidebar-item i{ width:18px; text-align:center; font-size:13px; color:var(--text-low); }
        .sidebar-item:hover{ background:var(--glass-2); color:var(--text-hi); }
        .sidebar-item.active{ background:linear-gradient(135deg, rgba(34,211,238,.18), rgba(37,99,235,.12)); color:var(--text-hi); border-color:rgba(34,211,238,.25); }
        .sidebar-item.active i{ color:var(--cyan-300); }

        .main-area{ display:flex; flex-direction:column; min-width:0; }
        .topbar{ position:sticky; top:0; z-index:50; background:rgba(11,18,32,.78); backdrop-filter:blur(14px); border-bottom:1px solid var(--bd-1); padding:14px 24px; display:flex; align-items:center; justify-content:space-between; }
        .page-title-wrap h2{ font-size:18px; font-weight:800; color:var(--text-hi); display:flex; align-items:center; gap:9px; }
        .page-title-wrap p{ font-size:11px; color:var(--text-mid); margin-top:3px; }
        .content-wrap{ padding:24px; max-width:1200px; margin:0 auto; width:100%; }

        .glass-card{ background:linear-gradient(180deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,.02) 100%); border:1px solid var(--bd-1); border-radius:20px; box-shadow:0 30px 60px -30px rgba(0,0,0,.5); backdrop-filter:blur(12px); margin-bottom:20px; }
        .card-head{ padding:16px 22px; border-bottom:1px solid var(--bd-1); display:flex; justify-content:space-between; align-items:center; }
        .card-head h3{ font-size:14px; font-weight:800; color:var(--text-hi); display:flex; align-items:center; gap:9px; }
        .card-head h3 .ti{ width:30px; height:30px; border-radius:9px; display:grid; place-items:center; background:rgba(34,211,238,.12); border:1px solid rgba(34,211,238,.25); color:var(--cyan-400); font-size:13px; }
        .card-body{ padding:20px 22px; }

        .form-group{ margin-bottom:16px; }
        .form-label{ display:block; font-size:11px; font-weight:800; color:var(--text-mid); text-transform:uppercase; letter-spacing:.1em; margin-bottom:8px; }
        .form-control{ width:100%; padding:12px 14px; border:1px solid var(--bd-2); border-radius:11px; font-family:inherit; font-size:14px; color:var(--text-hi); outline:none; background:rgba(0,0,0,.2); transition:all .2s; }
        .form-control:focus{ border-color:var(--cyan-400); box-shadow:0 0 0 3px rgba(34,211,238,.1); }
        textarea.form-control{ resize:vertical; min-height:120px; }

        .btn-primary{ padding:12px 24px; background:linear-gradient(135deg, var(--royal-600) 0%, var(--cyan-500) 100%); color:#fff; border:none; border-radius:12px; font-size:13px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; box-shadow:0 6px 16px -6px rgba(6,182,212,.5); }
        .btn-primary:hover{ transform:translateY(-1px); }
        .btn-back{ background:var(--glass-2); color:var(--text); border:1px solid var(--bd-2); padding:12px 20px; border-radius:12px; font-size:13px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }

        .alert-success{ background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.3); color:#6EE7B7; padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:10px; }
        .hidden{ display:none; }
        #toastWrap{ position:fixed; top:18px; right:18px; z-index:9999; display:flex; flex-direction:column; gap:10px; }
        .lci-toast{ display:flex; align-items:center; gap:10px; padding:13px 18px; border-radius:12px; font-size:13px; font-weight:700; color:#fff; box-shadow:0 20px 40px -18px rgba(0,0,0,.6); transform:translateX(120%); opacity:0; transition:transform .35s cubic-bezier(.16,1,.3,1), opacity .35s; }
        .lci-toast.show{ transform:none; opacity:1; }
        .lci-toast.ok{ background:linear-gradient(135deg,#059669,#10B981); }
        .lci-toast.err{ background:linear-gradient(135deg,#DC2626,#EF4444); }

        /* ===== Per-content media manager ===== */
        .media-drop{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .media-upload-btn{ display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:11px; border:1px dashed var(--bd-2); background:var(--glass-2); color:var(--cyan-300); font-size:13px; font-weight:700; cursor:pointer; }
        .media-upload-btn:hover{ border-color:var(--cyan-400); }
        .media-status{ font-size:11px; color:var(--text-low); }
        .media-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:14px; margin-top:14px; }
        .media-grid:empty::after{ content:'Belum ada foto. Klik "Upload Foto" untuk menambah.'; color:var(--text-low); font-size:12px; grid-column:1/-1; padding:8px 0; }
        .media-card{ position:relative; border:1px solid var(--bd-2); border-radius:14px; overflow:hidden; background:var(--glass-1); }
        .media-thumb{ width:100%; aspect-ratio:1/1; background-size:cover; background-position:center; background-color:#0b1220; }
        .media-fields{ padding:8px; display:flex; flex-direction:column; gap:6px; }
        .media-input{ width:100%; padding:7px 9px; border:1px solid var(--bd-2); border-radius:8px; background:rgba(0,0,0,.25); color:var(--text-hi); font-size:12px; font-family:inherit; outline:none; }
        .media-input:focus{ border-color:var(--cyan-400); }
        .media-del{ position:absolute; top:6px; right:6px; width:28px; height:28px; border-radius:8px; border:none; background:rgba(220,38,38,.92); color:#fff; font-size:17px; line-height:1; cursor:pointer; display:grid; place-items:center; z-index:2; }
        .media-del:hover{ background:#EF4444; }
        /* ===== Editor Kriteria per-konten ===== */
        .crit-mgr .crit-list{ display:flex; flex-direction:column; gap:10px; margin-bottom:12px; }
        .crit-row{ display:flex; align-items:center; gap:10px; }
        .crit-icpick{ position:relative; }
        .crit-ic-btn{ width:44px; height:44px; border-radius:11px; border:1px solid var(--bd-2); background:var(--glass-2); color:var(--cyan-300); font-size:16px; cursor:pointer; display:grid; place-items:center; }
        .crit-ic-btn:hover{ border-color:var(--cyan-400); }
        .crit-ic-pop{ position:absolute; top:52px; left:0; z-index:30; display:none; grid-template-columns:repeat(6,1fr); gap:6px; padding:10px; width:288px; background:var(--ocean-800); border:1px solid var(--bd-2); border-radius:12px; box-shadow:0 20px 50px -20px rgba(0,0,0,.8); }
        .crit-ic-pop.open{ display:grid; }
        .crit-ic-opt{ width:38px; height:38px; border-radius:9px; border:1px solid var(--bd-2); background:var(--glass-2); color:var(--text); font-size:14px; cursor:pointer; display:grid; place-items:center; }
        .crit-ic-opt:hover{ border-color:var(--cyan-400); color:var(--cyan-300); }
        .crit-label{ flex:1; padding:11px 13px; border:1px solid var(--bd-2); border-radius:11px; background:rgba(0,0,0,.2); color:var(--text-hi); font-size:14px; font-family:inherit; outline:none; }
        .crit-label:focus{ border-color:var(--cyan-400); }
        .crit-del{ width:40px; height:40px; border-radius:11px; border:none; background:rgba(220,38,38,.9); color:#fff; font-size:18px; cursor:pointer; display:grid; place-items:center; flex:none; }
        .crit-del:hover{ background:#EF4444; }
        .crit-add{ display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:11px; border:1px dashed var(--bd-2); background:var(--glass-2); color:var(--cyan-300); font-size:13px; font-weight:700; cursor:pointer; }
        .crit-add:hover{ border-color:var(--cyan-400); }
        /* ===== Editor FAQ per-konten ===== */
        .faq-mgr .faq-list{ display:flex; flex-direction:column; gap:12px; margin-bottom:12px; }
        .faq-row{ position:relative; border:1px solid var(--bd-2); border-radius:12px; padding:12px 44px 12px 12px; display:flex; flex-direction:column; gap:8px; background:var(--glass-1); }
        .faq-row .faq-q, .faq-row .faq-a{ width:100%; padding:10px 12px; border:1px solid var(--bd-2); border-radius:9px; background:rgba(0,0,0,.2); color:var(--text-hi); font-size:13px; font-family:inherit; outline:none; }
        .faq-row .faq-q:focus, .faq-row .faq-a:focus{ border-color:var(--cyan-400); }
        .faq-row .faq-q{ font-weight:700; }
        .faq-row .faq-a{ resize:vertical; min-height:54px; }
        .faq-del{ position:absolute; top:10px; right:10px; width:28px; height:28px; border-radius:8px; border:none; background:rgba(220,38,38,.9); color:#fff; font-size:16px; cursor:pointer; display:grid; place-items:center; }
        .faq-del:hover{ background:#EF4444; }
        .faq-add{ display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:11px; border:1px dashed var(--bd-2); background:var(--glass-2); color:var(--cyan-300); font-size:13px; font-weight:700; cursor:pointer; }
        .faq-add:hover{ border-color:var(--cyan-400); }
    </style>
</head>
<body>

<div class="admin-shell">
    <!-- SIDEBAR WEBSITE MANAGEMENT -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sb-mark"><i class="fas fa-globe"></i></div>
            <div class="sb-brand-text">
                <h1>LCI Web Admin</h1>
                <p>Landing Page CMS</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sb-section-label">Menu Pengaturan</div>
            <a class="sidebar-item active" data-target="hero"><i class="fas fa-mask"></i> Hero Section</a>
            <a class="sidebar-item" data-target="about"><i class="fas fa-circle-info"></i> Tentang Event</a>
            <a class="sidebar-item" data-target="kategori"><i class="fas fa-layer-group"></i> Kategori Lomba</a>
            <a class="sidebar-item" data-target="gallery"><i class="fas fa-images"></i> Galeri Foto</a>
            <a class="sidebar-item" data-target="sponsor"><i class="fas fa-handshake"></i> Sponsor</a>
            <a class="sidebar-item" data-target="faq"><i class="fas fa-question-circle"></i> FAQ</a>
            <a class="sidebar-item" data-target="kontak"><i class="fas fa-address-book"></i> Kontak</a>

            <div class="sb-section-label" style="margin-top:20px;">Sistem</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item"><i class="fas fa-arrow-left"></i> Kembali ke Penilaian</a>
            <a href="{{ route('landing.home') }}" target="_blank" class="sidebar-item"><i class="fas fa-external-link"></i> Lihat Website</a>
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <div class="main-area">
        <header class="topbar">
            <div class="page-title-wrap">
                <h2 id="pageTitle"><i class="fas fa-mask"></i> Hero Section</h2>
                <p id="pageSubtitle">Kelola tampilan utama Landing Page</p>
            </div>
            <a href="{{ route('landing.home') }}" target="_blank" class="btn-back"><i class="fas fa-eye"></i> Preview</a>
        </header>

        <div class="content-wrap">

            @if(session('success'))
                <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            <!-- FORM HERO -->
            <section class="page-section active" id="hero">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="hero">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-mask"></i></span> Hero Section</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Judul Utama</label>
                                <input type="text" name="title" class="form-control" value="{{ $contents['hero_title'] ?? 'NUSATIC WORLD FLOWERHORN CHAMPIONSHIP 2026' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sub Judul</label>
                                <input type="text" name="subtitle" class="form-control" value="{{ $contents['hero_subtitle'] ?? 'Kompetisi Ikan Louhan Bertaraf Internasional' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Eyebrow (teks kecil di atas judul)</label>
                                <input type="text" name="eyebrow" class="form-control" value="{{ $contents['hero_eyebrow'] ?? 'International Aquatic Championship' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total Hadiah</label>
                                <input type="text" name="prize" class="form-control" value="{{ $contents['hero_prize'] ?? 'Rp 100.000.000' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tampilkan Overlay "Total Hadiah" di Foto?</label>
                                <input type="hidden" name="show_prize" value="0">
                                <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;color:var(--text);font-weight:600;font-size:13px;">
                                    <input type="checkbox" name="show_prize" value="1" {{ ($contents['hero_show_prize'] ?? '1') !== '0' ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--cyan-500);">
                                    Ya, tampilkan teks Total Hadiah
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Foto Hero (bisa lebih dari 1 — otomatis bergeser/slideshow)</label>
                                <div class="media-mgr" data-type="simple">
                                    <div class="media-drop">
                                        <button type="button" class="media-upload-btn"><i class="fas fa-cloud-arrow-up"></i> Upload Foto</button>
                                        <input type="file" accept="image/*" multiple hidden class="media-file" data-upload="{{ route('admin.website.upload-media') }}">
                                        <span class="media-status">Bisa pilih beberapa foto sekaligus. Klik ✕ untuk menghapus.</span>
                                    </div>
                                    <div class="media-grid"></div>
                                    <textarea name="images" class="media-store" hidden>{{ $contents['hero_images'] ?? '' }}</textarea>
                                </div>
                                <p style="font-size:11px;color:var(--text-low);margin-top:8px;">Jika hanya 1 foto, tampil sebagai gambar tunggal. Jika lebih dari 1, foto akan bergeser otomatis di landing page.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM TENTANG -->
            <section class="page-section hidden" id="about">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="about">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-circle-info"></i></span> Tentang Event</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Judul Section</label>
                                <input type="text" name="title" class="form-control" value="{{ $contents['about_title'] ?? 'Tentang Kejuaraan' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Deskripsi Event</label>
                                <textarea name="description" class="form-control">{{ $contents['about_description'] ?? '' }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Lokasi Google Maps</label>
                                <input type="text" name="map_url" class="form-control" value="{{ $contents['about_map_url'] ?? '' }}">
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">
                                    Isi salah satu (yang paling gampang: <b>alamat</b>):<br>
                                    • <b>Alamat lengkap</b> — cth: <code>Lux Aquatic, Jl. Sidomoyo, Godean, Sleman, Yogyakarta</code><br>
                                    • <b>URL Google Maps</b> dari address bar browser (yang memuat koordinat <code>@-7.7,110.3</code>)<br>
                                    • Kode <code>&lt;iframe&gt;</code> hasil <b>Share → Embed a map</b><br>
                                    ⚠️ <b>Jangan</b> pakai link pendek <code>maps.app.goo.gl/...</code> — tidak bisa ditampilkan.
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Fitur / Keunggulan (disarankan 4 baris)</label>
                                <textarea name="features" class="form-control" style="min-height:130px">{{ $contents['about_features'] ?? "Standar Internasional | Penilaian mutiara, kepala, warna & proporsi.\nKomunitas Nasional | Aquarist & klub dari seluruh Indonesia.\nSistem Realtime | Skor & ranking diperbarui langsung.\nApresiasi Layak | Total hadiah Rp 5.jt untuk para juara." }}</textarea>
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Satu fitur per baris, format: <b>Judul | Deskripsi</b></p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM KATEGORI -->
            <section class="page-section hidden" id="kategori">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="kategori">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-layer-group"></i></span> Kategori Lomba</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Sub-judul Kategori</label>
                                <input type="text" name="subtitle" class="form-control" value="{{ $contents['kategori_subtitle'] ?? 'Setiap kelas dinilai dengan rubrik internasional yang berbeda.' }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Daftar Kategori — upload foto, lalu isi Judul & Deskripsi tiap konten</label>
                                <div class="media-mgr" data-type="kategori">
                                    <div class="media-drop">
                                        <button type="button" class="media-upload-btn"><i class="fas fa-cloud-arrow-up"></i> Upload Foto Kategori</button>
                                        <input type="file" accept="image/*" multiple hidden class="media-file" data-upload="{{ route('admin.website.upload-media') }}">
                                        <span class="media-status">Setiap foto jadi 1 kartu kategori. Isi Judul & Deskripsi, klik ✕ untuk hapus.</span>
                                    </div>
                                    <div class="media-grid"></div>
                                    <textarea name="list" class="media-store" hidden>{{ $contents['kategori_list'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Keterangan Kriteria (tulisan kecil)</label>
                                <input type="text" name="criteria_note" class="form-control" value="{{ $contents['kategori_criteria_note'] ?? 'Empat aspek utama yang menjadi fokus penilaian juri pada setiap ikan.' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kriteria Penilaian — atur per konten (icon + label)</label>
                                <div class="crit-mgr" data-icons="fa-gem,fa-circle-dot,fa-palette,fa-ruler-combined,fa-fish,fa-star,fa-crown,fa-award,fa-eye,fa-shield-halved,fa-bolt,fa-heart,fa-water,fa-trophy,fa-medal,fa-certificate,fa-droplet,fa-fire">
                                    <div class="crit-list"></div>
                                    <button type="button" class="crit-add"><i class="fas fa-plus"></i> Tambah Kriteria</button>
                                    <textarea id="criteriaBox" name="criteria" class="crit-store" hidden>{{ $contents['kategori_criteria'] ?? "Mutiara | fa-gem\nKepala (Kok) | fa-circle-dot\nWarna | fa-palette\nProporsi | fa-ruler-combined" }}</textarea>
                                </div>
                                <p style="font-size:11px;color:var(--text-low);margin-top:8px;">Klik kotak icon untuk memilih icon, ketik nama kriteria, klik ✕ untuk menghapus. "Tambah Kriteria" untuk baris baru.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM GALERI -->
            <section class="page-section hidden" id="gallery">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="gallery">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-images"></i></span> Galeri Foto</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Foto Galeri — upload & hapus per foto</label>
                                <div class="media-mgr" data-type="simple">
                                    <div class="media-drop">
                                        <button type="button" class="media-upload-btn"><i class="fas fa-cloud-arrow-up"></i> Upload Foto</button>
                                        <input type="file" accept="image/*" multiple hidden class="media-file" data-upload="{{ route('admin.website.upload-media') }}">
                                        <span class="media-status">Bisa pilih beberapa foto sekaligus. Klik ✕ untuk menghapus.</span>
                                    </div>
                                    <div class="media-grid"></div>
                                    <textarea name="images" class="media-store" hidden>{{ $contents['gallery_images'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM SPONSOR -->
            <section class="page-section hidden" id="sponsor">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="sponsor">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-handshake"></i></span> Sponsor</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Sponsor — upload foto, tulis nama pada tiap konten</label>
                                <div class="media-mgr" data-type="sponsor">
                                    <div class="media-drop">
                                        <button type="button" class="media-upload-btn"><i class="fas fa-cloud-arrow-up"></i> Upload Foto Sponsor</button>
                                        <input type="file" accept="image/*" multiple hidden class="media-file" data-upload="{{ route('admin.website.upload-media') }}">
                                        <span class="media-status">Setiap foto jadi 1 sponsor. Tulis nama di kotak, klik ✕ untuk hapus.</span>
                                    </div>
                                    <div class="media-grid"></div>
                                    <textarea name="list" class="media-store" hidden>{{ $contents['sponsor_list'] ?? '' }}</textarea>
                                </div>
                                <p style="font-size:11px;color:var(--text-low);margin-top:8px;">Nama sponsor akan muncul di depan foto saat kursor diarahkan ke logo (di landing page).</p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM FAQ -->
            <section class="page-section hidden" id="faq">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="faq">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-question-circle"></i></span> FAQ</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Daftar Pertanyaan — atur per konten</label>
                                <div class="faq-mgr">
                                    <div class="faq-list"></div>
                                    <button type="button" class="faq-add"><i class="fas fa-plus"></i> Tambah Pertanyaan</button>
                                    <textarea name="list" class="faq-store" hidden>{{ $contents['faq_list'] ?? '' }}</textarea>
                                </div>
                                <p style="font-size:11px;color:var(--text-low);margin-top:8px;">Tiap kotak = 1 pertanyaan + jawaban. Klik ✕ untuk menghapus, "Tambah Pertanyaan" untuk baris baru.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM KONTAK -->
            <section class="page-section hidden" id="kontak">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="contact">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-address-book"></i></span> Kontak & Sosial Media</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="text" name="email" class="form-control" value="{{ $contents['contact_email'] ?? 'info@louhanclub.id' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telepon / WhatsApp</label>
                                <input type="text" name="phone" class="form-control" value="{{ $contents['contact_phone'] ?? '+62 812 3456 7890' }}">
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Nomor ini otomatis jadi link WhatsApp (0812... → 62812...).</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">URL Instagram</label>
                                <input type="text" name="instagram" class="form-control" value="{{ $contents['contact_instagram'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">URL Facebook</label>
                                <input type="text" name="facebook" class="form-control" value="{{ $contents['contact_facebook'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">URL YouTube</label>
                                <input type="text" name="youtube" class="form-control" value="{{ $contents['contact_youtube'] ?? '' }}">
                            </div>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </div>
</div>

<div id="toastWrap"></div>

<script>
    // ===== Tab Switcher =====
    const items = document.querySelectorAll('.sidebar-item[data-target]');
    const sections = document.querySelectorAll('.page-section');
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');

    items.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            items.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            sections.forEach(s => s.classList.add('hidden'));
            const targetId = item.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);
            if(targetSection) targetSection.classList.remove('hidden');
            pageTitle.innerHTML = `<i class="fas ${item.querySelector('i').className.split(' ')[1]}"></i> ${item.textContent.trim()}`;
            pageSubtitle.textContent = `Kelola data ${item.textContent.trim()}`;
        });
    });

    // ===== Toast =====
    function showToast(msg, ok = true){
        let t = document.createElement('div');
        t.className = 'lci-toast ' + (ok ? 'ok' : 'err');
        t.innerHTML = '<i class="fas ' + (ok ? 'fa-check-circle' : 'fa-triangle-exclamation') + '"></i> ' + msg;
        document.getElementById('toastWrap').appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 350); }, 3200);
    }

    // ===== Auto-dismiss alert bawaan (fallback non-AJAX) =====
    document.querySelectorAll('.alert-success').forEach(function(a){
        setTimeout(function(){ a.style.transition='opacity .4s'; a.style.opacity='0'; setTimeout(()=>a.remove(),400); }, 3500);
    });

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ===== Simpan via AJAX: tanpa refresh, tombol loading =====
    document.querySelectorAll('.content-wrap form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const original = btn ? btn.innerHTML : '';
            if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'; }

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: new FormData(form)
            })
            .then(r => r.ok ? r.json() : Promise.reject(r))
            .then(d => { showToast(d.message || 'Berhasil disimpan!', true); })
            .catch(() => showToast('Gagal menyimpan. Coba lagi.', false))
            .finally(() => { if(btn){ btn.disabled = false; btn.innerHTML = original; } });
        });
    });

    // ===== Kriteria Penilaian per-konten =====
    function initCritMgr(mgr){
        var list = mgr.querySelector('.crit-list');
        var store = mgr.querySelector('.crit-store');
        var addBtn = mgr.querySelector('.crit-add');
        var icons = (mgr.dataset.icons || 'fa-check').split(',');
        function parse(){
            return (store.value||'').split(/\r?\n/).map(function(l){return l.trim();}).filter(Boolean)
                .map(function(l){ var p=l.split('|').map(function(s){return s.trim();}); return { label:p[0]||'', ic:p[1]||'fa-check' }; });
        }
        function sync(){
            var lines=[];
            list.querySelectorAll('.crit-row').forEach(function(row){
                var label = row.querySelector('.crit-label').value.trim();
                var ic = row.dataset.ic || 'fa-check';
                if(label) lines.push(label + ' | ' + ic);
            });
            store.value = lines.join('\n');
        }
        function makeRow(item){
            var row = document.createElement('div');
            row.className='crit-row';
            row.dataset.ic = item.ic || 'fa-check';
            var pop = icons.map(function(ic){ return '<button type="button" class="crit-ic-opt" data-ic="'+ic+'" title="'+ic+'"><i class="fas '+ic+'"></i></button>'; }).join('');
            row.innerHTML =
                '<div class="crit-icpick">' +
                    '<button type="button" class="crit-ic-btn"><i class="fas '+row.dataset.ic+'"></i></button>' +
                    '<div class="crit-ic-pop">'+pop+'</div>' +
                '</div>' +
                '<input type="text" class="crit-label" placeholder="Nama kriteria" value="'+ String(item.label||'').replace(/"/g,'&quot;') +'">' +
                '<button type="button" class="crit-del" title="Hapus">&times;</button>';
            var icBtn = row.querySelector('.crit-ic-btn');
            var popEl = row.querySelector('.crit-ic-pop');
            icBtn.addEventListener('click', function(e){
                e.stopPropagation();
                document.querySelectorAll('.crit-ic-pop.open').forEach(function(p){ if(p!==popEl) p.classList.remove('open'); });
                popEl.classList.toggle('open');
            });
            popEl.querySelectorAll('.crit-ic-opt').forEach(function(opt){
                opt.addEventListener('click', function(){
                    row.dataset.ic = opt.getAttribute('data-ic');
                    icBtn.innerHTML = '<i class="fas '+row.dataset.ic+'"></i>';
                    popEl.classList.remove('open');
                    sync();
                });
            });
            row.querySelector('.crit-label').addEventListener('input', sync);
            row.querySelector('.crit-del').addEventListener('click', function(){ row.remove(); sync(); });
            return row;
        }
        function render(){ list.innerHTML=''; parse().forEach(function(it){ list.appendChild(makeRow(it)); }); sync(); }
        if(addBtn){ addBtn.addEventListener('click', function(){ list.appendChild(makeRow({label:'', ic:icons[0]})); sync(); }); }
        render();
    }
    document.querySelectorAll('.crit-mgr').forEach(initCritMgr);
    document.addEventListener('click', function(){ document.querySelectorAll('.crit-ic-pop.open').forEach(function(p){ p.classList.remove('open'); }); });

    // ===== Per-content media manager (Hero / Kategori / Galeri / Sponsor) =====
    function initMediaMgr(mgr){
        var type = mgr.dataset.type;
        var grid = mgr.querySelector('.media-grid');
        var store = mgr.querySelector('.media-store');
        var fileInput = mgr.querySelector('.media-file');
        var btn = mgr.querySelector('.media-upload-btn');
        var status = mgr.querySelector('.media-status');
        var defaultStatus = status ? status.textContent : '';
        var fieldDefs = type==='kategori' ? [{ph:'Judul'},{ph:'Deskripsi'}]
                      : type==='sponsor'  ? [{ph:'Nama Sponsor'}]
                      : [];
        function parseLines(){
            return (store.value||'').split(/\r?\n/).map(function(l){return l.trim();}).filter(Boolean)
                .map(function(l){ return l.split('|').map(function(p){return p.trim();}); });
        }
        function makeCard(parts){
            var url = parts.length ? parts[parts.length-1] : '';
            var texts = fieldDefs.map(function(_,i){ return parts[i]||''; });
            var card = document.createElement('div');
            card.className='media-card';
            card.dataset.url = url;
            var html = '<button type="button" class="media-del" title="Hapus">&times;</button>';
            html += '<div class="media-thumb" style="background-image:url(\''+ String(url).replace(/'/g,"%27") +'\')"></div>';
            if(fieldDefs.length){
                html += '<div class="media-fields">';
                fieldDefs.forEach(function(f,i){
                    html += '<input type="text" class="media-input" placeholder="'+f.ph+'" value="'+ String(texts[i]||'').replace(/"/g,'&quot;') +'">';
                });
                html += '</div>';
            }
            card.innerHTML = html;
            card.querySelector('.media-del').addEventListener('click', function(){ card.remove(); sync(); });
            card.querySelectorAll('.media-input').forEach(function(inp){ inp.addEventListener('input', sync); });
            return card;
        }
        function sync(){
            var lines=[];
            grid.querySelectorAll('.media-card').forEach(function(card){
                var url = card.dataset.url || '';
                if(!fieldDefs.length){ if(url) lines.push(url); }
                else {
                    var vals=[]; card.querySelectorAll('.media-input').forEach(function(i){ vals.push(i.value.trim()); });
                    vals.push(url); lines.push(vals.join(' | '));
                }
            });
            store.value = lines.join('\n');
        }
        function render(){ grid.innerHTML=''; parseLines().forEach(function(p){ grid.appendChild(makeCard(p)); }); sync(); }

        if(btn && fileInput){ btn.addEventListener('click', function(){ fileInput.click(); }); }
        if(fileInput){
            fileInput.addEventListener('change', function(){
                var files = Array.prototype.slice.call(fileInput.files);
                if(!files.length) return;
                var fd = new FormData();
                files.forEach(function(f){ fd.append('files[]', f); });
                if(status) status.textContent = 'Mengunggah...';
                fetch(fileInput.dataset.upload, { method:'POST', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}, body:fd })
                .then(function(r){ return r.ok ? r.json() : Promise.reject(r); })
                .then(function(d){
                    (d.urls||[]).forEach(function(u){ var parts = fieldDefs.map(function(){return '';}); parts.push(u); grid.appendChild(makeCard(parts)); });
                    sync();
                    showToast('Foto ditambahkan. Klik Simpan untuk menyimpan.', true);
                })
                .catch(function(){ showToast('Upload gagal.', false); })
                .finally(function(){ if(status) status.textContent = defaultStatus; fileInput.value=''; });
            });
        }
        render();
    }
    // ===== FAQ per-konten =====
    function initFaqMgr(mgr){
        var list = mgr.querySelector('.faq-list');
        var store = mgr.querySelector('.faq-store');
        var addBtn = mgr.querySelector('.faq-add');
        function clean(s){ return String(s||'').replace(/[\r\n]+/g,' ').replace(/\|/g,'/').trim(); }
        function parse(){
            return (store.value||'').split(/\r?\n/).map(function(l){return l.trim();}).filter(Boolean)
                .map(function(l){ var p=l.split('|').map(function(s){return s.trim();}); return { q:p[0]||'', a:p.slice(1).join(' / ')||'' }; });
        }
        function sync(){
            var lines=[];
            list.querySelectorAll('.faq-row').forEach(function(row){
                var q = clean(row.querySelector('.faq-q').value);
                var a = clean(row.querySelector('.faq-a').value);
                if(q || a) lines.push(q + ' | ' + a);
            });
            store.value = lines.join('\n');
        }
        function makeRow(item){
            var row = document.createElement('div');
            row.className='faq-row';
            row.innerHTML =
                '<button type="button" class="faq-del" title="Hapus">&times;</button>' +
                '<input type="text" class="faq-q" placeholder="Pertanyaan">' +
                '<textarea class="faq-a" placeholder="Jawaban" rows="2"></textarea>';
            row.querySelector('.faq-q').value = item.q || '';
            row.querySelector('.faq-a').value = item.a || '';
            row.querySelector('.faq-q').addEventListener('input', sync);
            row.querySelector('.faq-a').addEventListener('input', sync);
            row.querySelector('.faq-del').addEventListener('click', function(){ row.remove(); sync(); });
            return row;
        }
        function render(){ list.innerHTML=''; parse().forEach(function(it){ list.appendChild(makeRow(it)); }); sync(); }
        if(addBtn){ addBtn.addEventListener('click', function(){ list.appendChild(makeRow({q:'',a:''})); sync(); }); }
        render();
    }
    document.querySelectorAll('.faq-mgr').forEach(initFaqMgr);
    document.querySelectorAll('.media-mgr').forEach(initMediaMgr);
</script>

</body>
</html>