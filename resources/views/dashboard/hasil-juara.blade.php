<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <meta name="theme-color" content="#0B1220">
    <title>Hasil Juara — LCI Suite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        :root{
            --ocean-950:#04070F;--ocean-900:#0B1220;--ocean-850:#0E1729;--ocean-800:#111E36;--ocean-700:#182947;
            --royal-600:#2563EB;--royal-500:#3B82F6;--cyan-500:#06B6D4;--cyan-400:#22D3EE;--cyan-300:#67E8F9;
            --gold-700:#B45309;--gold-600:#D97706;--gold-500:#F59E0B;--gold-400:#FBBF24;--gold-300:#FCD34D;
            --glass-1:rgba(255,255,255,.03);--glass-2:rgba(255,255,255,.05);--glass-3:rgba(255,255,255,.08);--glass-strong:rgba(255,255,255,.12);
            --bd-1:rgba(255,255,255,.06);--bd-2:rgba(255,255,255,.10);--bd-3:rgba(255,255,255,.16);
            --bd-cyan:rgba(34,211,238,.25);--bd-gold:rgba(245,158,11,.30);
            --text-hi:#F8FAFC;--text:#E2E8F0;--text-mid:#94A3B8;--text-low:#64748B;--text-faint:#475569;
            --success:#10B981;--danger:#EF4444;--warning:#F59E0B;--purple:#A855F7;
            --success-glow:rgba(16,185,129,.35);
        }
        html,body{height:100%}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--ocean-900);color:var(--text);min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased}
        body::before{content:'';position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(37,99,235,.14) 0%,transparent 55%),radial-gradient(ellipse 50% 50% at 100% 100%,rgba(6,182,212,.08) 0%,transparent 60%),radial-gradient(ellipse 40% 40% at 0% 70%,rgba(29,78,216,.08) 0%,transparent 60%),linear-gradient(180deg,var(--ocean-950) 0%,var(--ocean-900) 45%,var(--ocean-850) 100%)}
        .app-shell{position:relative;z-index:10;min-height:100vh;display:flex;flex-direction:column}
        .topnav{display:flex;align-items:center;justify-content:space-between;padding:18px 32px;background:rgba(11,18,32,.72);backdrop-filter:blur(14px);border-bottom:1px solid var(--bd-1);position:sticky;top:0;z-index:100}
        .brand{display:flex;align-items:center;gap:14px;min-width:0}
        .brand-mark{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;background:radial-gradient(circle at 30% 30%,rgba(34,211,238,.5),transparent 60%),linear-gradient(135deg,var(--royal-600) 0%,var(--cyan-500) 100%);box-shadow:0 6px 18px -6px rgba(6,182,212,.55),inset 0 1px 0 rgba(255,255,255,.25);flex-shrink:0}
        .brand-mark svg{width:24px;height:24px;color:white}
        .brand-text h1{font-family:'Fraunces',serif;font-weight:600;font-size:19px;letter-spacing:-.02em;color:var(--text-hi);line-height:1.05}
        .brand-text h1 em{font-style:italic;font-weight:400;color:var(--cyan-400)}
        .brand-text p{font-size:11px;color:var(--text-mid);margin-top:2px;letter-spacing:.04em;text-transform:uppercase;font-weight:600}
        .nav-user{display:flex;align-items:center;gap:14px}
        .user-card{display:flex;align-items:center;gap:12px;padding:6px 10px 6px 6px;border:1px solid var(--bd-2);border-radius:999px;background:var(--glass-2)}
        .avatar{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,var(--gold-500),var(--gold-700));color:white;font-weight:800;font-size:14px;box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 4px 10px -2px rgba(245,158,11,.4);letter-spacing:0}
        .user-info{text-align:left;line-height:1.1;padding-right:4px}
        .user-info h4{font-size:13px;font-weight:700;color:var(--text-hi)}
        .user-info span{font-size:10.5px;color:var(--text-mid);letter-spacing:.04em;text-transform:uppercase;font-weight:600}
        .btn-nav{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);border-radius:12px;font-size:12px;font-weight:700;letter-spacing:.02em;cursor:pointer;transition:all .2s;text-decoration:none;font-family:inherit}
        .btn-nav:hover{background:rgba(34,211,238,.12);color:var(--cyan-300);border-color:var(--bd-cyan)}
        .btn-nav.gold{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.30);color:var(--gold-300)}
        .btn-nav.gold:hover{background:rgba(245,158,11,.25);color:#fff}
        .btn-logout{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;background:var(--glass-2);border:1px solid var(--bd-2);color:var(--text-mid);border-radius:12px;font-size:12px;font-weight:700;letter-spacing:.02em;cursor:pointer;transition:all .2s;text-decoration:none;font-family:inherit}
        .btn-logout:hover{background:rgba(239,68,68,.12);color:#fca5a5;border-color:rgba(239,68,68,.35)}
        .main-wrap{flex:1;padding:32px;max-width:1100px;margin:0 auto;width:100%}
        .glass-card{background:linear-gradient(180deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.02) 100%);border:1px solid var(--bd-1);border-radius:24px;position:relative;overflow:hidden;box-shadow:0 30px 60px -30px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.04);backdrop-filter:blur(12px)}
        .glass-card::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(180deg,rgba(255,255,255,.05) 0%,transparent 30%);pointer-events:none}
        .card-header{padding:22px 26px 0;display:flex;justify-content:space-between;align-items:flex-start;gap:16px;position:relative}
        .card-title{font-size:16px;font-weight:800;color:var(--text-hi);letter-spacing:-.01em;display:flex;align-items:center;gap:10px}
        .card-title .title-icon{width:32px;height:32px;border-radius:10px;display:grid;place-items:center;font-size:14px}
        .card-subtitle{font-size:11.5px;color:var(--text-mid);margin-top:6px;font-weight:500;max-width:90%;line-height:1.5}
        .card-body{padding:22px 26px 26px;position:relative}
        .empty-state{text-align:center;padding:38px 20px;color:var(--text-low)}
        .empty-state i{font-size:30px;margin-bottom:8px;display:block;opacity:.4}
        .empty-state p{font-size:12px}
        .result-item{background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.2);border-radius:14px;padding:16px 18px;margin-bottom:12px;transition:all .2s}
        .result-item:hover{border-color:rgba(16,185,129,.35);background:rgba(16,185,129,.08)}
        .result-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px}
        .result-kategori{font-size:12px;font-weight:800;color:#6EE7B7;display:inline-flex;align-items:center;gap:6px}
        .result-juara{display:inline-flex;align-items:center;gap:6px;font-weight:900;font-size:15px}
        .result-juara.j1{color:#FFD700}
        .result-juara.j2{color:#C0C0C0}
        .result-juara.j3{color:#CD7F32}
        .result-juara.j4plus{color:#6EE7B7}
        .result-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px}
        .result-grid .rg-label{color:var(--text-low);font-weight:600;font-size:11px}
        .result-grid .rg-val{font-weight:700;color:var(--text-hi);margin-top:2px}
        .result-grid .rg-val.gold{color:var(--gold-300)}
        .result-grid .rg-val.cyan{color:var(--cyan-300)}
        .back-link{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--text-mid);cursor:pointer;transition:color .2s;text-decoration:none;margin-bottom:20px}
        .back-link:hover{color:var(--cyan-300)}
        @keyframes fadeIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
        .fade-in{animation:fadeIn .5s cubic-bezier(.16,1,.3,1) both}
        @media(max-width:768px){
            .topnav{padding:12px 16px;gap:10px;flex-wrap:wrap;align-items:flex-start}
            .nav-user{flex-direction:column;align-items:flex-end;gap:7px;flex-shrink:0}
            .main-wrap{padding:18px 16px}
            .result-grid{grid-template-columns:1fr}
        }
        @media(max-width:480px){
            .main-wrap{padding:14px 12px}
            .card-header{padding:18px 20px 0}
            .card-body{padding:18px 20px 22px}
            .brand-text h1{font-size:15px}
            .brand-text p{display:none}
        }
        .result-filter-wrap{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
            margin-bottom:18px;
        }
        .result-filter-card{
            background:rgba(255,255,255,.035);
            border:1px solid var(--bd-1);
            border-radius:14px;
            padding:12px;
        }
        .result-filter-card label{
            display:block;
            font-size:10px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.08em;
            color:var(--text-low);
            margin-bottom:7px;
        }
        .result-filter-card select{
            width:100%;
            padding:10px 12px;
            border-radius:10px;
            border:1px solid var(--bd-2);
            background-color:#111E36;
            color:#F8FAFC;
            font-family:inherit;
            font-size:12px;
            font-weight:800;
            outline:none;
            appearance:auto;
        }

        .result-filter-card select:focus{
            border-color:rgba(34,211,238,.45);
            box-shadow:0 0 0 3px rgba(34,211,238,.10);
        }

        .result-filter-card select option{
            background-color:#111E36;
            color:#F8FAFC;
            font-weight:700;
        }

        .result-filter-card select option:hover,
        .result-filter-card select option:checked{
            background-color:#182947;
            color:#67E8F9;
        }
        .result-section-title{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            margin:18px 0 10px;
            padding-bottom:10px;
            border-bottom:1px solid var(--bd-1);
        }
        .result-section-title h3{
            font-size:14px;
            font-weight:900;
            color:var(--text-hi);
            display:flex;
            align-items:center;
            gap:8px;
        }
        .result-count-pill{
            font-size:10px;
            font-weight:900;
            color:var(--cyan-300);
            background:rgba(34,211,238,.10);
            border:1px solid rgba(34,211,238,.22);
            border-radius:999px;
            padding:5px 9px;
            white-space:nowrap;
        }
        .simple-result-card{
            background:rgba(255,255,255,.035);
            border:1px solid var(--bd-1);
            border-radius:16px;
            padding:14px;
            margin-bottom:12px;
        }
        .simple-result-card.mvp{
            background:rgba(245,158,11,.045);
            border-color:rgba(245,158,11,.22);
        }
        .simple-result-top{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:12px;
            margin-bottom:12px;
        }
        .simple-result-title{
            min-width:0;
        }
        .simple-result-title h4{
            font-size:14px;
            font-weight:900;
            color:var(--text-hi);
            margin-bottom:4px;
        }
        .simple-result-title p{
            font-size:11px;
            font-weight:700;
            color:var(--text-low);
            line-height:1.45;
        }
        .rank-badge{
            min-width:52px;
            padding:8px 10px;
            border-radius:12px;
            text-align:center;
            background:rgba(16,185,129,.10);
            border:1px solid rgba(16,185,129,.22);
        }
        .rank-badge .small{
            display:block;
            font-size:8px;
            font-weight:900;
            color:var(--text-low);
            text-transform:uppercase;
            letter-spacing:.08em;
            margin-bottom:2px;
        }
        .score-row{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:8px;
            margin-bottom:12px;
        }
        .score-box{
            background:rgba(0,0,0,.20);
            border:1px solid var(--bd-1);
            border-radius:12px;
            padding:9px 10px;
        }
        .score-box .label{
            font-size:9px;
            font-weight:900;
            color:var(--text-low);
            text-transform:uppercase;
            letter-spacing:.07em;
            margin-bottom:4px;
        }
        .score-box .value{
            font-size:15px;
            font-weight:900;
            color:var(--text-hi);
        }
        .score-box .value.gold{color:var(--gold-300)}
        .score-box .value.cyan{color:var(--cyan-300)}
        .component-grid{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:7px;
        }
        .component-chip{
            background:rgba(255,255,255,.035);
            border:1px solid var(--bd-1);
            border-radius:10px;
            padding:8px 9px;
            display:flex;
            justify-content:space-between;
            gap:8px;
            align-items:center;
        }
        .component-chip span:first-child{
            font-size:10px;
            font-weight:800;
            color:var(--text-mid);
        }
        .component-chip span:last-child{
            font-size:12px;
            font-weight:900;
            color:var(--cyan-300);
        }
        .bonus-line{
            margin-top:10px;
            padding-top:10px;
            border-top:1px dashed var(--bd-1);
            font-size:11px;
            color:var(--text-mid);
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            align-items:center;
        }
        @media(max-width:768px){
            .result-filter-wrap{grid-template-columns:1fr}
            .score-row{grid-template-columns:repeat(2,1fr)}
            .component-grid{grid-template-columns:repeat(2,1fr)}
        }
</style>
</head>
<body>
    <div class="app-shell">
        <nav class="topnav">
            <div class="brand">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 12c0-3 4-7 10-7 5 0 7.5 3 8.5 5l1.5-1v6l-1.5-1c-1 2-3.5 5-8.5 5-6 0-10-4-10-7z" stroke="white" stroke-width="1.5" stroke-linejoin="round" fill="rgba(255,255,255,0.15)"/>
                        <circle cx="16" cy="10.5" r="1" fill="white"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <h1>LCI <em>Suite</em></h1>
                    <p>Hasil Juara</p>
                </div>
            </div>
            <div class="nav-user">
                <a href="{{ route('dashboard') }}" class="btn-nav"><i class="fas fa-arrow-left"></i> Kembali</a>
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
            <div class="glass-card fade-in" style="border-color:rgba(16,185,129,.25)!important;background:linear-gradient(180deg,rgba(16,185,129,.06) 0%,rgba(16,185,129,.02) 100%);">
                <div class="card-header">
                    <div>
                        <h2 class="card-title" style="color:#6EE7B7;">
                            <span class="title-icon" style="background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.30);color:#34D399;"><i class="fas fa-trophy"></i></span>
                            Pengumuman Hasil Juara
                        </h2>
                        <p class="card-subtitle">Hasil penjurian ikan Anda yang telah dikunci oleh Grand Juri.</p>
                    </div>
                </div>
                <div class="card-body" id="hasilJuaraBody">
                    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat hasil...</p></div>
                </div>
            </div>
        </main>
    </div>

<script>
    var hasilData = {
        results: [],
        mvp: [],
        activeResultGroup: 'all',
        activeMvpGroup: 'all'
    };

    function escapeHtml(str){
        if(str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    function formatNumber(n){
        n = parseFloat(n || 0);
        if(Number.isInteger(n)) return String(n);
        return n.toFixed(2).replace(/\.00$/, '');
    }

    function formatJuara(pos){
        pos = parseInt(pos || 0, 10);

        if(pos === 1){
            return '<span class="result-juara j1"><i class="fas fa-medal" style="color:#FFD700;font-size:16px;"></i> 1</span>';
        }
        if(pos === 2){
            return '<span class="result-juara j2"><i class="fas fa-medal" style="color:#C0C0C0;font-size:16px;"></i> 2</span>';
        }
        if(pos === 3){
            return '<span class="result-juara j3"><i class="fas fa-medal" style="color:#CD7F32;font-size:16px;"></i> 3</span>';
        }

        return '<span class="result-juara j4plus">' + (pos > 0 ? pos : '-') + '</span>';
    }

    function groupKey(item){
        return item.group_key || ((item.kategori || '-') + '|' + (item.kelas || '-'));
    }

    function groupLabel(item){
        if(item.group_label) return item.group_label;

        var kategori = item.kategori || '-';
        var kelas = item.kelas || '-';

        if(kategori === 'Bonsai' || kategori === 'Jumbo' || kelas === '-' || kelas === ''){
            return kategori;
        }

        return kategori + ' - Kelas ' + kelas;
    }

    function uniqueGroups(items){
        var map = {};

        items.forEach(function(item){
            map[groupKey(item)] = groupLabel(item);
        });

        return Object.keys(map)
            .map(function(k){ return {key:k, label:map[k]}; })
            .sort(function(a,b){ return a.label.localeCompare(b.label); });
    }

    function formatBonusList(list){
        list = Array.isArray(list) ? list : [];

        if(list.length === 0){
            return '<span style="color:var(--text-low);font-weight:700;">Tidak ada bonus</span>';
        }

        var labels = {
            best_of_the_best: 'BEST OF THE BEST',
            best_of_show: 'BEST OF SHOW',
            grand_champion: 'GRAND CHAMPION',
            young_champion: 'YOUNG CHAMPION',
            junior: 'JUNIOR',
            baby_champion: 'BABY CHAMPION',
            mini_champion: 'MINI CHAMPION'
        };

        return list.map(function(b){
            return '<span style="display:inline-block;margin:2px 4px 2px 0;padding:3px 7px;border-radius:6px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);color:#6EE7B7;font-size:10px;font-weight:800;">'
                + escapeHtml(labels[b] || b) +
            '</span>';
        }).join('');
    }

    function renderComponentSubtotals(subtotals){
        var order = ['overall','head','face','body','marking','pearl','color','finnage'];

        subtotals = subtotals || {};

        return '<div class="component-grid">' + order.map(function(k){
            var item = subtotals[k] || {};
            var label = item.label || k;
            var value = item.value || 0;

            return '<div class="component-chip">' +
                '<span>' + escapeHtml(label) + '</span>' +
                '<span>' + formatNumber(value) + '</span>' +
            '</div>';
        }).join('') + '</div>';
    }

    function buildFilters(){
        var resultGroups = uniqueGroups(hasilData.results);
        var mvpGroups = uniqueGroups(hasilData.mvp);

        var html = '';

        html += '<div class="result-filter-wrap">';

        html += '<div class="result-filter-card">';
        html += '<label><i class="fas fa-trophy"></i> Filter Hasil Juara</label>';
        html += '<select id="filterHasilGroup" onchange="onFilterHasilChange()">';
        html += '<option value="all">Semua Kategori & Kelas</option>';
        resultGroups.forEach(function(g){
            html += '<option value="'+escapeHtml(g.key)+'">'+escapeHtml(g.label)+'</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '<div class="result-filter-card">';
        html += '<label><i class="fas fa-star"></i> Filter MVP</label>';
        html += '<select id="filterMvpGroup" onchange="onFilterMvpChange()">';
        html += '<option value="all">Semua Kategori & Kelas MVP</option>';
        mvpGroups.forEach(function(g){
            html += '<option value="'+escapeHtml(g.key)+'">'+escapeHtml(g.label)+'</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '</div>';

        return html;
    }

    function onFilterHasilChange(){
        hasilData.activeResultGroup = document.getElementById('filterHasilGroup').value || 'all';
        renderPage();
    }

    function onFilterMvpChange(){
        hasilData.activeMvpGroup = document.getElementById('filterMvpGroup').value || 'all';
        renderPage();
    }

    function renderResultCard(r){
        var totalBonus = parseFloat(r.total_bonus || 0);
        var bonusHtml = '';

        if(totalBonus > 0 || (Array.isArray(r.bonus_list) && r.bonus_list.length > 0)){
            bonusHtml =
                '<div class="bonus-line">' +
                    '<b style="color:var(--text-hi);">Keterangan Bonus:</b> ' +
                    formatBonusList(r.bonus_list) +
                    '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:999px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);color:var(--gold-300);font-size:10px;font-weight:900;">+' + formatNumber(totalBonus) + ' point</span>' +
                '</div>';
        }

        return '<div class="simple-result-card">' +
            '<div class="simple-result-top">' +
                '<div class="simple-result-title">' +
                    '<h4>Tank ' + escapeHtml(r.nomor_tank || '-') + ' · ' + escapeHtml(groupLabel(r)) + '</h4>' +
                    '<p>' + escapeHtml(r.nama_peserta || '-') + ' · ' + escapeHtml(r.detail_anggota || '-') + '</p>' +
                '</div>' +
                '<div class="rank-badge">' +
                    '<span class="small">Juara</span>' +
                    formatJuara(r.position) +
                '</div>' +
            '</div>' +

            '<div class="score-row">' +
                '<div class="score-box"><div class="label">Total Point</div><div class="value cyan">'+formatNumber(r.point || 0)+'</div></div>' +
                '<div class="score-box"><div class="label">Rank Point</div><div class="value gold">'+formatNumber(r.rank_point || 0)+'</div></div>' +
                '<div class="score-box"><div class="label">Bonus</div><div class="value">'+formatNumber(totalBonus)+'</div></div>' +
                '<div class="score-box"><div class="label">Final</div><div class="value gold">'+formatNumber((parseFloat(r.rank_point || 0) + totalBonus))+'</div></div>' +
            '</div>' +

            renderComponentSubtotals(r.component_subtotals) +
            bonusHtml +
        '</div>';
    }

    function renderMvpCard(m){
        return '<div class="simple-result-card mvp">' +
            '<div class="simple-result-top">' +
                '<div class="simple-result-title">' +
                    '<h4>Tank ' + escapeHtml(m.nomor_tank || '-') + ' · MVP · ' + escapeHtml(groupLabel(m)) + '</h4>' +
                    '<p>' + escapeHtml(m.nama_peserta || '-') + ' · ' + escapeHtml(m.detail_anggota || '-') + '</p>' +
                '</div>' +
                '<div class="rank-badge" style="background:rgba(245,158,11,.10);border-color:rgba(245,158,11,.25);">' +
                    '<span class="small">Posisi</span>' +
                    formatJuara(m.position) +
                '</div>' +
            '</div>' +

            '<div class="score-row">' +
                '<div class="score-box"><div class="label">Rank Point</div><div class="value gold">'+formatNumber(m.rank_point || 0)+'</div></div>' +
                '<div class="score-box"><div class="label">Bonus Point</div><div class="value cyan">+'+formatNumber(m.total_bonus || 0)+'</div></div>' +
                '<div class="score-box"><div class="label">Final MVP</div><div class="value gold">'+formatNumber(m.final_rank_point || 0)+'</div></div>' +
                '<div class="score-box"><div class="label">Kategori</div><div class="value">'+escapeHtml(groupLabel(m))+'</div></div>' +
            '</div>' +

            renderComponentSubtotals(m.component_subtotals) +

            '<div class="bonus-line">' +
                '<b style="color:var(--text-hi);">Detail Bonus:</b> ' + formatBonusList(m.bonus_list) +
            '</div>' +
        '</div>';
    }

    function renderEmptyState(data){
        var resultUnlocked = !!(data && data.result_unlocked);

        if(resultUnlocked){
            return '<div class="empty-state" style="padding:50px 20px;">'+
                '<div style="width:80px;height:80px;border-radius:50%;background:var(--glass-2);border:1px solid var(--bd-2);display:grid;place-items:center;margin:0 auto 16px;color:var(--warning);font-size:32px;"><i class="fas fa-circle-exclamation"></i></div>'+
                '<p style="font-size:14px;font-weight:700;color:var(--text-mid);margin-bottom:6px;">Akses Hasil Sudah Dibuka</p>'+
                '<p style="font-size:12px;color:var(--text-low);max-width:390px;margin:0 auto;line-height:1.6;">Tetapi belum ada ikan Anda yang memenuhi syarat hasil juara. Syaratnya: punya nomor tank, sudah dinilai, dan sudah dikunci/final oleh Grand Juri/Admin.</p>'+
            '</div>';
        }

        return '<div class="empty-state" style="padding:50px 20px;">'+
            '<div style="width:80px;height:80px;border-radius:50%;background:var(--glass-2);border:1px solid var(--bd-2);display:grid;place-items:center;margin:0 auto 16px;color:var(--text-low);font-size:32px;"><i class="fas fa-lock"></i></div>'+
            '<p style="font-size:14px;font-weight:700;color:var(--text-mid);margin-bottom:6px;">Hasil Belum Tersedia</p>'+
            '<p style="font-size:12px;color:var(--text-low);max-width:360px;margin:0 auto;line-height:1.6;">Hasil juara belum dibuka oleh panitia, atau Anda belum memiliki ikan yang dikunci oleh Grand Juri.</p>'+
        '</div>';
    }

    function renderPage(){
        var body = document.getElementById('hasilJuaraBody');

        var results = hasilData.results.slice();
        var mvp = hasilData.mvp.slice();

        if(hasilData.activeResultGroup !== 'all'){
            results = results.filter(function(r){ return groupKey(r) === hasilData.activeResultGroup; });
        }

        if(hasilData.activeMvpGroup !== 'all'){
            mvp = mvp.filter(function(r){ return groupKey(r) === hasilData.activeMvpGroup; });
        }

        var html = '';

        html += buildFilters();

        html += '<div class="result-section-title">';
        html += '<h3><i class="fas fa-trophy" style="color:#6EE7B7;"></i> Pengumuman Hasil Juara</h3>';
        html += '<span class="result-count-pill">'+results.length+' data</span>';
        html += '</div>';

        if(results.length > 0){
            html += results.map(renderResultCard).join('');
        } else {
            html += '<div class="empty-state" style="padding:26px 10px;"><i class="fas fa-folder-open"></i><p>Tidak ada hasil juara pada filter ini.</p></div>';
        }

        html += '<div class="result-section-title">';
        html += '<h3><i class="fas fa-star" style="color:var(--gold-300);"></i> Data MVP Team Anda</h3>';
        html += '<span class="result-count-pill">'+mvp.length+' MVP</span>';
        html += '</div>';

        if(mvp.length > 0){
            html += mvp.map(renderMvpCard).join('');
        } else {
            html += '<div class="empty-state" style="padding:26px 10px;"><i class="fas fa-star-half-stroke"></i><p>Tidak ada data MVP pada filter ini.</p></div>';
        }

        body.innerHTML = html;

        var f1 = document.getElementById('filterHasilGroup');
        var f2 = document.getElementById('filterMvpGroup');

        if(f1) f1.value = hasilData.activeResultGroup;
        if(f2) f2.value = hasilData.activeMvpGroup;
    }

    function loadHasilJuara(){
        var body = document.getElementById('hasilJuaraBody');

        fetch('/api/user/my-ikans?_t=' + Date.now(), {
            headers:{
                'Accept':'application/json',
                'X-Requested-With':'XMLHttpRequest'
            }
        })
        .then(function(r){
            if(r.status === 401){
                window.location.href='/login';
                return null;
            }

            if(!r.ok){
                return r.json().then(function(d){ throw d; });
            }

            return r.json();
        })
        .then(function(data){
            if(!data){
                body.innerHTML = '<div class="empty-state"><i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i><p style="color:var(--danger);">Gagal memuat data.</p></div>';
                return;
            }

            hasilData.results = Array.isArray(data.my_results) ? data.my_results : [];
            hasilData.mvp = Array.isArray(data.my_mvp_results) ? data.my_mvp_results : [];

            if(hasilData.results.length === 0 && hasilData.mvp.length === 0){
                body.innerHTML = renderEmptyState(data);
                return;
            }

            hasilData.activeResultGroup = 'all';
            hasilData.activeMvpGroup = 'all';

            renderPage();
        })
        .catch(function(e){
            console.error('loadHasilJuara error:', e);

            body.innerHTML =
                '<div class="empty-state">' +
                    '<i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i>' +
                    '<p style="color:var(--danger);font-weight:700;">Gagal memuat data hasil.</p>' +
                '</div>';
        });
    }

    loadHasilJuara();
</script>
</body>
</html>