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
                    <form action="{{ route('admin.website.update') }}" method="POST" enctype="multipart/form-data">
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
                                <label class="form-label">Tanggal Hitung Mundur</label>
                                <input type="datetime-local" name="date" class="form-control" value="{{ $contents['hero_date'] ?? '2026-08-17T08:00' }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Media Background (Foto / Video)</label>
                                <input type="file" name="hero_media_file" class="form-control" accept="image/*,video/*" style="padding: 10px;">
                                <p style="font-size: 11px; color: var(--text-low); margin-top: 6px;">Kosongkan jika tidak ingin mengubah media. Bisa upload Gambar (JPG/PNG) atau Video (MP4).</p>
                                @if(!empty($contents['hero_media']))
                                    <div style="margin-top: 12px; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 10px;">
                                        <p style="font-size: 11px; color: var(--cyan-300); margin-bottom: 8px;"><i class="fas fa-image"></i> Media Aktif:</p>
                                        @if(\Illuminate\Support\Str::contains($contents['hero_media'], ['.mp4', '.webm']))
                                            <video src="{{ $contents['hero_media'] }}" controls style="width:180px;border-radius:8px;"></video>
                                        @else
                                            <img src="{{ $contents['hero_media'] }}" alt="Media" style="width:180px;border-radius:8px;">
                                        @endif
                                    </div>
                                @endif
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
                                <label class="form-label">URL Google Maps Lokasi (Embed)</label>
                                <input type="text" name="map_url" class="form-control" value="{{ $contents['about_map_url'] ?? '' }}">
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Google Maps → <b>Share</b> → <b>Embed a map</b> → salin <b>URL di dalam <code>src</code></b> ATAU seluruh kode <code>&lt;iframe&gt;</code>. URL share biasa (maps.app.goo.gl) TIDAK bisa ditampilkan di iframe.</p>
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

            <!-- FORM SPONSOR -->
            <section class="page-section hidden" id="sponsor">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="sponsor">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-handshake"></i></span> Sponsor</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Upload Foto Sponsor (opsional, bisa banyak)</label>
                                <input type="file" name="sponsor_files[]" class="form-control" accept="image/*" multiple style="padding:10px;">
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Tiap foto menambah baris <code>Sponsor | URL</code>. Foto tampil sebagai <b>background di belakang teks</b>. Ganti kata "Sponsor" dengan nama aslinya di kotak bawah.</p>
                            </div>
                            @php
                                $spPrev = collect(preg_split('/\r\n|\r|\n/', (string)($contents['sponsor_list'] ?? '')))
                                    ->map(fn($l)=>array_pad(array_map('trim', explode('|',$l)),2,''))
                                    ->filter(fn($x)=>$x[0]!=='' || $x[1]!=='');
                            @endphp
                            @if($spPrev->count())
                                <div class="form-group">
                                    <label class="form-label">Sponsor Saat Ini</label>
                                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                                        @foreach($spPrev as $sp)
                                            <div style="width:120px;height:70px;border-radius:10px;border:1px solid var(--bd-2);display:flex;align-items:center;justify-content:center;text-align:center;font-size:11px;font-weight:700;color:#fff;overflow:hidden;position:relative;@if(!empty($sp[1]))background:url('{{ $sp[1] }}') center/cover;@else background:var(--glass-2);@endif">
                                                <span style="position:relative;z-index:1;text-shadow:0 1px 6px rgba(0,0,0,.85);padding:4px;">{{ $sp[0] ?: 'Sponsor' }}</span>
                                                @if(!empty($sp[1]))<span style="position:absolute;inset:0;background:rgba(4,7,15,.45);"></span>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="form-label">Daftar Sponsor</label>
                                <textarea name="list" class="form-control" style="min-height:140px">{{ $contents['sponsor_list'] ?? '' }}</textarea>
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Format: <b>Nama | URL Foto (opsional)</b>. Satu sponsor per baris.</p>
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
                                <label class="form-label">Daftar Pertanyaan</label>
                                <textarea name="list" class="form-control" style="min-height:160px">{{ $contents['faq_list'] ?? '' }}</textarea>
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Satu FAQ per baris, format: <b>Pertanyaan | Jawaban</b><br>Contoh: <code>Bagaimana cara mendaftar? | Melalui panitia secara online.</code></p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

        <!-- FORM KATEGORI -->
            <section class="page-section hidden" id="kategori">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST" enctype="multipart/form-data">
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
                                <label class="form-label">Upload Foto Background Kategori (opsional, bisa banyak)</label>
                                <input type="file" name="kategori_files[]" class="form-control" accept="image/*" multiple style="padding:10px;">
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Tiap foto menambah <b>baris kategori baru</b> dengan URL foto di kolom ke-3. Lalu edit Judul/Deskripsi-nya di kotak bawah.</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Daftar Kategori</label>
                                <textarea name="list" class="form-control" style="min-height:160px">{{ $contents['kategori_list'] ?? '' }}</textarea>
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Format: <b>Judul | Deskripsi | URL Foto (opsional)</b><br>Contoh: <code>Golden Base | Standar mutu mutiara &amp; kepala. | https://.../foto.jpg</code></p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Keterangan Kriteria (tulisan kecil)</label>
                                <input type="text" name="criteria_note" class="form-control" value="{{ $contents['kategori_criteria_note'] ?? 'Empat aspek utama yang menjadi fokus penilaian juri pada setiap ikan.' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kriteria Penilaian (klik icon untuk menambah)</label>
                                <div class="icon-pick" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                                    @php $picks = ['fa-gem','fa-circle-dot','fa-palette','fa-ruler-combined','fa-fish','fa-star','fa-crown','fa-award','fa-eye','fa-shield-halved','fa-bolt','fa-heart']; @endphp
                                    @foreach($picks as $p)
                                        <button type="button" data-ic="{{ $p }}" title="{{ $p }}" style="width:38px;height:38px;border-radius:10px;border:1px solid var(--bd-2);background:var(--glass-2);color:var(--cyan-300);cursor:pointer;font-size:14px;"><i class="fas {{ $p }}"></i></button>
                                    @endforeach
                                </div>
                                <textarea id="criteriaBox" name="criteria" class="form-control" style="min-height:120px">{{ $contents['kategori_criteria'] ?? "Mutiara | fa-gem\nKepala (Kok) | fa-circle-dot\nWarna | fa-palette\nProporsi | fa-ruler-combined" }}</textarea>
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Satu kriteria per baris, format: <b>Label | nama-icon</b>. Klik icon di atas untuk menambah baris otomatis, lalu ubah labelnya.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FORM GALERI -->
            <section class="page-section hidden" id="gallery">
                <div class="glass-card">
                    <form action="{{ route('admin.website.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="gallery">
                        <div class="card-head">
                            <h3><span class="ti"><i class="fas fa-images"></i></span> Galeri Foto</h3>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Upload Foto (bisa pilih banyak sekaligus)</label>
                                <input type="file" name="gallery_files[]" class="form-control" accept="image/*" multiple style="padding:10px;">
                                <p style="font-size:11px;color:var(--text-low);margin-top:6px;">Foto yang di-upload akan <b>ditambahkan</b> ke daftar di bawah.</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Daftar Foto (satu URL per baris — hapus baris untuk menghapus foto)</label>
                                <textarea name="images" class="form-control" style="min-height:160px">{{ $contents['gallery_images'] ?? '' }}</textarea>
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

    // ===== Simpan via AJAX: tanpa refresh, tombol loading =====
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
            .then(d => {
                showToast(d.message || 'Berhasil disimpan!', true);
                form.querySelectorAll('input[type="file"]').forEach(f => f.value = '');
            })
            .catch(() => showToast('Gagal menyimpan. Coba lagi.', false))
            .finally(() => { if(btn){ btn.disabled = false; btn.innerHTML = original; } });
        });
    });
    // ===== Pemilih icon untuk Kriteria =====
    document.querySelectorAll('.icon-pick [data-ic]').forEach(function(b){
        b.addEventListener('click', function(){
            var ta = document.getElementById('criteriaBox');
            if(!ta) return;
            var line = 'Kriteria Baru | ' + b.getAttribute('data-ic');
            ta.value = ta.value.replace(/\s+$/,'') ? (ta.value.replace(/\s+$/,'') + '\n' + line) : line;
            ta.focus();
        });
    });
</script>

<div id="toastWrap"></div>
</body>
</html>