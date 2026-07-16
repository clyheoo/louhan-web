/* ═══════════════════════════════════════════════
   GLOBAL LOADER UTILITY
   ═══════════════════════════════════════════════ */
function showLoader(msg){
    var el = document.getElementById('globalLoader');
    if(!el) return;
    var t = el.querySelector('.loader-text');
    if(t) t.textContent = msg || 'Memproses...';
    el.classList.add('show');
}
function hideLoader(){
    var el = document.getElementById('globalLoader');
    if(el) el.classList.remove('show');
}

/* ═══════════════════════════════════════════════
   CHART.JS DEFAULTS (DARK THEME)
   ═══════════════════════════════════════════════ */
if(window.Chart){
    Chart.defaults.color = '#94A3B8';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
}

var currentTankMax = 1000;
var kelasList = ['A','B','C','D','E'];
var allScoringData = [];
var chartKat, chartStat, chartTop;
var _confirmCallback = null;
var kelasRangeData = {};
var allKategoriList = ['Cencu','Chingwa','Freemarking','Goldenbase','Klasik','Bonsai','Jumbo'];
var noKelasKategori = ['Bonsai', 'Jumbo'];
var kategoriListWithKelas = allKategoriList.filter(function(k){ return noKelasKategori.indexOf(k) === -1; });

// ★ MODUL 2: sinkronkan daftar kategori & kelas dari DB (fallback ke nilai default di atas)
function refreshTaxonomyArrays(cb){
    fetch('/api/admin/taxonomy', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d && Array.isArray(d.categories) && d.categories.length){
            allKategoriList = d.categories.map(function(c){ return c.name; });
            noKelasKategori = d.categories.filter(function(c){ return !c.uses_kelas; }).map(function(c){ return c.name; });
            kategoriListWithKelas = allKategoriList.filter(function(k){ return noKelasKategori.indexOf(k) === -1; });
        }
        if(d && Array.isArray(d.classes) && d.classes.length){
            kelasList = d.classes.slice();
        }
        if(typeof cb === 'function') cb();
    })
    .catch(function(){ if(typeof cb === 'function') cb(); });
}
refreshTaxonomyArrays();

function closeModal(id){document.getElementById(id).classList.remove('show');}

function openModal(id){
    var el = document.getElementById(id);
    if(!el) return;
    el.classList.add('show');
        if(id === 'modalImport'){
        importSelectedFile = null;
        var fi = document.getElementById('importFileInput');
        if(fi) fi.value = '';
        var fl = document.getElementById('importFileLabel');
        if(fl) fl.innerHTML = 'Klik atau seret file ke sini';
        var dz = document.getElementById('importDropZone');
        if(dz){ dz.style.borderColor='var(--bd-2)'; dz.style.background='var(--glass-1)'; }
        var ac = document.getElementById('importAutoCreate');
        if(ac) ac.checked = false;
        var pw = document.getElementById('importPasswordWrap');
        if(pw) pw.style.display = 'none';
        var rb = document.getElementById('importResultBox');
        if(rb) rb.style.display = 'none';
    }
    if(id === 'modalCreate'){
        var form = document.getElementById('formCreateUser');
        if(form) form.reset();
        var cPwdEl = document.getElementById('createPwd');
        var cConfEl = document.getElementById('createPwdConf');
        if(cPwdEl) cPwdEl.classList.remove('input-error','input-success');
        if(cConfEl) cConfEl.classList.remove('input-error','input-success');
        var errPwd = document.getElementById('createPwdErr');
        var barPwd = document.getElementById('createStrBar');
        var txtPwd = document.getElementById('createStrText');
        var errEmail = document.getElementById('createEmailErr');
        var matchNo = document.getElementById('createMatchNo');
        var matchOk = document.getElementById('createMatchOk');
        if(errPwd) errPwd.style.display='none';
        if(barPwd) barPwd.style.display='none';
        if(txtPwd) txtPwd.style.display='none';
        if(errEmail) errEmail.style.display='none';
        if(matchNo) matchNo.style.display='none';
        if(matchOk) matchOk.style.display='none';
        var cSegs = [document.getElementById('cSeg1'),document.getElementById('cSeg2'),document.getElementById('cSeg3'),document.getElementById('cSeg4'),document.getElementById('cSeg5')];
        for(var i=0;i<cSegs.length;i++){ if(cSegs[i]) cSegs[i].className='str-seg'; }
    }
    if(id === 'modalOld'){
        loadPesertaOld();
        loadTankRange();
    }
}

function esc(s){if(!s)return '';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function getCsrf(){return document.querySelector('meta[name="csrf-token"]').getAttribute('content');}

document.querySelectorAll('.modal-bg').forEach(function(m){
    m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});
});

function loadTankRange(){
    document.getElementById('katLoading').style.display='block';
    document.getElementById('katContent').style.display='none';

    fetch('/api/tank-range?_t='+Date.now(),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        kelasRangeData = {};
        var allKeys = kelasList.concat(noKelasKategori);
        for(var i=0;i<allKeys.length;i++){
            var k = allKeys[i];
            if(d[k]){
                var katObj = {};
                if(d[k].kategori && typeof d[k].kategori === 'object'){
                    katObj = d[k].kategori;
                }
                kelasRangeData[k] = {kategori: katObj};
            } else {
                kelasRangeData[k] = {kategori: {}};
            }
        }
        populateKelasSelect();
        renderRangeSummary();
        loadGlobalRangeText();

        document.getElementById('katKelasSelect').value = '';
        document.getElementById('katGridWrap').style.display = 'none';
        document.getElementById('katEmptyState').style.display = 'block';
        hideKatError();

        document.getElementById('katLoading').style.display='none';
        document.getElementById('katContent').style.display='block';
    })
    .catch(function(err){
        console.error('loadTankRange error:', err);
        var msg = 'Gagal memuat pengaturan rentang.';
        try {
            if(err.message && typeof err.message === 'string') msg = err.message;
        } catch(ex){}
        document.getElementById('katLoading').innerHTML='<div style="color:var(--danger);font-size:12px;"><i class="fas fa-triangle-exclamation" style="margin-right:4px;"></i>'+esc(msg)+'</div>';
    });
}

function loadGlobalRangeText(){
    fetch('/api/tank-range-global?_t='+Date.now(),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        document.getElementById('katGlobalRangeText').textContent = d.min + ' \u2013 ' + d.max;
    })
    .catch(function(){
        document.getElementById('katGlobalRangeText').textContent = '1 \u2013 1000';
    });
}

function renderRangeSummary(){
    var el = document.getElementById('katSummaryContent');
    var wrap = document.getElementById('katSummaryWrap');
    var html = '';
    var totalKelas = 0;
    var totalKat = 0;
    var allKeys = kelasList.concat(noKelasKategori);

    var hasAnyRange = false;
    for(var ci=0; ci<allKeys.length; ci++){
        var ck = allKeys[ci];
        var ckKats = kelasRangeData[ck] ? kelasRangeData[ck].kategori || {} : {};
        if(Object.keys(ckKats).length > 0){ hasAnyRange = true; break; }
    }

    if(hasAnyRange){
        html = '<div style="display:flex;justify-content:flex-end;margin-bottom:10px;">' +
            '<button type="button" onclick="resetAllRanges()" style="padding:6px 12px;border:1px solid rgba(239,68,68,.5);border-radius:8px;background:rgba(239,68,68,.18);color:#F87171;font-family:inherit;font-size:10px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .2s;" onmouseover="this.style.background=\'var(--danger)\';this.style.color=\'#fff\'" onmouseout="this.style.background=\'rgba(239,68,68,.18)\';this.style.color=\'#F87171\'">' +
            '<i class="fas fa-rotate-left"></i> Reset Semua Rentang</button></div>';
    }

    for(var i=0; i<allKeys.length; i++){
        var k = allKeys[i];
        var kats = kelasRangeData[k] ? kelasRangeData[k].kategori || {} : {};
        var keys = Object.keys(kats);
        if(keys.length === 0) continue;

        totalKelas++;
        totalKat += keys.length;
        var isNoKelas = noKelasKategori.indexOf(k) !== -1;
        html += '<div style="margin-bottom:10px;display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:8px 10px;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.08);border-radius:9px;">';
        html += '<b style="color:#FCD34D;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;min-width:60px;">'+(isNoKelas ? k : 'Kelas '+k)+'</b>';
        html += '<span style="color:#94A3B8;font-size:11px;">→</span>';
        var parts = [];
        for(var j=0; j<keys.length; j++){
            var name = keys[j];
            var r = kats[name];
            parts.push('<span style="background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.25);border-radius:6px;padding:3px 9px;font-weight:700;font-size:11px;color:#FDE68A;display:inline-flex;align-items:center;gap:5px;">'+name+'<span style="color:#A5F3FC;font-weight:800;background:rgba(34,211,238,.15);padding:0 5px;border-radius:4px;">'+r.min+'\u2013'+r.max+'</span></span>');
        }
        html += parts.join('');
        html += '</div>';
    }

    if(!hasAnyRange){
        wrap.style.display = 'none';
        return;
    }

    if(totalKelas > 0){
        html = '<div style="margin-bottom:12px;color:#FDE68A;font-weight:700;font-size:12px;display:flex;align-items:center;gap:6px;"><i class="fas fa-chart-pie" style="color:var(--gold-400);"></i> <b style="color:#fff;">'+totalKelas+'</b> group terkonfigurasi, <b style="color:#fff;">'+totalKat+'</b> kategori memiliki rentang khusus.</div>' + html;
    }
    el.innerHTML = html;
    wrap.style.display = 'block';
}

function populateKelasSelect(){
    var sel = document.getElementById('katKelasSelect');
    var currentVal = sel.value;
    sel.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    for(var i=0;i<kelasList.length;i++){
        var k=kelasList[i], d=kelasRangeData[k];
        var katCount = d && d.kategori ? Object.keys(d.kategori).length : 0;
        var opt=document.createElement('option');
        opt.value=k;
        opt.textContent='Kelas '+k;
        if(katCount > 0) opt.textContent += ' ('+katCount+' kategori)';
        sel.appendChild(opt);
    }
    var sep=document.createElement('option');
    sep.disabled=true;
    sep.textContent='\u2500\u2500 Tanpa Kelas \u2500\u2500';
    sel.appendChild(sep);
    for(var i=0;i<noKelasKategori.length;i++){
        var nk=noKelasKategori[i];
        var d=kelasRangeData[nk];
        var hasR=d&&d.kategori&&d.kategori[nk];
        var opt=document.createElement('option');
        opt.value=nk;
        opt.textContent=nk+' (Tanpa Kelas)';
        if(hasR) opt.textContent+=' \u2714';
        sel.appendChild(opt);
    }
    if(currentVal) sel.value = currentVal;
}

/* ═══ SAAT KELAS DIPILIH ═══ */
function onKatKelasChange(){
    var k = document.getElementById('katKelasSelect').value;
    if(!k){
        document.getElementById('katGridWrap').style.display='none';
        document.getElementById('katEmptyState').style.display='block';
        return;
    }
    document.getElementById('katGridWrap').style.display='block';
    document.getElementById('katEmptyState').style.display='none';
    hideKatError();
    renderKategoriGrid(k);
    showExistingInfo(k);
}

/* ═══ TAMPILKAN INFO RENTANG YANG SUDAH ADA DI KELAS INI ═══ */
function showExistingInfo(kelas){
    var infoEl = document.getElementById('katExistingInfo');
    var textEl = document.getElementById('katExistingText');
    var kats = kelasRangeData[kelas].kategori || {};
    var keys = Object.keys(kats);

    if(keys.length === 0){
        infoEl.style.display = 'none';
        return;
    }

    var parts = [];
    for(var i=0; i<keys.length; i++){
        var name = keys[i];
        parts.push('<b>' + name + '</b> (' + kats[name].min + '\u2013' + kats[name].max + ')');
    }
    var isNoKelas = noKelasKategori.indexOf(kelas) !== -1;
    textEl.innerHTML = (isNoKelas ? '' : 'Kelas ') + kelas + ' saat ini sudah dikonfigurasi: ' + parts.join(', ') + '.';
    infoEl.style.display = 'block';
}

function renderKategoriGrid(kelas){
    var container = document.getElementById('katGrid');
    container.innerHTML='';
    var existing = kelasRangeData[kelas] ? kelasRangeData[kelas].kategori || {} : {};

    var katsToShow;
    if(kelas === 'Bonsai'){
        katsToShow = ['Bonsai'];
    } else if(kelas === 'Jumbo'){
        katsToShow = ['Jumbo'];
    } else {
        katsToShow = kategoriListWithKelas;
    }

    for(var i=0;i<katsToShow.length;i++){
        (function(name){
            var kat = existing[name] || null;
            var hasSub = kat && kat.min && kat.max;
            var card = document.createElement('div');
            card.id = 'kat_card_' + name;
            card.style.cssText='background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.015));border:1px solid rgba(255,255,255,.10);border-radius:12px;padding:14px 12px;text-align:center;transition:border-color .2s,box-shadow .2s,background .2s;';
            card.innerHTML=
                '<div style="font-size:11px;font-weight:800;color:#FCD34D;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">'+name+'</div>'+
                '<div style="display:flex;gap:6px;align-items:center;margin-bottom:10px;">'+
                    '<input type="number" id="kat_'+name+'_min" value="'+(hasSub?kat.min:'')+'" placeholder="Dari" style="width:100%;text-align:center;font-weight:700;padding:9px 6px;font-size:13px;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.14);border-radius:8px;color:#F8FAFC;outline:none;font-family:inherit;transition:all .2s;" oninput="onKatInputChange()" onfocus="this.style.borderColor=\'#22D3EE\';this.style.background=\'rgba(0,0,0,.4)\';this.style.boxShadow=\'0 0 0 3px rgba(34,211,238,.1)\'" onblur="if(!this.style.borderColor.includes(\'239\')){this.style.borderColor=\'rgba(255,255,255,.14)\';this.style.background=\'rgba(0,0,0,.28)\';this.style.boxShadow=\'\';}">'+
                    '<span style="font-weight:800;color:#FBBF24;font-size:13px;">\u2013</span>'+
                    '<input type="number" id="kat_'+name+'_max" value="'+(hasSub?kat.max:'')+'" placeholder="Sampai" style="width:100%;text-align:center;font-weight:700;padding:9px 6px;font-size:13px;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.14);border-radius:8px;color:#F8FAFC;outline:none;font-family:inherit;transition:all .2s;" oninput="onKatInputChange()" onfocus="this.style.borderColor=\'#22D3EE\';this.style.background=\'rgba(0,0,0,.4)\';this.style.boxShadow=\'0 0 0 3px rgba(34,211,238,.1)\'" onblur="if(!this.style.borderColor.includes(\'239\')){this.style.borderColor=\'rgba(255,255,255,.14)\';this.style.background=\'rgba(0,0,0,.28)\';this.style.boxShadow=\'\';}">'+
                '</div>'+
                '<div id="kat_hint_'+name+'" style="font-size:9.5px;color:#94A3B8;font-weight:600;transition:color .2s;">Kosongkan = pakai rentang global</div>';
            container.appendChild(card);
        })(katsToShow[i]);
    }
}

/* ═══ VALIDASI REAL-TIME SAAT INPUT BERUBAH ═══ */
var katInputTimer = null;
function onKatInputChange(){
    clearTimeout(katInputTimer);
    katInputTimer = setTimeout(function(){
        var kelas = document.getElementById('katKelasSelect').value;
        if(!kelas) return;
        validateAndHighlight(kelas);
    }, 300);
}

function validateAndHighlight(kelas){
    var checkKats = (kelas==='Bonsai') ? ['Bonsai'] : (kelas==='Jumbo') ? ['Jumbo'] : kategoriListWithKelas;
    for(var i=0; i<checkKats.length; i++){
        var name = checkKats[i];
        var card = document.getElementById('kat_card_' + name);
        var hint = document.getElementById('kat_hint_' + name);
        var minEl = document.getElementById('kat_' + name + '_min');
        var maxEl = document.getElementById('kat_' + name + '_max');
        if(card){ card.style.borderColor='rgba(255,255,255,.10)'; card.style.boxShadow='none'; card.style.background='linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.015))'; }
        if(hint){ hint.style.color='#94A3B8'; hint.textContent='Kosongkan = pakai rentang global'; }
        if(minEl){ minEl.style.borderColor='rgba(255,255,255,.14)'; minEl.style.boxShadow=''; }
        if(maxEl){ maxEl.style.borderColor='rgba(255,255,255,.14)'; maxEl.style.boxShadow=''; }
    }

    for(var i=0; i<checkKats.length; i++){
        var name = checkKats[i];
        var minEl = document.getElementById('kat_' + name + '_min');
        var maxEl = document.getElementById('kat_' + name + '_max');
        if(!minEl || !maxEl) continue;
        var mv = minEl.value.trim(), xv = maxEl.value.trim();
        if(mv === '' && xv === '') continue;
        if(mv === '' || xv === ''){ highlightCardError(name, 'Isi kedua angka atau kosongkan semua'); continue; }
        mv = parseInt(mv); xv = parseInt(xv);
        if(isNaN(mv) || isNaN(xv) || mv < 1 || xv < 1){ highlightCardError(name, 'Nomor harus lebih dari 0'); continue; }
        if(xv < mv){ highlightCardError(name, 'Angka akhir harus \u2265 angka awal'); continue; }
    }

    var inputRanges = gatherInputRanges(kelas);
    if(inputRanges.length === 0){ hideKatError(); return; }

    var errors = validateRanges(kelas, inputRanges);

    var highlightKats = {};
    for(var e=0; e<errors.length; e++){
        for(var c=0; c<errors[e].kategori.length; c++){
            if(document.getElementById('kat_card_' + errors[e].kategori[c])){
                highlightKats[errors[e].kategori[c]] = true;
            }
        }
    }
    var hKeys = Object.keys(highlightKats);
    for(var i=0; i<hKeys.length; i++){
        highlightCardError(hKeys[i], 'Menyentuh batas kategori lain!');
    }

    if(errors.length > 0) showKatError(errors);
    else hideKatError();
}

function highlightCardError(name, msg){
    var card = document.getElementById('kat_card_' + name);
    var hint = document.getElementById('kat_hint_' + name);
    var minEl = document.getElementById('kat_' + name + '_min');
    var maxEl = document.getElementById('kat_' + name + '_max');
    if(card){ card.style.borderColor='rgba(239,68,68,.55)'; card.style.boxShadow='0 0 0 2px rgba(239,68,68,.12)'; card.style.background='rgba(239,68,68,.06)'; }
    if(hint){ hint.style.color='#FCA5A5'; hint.textContent=msg; }
    if(minEl){ minEl.style.borderColor='rgba(239,68,68,.65)'; }
    if(maxEl){ maxEl.style.borderColor='rgba(239,68,68,.65)'; }
}

function gatherInputRanges(kelas){
    var ranges = [];
    var checkKats = (kelas==='Bonsai') ? ['Bonsai'] : (kelas==='Jumbo') ? ['Jumbo'] : kategoriListWithKelas;
    for(var i=0; i<checkKats.length; i++){
        var name = checkKats[i];
        var minEl = document.getElementById('kat_' + name + '_min');
        var maxEl = document.getElementById('kat_' + name + '_max');
        if(!minEl || !maxEl) continue;

        var mv = minEl.value.trim(), xv = maxEl.value.trim();
        if(mv === '' && xv === '') continue;

        mv = parseInt(mv); xv = parseInt(xv);
        if(isNaN(mv) || isNaN(xv) || mv < 1 || xv < 1) continue;

        ranges.push({ kategori: name, min: mv, max: xv });
    }
    return ranges;
}

function isRangeAllowed(nMin, nMax, eMin, eMax){
    if(nMin > eMin && nMax < eMax) return true;
    if(eMin > nMin && eMax < nMax) return true;
    if(nMax < eMin) return true;
    if(nMin > eMax) return true;
    return false;
}

function validateRanges(kelas, inputRanges){
    var errors = [];

    var allExisting = [];
    var allKeys = kelasList.concat(noKelasKategori);
    for(var ki=0; ki<allKeys.length; ki++){
        var k = allKeys[ki];
        var kats = kelasRangeData[k].kategori || {};
        var keys = Object.keys(kats);
        for(var oi=0; oi<keys.length; oi++){
            var katName = keys[oi];
            var r = kats[katName];
            allExisting.push({
                kelas: k,
                kategori: katName,
                min: parseInt(r.min),
                max: parseInt(r.max)
            });
        }
    }

    for(var ii=0; ii<inputRanges.length; ii++){
        var ir = inputRanges[ii];
        var conflictFound = false;

        for(var ei=0; ei<allExisting.length; ei++){
            var er = allExisting[ei];
            if(er.kelas === kelas && er.kategori === ir.kategori) continue;

            if(!isRangeAllowed(ir.min, ir.max, er.min, er.max)){
                errors.push({
                    kategori: [ir.kategori],
                    message: '<b>'+ir.kategori+'</b> di Kelas '+kelas+' ('+ir.min+'–'+ir.max+') menyentuh/melewati batas rentang <b>'+er.kategori+'</b> di Kelas '+er.kelas+' ('+er.min+'–'+er.max+'). Pastikan rentang <b>ketat di dalam</b> (lebih besar dari '+er.min+' DAN lebih kecil dari '+er.max+') atau <b>sepenuhnya di luar</b> (berakhir sebelum '+er.min+' atau dimulai setelah '+er.max+').'
                });
                conflictFound = true;
                break;
            }
        }

        if(conflictFound) continue;

        for(var jj=0; jj<inputRanges.length; jj++){
            if(ii === jj) continue;

            if(!isRangeAllowed(ir.min, ir.max, inputRanges[jj].min, inputRanges[jj].max)){
                errors.push({
                    kategori: [ir.kategori],
                    message: '<b>'+ir.kategori+'</b> ('+ir.min+'–'+ir.max+') menyentuh/melewati batas rentang <b>'+inputRanges[jj].kategori+'</b> ('+inputRanges[jj].min+'–'+inputRanges[jj].max+') di Kelas ini. Pastikan rentang <b>ketat di dalam</b> atau <b>sepenuhnya di luar</b>.'
                });
                break;
            }
        }
    }

    return errors;
}

/* ═══ TAMPILKAN / SEMBUNYIKAN ERROR BOX ═══ */
function showKatError(errors){
    var box = document.getElementById('katErrorBox');
    var text = document.getElementById('katErrorText');
    var html = '';
    for(var i=0; i<errors.length; i++){
        html += '<div style="margin-bottom:' + (i < errors.length-1 ? '6px' : '0') + ';">\u2022 ' + errors[i].message + '</div>';
    }
    text.innerHTML = html;
    box.style.display = 'block';
}

function hideKatError(){
    document.getElementById('katErrorBox').style.display = 'none';
}

function saveKategoriRange(){
    var kelas = document.getElementById('katKelasSelect').value;
    if(!kelas){popupError('Pilih Kelas','Pilih kelas terlebih dahulu.'); return;}

    var kategori = {};
    var saveKats = (kelas==='Bonsai') ? ['Bonsai'] : (kelas==='Jumbo') ? ['Jumbo'] : kategoriListWithKelas;
    for(var i=0;i<saveKats.length;i++){
        var name = saveKats[i];
        var minEl=document.getElementById('kat_'+name+'_min');
        var maxEl=document.getElementById('kat_'+name+'_max');
        if(!minEl||!maxEl) continue;
        var mv = minEl.value.trim(), xv = maxEl.value.trim();
        if(mv==='' && xv==='') continue;
        mv = parseInt(mv); xv = parseInt(xv);
        if(!mv||!xv||mv<1||xv<1){
            popupError('Tidak Valid','Rentang "<b>'+name+'</b>" tidak lengkap atau tidak valid.');
            return;
        }
        if(xv<mv){
            popupError('Tidak Valid','"<b>'+name+'</b>": Angka akhir ('+xv+') harus ≥ angka awal ('+mv+').');
            return;
        }
        kategori[name]={min:mv,max:xv};
    }

    var ranges = {};
    var allKeys = kelasList.concat(noKelasKategori);
    for(var i=0;i<allKeys.length;i++){
        var k=allKeys[i];
        ranges[k] = {kategori: kelasRangeData[k].kategori || {}};
    }
    ranges[kelas].kategori = kategori;

    var btn = document.getElementById('btnSaveKatRange');
    var orig = btn.innerHTML;
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Memproses...';

    var fd=new FormData();
    fd.append('_token',getCsrf());
    fd.append('ranges',JSON.stringify(ranges));

    fetch('/api/admin/tank-range',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){if(!r.ok) return r.json().then(function(d){throw d;}); return r.json();})
    .then(function(dd){
        if(dd.success){
            fetch('/api/tank-range?_v='+Date.now(),{headers:{'Accept':'application/json'}})
            .then(function(r){return r.json();})
            .then(function(d){
                var reloadKeys = kelasList.concat(noKelasKategori);
                for(var i=0;i<reloadKeys.length;i++){
                    var k=reloadKeys[i];
                    if(d[k]){
                        var katObj={};
                        if(d[k].kategori && typeof d[k].kategori==='object') katObj=d[k].kategori;
                        kelasRangeData[k]={kategori:katObj};
                    }
                }
                populateKelasSelect();
                renderRangeSummary();
                var curKelas=document.getElementById('katKelasSelect').value;
                if(curKelas){
                    renderKategoriGrid(curKelas);
                    showExistingInfo(curKelas);
                    hideKatError();
                }
                loadDashboard();
                popupSuccess('Berhasil','Pengaturan rentang Kelas '+kelas+' berhasil disimpan.');
            })
            .catch(function(){ popupSuccess('Berhasil','Pengaturan tersimpan.'); loadDashboard(); });
        } else { popupError('Gagal',dd.message||'Terjadi kesalahan.'); }
    })
    .catch(function(e){
        if(e.message) popupError('Rentang Tidak Valid','<div style="text-align:left;line-height:1.8;font-size:12px;">'+e.message+'</div>');
        else popupError('Error','Gagal menghubungi server.');
    })
    .finally(function(){btn.disabled=false; btn.innerHTML=orig;});
}

/* ═══════════════════════════════════════════════
   POPUP SYSTEM
   ═══════════════════════════════════════════════ */
function showPopup(id){document.getElementById(id).classList.add('show');}
function hidePopup(id){document.getElementById(id).classList.remove('show');}
function popupSuccess(title,desc){
    document.getElementById('popupSuccessTitle').textContent=title||'Berhasil!';
    document.getElementById('popupSuccessDesc').innerHTML=desc||'';
    showPopup('popupSuccess');
}
function popupError(title,desc){
    document.getElementById('popupErrorTitle').textContent=title||'Gagal!';
    document.getElementById('popupErrorDesc').innerHTML=desc||'';
    showPopup('popupError');
}
function popupInfo(title,desc){
    document.getElementById('popupInfoTitle').textContent = title || 'Informasi';
    document.getElementById('popupInfoDesc').innerHTML = desc || '';
    showPopup('popupInfo');
}

/* =========================================================
   CONFIRM POPUP
   ========================================================= */
/* =========================================================
   CONFIRM POPUP
   ID modal di Blade: popupConfirm
   Nama function sengaja berbeda agar tidak bentrok dengan ID.
   ========================================================= */
function openConfirmPopup(title, desc, btnText, callback){
    var modalEl = document.getElementById('popupConfirm');
    var titleEl = document.getElementById('popupConfirmTitle');
    var descEl = document.getElementById('popupConfirmDesc');
    var btnEl = document.getElementById('popupConfirmBtn');

    if(!modalEl){
        console.error('Modal konfirmasi #popupConfirm tidak ditemukan di admin.blade.php.');
        popupError(
            'Modal Tidak Ditemukan',
            'Elemen popup konfirmasi belum tersedia di halaman admin.'
        );
        return;
    }

    if(titleEl){
        titleEl.textContent = title || 'Konfirmasi';
    }

    if(descEl){
        descEl.innerHTML = desc || '';
    }

    if(btnEl){
        btnEl.innerHTML =
            '<i class="fas fa-check"></i> ' +
            (btnText || 'Ya, Lanjutkan');
    }

    _confirmCallback = typeof callback === 'function'
        ? callback
        : null;

    showPopup('popupConfirm');
}

function executeConfirm(){
    var callback = _confirmCallback;

    // Cegah request ganda dari klik tombol berulang.
    _confirmCallback = null;

    hidePopup('popupConfirm');

    if(typeof callback === 'function'){
        callback();
    }
}

function cancelConfirm(){
    _confirmCallback = null;
    hidePopup('popupConfirm');
}

/* ★ Modal prompt kustom (pengganti window.prompt) */
var _promptCallback = null;
function popupPromptText(opts){
    opts = opts || {};
    document.getElementById('popupPromptTitle').textContent = opts.title || 'Ubah';
    document.getElementById('popupPromptDesc').innerHTML = opts.desc || '';
    document.getElementById('popupPromptIcon').className = 'fas ' + (opts.icon || 'fa-pen');
    var field = document.getElementById('popupPromptField');
    field.innerHTML = '<input type="text" id="popupPromptInput" class="popup-input">';
    var inp = document.getElementById('popupPromptInput');
    inp.placeholder = opts.placeholder || '';
    inp.value = opts.value || '';
    inp.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); executePrompt(); } });
    document.getElementById('popupPromptBtn').innerHTML = '<i class="fas fa-check"></i> ' + (opts.okText || 'Simpan');
    _promptCallback = function(){ if(opts.onOk) opts.onOk((inp.value || '').trim()); };
    showPopup('popupPrompt');
    setTimeout(function(){ inp.focus(); inp.select(); }, 60);
}
function popupPromptSelect(opts){
    opts = opts || {};
    document.getElementById('popupPromptTitle').textContent = opts.title || 'Pilih';
    document.getElementById('popupPromptDesc').innerHTML = opts.desc || '';
    document.getElementById('popupPromptIcon').className = 'fas ' + (opts.icon || 'fa-sliders');
    var field = document.getElementById('popupPromptField');
    field.innerHTML = '';
    var sel = document.createElement('select');
    sel.id = 'popupPromptSelectEl';
    sel.className = 'popup-input';
    (opts.options || []).forEach(function(o){
        var op = document.createElement('option');
        op.value = o.value; op.textContent = o.label;
        if(o.value === opts.value) op.selected = true;
        sel.appendChild(op);
    });
    field.appendChild(sel);
    document.getElementById('popupPromptBtn').innerHTML = '<i class="fas fa-check"></i> ' + (opts.okText || 'Simpan');
    _promptCallback = function(){ if(opts.onOk) opts.onOk(sel.value); };
    showPopup('popupPrompt');
}
function executePrompt(){ hidePopup('popupPrompt'); var cb = _promptCallback; _promptCallback = null; if(typeof cb === 'function') cb(); }
function cancelPrompt(){ hidePopup('popupPrompt'); _promptCallback = null; }

var currentBonusIkanId = null;
var mvpIkanDataCache = {};

var bonusTypes = [
    {key:'best_of_the_best', label:'BEST OF THE BEST', icon:'fa-gem'},
    {key:'best_of_show',     label:'BEST OF SHOW',     icon:'fa-star'},
    {key:'grand_champion',   label:'GRAND CHAMPION',   icon:'fa-crown'},
    {key:'young_champion',   label:'YOUNG CHAMPION',   icon:'fa-medal'},
    {key:'junior',           label:'JUNIOR CHAMPION',  icon:'fa-award'},
    {key:'baby_champion',    label:'BABY CHAMPION',    icon:'fa-baby'},
    {key:'mini_champion',    label:'MINI CHAMPION',    icon:'fa-seedling'},
];

function openBonusModal(idx){
    var p = allScoringData[idx];
    if(!p) return;

    currentBonusIkanId = p.id;

    var totalBonus = parseInt(p.total_bonus || 0, 10);
    var rankPoint = parseInt(p.rank_point || 0, 10);
    var finalRankPoint = rankPoint + totalBonus;

    var html = '';

    html += '<div style="background:linear-gradient(135deg,rgba(15,23,42,.96),rgba(17,30,54,.92));border:1px solid rgba(34,211,238,.22);border-radius:14px;padding:16px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;box-shadow:inset 0 1px 0 rgba(255,255,255,.05);">';
    html += '<div style="min-width:0;">';
    html += '<h4 style="font-size:14px;font-weight:900;color:var(--text-hi);margin-bottom:5px;">'+esc(p.nama_peserta || '-')+'</h4>';
    html += '<div style="font-size:11px;color:var(--text-mid);display:flex;gap:12px;flex-wrap:wrap;">';
    html += '<span><i class="fas fa-tag" style="color:var(--cyan-400);"></i> '+esc(p.kategori || '-')+' - '+esc(p.kelas || '-')+'</span>';
    html += '<span><i class="fas fa-hashtag" style="color:var(--gold-400);"></i> Tank '+(p.nomor_tank || '—')+'</span>';
    html += '</div>';
    html += '</div>';

    html += '<div style="display:grid;grid-template-columns:repeat(3,minmax(90px,1fr));gap:8px;min-width:310px;">';

    html += '<div style="background:rgba(255,255,255,.04);border:1px solid var(--bd-1);border-radius:12px;padding:10px 12px;text-align:center;">';
    html += '<div style="font-size:9px;color:var(--text-low);font-weight:900;letter-spacing:.08em;text-transform:uppercase;">Total Point</div>';
    html += '<div style="font-size:18px;font-weight:900;color:var(--cyan-300);margin-top:3px;">'+(p.total_point || 0)+'</div>';
    html += '</div>';

    html += '<div style="background:rgba(255,255,255,.04);border:1px solid var(--bd-1);border-radius:12px;padding:10px 12px;text-align:center;">';
    html += '<div style="font-size:9px;color:var(--text-low);font-weight:900;letter-spacing:.08em;text-transform:uppercase;">Rank Point</div>';
    html += '<div style="font-size:18px;font-weight:900;color:var(--gold-300);margin-top:3px;">'+rankPoint+'</div>';
    html += '</div>';

    html += '<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);border-radius:12px;padding:10px 12px;text-align:center;">';
    html += '<div style="font-size:9px;color:#6EE7B7;font-weight:900;letter-spacing:.08em;text-transform:uppercase;">Final Rank</div>';
    html += '<div style="font-size:18px;font-weight:900;color:#6EE7B7;margin-top:3px;">'+finalRankPoint+'</div>';
    if(totalBonus > 0){
        html += '<div style="font-size:9px;color:#6EE7B7;font-weight:800;margin-top:2px;">Rank '+rankPoint+' + Bonus '+totalBonus+'</div>';
    } else {
        html += '<div style="font-size:9px;color:var(--text-low);font-weight:800;margin-top:2px;">Belum ada bonus</div>';
    }
    html += '</div>';

    html += '</div>';
    html += '</div>';

    html += '<div style="font-size:10px;font-weight:800;color:var(--text-low);text-transform:uppercase;letter-spacing:.08em;margin-bottom:9px;">';
    html += '<i class="fas fa-circle-info" style="color:var(--gold-400);"></i> Pilih Bonus Rank Point (+100 per jenis)';
    html += '</div>';

    bonusTypes.forEach(function(bt){
        var applied = p.bonus_list && p.bonus_list.indexOf(bt.key) !== -1;

        if(applied){
            html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1px solid rgba(16,185,129,.28);border-radius:12px;margin-bottom:7px;background:rgba(16,185,129,.08);box-shadow:inset 0 1px 0 rgba(255,255,255,.04);">';
            html += '<div style="display:flex;align-items:center;gap:10px;">';
            html += '<i class="fas fa-check-circle" style="color:#6EE7B7;font-size:15px;"></i>';
            html += '<div>';
            html += '<div style="font-size:12px;font-weight:900;color:#6EE7B7;">'+esc(bt.label)+'</div>';
            html += '<div style="font-size:10px;color:rgba(110,231,183,.78);font-weight:700;">+100 rank point</div>';
            html += '</div>';
            html += '</div>';
            html += '<button class="btn-xs red" onclick="removeBonus(\''+bt.key+'\')" style="padding:5px 10px;"><i class="fas fa-times"></i> Hapus</button>';
            html += '</div>';
        } else {
            html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1px solid var(--bd-2);border-radius:12px;margin-bottom:7px;cursor:pointer;transition:all .2s;background:rgba(255,255,255,.025);" onclick="addBonus(\''+bt.key+'\',this)" onmouseover="this.style.borderColor=\'rgba(245,158,11,.35)\';this.style.background=\'rgba(245,158,11,.07)\'" onmouseout="this.style.borderColor=\'var(--bd-2)\';this.style.background=\'rgba(255,255,255,.025)\'">';
            html += '<div style="display:flex;align-items:center;gap:10px;">';
            html += '<i class="fas '+bt.icon+'" style="color:var(--text-low);font-size:15px;"></i>';
            html += '<div>';
            html += '<div style="font-size:12px;font-weight:800;color:var(--text);">'+esc(bt.label)+'</div>';
            html += '<div style="font-size:10px;color:var(--text-low);font-weight:700;">+100 rank point</div>';
            html += '</div>';
            html += '</div>';
            html += '<i class="fas fa-plus-circle" style="color:var(--text-low);font-size:17px;"></i>';
            html += '</div>';
        }
    });

    document.getElementById('bonusModalBody').innerHTML = html;
    openModal('modalBonus');
}

function getBonusLabel(type){
    var found = bonusTypes.find(function(b){ return b.key === type; });
    return found ? found.label : type;
}

function updateCurrentBonusDataFromResponse(d){
    if(!d || !currentBonusIkanId) return;

    for(var i = 0; i < allScoringData.length; i++){
        if(parseInt(allScoringData[i].id, 10) === parseInt(currentBonusIkanId, 10)){
            var totalBonus = parseInt(d.total_bonus || 0, 10);
            var rankPoint = parseInt(allScoringData[i].rank_point || 0, 10);

            allScoringData[i].bonus_list = Array.isArray(d.bonus_list) ? d.bonus_list : [];
            allScoringData[i].total_bonus = totalBonus;

            // Bonus hanya menambah rank point.
            allScoringData[i].final_rank_point = rankPoint + totalBonus;

            // Jangan biarkan kode lama membaca total_point + bonus.
            allScoringData[i].final_point = parseFloat(allScoringData[i].total_point || 0);

            break;
        }
    }
}

function setBonusModalBusy(isBusy, msg){
    var body = document.getElementById('bonusModalBody');
    var modal = document.getElementById('modalBonus');

    if(!body) return;

    if(isBusy){
        body.setAttribute('data-prev-html', body.innerHTML);
        body.innerHTML =
            '<div style="padding:32px 18px;text-align:center;">' +
                '<div style="width:42px;height:42px;border:4px solid rgba(255,255,255,.12);border-top-color:var(--gold-400);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 14px;"></div>' +
                '<div style="font-size:13px;font-weight:900;color:var(--text-hi);margin-bottom:5px;">' + esc(msg || 'Memproses bonus...') + '</div>' +
                '<div style="font-size:11px;color:var(--text-mid);">Mohon tunggu sampai proses selesai.</div>' +
            '</div>';

        if(modal){
            modal.classList.add('is-busy');
        }
    } else {
        if(modal){
            modal.classList.remove('is-busy');
        }
    }
}

function rerenderBonusModalAfterChange(d){
    updateCurrentBonusDataFromResponse(d);

    var reopenFromCurrentData = function(){
        var idx = -1;

        for(var i = 0; i < allScoringData.length; i++){
            if(parseInt(allScoringData[i].id, 10) === parseInt(currentBonusIkanId, 10)){
                idx = i;
                break;
            }
        }

        if(idx >= 0){
            openBonusModal(idx);
        } else {
            setBonusModalBusy(false);
        }
    };

    // Tampilkan ulang modal segera dari data lokal,
    // supaya loading tidak menggantung walaupun refresh data lambat.
    reopenFromCurrentData();

    // Refresh data tabel di background.
    // Tidak menunggu callback supaya modal tidak stuck loading.
    try {
        if(typeof loadScoringData === 'function'){
            loadScoringData();
        }
    } catch(e) {
        console.warn('loadScoringData background gagal:', e);
    }

    try {
        if(typeof loadMvpIkanData === 'function'){
            loadMvpIkanData();
        }
    } catch(e) {
        console.warn('loadMvpIkanData background gagal:', e);
    }

    try {
        if(typeof loadAdminPointRanking === 'function'){
            loadAdminPointRanking();
        }
    } catch(e) {
        console.warn('loadAdminPointRanking background gagal:', e);
    }

    try {
        if(typeof loadTeamChampionIkan === 'function'){
            loadTeamChampionIkan();
        }
    } catch(e) {
        console.warn('loadTeamChampionIkan background gagal:', e);
    }
}

function addBonus(type, el){
    if(!currentBonusIkanId) return;

    var label = getBonusLabel(type);

    if(el){
        el.style.pointerEvents = 'none';
        el.style.opacity = '.55';
    }

    setBonusModalBusy(true, 'Menambahkan bonus ' + label + '...');

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('ikan_id', currentBonusIkanId);
    fd.append('bonus_type', type);

    fetch('/api/admin/add-bonus', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(r){
        if(!r.ok){
            return r.json().then(function(d){ throw d; });
        }
        return r.json();
    })
    .then(function(d){
        if(d.success){
            popupSuccess(
                'Bonus Ditambahkan',
                '<b>' + esc(label) + '</b> (+100 rank point) berhasil ditambahkan.'
            );

            rerenderBonusModalAfterChange(d);

        } else {
            popupError('Gagal', d.message || 'Terjadi kesalahan.');
            setBonusModalBusy(false);
        }
    })
    .catch(function(e){
        popupError('Gagal', e && e.message ? esc(e.message) : 'Gagal menghubungi server.');
        setBonusModalBusy(false);

        if(el){
            el.style.pointerEvents = '';
            el.style.opacity = '';
        }

        var body = document.getElementById('bonusModalBody');
        if(body && body.getAttribute('data-prev-html')){
            body.innerHTML = body.getAttribute('data-prev-html');
            body.removeAttribute('data-prev-html');
        }
    });
}

function removeBonus(type){
    if(!currentBonusIkanId) return;

    var label = getBonusLabel(type);

    openConfirmPopup(
        'Hapus Bonus',
        'Yakin ingin menghapus bonus <strong>' + esc(label) + '</strong>? Rank point bonus akan dikurangi kembali.',
        'Ya, Hapus',
        function(){
            setBonusModalBusy(true, 'Menghapus bonus ' + label + '...');

            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('ikan_id', currentBonusIkanId);
            fd.append('bonus_type', type);

            fetch('/api/admin/remove-bonus', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            })
            .then(function(r){
                if(!r.ok){
                    return r.json().then(function(d){ throw d; });
                }
                return r.json();
            })
            .then(function(d){
                if(d.success){
                    popupSuccess('Bonus Dihapus', 'Bonus <b>' + esc(label) + '</b> berhasil dihapus.');

                    rerenderBonusModalAfterChange(d);

                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                    setBonusModalBusy(false);
                }
            })
            .catch(function(e){
                popupError('Gagal', e && e.message ? esc(e.message) : 'Gagal menghubungi server.');
                setBonusModalBusy(false);

                var body = document.getElementById('bonusModalBody');
                if(body && body.getAttribute('data-prev-html')){
                    body.innerHTML = body.getAttribute('data-prev-html');
                    body.removeAttribute('data-prev-html');
                }
            });
        }
    );
}

var formFields={
    overall:[{id:'impression',label:'Impression',desc:'Kelipatan 5 (10-90)'}],
    head:[{id:'size',label:'Size (Ukuran)',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk Kepala',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_head_penalty'}],
    face:[{id:'face',label:'Face',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_face_penalty'}],
    body:[{id:'bentuk',label:'Bentuk Badan',desc:'Kelipatan 5 (10-90)'},{id:'proporsi',label:'Proporsional',desc:'Kelipatan 5 (10-90)'},{id:'pangkal',label:'Pangkal',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_body_penalty'}],
    marking:[{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'},{id:'contrast',label:'Contrast',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk',desc:'Kelipatan 5 (10-90)'}],
    pearl:[{id:'shining',label:'Shining',desc:'Kelipatan 5 (10-90)'},{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'},{id:'bentuk',label:'Bentuk',desc:'Kelipatan 5 (10-90)'}],
    color:[{id:'komposisi',label:'Komposisi',desc:'Kelipatan 5 (10-90)'},{id:'kecerahan',label:'Kecerahan',desc:'Kelipatan 5 (10-90)'},{id:'fullness',label:'Fullness',desc:'Kelipatan 5 (10-90)'}],
    finnage:[{id:'bentuk',label:'Bentuk Sirip & Ekor',desc:'Kelipatan 5 (10-90)'},{id:'kecerahan',label:'Kecerahan',desc:'Kelipatan 5 (10-90)'},{id:'defect',label:'Defect',desc:'Pilih jika ada defect',type:'defect',defectKey:'raw_finnage_penalty'}]
};

var formFieldsLegacy={
    face:[{id:'pipi',label:'Pipi'},{id:'mata',label:'Mata'},{id:'bibir',label:'Bibir'},{id:'kondisi',label:'Kondisi Mata & Insang'}]
};

/* ═══════════════════════════════════════════════
   EDIT NILAI & KUNCI NILAI (ADMIN)
   ═══════════════════════════════════════════════ */
var ADMIN_MINOR_DEFECTS=[
    'Kutil',
    'Bibir Miring',
    'Bibir Miring (kasat mata)',
    'Katarak',
    'Bibir Tidak Menutup & Selaput Bergerak',
    'Abses / Luka',
    'Fintail Bleaching',
    'Fintail Bleaching / Transparan',
    'Pangkal Ekor Naik/Trn',
    'Pangkal Ekor Naik atau Turun',
    'Dayung Tdk Seimbang',
    'Sirip Dayung Tidak Seimbang'
];

var ADMIN_MAYOR_DEFECTS=[
    'Bagian Bibir Hilang',
    'Mulut Terbuka Terus',
    'Muka Miring',
    'Pangkal Bengkok/Patah',
    'Pangkal Bengkok / Melintir',
    'Fin/Tulang Hilang 1 Ruas'
];

var ADMIN_DEFECT_OPTIONS={
    raw_head_penalty:[
        {label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},
        {label:'--- MINOR ---',options:[{value:'Kutil',label:'Kutil'}]}
    ],
    raw_face_penalty:[
        {label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},
        {label:'--- MINOR ---',options:[
            {value:'Bibir Miring (kasat mata)',label:'Bibir Miring (kasat mata)'},
            {value:'Katarak',label:'Katarak'},
            {value:'Bibir Tidak Menutup & Selaput Bergerak',label:'Bibir Tidak Menutup & Selaput Bergerak'}
        ]},
        {label:'--- MAYOR ---',options:[
            {value:'Bagian Bibir Hilang',label:'Bagian Bibir Hilang'},
            {value:'Muka Miring',label:'Muka Miring'}
        ]}
    ],
    raw_body_penalty:[
        {label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},
        {label:'--- MINOR ---',options:[
            {value:'Kutil',label:'Kutil'},
            {value:'Abses / Luka',label:'Abses / Luka'}
        ]}
    ],
    raw_finnage_penalty:[
        {label:'--- AMAN ---',options:[{value:'0',label:'Aman (0)'}]},
        {label:'--- MINOR ---',options:[
            {value:'Kutil',label:'Kutil'},
            {value:'Fintail Bleaching / Transparan',label:'Fintail Bleaching / Transparan'},
            {value:'Pangkal Ekor Naik atau Turun',label:'Pangkal Ekor Naik atau Turun'},
            {value:'Sirip Dayung Tidak Seimbang',label:'Sirip Dayung Tidak Seimbang'}
        ]},
        {label:'--- MAYOR ---',options:[
            {value:'Fin/Tulang Hilang 1 Ruas',label:'Fin/Tulang Hilang 1 Ruas'},
            {value:'Pangkal Bengkok / Melintir',label:'Pangkal Bengkok / Melintir'}
        ]}
    ]
};

var ADMIN_DEFECT_LEGACY_MAP={'Bibir Miring':'Bibir Miring (kasat mata)','Fintail Bleaching':'Fintail Bleaching / Transparan','Pangkal Ekor Naik/Trn':'Pangkal Ekor Naik atau Turun','Dayung Tdk Seimbang':'Sirip Dayung Tidak Seimbang','Mulut Terbuka Terus':'Bibir Tidak Menutup & Selaput Bergerak','Pangkal Bengkok/Patah':'Pangkal Bengkok / Melintir'};
function adminNormalizeDefectLegacy(arr){return arr.map(function(v){return ADMIN_DEFECT_LEGACY_MAP[v]||v;});}

var adminEditDefectData={raw_head_penalty:['0'],raw_face_penalty:['0'],raw_body_penalty:['0'],raw_finnage_penalty:['0']};
var adminOrigDefectData={raw_head_penalty:['0'],raw_face_penalty:['0'],raw_body_penalty:['0'],raw_finnage_penalty:['0']};
var adminActiveDefectKey=null;
var adminEditId=null;
var adminEditPData=null;
var adminEditMemory={};
var adminOrigValues={};
var adminCurrentKat='overall';

function adminNormDefArr(v){if(!v)return['0'];if(typeof v==='string')return[v];if(Array.isArray(v))return v;return['0'];}
function adminGetScoreOptions(){var o=[];for(var i=90;i>=10;i-=5)o.push({value:i.toString(),label:i.toString()});return o;}
function adminFreshMemory(){var m={};Object.keys(formFields).forEach(function(k){m[k]={};});return m;}
function adminCloneValues(src){var m={};Object.keys(formFields).forEach(function(k){m[k]={};formFields[k].forEach(function(f){if(f.type==='defect')return;var v=(src&&src[k]&&src[k][f.id]);if(v===undefined&&f.id==='shining'&&src&&src[k]&&src[k].shinning!==undefined)v=src[k].shinning;m[k][f.id]=(v!==undefined&&v!==null&&v!=='')?String(v):'';});});return m;}
function adminEvaluateDefects(){var parts=['head','face','body','finnage'];var partStatus={};parts.forEach(function(p){partStatus[p]={minor:false,mayor:false,items:[]};});parts.forEach(function(p){var defs=adminNormDefArr(adminEditDefectData['raw_'+p+'_penalty']);defs.forEach(function(d){if(d&&d!=='0'){partStatus[p].items.push(d);if(ADMIN_MINOR_DEFECTS.indexOf(d)!==-1)partStatus[p].minor=true;if(ADMIN_MAYOR_DEFECTS.indexOf(d)!==-1)partStatus[p].mayor=true;}});});var componentsWithMinor=0;parts.forEach(function(p){if(partStatus[p].minor)componentsWithMinor++;});var isGlobalMayor=componentsWithMinor>=3;var results={};parts.forEach(function(p){if(partStatus[p].items.length>0){var isM=partStatus[p].mayor||(partStatus[p].minor&&isGlobalMayor);results[p+'_penalty']=isM?'30%':'10%';}else{results[p+'_penalty']='';}});return results;}

function openDefectModalAdmin(defectKey){
    adminActiveDefectKey=defectKey;
    var partName=defectKey.replace('raw_','').replace('_penalty','').toUpperCase();
    document.getElementById('defectAdminTitle').innerText='Pilih Defect - '+partName;
    var options=ADMIN_DEFECT_OPTIONS[defectKey];
    var currentValues=adminEditDefectData[defectKey]||['0'];
    var html='';
    options.forEach(function(group){
        html+='<div style="margin-bottom:20px;"><div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:10px;letter-spacing:.5px;">'+group.label+'</div><div style="display:flex;flex-direction:column;gap:8px;">';
        group.options.forEach(function(opt){
            var isChecked=currentValues.indexOf(opt.value)!==-1;
            html+='<label style="display:flex;align-items:center;gap:12px;padding:12px;border:2px solid '+(isChecked?'var(--purple)':'var(--bd-2)')+';border-radius:10px;cursor:pointer;background:'+(isChecked?'rgba(168,85,247,.12)':'var(--glass-2)')+';transition:all .2s;" onclick="toggleDefectAdmin(\''+defectKey+'\',\''+opt.value.replace(/'/g,"\\'")+'\')"><input type="checkbox" '+(isChecked?'checked':'')+' onclick="event.stopPropagation();toggleDefectAdmin(\''+defectKey+'\',\''+opt.value.replace(/'/g,"\\'")+'\')" style="width:18px;height:18px;accent-color:var(--purple);cursor:pointer;"><span style="font-size:13px;font-weight:600;color:var(--text);">'+opt.label+'</span></label>';
        });
        html+='</div></div>';
    });
    document.getElementById('defectAdminBody').innerHTML=html;
    openModal('modalDefectAdmin');
}
function closeDefectAdmin(){closeModal('modalDefectAdmin');adminActiveDefectKey=null;renderEditInputsAdmin(adminCurrentKat);}
function toggleDefectAdmin(defectKey,value){var current=adminEditDefectData[defectKey]||['0'];if(value==='0'){adminEditDefectData[defectKey]=['0'];}else{current=current.filter(function(v){return v!=='0';});if(current.indexOf(value)!==-1){current=current.filter(function(v){return v!==value;});}else{current.push(value);}if(current.length===0)current=['0'];adminEditDefectData[defectKey]=current;}openDefectModalAdmin(defectKey);}

function openEditAdmin(idx){
    var p=allScoringData[idx];
    if(!p)return;
    if(p.is_locked){popupError('Nilai Terkunci','Nilai ini sudah <b>TERKUNCI (FINAL)</b> dan tidak dapat diubah.');return;}
    adminEditId=p.id;adminCurrentKat='overall';adminEditPData=p;
    if(p.nilai_detail&&typeof p.nilai_detail==='object'){adminEditMemory=adminCloneValues(p.nilai_detail);}else{adminEditMemory=adminFreshMemory();}
    adminOrigValues=adminCloneValues(adminEditMemory);
    adminEditDefectData={raw_head_penalty:['0'],raw_face_penalty:['0'],raw_body_penalty:['0'],raw_finnage_penalty:['0']};
    if(p.all_scorings&&p.all_scorings.length>0){
        var targetSc=p.all_scorings.find(function(s){return s.edited_by_grand;})||p.all_scorings[0];
        if(targetSc){
            adminEditDefectData.raw_head_penalty=adminNormalizeDefectLegacy(adminNormDefArr(targetSc.raw_head_penalty));
            adminEditDefectData.raw_face_penalty=adminNormalizeDefectLegacy(adminNormDefArr(targetSc.raw_face_penalty));
            adminEditDefectData.raw_body_penalty=adminNormalizeDefectLegacy(adminNormDefArr(targetSc.raw_body_penalty));
            adminEditDefectData.raw_finnage_penalty=adminNormalizeDefectLegacy(adminNormDefArr(targetSc.raw_finnage_penalty));
        }
    }
    adminOrigDefectData=JSON.parse(JSON.stringify(adminEditDefectData));
    var info='<b>'+esc(p.nama_peserta)+'</b> — Tank '+(p.nomor_tank||'—');
    info+='<br><span style="font-size:11px;color:var(--primary);">'+esc(p.kategori)+' - '+(p.kelas||'—')+' | '+esc(p.detail_anggota||'—')+'</span>';
    if(p.grand_juri_nama){info+='<br><span style="font-size:11px;color:#D8B4FE;"><i class="fas fa-crown" style="margin-right:3px;"></i> Terakhir diedit oleh: <b>'+esc(p.grand_juri_nama)+'</b></span>';}
    document.getElementById('editAdminInfo').innerHTML=info;
    renderEditListAdmin();renderEditInputsAdmin('overall');
    openModal('modalEditAdmin');
}

function renderEditListAdmin(){
    var c=document.getElementById('editAdminKatList');c.innerHTML='';
    Object.keys(formFields).forEach(function(kat){
        var changes=adminCountChanges(kat);
        var btn=document.createElement('button');
        btn.style.cssText='padding:9px 11px;background:var(--glass-2);border:1px solid '+(kat===adminCurrentKat?'var(--purple)':'var(--bd-2)')+';border-radius:10px;text-align:left;font-size:12px;font-weight:600;color:'+(kat===adminCurrentKat?'var(--purple)':'var(--text-muted)')+';cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:space-between;';
        btn.innerHTML='<span>'+kat.charAt(0).toUpperCase()+kat.slice(1)+'</span><span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;min-width:22px;text-align:center;'+(changes>0?'background:var(--success);color:#fff;':'background:var(--purple-lt);color:var(--purple);')+'">'+(changes>0?changes:'—')+'</span>';
        btn.onclick=function(){adminSaveCurrentTab();adminCurrentKat=kat;renderEditListAdmin();renderEditInputsAdmin(kat);};
        c.appendChild(btn);
    });
}
function adminCountChanges(kat){if(!adminEditMemory[kat]||!adminOrigValues[kat])return 0;var n=0;formFields[kat].forEach(function(f){if(f.type==='defect')return;var cur=String(adminEditMemory[kat][f.id]||'');var ori=String(adminOrigValues[kat][f.id]||'');if(cur!==''&&cur!==ori)n++;});return n;}
function adminSaveCurrentTab(){if(!formFields[adminCurrentKat])return;if(!adminEditMemory[adminCurrentKat])adminEditMemory[adminCurrentKat]={};formFields[adminCurrentKat].forEach(function(f){var el=document.getElementById('admEdit-'+f.id);if(el)adminEditMemory[adminCurrentKat][f.id]=el.value;});}

function renderEditInputsAdmin(kat){
    if(!adminEditMemory[kat])adminEditMemory[kat]={};
    if(!adminOrigValues[kat])adminOrigValues[kat]={};
    var html='';
    formFields[kat].forEach(function(f){
        if(f.type==='defect'){
            var defectKey=f.defectKey;
            var currentValues=adminEditDefectData[defectKey]||['0'];
            var isAman=currentValues.indexOf('0')!==-1||currentValues.length===0;
            var evaluated=adminEvaluateDefects();
            var evalKey=defectKey.substring(4);
            var evalString=evaluated[evalKey];
            var btnLabel='AMAN';var btnStyle='padding:9px;text-align:center;border:2px solid var(--bd-2);border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;transition:.2s;background:var(--glass-2);color:var(--text-muted);font-family:inherit;width:100%;';
            if(!isAman&&evalString&&evalString!==''){var isMayor=evalString==='30%';var persen=isMayor?30:10;var defectNames=currentValues.filter(function(v){return v!=='0';}).join(', ');btnLabel=defectNames+' (-'+persen+'%)';btnStyle='padding:9px;text-align:center;border:2px solid '+(isMayor?'rgba(239,68,68,.30)':'rgba(245,158,11,.30)')+';border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;transition:.2s;background:'+(isMayor?'rgba(239,68,68,.12)':'rgba(245,158,11,.12)')+';color:'+(isMayor?'#FCA5A5':'var(--gold-300)')+';font-family:inherit;width:100%;';}
            html+='<div style="display:grid;grid-template-columns:1fr 115px;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--bd-1);"><div><div style="font-size:13px;font-weight:700;color:var(--text);">'+f.label+'</div><div style="font-size:11px;color:var(--text-muted);margin-top:2px;">'+f.desc+'</div></div><button type="button" style="'+btnStyle+'" onclick="openDefectModalAdmin(\''+defectKey+'\')">'+btnLabel+'</button></div>';
        } else {
            var options=adminGetScoreOptions();
            var currentVal=adminEditMemory[kat][f.id]||'';
            var origVal=adminOrigValues[kat][f.id]||'';
            var isChanged=(currentVal!==''&&currentVal!==origVal);
            html+='<div style="display:grid;grid-template-columns:1fr 115px;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--bd-1);">';
            html+='<div><div style="font-size:13px;font-weight:700;color:var(--text);">'+f.label+'</div><div style="font-size:11px;color:var(--text-muted);margin-top:2px;">'+f.desc;
            if(origVal!=='')html+=' &nbsp;|&nbsp; <span style="color:var(--text-muted);font-weight:600;">Nilai juri: <strong style="color:#D8B4FE;">'+origVal+'</strong></span>';
            html+='</div></div>';
            html+='<select class="gj-score-select'+(isChanged?' changed':'')+'" id="admEdit-'+f.id+'" onchange="onAdminEditInput(this,\''+kat+'\',\''+f.id+'\')" style="width:100%;padding:9px 28px 9px 8px;text-align:center;border:2px solid '+(isChanged?'var(--success)':'var(--bd-2)')+';border-radius:10px;font-size:15px;font-weight:800;color:'+(isChanged?'#34D399':'#D8B4FE')+';outline:none;transition:.2s;background:'+(isChanged?'rgba(16,185,129,.06)':'var(--glass-2)')+';cursor:pointer;appearance:none;-webkit-appearance:none;background-image:url(\'data:image/svg+xml,%3Csvg xmlns=\\\'http://www.w3.org/2000/svg\\\' width=\\\'12\\\' height=\\\'12\\\' viewBox=\\\'0 0 12 12\\\'%3E%3Cpath fill=\\\'%23A78BFA\\\' d=\\\'M6 8L1 3h10z\\\'/%3E%3C/svg%3E\');background-repeat:no-repeat;background-position:right 8px center;font-family:inherit;color-scheme:dark;"><option value="">-</option>';
            options.forEach(function(opt){html+='<option value="'+opt.value+'"'+(currentVal==opt.value?' selected':'')+'>'+opt.label+'</option>';});
            html+='</select></div>';
        }
    });
    html+='<div style="text-align:right;font-size:13px;font-weight:900;color:#D8B4FE;padding:10px 0 0;border-top:2px solid rgba(168,85,247,.25);margin-top:8px;">Subtotal <em>'+kat+'</em>: <span id="adminSubVal">0</span></div>';
    document.getElementById('editAdminFormArea').innerHTML=html;
    adminUpdateSub(kat);
}

function onAdminEditInput(el,kat,fid){
    var cur=el.value;var ori=adminOrigValues[kat]?String(adminOrigValues[kat][fid]||''):'';
    if(cur!==''&&cur!==ori){el.classList.add('changed');el.style.borderColor='var(--success)';el.style.background='rgba(16,185,129,.06)';el.style.color='#34D399';}
    else{el.classList.remove('changed');el.style.borderColor='var(--bd-2)';el.style.background='var(--glass-2)';el.style.color='#D8B4FE';}
    if(!adminEditMemory[kat])adminEditMemory[kat]={};
    adminEditMemory[kat][fid]=el.value;
    adminUpdateSub(kat);renderEditListAdmin();
}
function adminUpdateSub(kat){var t=0;formFields[kat].forEach(function(f){var el=document.getElementById('admEdit-'+f.id);if(el&&el.value!=='')t+=parseInt(el.value)||0;});var s=document.getElementById('adminSubVal');if(s)s.textContent=t;}

function submitEditAdmin(){
    adminSaveCurrentTab();
    var payload={};var totalChanged=0;
    Object.keys(formFields).forEach(function(kat){formFields[kat].forEach(function(f){if(f.type==='defect')return;var cur=adminEditMemory[kat]?adminEditMemory[kat][f.id]:'';var ori=adminOrigValues[kat]?adminOrigValues[kat][f.id]:'';if(cur===''&&ori==='')return;if(String(cur)===String(ori))return;var val=parseInt(cur);if(isNaN(val))return;if(val<0)return;if(!payload[kat])payload[kat]={};payload[kat][f.id]=val;totalChanged++;});});
    var defectChanged=false;
    ['raw_head_penalty','raw_face_penalty','raw_body_penalty','raw_finnage_penalty'].forEach(function(key){var cur=JSON.stringify(adminEditDefectData[key]||['0']);var ori=JSON.stringify(adminOrigDefectData[key]||['0']);if(cur!==ori)defectChanged=true;});
    if(totalChanged===0&&!defectChanged){popupInfo('Tidak Ada Perubahan','Ubah setidaknya satu komponen nilai sebelum menyimpan.');return;}
    var btn=document.getElementById('btnSaveEditAdmin');btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MENYIMPAN...';
    fetch('/api/grand-juri/edit-nilai',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({_token:getCsrf(),ikan_id:adminEditId,changed_fields:payload,defect_data:defectChanged?adminEditDefectData:null})})
    .then(function(res){if(!res.ok)return res.json().then(function(d){d._err=true;return d;});return res.json();})
    .then(function(d){
        if(d._err||!d.success){popupError('Gagal Menyimpan',d.message||'Terjadi kesalahan pada server.');return;}
        closeModal('modalEditAdmin');
        popupSuccess('Nilai Berhasil Diperbarui!','Total nilai akhir: <strong style="color:#D8B4FE;font-size:18px;">'+d.total+'</strong><br><span style="font-size:12px;color:var(--text-muted);">'+totalChanged+' komponen diperbarui'+(defectChanged?' + defect data diperbarui':'')+'</span>');
        loadScoringData();loadDashboard();if(typeof loadAdminPointRanking==='function')loadAdminPointRanking();
    })
    .catch(function(){popupError('Kesalahan Jaringan','Gagal menghubungi server.');})
    .finally(function(){btn.disabled=false;btn.innerHTML='<i class="fas fa-save"></i> SIMPAN PERUBAHAN';});
}

function kunciNilaiAdmin(ikanId){
    // ★ LOADING STATE: disable SEMUA tombol kunci untuk ikan ini agar tidak double-click
    var btns=document.querySelectorAll('button[onclick*="kunciNilaiAdmin('+ikanId+')"]');
    btns.forEach(function(b){
        b._origHTML=b.innerHTML;
        b.disabled=true;
        b.style.opacity='0.65';
        b.style.cursor='wait';
        b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Memproses...';
    });
    var restoreBtns=function(){
        btns.forEach(function(b){
            b.disabled=false;
            b.style.opacity='';
            b.style.cursor='';
            if(b._origHTML) b.innerHTML=b._origHTML;
        });
    };
    fetch('/api/grand-juri/kunci-nilai',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({_token:getCsrf(),ikan_id:ikanId})})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){
            popupSuccess('Berhasil',d.message);
            if(typeof loadAdminPointRanking==='function') loadAdminPointRanking(); // tombol di-render ulang
            loadScoringData();
            loadDashboard();
        } else {
            restoreBtns();
            popupError('Gagal',d.message||'Tidak dapat mengubah status kunci.');
        }
    })
    .catch(function(){
        restoreBtns();
        popupError('Kesalahan Jaringan','Gagal menghubungi server.');
    });
}

function kunciSemuaAdmin(){
    var badge = document.getElementById('kunciStatusBadge');
    var btn = document.getElementById('btnKunciSemua');
    var isAllLocked = badge && badge.innerHTML.indexOf('TERKUNCI') !== -1;

    if(isAllLocked){
        openConfirmPopup(
            'Buka Semua Kunci?',
            'Tindakan ini akan <b>MEMBUKA KUNCI SEMUA peserta</b> yang sudah terkunci. Setelah dibuka, nilai dapat diubah kembali oleh Grand Juri.<br><br>Lanjutkan?',
            'Ya, Buka Semua',
            function(){
                btn.disabled=true; btn.style.opacity='0.65'; btn.style.cursor='wait';
                btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MEMBUKA...';
                fetch('/api/admin/buka-semua-kunci',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    body:JSON.stringify({_token:getCsrf()})
                })
                .then(function(r){return r.json();})
                .then(function(d){
                    btn.disabled=false; btn.style.opacity=''; btn.style.cursor='';
                    if(d.success){
                        updateKunciUI(false, 0, 0); // Instant update ke state terbuka
                        popupSuccess('Berhasil!',d.message);
                        if(typeof loadAdminPointRanking==='function') loadAdminPointRanking();
                        if(typeof loadScoringData==='function') loadScoringData();
                        if(typeof loadDashboard==='function') loadDashboard();
                    } else {
                        updateKunciUI(true, 0, 0); // Revert ke state terkunci
                        popupError('Gagal',d.message||'Tidak dapat membuka kunci.');
                    }
                })
                .catch(function(){
                    btn.disabled=false; btn.style.opacity=''; btn.style.cursor='';
                    updateKunciUI(true, 0, 0);
                    popupError('Kesalahan Jaringan','Gagal menghubungi server.');
                });
            }
        );
    } else {
        openConfirmPopup(
            'Kunci Semua Peserta?',
            'Tindakan ini akan <b>MENGUNCI SEMUA peserta</b> yang sudah dinilai tetapi belum terkunci. Setelah dikunci, nilai tidak dapat diubah lagi.<br><br>Lanjutkan?',
            'Ya, Kunci Semua',
            function(){
                btn.disabled=true; btn.style.opacity='0.65'; btn.style.cursor='wait';
                btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MENGUNCI...';
                fetch('/api/grand-juri/kunci-semua',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    body:JSON.stringify({_token:getCsrf()})
                })
                .then(function(r){return r.json();})
                .then(function(d){
                    btn.disabled=false; btn.style.opacity=''; btn.style.cursor='';
                    if(d.success){
                        updateKunciUI(true, 0, 0); // Instant update ke state terkunci
                        popupSuccess('Berhasil!',d.message);
                        if(typeof loadAdminPointRanking==='function') loadAdminPointRanking();
                        if(typeof loadScoringData==='function') loadScoringData();
                        if(typeof loadDashboard==='function') loadDashboard();
                    } else {
                        updateKunciUI(false, 0, 0); // Revert ke state terbuka
                        popupError('Gagal',d.message||'Tidak dapat mengunci.');
                    }
                })
                .catch(function(){
                    btn.disabled=false; btn.style.opacity=''; btn.style.cursor='';
                    updateKunciUI(false, 0, 0);
                    popupError('Kesalahan Jaringan','Gagal menghubungi server.');
                });
            }
        );
    }
}

document.getElementById('btnSaveEditAdmin').addEventListener('click',submitEditAdmin);
document.getElementById('modalEditAdmin').addEventListener('click',function(e){if(e.target===this)closeModal('modalEditAdmin');});
document.getElementById('modalDefectAdmin').addEventListener('click',function(e){if(e.target===this)closeDefectAdmin();});

/* ═══ KELOLA AKSES PENILAIAN JURI (TOGGLE GLOBAL) ═══ */
function loadJuriScoringLockStatus(){
    fetch('/api/admin/scoring-status',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        updateJuriScoringLockUI(d.scoring_unlocked||false);
    })
    .catch(function(){updateJuriScoringLockUI(false);});
}

function toggleJuriScoringLock(){
    var btn=document.getElementById('btnToggleJuriScoring');
    btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
    fetch('/api/admin/toggle-scoring-lock',{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}
    })
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){
            updateJuriScoringLockUI(d.scoring_unlocked);
            popupSuccess('Status Penjurian Diperbarui',d.message);
        }else{
            popupError('Gagal',d.message||'Terjadi kesalahan.');
        }
    })
    .catch(function(){popupError('Error','Gagal menghubungi server.');})
    .finally(function(){btn.disabled=false;});
}

function updateJuriScoringLockUI(isUnlocked){
    var btn=document.getElementById('btnToggleJuriScoring');
    if(btn)btn.disabled=false;
    if(isUnlocked){
        btn.innerHTML='<i class="fas fa-lock-open"></i> KUNCI PENILAIAN JURI';
        btn.style.background='var(--danger)';btn.style.boxShadow='0 3px 10px rgba(239,68,68,.2)';
    }else{
        btn.innerHTML='<i class="fas fa-lock"></i> BUKA PENILAIAN JURI';
        btn.style.background='var(--success)';btn.style.boxShadow='0 3px 10px rgba(34,197,94,.2)';
    }
}

/* ═══ KIRIM HASIL NOMINASI KE PESERTA (TOGGLE GLOBAL) ═══ */
function loadNominasiPublishStatus(){
    fetch('/api/admin/nominasi-publish-status',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){ updateNominasiPublishUI(!!d.nominasi_published); })
    .catch(function(){ updateNominasiPublishUI(false); });
}
function toggleNominasiPublish(){
    var btn=document.getElementById('btnToggleNominasiPublish');
    if(btn){ btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('/api/admin/toggle-nominasi-publish',{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}
    })
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){
            updateNominasiPublishUI(!!d.nominasi_published);
            popupSuccess('Status Hasil Nominasi', d.message);
        } else { popupError('Gagal', d.message||'Terjadi kesalahan.'); loadNominasiPublishStatus(); }
    })
    .catch(function(){ popupError('Error','Gagal menghubungi server.'); loadNominasiPublishStatus(); })
    .finally(function(){ if(btn) btn.disabled=false; });
}
function updateNominasiPublishUI(isPublished){
    var btn=document.getElementById('btnToggleNominasiPublish');
    if(!btn) return;
    btn.disabled=false;
    if(isPublished){
        btn.innerHTML='<i class="fas fa-ban"></i> CABUT HASIL NOMINASI';
        btn.style.background='var(--danger)'; btn.style.boxShadow='0 3px 10px rgba(239,68,68,.2)';
    } else {
        btn.innerHTML='<i class="fas fa-paper-plane"></i> KIRIM HASIL NOMINASI';
        btn.style.background='var(--success)'; btn.style.boxShadow='0 3px 10px rgba(34,197,94,.2)';
    }
}

/* ── TOGGLE JURI DETAIL (ADMIN) ── */
function toggleJuriDetailAdmin(uid){
    var t=document.getElementById(uid+'-toggle');
    var s=document.getElementById(uid+'-scores');
    if(t.classList.contains('open')){
        t.classList.remove('open');s.classList.remove('open');
    } else {
        t.classList.add('open');s.classList.add('open');
    }
}

/* ═══════════════════════════════════════════════
   PASSWORD VALIDATION (CREATE USER MODAL)
   ═══════════════════════════════════════════════ */
var cPwd=document.getElementById('createPwd');
var cConf=document.getElementById('createPwdConf');
var cSegs=[document.getElementById('cSeg1'),document.getElementById('cSeg2'),document.getElementById('cSeg3'),document.getElementById('cSeg4'),document.getElementById('cSeg5')];

function validateCreatePwd(){
    var val=cPwd.value;
    var errEl=document.getElementById('createPwdErr');
    var barEl=document.getElementById('createStrBar');
    var txtEl=document.getElementById('createStrText');
    for(var i=0;i<cSegs.length;i++)cSegs[i].className='str-seg';
    txtEl.className='str-text';txtEl.style.display='none';
    barEl.style.display='none';errEl.style.display='none';
    cPwd.classList.remove('input-error','input-success');
    if(val.length===0){checkCreateMatch();return;}
    barEl.style.display='flex';txtEl.style.display='block';
    var hasL=/[a-z]/.test(val),hasU=/[A-Z]/.test(val),hasN=/[0-9]/.test(val),hasS=/[^A-Za-z0-9]/.test(val);
    var str=0;if(val.length>=8)str++;if(hasL)str++;if(hasU)str++;if(hasN)str++;if(hasS)str++;
    if(val.length<8||!hasL||!hasU||!hasN||!hasS){
        errEl.style.display='flex';cPwd.classList.add('input-error');
        txtEl.textContent='Belum memenuhi syarat';txtEl.classList.add('w');
        if(str>0)cSegs[0].classList.add('w');
    } else {
        cPwd.classList.add('input-success');
        if(str<=3){cSegs[0].classList.add('m');cSegs[1].classList.add('m');txtEl.textContent='Cukup';txtEl.classList.add('m');cPwd.classList.remove('input-success');}
        else if(str===4){for(var a=0;a<4;a++)cSegs[a].classList.add('s');txtEl.textContent='Kuat';txtEl.classList.add('s');}
        else{for(var b=0;b<5;b++)cSegs[b].classList.add('s');txtEl.textContent='Sangat kuat';txtEl.classList.add('s');}
    }
    checkCreateMatch();
}

function checkCreateMatch(){
    var p=cPwd.value,c=cConf.value;
    var noEl=document.getElementById('createMatchNo'),okEl=document.getElementById('createMatchOk');
    noEl.style.display='none';okEl.style.display='none';
    cConf.classList.remove('input-error','input-success');
    if(c.length===0)return;
    if(p!==c){noEl.style.display='flex';cConf.classList.add('input-error');}
    else{okEl.style.display='flex';cConf.classList.add('input-success');}
}

cPwd.addEventListener('input',validateCreatePwd);
cConf.addEventListener('input',checkCreateMatch);

/* Toggle password visibility */
document.getElementById('toggleCreatePwd').addEventListener('click',function(){
    var ic=this.querySelector('i');
    if(cPwd.type==='password'){cPwd.type='text';ic.classList.replace('fa-eye','fa-eye-slash');}
    else{cPwd.type='password';ic.classList.replace('fa-eye-slash','fa-eye');}
});
document.getElementById('toggleCreatePwdConf').addEventListener('click',function(){
    var ic=this.querySelector('i');
    if(cConf.type==='password'){cConf.type='text';ic.classList.replace('fa-eye','fa-eye-slash');}
    else{cConf.type='password';ic.classList.replace('fa-eye-slash','fa-eye');}
});

/* ═══════════════════════════════════════════════
   LOAD STATISTIK & CHARTS
   ═══════════════════════════════════════════════ */
function loadDashboard(){
    fetch('/api/admin/dashboard-stats',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        document.getElementById('sTotal').innerText=d.total_peserta||0;
        document.getElementById('sDinilai').innerText=d.sudah_dinilai||0;
        document.getElementById('sGrand').innerText=d.grand_edited||0;
        document.getElementById('sBelum').innerText=d.belum_dinilai||0;
        document.getElementById('sJuri').innerText=d.juri_aktif||0;
        document.getElementById('sAvg').innerText=d.rata_rata||0;
        document.getElementById('sSisaTank').innerText=d.sisa_tank||0;
        var totalPesertaUnik = parseInt(d.total_peserta_unik || 0, 10);
        document.getElementById('sPesertaUnik').innerText = totalPesertaUnik;

        var pesertaBelumTankEl = document.getElementById('sPesertaBelumTank');
        if(pesertaBelumTankEl){
            var sudahTank = parseInt(d.peserta_sudah_tank || 0, 10);
            var belumTank = parseInt(d.peserta_belum_tank || 0, 10);

            if(totalPesertaUnik <= 0){
                pesertaBelumTankEl.innerHTML =
                    '<span class="tank-status-muted"><i class="fas fa-circle-info"></i> Belum ada peserta</span>';
            } else {
                pesertaBelumTankEl.innerHTML =
                    '<span class="tank-status-ok"><i class="fas fa-circle-check"></i> ' + sudahTank + ' sudah mengacak nomor tank</span>' +
                    '<span class="tank-status-warn"><i class="fas fa-circle-exclamation"></i> ' + belumTank + ' belum mengacak nomor tank</span>';
            }
        }

        document.getElementById('sSisaTankLabel').innerText='Sisa Tank ('+ (d.global_range_min||1) +' \u2013 '+ (d.global_range_max||1000) +')';
        renderChartKategori(d.per_kategori||{});
        renderChartStatus(d.sudah_dinilai||0,d.grand_edited||0,d.belum_dinilai||0);
        renderChartTop(d.top_10||[]);
    }).catch(function(){});
}

function renderChartKategori(data){
    var labels=Object.keys(data),vals=Object.values(data);
    var colors=['#22D3EE','#A855F7','#10B981','#F59E0B','#EF4444','#14B8A6','#F97316','#6366F1'];
    if(chartKat)chartKat.destroy();
    chartKat=new Chart(document.getElementById('chartKategori'),{
        type:'bar',
        data:{labels:labels,datasets:[{data:vals,backgroundColor:colors.slice(0,labels.length),borderRadius:6,borderSkipped:false}]},
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{legend:{display:false}},
            scales:{
                y:{beginAtZero:true,ticks:{font:{size:10},color:'#94A3B8'},grid:{color:'rgba(255,255,255,.06)'}},
                x:{ticks:{font:{size:10},color:'#94A3B8'},grid:{display:false}}
            }
        }
    });
}

function renderChartStatus(dinilai,grand,belum){
    if(chartStat)chartStat.destroy();
    chartStat=new Chart(document.getElementById('chartStatus'),{
        type:'doughnut',
        data:{
            labels:['Sudah Dinilai','Grand Juri Edit','Belum Dinilai'],
            datasets:[{data:[dinilai,grand,belum],backgroundColor:['#10B981','#A855F7','#F59E0B'],borderWidth:0,spacing:2}]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            cutout:'65%',
            plugins:{
                legend:{position:'bottom',labels:{font:{size:10},padding:12,usePointStyle:true,pointStyleWidth:8,color:'#E2E8F0'}}
            }
        }
    });
}

function renderChartTop(data){
    var labels=[],vals=[],extras=[];
    for(var i=0;i<data.length;i++){
        labels.push(data[i].nama);
        vals.push(data[i].point);
        extras.push({
            point: data[i].point || 0,
            total: data[i].total || 0,
            kategori: data[i].kategori || '—',
            kelas: data[i].kelas || '—',
            tank: data[i].nomor_tank || '—'
        });
    }

    var barColors = [
        '#f59e0b','#d97706','#b45309','#92400e','#78350f',
        '#f59e0b','#d97706','#b45309','#92400e','#78350f'
    ];

    if(chartTop)chartTop.destroy();
    chartTop=new Chart(document.getElementById('chartTop'),{
        type:'bar',
        data:{
            labels:labels,
            datasets:[{
                label:'Point',
                data:vals,
                backgroundColor:barColors,
                borderRadius:4,
                borderSkipped:false,
                barThickness:22
            }]
        },
        options:{
            indexAxis:'y',
            responsive:true,
            maintainAspectRatio:false,
            layout:{padding:{right:10}},
            plugins:{
                legend:{display:false},
                tooltip:{
                    backgroundColor:'#1e293b',
                    titleFont:{family:'Plus Jakarta Sans',size:13,weight:'800'},
                    bodyFont:{family:'Plus Jakarta Sans',size:12,weight:'600'},
                    padding:14,
                    cornerRadius:10,
                    displayColors:false,
                    callbacks:{
                        title:function(items){
                            return items[0].label;
                        },
                        label:function(item){
                            var e=extras[item.dataIndex];
                            return 'Point: '+e.point;
                        },
                        afterLabel:function(item){
                            var e=extras[item.dataIndex];
                            return [
                                'Total Nilai: '+e.total,
                                'Kategori: '+e.kategori,
                                'Kelas: '+e.kelas,
                                'No. Tank: '+e.tank
                            ];
                        }
                    }
                }
            },
            scales:{
                x:{
                    beginAtZero:true,
                    title:{display:true,text:'POINT',font:{size:10,family:'Plus Jakarta Sans',weight:'800'},color:'#FCD34D'},
                    ticks:{font:{size:10,family:'Plus Jakarta Sans'},color:'#94A3B8'},
                    grid:{color:'rgba(255,255,255,.06)'}
                },
                y:{
                    ticks:{font:{size:9,family:'Plus Jakarta Sans',weight:'600'},color:'#E2E8F0'},
                    grid:{display:false}
                }
            }
        }
    });
}

/* ═══════════════════════════════════════════════
   LOAD DATA PENILAIAN
   ═══════════════════════════════════════════════ */
function loadScoringData(callback){
    var params=new URLSearchParams();
    var s=document.getElementById('filterSearch').value;
    var k=document.getElementById('filterKategori').value;
    var st=document.getElementById('filterStatus').value;
    var tn=document.getElementById('filterTank').value;
    if(s)params.set('search',s);if(k)params.set('kategori',k);if(st)params.set('status',st);
    if(tn)params.set('tank',tn);
    fetch('/api/admin/scoring-data?'+params.toString(),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){allScoringData=data;renderTable(data);
        if(typeof callback === 'function'){
            callback();
        }
    })
    .catch(function(){});
}

function renderTable(data){
    var tb=document.getElementById('tBody');tb.innerHTML='';

    /* ★ RESET state checkbox saat re-render */
    var master = document.getElementById('checkAllRows');
    if(master){ master.checked = false; master.indeterminate = false; }
    var bulkBtn = document.getElementById('btnBulkDelete');
    if(bulkBtn) bulkBtn.style.display = 'none';

    if(!data||data.length===0){tb.innerHTML='<tr><td colspan="12"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data.</p></div></td></tr>';return;}
    for(var i=0;i<data.length;i++){
        var p=data[i],tr=document.createElement('tr');

        /* ★ FIX: DINILAI OLEH — tampilkan semua juri */
        var jh='<span style="color:var(--light);font-size:11px;">—</span>';
        if(p.juri_list&&p.juri_list.length>0){
            jh='<div class="juri-info">';
            p.juri_list.forEach(function(j){
                if(j.is_grand && j.is_editor){
                    jh+='<div class="g-name"><i class="fas fa-pen-to-square" style="font-size:9px;"></i> '+esc(j.name)+' <span style="font-size:9px;opacity:.7;">(edit)</span></div>';
                } else if(j.is_grand){
                    jh+='<div class="g-name"><i class="fas fa-crown" style="font-size:9px;"></i> '+esc(j.name)+'</div>';
                } else {
                    jh+='<div><i class="fas fa-user-pen" style="font-size:9px;color:var(--primary);margin-right:2px;"></i><span class="j-name">'+esc(j.name)+'</span></div>';
                }
            });
            jh+='</div>';
        }

        /* Status */
        var sc, st;

        if(!p.nomor_tank || p.status === 'Belum Dapat Tank'){
            sc = 's-belum';
            st = 'BELUM TANK';
        }
        else if(p.is_locked){
            sc = 's-grand';
            st = '<i class="fas fa-lock" style="margin-right:3px;font-size:8px;"></i>TERKUNCI';
        }
        else if(p.grand_juri_nama || p.status === 'Grand Juri Edit'){
            sc = 's-grand';
            st = 'GRAND EDIT';
        }
        else if(p.status === 'Sudah Dinilai'){
            sc = 's-dinilai';
            st = 'DINILAI';
        }
        else{
            sc = 's-belum';
            st = 'BELUM DINILAI';
        }

        /* ★ FIX: TOTAL NILAI — dari semua juri */
        var tv=p.total_nilai_semua>0
            ?'<div style="font-weight:800;">'+p.total_nilai_semua+'</div><div style="font-size:9px;color:var(--light);font-weight:600;"><i class="fas fa-users" style="font-size:8px;margin-right:2px;"></i>'+p.jumlah_juri+' juri</div>'
            :'<span class="total-val zero">—</span>';

        /* ★ FIX: POINT — dari rata-rata semua juri */
        var pv='';
        if(p.final_point>0){
            pv='<div style="font-size:13px;font-weight:900;color:#f59e0b;">'+p.final_point+'</div>';
            if(p.total_bonus>0) pv+='<div style="font-size:8px;color:#16a34a;font-weight:800;"><i class="fas fa-trophy" style="font-size:7px;"></i> +'+p.total_bonus+'</div>';
        } else if(p.total_point>0){
            pv='<span style="font-size:13px;font-weight:900;color:#f59e0b;">'+p.total_point+'</span>';
        } else {
            pv='<span style="font-size:11px;color:var(--light);">—</span>';
        }

        /* ASAL/TEAM */
        var asalHtml='<span style="color:var(--light);font-size:11px;">—</span>';
        if(p.detail_anggota&&p.detail_anggota!=='—'){
            asalHtml='<div style="font-size:11px;color:var(--text-muted);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="'+esc(p.detail_anggota)+'"><i class="fas fa-building" style="font-size:9px;color:var(--primary);margin-right:3px;"></i>'+esc(p.detail_anggota)+'</div>';
        }

        tr.innerHTML=
            '<td style="text-align:center;padding-right:6px;"><input type="checkbox" class="row-check" data-id="'+p.id+'" data-name="'+esc(p.nama_peserta).replace(/"/g,'&quot;')+'" onchange="onRowCheckChange()" style="cursor:pointer;width:15px;height:15px;accent-color:var(--cyan-400);vertical-align:middle;"></td>'+
            '<td style="font-weight:700;color:var(--light);font-size:11px;">'+(i+1)+'</td>'+
            '<td style="font-weight:700;">'+esc(p.nama_peserta)+(p.has_foto?' <span title="Sudah mengirim foto" style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:800;color:#34D399;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);padding:2px 6px;border-radius:999px;vertical-align:middle;"><i class=\'fas fa-camera\' style=\'font-size:8px;\'></i> FOTO</span>':'')+'</td>'+
            '<td style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;">'+esc(p.kategori)+'</td>'+
            '<td style="font-size:11px;color:var(--muted);">'+esc(p.kelas)+'</td>'+
            '<td style="font-weight:700;color:var(--primary);">Tank '+(p.nomor_tank||'—')+'</td>'+
            '<td>'+asalHtml+'</td>'+
            '<td>'+jh+'</td>'+
            '<td>'+tv+'</td>'+
            '<td style="text-align:center;">'+pv+'</td>'+
            '<td><span class="status-badge '+sc+'">'+st+'</span></td>'+
            '<td><div style="display:flex;gap:4px;">'+
            '<button class="btn-xs blue" onclick="openDetail('+i+')" title="Lihat Detail"><i class="fas fa-eye"></i></button>'+
            '<button class="btn-xs gold" onclick="openEditKatKelas('+i+')" title="Edit Kategori & Kelas"><i class="fas fa-tags"></i></button>'+
            (p.nomor_tank && !p.is_locked
                ? '<button class="btn-xs purple" onclick="openEditAdmin('+i+')" title="Edit Nilai"><i class="fas fa-pen-to-square"></i></button>'
                : '<button class="btn-xs purple" style="opacity:.35;cursor:not-allowed;" disabled title="'+(p.nomor_tank ? 'Nilai terkunci — buka kunci dari Point Ranking' : 'Isi nomor tank terlebih dahulu')+'"><i class="fas fa-pen-to-square"></i></button>'
            )+
            '<button class="btn-xs red" onclick="deleteIkan('+p.id+',\''+esc(p.nama_peserta).replace(/'/g,"\\'")+'\')" title="Hapus Data"><i class="fas fa-trash-can"></i></button>'+
            '</div></td>';
      tb.appendChild(tr);
    }
}

/* ═══ EDIT KATEGORI & KELAS ═══ */
function openEditKatKelas(idx){
    var p = allScoringData[idx];
    if(!p) return;

    document.getElementById('editKKIdx').value = idx;

    var nama = p.nama_peserta || '';
    var jenis = p.jenis_keanggotaan || 'perorangan';
    var detail = p.detail_anggota || '';

    if(detail === '—') detail = '';

    document.getElementById('editKKNamaPreview').textContent = nama || '-';
    document.getElementById('editKKNamaInput').value = nama;
    document.getElementById('editKKJenis').value = jenis === 'team' ? 'team' : 'perorangan';
    document.getElementById('editKKDetail').value = detail;
    document.getElementById('editKKTank').textContent = 'Tank ' + (p.nomor_tank || '—');

    var tankInput = document.getElementById('editKKTankInput');
    if(tankInput){
        tankInput.value = p.nomor_tank || '';
    }

    onEditKKJenisChange();

    var katSel = document.getElementById('editKKKat');
    katSel.value = p.kategori || '';

    onEditKKKatChange();

    if(noKelasKategori.indexOf(p.kategori) === -1){
        document.getElementById('editKKKelas').value = p.kelas || '';
    } else {
        document.getElementById('editKKKelas').value = '';
    }

    openModal('modalEditKatKelas');

    var availableBox = document.getElementById('availableTankInfo');
    if(availableBox){
        availableBox.style.display = 'none';
        availableBox.innerHTML = '';
    }
}

function onEditKKJenisChange(){
    var jenis = document.getElementById('editKKJenis').value;
    var label = document.getElementById('editKKDetailLabel');
    var input = document.getElementById('editKKDetail');

    if(jenis === 'team'){
        label.textContent = 'Nama Team / Club';
        input.placeholder = 'Contoh: LCI Team / Club Louhan';
    } else {
        label.textContent = 'Kota Asal';
        input.placeholder = 'Contoh: Jakarta / Bandung / Surabaya';
    }
}

function onEditKKKatChange(){
    var kat=document.getElementById('editKKKat').value;
    var kelasSel=document.getElementById('editKKKelas');
    var kelasWrap=document.getElementById('editKKKelasWrap');
    if(noKelasKategori.indexOf(kat)!==-1){
        kelasSel.value='';
        kelasWrap.style.display='none';
    } else {
        kelasWrap.style.display='';
    }
}

function loadAvailableTankBlock(){
    var box = document.getElementById('availableTankInfo');

    if(!box){
        return;
    }

    box.style.display = 'block';
    box.innerHTML =
        '<div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:#94A3B8;font-size:11px;font-weight:700;">' +
            '<i class="fas fa-spinner fa-spin"></i> Mengecek nomor kosong...' +
        '</div>';

    fetch('/api/admin/available-tank-block?_t=' + Date.now(), {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(r){
        return r.json();
    })
    .then(function(d){
        if(!d.success){
            box.innerHTML =
                '<div style="padding:10px;border-radius:10px;background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.35);color:#FCA5A5;font-size:11px;font-weight:700;">' +
                    esc(d.message || 'Gagal mengecek nomor kosong.') +
                '</div>';
            return;
        }

        if(!d.available_numbers || d.available_numbers.length === 0){
        box.innerHTML =
            '<div class="available-tank-error">' +
                esc(d.message || 'Semua nomor tank sudah terisi.') +
            '</div>';
            return;
        }

        var html = '';

        html +=
            '<div style="padding:10px;border-radius:10px;background:rgba(16,185,129,.10);border:1px solid rgba(16,185,129,.30);margin-bottom:8px;">' +
                '<div style="color:#6EE7B7;font-size:11px;font-weight:900;margin-bottom:4px;">' +
                    '<i class="fas fa-circle-check"></i> Nomor kosong ditemukan di rentang ' + esc(d.range_label) +
                '</div>' +
                '<div style="color:#94A3B8;font-size:10.5px;font-weight:600;line-height:1.5;">' +
                    'Kosong: <b style="color:#fff;">' + d.available_count + '</b> nomor. ' +
                    'Klik salah satu nomor untuk mengisi field Nomor Tank.' +
                '</div>' +
            '</div>';

        html += '<div class="available-tank-box">';

        html +=
            '<div class="available-tank-head">' +
                '<div class="available-tank-title">' +
                    '<i class="fas fa-circle-check"></i> Nomor kosong ditemukan di rentang ' + esc(d.range_label) +
                '</div>' +
                '<div class="available-tank-desc">' +
                    'Kosong: <b style="color:#fff;">' + d.available_count + '</b> nomor. ' +
                    'Klik salah satu nomor untuk mengisi field Nomor Tank.' +
                '</div>' +
            '</div>';

        html += '<div class="available-tank-scroll">';
        html += '<div class="available-tank-grid">';

        for(var i = 0; i < d.available_numbers.length; i++){
            var n = d.available_numbers[i];

            html +=
                '<button type="button" class="available-tank-num" onclick="setEditTankNumber(' + n + ')">' +
                    n +
                '</button>';
        }

        html += '</div>';
        html += '</div>';
        html += '</div>';

        box.innerHTML = html;
    })
    .catch(function(){
    box.innerHTML =
        '<div class="available-tank-error">' +
            esc(d.message || 'Gagal mengecek nomor kosong.') +
        '</div>';
    });
}

function setEditTankNumber(n){
    var input = document.getElementById('editKKTankInput');

    if(input){
        input.value = n;
        input.focus();
    }
}

function submitEditKatKelas(){
    var idx = parseInt(document.getElementById('editKKIdx').value, 10);
    var p = allScoringData[idx];

    if(!p) return;

    var nama = (document.getElementById('editKKNamaInput').value || '').trim();
    var jenis = document.getElementById('editKKJenis').value;
    var detail = (document.getElementById('editKKDetail').value || '').trim();
    var kat = document.getElementById('editKKKat').value;
    var kelas = document.getElementById('editKKKelas').value;
    var nomorTank = (document.getElementById('editKKTankInput').value || '').trim();

    if(!nama){
        popupError('Nama Wajib', 'Nama peserta tidak boleh kosong.');
        return;
    }

    if(jenis !== 'perorangan' && jenis !== 'team'){
        popupError('Jenis Keanggotaan Wajib', 'Pilih jenis keanggotaan yang valid.');
        return;
    }

    if(!detail){
        popupError(
            jenis === 'team' ? 'Nama Team / Club Wajib' : 'Kota Asal Wajib',
            jenis === 'team'
                ? 'Isi nama team / club terlebih dahulu.'
                : 'Isi kota asal terlebih dahulu.'
        );
        return;
    }

    if(!kat){
        popupError('Kategori Wajib', 'Pilih kategori terlebih dahulu.');
        return;
    }

    if(noKelasKategori.indexOf(kat) === -1 && !kelas){
        popupError('Kelas Wajib', 'Pilih kelas terlebih dahulu.');
        return;
    }
    if(nomorTank !== ''){
        var tankNum = parseInt(nomorTank, 10);

        if(isNaN(tankNum) || tankNum < 1 || String(tankNum) !== nomorTank){
            popupError('Nomor Tank Tidak Valid', 'Nomor tank harus berupa angka bulat lebih dari 0.');
            return;
        }
    }

    var btn = document.getElementById('btnSaveKK');
    var orig = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('ikan_id', p.id);
    fd.append('nama_peserta', nama);
    fd.append('jenis_keanggotaan', jenis);
    fd.append('detail_anggota', detail);
    fd.append('kategori', kat);
    fd.append('kelas', kelas);
    fd.append('nomor_tank', nomorTank);

    fetch('/api/admin/edit-kategori-kelas', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(r){
        if(!r.ok){
            return r.json().then(function(d){ throw d; });
        }

        return r.json();
    })
    .then(function(d){
        if(d.success){
            closeModal('modalEditKatKelas');

            loadScoringData();
            loadDashboard();

            if(typeof loadAdminPointRanking === 'function'){
                loadAdminPointRanking();
            }

            popupSuccess('Berhasil', d.message || 'Data peserta, kategori, dan kelas berhasil diperbarui.');
        } else {
            popupError('Gagal', d.message || 'Terjadi kesalahan.');
        }
    })
    .catch(function(e){
        if(e && e.message){
            popupError('Gagal', esc(e.message));
        } else {
            popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
        }
    })
    .finally(function(){
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

var filterT;
document.getElementById('filterSearch').addEventListener('input',function(){clearTimeout(filterT);filterT=setTimeout(loadScoringData,300);});
document.getElementById('filterKategori').addEventListener('change',loadScoringData);
document.getElementById('filterStatus').addEventListener('change',loadScoringData);
document.getElementById('filterTank').addEventListener('input',function(){clearTimeout(filterT);filterT=setTimeout(loadScoringData,300);});

/* ═══════════════════════════════════════════════
   DETAIL NILAI MODAL (UPDATE: LANGSUNG DARI DATA TABEL)
   ═══════════════════════════════════════════════ */
function openDetail(idx){
    openModal('modalDetail');
    var p = allScoringData[idx];
    if(!p){document.getElementById('detailBody').innerHTML='<div class="empty-state">Data tidak ditemukan.</div>';return;}
    renderDetailView(p);
    loadAdminIkanFotos(p.id);
}

function renderDetailView(p){
    var html='';

    /* Banner */
    html+='<div class="detail-banner"><div><h4>'+esc(p.nama_peserta)+'</h4><div class="meta">';
    html+='<span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank||'—')+'</span>';
    html+='<span><i class="fas fa-tag"></i> '+esc(p.kategori)+' - Kelas '+esc(p.kelas)+'</span>';
    if(p.detail_anggota&&p.detail_anggota!=='—') html+='<span><i class="fas fa-users"></i> '+esc(p.detail_anggota)+'</span>';
    html+='</div></div>';
    html+='<div class="detail-total-chip"><i class="fas fa-star" style="margin-right:4px;"></i> '+p.total_nilai_semua+' <span style="font-size:10px;font-weight:600;opacity:.8;">('+p.jumlah_juri+' juri)</span></div></div>';

    /* Galeri foto ikan (dimuat async) */
    html+='<div id="admFotoGallery" style="margin-bottom:14px;"><div style="padding:14px;text-align:center;color:var(--text-mid,#94a3b8);font-size:12px;"><i class="fas fa-spinner fa-spin"></i> Memuat foto...</div></div>';

    if(p.grand_juri_nama) html+='<div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;margin-bottom:14px;display:flex;gap:6px;align-items:flex-start;"><i class="fas fa-circle-info" style="margin-top:1px;"></i><span>Nilai final oleh <b>'+esc(p.grand_juri_nama)+'</b>.</span></div>';

    if(!p.all_scorings||p.all_scorings.length===0){
        html+='<div class="empty-state" style="padding:30px;"><i class="fas fa-clipboard-list"></i><p>Belum ada nilai.</p></div>';
        document.getElementById('detailBody').innerHTML=html;return;
    }

    p.all_scorings.forEach(function(sc,idx){
        var uid='adm-dj-'+idx;
        var iconCls='fas fa-user-pen';
        var label='Juri: '+esc(sc.juri_name);

        if(sc.edited_by_grand && sc.grand_juri_name){
            label+=' <span style="color:var(--purple);font-size:11px;font-weight:600;"><i class="fas fa-pen-to-square" style="font-size:9px;"></i> diedit: '+esc(sc.grand_juri_name)+'</span>';
        }

        html+='<div class="detail-juri-accordion">';
        html+='<div class="detail-juri-toggle" id="'+uid+'-toggle" onclick="toggleJuriDetailAdmin(\''+uid+'\')">';
        html+='<span class="dj-name"><i class="'+iconCls+'" style="font-size:11px;color:var(--primary);"></i> '+label+'</span>';
        html+='<span style="display:flex;align-items:center;gap:10px;"><span class="dj-total">'+sc.total_nilai+'</span><i class="fas fa-chevron-down dj-arrow"></i></span>';
        html+='</div>';

        html+='<div class="detail-juri-scores" id="'+uid+'-scores">';
        var nd=sc.nilai_detail;
        if(!nd||typeof nd!=='object'){
            html+='<div style="padding:16px;text-align:center;color:var(--light);font-size:12px;">Tidak ada data nilai.</div>';
        } else {
            Object.keys(formFields).forEach(function(kat){
                var fields=formFields[kat];
                if(kat==='face'&&nd.face){
                    if(nd.face.face===undefined&&(nd.face.pipi!==undefined||nd.face.mata!==undefined)){
                        fields=formFieldsLegacy.face;
                    }
                }
                html+='<div style="margin-bottom:10px;border:1px solid var(--border);border-radius:10px;overflow:hidden;">';
                var katNilai=nd[kat]||{},sub=0;
                fields.forEach(function(f){if(f.type==='defect')return;var fv=katNilai[f.id];if(fv===undefined&&f.id==='shining'&&katNilai.shinning!==undefined)fv=katNilai.shinning;if(fv!==undefined&&fv!==null)sub+=parseInt(fv)||0;});

                var defectEval=sc.defect_eval||{};
                var penaltyKey=kat+'_penalty';
                var penaltyStr=defectEval[penaltyKey]||'';
                var defectPersen=0,hasDefect=false,defectNames=[];
                if(penaltyStr&&penaltyStr!==''){
                    hasDefect=true;defectPersen=parseInt(penaltyStr)||0;
                    var rawKey='raw_'+kat+'_penalty',rawDefs=sc[rawKey];
                    if(rawDefs){if(!Array.isArray(rawDefs))rawDefs=[rawDefs];defectNames=rawDefs.filter(function(v){return v&&v!=='0';});}
                }
                var displaySub=sub;
                if(hasDefect&&defectPersen>0){displaySub=Math.round(sub*(1-defectPersen/100)*10)/10;}

                if(hasDefect&&defectPersen>0){
                    html+='<div class="detail-kat-mini-admin"><span>'+kat.toUpperCase()+'</span><span>Subtotal: <s style="color:var(--light);font-size:10px;">'+sub+'</s> → <strong style="color:var(--primary);">'+displaySub+'</strong> <span style="color:var(--danger);font-weight:700;">(-'+defectPersen+'%)</span></span></div>';
                }else{
                    html+='<div class="detail-kat-mini-admin"><span>'+kat.toUpperCase()+'</span><span>Subtotal: '+sub+'</span></div>';
                }

                var hasDefectField=fields.some(function(f){return f.type==='defect';});
                fields.forEach(function(f){
                    if(f.type==='defect')return;
                var val=katNilai[f.id];if(val===undefined&&f.id==='shining'&&katNilai.shinning!==undefined)val=katNilai.shinning;var has=(val!==undefined&&val!==null&&val!=='');
                html+='<div class="detail-field-row-admin"><div><div class="detail-field-admin-name">'+f.label+'</div><div class="detail-field-admin-meta">'+f.desc+'</div></div><span class="score-chip-admin '+(has?'filled':'empty')+'">'+(has?val:'N/A')+'</span></div>';
                });

                if(hasDefectField){
                    if(hasDefect&&defectPersen>0&&defectNames.length>0){
                        var isMayor=defectPersen>=30;
                        html+='<div class="detail-field-row-admin" style="background:'+(isMayor?'var(--danger-lt)':'#fff7ed')+';">';
                        html+='<div><div class="detail-field-admin-name" style="color:'+(isMayor?'var(--danger)':'#c2410c')+';"><i class="fas fa-exclamation-triangle" style="margin-right:4px;font-size:10px;"></i>Defect '+(isMayor?'(MAYOR)':'(MINOR)')+'</div>';
                        html+='<div class="detail-field-admin-meta" style="color:'+(isMayor?'#991b1b':'#9a3412')+';font-weight:600;">'+defectNames.join(', ')+'</div></div>';
                        html+='<span class="score-chip-admin" style="background:'+(isMayor?'var(--danger-lt)':'#fff7ed')+';color:'+(isMayor?'var(--danger)':'#c2410c')+';font-weight:800;">-'+defectPersen+'%</span></div>';
                    }else{
                        html+='<div class="detail-field-row-admin" style="background:var(--success-lt);">';
                        html+='<div><div class="detail-field-admin-name" style="color:var(--success);"><i class="fas fa-check-circle" style="margin-right:4px;font-size:10px;"></i>Defect</div>';
                        html+='<div class="detail-field-admin-meta" style="color:#15803d;font-weight:600;">Tidak ada defect</div></div>';
                        html+='<span class="score-chip-admin" style="background:var(--success-lt);color:var(--success);font-weight:800;">AMAN</span></div>';
                    }
                }
                html+='</div>';
            });
        }
        html+='</div></div>';
    });

    /* ★ Ringkasan Nilai & Point */
    if(p.detail_list_per_juri&&p.detail_list_per_juri.length>0){
        html+='<div style="margin-top:16px;border:2px solid rgba(124,58,237,.25);border-radius:12px;overflow:hidden;">';
        html+='<div style="padding:12px 16px;background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(124,58,237,.04));border-bottom:2px solid rgba(124,58,237,.25);display:flex;justify-content:space-between;align-items:center;">';
        html+='<span style="font-size:13px;font-weight:800;color:#FFFFFF;"><i class="fas fa-calculator" style="margin-right:6px;color:var(--purple);"></i>Ringkasan Nilai & Point</span>';
        html+='<span style="font-size:11px;color:var(--purple);font-weight:700;">'+p.jumlah_juri+' juri</span>';
        html+='</div>';

        html+='<table style="width:100%;border-collapse:collapse;font-size:12px;">';
        html+='<thead><tr style="background:rgba(124,58,237,.08);"><th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid rgba(124,58,237,.20);">JURI</th><th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid rgba(124,58,237,.20);">TOTAL NILAI</th></tr></thead>';
        html+='<tbody>';
        var grandTotal=0;
        var rowNum=0;
        p.detail_list_per_juri.forEach(function(j){
            if(!j.is_grand){
                rowNum++;
                grandTotal+=j.total_nilai;
                var rowBg=rowNum%2===0?'rgba(255,255,255,0.02)':'transparent';
                html+='<tr style="background:'+rowBg+';"><td style="padding:10px 16px;font-weight:600;border-bottom:1px solid var(--bd-1);color:var(--text-hi);">'+esc(j.juri_name)+'</td><td style="padding:10px 16px;font-weight:800;text-align:right;border-bottom:1px solid var(--bd-1);color:var(--text-hi);">'+j.total_nilai+'</td></tr>';
            }
        });
        html+='<tr style="background:rgba(124,58,237,.08);border-top:2px solid rgba(124,58,237,.25);"><td style="padding:12px 16px;font-weight:800;color:var(--purple);font-size:11px;text-transform:uppercase;letter-spacing:.3px;">Total Semua Juri</td>';
        html+='<td style="padding:12px 16px;font-weight:900;text-align:right;color:var(--purple);font-size:16px;">'+grandTotal+'</td></tr>';
        html+='</tbody></table>';

        html+='<div style="display:grid;grid-template-columns:1fr auto;border-top:2px solid rgba(124,58,237,.25);">';
        html+='<div style="padding:14px 16px;background:rgba(255,255,255,0.02);display:flex;flex-direction:column;justify-content:center;gap:2px;">';
        html+='<div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Total Point</div>';
        html+='<div style="font-size:10px;color:var(--text-muted);">Dihitung dari '+p.jumlah_juri+' juri</div>';
        html+='</div>';
        html+='<div style="padding:14px 20px;background:rgba(255,255,255,0.02);display:flex;align-items:center;justify-content:flex-end;min-width:160px;">';
        var finalPt=p.final_point??p.total_point??0;
        var basePt=p.total_point??0;
        var bonusPt=p.total_bonus??0;
        html+='<div style="text-align:right;">';
        html+='<div style="font-size:22px;font-weight:900;color:var(--gold-300);line-height:1;">'+finalPt+'</div>';
        if(bonusPt>0) html+='<div style="font-size:9px;color:#34D399;font-weight:700;margin-top:3px;">Dasar '+basePt+' + Bonus +'+bonusPt+'</div>';
        html+='</div></div></div>';

        html+='</div>';
    }

    /* Point Breakdown */
    if(p.point_breakdown){
        var pb=p.point_breakdown;
        html+='<div style="margin-top:16px;border:2px solid rgba(245,158,11,.25);border-radius:12px;overflow:hidden;">';
        html+='<div style="padding:10px 16px;background:linear-gradient(135deg,rgba(245,158,11,.10),rgba(245,158,11,.04));border-bottom:1px solid rgba(245,158,11,.20);display:flex;justify-content:space-between;align-items:center;">';
        html+='<span style="font-size:11px;font-weight:800;color:#FFFFFF;text-transform:uppercase;"><i class="fas fa-trophy" style="margin-right:6px;color:var(--gold-400);"></i>SISTEM POINT</span>';
        html+='<span style="font-size:11px;font-weight:700;color:var(--gold-300);">Total: <b>'+pb.total+'</b></span>';
        html+='</div><div style="padding:0;">';
        var katLabels={'overall':'Overall','head':'Head','face':'Face','body':'Body Shape','marking':'Marking','pearl':'Pearl','color':'Color','finnage':'Finnage'};
        for(var ki in katLabels){
            if(!pb[ki])continue;
            var kd=pb[ki];
            html+='<div style="display:grid;grid-template-columns:120px 1fr 80px;align-items:center;padding:8px 14px;border-bottom:1px solid var(--bd-1);font-size:11px;">';
            html+='<span style="font-weight:700;color:#FFFFFF;">'+katLabels[ki]+'</span>';
            html+='<span style="color:var(--text-mid);font-size:10px;">'+kd.parts.join(' + ')+'</span>';
            html+='<span style="text-align:right;font-weight:900;color:var(--gold-300);">'+kd.point+'</span>';
            html+='</div>';
        }
        html+='<div style="display:grid;grid-template-columns:1fr 80px;align-items:center;padding:10px 14px;font-size:12px;background:rgba(245,158,11,.08);">';
        html+='<span style="font-weight:800;color:#FFFFFF;">TOTAL POINT</span>';
        html+='<span style="text-align:right;font-weight:900;font-size:16px;color:var(--gold-300);">'+pb.total+'</span>';
        html+='</div></div></div>';
    }

    document.getElementById('detailBody').innerHTML=html;
}

/* ═══════════════════════════════════════════════
   KELOLA USER
   ═══════════════════════════════════════════════ */
var roleColors={admin:'#2563eb',juri:'#16a34a',grand_juri:'#7c3aed',user:'#94a3b8'};
var roleLabels={admin:'ADMIN',juri:'JURI',grand_juri:'GRAND JURI',user:'USER'};
var roleBadgeCls={admin:'role-admin',juri:'role-juri',grand_juri:'role-grand',user:'role-user'};

function loadUsers(){
    var c=document.getElementById('userList');
    c.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>';
    fetch(window.ADMIN_ROUTES.listUsers,{headers:{'Accept':'application/json'}})
    .then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(function(data){
        if(!Array.isArray(data)){
            c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">Error</p></div>';
            document.getElementById('userCount').textContent='Error';return;
        }
        allUsersCache=data;
        document.getElementById('searchUser').value='';
        filterUsers('');
    })
    .catch(function(err){
        c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">'+esc(err.message)+'</p></div>';
        document.getElementById('userCount').textContent='Error';
    });
}

/* ★ CREATE USER — validasi password lengkap */
function submitCreateUser(){
    var form=document.getElementById('formCreateUser');
    var fd=new FormData(form);fd.append('_token',getCsrf());
    var name=fd.get('name'),email=fd.get('email'),pw=cPwd.value,conf=cConf.value,role=fd.get('role');

    if(!name||!email||!pw||!conf||!role){popupError('Form Tidak Lengkap','Semua field wajib diisi.');return;}

    /* Validasi password sama seperti register */
    var hasL=/[a-z]/.test(pw),hasU=/[A-Z]/.test(pw),hasN=/[0-9]/.test(pw),hasS=/[^A-Za-z0-9]/.test(pw);
    if(pw.length<8||!hasL||!hasU||!hasN||!hasS){
        document.getElementById('createPwdErr').style.display='flex';
        cPwd.classList.add('input-error');cPwd.focus();
        popupError('Password Tidak Valid','Password wajib mengandung:<br><div style="text-align:left;margin-top:6px;line-height:1.8;">• Min. <strong>8 karakter</strong><br>• Huruf <strong>kecil</strong> (a-z)<br>• Huruf <strong>besar</strong> (A-Z)<br>• <strong>Angka</strong> (0-9)<br>• <strong>Simbol</strong> (!@#$% dll)</div>');
        return;
    }
    if(pw!==conf){
        document.getElementById('createMatchNo').style.display='flex';
        cConf.classList.add('input-error');cConf.focus();
        popupError('Password Tidak Cocok','Konfirmasi password tidak sesuai dengan password utama.');return;
    }

    fetch('/api/admin/create-user',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){if(!r.ok)return r.json().then(function(d){throw d;});return r.json();})
    .then(function(d){
        if(d.success){closeModal('modalCreate');form.reset();loadUsers();popupSuccess('User Berhasil Ditambahkan!','User <strong>'+esc(name)+'</strong> didaftarkan sebagai <strong>'+esc(roleLabels[role])+'</strong>.');}
        else popupError('Gagal',d.message||'Terjadi kesalahan.');
    })
    .catch(function(e){
        if(e.errors){var msg='';var keys=Object.keys(e.errors);for(var i=0;i<keys.length;i++)msg+='<div style="margin-bottom:4px;">• '+esc(e.errors[keys[i]][0])+'</div>';popupError('Validasi Gagal',msg);}
        else popupError('Kesalahan Jaringan','Gagal menyimpan.');
    });
}

/* ★ DELETE USER */
function deleteUser(uid,name){
    openConfirmPopup(
        'Hapus User',
        'Yakin ingin menghapus <strong>'+esc(name)+'</strong>?<br><span style="font-size:11px;color:var(--danger);">Tindakan ini tidak dapat dibatalkan.</span>',
        'Ya, Hapus',
        function(){
            var fd=new FormData();fd.append('_token',getCsrf());fd.append('user_id',uid);
            fetch('/api/admin/delete-user',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.success){loadUsers();popupSuccess('User Dihapus','<strong>'+esc(name)+'</strong> berhasil dihapus dari sistem.');}
                else popupError('Gagal Menghapus',d.message||'Terjadi kesalahan.');
            })
            .catch(function(){popupError('Kesalahan Jaringan','Gagal menghubungi server.');});
        }
    );
}

var currentPwdVisible = false;

function openPwdModal(id,name){
    document.getElementById('pwdUserId').value=id;
    document.getElementById('pwdTarget').textContent=name;
    document.getElementById('pwdNew').value='';

    /* Reset toggle ke posisi TUTUP */
    currentPwdVisible = false;
    document.getElementById('togglePwdIcon').className = 'fas fa-eye-slash';
    document.getElementById('togglePwdLabel').textContent = 'TUTUP';

    var plainPwd = plainPwdMap[id] || '';
    var display = document.getElementById('pwdCurrentDisplay');
    var noData = document.getElementById('pwdNoData');
    var toggleBtn = document.getElementById('togglePwdView');

    if(plainPwd !== ''){
        display.textContent = '••••••••';
        display.style.display = 'block';
        noData.style.display = 'none';
        toggleBtn.style.display = 'flex';
    } else {
        display.textContent = '—';
        display.style.display = 'block';
        noData.style.display = 'block';
        toggleBtn.style.display = 'none';
    }

    /* Reset toggle input baru */
    var newInput = document.getElementById('pwdNew');
    newInput.type = 'password';
    document.getElementById('toggleNewPwd').querySelector('i').className = 'fas fa-eye';

    openModal('modalPwd');
}

function toggleCurrentPwd(){
    var id = document.getElementById('pwdUserId').value;
    var plainPwd = plainPwdMap[id] || '';
    var display = document.getElementById('pwdCurrentDisplay');

    currentPwdVisible = !currentPwdVisible;

    if(currentPwdVisible){
        display.textContent = plainPwd;
        document.getElementById('togglePwdIcon').className = 'fas fa-eye';
        document.getElementById('togglePwdLabel').textContent = 'LIHAT';
    } else {
        display.textContent = '••••••••';
        document.getElementById('togglePwdIcon').className = 'fas fa-eye-slash';
        document.getElementById('togglePwdLabel').textContent = 'TUTUP';
    }
}

function toggleNewPwdInput(){
    var input = document.getElementById('pwdNew');
    var icon = document.getElementById('toggleNewPwd').querySelector('i');
    if(input.type === 'password'){
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function submitPwd(){
    var pw = document.getElementById('pwdNew').value;

    if(!pw){
        popupError('Password Kosong','Masukkan password baru terlebih dahulu.');
        return;
    }

    var hasL = /[a-z]/.test(pw);
    var hasU = /[A-Z]/.test(pw);
    var hasN = /[0-9]/.test(pw);
    var hasS = /[^A-Za-z0-9]/.test(pw);

    if(pw.length < 8 || !hasL || !hasU || !hasN || !hasS){
        var missing = [];
        if(pw.length < 8) missing.push('Min. <strong>8 karakter</strong>');
        if(!hasL) missing.push('Huruf <strong>kecil</strong> (a-z)');
        if(!hasU) missing.push('Huruf <strong>besar</strong> (A-Z)');
        if(!hasN) missing.push('<strong>Angka</strong> (0-9)');
        if(!hasS) missing.push('<strong>Simbol</strong> (!@#$% dll)');

        var detail = '';
        for(var i = 0; i < missing.length; i++){
            detail += '<div style="margin-bottom:3px;">• ' + missing[i] + '</div>';
        }

        popupError(
            'Password Tidak Valid',
            'Password baru tidak memenuhi syarat:<br><div style="text-align:left;margin-top:6px;line-height:1.8;">' + detail + '</div>'
        );
        return;
    }

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('user_id', document.getElementById('pwdUserId').value);
    fd.append('new_password', pw);

    var btn = document.querySelector('#modalPwd .btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    fetch(window.ADMIN_ROUTES.updatePassword, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
        body: fd
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            closeModal('modalPwd');
            
            // UPDATE CACHE LANGSUNG agar tidak perlu menunggu loadUsers selesai
            var uid = document.getElementById('pwdUserId').value;
            var newPw = document.getElementById('pwdNew').value;
            for(var i=0; i<allUsersCache.length; i++){
                if(allUsersCache[i].id == uid){
                    allUsersCache[i].plain_password = newPw;
                    break;
                }
            }
            plainPwdMap[uid] = newPw;
            
            loadUsers(); // Tetap jalankan untuk sync ulang data user
            popupSuccess('Password Diubah', 'Password user berhasil diperbarui.');
        } else {
            popupError('Gagal', d.message || 'Tidak dapat mengubah password.');
        }
    })
    .catch(function(){
        popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
    })
    .finally(function(){
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan Password Baru';
    });
}

/* ★ CHANGE ROLE — dropdown tidak terpotong layar */
var activeRoleMenu=null;
function openRoleMenu(e,uid,name,currentRole){
    e.stopPropagation();closeRoleMenu();
    var menu=document.createElement('div');menu.id='roleMenuDropdown';
    menu.style.cssText='position:fixed;z-index:99999;background:#131c2e;border:1px solid var(--bd-3);border-radius:10px;box-shadow:0 12px 34px rgba(0,0,0,.55);padding:6px;min-width:172px;visibility:hidden;';
    var roles=[{key:'admin',label:'Admin',color:'#2563eb'},{key:'juri',label:'Juri',color:'#16a34a'},{key:'grand_juri',label:'Grand Juri',color:'#7c3aed'},{key:'user',label:'User Biasa',color:'#94a3b8'}];
    for(var i=0;i<roles.length;i++){
        (function(r){
            var isActive=r.key===currentRole;
            var btn=document.createElement('button');
            btn.style.cssText='display:flex;align-items:center;gap:8px;width:100%;padding:9px 11px;border:none;border-radius:7px;font-family:inherit;font-size:12px;font-weight:'+(isActive?'800':'600')+';cursor:pointer;background:'+(isActive?'rgba(34,211,238,.14)':'transparent')+';color:#F8FAFC;white-space:nowrap;';
            btn.innerHTML='<span style="width:8px;height:8px;border-radius:50%;background:'+r.color+';flex-shrink:0;"></span>'+r.label+(isActive?' <i class="fas fa-check" style="margin-left:auto;font-size:10px;color:var(--primary);"></i>':'');
            btn.onmouseover=function(){if(!isActive)this.style.background='rgba(255,255,255,.06)';};
            btn.onmouseout=function(){if(!isActive)this.style.background='transparent';};
            btn.onclick=function(ev){
                ev.stopPropagation();closeRoleMenu();
                if(r.key===currentRole){popupInfo('Tidak Ada Perubahan','User sudah memiliki role <strong>'+roleLabels[r.key]+'</strong>.');return;}
                changeRole(uid,name,r.key);
            };
            menu.appendChild(btn);
        })(roles[i]);
    }
    document.body.appendChild(menu);

    /* ★ SMART POSITIONING — tidak terpotong layar */
    menu.style.visibility='hidden';
    menu.style.left='0px';menu.style.top='0px';
    var mRect=menu.getBoundingClientRect();
    var vw=window.innerWidth,vh=window.innerHeight;
    var left=e.clientX,top=e.clientY;
    if(left+mRect.width>vw-12)left=vw-mRect.width-12;
    if(top+mRect.height>vh-12)top=vh-mRect.height-12;
    if(left<12)left=12;if(top<12)top=12;
    menu.style.left=left+'px';menu.style.top=top+'px';
    menu.style.visibility='visible';

    activeRoleMenu=menu;
    setTimeout(function(){document.addEventListener('click',closeRoleMenu,{once:true});},10);
}
function closeRoleMenu(){var m=document.getElementById('roleMenuDropdown');if(m)m.remove();activeRoleMenu=null;}

function changeRole(uid,name,newRole){
    openConfirmPopup(
        'Ubah Role User',
        'Ubah role <strong>'+esc(name)+'</strong> menjadi <strong style="color:'+roleColors[newRole]+';">'+roleLabels[newRole]+'</strong>?',
        'Ya, Ubah Role',
        function(){
            var fd=new FormData();fd.append('_token',getCsrf());fd.append('user_id',uid);fd.append('new_role',newRole);
            fetch('/api/admin/change-role',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
            .then(function(r){return r.json();})
            .then(function(d){if(d.success){loadUsers();popupSuccess('Role Diubah','<strong>'+esc(name)+'</strong> → <strong>'+roleLabels[newRole]+'</strong>');}else popupError('Gagal',d.message||'Terjadi kesalahan.');})
            .catch(function(){popupError('Kesalahan Jaringan','Gagal menghubungi server.');});
        }
    );
}

function deleteIkan(ikanId, nama){
    openConfirmPopup(
        'Hapus Data Penilaian',
        'Yakin ingin menghapus data ikan milik <strong>'+esc(nama)+'</strong>?<br><span style="font-size:11px;color:var(--danger);">Semua nilai penilaian terkait juga akan dihapus permanen.</span>',
        'Ya, Hapus Permanen',
        function(){
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('ikan_id', ikanId);
            fetch('/api/admin/delete-ikan', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: fd
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    loadScoringData();
                    loadDashboard();
                    popupSuccess('Berhasil Dihapus', 'Data milik <strong>'+esc(nama)+'</strong> berhasil dihapus dari sistem.');
                } else {
                    popupError('Gagal Menghapus', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){
                popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
            });
        }
    );
}

/* ═══════════════════════════════════════════════
   USER PESERTA DETAIL (RIWAYAT IDENTITAS)
   ═══════════════════════════════════════════════ */
function openUserDetail(uid, name){
    document.getElementById('userDetailBody').innerHTML =
        '<div class="empty-state" style="padding:30px;"><i class="fas fa-spinner fa-spin"></i><p>Memuat data peserta...</p></div>';
    openModal('modalUserDetail');

    fetch('/api/admin/user-peserta-detail?user_id=' + uid, {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(d){ renderUserDetailModal(d); })
    .catch(function(){
        document.getElementById('userDetailBody').innerHTML =
            '<div class="empty-state" style="padding:30px;"><i class="fas fa-triangle-exclamation" style="color:var(--danger);font-size:24px;opacity:.6;"></i><p style="color:var(--danger);margin-top:8px;">Gagal memuat data.</p></div>';
    });
}

function renderUserDetailModal(d){
    var html = '';

    /* === HEADER: USER INFO === */
    html += '<div style="background:linear-gradient(135deg, rgba(168,85,247,.12), rgba(168,85,247,.04));border:1px solid rgba(168,85,247,.30);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">';
    html += '<div style="min-width:0;"><h4 style="font-size:14px;font-weight:800;color:#D8B4FE;margin-bottom:4px;">' + esc(d.user.name) + '</h4>';
    html += '<div style="font-size:11px;color:var(--text-mid);">' + esc(d.user.email) + '</div></div>';
    html += '<div style="background:rgba(168,85,247,.2);color:#D8B4FE;padding:5px 12px;border-radius:8px;font-size:10px;font-weight:800;border:1px solid rgba(168,85,247,.4);letter-spacing:.3px;">' + (roleLabels[d.user.role] || 'USER') + '</div>';
    html += '</div>';

    /* === JIKA BELUM ADA PROFIL PESERTA === */
    if(!d.has_peserta){
        html += '<div class="empty-state" style="padding:36px 20px;"><i class="fas fa-user-slash" style="font-size:28px;opacity:.4;"></i><p style="margin-top:10px;font-size:12px;color:var(--text-mid);">User ini belum memiliki profil peserta atau belum mendaftarkan ikan apapun.</p></div>';
        document.getElementById('userDetailBody').innerHTML = html;
        return;
    }

    /* === SECTION 1: PROFIL AKTIF === */
    var p = d.current_profile;
    var jenisLabel = p.jenis_keanggotaan === 'team' ? 'Team / Club' : 'Perorangan';
    var asalLabel  = p.jenis_keanggotaan === 'team' ? 'Nama Team / Club' : 'Kota Asal';

    html += '<div style="margin-bottom:18px;">';
    html += '<div style="font-size:10px;font-weight:800;color:var(--cyan-300);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;"><i class="fas fa-user-check" style="margin-right:5px;"></i>Profil Aktif Saat Ini</div>';
    html += '<div style="background:var(--glass-2);border:1px solid var(--bd-cyan);border-radius:12px;padding:14px 16px;">';
    html += '<div style="display:grid;grid-template-columns:150px 1fr;gap:10px 14px;font-size:12px;">';
    html += '<div style="color:var(--text-mid);font-weight:700;">Nama Peserta</div><div style="color:var(--text-hi);font-weight:700;">' + esc(p.nama_peserta || '-') + '</div>';
    html += '<div style="color:var(--text-mid);font-weight:700;">Jenis Keanggotaan</div><div style="color:var(--text-hi);font-weight:700;">' + esc(jenisLabel) + '</div>';
    html += '<div style="color:var(--text-mid);font-weight:700;">' + esc(asalLabel) + '</div><div style="color:var(--text-hi);font-weight:700;">' + esc(p.detail_anggota || '-') + '</div>';
    if(p.updated_at){
        html += '<div style="color:var(--text-mid);font-weight:700;">Terakhir Diubah</div><div style="color:var(--text-low);font-size:11px;">' + esc(p.updated_at) + '</div>';
    }
    html += '</div></div></div>';

    /* === SECTION 2: KOMBINASI UNIK === */
    if(d.unique_combinations && d.unique_combinations.length > 0){
        html += '<div style="margin-bottom:18px;">';
        html += '<div style="font-size:10px;font-weight:800;color:var(--gold-300);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;"><i class="fas fa-layer-group" style="margin-right:5px;"></i>Identitas yang Pernah Dipakai untuk Mendaftar Ikan (' + d.unique_combinations.length + ')</div>';

        if(d.unique_combinations.length === 1){
            html += '<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);border-radius:10px;padding:10px 14px;font-size:11px;color:#6EE7B7;line-height:1.6;"><i class="fas fa-check-circle" style="margin-right:4px;"></i>User ini konsisten menggunakan <b>1 identitas</b> untuk semua ikannya.</div>';
        } else {
            html += '<div style="background:rgba(245,158,11,.08);border:1px solid var(--bd-gold);border-radius:10px;padding:10px 14px;margin-bottom:10px;font-size:11px;color:var(--gold-300);line-height:1.6;"><i class="fas fa-triangle-exclamation" style="margin-right:4px;"></i>User ini menggunakan <b>' + d.unique_combinations.length + ' identitas berbeda</b> dalam riwayat pendaftaran ikan.</div>';

            html += '<div style="overflow-x:auto;border:1px solid var(--bd-2);border-radius:12px;">';
            html += '<table class="data-table" style="min-width:auto;">';
            html += '<thead><tr><th style="width:30px;">#</th><th>NAMA PESERTA</th><th>JENIS</th><th>ASAL / TEAM</th><th style="text-align:center;">JUMLAH IKAN</th></tr></thead><tbody>';
            for(var i=0; i<d.unique_combinations.length; i++){
                var c = d.unique_combinations[i];
                var jLabel = c.jenis_keanggotaan === 'team' ? 'Team' : (c.jenis_keanggotaan === 'perorangan' ? 'Perorangan' : c.jenis_keanggotaan);
                html += '<tr>';
                html += '<td style="font-weight:700;color:var(--text-low);font-size:11px;">' + (i+1) + '</td>';
                html += '<td style="font-weight:700;">' + esc(c.nama_peserta) + '</td>';
                html += '<td style="font-size:11px;color:var(--muted);">' + esc(jLabel) + '</td>';
                html += '<td style="font-size:11px;">' + esc(c.detail_anggota) + '</td>';
                html += '<td style="text-align:center;font-weight:800;color:var(--cyan-300);font-size:13px;">' + c.count + '<span style="font-size:10px;color:var(--text-low);font-weight:600;"> ikan</span></td>';
                html += '</tr>';
            }
            html += '</tbody></table></div>';
        }
        html += '</div>';
    }

    /* === SECTION 3: DETAIL PER IKAN === */
    if(d.ikans && d.ikans.length > 0){
        html += '<div>';
        html += '<div style="font-size:10px;font-weight:800;color:#D8B4FE;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;"><i class="fas fa-fish" style="margin-right:5px;"></i>Detail Setiap Ikan yang Didaftarkan (' + d.total_ikan + ')</div>';
        html += '<div style="overflow-x:auto;border:1px solid var(--bd-2);border-radius:12px;max-height:300px;overflow-y:auto;">';
        html += '<table class="data-table" style="min-width:780px;">';
        html += '<thead><tr><th style="width:30px;">#</th><th>NAMA (SAAT DAFTAR)</th><th>JENIS</th><th>ASAL/TEAM</th><th>KATEGORI</th><th>KELAS</th><th>TANK</th><th>TANGGAL DAFTAR</th></tr></thead><tbody>';
        for(var i=0; i<d.ikans.length; i++){
            var ik = d.ikans[i];
            var jLabel2 = ik.jenis_keanggotaan === 'team' ? 'Team' : (ik.jenis_keanggotaan === 'perorangan' ? 'Perorangan' : ik.jenis_keanggotaan);
            html += '<tr>';
            html += '<td style="font-weight:700;color:var(--text-low);font-size:11px;">' + (i+1) + '</td>';
            html += '<td style="font-weight:700;font-size:11px;">' + esc(ik.nama_peserta) + '</td>';
            html += '<td style="font-size:11px;color:var(--muted);">' + esc(jLabel2) + '</td>';
            html += '<td style="font-size:11px;">' + esc(ik.detail_anggota) + '</td>';
            html += '<td style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--muted);">' + esc(ik.kategori) + '</td>';
            html += '<td style="font-size:11px;">' + esc(ik.kelas) + '</td>';
            html += '<td style="font-weight:700;color:var(--primary);font-size:11px;">' + (ik.nomor_tank ? 'Tank '+ik.nomor_tank : '<span style="color:var(--text-low);font-weight:600;">—</span>') + '</td>';
            html += '<td style="font-size:10px;color:var(--text-low);white-space:nowrap;">' + esc(ik.created_at) + '</td>';
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        html += '</div>';
    } else {
        html += '<div class="empty-state" style="padding:24px;"><i class="fas fa-inbox" style="font-size:24px;opacity:.4;"></i><p style="margin-top:8px;font-size:12px;color:var(--text-mid);">Profil peserta sudah ada, tetapi belum ada ikan yang didaftarkan.</p></div>';
    }

    document.getElementById('userDetailBody').innerHTML = html;
}

/* ═══════════════════════════════════════════════
   AUTO-INJECT TOMBOL "HAPUS TERPILIH" KE FILTER BAR
   (dijalankan saat halaman siap, idempoten)
   ═══════════════════════════════════════════════ */
function ensureBulkDeleteButton(){
    if(document.getElementById('btnBulkDelete')) return; // sudah ada, skip

    var filterStatus = document.getElementById('filterStatus');
    if(!filterStatus) return;

    var filterBar = filterStatus.closest('.filter-bar');
    if(!filterBar) return;

    var btn = document.createElement('button');
    btn.id = 'btnBulkDelete';
    btn.type = 'button';
    btn.onclick = bulkDeleteIkan;
    btn.style.cssText = ''
        + 'display:none;'
        + 'padding:10px 16px;'
        + 'border-radius:11px;'
        + 'border:1px solid rgba(239,68,68,.45);'
        + 'background:rgba(239,68,68,.15);'
        + 'color:#FCA5A5;'
        + 'font-family:inherit;'
        + 'font-size:11.5px;'
        + 'font-weight:800;'
        + 'cursor:pointer;'
        + 'letter-spacing:.02em;'
        + 'transition:all .2s;'
        + 'align-items:center;'
        + 'gap:7px;'
        + 'white-space:nowrap;';
    btn.innerHTML = '<i class="fas fa-trash-can"></i> Hapus Terpilih <span id="bulkDeleteCount" style="font-weight:900;color:#fff;background:rgba(239,68,68,.5);padding:1px 7px;border-radius:5px;">0</span>';

    btn.addEventListener('mouseenter', function(){
        this.style.background = 'var(--danger)';
        this.style.color = '#fff';
        this.style.transform = 'translateY(-1px)';
    });
    btn.addEventListener('mouseleave', function(){
        this.style.background = 'rgba(239,68,68,.15)';
        this.style.color = '#FCA5A5';
        this.style.transform = 'translateY(0)';
    });

    filterBar.appendChild(btn);
}

/* Jalankan saat DOM siap dan saat halaman penilaian dibuka */
if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', ensureBulkDeleteButton);
} else {
    ensureBulkDeleteButton();
}

/* ═══════════════════════════════════════════════
   BULK DELETE — CHECKBOX & MASSAL HAPUS
   ═══════════════════════════════════════════════ */
function toggleAllRows(masterCheckbox){
    var checks = document.querySelectorAll('.row-check');
    for(var i=0; i<checks.length; i++){
        checks[i].checked = masterCheckbox.checked;
    }
    updateBulkDeleteButton();
}

function onRowCheckChange(){
    var checks = document.querySelectorAll('.row-check');
    var checkedCount = 0;
    for(var i=0; i<checks.length; i++){
        if(checks[i].checked) checkedCount++;
    }
    var master = document.getElementById('checkAllRows');
    if(master){
        master.checked = (checkedCount > 0 && checkedCount === checks.length);
        master.indeterminate = (checkedCount > 0 && checkedCount < checks.length);
    }
    updateBulkDeleteButton();
}

function updateBulkDeleteButton(){
    var checked = document.querySelectorAll('.row-check:checked');
    var btn = document.getElementById('btnBulkDelete');
    var counter = document.getElementById('bulkDeleteCount');
    if(!btn) return;
    if(checked.length > 0){
        btn.style.display = 'inline-flex';
        if(counter) counter.textContent = checked.length;
    } else {
        btn.style.display = 'none';
    }
}

function bulkDeleteIkan(){
    var checks = document.querySelectorAll('.row-check:checked');
    if(!checks.length){
        popupInfo('Belum Ada Pilihan','Pilih minimal satu data terlebih dahulu.');
        return;
    }

    var ids = [], names = [];
    for(var i=0; i<checks.length; i++){
        ids.push(checks[i].dataset.id);
        names.push(checks[i].dataset.name);
    }

    var listHtml = '<div style="text-align:left;max-height:170px;overflow-y:auto;font-size:11px;line-height:1.8;background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.18);border-radius:8px;padding:10px 12px;margin-top:10px;">';
    var shown = Math.min(names.length, 10);
    for(var j=0; j<shown; j++){
        listHtml += '• ' + esc(names[j]) + '<br>';
    }
    if(names.length > 10){
        listHtml += '<i style="color:var(--text-mid);">... dan ' + (names.length - 10) + ' lainnya</i>';
    }
    listHtml += '</div>';

    openConfirmPopup(
        'Hapus Data Penilaian Massal',
        'Yakin ingin menghapus <strong>' + ids.length + ' data ikan</strong> berikut?' + listHtml +
        '<div style="font-size:11px;color:var(--danger);margin-top:8px;"><i class="fas fa-triangle-exclamation"></i> Semua nilai penilaian terkait juga akan dihapus permanen.</div>',
        'Ya, Hapus Semua',
        function(){ executeBulkDeleteIkan(ids); }
    );
}

function executeBulkDeleteIkan(ids){
    showLoader('Menghapus ' + ids.length + ' data...');

    var fd = new FormData();
    fd.append('_token', getCsrf());
    for(var i=0; i<ids.length; i++){
        fd.append('ikan_ids[]', ids[i]);
    }

    fetch('/api/admin/bulk-delete-ikan', {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:fd
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        hideLoader();
        if(d.success){
            loadScoringData();
            loadDashboard();
            popupSuccess('Berhasil Dihapus', d.message || (ids.length + ' data berhasil dihapus.'));
        } else {
            popupError('Gagal Menghapus', d.message || 'Terjadi kesalahan.');
        }
    })
    .catch(function(){
        hideLoader();
        popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
    });
}

/* ═══════════════════════════════════════════════
   EXPORT CSV
   ═══════════════════════════════════════════════ */
var statTypeIcons={total_ikan:'fa-fish',total_peserta:'fa-users',sudah_dinilai:'fa-check-double',grand_edit:'fa-crown',belum_dinilai:'fa-clock',juri_aktif:'fa-user-pen'};
var statTypeColors={total_ikan:'var(--primary)',total_peserta:'#14b8a6',sudah_dinilai:'var(--success)',grand_edit:'var(--purple)',belum_dinilai:'var(--danger)',juri_aktif:'var(--warning)'};

function openStatPopup(type, title){
    var iconEl=document.getElementById('statDetailIcon');
    var iconI=document.getElementById('statDetailIconI');
    if(iconEl&&iconI){iconEl.style.background=statTypeColors[type]||'var(--primary)';}
    if(iconI){iconI.className='fas '+(statTypeIcons[type]||'fa-chart-bar')+' style="color:#fff;font-size:16px;"';}
    document.getElementById('statDetailTitle').textContent=title;
    document.getElementById('statDetailCount').textContent='Memuat...';
    document.getElementById('statDetailBody').innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>';
    showPopup('popupStatDetail');
    fetch('/api/admin/stat-detail?type='+type,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.error){document.getElementById('statDetailBody').innerHTML='<div class="sd-empty"><i class="fas fa-triangle-exclamation"></i><p>Data tidak valid.</p></div>';return;}
        document.getElementById('statDetailCount').innerHTML='Menampilkan <b style="color:'+(statTypeColors[type]||'var(--primary)')+';">'+d.rows.length+'</b> data';
        var numCols={};
        d.columns.forEach(function(c,i){
            if(['JURI','TOTAL NILAI','JUMLAH IKAN','PESERTA DINILAI','SUDAH TANK','BELUM TANK'].indexOf(c)!==-1)numCols[i]=true;
        });
        var valColor={};
        if(type==='sudah_dinilai')valColor={5:'success'};
        else if(type==='grand_edit')valColor={5:'purple',6:'primary'};
        else if(type==='belum_dinilai')valColor={};
        else if(type==='juri_aktif')valColor={2:'purple',3:'amber'};

        var h='<div class="sd-table-wrap"><table class="sd-table"><thead><tr>';
        d.columns.forEach(function(c,i){
            var cls='';if(i===0)cls=' num';if(numCols[i])cls=' right';
            h+='<th class="'+cls+'">'+c+'</th>';
        });
        h+='</tr></thead><tbody>';
        if(!d.rows.length){
            h+='<tr><td colspan="'+d.columns.length+'"><div class="sd-empty"><i class="fas fa-inbox"></i><p>Tidak ada data untuk ditampilkan.</p></div></td></tr>';
        } else {
            d.rows.forEach(function(row){
                h+='<tr>';
                row.forEach(function(cell,ci){
                    if(ci===0){
                        h+='<td class="td-num">'+esc(String(cell))+'</td>';
                    } else if(type==='juri_aktif'&&ci===2){
                        var roleColors={Juri:'blue',GrandJuri:'purple',Admin:'blue'};
                        h+='<td><span class="sd-badge '+(roleColors[cell]||'blue')+'">'+esc(String(cell))+'</span></td>';
                    } else if(type === 'total_peserta' && ci === 5){
                        h+='<td class="td-val" style="color:#6EE7B7;">'+esc(String(cell))+'</td>';
                    } else if(type === 'total_peserta' && ci === 6){
                        var belumNum = parseInt(cell || 0, 10);
                        h+='<td class="td-val" style="color:'+(belumNum > 0 ? 'var(--gold-300)' : '#6EE7B7')+';">'+esc(String(cell))+'</td>';
                    } else if(type === 'total_peserta' && ci === 7){
                        var isBelum = String(cell).toLowerCase().indexOf('belum') !== -1;
                        h+='<td><span style="display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:900;color:'+(isBelum ? 'var(--gold-300)' : '#6EE7B7')+';">' +
                            '<i class="fas '+(isBelum ? 'fa-circle-exclamation' : 'fa-circle-check')+'"></i> ' + esc(String(cell)) +
                        '</span></td>';
                    } else if(numCols[ci]){
                        var vc=valColor[ci]||'';
                        h+='<td class="td-val '+(vc?' '+vc:'')+'">'+esc(String(cell))+'</td>';
                    } else {
                        h+='<td class="td-name">'+esc(String(cell))+'</td>';
                    }
                });
                h+='</tr>';
            });
        }
        h+='</tbody></table></div>';
        document.getElementById('statDetailBody').innerHTML=h;
    })
    .catch(function(){document.getElementById('statDetailBody').innerHTML='<div class="sd-empty"><i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i><p style="color:var(--danger);">Gagal memuat data.</p></div>';});
}

function doExport(sheets){
    var dd=document.getElementById('exportDD');
    if(dd) dd.classList.remove('show');
    _exportToast('Menyiapkan export... jangan tutup halaman.', true);
    fetch('/api/admin/export-async?sheets='+encodeURIComponent(sheets),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        if(!d.token){ _exportToast('Gagal memulai export.', false); _exportToastHide(5000); return; }
        pollExportToast(d.token, 0);
    })
    .catch(function(){ _exportToast('Kesalahan jaringan saat memulai export.', false); _exportToastHide(5000); });
}
document.addEventListener('click',function(e){
    if(!e.target.closest('.export-wrap')){
        document.getElementById('exportDD').classList.remove('show');
    }
});

// 2. Load Dropdown Ikan yang belum dapat tank
function loadPesertaOld(){
    var sel=document.getElementById('pesertaSelectOld');
    var counter=document.getElementById('tankCounter');
    sel.innerHTML='<option value="" disabled selected>Memuat...</option>';
    if(counter) counter.textContent='Memuat...';

    fetch(window.ADMIN_ROUTES.pesertaBelumTank,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        sel.innerHTML='';
        if(!data.length){
            sel.innerHTML='<option disabled>Semua ikan sudah mendapat nomor tank</option>';
            if(counter) counter.innerHTML='<i class="fas fa-check-circle" style="color:#22c55e;"></i> Semua ikan sudah diundi';
            sel.disabled=true;
            document.getElementById('btnAcakOld').disabled=true;
            return;
        }

        sel.disabled=false;
        document.getElementById('btnAcakOld').disabled=false;
        if(counter) counter.innerHTML=data.length+' ikan belum diundi';

        sel.innerHTML='<option value="" disabled selected>Pilih ikan yang belum diundi ('+data.length+')</option>';
        for(var i=0;i<data.length;i++){
            var o=document.createElement('option');
            o.value=data[i].id;
            o.textContent=data[i].nama_peserta+' — '+data[i].kategori+' ('+data[i].kelas+')';
            sel.appendChild(o);
        }

        /* Reset display ke -- */
        document.getElementById('numberDisplayOld').textContent='--';
        document.getElementById('numberDisplayOld').style.color='#fff';
    })
    .catch(function(){
        sel.innerHTML='<option disabled>Gagal memuat data</option>';
        if(counter) counter.textContent='Error';
    });
}

document.getElementById('btnAcakOld').addEventListener('click',function(){
    var sel=document.getElementById('pesertaSelectOld');
    if(!sel.value)return;

    var display=document.getElementById('numberDisplayOld');
    var btn=this;
    display.style.color='#60a5fa';
    btn.disabled=true;
    display.textContent='...';

    var fd=new FormData();
    fd.append('_token',getCsrf());
    fd.append('ikan_id',sel.value);

    // Panggil API dulu, baru animasi berakhir tepat di nomor hasil
    fetch(window.ADMIN_ROUTES.acakTankAdmin,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        if(!d.success) throw new Error(d.message);

        var finalNumber=d.nomor_tank;
        var maxForAnim=currentTankMax||1000;
        var totalSteps=18,step=0;

        var iv=setInterval(function(){
            step++;
            if(step<totalSteps){
                if(step>totalSteps-5){
                    var spread=Math.floor((totalSteps-step)*3)+5;
                    var minA=Math.max(1,finalNumber-spread),maxA=finalNumber+spread;
                    display.textContent=Math.floor(Math.random()*(maxA-minA+1))+minA;
                } else {
                    display.textContent=Math.floor(Math.random()*maxForAnim)+1;
                }
            } else {
                display.textContent=finalNumber;
                display.style.color='#22c55e';
                clearInterval(iv);
                setTimeout(function(){
                    display.textContent='--';
                    display.style.color='#fff';
                    btn.disabled=false;
                    loadPesertaOld();
                    loadDashboard();
                },2000);
            }
        },60);
    })
    .catch(function(e){
        display.textContent='--';
        display.style.color='#fff';
        btn.disabled=false;
        loadPesertaOld(); // ★ Refresh dropdown agar ikan yang sudah diundi hilang
        popupError('Undian Gagal',esc(e.message));
    });
});

/* ═══════════════════════════════════════════════
   RESET NOMOR TANK (JS)
   ═══════════════════════════════════════════════ */
function openResetTankModal() {
    document.getElementById('resetReason').value = '';
    openModal('modalResetTank');
}

function submitResetTank() {
    var reason = document.getElementById('resetReason').value.trim();
    if (!reason) {
        popupError('Alasan Wajib Diisi', 'Anda harus mencantumkan alasan mengapa nomor tank direset.');
        return;
    }
    
    openConfirmPopup(
        'Konfirmasi Reset',
        'Anda yakin ingin menghapus <b>SEMUA</b> nomor tank?<br><span style="font-size:11px;color:var(--danger);">Tindakan ini tidak dapat dibatalkan.</span>',
        'Ya, Reset Sekarang',
        function() {
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('reason', reason);
            
            var btn = document.getElementById('btnSubmitReset');
            var btnOldHtml = btn ? btn.innerHTML : '';

            if(btn){
                btn.disabled = true;
                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mereset nomor tank...';
            }

            showLoader('Mereset semua nomor tank. Mohon tunggu...');

            fetch('/api/admin/reset-tank', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            })
            .then(function(r){
                if(!r.ok){
                    return r.json().then(function(d){ throw d; });
                }
                return r.json();
            })
            .then(function(d){
                if (d.success) {
                    closeModal('modalResetTank');

                    if(document.getElementById('numberDisplayOld')){
                        document.getElementById('numberDisplayOld').textContent = '--';
                    }

                    if(typeof loadPesertaOld === 'function') loadPesertaOld();
                    if(typeof loadDashboard === 'function') loadDashboard();
                    if(typeof loadScoringData === 'function') loadScoringData();

                    popupSuccess(
                        'Berhasil Direset',
                        (d.message || 'Semua nomor tank berhasil direset.') +
                        '<br><span style="font-size:11px;color:var(--text-mid);">Sync spreadsheet berjalan di background, jadi halaman tidak perlu menunggu lama.</span>'
                    );
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(e){
                popupError('Error', e && e.message ? e.message : 'Gagal menghubungi server.');
            })
            .finally(function(){
                hideLoader();

                if(btn){
                    btn.disabled = false;
                    btn.style.opacity = '';
                    btn.style.cursor = '';
                    btn.innerHTML = btnOldHtml || '<i class="fas fa-rotate-left"></i> Ya, Reset Semua';
                }
            });
        }
    );
}

/* ═══════════════════════════════════════════════
   RESET DATA PESERTA (JS)
   ═══════════════════════════════════════════════ */
function openResetPesertaModal(){
    var mode = document.getElementById('resetPesertaMode');
    var txt = document.getElementById('resetPesertaConfirmText');
    var chk = document.getElementById('resetPesertaCheck');

    if(mode) mode.value = '';
    if(txt) txt.value = '';
    if(chk) chk.checked = false;

    openModal('modalResetPeserta');
}

function getResetPesertaModeLabel(mode){
    var labels = {
        scores_only: 'Hapus nilai user',
        users_only: 'Hapus user dengan role user',
        all: 'Hapus nilai beserta usernya'
    };
    return labels[mode] || '-';
}

function submitResetPeserta(){
    var mode = document.getElementById('resetPesertaMode').value;
    var confirmText = document.getElementById('resetPesertaConfirmText').value.trim();
    var checked = document.getElementById('resetPesertaCheck').checked;

    if(!mode){
        popupError('Aksi Belum Dipilih', 'Pilih salah satu aksi reset terlebih dahulu.');
        return;
    }

    if(confirmText !== 'RESET PESERTA'){
        popupError('Verifikasi Salah', 'Ketik <b>RESET PESERTA</b> dengan huruf besar semua.');
        return;
    }

    if(!checked){
        popupError('Persetujuan Wajib', 'Centang pernyataan bahwa Anda memahami risiko reset data.');
        return;
    }

    var label = getResetPesertaModeLabel(mode);

    openConfirmPopup(
        'Verifikasi 2: ' + label,
        'Anda benar-benar yakin ingin menjalankan aksi <b>' + esc(label) + '</b>?<br>' +
        '<span style="font-size:11px;color:var(--danger);font-weight:800;">Tindakan ini tidak dapat dibatalkan dari aplikasi.</span>',
        'Ya, Jalankan Reset',
        function(){
            executeResetPeserta(mode, confirmText);
        }
    );
}

function executeResetPeserta(mode, confirmText){
    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('mode', mode);
    fd.append('confirm_text', confirmText);
    fd.append('confirm_check', '1');

    var btn = document.getElementById('btnSubmitResetPeserta');
    var oldHtml = btn ? btn.innerHTML : '';

    if(btn){
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'wait';
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    }

    showLoader('Mereset data peserta. Mohon tunggu...');

    fetch('/api/admin/reset-participants', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(r){
        if(!r.ok){
            return r.json().then(function(d){ throw d; });
        }
        return r.json();
    })
    .then(function(d){
        if(d.success){
            closeModal('modalResetPeserta');

            if(typeof loadDashboard === 'function') loadDashboard();
            if(typeof loadScoringData === 'function') loadScoringData();
            if(typeof loadUsers === 'function') loadUsers();
            if(typeof loadPesertaOld === 'function') loadPesertaOld();

            var detail = '';
            if(d.data){
                detail =
                    '<br><span style="font-size:11px;color:var(--text-mid);line-height:1.5;display:block;margin-top:6px;">' +
                    'User target: <b>' + (d.data.users_target || 0) + '</b>, ' +
                    'Peserta target: <b>' + (d.data.peserta_target || 0) + '</b>, ' +
                    'Ikan target: <b>' + (d.data.ikan_target || 0) + '</b>, ' +
                    'Nilai terhapus: <b>' + (d.data.deleted_scoring || 0) + '</b>.' +
                    '</span>';
            }

            popupSuccess('Reset Berhasil', (d.message || 'Reset data peserta berhasil.') + detail);
        } else {
            popupError('Reset Gagal', d.message || 'Terjadi kesalahan.');
        }
    })
    .catch(function(e){
        popupError('Reset Gagal', e && e.message ? e.message : 'Gagal menghubungi server.');
    })
    .finally(function(){
        hideLoader();

        if(btn){
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.cursor = '';
            btn.innerHTML = oldHtml || '<i class="fas fa-triangle-exclamation"></i> Lanjutkan';
        }
    });
}

// ── SEARCHABLE DROPDOWN PESERTA (modalOld) ──
var admRegUserCache = [];
var admRegSelected = false;

var admRegSearchEl = document.getElementById('admRegSearch');
var admRegListEl = document.getElementById('admRegList');
var admRegClearEl = document.getElementById('admRegClear');
var admRegHiddenName = document.getElementById('admRegNama');

if(admRegSearchEl){
    admRegSearchEl.addEventListener('focus', function(){
        if(admRegUserCache.length===0) loadAdmRegUsers();
        admRegListEl.classList.add('show');
    });
    admRegSearchEl.addEventListener('input', function(){
        var q = this.value.toLowerCase().trim();
        admRegClearEl.style.display = q ? 'block' : 'none';
        if(!q){ renderAdmRegList(admRegUserCache); return; }
        var filtered = [];
        for(var i=0;i<admRegUserCache.length;i++){
            var u=admRegUserCache[i];
            if(u.name.toLowerCase().indexOf(q)!==-1 || u.email.toLowerCase().indexOf(q)!==-1) filtered.push(u);
        }
        renderAdmRegList(filtered);
    });
    admRegClearEl.addEventListener('click', function(){
        admRegSearchEl.value='';
        admRegClearEl.style.display='none';
        admRegHiddenName.value='';
        admRegUserIdEl.value='';
        admRegSelected=false;
        admRegSearchEl.classList.remove('input-success');
        document.getElementById('admPerorangan').checked = true;
        updateAdmToggleUI();
        document.getElementById('admInputDetail').value = '';
        renderAdmRegList(admRegUserCache);
        admRegSearchEl.focus();
    });
    document.addEventListener('click', function(e){
        if(!e.target.closest('#admRegDropdown')) admRegListEl.classList.remove('show');
    });
}

function loadAdmRegUsers(){
    fetch(window.ADMIN_ROUTES.listUsers,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        admRegUserCache = data.filter(function(u){ return u.role==='user'; });
        renderAdmRegList(admRegUserCache);
    })
    .catch(function(){});
}

// Tambahkan variabel di atas (dekat var admRegUserCache)
var admRegUserIdEl = document.getElementById('admRegUserId');

// Toggle jenis keanggotaan admin
var admRadioP = document.getElementById('admPerorangan');
var admRadioT = document.getElementById('admTeam');
function updateAdmToggleUI() {
    if (admRadioT.checked) {
        document.getElementById('admLabelDetail').textContent = 'Nama Team / Club';
        document.getElementById('admInputDetail').placeholder = 'Contoh: Louhan Fanatic Jakarta';
        document.getElementById('admIconDetail').classList.replace('fa-city', 'fa-shield-halved');
    } else {
        document.getElementById('admLabelDetail').textContent = 'Kota Asal';
        document.getElementById('admInputDetail').placeholder = 'Contoh: Jakarta';
        document.getElementById('admIconDetail').classList.replace('fa-shield-halved', 'fa-city');
    }
}
if(admRadioP) admRadioP.addEventListener('change', updateAdmToggleUI);
if(admRadioT) admRadioT.addEventListener('change', updateAdmToggleUI);

function loadPesertaDetail(userId) {
    fetch('/api/admin/get-peserta-by-user?user_id=' + userId, { headers: {'Accept': 'application/json'} })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.found) {
            if (d.jenis_keanggotaan === 'team') { document.getElementById('admTeam').checked = true; }
            else { document.getElementById('admPerorangan').checked = true; }
            updateAdmToggleUI();
            document.getElementById('admInputDetail').value = d.detail_anggota || '';
        } else {
            document.getElementById('admPerorangan').checked = true;
            updateAdmToggleUI();
            document.getElementById('admInputDetail').value = '';
        }
    })
    .catch(function() {});
}

function renderAdmRegList(list){
    if(!admRegListEl) return;
    admRegListEl.innerHTML='';
    if(!list.length){
        admRegListEl.innerHTML='<div class="dropdown-empty"><i class="fas fa-user-slash" style="font-size:16px;display:block;margin-bottom:4px;opacity:.4;"></i>Tidak ditemukan</div>';
        return;
    }
    for(var i=0;i<list.length;i++){
        (function(u){
            var div=document.createElement('div');
            div.className='dropdown-item';
            div.innerHTML=
                '<div class="di-avatar" style="background:#94a3b8;">'+esc(u.name.charAt(0).toUpperCase())+'</div>'+
                '<div class="di-info"><div class="di-name">'+esc(u.name)+'</div><div class="di-email">'+esc(u.email)+'</div></div>'+
                '<span class="di-role role-user">USER</span>';
            div.addEventListener('click',function(){
                admRegSearchEl.value=u.name;
                admRegHiddenName.value=u.name;
                admRegUserIdEl.value=u.id;
                admRegSelected=true;
                admRegSearchEl.classList.add('input-success');
                admRegListEl.classList.remove('show');
                loadPesertaDetail(u.id);
            });
            admRegListEl.appendChild(div);
        })(list[i]);
    }
}

/* ═══ SIMPAN DATA PESERTA SAJA (TANPA IKAN BARU) ═══ */
function submitSavePeserta(){
    if(!admRegSelected){
        popupError('Peserta Belum Dipilih','Silakan pilih nama peserta dari dropdown terlebih dahulu.');
        return;
    }

    var jenisK = document.querySelector('#admRegToggleGroup input[name="jenis_keanggotaan"]:checked');
    var detailVal = document.getElementById('admInputDetail').value.trim();

    if(!jenisK){
        popupError('Data Tidak Lengkap','Pilih jenis keanggotaan (Perorangan / Team).');
        return;
    }
    if(!detailVal){
        var labelDetail = document.getElementById('admLabelDetail').textContent;
        popupError('Data Tidak Lengkap','Field <b>' + esc(labelDetail) + '</b> wajib diisi.');
        document.getElementById('admInputDetail').focus();
        return;
    }

    var btn = document.getElementById('btnSavePesertaOnly');
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> MENYIMPAN...';
    btn.style.opacity = '.6';
    btn.style.cursor = 'wait';

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('user_id', admRegUserIdEl.value);
    fd.append('nama_peserta', admRegHiddenName.value);
    fd.append('jenis_keanggotaan', jenisK.value);
    fd.append('detail_anggota', detailVal);

    fetch('/api/admin/update-peserta-data', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
        body: fd
    })
    .then(function(r){
        if(!r.ok) return r.json().then(function(d){ throw d; });
        return r.json();
    })
    .then(function(d){
        if(d.success){
            popupSuccess('Data Peserta Tersimpan', d.message);
            loadScoringData();
            loadDashboard();
        } else {
            popupError('Gagal Menyimpan', d.message || 'Terjadi kesalahan.');
        }
    })
    .catch(function(e){
        if(e.errors){
            var msg = '';
            var keys = Object.keys(e.errors);
            for(var i = 0; i < keys.length; i++) msg += '<div style="margin-bottom:4px;">• ' + esc(e.errors[keys[i]][0]) + '</div>';
            popupError('Validasi Gagal', msg);
        } else {
            popupError('Gagal', e.message || 'Terjadi kesalahan saat menyimpan.');
        }
    })
    .finally(function(){
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    });
}

// ── SUBMIT REGISTRASI PESERTA & IKAN ──
var _regForm=document.getElementById('regPesertaIkanForm');
if(_regForm) _regForm.addEventListener('submit',function(e){
    e.preventDefault();
    var form=this;
    var btn=form.querySelector('.btn-primary');

    if(!admRegSelected){
        popupError('Peserta Belum Dipilih','Silakan pilih nama peserta dari dropdown terlebihkan dahulu.');
        return;
    }

    btn.disabled=true;
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> MEMPROSES...';

    var fd=new FormData(form);
    fd.append('_token',getCsrf());

    fetch('/api/admin/register-peserta-ikan',{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:fd
    })
    .then(function(r){if(!r.ok) return r.json().then(function(d){throw d;}); return r.json();})
    .then(function(d){
        if(d.success){
            form.reset();
            admRegSearchEl.value='';
            if(regKelasWrap) regKelasWrap.style.display='';
            admRegSearchEl.classList.remove('input-success');
            admRegClearEl.style.display='none';
            admRegNama.value='';
            admRegUserIdEl.value='';
            admRegSelected=false;
            loadPesertaOld();
            loadDashboard();
            popupSuccess('Berhasil Didaftarkan!','Peserta baru beserta ikan berhasil ditambahkan ke sistem.');
        } else {
            popupError('Gagal Mendaftar',d.message||'Terjadi kesalahan saat mendaftarkan peserta.');
        }
    })
    .catch(function(e){
        if(e.errors){
            var msg='';var keys=Object.keys(e.errors);
            for(var i=0;i<keys.length;i++) msg+='<div style="margin-bottom:4px;">• '+esc(e.errors[keys[i]][0])+'</div>';
            popupError('Validasi Gagal',msg);
        } else {
            popupError('Gagal',e.message||'Terjadi kesalahan.');
        }
    })
    .finally(function(){
        btn.disabled=false;
        btn.innerHTML='<i class="fas fa-fish" style="margin-right:6px;"></i> DAFTARKAN PESERTA & IKAN';
    });
});

function toggleGlobalRangeEdit(show){
    var viewEl=document.getElementById('globalRangeViewMode'),editEl=document.getElementById('globalRangeEditMode');
    if(viewEl)viewEl.style.display=show?'none':'flex';if(editEl)editEl.style.display=show?'block':'none';
}

function saveGlobalTankRange(){
    var min=parseInt(document.getElementById('inputGlobalRangeMin').value),max=parseInt(document.getElementById('inputGlobalRangeMax').value);
    if(isNaN(min)||isNaN(max)||min<1||max<1){popupError('Tidak Valid','Nomor harus lebih dari 0.');return;}
    if(max<=min){popupError('Tidak Valid','Nomor akhir harus lebih besar.');return;}
    
    var btn=event.target.closest('button');
    var originalHtml=btn.innerHTML;
    btn.disabled=true;
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Memproses...';
    
    var fd=new FormData();fd.append('_token',getCsrf());fd.append('min',min);fd.append('max',max);
    fetch('/api/admin/tank-range-global',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.success){currentTankMax=max;document.getElementById('globalRangeDisplayText').textContent=min+' – '+max;document.getElementById('globalRangeDesc').textContent='Rentang undian yang berlaku saat ini: '+min+' s/d '+max+' — ikan tanpa sub-rentang khusus akan diundi dari rentang ini.';toggleGlobalRangeEdit(false);loadDashboard();popupSuccess('Berhasil','Rentang global: <b>'+min+' – '+max+'</b>');}
        else popupError('Gagal',d.message||'Error');
    }).catch(function(){popupError('Error','Gagal menyimpan.');})
    .finally(function(){btn.disabled=false;btn.innerHTML=originalHtml;});
}

/* ═══════════════════════════════════════════════
   SEARCH USER
   ═══════════════════════════════════════════════ */
var allUsersCache=[];
var plainPwdMap={};

var searchUserT;
document.getElementById('searchUser').addEventListener('input',function(){
    clearTimeout(searchUserT);
    var q=this.value;
    searchUserT=setTimeout(function(){filterUsers(q);},200);
});

function filterUsers(q){
    q=q.toLowerCase().trim();
    var c=document.getElementById('userList');c.innerHTML='';
    var filtered=[];

    plainPwdMap={};
    for(var i=0;i<allUsersCache.length;i++){
        plainPwdMap[allUsersCache[i].id]=allUsersCache[i].plain_password||'';
    }

    if(!q){filtered=allUsersCache;}
    else{
        for(var i=0;i<allUsersCache.length;i++){
            var u=allUsersCache[i];
            if(u.name.toLowerCase().indexOf(q)!==-1||u.email.toLowerCase().indexOf(q)!==-1||(roleLabels[u.role]||'').toLowerCase().indexOf(q)!==-1){
                filtered.push(u);
            }
        }
    }
    document.getElementById('userCount').textContent=filtered.length+' user';
    if(!filtered.length){c.innerHTML='<div class="empty-state"><i class="fas fa-user-slash"></i><p>Tidak ada user ditemukan.</p></div>';return;}
    renderUserList(filtered);
}

function renderUserList(data){
    var c=document.getElementById('userList');c.innerHTML='';
    var myId=window.MY_AUTH_ID;
    for(var i=0;i<data.length;i++){
        var u=data[i],role=u.role||'user',isMe=myId===u.id,isOtherAdmin=(role==='admin'&&!isMe);
        var div=document.createElement('div');div.className='user-card';
        var safeName=esc(u.name).replace(/'/g,"\\");

        var topHtml=
            '<div class="user-card-top">'+
                '<div class="user-avatar" style="background:'+roleColors[role]+';">'+esc(u.name.charAt(0).toUpperCase())+'</div>'+
                '<div class="user-card-body"><h4>'+esc(u.name)+'</h4><span>'+esc(u.email)+'</span></div>'+
                '<span class="role-badge '+roleBadgeCls[role]+'" style="flex-shrink:0;">'+roleLabels[role]+'</span>'+
            '</div>';

        var actions='';
        /* ★ Tombol Detail Peserta — HANYA untuk role 'user' (peserta), bukan admin/juri/grand_juri */
        if(role === 'user'){
            actions+='<button class="btn-xs purple" onclick="openUserDetail('+u.id+',\''+safeName+'\')" title="Lihat Riwayat Identitas Peserta"><i class="fas fa-id-card"></i></button>';
        }
        if(!isMe&&!isOtherAdmin){
            actions+='<button class="btn-xs blue" onclick="openPwdModal('+u.id+',\''+safeName+'\')" title="Password"><i class="fas fa-key"></i></button>';
        }
        if(!isMe){
            actions+='<button class="btn-xs green" onclick="openRoleMenu(event,'+u.id+',\''+safeName+'\',\''+role+'\')" title="Ubah Role"><i class="fas fa-arrows-rotate"></i></button>';
            actions+='<button class="btn-xs red" onclick="deleteUser('+u.id+',\''+safeName+'\')" title="Hapus User"><i class="fas fa-trash-can"></i></button>';
        }

        var bottomHtml='';
        if(actions){
            bottomHtml='<div class="user-card-bottom">'+actions+'</div>';
        }

        div.innerHTML=topHtml+bottomHtml;
        c.appendChild(div);
    }
}

/* ═══ KELOLA MVP ═══ */
function loadMvpData() {
    fetch('/api/admin/mvp-ikan', {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(data => {
        var tb = document.getElementById('mvpTableBody');
        if(!data.length) {
            tb.innerHTML = '<tr><td colspan="7" style="text-align:center; color:var(--light); padding:20px;"><i class="fas fa-inbox" style="font-size:18px;display:block;margin-bottom:6px;opacity:.4;"></i>Belum ada ikan yang didaftarkan MVP.</td></tr>';
            return;
        }
        tb.innerHTML = '';
        data.forEach((d, idx) => {
            // Escape nama untuk dipakai di onclick string
            var safeName = esc(d.nama_peserta).replace(/'/g, "\\'");
            tb.innerHTML += 
                '<tr>' +
                    '<td style="font-weight:600;color:var(--light);font-size:11px;">' + (idx + 1) + '</td>' +
                    '<td style="font-weight:700;">' + esc(d.nama_peserta) + '</td>' +
                    '<td style="font-size:11px;color:var(--muted);">' + esc(d.detail_anggota) + '</td>' +
                    '<td style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;">' + esc(d.kategori) + '</td>' +
                    '<td style="font-size:11px;color:var(--muted);">' + esc(d.kelas) + '</td>' +
                    '<td style="font-weight:700;color:var(--primary);">Tank ' + esc(d.nomor_tank) + '</td>' +
                    '<td style="text-align:center;">' +
                        '<button class="btn-xs red" onclick="deleteMvpIkan(' + d.id + ',\'' + safeName + '\')" title="Hapus dari MVP"><i class="fas fa-trash-can"></i></button>' +
                    '</td>' +
                '</tr>';
        });
    })
    .catch(function(){
        var tb = document.getElementById('mvpTableBody');
        tb.innerHTML = '<tr><td colspan="7" style="text-align:center; color:var(--danger); padding:20px;"><i class="fas fa-triangle-exclamation"></i> Gagal memuat data.</td></tr>';
    });
}

/* ═══════════════════════════════════════════════
   DATA IKAN MVP (SAMA LIKE GRAND JURI)
   ═══════════════════════════════════════════════ */
function loadMvpIkanData(){
    var tb = document.getElementById('mvpDataBody');
    if(!tb) return;
    tb.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data MVP...</p></div></td></tr>';

    fetch('/api/admin/mvp-ikan-data',{headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data){
        mvpIkanDataCache = {};
        if(!data || data.length === 0){
            tb.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-star" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada peserta yang mengirimkan data MVP.</div></td></tr>';
            return;
        }
        tb.innerHTML = '';
        data.forEach(function(p, idx){
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td style="font-weight:700;"><i class="fas fa-city" style="color:#D8B4FE;margin-right:6px;font-size:11px;"></i>'+esc(p.detail_anggota)+'</td>'
                +'<td style="text-align:center;"><span class="role-badge role-grand"><i class="fas fa-users" style="margin-right:3px;font-size:9px;"></i>'+p.jumlah_peserta+' Peserta</span></td>'
                +'<td style="text-align:center;"><span class="role-badge role-grand"><i class="fas fa-star" style="margin-right:3px;font-size:9px;color:var(--gold-400);"></i>'+p.total_mvp+' Ikan MVP</span></td>'
                +'<td style="text-align:center;"><span class="status-badge s-dinilai"><i class="fas fa-paper-plane" style="margin-right:3px;font-size:9px;"></i>TERKIRIM</span></td>'
                +'<td style="text-align:center;"><button class="btn-xs purple" onclick="openMvpDataDetail('+idx+')"><i class="fas fa-eye"></i> Lihat Ikan</button></td>';
            tb.appendChild(tr);

            // Cache data per ikan untuk bonus modal
            if(p.ikans){
                p.ikans.forEach(function(ikan){
                    mvpIkanDataCache[ikan.ikan_id] = ikan;
                });
            }
        });
        window._mvpDataGrouped = data;
    })
    .catch(function(){
        if(tb) tb.innerHTML = '<tr><td colspan="5"><div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i> Gagal memuat data MVP.</div></td></tr>';
    });
}

function openMvpDataDetail(idx){
    var p = window._mvpDataGrouped[idx];
    if(!p) return;

    var totalNote = (p.total_team_rank_point !== undefined)
        ? ' Total Rank Point Team: <strong style="color:#D8B4FE;">'+p.total_team_rank_point+'</strong> (rank '+p.total_rank_only+' + bonus '+(p.total_team_rank_point - p.total_rank_only)+').'
        : '';

    var html = '<div class="detail-banner" style="background:linear-gradient(135deg,rgba(168,85,247,.10),rgba(168,85,247,.04));border-color:rgba(168,85,247,.30);">'
        +'<div><h4 style="color:#D8B4FE;">'+esc(p.detail_anggota)+'</h4>'
        +'<div class="meta"><span><i class="fas fa-star"></i> '+p.total_mvp+' ikan MVP</span><span><i class="fas fa-users"></i> '+p.jumlah_peserta+' peserta</span></div></div>'
        +'<div class="detail-total-chip" style="background:linear-gradient(135deg,#7c3aed,var(--purple));"><i class="fas fa-trophy" style="margin-right:4px;"></i> '+p.total_team_rank_point+'</div>'
        +'</div>'
        +'<div style="background:rgba(168,85,247,.06);border:1px solid rgba(168,85,247,.25);border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:11px;color:var(--text);line-height:1.6;">'
        +'<div style="font-weight:800;color:#D8B4FE;margin-bottom:4px;"><i class="fas fa-circle-info" style="margin-right:4px;"></i> Sistem Rank Point</div>'
        +'Rank Point dihitung dari posisi <strong>Top 10 per Kategori+Kelas</strong>. Bonus (+100 per jenis) ditambahkan setelah ranking.'
        +totalNote
        +'</div>';

    html += '<div style="overflow-x:auto;border:1px solid var(--bd-2);border-radius:12px;"><table class="data-table" style="min-width:700px;">';
    html += '<thead><tr><th style="width:30px;">#</th><th>PESERTA</th><th>KATEGORI</th><th>KELAS</th><th>NO. TANK</th><th style="text-align:center;">RANK POINT</th><th>BONUS</th><th style="text-align:center;">AKSI</th></tr></thead><tbody>';

    p.ikans.forEach(function(ikan, i){
        var bonusCount = (ikan.bonus_list || []).length;
        var bonusHtml = bonusCount > 0
            ? '<span class="role-badge role-grand" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.3);color:#6EE7B7;"><i class="fas fa-trophy" style="margin-right:3px;font-size:9px;color:var(--gold-400);"></i>+'+ikan.total_bonus+' ('+bonusCount+')</span>'
            : '<span style="font-size:11px;color:var(--text-low);">—</span>';

        var rankPt = ikan.rank_point || 0;
        var finalRank = ikan.final_rank_point || 0;
        var pos = ikan.position || 0;
        var rankHtml;

        if(finalRank > 0 && pos >= 1 && pos <= 10){
            var rkBg, rkColor;
            if(pos === 1){ rkBg = 'rgba(16,185,129,.14)'; rkColor = '#34D399'; }
            else if(pos <= 3){ rkBg = 'var(--warning-lt)'; rkColor = 'var(--gold-300)'; }
            else if(pos <= 6){ rkBg = 'rgba(34,211,238,.08)'; rkColor = 'var(--cyan-300)'; }
            else { rkBg = 'rgba(168,85,247,.12)'; rkColor = '#D8B4FE'; }

            var medal = '';
            if(pos === 1) medal = '<i class="fas fa-medal" style="color:var(--gold-400);font-size:11px;margin-right:3px;"></i>';
            else if(pos === 2) medal = '<i class="fas fa-medal" style="color:#C0C0C0;font-size:11px;margin-right:3px;"></i>';
            else if(pos === 3) medal = '<i class="fas fa-medal" style="color:#CD7F32;font-size:11px;margin-right:3px;"></i>';

            rankHtml = '<div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">' +
                '<span style="display:inline-block;padding:5px 12px;border-radius:8px;font-size:14px;font-weight:900;background:'+rkBg+';color:'+rkColor+';border:1px solid rgba(255,255,255,.08);">'+finalRank+'</span>' +
                medal + // Dipindahkan ke sebelah kanan Rank Point
            '</div>';
        } else {
            rankHtml = ikan.total_bonus > 0
                ? '<div style="text-align:center;"><span style="display:inline-block;padding:4px 12px;border-radius:8px;font-size:14px;font-weight:900;background:rgba(16,185,129,.12);color:#34D399;border:1px solid rgba(16,185,129,.25);">'+ikan.total_bonus+'</span><div style="font-size:9px;color:var(--text-low);font-weight:700;margin-top:3px;">bonus saja</div></div>'
                : '<div style="text-align:center;font-size:11px;color:var(--text-low);">—</div>';
        }

        html += '<tr>'
            +'<td style="font-weight:700;color:var(--text-low);font-size:11px;">'+(i+1)+'</td>'
            +'<td style="font-weight:700;">'+esc(ikan.nama_peserta)+'</td>'
            +'<td style="font-weight:600;text-transform:uppercase;color:var(--text-mid);font-size:11px;">'+esc(ikan.kategori)+'</td>'
            +'<td>'+esc(ikan.kelas || '-')+'</td>'
            +'<td style="font-weight:700;color:var(--cyan-300);">Tank '+(ikan.nomor_tank || '—')+'</td>'
            +'<td>'+rankHtml+'</td>'
            +'<td>'+bonusHtml+'</td>'
            +'<td style="text-align:center;"><button class="btn-xs gold" onclick="openMvpDataBonus('+ikan.ikan_id+')" title="Kelola Bonus Point"><i class="fas fa-trophy"></i> Bonus</button></td>'
            +'</tr>';
    });

    html += '</tbody></table></div>';

    document.getElementById('detailBody').innerHTML = html;
    openModal('modalDetail');
}

function openMvpDataBonus(ikanId){
    var p = mvpIkanDataCache[ikanId];
    if(!p){
        popupError('Data Tidak Ditemukan','Ikan MVP tidak ditemukan dalam cache. Coba refresh halaman.');
        return;
    }
    currentBonusIkanId = ikanId;

    var html = '';
    html += '<div style="background:linear-gradient(135deg,rgba(245,158,11,.10),rgba(245,158,11,.04));border:1px solid rgba(245,158,11,.25);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">';
    html += '<div><h4 style="font-size:14px;font-weight:800;color:var(--text-hi);">'+esc(p.nama_peserta)+'</h4>';
    html += '<div style="font-size:11px;color:var(--gold-300);margin-top:3px;display:flex;gap:12px;"><span><i class="fas fa-tag"></i> '+esc(p.kategori)+' - '+esc(p.kelas)+'</span><span><i class="fas fa-hashtag"></i> Tank '+(p.nomor_tank || '—')+'</span></div></div>';
    html += '<div style="text-align:center;flex-shrink:0;">';
    html += '<div style="font-size:9px;color:var(--gold-300);font-weight:800;letter-spacing:.5px;">RANK POINT</div>';
    html += '<div style="font-size:22px;font-weight:900;color:var(--gold-300);line-height:1;">'+(p.rank_point || 0)+'</div>';
    if(p.total_bonus > 0){
        html += '<div style="font-size:9px;color:#6EE7B7;font-weight:800;margin-top:2px;">+ '+p.total_bonus+' BONUS</div>';
        html += '<div style="font-size:26px;font-weight:900;color:#6EE7B7;">'+(p.final_rank_point || p.rank_point || 0)+'</div>';
    }
    if((p.rank_point || 0) === 0 && (p.total_bonus || 0) === 0){
        html += '<div style="font-size:10px;color:var(--text-low);font-weight:600;margin-top:4px;">(Tidak masuk Top 10)</div>';
    }
    html += '</div></div>';

    html += '<div style="font-size:10px;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Pilih Bonus Point (+100 per jenis)</div>';

    bonusTypes.forEach(function(bt){
        var applied = (p.bonus_list || []).indexOf(bt.key) !== -1;
        if(applied){
            html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border:1px solid rgba(16,185,129,.3);border-radius:10px;margin-bottom:5px;background:rgba(16,185,129,.06);">';
            html += '<div style="display:flex;align-items:center;gap:10px;">';
            html += '<i class="fas fa-check-circle" style="color:#6EE7B7;font-size:15px;"></i>';
            html += '<div><div style="font-size:12px;font-weight:800;color:#6EE7B7;">'+bt.label+'</div><div style="font-size:10px;color:#059669;">+100 point</div></div></div>';
            html += '<button class="btn-xs red" onclick="removeBonus(\''+bt.key+'\')" style="padding:5px 10px;"><i class="fas fa-times"></i> Hapus</button>';
            html += '</div>';
        } else {
            html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border:1px solid var(--bd-2);border-radius:10px;margin-bottom:5px;cursor:pointer;transition:all .2s;" onclick="addBonus(\''+bt.key+'\',this)" onmouseover="this.style.borderColor=\'rgba(245,158,11,.30)\';this.style.background=\'rgba(245,158,11,.06)\'" onmouseout="this.style.borderColor=\'var(--bd-2)\';this.style.background=\'transparent\'">';
            html += '<div style="display:flex;align-items:center;gap:10px;">';
            html += '<i class="fas '+bt.icon+'" style="color:var(--text-low);font-size:15px;"></i>';
            html += '<div><div style="font-size:12px;font-weight:700;color:var(--text-hi);">'+bt.label+'</div><div style="font-size:10px;color:var(--text-mid);">+100 point</div></div></div>';
            html += '<i class="fas fa-plus-circle" style="color:var(--text-low);font-size:17px;"></i>';
            html += '</div>';
        }
    });

    document.getElementById('bonusModalBody').innerHTML = html;
    openModal('modalBonus');
}

/* ═══ HAPUS IKAN DARI MVP ═══ */
function deleteMvpIkan(ikanId, nama) {
    openConfirmPopup(
        'Hapus dari Pendaftaran MVP',
        'Yakin ingin menghapus ikan milik <strong>' + esc(nama) + '</strong> dari pendaftaran MVP?<br><span style="font-size:11px;color:var(--warning);">Ikan tetap ada di sistem, hanya dihapus dari daftar MVP. Peserta dapat mendaftarkan ulang.</span>',
        'Ya, Hapus dari MVP',
        function() {
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('ikan_id', ikanId);

            fetch('/api/admin/delete-mvp-ikan', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    loadMvpData();
                    popupSuccess('Berhasil Dihapus dari MVP', 'Ikan milik <strong>' + esc(nama) + '</strong> berhasil dihapus dari pendaftaran MVP. Peserta dapat mendaftarkan ulang ikan ini.');
                } else {
                    popupError('Gagal Menghapus', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function() {
                popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
            });
        }
    );
}

function loadMvpStatus() {
    fetch('/api/admin/mvp-status', {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(d => {
        updateMvpToggleUI(d.is_open || false);
        updateMvpFeatureUI(d.feature_enabled !== false);

        var input = document.getElementById('mvpMaxInput');
        if (input && d.max_mvp) input.value = d.max_mvp;
    })
    .catch(() => updateMvpToggleUI(false));
}

function saveMvpRegistrationMax() {
    var input = document.getElementById('mvpMaxInput');
    var btn = document.getElementById('btnSaveMvpMax');
    var limit = input ? parseInt(input.value, 10) : 0;

    if (!limit || limit < 1) {
        popupError('Batas Tidak Valid', 'Batas MVP minimal 1 ikan per user.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.dataset.oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    }

    if (input) input.disabled = true;

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('max_mvp', limit);

    fetch('/api/admin/mvp-registration-max', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(r){
        return r.json().then(function(d){
            if (!r.ok) throw d;
            return d;
        });
    })
    .then(function(d){
        if (!d.success) throw d;

        if (input) input.value = d.max_mvp || limit;

        popupSuccess(
            'Batas MVP Disimpan',
            d.message || 'Batas MVP berhasil diperbarui.'
        );

        loadMvpStatus();
    })
    .catch(function(e){
        popupError('Gagal', e.message || 'Gagal menyimpan batas MVP.');
    })
    .finally(function(){
        if (input) input.disabled = false;

        if (btn) {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.oldHtml || '<i class="fas fa-floppy-disk"></i> Simpan Batas';
        }
    });
}

function toggleMvpRegistration() {
    var btn = document.getElementById('btnToggleMvp');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/api/admin/toggle-mvp-registration', {
        method:'POST',
        headers:{
            'X-Requested-With':'XMLHttpRequest',
            'Accept':'application/json',
            'X-CSRF-TOKEN':getCsrf()
        }
    })
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            updateMvpToggleUI(d.is_open);

            var input = document.getElementById('mvpMaxInput');
            if (input && d.max_mvp) input.value = d.max_mvp;

            popupSuccess('Status MVP Diperbarui', d.message);
        } else {
            popupError('Gagal', d.message);
        }
    })
    .catch(() => popupError('Error', 'Gagal menghubungi server'))
    .finally(() => { btn.disabled = false; });
}

function loadTeamChampionStatus() {
    fetch('/api/admin/team-champion-status', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(d){
        updateTeamChampionToggleUI(!!d.is_open);
        updateTeamChampionFeatureUI(d.feature_enabled !== false);

        var input = document.getElementById('teamChampionMaxInput');
        if (input && d.max_team_champion) input.value = d.max_team_champion;
    })
    .catch(function(){
        updateTeamChampionToggleUI(false);
    });
}

function updateTeamChampionToggleUI(isOpen) {
    var btn = document.getElementById('btnToggleTeamChampion');
    var txt = document.getElementById('teamChampionStatusText');

    if (!btn || !txt) return;

    if (isOpen) {
        btn.className = 'btn-primary';
        btn.innerHTML = '<i class="fas fa-lock"></i> Tutup Team Champion';
        txt.innerHTML = '<span style="color:#6EE7B7;"><i class="fas fa-check-circle"></i> Pendaftaran Team Champion sedang DIBUKA untuk user.</span>';
    } else {
        btn.className = 'btn-primary';
        btn.innerHTML = '<i class="fas fa-lock-open"></i> Buka Team Champion';
        txt.innerHTML = '<span style="color:#FCA5A5;"><i class="fas fa-lock"></i> Pendaftaran Team Champion sedang DITUTUP untuk user.</span>';
    }
}

function toggleTeamChampionRegistration() {
    var btn = document.getElementById('btnToggleTeamChampion');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    }

    fetch('/api/admin/toggle-team-champion-registration', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrf()
        }
    })
    .then(function(r){
        return r.json().then(function(d){
            if (!r.ok) throw d;
            return d;
        });
    })
    .then(function(d){
        if (!d.success) throw d;

        var isOpen = !!d.is_open;

        updateTeamChampionToggleUI(isOpen);
        var input = document.getElementById('teamChampionMaxInput');
        if (input && d.max_team_champion) input.value = d.max_team_champion;

        popupSuccess(
            isOpen ? 'Team Champion Dibuka' : 'Team Champion Ditutup',
            d.message || (
                isOpen
                    ? 'Halaman Team Champion di panel user sudah dibuka.'
                    : 'Halaman Team Champion di panel user sudah ditutup.'
            )
        );

        if (typeof loadTeamChampionStatus === 'function') loadTeamChampionStatus();
    })
    .catch(function(e){
        popupError(
            'Gagal',
            e.message || 'Gagal mengubah status pendaftaran Team Champion.'
        );

        if (typeof loadTeamChampionStatus === 'function') loadTeamChampionStatus();
    })
    .finally(function(){
        if (btn) btn.disabled = false;
    });
}

function saveTeamChampionRegistrationMax() {
    var input = document.getElementById('teamChampionMaxInput');
    var btn = document.getElementById('btnSaveTeamChampionMax');
    var limit = input ? parseInt(input.value, 10) : 0;

    if (!limit || limit < 1) {
        popupError('Batas Tidak Valid', 'Batas Team Champion minimal 1 ikan per user.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.dataset.oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    }

    if (input) input.disabled = true;

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('max_team_champion', limit);

    fetch('/api/admin/team-champion-registration-max', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(r){
        return r.json().then(function(d){
            if (!r.ok) throw d;
            return d;
        });
    })
    .then(function(d){
        if (!d.success) throw d;

        if (input) input.value = d.max_team_champion || limit;

        popupSuccess(
            'Batas Team Champion Disimpan',
            d.message || 'Batas Team Champion berhasil diperbarui.'
        );

        loadTeamChampionStatus();
    })
    .catch(function(e){
        popupError('Gagal', e.message || 'Gagal menyimpan batas Team Champion.');
    })
    .finally(function(){
        if (input) input.disabled = false;

        if (btn) {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.oldHtml || '<i class="fas fa-floppy-disk"></i> Simpan Batas';
        }
    });
}

function loadTeamChampionPeserta() {
    var tb = document.getElementById('teamChampionPesertaBody');
    var countEl = document.getElementById('teamChampionPesertaCount');

    if (!tb) return;

    tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:16px;color:var(--text-mid);">Memuat data...</td></tr>';

    fetch('/api/admin/team-champion-submitted-peserta', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (countEl) countEl.textContent = data.length + ' peserta';

        if (!data.length) {
            tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:16px;color:var(--text-mid);">Belum ada peserta yang mengirim Team Champion.</td></tr>';
            return;
        }

        tb.innerHTML = '';

        data.forEach(function(d, idx){
            var safeName = esc(d.nama_peserta || '-').replace(/'/g, "\\'");
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + (idx + 1) + '</td>' +
                '<td style="font-weight:800;">' + esc(d.nama_peserta || '-') + '</td>' +
                '<td>' + esc(d.detail_anggota || '-') + '</td>' +
                '<td>' + esc(d.email || '-') + '</td>' +
                '<td style="text-align:center;font-weight:900;color:var(--gold-300);">' + d.jumlah_team_champion + '</td>' +
                '<td style="text-align:center;font-weight:900;color:var(--cyan-300);">' + d.jumlah_mvp + '</td>' +
                '<td style="text-align:center;">' +
                    '<button class="btn-xs green" onclick="unlockTeamChampionPeserta(' + d.peserta_id + ',\'' + safeName + '\')">' +
                        '<i class="fas fa-lock-open"></i> Buka' +
                    '</button>' +
                '</td>';
            tb.appendChild(tr);
        });
    })
    .catch(function(){
        tb.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--danger);padding:16px;">Gagal memuat data.</td></tr>';
    });
}

function unlockTeamChampionPeserta(pesertaId, nama) {
    openConfirmPopup(
        'Buka Kunci Team Champion',
        'Yakin ingin membuka kembali Team Champion untuk <strong>' + esc(nama) + '</strong>? MVP peserta ini juga akan dibuka ulang agar data tetap konsisten.',
        'Ya, Buka Kunci',
        function(){
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('peserta_id', pesertaId);

            fetch('/api/admin/unlock-team-champion-peserta', {
                method:'POST',
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body:fd
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.success) {
                    loadTeamChampionPeserta();
                    loadTeamChampionIkan();
                    if (typeof loadMvpPeserta === 'function') loadMvpPeserta();
                    popupSuccess('Berhasil Dibuka', d.message);
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){
                popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
            });
        }
    );
}

function openTeamChampionBonus(ikanId){
    function openFromScoringCache(){
        var idx = allScoringData.findIndex(function(item){
            return parseInt(item.id, 10) === parseInt(ikanId, 10);
        });

        if(idx < 0){
            popupError(
                'Data Tidak Ditemukan',
                'Data penilaian ikan belum tersedia. Pastikan ikan sudah final dan memiliki nilai.'
            );
            return;
        }

        openBonusModal(idx);
    }

    var hasIkan = allScoringData.some(function(item){
        return parseInt(item.id, 10) === parseInt(ikanId, 10);
    });

    if(hasIkan){
        openFromScoringCache();
        return;
    }

    fetch('/api/admin/scoring-data', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(r){
        if(!r.ok){
            return r.json().then(function(d){ throw d; });
        }

        return r.json();
    })
    .then(function(data){
        allScoringData = Array.isArray(data) ? data : [];
        openFromScoringCache();
    })
    .catch(function(e){
        popupError(
            'Gagal Memuat Data',
            e && e.message ? e.message : 'Gagal memuat data ranking ikan.'
        );
    });
}

function loadTeamChampionIkan() {
    var tb = document.getElementById('teamChampionIkanBody');
    var countEl = document.getElementById('teamChampionIkanCount');

    if (!tb) return;

    tb.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:16px;color:var(--text-mid);">Memuat data...</td></tr>';

    fetch('/api/admin/team-champion-ikan', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (countEl) countEl.textContent = data.length + ' ikan';

        if (!data.length) {
            tb.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:16px;color:var(--text-mid);">Belum ada ikan Team Champion.</td></tr>';
            return;
        }

        tb.innerHTML = '';

        data.forEach(function(d, idx){
            var safeName = esc(d.nama_peserta || '-').replace(/'/g, "\\'");
            var tr = document.createElement('tr');
            var bonusHtml = d.total_bonus > 0
                ? '<span class="status-badge s-dinilai">+' + esc(d.total_bonus) + ' (' + (d.bonus_list || []).length + ')</span>'
                : '<span style="color:var(--text-low);">—</span>';

            var statusHtml = d.can_manage_bonus
                ? '<span class="status-badge s-dinilai">FINAL</span>'
                : (
                    !d.is_submitted
                        ? '<span style="font-size:10px;color:var(--gold-300);">Belum dikirim</span>'
                        : '<span style="font-size:10px;color:var(--text-low);">Belum final</span>'
                );

            var actionHtml =
                (d.can_manage_bonus
                    ? '<button class="btn-xs gold" onclick="openTeamChampionBonus(' + d.id + ')" title="Kelola Bonus Point">' +
                        '<i class="fas fa-trophy"></i> Bonus' +
                    '</button> '
                    : ''
                ) +
                '<button class="btn-xs red" onclick="deleteTeamChampionIkan(' + d.id + ',\'' + safeName + '\')">' +
                    '<i class="fas fa-trash"></i> Hapus' +
                '</button>';

            tr.innerHTML =
                '<td>' + (idx + 1) + '</td>' +
                '<td style="font-weight:800;">' + esc(d.nama_peserta || '-') + '</td>' +
                '<td>' + esc(d.detail_anggota || '-') + '</td>' +
                '<td>' + esc(d.kategori || '-') + '</td>' +
                '<td>' + esc(d.kelas || '-') + '</td>' +
                '<td>' + esc(d.nomor_tank || '-') + '</td>' +
                '<td style="text-align:center;">' + bonusHtml + '</td>' +
                '<td style="text-align:center;">' + statusHtml + '</td>' +
                '<td style="text-align:center;white-space:nowrap;">' + actionHtml + '</td>';
            tb.appendChild(tr);
        });
    })
    .catch(function(){
        tb.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--danger);padding:16px;">Gagal memuat data.</td></tr>';
    });
}

function deleteTeamChampionIkan(ikanId, nama) {
    openConfirmPopup(
        'Hapus dari Team Champion',
        'Yakin ingin menghapus ikan milik <strong>' + esc(nama) + '</strong> dari Team Champion? Status MVP ikan ini tidak akan diubah.',
        'Ya, Hapus',
        function(){
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('ikan_id', ikanId);

            fetch('/api/admin/delete-team-champion-ikan', {
                method:'POST',
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body:fd
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.success) {
                    loadTeamChampionIkan();
                    loadTeamChampionPeserta();
                    if (typeof loadMvpData === 'function') loadMvpData();
                    popupSuccess('Berhasil', d.message);
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){
                popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
            });
        }
    );
}

/* ═══ KELOLA MESIN UNDIAN ═══ */
function loadUndianStatus() {
    fetch('/api/admin/undian-status', {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(d => {
        updateUndianToggleUI(d.is_open || false);
    })
    .catch(() => updateUndianToggleUI(true));
}

function toggleUndianRegistration() {
    var btn = document.getElementById('btnToggleUndian');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('/api/admin/toggle-undian-registration', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}})
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            updateUndianToggleUI(d.is_open);
            popupSuccess('Status Mesin Undian Diperbarui', d.message);
        } else popupError('Gagal', d.message);
    })
    .catch(() => popupError('Error', 'Gagal menghubungi server'))
    .finally(() => { btn.disabled = false; });
}

function updateUndianToggleUI(isOpen) {
    var btn = document.getElementById('btnToggleUndian');
    var txt = document.getElementById('undianStatusText');
    if(btn) btn.disabled = false;
    if(isOpen) {
        btn.innerHTML = '<i class="fas fa-lock-open"></i> KUNCI MESIN UNDIAN';
        btn.style.background = 'var(--danger)'; btn.style.boxShadow = '0 3px 10px rgba(239,68,68,.2)';
        txt.innerHTML = '<i class="fas fa-circle-check" style="color:var(--success);"></i> Mesin Undian sedang <b style="color:var(--success);">DIBUKA</b>. Peserta dapat mengacak nomor tank.';
    } else {
        btn.innerHTML = '<i class="fas fa-lock"></i> BUKA MESIN UNDIAN';
        btn.style.background = 'var(--success)'; btn.style.boxShadow = '0 3px 10px rgba(34,197,94,.2)';
        txt.innerHTML = '<i class="fas fa-circle-xmark" style="color:var(--danger);"></i> Mesin Undian sedang <b style="color:var(--danger);">DIKUNCI</b>. Peserta hanya bisa mendaftarkan ikan.';
    }
}

var _fotoPageUnlocked = false;

function loadFotoReplaceStatus(){
    fetch('/api/admin/foto-replace-status', {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(d => { _fotoPageUnlocked = !!d.foto_page_unlocked; updateFotoReplaceToggleUI(_fotoPageUnlocked); })
    .catch(() => { _fotoPageUnlocked = false; updateFotoReplaceToggleUI(false); });
}

function toggleFotoPageLock(){
    var btn = document.getElementById('btnToggleFotoReplace');
    if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('/api/admin/toggle-foto-replace', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}})
    .then(r => r.json())
    .then(d => {
        if(!d.success) throw new Error(d.message || 'Gagal');
        _fotoPageUnlocked = !!d.foto_page_unlocked;
        updateFotoReplaceToggleUI(_fotoPageUnlocked);
        popupSuccess(_fotoPageUnlocked ? 'Foto Ikan Dibuka' : 'Foto Ikan Dikunci', d.message);
    })
    .catch(e => popupError('Gagal', e.message || 'Gagal menghubungi server'))
    .finally(() => { var b=document.getElementById('btnToggleFotoReplace'); if(b) b.disabled=false; });
}

function updateFotoReplaceToggleUI(unlocked){
    var btn = document.getElementById('btnToggleFotoReplace');
    var txt = document.getElementById('fotoReplaceStatusText');
    if(!btn) return;
    btn.disabled = false;
    if(unlocked){
        btn.innerHTML = '<i class="fas fa-lock"></i> KUNCI';
        btn.style.background = 'var(--danger)'; btn.style.boxShadow = '0 3px 10px rgba(239,68,68,.2)';
        if(txt) txt.innerHTML = '<i class="fas fa-lock-open" style="color:var(--success);"></i> Halaman Foto Ikan juri <b style="color:var(--success);">TERBUKA</b>. Juri bisa mengupload foto ikan yang sudah disetujui (batas 3MB/tank). Tombol <b>Minta Upload Ulang</b> per-tank tetap berfungsi.';
    } else {
        btn.innerHTML = '<i class="fas fa-lock-open"></i> IZINKAN';
        btn.style.background = 'var(--success)'; btn.style.boxShadow = '0 3px 10px rgba(34,197,94,.2)';
        if(txt) txt.innerHTML = '<i class="fas fa-lock" style="color:var(--danger);"></i> Halaman Foto Ikan juri <b style="color:var(--danger);">TERKUNCI</b>. Panel foto di dashboard juri tertutup (overlay), juri tidak bisa upload.';
    }
}

function loadAdminIkanFotos(ikanId){
    var box = document.getElementById('admFotoGallery');
    if(!box) return;
    box.innerHTML = '<div style="padding:14px;text-align:center;color:var(--text-mid,#94a3b8);font-size:12px;"><i class="fas fa-spinner fa-spin"></i> Memuat foto...</div>';

    fetch('/api/admin/ikan-fotos/'+ikanId, {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(d => {
        if(!d || !d.success){ box.innerHTML = '<div style="padding:14px;color:#f87171;font-size:12px;text-align:center;">Gagal memuat foto.</div>'; return; }

        if(!d.fotos || d.fotos.length === 0){
            box.innerHTML = '<div style="padding:14px;border:1px dashed var(--bd-3,#cbd5e1);border-radius:10px;color:var(--text-mid,#94a3b8);font-size:12px;text-align:center;"><i class="fas fa-image" style="margin-right:6px;"></i>Peserta belum mengunggah foto ikan.</div>';
            return;
        }

        var mb = (d.used_bytes/1048576).toFixed(2);
        var html = '<div style="font-size:11px;color:var(--text-mid,#94a3b8);margin-bottom:8px;font-weight:700;"><i class="fas fa-images" style="margin-right:5px;"></i>'+d.count+' foto · '+mb+' MB / 3 MB</div>';
        html += '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;">';
        d.fotos.forEach(function(f){
            html += '<div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--bd-2,#e5e7eb);">'
                + '<img src="'+f.url+'" title="Klik untuk perbesar" onclick="window.open(this.src,\'_blank\')" style="width:100%;height:96px;object-fit:cover;display:block;cursor:zoom-in;">'
                + '<button onclick="admDeleteFoto('+f.id+','+ikanId+')" title="Hapus foto ini" style="position:absolute;top:5px;right:5px;width:24px;height:24px;border:none;border-radius:7px;background:rgba(239,68,68,.92);color:#fff;cursor:pointer;font-size:11px;display:grid;place-items:center;"><i class="fas fa-trash-can"></i></button>'
                + '</div>';
        });
        html += '</div>';
        box.innerHTML = html;
    })
    .catch(function(){ box.innerHTML = '<div style="padding:14px;color:#f87171;font-size:12px;text-align:center;">Gagal memuat foto.</div>'; });
}

function admDeleteFoto(fotoId, ikanId){
    openConfirmPopup('Hapus Foto Ikan?', 'Foto ini akan <b>dihapus permanen</b> dan tidak bisa dikembalikan.', 'Ya, Hapus', function(){
        var fd = new FormData();
        fd.append('_token', getCsrf());
        fd.append('foto_id', fotoId);
        fetch('/api/admin/delete-foto', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}, body: fd})
        .then(r => r.json())
        .then(d => {
            if(!d.success) throw new Error(d.message || 'Gagal');
            popupSuccess('Foto Dihapus', d.message);
            loadAdminIkanFotos(ikanId);
        })
        .catch(e => popupError('Gagal', e.message || 'Gagal menghapus foto.'));
    });
}

/* ═══════════════════════════════════════════════
   GALERI FOTO — semua unggahan (read-only + hapus)
   ═══════════════════════════════════════════════ */
function galeriFotoBadge(role){
    if(role === 'juri')  return '<span style="position:absolute;left:6px;top:6px;padding:2px 7px;border-radius:6px;background:rgba(34,211,238,.9);color:#04121e;font-size:8px;font-weight:900;">JURI</span>';
    if(role === 'admin') return '<span style="position:absolute;left:6px;top:6px;padding:2px 7px;border-radius:6px;background:rgba(124,58,237,.9);color:#fff;font-size:8px;font-weight:900;">ADMIN</span>';
    return '<span style="position:absolute;left:6px;top:6px;padding:2px 7px;border-radius:6px;background:rgba(148,163,184,.9);color:#0b1220;font-size:8px;font-weight:900;">LAMA</span>';
}

var _galeriItems = []; // ★ cache galeri agar search tidak fetch ulang

function galeriSafeName(s){
    return String(s || '').trim().replace(/[^a-zA-Z0-9\-]+/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
}

function loadGaleriFoto(){
    var wrap = document.getElementById('galeriFotoWrap');
    if(!wrap) return;
    wrap.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat galeri...</p></div>';

    fetch('/api/admin/all-fotos', {headers:{'Accept':'application/json'}})
    .then(r => r.json())
    .then(d => {
        if(!d || !d.success){ _galeriItems = []; wrap.innerHTML = '<div class="empty-state"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat galeri.</p></div>'; return; }
        _galeriItems = d.items || [];
        var si = document.getElementById('galeriSearchInput');
        renderGaleriFoto(si ? si.value : '');
    })
    .catch(function(){ _galeriItems = []; wrap.innerHTML = '<div class="empty-state"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat galeri.</p></div>'; });
}

function filterGaleriFoto(q){ renderGaleriFoto(q); }

function galeriRoleLabel(role){ return role==='juri'?'JURI':(role==='admin'?'ADMIN':'LAMA'); }
function galeriRoleColor(role){ return role==='juri'?'#67e8f9':(role==='admin'?'#c4b5fd':'#cbd5e1'); }

function renderGaleriFoto(filter){
    var wrap = document.getElementById('galeriFotoWrap');
    if(!wrap) return;
    var q = (filter || '').trim().toLowerCase();
    var items = _galeriItems;
    if(q){
        items = items.filter(function(it){
            var hay = [(it.nama_peserta||''), (it.kategori||''), (it.kelas||''), 'tank '+(it.nomor_tank||''), String(it.nomor_tank||'')].join(' ').toLowerCase();
            return hay.indexOf(q) !== -1;
        });
    }
    var countEl = document.getElementById('galeriSearchCount');
    if(countEl) countEl.textContent = _galeriItems.length ? (items.length + ' / ' + _galeriItems.length + ' tank') : '';

    if(!_galeriItems.length){ wrap.innerHTML = '<div class="empty-state"><i class="fas fa-image"></i><p>Belum ada foto ikan di sistem.</p></div>'; return; }
    if(!items.length){ wrap.innerHTML = '<div class="empty-state"><i class="fas fa-magnifying-glass"></i><p>Tidak ada tank yang cocok dengan pencarian.</p></div>'; return; }

    var html = '<div style="display:flex;flex-direction:column;gap:16px;">';
    items.forEach(function(it){
        var mb = (it.total_size/1048576).toFixed(2);
        var baseName = galeriSafeName(it.nama_peserta || 'peserta') + '_Tank' + (it.nomor_tank || '0') + '_' + galeriSafeName(it.kategori || 'kat') + (it.kelas ? ('_' + galeriSafeName(it.kelas)) : '');
        // ★ #5 nama pengupload (distinct) untuk header
        var uploaders = [];
        it.fotos.forEach(function(f){ if(f.uploader && uploaders.indexOf(f.uploader) === -1) uploaders.push(f.uploader); });
        var uploaderHdr = uploaders.length ? ' <span style="color:var(--cyan-300);font-weight:700;">· <i class="fas fa-camera" style="font-size:9px;margin:0 2px 0 1px;"></i>' + uploaders.map(esc).join(', ') + '</span>' : '';
        html += '<div data-ikan="' + it.ikan_id + '" style="border:1px solid var(--bd-1);border-radius:14px;overflow:hidden;background:var(--glass-2);">'
            + '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;background:rgba(255,255,255,.03);border-bottom:1px solid var(--bd-1);flex-wrap:wrap;">'
            +   '<div style="font-size:12.5px;font-weight:800;color:var(--text-hi);"><i class="fas fa-hashtag" style="color:var(--cyan-400);margin-right:4px;"></i>Tank ' + (it.nomor_tank || '—') + ' <span style="font-weight:600;color:var(--text-mid);font-size:11px;">· ' + (it.kategori || '-') + (it.kelas ? (' / ' + it.kelas) : '') + ' · ' + (it.nama_peserta || '-') + '</span>' + uploaderHdr + '</div>'
            +   '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
            +     '<div style="font-size:10.5px;font-weight:700;color:var(--text-mid);white-space:nowrap;"><i class="fas fa-images" style="margin-right:4px;"></i>' + it.foto_count + ' foto · ' + mb + ' MB</div>'
            +     galeriReupBtn(it)
            +     '<button id="dlbtn-' + it.ikan_id + '" class="gal-anim-btn" onclick="galeriDownloadTank(' + it.ikan_id + ')" title="Unduh semua foto, atau centang beberapa lalu klik untuk unduh terpilih" style="padding:5px 10px;border-radius:8px;border:1px solid rgba(34,211,238,.35);background:rgba(34,211,238,.12);color:var(--cyan-300);font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap;"><i class="fas fa-download"></i> <span class="dl-label">Unduh Semua</span></button>'
            +     '<button class="gal-anim-btn" onclick="admGaleriUpload(' + it.ikan_id + ')" title="Upload foto tank ini (file atau kamera)" style="padding:5px 10px;border-radius:8px;border:1px solid rgba(124,58,237,.4);background:rgba(124,58,237,.14);color:#c4b5fd;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap;"><i class="fas fa-cloud-arrow-up"></i> Upload</button>'
            +   '</div>'
            + '</div>'
            + '<div style="padding:12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">';
        it.fotos.forEach(function(f, fi){
            var fname = baseName + (it.fotos.length > 1 ? ('_' + (fi+1)) : '');
            html += '<div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--bd-2);">'
                + '<input type="checkbox" class="gal-cb" data-url="' + f.url + '" data-fname="' + fname + '" onchange="galeriUpdateDownloadBtn(' + it.ikan_id + ')" title="Pilih foto ini" style="position:absolute;left:6px;top:6px;width:18px;height:18px;accent-color:var(--cyan-500);cursor:pointer;z-index:3;">'
                + '<button onclick="galeriDeleteFoto(' + f.id + ')" title="Hapus foto ini" style="position:absolute;top:6px;right:6px;width:24px;height:24px;border:none;border-radius:7px;background:rgba(239,68,68,.92);color:#fff;cursor:pointer;font-size:11px;display:grid;place-items:center;z-index:3;"><i class="fas fa-trash-can"></i></button>'
                + '<img src="' + f.url + '" title="Klik untuk perbesar" onclick="window.open(this.src,\'_blank\')" style="width:100%;height:112px;object-fit:cover;display:block;cursor:zoom-in;">'
                + '<div style="position:absolute;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:space-between;gap:6px;padding:5px 7px;background:linear-gradient(0deg,rgba(2,6,14,.9),rgba(2,6,14,.35) 70%,transparent);">'
                +   '<div style="min-width:0;">'
                +     '<span style="font-size:8px;font-weight:900;color:' + galeriRoleColor(f.role) + ';text-transform:uppercase;letter-spacing:.3px;">' + galeriRoleLabel(f.role) + '</span>'
                +     (f.uploader ? '<div style="font-size:9px;font-weight:700;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i class="fas fa-user" style="font-size:7px;margin-right:3px;opacity:.8;"></i>' + esc(f.uploader) + '</div>' : '')
                +   '</div>'
                +   '<button onclick="galeriDownloadFoto(\'' + f.url + '\',\'' + fname + '\')" title="Unduh foto ini" style="flex:0 0 auto;width:24px;height:24px;border:none;border-radius:7px;background:rgba(34,211,238,.92);color:#04121e;cursor:pointer;font-size:11px;display:grid;place-items:center;"><i class="fas fa-download"></i></button>'
                + '</div>'
                + '</div>';
        });
        html += '</div></div>';
    });
    html += '</div>';
    wrap.innerHTML = html;
}

// ★ #2: perbarui label tombol unduh sesuai jumlah checkbox tercentang
function galeriUpdateDownloadBtn(ikanId){
    var tankEl = document.querySelector('[data-ikan="' + ikanId + '"]');
    if(!tankEl) return;
    var cbs = Array.prototype.slice.call(tankEl.querySelectorAll('.gal-cb'));
    var n = cbs.filter(function(cb){ return cb.checked; }).length;
    var lbl = document.querySelector('#dlbtn-' + ikanId + ' .dl-label');
    if(lbl) lbl.textContent = n > 0 ? ('Unduh Terpilih (' + n + ')') : 'Unduh Semua';
}

function galeriDownloadFoto(url, filename){
    fetch(url, {headers:{'Accept':'image/*'}})
    .then(function(r){ if(!r.ok) throw new Error('fail'); return r.blob(); })
    .then(function(blob){
        var ext = (blob.type && blob.type.indexOf('png') !== -1) ? 'png' : 'jpg';
        var objectUrl = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = objectUrl;
        a.download = (filename || 'foto-ikan') + '.' + ext;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function(){ URL.revokeObjectURL(objectUrl); }, 1500);
    })
    .catch(function(){ popupError('Gagal', 'Gagal mengunduh foto. Coba lagi.'); });
}

// ★ Unduh semua foto di 1 tank, atau hanya yang dicentang
function galeriDownloadTank(ikanId){
    var tankEl = document.querySelector('[data-ikan="' + ikanId + '"]');
    if(!tankEl) return;
    var cbs = Array.prototype.slice.call(tankEl.querySelectorAll('.gal-cb'));
    var chosen = cbs.filter(function(cb){ return cb.checked; });
    var pick = (chosen.length ? chosen : cbs).map(function(cb){ return {url:cb.getAttribute('data-url'), fname:cb.getAttribute('data-fname')}; });
    if(!pick.length) return;
    popupSuccess('Mengunduh', 'Memulai unduhan ' + pick.length + ' foto' + (chosen.length ? ' (terpilih)' : '') + '...');
    var i = 0;
    (function next(){
        if(i >= pick.length) return;
        galeriDownloadFoto(pick[i].url, pick[i].fname);
        i++;
        setTimeout(next, 700); // jeda agar browser tidak memblokir unduhan beruntun
    })();
}

// ★ Admin upload foto (modal: pilih file ATAU kamera) — mirip panel juri
var _admUp = {ikanId:null, blob:null, stream:null};

function admGaleriUpload(ikanId){
    _admUp = {ikanId:ikanId, blob:null, stream:null};
    var it = null;
    for(var i=0;i<_galeriItems.length;i++){ if(_galeriItems[i].ikan_id === ikanId){ it = _galeriItems[i]; break; } }
    var sub = document.getElementById('admUpSub');
    if(sub) sub.textContent = it ? ('· Tank ' + (it.nomor_tank||'-')) : '';
    admResetUploadUI();
    openModal('modalAdmUpload');
}

function admResetUploadUI(){
    admStopCamera();
    _admUp.blob = null;
    var p=document.getElementById('admUpPreview'); if(p){ p.style.display='none'; p.src=''; }
    var ph=document.getElementById('admUpPlaceholder'); if(ph) ph.style.display='block';
    var sb=document.getElementById('admUpSaveBtn'); if(sb) sb.style.display='none';
    var act=document.getElementById('admUpActions'); if(act) act.style.display='flex';
}

function admCloseUpload(){ admStopCamera(); closeModal('modalAdmUpload'); }

function admOnFilePicked(inp){
    var file = inp.files && inp.files[0];
    inp.value = '';
    if(!file) return;
    admResizeFoto(file, function(resized){ admSetPreviewBlob(resized); });
}

function admSetPreviewBlob(blob){
    _admUp.blob = blob;
    var url = URL.createObjectURL(blob);
    var p = document.getElementById('admUpPreview');
    if(p){ p.src = url; p.style.display='block'; }
    var ph=document.getElementById('admUpPlaceholder'); if(ph) ph.style.display='none';
    var v=document.getElementById('admUpVideo'); if(v) v.style.display='none';
    var camAct=document.getElementById('admUpCamActions'); if(camAct) camAct.style.display='none';
    var act=document.getElementById('admUpActions'); if(act) act.style.display='flex';
    var sb=document.getElementById('admUpSaveBtn'); if(sb) sb.style.display='inline-flex';
}

function admStartCamera(){
    var v = document.getElementById('admUpVideo');
    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){ document.getElementById('admUpFile').click(); return; }
    navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}, audio:false})
    .then(function(stream){
        _admUp.stream = stream;
        if(v){ v.srcObject = stream; v.style.display='block'; }
        var ph=document.getElementById('admUpPlaceholder'); if(ph) ph.style.display='none';
        var p=document.getElementById('admUpPreview'); if(p) p.style.display='none';
        var act=document.getElementById('admUpActions'); if(act) act.style.display='none';
        var camAct=document.getElementById('admUpCamActions'); if(camAct) camAct.style.display='flex';
        var sb=document.getElementById('admUpSaveBtn'); if(sb) sb.style.display='none';
    })
    .catch(function(){ popupError('Kamera', 'Tidak bisa mengakses kamera. Gunakan Pilih File.'); document.getElementById('admUpFile').click(); });
}

function admCapture(){
    var v = document.getElementById('admUpVideo');
    if(!v || !v.videoWidth){ return; }
    var c = document.createElement('canvas'); c.width = v.videoWidth; c.height = v.videoHeight;
    c.getContext('2d').drawImage(v,0,0,c.width,c.height);
    c.toBlob(function(b){
        admStopCamera();
        if(!b){ return; }
        var f = new File([b], 'kamera_'+Date.now()+'.jpg', {type:'image/jpeg'});
        admResizeFoto(f, function(resized){ admSetPreviewBlob(resized); });
    }, 'image/jpeg', 0.85);
}

function admStopCamera(){
    if(_admUp.stream){ _admUp.stream.getTracks().forEach(function(t){ t.stop(); }); _admUp.stream=null; }
    var v=document.getElementById('admUpVideo'); if(v){ v.srcObject=null; v.style.display='none'; }
    var camAct=document.getElementById('admUpCamActions'); if(camAct) camAct.style.display='none';
}

function admDoUpload(){
    if(!_admUp.blob || !_admUp.ikanId){ popupError('Belum ada foto', 'Pilih file atau ambil foto dulu.'); return; }
    var sb = document.getElementById('admUpSaveBtn');
    if(sb){ sb.disabled=true; sb.innerHTML='<i class="fas fa-spinner fa-spin"></i> Mengunggah...'; }
    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('ikan_id', _admUp.ikanId);
    fd.append('foto', _admUp.blob, 'admin_'+Date.now()+'.jpg');
    fetch('/api/admin/upload-foto', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}, body: fd})
    .then(r => r.json())
    .then(d => {
        if(!d.success) throw new Error(d.message || 'Gagal');
        popupSuccess('Foto Terunggah', d.message);
        admCloseUpload();
        loadGaleriFoto();
    })
    .catch(e => popupError('Gagal', e.message || 'Gagal mengunggah foto.'))
    .finally(() => { var b=document.getElementById('admUpSaveBtn'); if(b){ b.disabled=false; b.innerHTML='<i class="fas fa-cloud-arrow-up"></i> Simpan Foto'; } });
}

function admResizeFoto(file, cb){
    try{
        var url = URL.createObjectURL(file);
        var img = new Image();
        img.onload = function(){
            URL.revokeObjectURL(url);
            var maxDim = 1280, w = img.width, h = img.height;
            var scale = Math.min(1, maxDim / Math.max(w, h));
            var nw = Math.round(w * scale), nh = Math.round(h * scale);
            var c = document.createElement('canvas'); c.width = nw; c.height = nh;
            c.getContext('2d').drawImage(img, 0, 0, nw, nh);
            c.toBlob(function(b){
                if(!b){ cb(file); return; }
                var base = (file.name || 'foto').replace(/\.(png|jpe?g)$/i, '');
                cb(new File([b], base + '.jpg', {type:'image/jpeg'}));
            }, 'image/jpeg', 0.82);
        };
        img.onerror = function(){ cb(file); };
        img.src = url;
    }catch(e){ cb(file); }
}

// ★ #4 animasi "pop" saat tombol galeri diklik (delegated, jalan utk elemen dinamis)
document.addEventListener('click', function(e){
    var b = e.target && e.target.closest ? e.target.closest('.gal-anim-btn') : null;
    if(!b) return;
    b.classList.remove('gal-pop');
    void b.offsetWidth; // reflow → restart animasi
    b.classList.add('gal-pop');
    setTimeout(function(){ b.classList.remove('gal-pop'); }, 420);
});

// ★ Tombol per-ikan: minta / batalkan upload ulang foto tank tertentu
function galeriReupBtn(it){
    if(!it.foto_count) return ''; // belum ada foto → juri memang sudah boleh upload
    if(it.reupload_requested){
        return '<button class="gal-anim-btn" onclick="galeriRequestReupload('+it.ikan_id+',0)" title="Batalkan permintaan upload ulang" style="padding:5px 10px;border-radius:8px;border:1px solid rgba(245,158,11,.4);background:rgba(245,158,11,.14);color:var(--gold-300);font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap;"><i class="fas fa-rotate"></i> Batalkan Upload Ulang</button>';
    }
    return '<button class="gal-anim-btn" onclick="galeriRequestReupload('+it.ikan_id+',1)" title="Minta juri upload ulang foto tank ini" style="padding:5px 10px;border-radius:8px;border:1px solid var(--bd-2);background:var(--glass-2);color:var(--text-mid);font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap;"><i class="fas fa-rotate"></i> Minta Upload Ulang</button>';
}

function galeriRequestReupload(ikanId, want){
    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('ikan_id', ikanId);
    fd.append('value', want);
    fetch('/api/admin/request-reupload', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}, body: fd})
    .then(r => r.json())
    .then(d => {
        if(!d.success) throw new Error(d.message || 'Gagal');
        popupSuccess('Berhasil', d.message);
        for(var i=0;i<_galeriItems.length;i++){ if(_galeriItems[i].ikan_id===ikanId){ _galeriItems[i].reupload_requested = !!Number(want); break; } }
        var si = document.getElementById('galeriSearchInput');
        renderGaleriFoto(si ? si.value : '');
    })
    .catch(e => popupError('Gagal', e.message || 'Gagal memproses permintaan.'));
}

function galeriDeleteFoto(fotoId){
    openConfirmPopup('Hapus Foto Ikan?', 'Foto ini akan <b>dihapus permanen</b> dan tidak bisa dikembalikan.', 'Ya, Hapus', function(){
        var fd = new FormData();
        fd.append('_token', getCsrf());
        fd.append('foto_id', fotoId);
        fetch('/api/admin/delete-foto', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}, body: fd})
        .then(r => r.json())
        .then(d => {
            if(!d.success) throw new Error(d.message || 'Gagal');
            popupSuccess('Foto Dihapus', d.message);
            loadGaleriFoto();
        })
        .catch(e => popupError('Gagal', e.message || 'Gagal menghapus foto.'));
    });
}

function updateMvpToggleUI(isOpen) {
    var btn = document.getElementById('btnToggleMvp');
    var txt = document.getElementById('mvpStatusText');
    if(btn) btn.disabled = false;
    if(isOpen) {
        btn.innerHTML = '<i class="fas fa-lock-open"></i> TUTUP MVP';
        btn.style.background = 'var(--danger)'; btn.style.boxShadow = '0 3px 10px rgba(239,68,68,.2)';
        txt.innerHTML = '<i class="fas fa-circle-check" style="color:var(--success);"></i> Pendaftaran MVP sedang <b style="color:var(--success);">DIBUKA</b>';
    } else {
        btn.innerHTML = '<i class="fas fa-lock"></i> BUKA MVP';
        btn.style.background = 'var(--success)'; btn.style.boxShadow = '0 3px 10px rgba(34,197,94,.2)';
        txt.innerHTML = '<i class="fas fa-circle-xmark" style="color:var(--danger);"></i> Pendaftaran MVP sedang <b style="color:var(--danger);">DITUTUP</b>';
    }
}

function loadMvpPeserta() {
    fetch('/api/admin/mvp-submitted-peserta', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data) {
        var tb = document.getElementById('mvpPesertaBody');
        var countEl = document.getElementById('mvpPesertaCount');
        if(!data.length) {
            tb.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--light);padding:16px;"><i class="fas fa-inbox" style="font-size:14px;display:block;margin-bottom:4px;opacity:.4;"></i>Belum ada peserta yang mengirim data MVP.</td></tr>';
            countEl.textContent = '0 peserta';
            return;
        }
        var uniqueNames = [];
        for(var u = 0; u < data.length; u++){
            if(uniqueNames.indexOf(data[u].nama_peserta) === -1) uniqueNames.push(data[u].nama_peserta);
        }
        countEl.textContent = uniqueNames.length + ' peserta';
        tb.innerHTML = '';
        for(var i = 0; i < data.length; i++) {
            (function(d, idx) {
                var safeName = esc(d.nama_peserta).replace(/'/g, "\\'");
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td style="font-weight:600;color:var(--light);font-size:11px;">' + (idx + 1) + '</td>'
                    + '<td style="font-weight:700;"><i class="fas fa-city" style="color:#D8B4FE;margin-right:6px;font-size:11px;"></i>' + esc(d.detail_anggota) + '</td>'
                    + '<td style="text-align:center;"><span class="role-badge role-grand"><i class="fas fa-users" style="margin-right:3px;font-size:9px;"></i>' + uniqueNames.length + ' Peserta</span></td>'
                    + '<td style="text-align:center;"><span style="font-weight:800;color:#f59e0b;font-size:13px;">' + d.jumlah_mvp + '</span><span style="font-size:10px;color:var(--light);"> ikan</span></td>'
                    + '<td style="text-align:center;">'
                        + '<button class="btn-xs green" onclick="unlockMvpPeserta(' + d.peserta_id + ',\'' + safeName + '\')" title="Buka kunci agar peserta bisa ubah pilihan MVP"><i class="fas fa-lock-open"></i> Buka</button>'
                    + '</td>';
                tb.appendChild(tr);
            })(data[i], i);
        }
    })
    .catch(function() {
        var tb = document.getElementById('mvpPesertaBody');
        tb.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--danger);padding:16px;">Gagal memuat data.</td></tr>';
    });
}

function unlockMvpPeserta(pesertaId, nama) {
    openConfirmPopup(
        'Buka Kunci MVP Peserta',
        'Yakin ingin membuka kembali pendaftaran MVP untuk <strong>' + esc(nama) + '</strong>?<br><div style="text-align:left;margin-top:8px;padding:10px;background:var(--bg);border-radius:8px;font-size:11px;line-height:1.6;color:var(--muted);"><i class="fas fa-circle-info" style="color:var(--primary);"></i> Peserta dapat menambah/hapus pilihan ikan MVP mereka sesuai batas yang admin atur. Setelah mereka kirim ulang, akan terkunci otomatis.</div>',
        'Ya, Buka Kunci',
        function() {
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('peserta_id', pesertaId);

            fetch('/api/admin/unlock-mvp-peserta', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: fd
            })
            .then(function(r){ return r.json(); })
            .then(function(d) {
                if(d.success) {
                    loadMvpPeserta();
                    loadMvpData();
                    popupSuccess('Berhasil Dibuka', 'Peserta <strong>' + esc(nama) + '</strong> dapat kembali mendaftarkan ikan MVP.');
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function() {
                popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
            });
        }
    );
}


// Override openModal buat MVP
var origOpenModal = openModal;
openModal = function(id) {
    origOpenModal(id);
    if(id === 'modalMvp') {
        loadMvpData();
        loadMvpStatus();
        loadMvpPeserta();
    }
}

/* ═══════════════════════════════════════════════
   RESET SEMUA RENTANG SUB-KATEGORI
   ═══════════════════════════════════════════════ */
function resetAllRanges(){
    openConfirmPopup(
        'Reset Semua Rentang',
        'Yakin ingin menghapus <b>SEMUA</b> pengaturan rentang sub-kategori?<br><span style="font-size:11px;color:var(--warning);">Rentang Global <b>tidak terpengaruh</b>. Hanya sub-rentang per kategori/kelas yang dihapus.</span>',
        'Ya, Hapus Semua',
        function(){
            var fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('ranges', JSON.stringify({}));

            fetch('/api/admin/tank-range',{
                method:'POST',
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body:fd
            })
            .then(function(r){if(!r.ok) return r.json().then(function(d){throw d;}); return r.json();})
            .then(function(d){
                if(d.success){
                    var allKeys = kelasList.concat(noKelasKategori);
                    for(var i=0; i<allKeys.length; i++){
                        kelasRangeData[allKeys[i]] = {kategori: {}};
                    }
                    populateKelasSelect();
                    renderRangeSummary();
                    document.getElementById('katKelasSelect').value = '';
                    document.getElementById('katGridWrap').style.display = 'none';
                    document.getElementById('katEmptyState').style.display = 'block';
                    hideKatError();
                    loadDashboard();
                    popupSuccess('Berhasil Direset','Semua pengaturan rentang sub-kategori telah dihapus. Ikan akan menggunakan Rentang Global.');
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(e){
                if(e.message) popupError('Gagal', esc(e.message));
                else popupError('Kesalahan Jaringan','Gagal menghubungi server.');
            });
        }
    );
}

function loadGlobalRangeDisplay(){
    fetch('/api/tank-range-global?_t='+Date.now(),{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        document.getElementById('globalRangeDisplayText').textContent=d.min+' – '+d.max;
        document.getElementById('globalRangeDesc').textContent='Rentang undian yang berlaku saat ini: '+d.min+' s/d '+d.max+' — ikan tanpa sub-rentang khusus akan diundi dari rentang ini.';
    })
    .catch(function(){
        document.getElementById('globalRangeDesc').textContent='Rentang undian: 1 – 1000';
    });
}

/* ═══════════════════════════════════════════════
   REGISTRASI: HIDE KELAS UNTUK BONSAI/JUMBO
   ═══════════════════════════════════════════════ */
var regKategoriSelect = document.querySelector('#regPesertaIkanForm select[name="kategori"]');
var regKelasWrap = regKategoriSelect ? regKategoriSelect.closest('.form-group').nextElementSibling : null;
var regKelasSelect = regKelasWrap ? regKelasWrap.querySelector('select[name="kelas"]') : null;

if(regKategoriSelect && regKelasWrap){
    regKategoriSelect.addEventListener('change', function(){
        if(noKelasKategori.indexOf(this.value) !== -1){
            if(regKelasSelect) regKelasSelect.value = '';
            regKelasWrap.style.display = 'none';
        } else {
            regKelasWrap.style.display = '';
        }
    });
}

/* ═══════════════════════════════════════════════
   IMPORT EXCEL
   ═══════════════════════════════════════════════ */
var importSelectedFile = null;

function handleImportFileSelect(input) {
    if (input.files && input.files[0]) {
        importSelectedFile = input.files[0];
        var sizeKB = (importSelectedFile.size / 1024).toFixed(1);
        document.getElementById('importFileLabel').innerHTML = '<i class="fas fa-file-excel" style="color:var(--gold-400);margin-right:4px;"></i>' + esc(importSelectedFile.name) + ' <span style="color:var(--text-low);font-size:10px;">(' + sizeKB + ' KB)</span>';
        var dz = document.getElementById('importDropZone');
        dz.style.borderColor = 'rgba(245,158,11,.45)';
        dz.style.background = 'rgba(245,158,11,.06)';
    }
}

function handleImportDrop(e) {
    var files = e.dataTransfer.files;
    if (files.length) {
        importSelectedFile = files[0];
        document.getElementById('importFileInput').files = files;
        handleImportFileSelect(document.getElementById('importFileInput'));
    }
}

function toggleImportAutoCreate() {
    var wrap = document.getElementById('importPasswordWrap');
    wrap.style.display = document.getElementById('importAutoCreate').checked ? 'block' : 'none';
}

function submitImport() {
    if (!importSelectedFile) {
        popupError('File Belum Dipilih', 'Silakan pilih file Excel terlebih dahulu.');
        return;
    }

    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('file', importSelectedFile);
    fd.append('auto_create_user', document.getElementById('importAutoCreate').checked ? '1' : '0');
    fd.append('default_password', document.getElementById('importDefaultPassword').value);

    var btn = document.getElementById('btnSubmitImport');
    var origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengimpor...';
    document.getElementById('importResultBox').style.display = 'none';

    fetch('/api/admin/import-excel', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
        body: fd
    })
    .then(function(r) {
        if (!r.ok) {
            return r.text().then(function(text) {
                try {
                    var d = JSON.parse(text);
                    throw d;
                } catch(err) {
                    // Jika bukan JSON (halaman HTML error dari Laravel), tampilkan pesan generik
                    throw { message: 'Server error (500). Cek file <b>storage/logs/laravel.log</b> untuk detail error.' };
                }
            });
        }
        return r.json();
    })
    .then(function(d) {
        if (d.success) {
            var html = '<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);border-radius:11px;padding:14px 16px;margin-bottom:10px;">';
            html += '<div style="font-size:13px;font-weight:800;color:#6EE7B7;margin-bottom:6px;"><i class="fas fa-check-circle"></i> Import Berhasil</div>';
            html += '<div style="font-size:12px;color:var(--text);line-height:1.6;">' + d.message + '</div>';
            html += '</div>';

            if (d.errors && d.errors.length > 0) {
                html += '<div style="background:rgba(245,158,11,.08);border:1px solid var(--bd-gold);border-radius:11px;padding:14px 16px;max-height:200px;overflow-y:auto;">';
                html += '<div style="font-size:11px;font-weight:800;color:var(--gold-300);margin-bottom:6px;"><i class="fas fa-triangle-exclamation"></i> Baris Dilewati (' + d.errors.length + ')</div>';
                html += '<div style="font-size:11px;color:var(--text-mid);line-height:1.7;">';
                var showMax = Math.min(d.errors.length, 30);
                for (var i = 0; i < showMax; i++) {
                    html += '<div>• ' + esc(d.errors[i]) + '</div>';
                }
                if (d.errors.length > 30) {
                    html += '<div style="color:var(--text-low);font-style:italic;">... dan ' + (d.errors.length - 30) + ' lainnya</div>';
                }
                html += '</div></div>';
            }

            document.getElementById('importResultBox').innerHTML = html;
            document.getElementById('importResultBox').style.display = 'block';
            loadUsers();
            loadDashboard();
            loadScoringData();
        } else {
            popupError('Import Gagal', d.message || 'Terjadi kesalahan.');
        }
    })
    .catch(function(e) {
        if (e && e.message) popupError('Import Gagal', esc(e.message));
        else popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    });
}

function _exportToast(msg, spinning){
    var t=document.getElementById('exportToast');
    if(!t){
        t=document.createElement('div');
        t.id='exportToast';
        t.style.cssText='position:fixed;right:20px;bottom:20px;z-index:99999;background:#0b1220;color:#e2e8f0;border:1px solid rgba(34,211,238,.35);border-radius:12px;padding:14px 18px;font-size:13px;box-shadow:0 10px 30px rgba(0,0,0,.45);display:flex;align-items:center;gap:10px;max-width:340px;';
        document.body.appendChild(t);
    }
    t.innerHTML=(spinning?'<i class="fas fa-spinner fa-spin" style="color:#22D3EE;"></i>':'<i class="fas fa-circle-info" style="color:#34D399;"></i>')+'<span>'+msg+'</span>';
    t.style.display='flex';
}
function _exportToastHide(delay){
    var t=document.getElementById('exportToast');
    if(t) setTimeout(function(){ if(t) t.style.display='none'; }, delay||0);
}

function pollExportToast(token, tries){
    fetch('/api/admin/export-status/'+token,{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.status==='ready'){ _exportToast('File siap, mengunduh...', false); _exportToastHide(3000); window.location.href='/api/admin/export-download/'+token; return; }
        if(d.status==='failed'){ _exportToast('Export gagal: '+(d.message||'kesalahan server'), false); _exportToastHide(8000); return; }
        var elapsed=tries*2;
        if(d.status==='queued'){
            if(elapsed>=20){ _exportToast('Tugas masih MENGANTRI — worker (cron queue:work) belum berjalan di server.', false); _exportToastHide(10000); return; }
            _exportToast('Menunggu worker memproses... ('+elapsed+' dtk)', true);
        } else {
            _exportToast('Membuat file export... ('+elapsed+' dtk)', true);
        }
        if(tries>450){ _exportToast('Export terlalu lama. Cek log/worker server.', false); _exportToastHide(10000); return; }
        setTimeout(function(){ pollExportToast(token, tries+1); }, 2000);
    })
    .catch(function(){ setTimeout(function(){ pollExportToast(token, tries+1); }, 3000); });
}

/* ═══════════════════════════════════════════════
   POINT RANKING (SAMA SEPERTI GRAND JURI)
   ═══════════════════════════════════════════════ */
var adminPointScope = 'per_kategori_kelas';

function setAdminPointScope(s){
    adminPointScope = s;
    var btnKelas = document.getElementById('admBtnScopeKelas');
    var btnKat = document.getElementById('admBtnScopeKat');
    var btnGlobal = document.getElementById('admBtnScopeGlobal');
    [btnKelas, btnKat, btnGlobal].forEach(function(b){
        b.style.background = 'var(--warning-lt)';
        b.style.color = 'var(--gold-300)';
        b.style.border = '1px solid rgba(245,158,11,.25)';
    });
    var activeBtn = s === 'per_kategori_kelas' ? btnKelas : s === 'per_kategori' ? btnKat : btnGlobal;
    activeBtn.style.background = 'linear-gradient(135deg,var(--royal-600),var(--cyan-500))';
    activeBtn.style.color = '#fff';
    activeBtn.style.border = 'none';
    document.getElementById('admPointFilterKategori').style.display = s === 'global' ? 'none' : '';
    document.getElementById('admPointFilterKelas').style.display = s === 'global' ? 'none' : '';
    loadAdminPointRanking();
}

/* ★ EDIT NILAI dari Point Ranking — pakai modal & endpoint yang SAMA dgn Data Penilaian Keseluruhan */
function openEditAdminFromRanking(ikanId){
    function findIdx(){
        for(var i=0;i<allScoringData.length;i++){
            if(parseInt(allScoringData[i].id,10)===parseInt(ikanId,10)) return i;
        }
        return -1;
    }
    var idx=findIdx();
    if(idx!==-1){ openEditAdmin(idx); return; }
    loadScoringData(function(){
        var j=findIdx();
        if(j!==-1){ openEditAdmin(j); }
        else { popupError('Data Tidak Ditemukan','Data penilaian untuk ikan ini belum tersedia. Buka menu "Data Penilaian Keseluruhan" lalu ulangi.'); }
    });
}

function loadAdminPointRanking(){
    var kat = document.getElementById('admPointFilterKategori').value;
    var kelas = document.getElementById('admPointFilterKelas').value;
    var params = '?scope=' + adminPointScope;
    if(kat) params += '&kategori=' + encodeURIComponent(kat);
    if(kelas) params += '&kelas=' + encodeURIComponent(kelas);
    if(adminPointScope === 'global') params += '&limit=10';

    var el = document.getElementById('adminPointRankingContent');
    el.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat ranking...</p></div>';

    fetch('/api/admin/point-ranking' + params, {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(groups){
        if(!groups || !groups.length){
            el.innerHTML = '<div class="empty-state"><i class="fas fa-trophy" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Belum ada data ikan yang dikunci.</div>';
            return;
        }
        var isGlobal = (adminPointScope === 'global');
        var html = '';
        groups.forEach(function(g){
            html += '<div style="margin-bottom:20px;">';
            html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:linear-gradient(135deg,rgba(245,158,11,.10),rgba(245,158,11,.04));border:1px solid rgba(245,158,11,.25);border-radius:10px 10px 0 0;">';
            html += '<div style="font-size:13px;font-weight:800;color:var(--text-hi);display:flex;align-items:center;gap:9px;"><i class="fas '+(isGlobal?'fa-globe':'fa-layer-group')+'" style="color:var(--gold-500);"></i> '+esc(g.group_name)+'</div>';
            html += '<span style="font-size:11px;color:var(--gold-300);font-weight:700;">'+g.total+' peserta</span></div>';
            html += '<div style="overflow-x:auto;border-radius:0 0 10px 10px;border:1px solid rgba(245,158,11,.20);border-top:none;"><table class="data-table" style="min-width:800px;">';
            html += '<thead><tr><th style="width:40px;text-align:center;">#</th><th>PESERTA</th>'+(isGlobal?'<th>KATEGORI</th>':'')+'<th style="width:70px;">TANK</th><th style="width:50px;">KELAS</th><th>ASAL/TEAM</th><th style="width:90px;text-align:center;">TOTAL NILAI</th><th style="width:80px;text-align:center;">POINT</th><th style="width:100px;text-align:center;">RANK POINT</th><th style="width:130px;text-align:center;">AKSI</th></tr></thead><tbody>';
            g.data.forEach(function(d,i){
                var rankPt = d.rank_point ?? 0;
                var frp = d.final_rank_point ?? rankPt;
                var basePt = d.total_point ?? 0;
                var bonus = d.total_bonus || 0;
                var posisi = i + 1;
                var rankBg, rankColor, rankBorder;
                if(posisi === 1){ rankBg='rgba(16,185,129,.14)'; rankColor='#34D399'; rankBorder='rgba(16,185,129,.35)'; }
                else if(posisi <= 3){ rankBg='var(--warning-lt)'; rankColor='var(--gold-300)'; rankBorder='rgba(245,158,11,.30)'; }
                else if(posisi <= 6){ rankBg='rgba(34,211,238,.08)'; rankColor='var(--cyan-300)'; rankBorder='rgba(34,211,238,.25)'; }
                else if(posisi <= 10){ rankBg='var(--purple-lt)'; rankColor='var(--purple)'; rankBorder='rgba(168,85,247,.25)'; }
                else{ rankBg='var(--glass-2)'; rankColor='var(--text-faint)'; rankBorder='var(--bd-2)'; }
                var rowStyle = d.is_locked ? '' : 'opacity:.6;';
                var posisiHtml;
                if(!d.is_locked){
                    posisiHtml = '<i class="fas fa-lock-open" style="color:var(--gold-300);font-size:11px;" title="Belum dikunci"></i>';
                } else if(posisi === 1){
                    posisiHtml = '<span style="display:inline-flex;align-items:center;gap:3px;font-weight:900;color:#FBBF24;font-size:12px;"><i class="fas fa-medal" style="font-size:11px;"></i> 1</span>';
                } else if(posisi === 2){
                    posisiHtml = '<span style="display:inline-flex;align-items:center;gap:3px;font-weight:900;color:#C0C0C0;font-size:12px;"><i class="fas fa-medal" style="font-size:11px;"></i> 2</span>';
                } else if(posisi === 3){
                    posisiHtml = '<span style="display:inline-flex;align-items:center;gap:3px;font-weight:900;color:#CD7F32;font-size:12px;"><i class="fas fa-medal" style="font-size:11px;"></i> 3</span>';
                } else {
                    posisiHtml = '<span style="font-weight:800;color:var(--text-muted);font-size:12px;">'+posisi+'</span>';
                }
                html += '<tr style="'+rowStyle+'">';
                html += '<td style="text-align:center;font-weight:800;color:var(--text-muted);">'+formatRankCellAdmin(posisi)+'</td>';
                html += '<td style="font-weight:800;color:var(--text-hi);">'+esc(d.nama_peserta || '-')+'</td>';

                if(isGlobal){
                    html += '<td style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">'+esc(d.kategori || '-')+'</td>';
                }

                html += '<td style="font-weight:700;color:var(--primary);text-align:center;">'+(d.nomor_tank || '—')+'</td>';
                html += '<td style="text-align:center;">'+esc(d.kelas)+'</td>';
                html += '<td style="font-size:11px;color:var(--text-muted);">'+esc(d.detail_anggota)+'</td>';
                html += '<td style="text-align:center;"><div style="font-weight:800;">'+(d.total_nilai_semua ?? 0)+'</div>';
                if(d.jumlah_juri > 0) html += '<div style="font-size:9px;color:var(--text-muted);font-weight:600;">'+d.jumlah_juri+' juri</div>';
                html += '</td>';
                html += '<td style="text-align:center;"><div style="font-weight:900;font-size:15px;color:var(--primary);">'+basePt+'</div></td>';
                html += '<td style="text-align:center;"><span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:8px;font-size:14px;font-weight:900;background:'+rankBg+';color:'+rankColor+';border:1px solid '+rankBorder+';">'+frp+'</span>';
                if(bonus > 0) html += '<div style="font-size:9px;color:#34D399;font-weight:800;margin-top:3px;"><i class="fas fa-trophy" style="font-size:7px;"></i> '+rankPt+' + '+bonus+'</div>';
                html += '</td>';
                // ★ Tombol Kunci / Buka Kunci di Point Ranking — versi cerah & menonjol
                // GANTI BLOK TOMBOL KUNCI/BUKA DENGAN INI:
                if(d.is_locked){
                    html += '<td style="text-align:center;"><div style="margin-bottom:4px;"><span style="font-size:9px;font-weight:800;color:#FCA5A5;display:inline-flex;align-items:center;gap:3px;"><i class="fas fa-lock" style="font-size:8px;"></i> Terkunci</span></div><div style="display:inline-flex;gap:4px;justify-content:center;"><button class="btn-xs" style="background:rgba(239,68,68,.15);color:#FCA5A5;border:1px solid rgba(239,68,68,.3);font-weight:800;padding:5px 10px;transition:all .2s;" onmouseover="this.style.background=\'var(--danger)\';this.style.color=\'#fff\'" onmouseout="this.style.background=\'rgba(239,68,68,.15)\';this.style.color=\'#FCA5A5\'" onclick="kunciNilaiAdmin('+d.ikan_id+')" title="Buka kunci nilai"><i class="fas fa-lock-open"></i> Buka</button><button class="btn-xs purple" style="opacity:.35;cursor:not-allowed;padding:5px 9px;" disabled title="Nilai terkunci — buka kunci dulu untuk mengedit"><i class="fas fa-pen-to-square"></i></button></div></td>';
                } else {
                    html += '<td style="text-align:center;"><div style="margin-bottom:4px;"><span style="font-size:9px;font-weight:800;color:var(--gold-300);display:inline-flex;align-items:center;gap:3px;"><i class="fas fa-lock-open" style="font-size:8px;"></i> Terbuka</span></div><div style="display:inline-flex;gap:4px;justify-content:center;"><button class="btn-xs" style="background:rgba(245,158,11,.15);color:var(--gold-300);border:1px solid rgba(245,158,11,.3);font-weight:800;padding:5px 10px;transition:all .2s;" onmouseover="this.style.background=\'var(--gold-500)\';this.style.color=\'#fff\'" onmouseout="this.style.background=\'rgba(245,158,11,.15)\';this.style.color=\'var(--gold-300)\'" onclick="kunciNilaiAdmin('+d.ikan_id+')" title="Kunci nilai (final)"><i class="fas fa-lock"></i> Kunci</button><button class="btn-xs purple" style="padding:5px 9px;" onclick="openEditAdminFromRanking('+d.ikan_id+')" title="Edit Nilai"><i class="fas fa-pen-to-square"></i></button></div></td>';
                }
                html += '</tr>';
            });
            html += '</tbody></table></div></div>';
        });
        el.innerHTML = html;
        updateKunciHeaderBadge(groups);
    })
    .catch(function(){
        el.innerHTML = '<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation" style="font-size:28px;display:block;margin-bottom:8px;"></i>Gagal memuat data ranking.</div>';
    });
}

function formatRankCellAdmin(posisi){
    posisi = parseInt(posisi || 0, 10);

    if(posisi === 1){
        return '<i class="fas fa-medal" style="color:var(--gold-500);font-size:12px;margin-right:4px;" title="Juara 1"></i>1';
    }
    if(posisi === 2){
        return '<i class="fas fa-medal" style="color:#C0C0C0;font-size:12px;margin-right:4px;" title="Juara 2"></i>2';
    }
    if(posisi === 3){
        return '<i class="fas fa-medal" style="color:#CD7F32;font-size:12px;margin-right:4px;" title="Juara 3"></i>3';
    }

    return String(posisi > 0 ? posisi : '-');
}

/* ═══════════════════════════════════════════════
   UPDATE KUNCI HEADER BADGE
   ═══════════════════════════════════════════════ */
function updateKunciHeaderBadge(groups){
    var totalLocked=0, totalUnlocked=0;
    if(groups && groups.length){
        groups.forEach(function(g){
            if(g.data && g.data.length){
                g.data.forEach(function(d){
                    if(d.is_locked) totalLocked++; else totalUnlocked++;
                });
            }
        });
    }
    // Jika semua terkunci -> state LOCKED, jika ada yang terbuka -> state UNLOCKED
    var isLocked = (totalUnlocked === 0 && totalLocked > 0);
    updateKunciUI(isLocked, totalLocked, totalUnlocked);
}

/* ═══ UPDATE UI KUNCI (Mirip MVP Toggle) ═══ */
function updateKunciUI(isLocked, totalLocked, totalUnlocked){
    var btn = document.getElementById('btnKunciSemua');
    var badge = document.getElementById('kunciStatusBadge');
    if(!btn) return;

    if(isLocked){
        // STATE: TERKUNCI -> Tombol merah untuk BUKA
        btn.innerHTML = '<i class="fas fa-lock-open"></i> BUKA SEMUA KUNCI';
        btn.style.background = 'var(--danger)';
        btn.style.boxShadow = '0 3px 10px rgba(239,68,68,.2)';

        if(badge){
            badge.innerHTML = '<i class="fas fa-circle-check" style="color:var(--success);"></i> Status: <b style="color:var(--success);">TERKUNCI</b> ('+totalLocked+' peserta final)';
            badge.style.display = 'inline-flex';
            badge.style.background = 'rgba(16,185,129,.08)';
            badge.style.color = '#6EE7B7';
            badge.style.borderColor = 'rgba(16,185,129,.3)';
        }
    } else {
        // STATE: TERBUKA -> Tombol hijau untuk KUNCI
        btn.innerHTML = '<i class="fas fa-lock"></i> KUNCI SEMUA PESERTA';
        btn.style.background = 'var(--success)';
        btn.style.boxShadow = '0 3px 10px rgba(34,197,94,.2)';

        if(badge){
            var info = '';
            if(totalUnlocked > 0 && totalLocked > 0){
                info = totalUnlocked+' terbuka, '+totalLocked+' terkunci';
            } else if(totalUnlocked > 0){
                info = totalUnlocked+' peserta belum dikunci';
            } else {
                info = 'Belum ada peserta';
            }
            badge.innerHTML = '<i class="fas fa-circle-xmark" style="color:var(--danger);"></i> Status: <b style="color:var(--danger);">TERBUKA</b> ('+info+')';
            badge.style.display = 'inline-flex';
            badge.style.background = 'rgba(239,68,68,.08)';
            badge.style.color = '#FCA5A5';
            badge.style.borderColor = 'rgba(239,68,68,.3)';
        }
    }
}

/* ═══════════════════════════════════════════════
   MODUL KIRIM HASIL JUARA
   ═══════════════════════════════════════════════ */
function loadResultsStatus(){
    var tb = document.getElementById('resultsUserBody');
    if(!tb) return;

    tb.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data peserta...</p></div></td></tr>';

    fetch('/api/admin/results-status?_t=' + Date.now(), {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(r){
        if(!r.ok){
            return r.json().then(function(d){ throw d; });
        }
        return r.json();
    })
    .then(function(d){
        var badge = document.getElementById('resultsStatusBadge');

        if(badge){
            var totalPublished = parseInt(d.total_published || 0, 10);
            var totalPeserta = parseInt(d.total_peserta || 0, 10);

            if(totalPublished > 0){
                badge.innerHTML = '<i class="fas fa-check-circle" style="font-size:8px;"></i> ' + totalPublished + '/' + totalPeserta + ' peserta sudah menerima hasil';
                badge.style.background = 'rgba(16,185,129,.12)';
                badge.style.color = '#6EE7B7';
                badge.style.borderColor = 'rgba(16,185,129,.3)';
            } else {
                badge.innerHTML = '<i class="fas fa-circle-xmark" style="font-size:8px;"></i> Belum ada peserta yang menerima hasil';
                badge.style.background = 'var(--glass-3)';
                badge.style.color = 'var(--text-mid)';
                badge.style.borderColor = 'var(--bd-2)';
            }
        }

        if(!d.users || d.users.length === 0){
            tb.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-users-slash"></i><p>Tidak ada peserta.</p></div></td></tr>';
            return;
        }

        var html = '';

        d.users.forEach(function(u, idx){
            var finalCount = parseInt(
                u.final_ikan_count !== undefined && u.final_ikan_count !== null
                    ? u.final_ikan_count
                    : (u.locked_ikan_count || 0),
                10
            );

            var hasPeserta = !!u.has_peserta;
            var hasFinal = hasPeserta && finalCount > 0;
            var isUnlocked = !!u.result_unlocked;

            var statusHtml = '';
            var actionHtml = '';

            if(!hasPeserta){
                statusHtml = '<span style="font-size:10px;font-weight:700;color:var(--text-low);display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-user-slash" style="font-size:9px;"></i> Belum ada profil peserta</span>';
                actionHtml = '<span style="font-size:10px;color:var(--text-faint);">—</span>';
            } else if(!hasFinal){
                statusHtml = '<span style="font-size:10px;font-weight:700;color:var(--text-low);display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-minus-circle" style="font-size:9px;"></i> Tidak ada ikan final/terkunci</span>';
                actionHtml = '<span style="font-size:10px;color:var(--text-faint);">—</span>';
            } else if(isUnlocked){
                statusHtml = '<span style="font-size:10px;font-weight:800;color:#6EE7B7;display:inline-flex;align-items:center;gap:4px;background:rgba(16,185,129,.12);padding:4px 10px;border-radius:6px;border:1px solid rgba(16,185,129,.25);"><i class="fas fa-check-circle" style="font-size:9px;"></i> Sudah Dikirim</span>';
                actionHtml = '<button class="btn-xs red" onclick="unpublishResultSingleUser('+u.id+','+JSON.stringify(u.display_name || u.name)+')" style="padding:5px 10px;font-size:9px;"><i class="fas fa-ban"></i> Cabut</button>';
            } else {
                statusHtml = '<span style="font-size:10px;font-weight:800;color:var(--gold-300);display:inline-flex;align-items:center;gap:4px;background:rgba(245,158,11,.10);padding:4px 10px;border-radius:6px;border:1px solid rgba(245,158,11,.20);"><i class="fas fa-clock" style="font-size:9px;"></i> Belum Dikirim</span>';
                actionHtml = '<button class="btn-xs green" onclick="publishResultSingleUser('+u.id+','+JSON.stringify(u.display_name || u.name)+')" style="padding:5px 10px;font-size:9px;"><i class="fas fa-paper-plane"></i> Kirim</button>';
            }

            var nameCell = esc(u.display_name || u.name || '-');

            if(u.jenis_keanggotaan === 'team'){
                nameCell += ' <span style="font-size:9px;font-weight:700;color:var(--gold-300);background:rgba(245,158,11,.10);padding:2px 6px;border-radius:4px;border:1px solid rgba(245,158,11,.20);margin-left:4px;vertical-align:middle;">TEAM</span>';
            }

            html += '<tr>';
            html += '<td style="font-weight:700;color:var(--text-low);font-size:11px;">'+(idx+1)+'</td>';
            html += '<td style="font-weight:700;">'+nameCell+'</td>';
            html += '<td style="font-size:11px;color:var(--text-mid);">'+esc(u.email || '-')+'</td>';
            html += '<td style="text-align:center;font-weight:800;color:'+(finalCount > 0 ? 'var(--cyan-300)' : 'var(--text-faint)')+';">'+finalCount+'</td>';
            html += '<td style="text-align:center;">'+statusHtml+'</td>';
            html += '<td style="text-align:center;">'+actionHtml+'</td>';
            html += '</tr>';
        });

        tb.innerHTML = html;
    })
    .catch(function(e){
        var msg = e && e.message ? e.message : 'Gagal memuat data.';
        tb.innerHTML = '<tr><td colspan="6"><div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>'+esc(msg)+'</p></div></td></tr>';
    });
}

function unpublishResultSingleUser(userId, userName){
    openConfirmPopup(
        'Cabut Akses Hasil Juara?',
        'Cabut akses hasil juara dari <strong>'+esc(userName)+'</strong>?<br><span style="font-size:11px;color:var(--danger);">Peserta tidak akan bisa lagi melihat hasil juara mereka.</span>',
        'Ya, Cabut',
        function(){
            fetch('/api/admin/unpublish-result-user', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({user_id: userId})
            })
            .then(function(r){
                if(!r.ok){
                    return r.json().then(function(d){ throw d; });
                }
                return r.json();
            })
            .then(function(d){
                if(d.success){
                    popupSuccess('Berhasil', d.message || 'Akses berhasil dicabut.');
                    loadResultsStatus();
                } else {
                    popupError('Gagal', d.message || 'Gagal mencabut akses.');
                }
            })
            .catch(function(e){
                popupError('Error', e && e.message ? e.message : 'Gagal menghubungi server.');
            });
        }
    );
}

function publishResultsAll(){
    openConfirmPopup(
        'Publikasikan Hasil ke Semua Peserta?',
        'Tindakan ini akan membuka akses hasil juara untuk semua peserta yang sudah memiliki ikan final/terkunci.<br><span style="font-size:11px;color:var(--warning);">Pastikan semua nilai sudah dikunci dan benar.</span>',
        'Ya, Publikasikan',
        function(){
            fetch('/api/admin/publish-results-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(r){
                if(!r.ok){
                    return r.json().then(function(d){ throw d; });
                }
                return r.json();
            })
            .then(function(d){
                if(d.success){
                    popupSuccess('Berhasil', d.message || 'Hasil berhasil dikirim.');
                    loadResultsStatus();
                } else {
                    popupError('Gagal', d.message || 'Gagal mengirim hasil.');
                }
            })
            .catch(function(e){
                popupError('Error', e && e.message ? e.message : 'Gagal menghubungi server.');
            });
        }
    );
}

function unpublishResultsAll(){
    openConfirmPopup(
        'Cabut Akses Hasil Juara?',
        'Peserta tidak akan bisa lagi melihat hasil juara mereka.',
        'Ya, Cabut',
        function(){
            fetch('/api/admin/unpublish-results-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(r){
                if(!r.ok){
                    return r.json().then(function(d){ throw d; });
                }
                return r.json();
            })
            .then(function(d){
                if(d.success){
                    popupSuccess('Berhasil', d.message || 'Akses semua peserta berhasil dicabut.');
                    loadResultsStatus();
                } else {
                    popupError('Gagal', d.message || 'Gagal mencabut akses.');
                }
            })
            .catch(function(e){
                popupError('Error', e && e.message ? e.message : 'Gagal menghubungi server.');
            });
        }
    );
}

function publishResultSingleUser(userId, userName){
    openConfirmPopup(
        'Kirim Hasil ke Peserta?',
        'Kirim hasil juara ke <strong>'+esc(userName)+'</strong>?',
        'Ya, Kirim',
        function(){
            fetch('/api/admin/publish-result-user', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({user_id: userId})
            })
            .then(function(r){
                if(!r.ok){
                    return r.json().then(function(d){ throw d; });
                }
                return r.json();
            })
            .then(function(d){
                if(d.success){
                    popupSuccess('Berhasil', d.message || 'Hasil berhasil dikirim.');
                    loadResultsStatus();
                } else {
                    popupError('Gagal', d.message || 'Gagal mengirim hasil.');
                }
            })
            .catch(function(e){
                popupError('Error', e && e.message ? e.message : 'Gagal menghubungi server.');
            });
        }
    );
}

/* ═══════════════════════════════════════════════
   MODUL PIAGAM HASIL JUARA
   ═══════════════════════════════════════════════ */
var piagamRecipientsData = [];
var piagamUploadTarget = null;

function loadPiagamRecipients(){
    var tb = document.getElementById('piagamRecipientBody');
    if(!tb) return;

    // ★ Cegah autofill browser (mis. email tersimpan) mengisi kolom cari lalu memfilter semua penerima.
    var _ps = document.getElementById('piagamSearch');
    if(_ps && _ps.value){ _ps.value = ''; }

    tb.innerHTML = '<tr><td colspan="4"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat penerima...</p></div></td></tr>';

    fetch('/api/admin/piagam-recipients?_t=' + Date.now(), {
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
    })
    .then(function(r){ if(!r.ok){ return r.json().then(function(d){ throw d; }); } return r.json(); })
    .then(function(d){
        piagamRecipientsData = Array.isArray(d.recipients) ? d.recipients : [];
        renderPiagamRecipients();
    })
    .catch(function(e){
        var msg = e && e.message ? e.message : 'Gagal memuat data.';
        tb.innerHTML = '<tr><td colspan="4"><div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>'+esc(msg)+'</p></div></td></tr>';
    });
}

function renderPiagamRecipients(){
    var tb = document.getElementById('piagamRecipientBody');
    if(!tb) return;

    var q = ((document.getElementById('piagamSearch') || {}).value || '').toLowerCase().trim();
    var fKat = (document.getElementById('piagamFilterKat') || {}).value || '';
    var fJenis = (document.getElementById('piagamFilterJenis') || {}).value || '';

    var rows = piagamRecipientsData.filter(function(r){
        if(fJenis && (r.jenis_keanggotaan || 'perorangan') !== fJenis) return false;
        if(fKat){
            var kats = Array.isArray(r.kategoris) ? r.kategoris : [];
            if(kats.indexOf(fKat) === -1) return false;
        }
        if(q){
            var hay = ((r.name || '') + ' ' + (r.email || '')).toLowerCase();
            if(hay.indexOf(q) === -1) return false;
        }
        return true;
    });

    if(rows.length === 0){
        tb.innerHTML = '<tr><td colspan="4"><div class="empty-state"><i class="fas fa-users-slash"></i><p>Tidak ada penerima pada filter ini.</p></div></td></tr>';
        return;
    }

    var html = '';
    rows.forEach(function(r, idx){
        var nameCell = esc(r.name || '-');
        if((r.jenis_keanggotaan || '') === 'team'){
            nameCell += ' <span style="font-size:9px;font-weight:700;color:var(--gold-300);background:rgba(245,158,11,.10);padding:2px 6px;border-radius:4px;border:1px solid rgba(245,158,11,.20);margin-left:4px;vertical-align:middle;">TEAM</span>';
        }
        var emailLine = '<div style="font-size:10px;color:var(--text-low);margin-top:2px;">'+esc(r.email || '-')+'</div>';

        var count = parseInt(r.piagam_count || 0, 10);
        var countBadge = count > 0
            ? '<span style="font-size:10px;font-weight:800;color:#6EE7B7;background:rgba(16,185,129,.12);padding:4px 10px;border-radius:6px;border:1px solid rgba(16,185,129,.25);">'+count+'</span>'
            : '<span style="font-size:10px;color:var(--text-faint);">0</span>';

        var chips = '';
        if(Array.isArray(r.piagams) && r.piagams.length){
            chips = '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">';
            r.piagams.forEach(function(pg){
                var label = pg.judul || pg.original_name || ('Piagam #'+pg.id);
                chips += '<span style="display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:700;color:var(--text-mid);background:var(--glass-3);border:1px solid var(--bd-2);border-radius:999px;padding:4px 8px 4px 10px;">'
                    + '<a href="'+pg.url+'" target="_blank" rel="noopener" style="color:var(--cyan-300);text-decoration:none;"><i class="fas fa-eye" style="font-size:9px;"></i> '+esc(label)+'</a>'
                    + '<button onclick="deletePiagam('+pg.id+')" title="Hapus" style="border:none;background:transparent;color:var(--danger);cursor:pointer;padding:0;font-size:11px;line-height:1;"><i class="fas fa-times-circle"></i></button>'
                    + '</span>';
            });
            chips += '</div>';
        }

        html += '<tr>';
        html += '<td style="font-weight:700;color:var(--text-low);font-size:11px;vertical-align:top;padding-top:12px;">'+(idx+1)+'</td>';
        html += '<td style="font-weight:700;">'+nameCell+emailLine+chips+'</td>';
        html += '<td style="text-align:center;vertical-align:top;padding-top:12px;">'+countBadge+'</td>';
        html += '<td style="text-align:center;vertical-align:top;padding-top:10px;"><button class="btn-xs green" onclick="openPiagamUpload('+r.peserta_id+')" style="padding:6px 12px;font-size:10px;"><i class="fas fa-upload"></i> Kirim Piagam</button></td>';
        html += '</tr>';
    });

    tb.innerHTML = html;
}

function openPiagamUpload(pesertaId){
    piagamUploadTarget = pesertaId;
    var inp = document.getElementById('piagamFileInput');
    if(inp){ inp.value = ''; inp.click(); }
}

function onPiagamFileChosen(input){
    if(!input || !input.files || !input.files.length) return;
    if(!piagamUploadTarget){ popupError('Gagal', 'Penerima tidak dikenali. Klik tombol "Kirim Piagam" lagi.'); return; }

    var files = input.files;
    var fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('peserta_id', piagamUploadTarget);
    for(var i=0;i<files.length;i++){ fd.append('files[]', files[i]); }

    _exportToast('Mengunggah piagam...', true);

    fetch('/api/admin/upload-piagam', {
        method:'POST',
        headers:{'X-CSRF-TOKEN':getCsrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: fd
    })
    .then(function(r){ if(!r.ok){ return r.json().then(function(d){ throw d; }); } return r.json(); })
    .then(function(d){
        _exportToastHide(200);
        if(d.success){
            popupSuccess('Berhasil', d.message || 'Piagam berhasil dikirim.');
            loadPiagamRecipients();
        } else {
            popupError('Gagal', d.message || 'Gagal mengunggah piagam.');
        }
    })
    .catch(function(e){
        _exportToastHide(200);
        var msg = (e && e.message) ? e.message : 'Gagal mengunggah. Pastikan file jpg/png/webp/pdf & maks 5MB.';
        popupError('Error', msg);
    });
}

function deletePiagam(id){
    openConfirmPopup(
        'Hapus Piagam?',
        'Piagam ini akan dihapus permanen dan tidak lagi bisa diunduh oleh penerima.',
        'Ya, Hapus',
        function(){
            fetch('/api/admin/delete-piagam', {
                method:'POST',
                headers:{'X-CSRF-TOKEN':getCsrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({id:id})
            })
            .then(function(r){ if(!r.ok){ return r.json().then(function(d){ throw d; }); } return r.json(); })
            .then(function(d){
                if(d.success){ popupSuccess('Berhasil', d.message || 'Piagam dihapus.'); loadPiagamRecipients(); }
                else { popupError('Gagal', d.message || 'Gagal menghapus piagam.'); }
            })
            .catch(function(e){ popupError('Error', e && e.message ? e.message : 'Gagal menghubungi server.'); });
        }
    );
}

/* ═══════════════════════════════════════════════
   INIT
   ═══════════════════════════════════════════════ */
loadGlobalRangeDisplay();
loadDashboard();
loadScoringData();
loadUsers();
loadJuriScoringLockStatus();

function ensureJaStyle(){
    if(document.getElementById('ja-checklist-style')) return;
    var st=document.createElement('style');
    st.id='ja-checklist-style';
    st.textContent=''
      +'.ja-kat-row{border:1px solid var(--bd-1);border-radius:12px;padding:12px 14px;margin-bottom:10px;background:var(--glass-1);}'
      +'.ja-kat-head{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--text-hi);font-weight:700;cursor:pointer;user-select:none;}'
      +'.ja-kat-head input{width:16px;height:16px;accent-color:var(--cyan-500);cursor:pointer;}'
      +'.ja-kat-head .muted{font-size:10.5px;color:var(--text-low);font-weight:600;}'
      +'.ja-kelas-wrap{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}'
      +'.ja-kelas-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border:1px solid var(--bd-2);border-radius:9px;background:var(--glass-2);font-size:12.5px;color:var(--text-mid);cursor:pointer;transition:all .15s;user-select:none;}'
      +'.ja-kelas-pill:hover{border-color:var(--bd-cyan);color:var(--text-hi);}'
      +'.ja-kelas-pill input{width:15px;height:15px;accent-color:var(--cyan-500);cursor:pointer;}'
      +'.ja-kelas-pill:has(input:checked){background:rgba(34,211,238,.12);border-color:var(--bd-cyan);color:var(--cyan-300);font-weight:700;}'
      +'.ja-nokelas{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border:1px solid var(--bd-2);border-radius:10px;background:var(--glass-2);font-size:13px;color:var(--text);cursor:pointer;font-weight:700;user-select:none;}'
      +'.ja-nokelas input{width:16px;height:16px;accent-color:var(--cyan-500);cursor:pointer;}'
      +'.ja-nokelas:has(input:checked){background:rgba(34,211,238,.12);border-color:var(--bd-cyan);color:var(--cyan-300);}';
    document.head.appendChild(st);
}

/* ═══════════════════════════════════════════════
   KELOLA JURI — PENUGASAN KATEGORI & KELAS
   ═══════════════════════════════════════════════ */
function loadJuriAssignments(){
    ensureJaStyle();
    var c=document.getElementById('kelolaJuriList');
    if(!c) return;
    c.innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>';
    fetch('/api/admin/juri-assignments',{headers:{'Accept':'application/json'}})
    .then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(function(d){
        var juris=(d&&d.juris)||[];
        if(!juris.length){ c.innerHTML='<div class="empty-state"><i class="fas fa-user-slash"></i><p>Belum ada user dengan role Juri.</p></div>'; return; }
        c.innerHTML=juris.map(renderJuriAssignmentCard).join('');
        // Set indikator header "semua kelas" (penuh / strip sebagian) sesuai data tersimpan.
        c.querySelectorAll('.ja-all').forEach(function(allCb){
            var card=allCb.closest('[id^="ja-card-"]');
            if(card) refreshKategoriState(card, allCb.dataset.kat);
        });
    })
    .catch(function(err){
        c.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">'+esc(err.message)+'</p></div>';
    });
}

function renderJuriAssignmentCard(j){
    var has={};
    (j.assignments||[]).forEach(function(a){
        var kelas=(a.kelas===null||a.kelas===undefined)?'':String(a.kelas);
        has[a.kategori+'|'+kelas]=true;
    });
    var jid=j.id;
    var count=(j.assignments||[]).length;
    var badge=count>0
        ? '<span style="font-size:9.5px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(16,185,129,.15);color:#6EE7B7;border:1px solid rgba(16,185,129,.3);">'+count+' PENUGASAN</span>'
        : '<span style="font-size:9.5px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(239,68,68,.12);color:#FCA5A5;border:1px solid rgba(239,68,68,.3);">BELUM DITUGASKAN</span>';

    var rows='';
    var noKelasChips='';
    allKategoriList.forEach(function(kat){
        var isNoKelas=noKelasKategori.indexOf(kat)!==-1;
        if(isNoKelas){
            var ck=has[kat+'|']?'checked':'';
            noKelasChips+='<label class="ja-nokelas"><input type="checkbox" class="ja-cb" data-jid="'+jid+'" data-kat="'+kat+'" data-kelas="" '+ck+'> '+kat+'</label>';
        }else{
            var ckAll=has[kat+'|']?'checked':'';
            var pills='';
            kelasList.forEach(function(kl){
                var ckk=(has[kat+'|'+kl]||has[kat+'|'])?'checked':'';
                pills+='<label class="ja-kelas-pill"><input type="checkbox" class="ja-cb ja-kelas" data-jid="'+jid+'" data-kat="'+kat+'" data-kelas="'+kl+'" '+ckk+' onchange="onKelasChange(this)"> '+kl+'</label>';
            });
            rows+='<div class="ja-kat-row">'
                +'<label class="ja-kat-head"><input type="checkbox" class="ja-cb ja-all" data-jid="'+jid+'" data-kat="'+kat+'" data-kelas="" '+ckAll+' onchange="toggleJaAll(this)"> '+kat+' <span class="muted">(semua kelas)</span></label>'
                +'<div class="ja-kelas-wrap">'+pills+'</div>'
                +'</div>';
        }
    });
    if(noKelasChips){
        rows+='<div class="ja-kat-row" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">'
            +'<span style="font-size:11px;color:var(--text-low);font-weight:700;">Tanpa Kelas:</span>'+noKelasChips+'</div>';
    }

    return '<div class="detail-juri-accordion" id="ja-card-'+jid+'">'
        +'<div class="detail-juri-toggle" onclick="toggleJaCard('+jid+')">'
        +'<span class="dj-name"><i class="fas fa-user" style="color:var(--cyan-400);"></i> '+esc(j.name)+' '+badge+'</span>'
        +'<i class="fas fa-chevron-down dj-arrow"></i>'
        +'</div>'
        +'<div class="detail-juri-scores" style="padding:14px 16px;">'
        +'<div style="font-size:10.5px;color:var(--text-low);margin-bottom:12px;"><i class="fas fa-envelope"></i> '+esc(j.email)+'</div>'
        +rows
        +'<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">'
        +'<button class="btn-xs" onclick="resetJuriAssignmentForm('+jid+')" style="background:var(--glass-3);color:var(--text-mid);border:1px solid var(--bd-2);"><i class="fas fa-rotate-left"></i> Atur Ulang</button>'
        +'<button class="btn-xs blue" onclick="saveJuriAssignments('+jid+',this)"><i class="fas fa-floppy-disk"></i> Simpan Penugasan</button>'
        +'</div>'
        +'</div>'
        +'</div>';
}

function toggleJaCard(jid){
    var card=document.getElementById('ja-card-'+jid);
    if(!card) return;
    var tog=card.querySelector('.detail-juri-toggle');
    var body=card.querySelector('.detail-juri-scores');
    var open=body.classList.toggle('open');
    if(tog) tog.classList.toggle('open',open);
}

// Header "semua kelas": penuh = tercentang, sebagian = strip (indeterminate), kosong = lepas.
// Saat sebagian, header TIDAK dipaksa ter-uncheck (sesuai permintaan).
function refreshKategoriState(card, kat){
    var kelasCbs=card.querySelectorAll('.ja-kelas[data-kat="'+kat+'"]');
    if(!kelasCbs.length) return;
    var checked=0;
    kelasCbs.forEach(function(k){ if(k.checked) checked++; });
    var allCb=card.querySelector('.ja-all[data-kat="'+kat+'"]');
    if(!allCb) return;
    if(checked===kelasCbs.length){ allCb.checked=true; allCb.indeterminate=false; }
    else if(checked===0){ allCb.checked=false; allCb.indeterminate=false; }
    else { allCb.indeterminate=true; } // sebagian — biarkan .checked apa adanya
}

function onKelasChange(cb){
    var card=cb.closest('[id^="ja-card-"]');
    if(!card) return;
    refreshKategoriState(card, cb.dataset.kat);
}

// "Atur Ulang" per juri: kosongkan semua centang di kartu ini (belum tersimpan sampai Simpan ditekan)
function resetJuriAssignmentForm(jid){
    var card=document.getElementById('ja-card-'+jid);
    if(!card) return;
    card.querySelectorAll('.ja-cb[data-jid="'+jid+'"]').forEach(function(a){ a.checked=false; a.indeterminate=false; });
}

// "Reset Penugasan" global: hapus SEMUA penugasan juri → semua juri kembali ke jam pasir
function resetAllJuriAssignments(){
    openConfirmPopup(
        'Reset Semua Penugasan',
        'Semua penugasan kategori &amp; kelas untuk <strong>SELURUH juri</strong> akan dihapus. Setiap juri akan kembali menunggu (jam pasir) sampai Anda mengaturnya lagi.<br><span style="font-size:11px;color:var(--danger);">Tindakan ini tidak dapat dibatalkan.</span>',
        'Ya, Reset Semua',
        function(){
            fetch('/api/admin/reset-juri-assignments',{
                method:'POST',
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}
            })
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.success){ popupSuccess('Penugasan Direset',d.message||'Semua penugasan dihapus.'); loadJuriAssignments(); }
                else popupError('Gagal',d.message||'Terjadi kesalahan.');
            })
            .catch(function(){ popupError('Kesalahan Jaringan','Gagal menghubungi server.'); });
        }
    );
}

function toggleJaAll(cb){
    var card=cb.closest('[id^="ja-card-"]');
    if(!card) return;
    cb.indeterminate=false; // klik master = jadikan semua / kosong
    card.querySelectorAll('.ja-kelas[data-kat="'+cb.dataset.kat+'"]').forEach(function(k){ k.checked=cb.checked; });
}

function saveJuriAssignments(jid,btn){
    var card=document.getElementById('ja-card-'+jid);
    if(!card) return;
    var assignments=[];

    // Kategori tanpa kelas (Bonsai/Jumbo)
    card.querySelectorAll('.ja-cb[data-jid="'+jid+'"]').forEach(function(a){
        if(a.classList.contains('ja-kelas')||a.classList.contains('ja-all')) return;
        if(a.checked) assignments.push({kategori:a.dataset.kat,kelas:null});
    });

    // Kategori berkelas: kelompokkan per kategori
    var perKat={};
    card.querySelectorAll('.ja-kelas[data-jid="'+jid+'"]').forEach(function(a){
        var kat=a.dataset.kat;
        if(!perKat[kat]) perKat[kat]={total:0,checked:[]};
        perKat[kat].total++;
        if(a.checked) perKat[kat].checked.push(a.dataset.kelas);
    });
    Object.keys(perKat).forEach(function(kat){
        var info=perKat[kat];
        if(info.checked.length===0) return;
        if(info.checked.length===info.total) assignments.push({kategori:kat,kelas:null}); // semua kelas
        else info.checked.forEach(function(kl){ assignments.push({kategori:kat,kelas:kl}); });
    });

    var orig=btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    fetch('/api/admin/juri-assignments',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        body:JSON.stringify({juri_id:jid,assignments:assignments})
    })
    .then(function(r){return r.json();})
    .then(function(d){
        btn.disabled=false; btn.innerHTML=orig;
        if(d.success){ popupSuccess('Tersimpan',d.message||'Penugasan disimpan.'); loadJuriAssignments(); }
        else popupError('Gagal',d.message||'Terjadi kesalahan.');
    })
    .catch(function(){ btn.disabled=false; btn.innerHTML=orig; popupError('Kesalahan Jaringan','Gagal menghubungi server.'); });
}

/* ═══════════════════════════════════════════════
   SIDEBAR NAVIGATION
   ═══════════════════════════════════════════════ */
(function initSidebar(){
    var pageTitles = {
        results:      { title:'Kirim Hasil Juara',              sub:'Kirim data hasil juara ke peserta', icon:'fa-paper-plane' },
        dashboard:    { title:'Dashboard',                       sub:'Ringkasan statistik & grafik kontes', icon:'fa-gauge-high' },
        galeri:       { title:'Galeri Foto',                     sub:'Semua foto ikan yang diunggah juri (foto lama peserta ditandai)', icon:'fa-images' },
        penilaian:    { title:'Data Penilaian',                  sub:'Semua input nilai dari Juri & Grand Juri', icon:'fa-table-list' },
        users:        { title:'Kelola User',                     sub:'Manajemen akun pengguna sistem', icon:'fa-users-gear' },
        registrasi:   { title:'Registrasi & Undian Tank',        sub:'Pendaftaran peserta, undian, dan rentang nomor', icon:'fa-database' },
        nominasi:     { title:'Nominasi',                        sub:'Pilih tank & review nominasi (admin = juri + grand juri)', icon:'fa-award' },
        mvp:          { title:'Kelola MVP',                      sub:'Manajemen pendaftaran ikan MVP', icon:'fa-star' },
        ranking:      { title:'Point Ranking',                   sub:'Peringkat berdasarkan nilai point (hanya ikan yang sudah DIKUNCI Grand Juri)', icon:'fa-trophy' },
        undian:       { title:'Kelola Mesin Undian',             sub:'Membuka dan mengunci mesin undian tank untuk peserta', icon:'fa-dice' },
        taxonomy:     { title:'Kelola Kategori & Kelas',         sub:'Tambah, ubah nama, atur mode kelas, atau hapus kategori & kelas', icon:'fa-layer-group' },
        jumbo_calc:   { title:'Hitung Point Jumbo',              sub:'Kalkulasi point Jumbo berdasarkan rumus kategori lain', icon:'fa-calculator' },
        team_champion:{ title:'Team Champion',                   sub:'Kelola pendaftaran Team Champion, status buka/tutup, dan data peserta', icon:'fa-users-crown' },
        kelola_juri:  { title:'Kelola Juri',                     sub:'Atur kategori & kelas yang boleh dinilai tiap juri', icon:'fa-user-shield' },
        scoring_config:{ title:'Pengaturan Penilaian',           sub:'Atur bobot & persentase point tiap kategori (rumus tidak berubah)', icon:'fa-sliders' }
    };
    var loaded = { dashboard:true }; // dashboard loaded by initial loadDashboard()

    var adminNomPollTimer = null; // ★ Timer untuk auto-refresh nominasi

    window.activatePage = function(pageId){
        // ★ CLEAR polling timer dari halaman sebelumnya
        if(adminNomPollTimer){ clearInterval(adminNomPollTimer); adminNomPollTimer = null; }

        // Toggle section visibility
        document.querySelectorAll('.page-section').forEach(function(s){
            s.style.display = (s.dataset.page === pageId) ? 'block' : 'none';
        });
        // Highlight sidebar
        document.querySelectorAll('.sidebar-item').forEach(function(a){
            if(a.dataset.page === pageId) a.classList.add('active');
            else a.classList.remove('active');
        });
        // Update topbar title
        var info = pageTitles[pageId] || pageTitles.dashboard;
        var ptEl = document.getElementById('pageTitle');
        var psEl = document.getElementById('pageSubtitle');
        if(ptEl) ptEl.innerHTML = '<i class="fas '+info.icon+'"></i> '+info.title;
        if(psEl) psEl.textContent = info.sub;

        // Lazy-load data per page (cuma sekali)
        if(!loaded[pageId]){
            loaded[pageId] = true;

            if(pageId === 'penilaian'){ loadScoringData(); }
            if(pageId === 'users'){ /* loadUsers sudah jalan di init */ }
            if(pageId === 'registrasi'){ loadPesertaOld(); loadTankRange(); loadGlobalRangeDisplay(); }
            if(pageId === 'nominasi'){ loadAdminNominasiAll(); }
            if(pageId === 'mvp'){ loadMvpData(); loadMvpStatus(); loadMvpPeserta(); loadMvpIkanData(); }
            if(pageId === 'team_champion'){ loadTeamChampionStatus(); loadTeamChampionPeserta(); loadTeamChampionIkan(); }
            if(pageId === 'results'){ loadResultsStatus(); loadPiagamRecipients(); }
            if(pageId === 'ranking'){ loadAdminPointRanking(); }
            if(pageId === 'undian'){ loadUndianStatus(); }
            if(pageId === 'jumbo_calc'){ loadJumboCalcFish(); }
            if(pageId === 'kelola_juri'){ loadJuriAssignments(); loadJuriScoringLockStatus(); }
            if(pageId === 'taxonomy'){ loadTaxonomyManage(); }
            if(pageId === 'scoring_config'){ loadScoringConfigs(); }
            if(pageId === 'galeri'){ loadGaleriFoto(); loadFotoReplaceStatus(); }

        } else {
            // Refresh ringan saat dibuka ulang
            if(pageId === 'nominasi'){ loadAdminNominasiAll(); }
            if(pageId === 'mvp'){ loadMvpStatus(); }
            if(pageId === 'team_champion'){ loadTeamChampionStatus(); loadTeamChampionPeserta(); loadTeamChampionIkan(); }
            if(pageId === 'results'){ loadResultsStatus(); loadPiagamRecipients(); }
            if(pageId === 'ranking'){ loadAdminPointRanking(); }
            if(pageId === 'undian'){ loadUndianStatus(); }
            if(pageId === 'galeri'){ loadFotoReplaceStatus(); }
            if(pageId === 'kelola_juri'){ loadJuriScoringLockStatus(); }
        }

        // ★ START auto-polling kalau halaman nominasi aktif
        if(pageId === 'nominasi'){
            adminNomPollTimer = setInterval(function(){
                loadAdminPendingReview(true);
                loadAdminLateIkan(true);
            }, 5000);
        }

        document.body.classList.remove('sidebar-open');
        window.scrollTo({top:0,behavior:'smooth'});
    };

    document.querySelectorAll('.sidebar-item').forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            activatePage(this.dataset.page);
        });
    });

    var mt = document.getElementById('menuToggle');
    if(mt) mt.addEventListener('click', function(){ document.body.classList.toggle('sidebar-open'); });
    var ov = document.getElementById('sidebarOverlay');
    if(ov) ov.addEventListener('click', function(){ document.body.classList.remove('sidebar-open'); });

    // Override openModal supaya panggilan ke modalOld/modalMvp diarahkan ke page section
    var __origOpenModal = window.openModal;
    window.openModal = function(id){
        if(id === 'modalOld'){ activatePage('registrasi'); return; }
        if(id === 'modalMvp'){ activatePage('mvp'); return; }
        __origOpenModal(id);
    };
})();

/* ═══════════════════════════════════════════════
   HITUNG POINT JUMBO
   ═══════════════════════════════════════════════ */
/* ═══════════════════════════════════════════════
   HITUNG POINT JUMBO (TABLE LAYOUT)
   ═══════════════════════════════════════════════ */
function loadJumboCalcFish(){
    var wrap = document.getElementById('jumboCalcTableWrap');
    var resultBox = document.getElementById('jumboCalcResult');
    if(resultBox) resultBox.style.display = 'none';

    wrap.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data ikan Jumbo...</p></div>';

    fetch('/api/admin/jumbo-scored-ikans', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(!data || data.length === 0){
            wrap.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada ikan Jumbo yang sudah memiliki nilai dari juri.</p></div>';
            return;
        }

        var kats = allKategoriList.filter(function(k){ return k !== 'Jumbo'; });
        var katOptions = '<option value="">-- Pilih Rumus --</option>';
        kats.forEach(function(k){ katOptions += '<option value="'+k+'">'+k+'</option>'; });

        var html = '<div style="overflow-x:auto;border:1px solid var(--bd-2);border-radius:12px;">';
        html += '<table style="width:100%;border-collapse:collapse;font-size:12px;">';
        
        // Table Head
        html += '<thead><tr style="border-bottom:2px solid var(--bd-2);background:var(--glass-2);">';
        html += '<th style="padding:12px 14px;text-align:left;font-size:10px;font-weight:800;color:var(--text-muted);width:40px;">#</th>';
        html += '<th style="padding:12px 14px;text-align:left;font-size:10px;font-weight:800;color:var(--text-muted);">NO TANK</th>';
        html += '<th style="padding:12px 14px;text-align:left;font-size:10px;font-weight:800;color:var(--text-muted);">NAMA PESERTA</th>';
        html += '<th style="padding:12px 14px;text-align:center;font-size:10px;font-weight:800;color:var(--text-muted);width:60px;">JURI</th>';
        html += '<th style="padding:12px 14px;text-align:left;font-size:10px;font-weight:800;color:var(--text-muted);width:220px;">PILIH RUMUS</th>';
        html += '<th style="padding:12px 14px;text-align:center;font-size:10px;font-weight:800;color:var(--text-muted);width:100px;">AKSI</th>';
        html += '</tr></thead><tbody>';

        // Table Rows
        data.forEach(function(ikan, idx){
            html += '<tr id="jumbo-row-'+ikan.id+'" style="border-bottom:1px solid var(--bd-1);transition:background .2s;">';
            html += '<td style="padding:12px 14px;font-weight:700;color:var(--text-muted);">'+(idx+1)+'</td>';
            html += '<td style="padding:12px 14px;font-weight:800;color:var(--cyan-400);">Tank '+(ikan.nomor_tank||'?')+'</td>';
            html += '<td style="padding:12px 14px;font-weight:600;color:var(--text-hi);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="'+esc(ikan.nama_peserta)+'">'+esc(ikan.nama_peserta)+'</td>';
            html += '<td style="padding:12px 14px;text-align:center;font-weight:700;color:var(--text-mid);">'+ikan.jumlah_juri+'</td>';
            
            // Custom Dark Dropdown
            html += '<td style="padding:10px 14px;">';
            html += '<select id="jumboKat-'+ikan.id+'" style="width:100%;padding:9px 28px 9px 12px;background:rgba(0,0,0,.3);border:1px solid var(--bd-2);border-radius:8px;color:var(--text-hi);font-family:inherit;font-size:12px;font-weight:600;outline:none;cursor:pointer;color-scheme:dark;transition:border-color .2s;appearance:none;-webkit-appearance:none;background-image:url(\'data:image/svg+xml,%3Csvg xmlns=\\\'http://www.w3.org/2000/svg\\\' width=\\\'12\\\' height=\\\'12\\\' viewBox=\\\'0 0 12 12\\\'%3E%3Cpath fill=\\\'%23FCD34D\\\' d=\\\'M6 8L1 3h10z\\\'/%3E%3C/svg%3E\');background-repeat:no-repeat;background-position:right 10px center;" onfocus="this.style.borderColor=\'var(--gold-400)\'" onblur="this.style.borderColor=\'var(--bd-2)\'">'+katOptions+'</select>';
            html += '</td>';
            
            // Hitung Button
            html += '<td style="padding:10px 14px;text-align:center;">';
            html += '<button onclick="calcJumboPointFromTable('+ikan.id+')" id="jumboBtn-'+ikan.id+'" style="padding:8px 14px;font-size:11px;border-radius:8px;border:none;background:linear-gradient(135deg,var(--gold-600),var(--gold-500));color:var(--ocean-900);font-weight:800;cursor:pointer;box-shadow:0 2px 6px rgba(245,158,11,.3);transition:all .2s;font-family:inherit;"><i class="fas fa-calculator" style="margin-right:4px;"></i>Hitung</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        wrap.innerHTML = html;
    })
    .catch(function(){
        wrap.innerHTML = '<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat data Jumbo.</p></div>';
    });
}

function calcJumboPointFromTable(ikanId){
    var katSel = document.getElementById('jumboKat-'+ikanId);
    var targetKat = katSel ? katSel.value : '';
    var row = document.getElementById('jumbo-row-'+ikanId);
    var btn = document.getElementById('jumboBtn-'+ikanId);
    
    if(!targetKat){
        popupError('Rumus Belum Dipilih', 'Pilih rumus kategori terlebih dahulu pada baris Tank ini.');
        if(katSel) katSel.style.borderColor = 'var(--danger)';
        setTimeout(function(){ if(katSel) katSel.style.borderColor = 'var(--bd-2)'; }, 2000);
        return;
    }

    // Highlight active row
    if(row) row.style.background = 'rgba(245,158,11,.05)';
    
    var resultBox = document.getElementById('jumboCalcResult');
    resultBox.style.display = 'none';

    if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }

    fetch('/api/admin/calc-jumbo-point', {
        method: 'POST',
        headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN':getCsrf(), 'Accept':'application/json'},
        body: JSON.stringify({ ikan_id: ikanId, target_kategori: targetKat })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            renderJumboCalcResult(d.data, targetKat);
            resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            popupError('Gagal Menghitung', d.message || 'Terjadi kesalahan saat menghitung point.');
        }
    })
    .catch(function(){
        popupError('Kesalahan Jaringan', 'Gagal menghubungi server.');
    })
    .finally(function(){
        if(row) row.style.background = '';
        if(btn){ btn.disabled = false; btn.innerHTML = '<i class="fas fa-calculator" style="margin-right:4px;"></i>Hitung'; }
    });
}

function renderJumboCalcResult(data, kat){
    var box = document.getElementById('jumboCalcResult');
    var pb = data.point_breakdown || {};
    
    var html = '<div style="background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.01));border:1px solid var(--bd-2);border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,.2);">';
    
    // Header Info
    html += '<div style="font-size:13px;font-weight:800;color:var(--gold-300);margin-bottom:20px;display:flex;align-items:center;gap:10px;"><i class="fas fa-chart-line" style="font-size:16px;"></i> HASIL PERHITUNGAN POINT</div>';
    
    html += '<div style="display:grid;grid-template-columns:140px 1fr;gap:10px 16px;font-size:12px;margin-bottom:20px;padding:14px 16px;background:var(--glass-2);border-radius:10px;border:1px solid var(--bd-1);">';
    html += '<div style="color:var(--text-muted);font-weight:700;">Ikan Jumbo</div><div style="color:var(--text-hi);font-weight:800;">Tank '+data.nomor_tank+' — '+esc(data.nama_peserta)+'</div>';
    html += '<div style="color:var(--text-muted);font-weight:700;">Rumus Kategori</div><div style="color:var(--purple);font-weight:800;">'+esc(kat)+'</div>';
    html += '</div>';

    // Tabel Breakdown
    html += '<div style="overflow-x:auto;border:1px solid var(--bd-2);border-radius:10px;">';
    html += '<table style="width:100%;border-collapse:collapse;font-size:12px;">';
    
    // Table Head
    html += '<thead><tr style="border-bottom:2px solid var(--bd-2);background:var(--glass-2);">';
    html += '<th style="padding:12px 14px;text-align:left;font-size:10px;font-weight:800;color:var(--text-muted);letter-spacing:.5px;">KOMPONEN</th>';
    html += '<th style="padding:12px 14px;text-align:left;font-size:10px;font-weight:800;color:var(--text-muted);letter-spacing:.5px;">DETAIL RUMUS</th>';
    html += '<th style="padding:12px 14px;text-align:right;font-size:10px;font-weight:800;color:var(--text-muted);letter-spacing:.5px;">POINT</th>';
    html += '</tr></thead>';
    
    // Table Body
    html += '<tbody>';
    var katOrder = ['overall','head','face','body','marking','pearl','color','finnage'];
    
    katOrder.forEach(function(ki){
        if(!pb[ki]) return;
        var item = pb[ki];
        
        // Parsing label defect (Contoh: "Head (Defect -10%)")
        var labelMain = item.label;
        var defectHtml = '';
        var defectIndex = labelMain.indexOf(' (Defect');
        if(defectIndex !== -1){
            var mainText = labelMain.substring(0, defectIndex);
            var defectText = labelMain.substring(defectIndex);
            labelMain = mainText;
            defectHtml = '<div style="font-size:10px;font-weight:700;color:#FCA5A5;margin-top:3px;"><i class="fas fa-exclamation-triangle" style="margin-right:3px;font-size:9px;"></i>'+defectText+'</div>';
        }
        
        // Formatting parts/rumus
        var partsHtml = '-';
        if(item.parts && item.parts.length > 0){
            partsHtml = item.parts.map(function(p){
                return '<span style="display:inline-block;padding:2px 6px;background:var(--glass-2);border-radius:4px;margin:1px 0;font-size:10px;color:var(--text-mid);font-family:monospace;letter-spacing:-.3px;">'+p+'</span>';
            }).join(' <span style="color:var(--text-faint);margin:0 2px;">+</span> ');
        }

        var rowBg = defectHtml ? 'background:rgba(239,68,68,.04);' : '';
        
        html += '<tr style="border-bottom:1px solid var(--bd-1);'+rowBg+'">';
        html += '<td style="padding:12px 14px;font-weight:700;color:var(--text-hi);vertical-align:top;">'+labelMain+defectHtml+'</td>';
        html += '<td style="padding:12px 14px;color:var(--text-mid);vertical-align:top;line-height:1.8;">'+partsHtml+'</td>';
        html += '<td style="padding:12px 14px;text-align:right;font-weight:900;color:var(--gold-300);font-family:\'Fraunces\',serif;font-size:15px;vertical-align:top;">'+item.point.toFixed(2)+'</td>';
        html += '</tr>';
    });
    
    html += '</tbody>';
    
    // Table Foot (Total)
    html += '<tfoot><tr style="border-top:2px solid var(--gold-500);background:rgba(245,158,11,.08);">';
    html += '<td colspan="2" style="padding:16px 14px;font-size:14px;font-weight:900;color:var(--text-hi);">TOTAL POINT JUMBO</td>';
    html += '<td style="padding:16px 14px;text-align:right;font-size:24px;font-weight:900;color:var(--gold-300);font-family:\'Fraunces\',serif;">'+(pb.total ? pb.total.toFixed(2) : '0.00')+'</td>';
    html += '</tr></tfoot>';
    
    html += '</table></div>';
    html += '</div>';
    
    box.innerHTML = html;
    box.style.display = 'block';
}

/* ═══════════════════════════════════════════════
   LOADING STATE OVERLAY UNTUK FETCH UTAMA
   (wraps existing functions tanpa mengubah backend)
   ═══════════════════════════════════════════════ */
(function attachLoaderToFetches(){
    // loadScoringData — tambahin overlay
    var __origLoadScoring = window.loadScoringData;
    if(typeof __origLoadScoring === 'function'){
        window.loadScoringData = function(){
            var tb = document.getElementById('tBody');
            if(tb) tb.innerHTML='<tr><td colspan="11"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data penilaian...</p></div></td></tr>';
            return __origLoadScoring.apply(this, arguments);
        };
    }
    // loadMvpStatus
    var __origLoadStatus = window.loadMvpStatus;
    if(typeof __origLoadStatus === 'function'){
        window.loadMvpStatus = function(){
            var btn = document.getElementById('btnToggleMvp');
            if(btn){ btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>'; btn.disabled=true; }
            return __origLoadStatus.apply(this, arguments);
        };
    }
    // loadDashboard - kasih placeholder
    var __origLoadDash = window.loadDashboard;
    if(typeof __origLoadDash === 'function'){
        window.loadDashboard = function(){
            ['sTotal','sDinilai','sGrand','sBelum','sJuri','sAvg','sSisaTank','sPesertaUnik'].forEach(function(id){
                var el = document.getElementById(id);
                if(el && el.innerText === '0') el.innerText = '…';
            });
            return __origLoadDash.apply(this, arguments);
        };
    }

    /* ═══════════════════════════════════════════════
   ADMIN NOMINASI — STATE + FUNCTIONS
   ═══════════════════════════════════════════════ */
var adminNomState = {
    tanks: [],
    selected: new Set(),
    defects: {},
    defectCtx: null,
    histTab: 'approved',
    histData: { approved: [], rejected: [] },
    pendingData: null,
    lateData: null,
    filterKat: '',
    filterKelas: '',
    histFilterKat: '',
    histFilterKelas: '',
    histSearchTank: ''
};

var _adminPendingCache = '';
var _adminLateCache = '';

/* ★ Pencocokan filter (abaikan kelas utk Bonsai/Jumbo) */
function adminNomTankMatch(t){
    if(!t) return false;
    var noKelas=['Bonsai','Jumbo'];
    if(adminNomState.filterKat && (t.kategori||'')!==adminNomState.filterKat) return false;
    if(adminNomState.filterKelas && noKelas.indexOf(t.kategori)===-1 && (t.kelas||'')!==adminNomState.filterKelas) return false;
    return true;
}
function adminHistTankMatch(t){
    if(!t) return false;

    var noKelas = ['Bonsai','Jumbo'];

    if(adminNomState.histFilterKat && (t.kategori || '') !== adminNomState.histFilterKat) return false;
    if(adminNomState.histFilterKelas && noKelas.indexOf(t.kategori) === -1 && (t.kelas || '') !== adminNomState.histFilterKelas) return false;

    var q = (adminNomState.histSearchTank || '').toLowerCase().trim();
    if(q){
        var nomor = String(t.nomor_tank || '').toLowerCase();
        if(nomor.indexOf(q) === -1) return false;
    }

    return true;
}
function adminNomApplyFilter(){
    adminNomState.filterKat=(document.getElementById('adminNomReviewFilterKat')||{}).value||'';
    adminNomState.filterKelas=(document.getElementById('adminNomReviewFilterKelas')||{}).value||'';
    var ks=document.getElementById('adminNomReviewFilterKelas');
    if(ks){ if(adminNomState.filterKat && ['Bonsai','Jumbo'].indexOf(adminNomState.filterKat)!==-1){ ks.value=''; adminNomState.filterKelas=''; ks.disabled=true; } else { ks.disabled=false; } }
    renderAdminPending(); renderAdminLate();
}
function adminNomResetFilter(){
    adminNomState.filterKat=''; adminNomState.filterKelas='';
    var a=document.getElementById('adminNomReviewFilterKat'); if(a) a.value='';
    var b=document.getElementById('adminNomReviewFilterKelas'); if(b){ b.value=''; b.disabled=false; }
    renderAdminPending(); renderAdminLate();
}
function adminHistApplyFilter(){
    adminNomState.histSearchTank = (document.getElementById('adminHistSearchTank') || {}).value || '';
    adminNomState.histFilterKat  = (document.getElementById('adminHistFilterKat') || {}).value || '';
    adminNomState.histFilterKelas = (document.getElementById('adminHistFilterKelas') || {}).value || '';

    var ks = document.getElementById('adminHistFilterKelas');
    if(ks){
        if(adminNomState.histFilterKat && ['Bonsai','Jumbo'].indexOf(adminNomState.histFilterKat) !== -1){
            ks.value = '';
            adminNomState.histFilterKelas = '';
            ks.disabled = true;
        } else {
            ks.disabled = false;
        }
    }

    renderAdminHistory();
}

function adminHistResetFilter(){
    adminNomState.histSearchTank = '';
    adminNomState.histFilterKat = '';
    adminNomState.histFilterKelas = '';

    var s = document.getElementById('adminHistSearchTank'); if(s) s.value = '';
    var a = document.getElementById('adminHistFilterKat'); if(a) a.value = '';
    var b = document.getElementById('adminHistFilterKelas'); if(b){ b.value = ''; b.disabled = false; }

    renderAdminHistory();
}

function adminNomJsonArg(obj){
    try { return encodeURIComponent(JSON.stringify(obj || {})); }
    catch(e){ return encodeURIComponent('{}'); }
}

function adminNomParseJsonArg(raw){
    try { return JSON.parse(decodeURIComponent(raw || '%7B%7D')); }
    catch(e){ return {}; }
}

function adminNomDefectArr(t, key){
    var arr = t && Array.isArray(t[key]) ? t[key] : ['0'];

    if(typeof adminNormalizeDefectLegacy === 'function'){
        arr = adminNormalizeDefectLegacy(arr);
    }

    arr = arr.filter(function(v){
        return v !== null && v !== undefined && String(v).trim() !== '';
    });

    return arr.length ? arr : ['0'];
}

function adminNomHasDefect(t){
    var keys = ['raw_head_penalty','raw_face_penalty','raw_body_penalty','raw_finnage_penalty'];

    for(var i = 0; i < keys.length; i++){
        var arr = adminNomDefectArr(t, keys[i]);
        var real = arr.filter(function(v){ return String(v) !== '0'; });
        if(real.length > 0) return true;
    }

    return false;
}

function adminNomDefectButton(t){
    var has = adminNomHasDefect(t);
    var bg  = has ? 'rgba(245,158,11,.12)' : 'rgba(34,211,238,.08)';
    var bd  = has ? 'rgba(245,158,11,.30)' : 'rgba(34,211,238,.18)';
    var col = has ? 'var(--gold-300)' : 'var(--cyan-300)';
    var txt = has ? 'Edit Defect' : 'Edit Defect';

    return '<button class="btn-xs" onclick="event.stopPropagation();adminOpenReviewNomDefect(\''+adminNomJsonArg(t)+'\')" style="margin-top:8px;width:100%;font-size:9px;padding:5px 8px;background:'+bg+';border:1px solid '+bd+';color:'+col+';">' +
        '<i class="fas fa-pen-to-square" style="margin-right:3px;"></i>'+txt+
    '</button>';
}

function adminNomDefectRow(label, arr){
    var real = arr.filter(function(v){ return String(v) !== '0'; });
    var val = real.length ? real.join(', ') : 'Aman (0)';
    var isSafe = real.length === 0;

    return '<div style="padding:9px 10px;border-radius:10px;border:1px solid '+(isSafe?'rgba(34,211,238,.16)':'rgba(245,158,11,.28)')+';background:'+(isSafe?'rgba(34,211,238,.05)':'rgba(245,158,11,.07)')+';">' +
        '<div style="font-size:10px;font-weight:900;color:var(--text-hi);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">'+label+'</div>' +
        '<div style="font-size:11px;line-height:1.55;color:'+(isSafe?'var(--cyan-300)':'var(--gold-300)')+';font-weight:700;">'+esc(val)+'</div>' +
    '</div>';
}

function adminOpenReviewNomDefect(encoded){
    var t = adminNomParseJsonArg(encoded);

    if(!t || !t.nominasi_id){
        popupError('Data Tidak Valid', 'Nominasi ID tidak ditemukan.');
        return;
    }

    adminNomState.defectCtx = {
        mode: 'edit_nominasi',
        nominasiId: parseInt(t.nominasi_id, 10),
        tankId: t.ikan_id || t.id || t.nominasi_id,
        tank: t,
        working: {
            raw_head_penalty: adminNomNormDef(t.raw_head_penalty),
            raw_face_penalty: adminNomNormDef(t.raw_face_penalty),
            raw_body_penalty: adminNomNormDef(t.raw_body_penalty),
            raw_finnage_penalty: adminNomNormDef(t.raw_finnage_penalty)
        }
    };

    var title = document.getElementById('adminNomDefectTitle');
    if(title){
        title.innerHTML =
            '<i class="fas fa-pen-to-square" style="color:var(--gold-400);"></i> Edit Defect Nominasi';
    }

    var info = document.getElementById('adminNomDefectInfo');
    if(info){
        info.innerHTML =
            'Tank <b style="color:var(--cyan-300);">#'+esc(t.nomor_tank || '-')+'</b>' +
            ' · '+esc(t.kategori || '-') +
            ((t.kelas && ['Bonsai','Jumbo'].indexOf(t.kategori) === -1) ? ' · Kelas '+esc(t.kelas) : '') +
            '<br>Peserta: <b style="color:var(--text-hi);">'+esc(t.nama_peserta || '-')+'</b>' +
            '<br><span style="color:var(--gold-300);font-weight:800;">Perubahan disimpan ke data nominasi dan akan terbaca di panel juri/grand juri.</span>';
    }

    var saveBtn = document.getElementById('adminNomDefectSaveBtn');
    if(saveBtn){
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
    }

    adminRenderNomDefectBody();
    openModal('modalAdminNomDefect');

    setTimeout(function(){
        var sc = document.getElementById('adminNomDefectScroll');
        if(sc) sc.scrollTop = 0;
    }, 50);
}

// Alias agar onclick lama tidak error kalau masih ada cache browser.
function adminShowNomDefect(encoded){
    adminOpenReviewNomDefect(encoded);
}

function adminDeleteNominasi(nominasiId, nomorTank){
    openConfirmPopup(
        'Hapus Nominasi?',
        'Yakin ingin menghapus nominasi Tank <strong>'+esc(nomorTank || '-')+'</strong>?<br>' +
        '<span style="font-size:11px;color:var(--text-mid);line-height:1.6;display:block;margin-top:6px;">' +
        'Aksi ini hanya menghapus record nominasi. Data ikan/tank/peserta tidak dihapus. Tank bisa masuk nominasi lagi jika dipilih ulang oleh juri/admin.' +
        '</span>',
        'Ya, Hapus',
        function(){
            fetch('/api/admin/hapus-nominasi', {
                method: 'POST',
                headers: {
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept':'application/json'
                },
                body: JSON.stringify({ nominasi_id: nominasiId })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    popupSuccess('Nominasi Dihapus', d.message || 'Nominasi berhasil dihapus.');

                    _adminPendingCache = '';
                    _adminLateCache = '';

                    loadAdminNomHistory();
                    loadAdminPendingReview();
                    loadAdminLateIkan();
                    loadAdminNomTanks();
                } else {
                    popupError('Gagal', d.message || 'Nominasi gagal dihapus.');
                }
            })
            .catch(function(){
                popupError('Error', 'Gagal menghubungi server.');
            });
        }
    );
}

function loadAdminNominasiAll(){
    _adminPendingCache = '';
    _adminLateCache = '';
    loadAdminNomTanks();
    loadAdminPendingReview();
    loadAdminLateIkan();
    loadAdminNomHistory();
    loadNominasiPublishStatus();   // ★ status tombol Kirim Hasil Nominasi
}

/* ── (A) PILIH TANK ── */
function loadAdminNomTanks(){
    var grid = document.getElementById('adminNomGrid');
    if(grid) grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-spinner fa-spin"></i><p>Memuat tank...</p></div>';

    fetch('/api/admin/tanks-nominasi-tersedia', { headers:{'Accept':'application/json'} })
    .then(function(r){ return r.json(); })
    .then(function(d){
        adminNomState.tanks = d.tanks || [];
        adminNomState.selected.clear();
        adminNomState.defects = {};
        adminNomState.defectCtx = null;
        renderAdminNomGrid();
        updateAdminNomCount();
    })
    .catch(function(){
        if(grid) grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat tank.</p></div>';
    });
}

function adminNomGetFiltered(){
    var q = (document.getElementById('adminNomSearch').value || '').toLowerCase().trim();
    var fK = document.getElementById('adminNomFilterKat').value;
    var fKl = document.getElementById('adminNomFilterKelas').value;
    return adminNomState.tanks.filter(function(t){
        if(fK && t.kategori !== fK) return false;
        if(fKl && t.kelas !== fKl) return false;
        if(q){
            var hay = (String(t.nomor_tank) + ' ' + (t.kategori||'') + ' ' + (t.kelas||'') + ' ' + (t.nama_peserta||'')).toLowerCase();
            if(hay.indexOf(q) === -1) return false;
        }
        return true;
    });
}

function adminNomNormDef(v){
    if(!v) return ['0'];
    if(typeof v === 'string') return adminNormalizeDefectLegacy([v]);
    if(Array.isArray(v)) return adminNormalizeDefectLegacy(v);
    return ['0'];
}

function adminNomEmptyDefect(){
    return {
        raw_head_penalty: ['0'],
        raw_face_penalty: ['0'],
        raw_body_penalty: ['0'],
        raw_finnage_penalty: ['0']
    };
}

function adminNomEvalDefects(defectData){
    var parts = ['head','face','body','finnage'];
    var status = {};

    parts.forEach(function(p){
        status[p] = { minor:false, mayor:false, items:[] };
    });

    parts.forEach(function(p){
        var key = 'raw_' + p + '_penalty';
        var vals = adminNomNormDef(defectData[key]);

        vals.forEach(function(d){
            if(d && d !== '0'){
                status[p].items.push(d);
                if(ADMIN_MINOR_DEFECTS.indexOf(d) !== -1) status[p].minor = true;
                if(ADMIN_MAYOR_DEFECTS.indexOf(d) !== -1) status[p].mayor = true;
            }
        });
    });

    var minorComponents = 0;
    parts.forEach(function(p){
        if(status[p].minor) minorComponents++;
    });

    var globalMayor = minorComponents >= 3;
    var result = {};

    parts.forEach(function(p){
        if(status[p].items.length === 0){
            result[p] = '';
        } else {
            result[p] = (status[p].mayor || (status[p].minor && globalMayor)) ? '30%' : '10%';
        }
    });

    return result;
}

function adminNomDefectSummary(tankId){
    var d = adminNomState.defects[tankId];
    if(!d) return { count:0, level:'none' };

    var count = 0;
    ['head','face','body','finnage'].forEach(function(p){
        var vals = adminNomNormDef(d['raw_' + p + '_penalty']);
        vals.forEach(function(v){
            if(v && v !== '0') count++;
        });
    });

    if(count === 0) return { count:0, level:'none' };

    var ev = adminNomEvalDefects(d);
    var mayor = ['head','face','body','finnage'].some(function(p){
        return ev[p] === '30%';
    });

    return { count:count, level: mayor ? 'mayor' : 'minor' };
}

function adminNomDefectBtnHtml(tankId){
    var sum = adminNomDefectSummary(tankId);

    var bg = 'rgba(34,211,238,.08)';
    var bd = 'rgba(34,211,238,.22)';
    var color = 'var(--cyan-300)';
    var icon = 'fa-circle-exclamation';
    var label = 'Tandai Defect';

    if(sum.level === 'minor'){
        bg = 'rgba(245,158,11,.10)';
        bd = 'rgba(245,158,11,.30)';
        color = 'var(--gold-300)';
        label = 'Defect Minor (' + sum.count + ')';
    }

    if(sum.level === 'mayor'){
        bg = 'rgba(239,68,68,.12)';
        bd = 'rgba(239,68,68,.32)';
        color = '#FCA5A5';
        icon = 'fa-triangle-exclamation';
        label = 'Defect Mayor (' + sum.count + ')';
    }

    return '<button type="button" onclick="event.stopPropagation();adminOpenNomDefect('+tankId+')" ' +
        'style="margin-top:8px;width:100%;padding:6px 8px;border-radius:8px;border:1px solid '+bd+';background:'+bg+';color:'+color+';font-family:inherit;font-size:9.5px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;">' +
        '<i class="fas '+icon+'"></i> '+label+
    '</button>';
}

function adminNomPartLabel(defectKey){
    if(defectKey === 'raw_head_penalty') return 'HEAD';
    if(defectKey === 'raw_face_penalty') return 'FACE';
    if(defectKey === 'raw_body_penalty') return 'BODY';
    if(defectKey === 'raw_finnage_penalty') return 'FINNAGE';
    return defectKey;
}

function adminOpenNomDefect(tankId){
    var tank = adminNomState.tanks.find(function(t){ return parseInt(t.id,10) === parseInt(tankId,10); });
    var current = adminNomState.defects[tankId] || adminNomEmptyDefect();

    adminNomState.defectCtx = {
        tankId: tankId,
        working: {
            raw_head_penalty: adminNomNormDef(current.raw_head_penalty),
            raw_face_penalty: adminNomNormDef(current.raw_face_penalty),
            raw_body_penalty: adminNomNormDef(current.raw_body_penalty),
            raw_finnage_penalty: adminNomNormDef(current.raw_finnage_penalty)
        }
    };

    var title = document.getElementById('adminNomDefectTitle');
    if(title) title.textContent = 'Defect Nominasi Admin';

    var info = document.getElementById('adminNomDefectInfo');
    if(info){
        info.innerHTML =
            'Tank <b style="color:var(--cyan-300);">#'+esc(tank ? tank.nomor_tank : tankId)+'</b>' +
            ' · '+esc(tank ? tank.kategori : '-') +
            (tank && tank.kelas && ['Bonsai','Jumbo'].indexOf(tank.kategori) === -1 ? ' · Kelas '+esc(tank.kelas) : '') +
            '<br>Peserta: <b style="color:var(--text-hi);">'+esc(tank ? (tank.nama_peserta || '-') : '-')+'</b>';
    }

    adminRenderNomDefectBody();
    openModal('modalAdminNomDefect');

    setTimeout(function(){
        var sc = document.getElementById('adminNomDefectScroll');
        if(sc) sc.scrollTop = 0;
    }, 50);
}

function adminRenderNomDefectBody(){
    var ctx = adminNomState.defectCtx;
    var body = document.getElementById('adminNomDefectBody');
    if(!ctx || !body) return;

    var ev = adminNomEvalDefects(ctx.working);
    var keys = ['raw_head_penalty','raw_face_penalty','raw_body_penalty','raw_finnage_penalty'];

    var html =
        '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;align-items:start;">';

    keys.forEach(function(key){
        var part = key.replace('raw_','').replace('_penalty','');
        var current = adminNomNormDef(ctx.working[key]);
        var options = ADMIN_DEFECT_OPTIONS[key] || [];

        var tag =
            '<span style="padding:4px 8px;border-radius:999px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);color:#6EE7B7;font-size:9px;font-weight:900;white-space:nowrap;">AMAN</span>';

        if(ev[part] === '10%'){
            tag =
                '<span style="padding:4px 8px;border-radius:999px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.30);color:var(--gold-300);font-size:9px;font-weight:900;white-space:nowrap;">MINOR −10%</span>';
        }

        if(ev[part] === '30%'){
            tag =
                '<span style="padding:4px 8px;border-radius:999px;background:rgba(239,68,68,.13);border:1px solid rgba(239,68,68,.35);color:#FCA5A5;font-size:9px;font-weight:900;white-space:nowrap;">MAYOR −30%</span>';
        }

        html +=
            '<div style="border:1px solid var(--bd-2);border-radius:16px;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02));overflow:hidden;box-shadow:0 10px 24px -18px rgba(0,0,0,.55);">';

        html +=
            '<div style="padding:12px 14px;border-bottom:1px solid var(--bd-1);display:flex;justify-content:space-between;align-items:center;gap:10px;background:rgba(255,255,255,.025);">' +
                '<b style="font-size:12px;color:var(--text-hi);letter-spacing:.08em;">' + adminNomPartLabel(key) + '</b>' +
                tag +
            '</div>';

        html += '<div style="padding:13px;display:flex;flex-direction:column;gap:13px;">';

        options.forEach(function(group, groupIdx){
            html +=
                '<div style="border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:10px;background:rgba(0,0,0,.12);">' +
                    '<div style="font-size:10px;font-weight:900;color:var(--text-low);letter-spacing:.08em;margin-bottom:9px;text-transform:uppercase;">' +
                        esc(group.label).replace(/---/g, '') +
                    '</div>' +
                    '<div style="display:grid;grid-template-columns:1fr;gap:8px;">';

            group.options.forEach(function(opt, optIdx){
                var checked = current.indexOf(opt.value) !== -1;
                var safeVal = String(opt.value).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                var inputId = 'admin_nom_def_' + key + '_' + groupIdx + '_' + optIdx;

                html +=
                    '<label for="' + inputId + '" onclick="event.stopPropagation();" ' +
                    'style="' +
                        'display:grid;' +
                        'grid-template-columns:18px 1fr;' +
                        'align-items:center;' +
                        'gap:10px;' +
                        'min-height:44px;' +
                        'padding:10px 11px;' +
                        'border-radius:11px;' +
                        'border:1px solid ' + (checked ? 'var(--cyan-400)' : 'var(--bd-2)') + ';' +
                        'background:' + (checked ? 'rgba(34,211,238,.12)' : 'rgba(255,255,255,.03)') + ';' +
                        'cursor:pointer;' +
                        'user-select:none;' +
                        'transition:all .15s;' +
                    '">' +
                        '<input id="' + inputId + '" type="checkbox" ' + (checked ? 'checked' : '') + ' ' +
                            'onclick="event.stopPropagation();" ' +
                            'onchange="adminToggleNomDefect(\'' + key + '\',\'' + safeVal + '\')" ' +
                            'style="width:17px;height:17px;accent-color:var(--cyan-400);cursor:pointer;">' +
                        '<span style="font-size:11.5px;font-weight:800;color:var(--text);line-height:1.35;">' + esc(opt.label) + '</span>' +
                    '</label>';
            });

            html += '</div></div>';
        });

        html += '</div></div>';
    });

    html += '</div>';
    body.innerHTML = html;
}

function adminToggleNomDefect(defectKey, value){
    var ctx = adminNomState.defectCtx;
    if(!ctx) return;

    var current = adminNomNormDef(ctx.working[defectKey]);

    if(value === '0'){
        current = ['0'];
    } else {
        current = current.filter(function(v){ return v !== '0'; });

        if(current.indexOf(value) !== -1){
            current = current.filter(function(v){ return v !== value; });
        } else {
            current.push(value);
        }

        if(current.length === 0) current = ['0'];
    }

    ctx.working[defectKey] = current;
    adminRenderNomDefectBody();
}

function adminResetNomDefect(){
    if(!adminNomState.defectCtx) return;

    adminNomState.defectCtx.working = adminNomEmptyDefect();
    adminRenderNomDefectBody();
}

function adminNomWorkingPayload(working){
    return {
        raw_head_penalty: adminNomNormDef(working.raw_head_penalty),
        raw_face_penalty: adminNomNormDef(working.raw_face_penalty),
        raw_body_penalty: adminNomNormDef(working.raw_body_penalty),
        raw_finnage_penalty: adminNomNormDef(working.raw_finnage_penalty)
    };
}

function adminSaveNomDefect(){
    var ctx = adminNomState.defectCtx;
    if(!ctx) return;

    var payload = adminNomWorkingPayload(ctx.working);

    // MODE EDIT: dari Review Nominasi Pending / Riwayat Review.
    if(ctx.mode === 'edit_nominasi'){
        var btn = document.getElementById('adminNomDefectSaveBtn');
        var oldHtml = btn ? btn.innerHTML : '';

        if(btn){
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        }

        fetch('/api/admin/update-nominasi-defect', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nominasi_id: ctx.nominasiId,
                defects: payload
            })
        })
        .then(function(r){
            return r.json().then(function(d){
                if(!r.ok) throw d;
                return d;
            });
        })
        .then(function(d){
            if(!d.success){
                popupError('Gagal', d.message || 'Defect nominasi gagal diperbarui.');
                return;
            }

            adminNomState.defectCtx = null;
            closeModal('modalAdminNomDefect');

            _adminPendingCache = '';
            _adminLateCache = '';

            loadAdminPendingReview();
            loadAdminNomHistory();
            loadAdminNomTanks();

            popupSuccess('Defect Diperbarui', d.message || 'Defect nominasi berhasil diperbarui.');
        })
        .catch(function(e){
            popupError('Error', e.message || 'Gagal menyimpan defect nominasi.');
        })
        .finally(function(){
            if(btn){
                btn.disabled = false;
                btn.innerHTML = oldHtml || '<i class="fas fa-save"></i> Simpan Perubahan';
            }
        });

        return;
    }

    // MODE LAMA: pilih defect saat admin membuat nominasi baru.
    var hasAny = ['raw_head_penalty','raw_face_penalty','raw_body_penalty','raw_finnage_penalty'].some(function(key){
        return adminNomNormDef(ctx.working[key]).some(function(v){ return v && v !== '0'; });
    });

    if(hasAny){
        adminNomState.defects[ctx.tankId] = payload;
    } else {
        delete adminNomState.defects[ctx.tankId];
    }

    adminNomState.defectCtx = null;
    closeModal('modalAdminNomDefect');
    renderAdminNomGrid();
}

function adminCloseNomDefect(){
    adminNomState.defectCtx = null;
    closeModal('modalAdminNomDefect');
}

function renderAdminNomGrid(){
    var grid = document.getElementById('adminNomGrid');
    if(!grid) return;
    var filtered = adminNomGetFiltered();
    document.getElementById('adminNomCounter').textContent = filtered.length + ' tank tersedia';
    if(filtered.length === 0){
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-inbox"></i><p>Tidak ada tank yang cocok filter.</p></div>';
        return;
    }
    var html = '';
    filtered.forEach(function(t){
        var sel = adminNomState.selected.has(t.id);
        var bg = sel ? 'linear-gradient(135deg,rgba(34,211,238,.18),rgba(34,211,238,.05))' : 'var(--glass-2)';
        var border = sel ? 'var(--cyan-400)' : 'var(--bd-2)';
        var shadow = sel ? '0 0 0 1px var(--cyan-400),0 8px 20px -8px rgba(6,182,212,.4)' : 'none';
        var kelasBadge = (t.kelas && ['Bonsai','Jumbo'].indexOf(t.kategori) === -1)
            ? '<div style="font-size:9px;font-weight:700;padding:3px 7px;border-radius:6px;background:rgba(16,185,129,.10);color:#6EE7B7;border:1px solid rgba(16,185,129,.20);margin-top:4px;text-align:center;">Kelas '+esc(t.kelas)+'</div>' : '';
        html += '<div onclick="adminNomToggleTank('+t.id+')" style="padding:10px;border-radius:12px;border:1px solid '+border+';background:'+bg+';box-shadow:'+shadow+';cursor:pointer;transition:all .25s;">';
        html += '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">';
        html += '<div style="width:38px;height:38px;border-radius:10px;display:grid;place-items:center;font-weight:800;font-size:15px;color:#fff;background:linear-gradient(135deg,'+(sel?'var(--royal-600),var(--cyan-500)':'var(--ocean-700),var(--ocean-600)')+');">'+(t.nomor_tank||'?')+'</div>';
        html += '<i class="fas '+(sel?'fa-star':'fa-star')+'" style="color:'+(sel?'var(--gold-400)':'var(--text-faint)')+';font-size:14px;"></i>';
        html += '</div>';
        html += '<div style="font-size:10px;font-weight:700;padding:3px 7px;border-radius:6px;background:rgba(34,211,238,.08);color:var(--cyan-300);border:1px solid rgba(34,211,238,.18);text-align:center;">'+esc(t.kategori||'-')+'</div>';
        html += kelasBadge;
        html += '<div style="font-size:10px;color:var(--text-mid);margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">'+esc(t.nama_peserta||'-')+'</div>';

        if(sel){
            html += adminNomDefectBtnHtml(t.id);
        }

        html += '</div>';
    });
    grid.innerHTML = html;
}

function adminNomToggleTank(id){
    if(adminNomState.selected.has(id)){
        adminNomState.selected.delete(id);
        delete adminNomState.defects[id];
    } else {
        adminNomState.selected.add(id);
    }

    renderAdminNomGrid();
    updateAdminNomCount();
}

function updateAdminNomCount(){
    var c = adminNomState.selected.size;
    document.getElementById('adminNomSelectedCount').textContent = c;
    var btn = document.getElementById('adminNomSubmitBtn');
    if(c > 0){
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.style.opacity = '.5';
        btn.style.cursor = 'not-allowed';
    }
}

function adminNomSubmit(){
    if(adminNomState.selected.size === 0) return;
    var ids = Array.from(adminNomState.selected);
    var defectsPayload = {};
    ids.forEach(function(id){
        if(adminNomState.defects[id]){
            defectsPayload[id] = adminNomState.defects[id];
        }
    });
    openConfirmPopup(
        'Submit Nominasi',
        'Yakin ingin submit <strong>'+ids.length+' tank</strong> sebagai nominasi admin?<br><span style="font-size:11px;color:var(--text-mid);">Tank akan masuk ke daftar pending review dan bisa di-ACC/Tolak oleh admin atau grand juri.</span>',
        'Ya, Submit',
        function(){
            var btn = document.getElementById('adminNomSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            fetch('/api/admin/submit-nominasi', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
                body: JSON.stringify({
                    ikan_ids: ids,
                    defects: defectsPayload
                })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    popupSuccess('Nominasi Terkirim', d.message);
                    adminNomState.selected.clear();
                    adminNomState.defects = {};
                    adminNomState.defectCtx = null;
                    loadAdminNomTanks();
                    loadAdminPendingReview();
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); })
            .finally(function(){
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Nominasi';
                updateAdminNomCount();
            });
        }
    );
}

document.addEventListener('input', function(e){
    if(e.target && e.target.id === 'adminNomSearch') renderAdminNomGrid();
});

function loadAdminPendingReview(silent){
    var body = document.getElementById('adminPendingBody');
    if(!body) return;
    if(!silent) _adminPendingCache = '';
    if(!silent && (!body.children.length || body.querySelector('.fa-spinner'))){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat pending...</p></div>';
    }
    fetch('/api/grand-juri/nominasi', { headers:{'Accept':'application/json'} })
    .then(function(r){ return r.json(); })
    .then(function(d){
        var fingerprint = JSON.stringify(d);
        if(fingerprint === _adminPendingCache) return;
        _adminPendingCache = fingerprint;
        document.getElementById('adminPendingCount').textContent = (d.total_pending || 0) + ' pending';
        adminNomState.pendingData = d;
        renderAdminPending();
    })
    .catch(function(){
        if(silent) return;
        body.innerHTML = '<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat pending.</p></div>';
    });
}

function adminApprovePending(btn, nominasiId){
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('/api/grand-juri/nominasi-review', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
        body: JSON.stringify({ nominasi_id: nominasiId, action:'approve' })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            popupSuccess('Disetujui', d.message);
            loadAdminPendingReview();
            loadAdminNomHistory();
            loadAdminLateIkan();
        } else {
            popupError('Gagal', d.message || 'Error.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> ACC';
        }
    })
    .catch(function(){
        popupError('Error', 'Gagal menghubungi server.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> ACC';
    });
}

function adminRejectPending(nominasiId, nomorTank){
    openConfirmPopup(
        'Tolak Nominasi?',
        'Yakin ingin menolak Tank <strong>'+nomorTank+'</strong>?<br><span style="font-size:11px;color:var(--text-mid);">Juri akan diminta resubmit nominasi.</span>',
        'Ya, Tolak',
        function(){
            fetch('/api/grand-juri/nominasi-review', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
                body: JSON.stringify({ nominasi_id: nominasiId, action:'reject', catatan:'' })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    popupSuccess('Ditolak', d.message);
                    loadAdminPendingReview();
                    loadAdminNomHistory();
                } else popupError('Gagal', d.message || 'Error.');
            })
            .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); });
        }
    );
}

function adminApproveAllInGroup(btn, idsJson){
    var ids;
    try { ids = (typeof idsJson === 'string') ? JSON.parse(idsJson.replace(/&quot;/g,'"')) : idsJson; }
    catch(e){ ids = []; }
    if(!ids.length) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    var success = 0, fail = 0;
    var promises = ids.map(function(id){
        return fetch('/api/grand-juri/nominasi-review', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
            body: JSON.stringify({ nominasi_id: id, action:'approve' })
        }).then(function(r){ return r.json(); })
        .then(function(d){ if(d.success) success++; else fail++; })
        .catch(function(){ fail++; });
    });
    Promise.all(promises).then(function(){
        if(fail === 0) popupSuccess('Berhasil', 'Semua '+success+' nominasi disetujui.');
        else popupError('Sebagian Gagal', success+' berhasil, '+fail+' gagal.');
        loadAdminPendingReview();
        loadAdminNomHistory();
        loadAdminLateIkan();
    });
}

function loadAdminLateIkan(silent){
    var body = document.getElementById('adminLateBody');
    if(!body) return;
    if(!silent) _adminLateCache = '';
    if(!silent && (!body.children.length || body.querySelector('.fa-spinner'))){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>';
    }
    fetch('/api/grand-juri/late-ikan', { headers:{'Accept':'application/json'} })
    .then(function(r){ return r.json(); })
    .then(function(d){
        var fingerprint = JSON.stringify(d);
        if(fingerprint === _adminLateCache) return;
        _adminLateCache = fingerprint;
        adminNomState.lateData = d;
        renderAdminLate();
    })
    .catch(function(){
        if(silent) return;
        body.innerHTML = '<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat data.</p></div>';
    });
}

function renderAdminPending(){
    var body = document.getElementById('adminPendingBody');
    if(!body) return;
    var d = adminNomState.pendingData;
    if(!d || !d.grouped || d.grouped.length === 0){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada nominasi pending untuk direview.</p></div>';
        return;
    }
    var html = ''; var anyVisible = false;
    d.grouped.forEach(function(g){
        var visTanks = (g.tanks || []).filter(adminNomTankMatch);
        if(visTanks.length === 0) return;
        anyVisible = true;
        var tankIds = visTanks.map(function(t){ return t.nominasi_id; });
        html += '<div style="margin-bottom:14px;border:1px solid var(--bd-2);border-radius:14px;overflow:hidden;background:var(--glass-1);">';
        html += '<div style="padding:11px 14px;background:var(--glass-2);border-bottom:1px solid var(--bd-1);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">';
        html += '<div style="display:flex;align-items:center;gap:10px;"><div style="width:34px;height:34px;border-radius:10px;background:rgba(34,211,238,.12);color:var(--cyan-300);display:grid;place-items:center;"><i class="fas fa-user-pen"></i></div>';
        html += '<div><div style="font-size:13px;font-weight:800;color:var(--text-hi);">'+esc(g.juri_name||'Unknown')+'</div><div style="font-size:10px;color:var(--text-mid);font-weight:600;">'+visTanks.length+' tank dinominasikan</div></div></div>';
        html += '<button class="btn-xs green" onclick="adminApproveAllInGroup(this,'+JSON.stringify(tankIds).replace(/"/g,'&quot;')+')" style="padding:6px 12px;"><i class="fas fa-check-double"></i> ACC Semua</button>';
        html += '</div>';
        html += '<div style="padding:12px 14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;">';
        visTanks.forEach(function(t){
            var kelasH = (t.kelas && ['Bonsai','Jumbo'].indexOf(t.kategori) === -1)
                ? '<div style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;background:rgba(16,185,129,.10);color:#6EE7B7;border:1px solid rgba(16,185,129,.20);margin-top:3px;text-align:center;">Kelas '+esc(t.kelas)+'</div>' : '';
            html += '<div id="admPendCard-'+t.nominasi_id+'" style="padding:10px;border-radius:10px;border:1px solid var(--bd-2);background:var(--glass-2);">';
            html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;"><div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:#fff;display:grid;place-items:center;font-weight:800;">'+(t.nomor_tank||'?')+'</div></div>';
            html += '<div style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:5px;background:rgba(34,211,238,.08);color:var(--cyan-300);border:1px solid rgba(34,211,238,.18);text-align:center;">'+esc(t.kategori||'-')+'</div>';
            html += kelasH;
            html += '<div style="font-size:9px;color:var(--text-mid);margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">'+esc(t.nama_peserta||'-')+'</div>';
            html += adminNomDefectButton(t);
            html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:8px;">';
            html += '<button class="btn-xs green" onclick="adminApprovePending(this,'+t.nominasi_id+')" style="padding:5px;font-size:9px;"><i class="fas fa-check"></i> ACC</button>';
            html += '<button class="btn-xs red" onclick="adminRejectPending('+t.nominasi_id+','+(t.nomor_tank||0)+')" style="padding:5px;font-size:9px;"><i class="fas fa-times"></i> Tolak</button>';
            html += '</div></div>';
        });
        html += '</div></div>';
    });
    if(!anyVisible){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-filter"></i><p>Tidak ada nominasi pending yang cocok dengan filter.</p></div>';
        return;
    }
    body.innerHTML = html;
}

function renderAdminLate(){
    var body = document.getElementById('adminLateBody');
    if(!body) return;
    var d = adminNomState.lateData;
    if(!d) return;
    var countEl = document.getElementById('adminLateCount');
    if(!d.enabled){
        if(countEl) countEl.textContent = (d.juri_done||0)+'/'+(d.total_juri||0)+' juri selesai';
        body.innerHTML = '<div class="empty-state"><i class="fas fa-lock" style="color:var(--text-faint);"></i><p>Modul aktif setelah <strong>semua juri</strong> selesai nominasi ('+(d.juri_done||0)+'/'+(d.total_juri||0)+' juri sudah selesai).</p></div>';
        return;
    }
    var allIkans = d.ikans || [];
    if(allIkans.length === 0){
        if(countEl) countEl.textContent = '0 tank terlambat';
        body.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle" style="color:#6EE7B7;"></i><p>Tidak ada ikan terlambat yang menunggu review.</p></div>';
        return;
    }
    var ikans = allIkans.filter(adminNomTankMatch);
    if(countEl) countEl.textContent = ikans.length + ' tank terlambat';
    if(ikans.length === 0){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-filter"></i><p>Tidak ada ikan terlambat yang cocok dengan filter.</p></div>';
        return;
    }
    var html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">';
    ikans.forEach(function(ikan){
        var kelasH = (ikan.kelas && ['Bonsai','Jumbo'].indexOf(ikan.kategori) === -1)
            ? '<div style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;background:rgba(16,185,129,.10);color:#6EE7B7;border:1px solid rgba(16,185,129,.20);margin-top:3px;text-align:center;">Kelas '+esc(ikan.kelas)+'</div>' : '';
        html += '<div id="admLateCard-'+ikan.ikan_id+'" style="padding:11px;border-radius:11px;border:1px solid var(--bd-gold);background:rgba(245,158,11,.04);">';
        html += '<div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--gold-600),var(--gold-500));color:#fff;display:grid;place-items:center;font-weight:800;margin-bottom:8px;">'+(ikan.nomor_tank||'?')+'</div>';
        html += '<div style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:5px;background:rgba(34,211,238,.08);color:var(--cyan-300);border:1px solid rgba(34,211,238,.18);text-align:center;">'+esc(ikan.kategori||'-')+'</div>';
        html += kelasH;
        html += '<div style="font-size:9px;color:var(--text-mid);margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">'+esc(ikan.nama_peserta||'-')+'</div>';
        html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:8px;">';
        html += '<button class="btn-xs green" onclick="adminApproveLate(this,'+ikan.ikan_id+')" style="padding:5px;font-size:9px;"><i class="fas fa-check"></i> ACC</button>';
        html += '<button class="btn-xs red" onclick="adminRejectLate('+ikan.ikan_id+','+(ikan.nomor_tank||0)+')" style="padding:5px;font-size:9px;"><i class="fas fa-times"></i> Tolak</button>';
        html += '</div></div>';
    });
    html += '</div>';
    body.innerHTML = html;
}

function adminApproveLate(btn, ikanId){
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('/api/grand-juri/late-ikan-review', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
        body: JSON.stringify({ ikan_id: ikanId, action:'approve' })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            popupSuccess('Disetujui', d.message);
            loadAdminLateIkan();
            loadAdminNomHistory();
        } else {
            popupError('Gagal', d.message || 'Error.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> ACC';
        }
    })
    .catch(function(){
        popupError('Error', 'Gagal menghubungi server.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> ACC';
    });
}

function adminRejectLate(ikanId, nomorTank){
    openConfirmPopup(
        'Tolak Ikan Terlambat?',
        'Tolak ikan terlambat Tank <strong>'+nomorTank+'</strong>?',
        'Ya, Tolak',
        function(){
            fetch('/api/grand-juri/late-ikan-review', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
                body: JSON.stringify({ ikan_id: ikanId, action:'reject', catatan:'' })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    popupSuccess('Ditolak', d.message);
                    loadAdminLateIkan();
                    loadAdminNomHistory();
                } else popupError('Gagal', d.message || 'Error.');
            })
            .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); });
        }
    );
}

function adminResetRejected(nominasiId, nomorTank){
    openConfirmPopup(
        'Reset Nominasi Ditolak?',
        'Yakin ingin mereset penolakan Tank <strong>'+nomorTank+'</strong>?<br><span style="font-size:11px;color:var(--text-mid);">Data penolakan akan dihapus sehingga Juri dapat mengajukan ulang nominasi tank ini.</span>',
        'Ya, Reset',
        function(){
            fetch('/api/admin/reset-rejected-nominasi', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'},
                body: JSON.stringify({ nominasi_id: nominasiId })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.success){
                    popupSuccess('Berhasil Direset', d.message);
                    loadAdminNomHistory(); // Refresh riwayat
                    loadAdminNomTanks();   // Refresh grid tank yang tersedia
                } else {
                    popupError('Gagal', d.message || 'Terjadi kesalahan.');
                }
            })
            .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); });
        }
    );
}

/* ── (D) HISTORY ── */
function loadAdminNomHistory(){
    fetch('/api/grand-juri/nominasi-history', { headers:{'Accept':'application/json'} })
    .then(function(r){ return r.json(); })
    .then(function(d){
        adminNomState.histData.approved = d.approved || [];
        adminNomState.histData.rejected = d.rejected || [];
        document.getElementById('adminHistAppCount').textContent = d.total_approved || 0;
        document.getElementById('adminHistRejCount').textContent = d.total_rejected || 0;
        renderAdminHistory();
    })
    .catch(function(){
        var body = document.getElementById('adminHistBody');
        if(body) body.innerHTML = '<div class="empty-state" style="color:var(--danger);"><i class="fas fa-triangle-exclamation"></i><p>Gagal memuat riwayat.</p></div>';
    });
}

function adminSwitchHistTab(tab){
    adminNomState.histTab = tab;
    var btnApp = document.getElementById('adminHistTabApp');
    var btnRej = document.getElementById('adminHistTabRej');
    if(tab === 'approved'){
        btnApp.style.cssText = 'padding:6px 12px;background:rgba(16,185,129,.20);color:#6EE7B7;border:none;';
        btnRej.style.cssText = 'padding:6px 12px;background:transparent;color:var(--text-mid);border:none;';
    } else {
        btnApp.style.cssText = 'padding:6px 12px;background:transparent;color:var(--text-mid);border:none;';
        btnRej.style.cssText = 'padding:6px 12px;background:rgba(239,68,68,.20);color:#FCA5A5;border:none;';
    }
    renderAdminHistory();
}

function renderAdminHistory(){
    var body = document.getElementById('adminHistBody');
    if(!body) return;

    var groups = adminNomState.histData[adminNomState.histTab];
    var isApp = adminNomState.histTab === 'approved';

    if(!groups || groups.length === 0){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data '+(isApp?'yang diterima':'yang ditolak')+'.</p></div>';
        return;
    }

    var html = '';
    var anyVisible = false;

    groups.forEach(function(g){
        var visTanks = (g.tanks || []).filter(adminHistTankMatch);
        if(visTanks.length === 0) return;

        anyVisible = true;

        var color = isApp ? '#6EE7B7' : '#FCA5A5';
        var bgC = isApp ? 'rgba(16,185,129,.08)' : 'rgba(239,68,68,.08)';
        var bdC = isApp ? 'rgba(16,185,129,.25)' : 'rgba(239,68,68,.25)';

        html += '<div style="margin-bottom:12px;border:1px solid '+bdC+';border-radius:12px;background:'+bgC+';overflow:hidden;">';
        html += '<div style="padding:10px 14px;display:flex;align-items:center;gap:10px;">';
        html += '<div style="width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.05);color:'+color+';display:grid;place-items:center;"><i class="fas fa-'+(isApp?'check':'times')+'"></i></div>';
        html += '<div><div style="font-size:13px;font-weight:800;color:var(--text-hi);">'+esc(g.juri_name)+'</div><div style="font-size:10px;color:var(--text-mid);font-weight:600;">'+visTanks.length+' tank</div></div></div>';

        html += '<div style="padding:8px 14px 12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:8px;">';

        visTanks.forEach(function(t){
            var kelasH = (t.kelas && ['Bonsai','Jumbo'].indexOf(t.kategori) === -1)
                ? '<div style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;background:rgba(16,185,129,.10);color:#6EE7B7;border:1px solid rgba(16,185,129,.20);margin-top:3px;text-align:center;">Kelas '+esc(t.kelas)+'</div>'
                : '';

            html += '<div style="padding:9px;border-radius:9px;border:1px solid '+bdC+';background:rgba(255,255,255,.02);">';
            html += '<div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--royal-600),var(--cyan-500));color:#fff;display:grid;place-items:center;font-weight:800;margin-bottom:6px;font-size:13px;">'+(t.nomor_tank||'?')+'</div>';
            html += '<div style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:5px;background:rgba(34,211,238,.08);color:var(--cyan-300);border:1px solid rgba(34,211,238,.18);text-align:center;">'+esc(t.kategori||'-')+'</div>';
            html += kelasH;

            if(!isApp && t.catatan){
                html += '<div style="font-size:9px;color:'+color+';margin-top:5px;line-height:1.4;"><i class="fas fa-comment-dots"></i> '+esc(t.catatan)+'</div>';
            }

            html += '<div style="font-size:9px;color:var(--text-low);margin-top:5px;font-weight:600;">'+esc(t.reviewed_at||'')+'</div>';

            html += adminNomDefectButton(t);

            html += '<div style="display:grid;grid-template-columns:1fr;gap:5px;margin-top:6px;">';

            if(!isApp){
                html += '<button class="btn-xs blue" onclick="adminResetRejected('+t.nominasi_id+',\''+(t.nomor_tank||0)+'\')" style="width:100%;font-size:9px;padding:5px 8px;"><i class="fas fa-rotate-left" style="margin-right:3px;"></i>Reset & Ajukan Ulang</button>';
            }

            html += '<button class="btn-xs red" onclick="adminDeleteNominasi('+t.nominasi_id+',\''+(t.nomor_tank||0)+'\')" style="width:100%;font-size:9px;padding:5px 8px;"><i class="fas fa-trash" style="margin-right:3px;"></i>Hapus Nominasi</button>';

            html += '</div>';
            html += '</div>';
        });

        html += '</div></div>';
    });

    if(!anyVisible){
        body.innerHTML = '<div class="empty-state"><i class="fas fa-filter"></i><p>Tidak ada data '+(isApp?'diterima':'ditolak')+' yang cocok dengan filter.</p></div>';
        return;
    }

    body.innerHTML = html;
}

/* ═══════════════════════════════════════════════
   MODUL 1 — PENGATURAN PENILAIAN (SCORING POINT CONFIG)
   Field id di bawah = NAMA KOLOM DB (jangan diganti).
   ═══════════════════════════════════════════════ */
var spcConfigs = {}; // cache: { kategori: {...kolom config...} }

var SPC_SECTIONS = [
    { key:'overall', label:'Overall Impression', icon:'fa-star',
      bobot:'overall_bobot',
      fields:[ {col:'overall_point', label:'Impression'} ] },
    { key:'head', label:'Head', icon:'fa-brain',
      bobot:'head_bobot',
      fields:[ {col:'head_size_pct', label:'Size (Ukuran)'}, {col:'head_bentuk_k_pct', label:'Bentuk Kepala'} ] },
    { key:'face', label:'Face', icon:'fa-face-smile',
      bobot:'face_bobot',
      fields:[ {col:'face_face_pct', label:'Face'} ] },
    { key:'body', label:'Body Shape', icon:'fa-shapes',
      bobot:'body_bobot',
      fields:[ {col:'body_bentuk_pct', label:'Bentuk Badan'}, {col:'body_proposional_pct', label:'Proporsional'}, {col:'body_pangkal_pct', label:'Pangkal'} ] },
    { key:'marking', label:'Marking', icon:'fa-pen-nib',
      bobot:'marking_bobot',
      fields:[ {col:'marking_fullness_pct', label:'Fullness'}, {col:'marking_contrast_pct', label:'Contrast'}, {col:'marking_bentuk_pct', label:'Bentuk'} ] },
    { key:'pearl', label:'Pearl', icon:'fa-circle-dot',
      bobot:'pearl_bobot',
      fields:[ {col:'pearl_shinning_pct', label:'Shining'}, {col:'pearl_fullnes_pct', label:'Fullness'}, {col:'pearl_bentuk_pearl_pct', label:'Bentuk'} ] },
    { key:'color', label:'Color', icon:'fa-palette',
      bobot:'color_bobot',
      fields:[ {col:'color_komposisi_pct', label:'Komposisi'}, {col:'color_kecerahan_pct', label:'Kecerahan'}, {col:'color_fullness_colour_pct', label:'Fullness'} ] },
    { key:'finnage', label:'Finnage', icon:'fa-feather',
      bobot:'finnage_bobot',
      fields:[ {col:'finnage_bentuk_sirip_ekor_pct', label:'Bentuk Sirip & Ekor'}, {col:'finnage_kecerahan_pct', label:'Kecerahan'} ] }
];

function spcInputStyle(){
    return 'width:100%;text-align:center;font-weight:700;padding:9px 6px;font-size:13px;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.14);border-radius:8px;color:#F8FAFC;outline:none;font-family:inherit;';
}

function loadScoringConfigs(){
    var sel = document.getElementById('spcCategorySelect');
    var form = document.getElementById('scoringConfigForm');
    if(!sel || !form) return;
    form.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat konfigurasi...</p></div>';
    fetch('/api/admin/scoring-configs', {headers:{'Accept':'application/json'}})
    .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    .then(function(d){
        spcConfigs = {};
        var list = (d && d.configs) || [];
        list.forEach(function(c){ spcConfigs[c.kategori] = c; });
        var prev = sel.value;
        sel.innerHTML = '<option value="">-- Pilih Kategori --</option>';
        Object.keys(spcConfigs).sort().forEach(function(k){
            var o = document.createElement('option');
            o.value = k; o.textContent = k;
            sel.appendChild(o);
        });
        if(prev && spcConfigs[prev]){ sel.value = prev; renderScoringConfigForm(prev); }
        else { form.innerHTML = '<div class="empty-state"><i class="fas fa-hand-pointer"></i><p>Pilih kategori untuk mulai menyetel.</p></div>'; }
    })
    .catch(function(err){
        form.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">'+esc(err.message)+'</p></div>';
    });
}

function onScoringConfigCategoryChange(){
    var k = document.getElementById('spcCategorySelect').value;
    var form = document.getElementById('scoringConfigForm');
    if(!k){ form.innerHTML = '<div class="empty-state"><i class="fas fa-hand-pointer"></i><p>Pilih kategori untuk mulai menyetel.</p></div>'; return; }
    renderScoringConfigForm(k);
}

function renderScoringConfigForm(kategori){
    var cfg = spcConfigs[kategori] || {};
    var form = document.getElementById('scoringConfigForm');
    if(!form) return;
    var html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:14px;">';
    SPC_SECTIONS.forEach(function(sec){
        html += '<div style="background:var(--glass-2);border:1px solid var(--bd-2);border-radius:14px;padding:14px;">';
        html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">'
              + '<span style="width:28px;height:28px;border-radius:8px;display:grid;place-items:center;background:rgba(34,211,238,.12);border:1px solid var(--bd-cyan);color:var(--cyan-400);font-size:12px;"><i class="fas '+sec.icon+'"></i></span>'
              + '<b style="font-size:12px;color:var(--text-hi);text-transform:uppercase;letter-spacing:.04em;">'+esc(sec.label)+'</b></div>';

        var bobotVal = (cfg[sec.bobot] !== undefined && cfg[sec.bobot] !== null) ? cfg[sec.bobot] : '';
        html += '<div style="margin-bottom:10px;">'
              + '<label style="display:block;font-size:10px;font-weight:700;color:var(--cyan-300);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;">Bobot</label>'
              + '<input type="number" step="0.01" min="0" id="spc-'+sec.bobot+'" value="'+esc(String(bobotVal))+'" style="'+spcInputStyle()+'"></div>';

        sec.fields.forEach(function(f){
            var v = (cfg[f.col] !== undefined && cfg[f.col] !== null) ? cfg[f.col] : '';
            var suffix = (sec.key === 'overall') ? 'Point' : '%';
            html += '<div style="margin-bottom:8px;">'
                  + '<label style="display:block;font-size:10px;font-weight:600;color:var(--text-mid);margin-bottom:5px;">'+esc(f.label)+' <span style="color:var(--text-low);">('+suffix+')</span></label>'
                  + '<input type="number" step="0.01" min="0" id="spc-'+f.col+'" value="'+esc(String(v))+'" oninput="spcUpdateSum(\''+sec.key+'\')" style="'+spcInputStyle()+'"></div>';
        });

        if(sec.key !== 'overall' && sec.fields.length > 1){
            html += '<div id="spc-sum-'+sec.key+'" style="font-size:10px;font-weight:700;color:var(--text-low);margin-top:4px;"></div>';
        }
        html += '</div>';
    });
    html += '</div>';
    html += '<div style="margin-top:18px;display:flex;justify-content:flex-end;">'
          + '<button class="btn-primary" id="spcSaveBtn" onclick="saveScoringConfig(this)" style="padding:10px 22px;font-size:12px;"><i class="fas fa-floppy-disk"></i> Simpan Konfigurasi</button></div>';
    form.innerHTML = html;

    SPC_SECTIONS.forEach(function(sec){ if(sec.key !== 'overall' && sec.fields.length > 1) spcUpdateSum(sec.key); });
}

function spcUpdateSum(sectionKey){
    var sec = null;
    for(var i=0;i<SPC_SECTIONS.length;i++){ if(SPC_SECTIONS[i].key === sectionKey){ sec = SPC_SECTIONS[i]; break; } }
    if(!sec) return;
    var el = document.getElementById('spc-sum-'+sectionKey);
    if(!el) return;
    var sum = 0;
    sec.fields.forEach(function(f){
        var node = document.getElementById('spc-'+f.col);
        var v = node ? parseFloat(node.value) : NaN;
        if(!isNaN(v)) sum += v;
    });
    sum = Math.round(sum * 100) / 100;
    var ok = Math.abs(sum - 100) < 0.001;
    el.innerHTML = 'Total persen: <b style="color:'+(ok ? '#34D399' : '#FBBF24')+';">'+sum+'%</b>' + (ok ? '' : ' <span style="color:var(--text-low);">(umumnya 100%)</span>');
}

function saveScoringConfig(btn){
    var kategori = document.getElementById('spcCategorySelect').value;
    if(!kategori){ popupError('Belum ada kategori', 'Pilih kategori terlebih dahulu.'); return; }

    var payload = { _token: getCsrf(), kategori: kategori };
    var invalid = false;
    SPC_SECTIONS.forEach(function(sec){
        var bEl = document.getElementById('spc-'+sec.bobot);
        payload[sec.bobot] = bEl ? bEl.value : '';
        if(bEl && (bEl.value === '' || isNaN(parseFloat(bEl.value)) || parseFloat(bEl.value) < 0)) invalid = true;
        sec.fields.forEach(function(f){
            var el = document.getElementById('spc-'+f.col);
            payload[f.col] = el ? el.value : '';
            if(el && (el.value === '' || isNaN(parseFloat(el.value)) || parseFloat(el.value) < 0)) invalid = true;
        });
    });
    if(invalid){ popupError('Input tidak valid', 'Semua kolom bobot & persentase wajib diisi angka ≥ 0.'); return; }

    var orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    fetch('/api/admin/scoring-config', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, body:j}; }); })
    .then(function(res){
        btn.disabled = false; btn.innerHTML = orig;
        if(res.ok && res.body && res.body.success){
            if(res.body.config) spcConfigs[kategori] = res.body.config;
            popupSuccess('Tersimpan', res.body.message || 'Konfigurasi penilaian berhasil disimpan.');
        } else {
            popupError('Gagal', (res.body && res.body.message) || 'Gagal menyimpan konfigurasi.');
        }
    })
    .catch(function(err){
        btn.disabled = false; btn.innerHTML = orig;
        popupError('Gagal', err.message);
    });
}

/* ═══════════════════════════════════════════════
   MODUL 3 — SAKLAR FITUR MVP & TEAM CHAMPION
   ═══════════════════════════════════════════════ */
function updateMvpFeatureUI(enabled){
    var btn = document.getElementById('btnToggleMvpFeature');
    var txt = document.getElementById('mvpFeatureText');
    if(!btn || !txt) return;
    btn.className = 'btn-primary';
    if(enabled){
        btn.innerHTML = '<i class="fas fa-toggle-on"></i> Nonaktifkan Fitur';
        txt.innerHTML = '<span style="color:#6EE7B7;"><i class="fas fa-circle-check"></i> Fitur MVP <b>AKTIF</b> — menu, pendaftaran, dan hasil MVP tampil untuk user.</span>';
    } else {
        btn.innerHTML = '<i class="fas fa-toggle-off"></i> Aktifkan Fitur';
        txt.innerHTML = '<span style="color:#FCA5A5;"><i class="fas fa-ban"></i> Fitur MVP <b>NONAKTIF</b> — menu, pendaftaran, dan hasil MVP disembunyikan dari user.</span>';
    }
}

function toggleMvpFeature(){
    var btn = document.getElementById('btnToggleMvpFeature');
    if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('/api/admin/toggle-mvp-feature', {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            updateMvpFeatureUI(!!d.feature_enabled);
            popupSuccess('Fitur MVP Diperbarui', d.message);
        } else { popupError('Gagal', d.message || 'Gagal memperbarui fitur MVP.'); }
    })
    .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); })
    .finally(function(){ if(btn) btn.disabled = false; });
}

function updateTeamChampionFeatureUI(enabled){
    var btn = document.getElementById('btnToggleTeamChampionFeature');
    var txt = document.getElementById('teamChampionFeatureText');
    if(!btn || !txt) return;
    btn.className = 'btn-primary';
    if(enabled){
        btn.innerHTML = '<i class="fas fa-toggle-on"></i> Nonaktifkan Fitur';
        txt.innerHTML = '<span style="color:#6EE7B7;"><i class="fas fa-circle-check"></i> Fitur Team Champion <b>AKTIF</b> — menu & pendaftaran tampil untuk user.</span>';
    } else {
        btn.innerHTML = '<i class="fas fa-toggle-off"></i> Aktifkan Fitur';
        txt.innerHTML = '<span style="color:#FCA5A5;"><i class="fas fa-ban"></i> Fitur Team Champion <b>NONAKTIF</b> — pendaftaran diblokir untuk user.</span>';
    }
}

function toggleTeamChampionFeature(){
    var btn = document.getElementById('btnToggleTeamChampionFeature');
    if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('/api/admin/toggle-team-champion-feature', {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':getCsrf()}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            updateTeamChampionFeatureUI(!!d.feature_enabled);
            popupSuccess('Fitur Team Champion Diperbarui', d.message);
        } else { popupError('Gagal', d.message || 'Gagal memperbarui fitur Team Champion.'); }
    })
    .catch(function(){ popupError('Error', 'Gagal menghubungi server.'); })
    .finally(function(){ if(btn) btn.disabled = false; });
}

/* ═══════════════════════════════════════════════
   MODUL 2 — KELOLA KATEGORI & KELAS (UI)
   ═══════════════════════════════════════════════ */
var taxCategories = [];
var taxClasses = [];
var taxPresets = [];

function loadTaxonomyManage(){
    var cc = document.getElementById('categoryList');
    if(cc) cc.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat...</p></div>';
    fetch('/api/admin/taxonomy', {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(d){
        taxCategories = (d && d.categories) || [];
        taxClasses = (d && d.classes) || [];
        taxPresets = (d && d.presets) || [];
        renderCategoryList();
        renderClassList();
        populateCopyFromSelect();
        refreshTaxonomyArrays(); // selaraskan array global utk UI lain
    })
    .catch(function(err){
        if(cc) cc.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i><p style="color:var(--danger);">'+esc(err.message)+'</p></div>';
    });
}

function renderCategoryList(){
    var c = document.getElementById('categoryList');
    if(!c) return;
    if(!taxCategories.length){ c.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada kategori.</p></div>'; return; }
    c.innerHTML = taxCategories.map(function(cat){
        var n = esc(cat.name);
        var safe = n.replace(/'/g,"\\'");
        var modeOn = !!cat.uses_kelas;
        var modeBtn = '<button type="button" onclick="toggleCategoryKelas(\''+safe+'\')" title="Klik untuk menyalakan / mematikan mode kelas" '
            + 'style="display:inline-flex;align-items:center;gap:6px;padding:7px 11px;font-size:10px;font-weight:800;border-radius:8px;cursor:pointer;'
            + (modeOn
                ? 'border:1px solid var(--bd-cyan);background:rgba(34,211,238,.14);color:var(--cyan-300);'
                : 'border:1px solid var(--bd-2);background:rgba(255,255,255,.03);color:var(--text-low);')
            + '">'
            + '<i class="fas '+(modeOn ? 'fa-toggle-on' : 'fa-toggle-off')+'" style="font-size:14px;"></i> Mode Kelas: '+(modeOn ? 'ON' : 'OFF')+'</button>';
        return '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:var(--glass-2);border:1px solid var(--bd-2);border-radius:11px;padding:10px 12px;">'
            + '<b style="font-size:13px;color:var(--text-hi);flex:1;min-width:120px;">'+n+'</b>'
            + modeBtn
            + '<button type="button" onclick="renameCategoryPrompt(\''+safe+'\')" style="padding:7px 11px;font-size:10px;font-weight:700;border-radius:8px;border:1px solid var(--bd-2);background:var(--glass-2);color:var(--cyan-300);cursor:pointer;"><i class="fas fa-pen"></i> Ubah Nama</button>'
            + '<button type="button" onclick="editCategoryFormulaPrompt(\''+safe+'\')" style="padding:7px 11px;font-size:10px;font-weight:700;border-radius:8px;border:1px solid rgba(168,85,247,.4);background:rgba(168,85,247,.12);color:#D8B4FE;cursor:pointer;"><i class="fas fa-sliders"></i> Edit Rumus</button>'
            + '<button type="button" onclick="deleteCategory(\''+safe+'\')" style="padding:7px 11px;font-size:10px;font-weight:700;border-radius:8px;border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.12);color:#FCA5A5;cursor:pointer;"><i class="fas fa-trash"></i> Hapus</button>'
            + '</div>';
    }).join('');
}

function renderClassList(){
    var c = document.getElementById('classList');
    if(!c) return;
    if(!taxClasses.length){ c.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada kelas.</p></div>'; return; }
    c.innerHTML = taxClasses.map(function(name){
        var n = esc(name); var safe = n.replace(/'/g,"\\'");
        return '<div style="display:flex;align-items:center;gap:8px;background:var(--glass-2);border:1px solid var(--bd-2);border-radius:11px;padding:8px 12px;">'
            + '<b style="font-size:13px;color:var(--text-hi);">'+n+'</b>'
            + '<button type="button" title="Ubah nama" onclick="renameClassPrompt(\''+safe+'\')" style="padding:6px 9px;font-size:10px;border-radius:7px;border:1px solid var(--bd-2);background:var(--glass-2);color:var(--cyan-300);cursor:pointer;"><i class="fas fa-pen"></i></button>'
            + '<button type="button" title="Hapus" onclick="deleteClass(\''+safe+'\')" style="padding:6px 9px;font-size:10px;border-radius:7px;border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.12);color:#FCA5A5;cursor:pointer;"><i class="fas fa-trash"></i></button>'
            + '</div>';
    }).join('');
}

function taxPost(url, payload, okTitle, btn){
    var orig = btn ? btn.innerHTML : null;
    if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    payload._token = getCsrf();
    return fetch(url, {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, body:j}; }); })
    .then(function(res){
        if(btn){ btn.disabled = false; btn.innerHTML = orig; }
        if(res.ok && res.body && res.body.success){
            popupSuccess(okTitle, res.body.message || 'Berhasil.');
            loadTaxonomyManage();
        } else {
            popupError('Gagal', (res.body && res.body.message) || 'Operasi gagal.');
        }
    })
    .catch(function(err){
        if(btn){ btn.disabled = false; btn.innerHTML = orig; }
        popupError('Gagal', err.message);
    });
}

function populateCopyFromSelect(){
    var sel = document.getElementById('newCategoryCopyFrom');
    if(!sel) return;
    var prev = sel.value;
    sel.innerHTML = '<option value="">Rumus baru (kosong)</option>';
    taxPresets.forEach(function(name){
        var o = document.createElement('option');
        o.value = name;
        o.textContent = 'Ikuti rumus: ' + name;
        sel.appendChild(o);
    });
    if(prev) sel.value = prev;
}

function addCategory(btn){
    var el = document.getElementById('newCategoryName');
    var name = (el.value || '').trim();
    if(!name){ popupError('Nama kosong', 'Isi nama kategori.'); return; }
    var usesKelas = document.getElementById('newCategoryUsesKelas').checked;
    var copyEl = document.getElementById('newCategoryCopyFrom');
    var copyFrom = copyEl ? copyEl.value : '';
    taxPost('/api/admin/category/add', {name:name, uses_kelas: usesKelas ? 1 : 0, copy_from: copyFrom}, 'Kategori Ditambahkan', btn)
        .then(function(){ el.value = ''; if(copyEl) copyEl.value = ''; });
}

function renameCategoryPrompt(oldName){
    popupPromptText({
        title:'Ubah Nama Kategori',
        desc:'Ubah nama kategori <b>'+esc(oldName)+'</b>. Data ikan, konfigurasi point, penugasan juri, dan rentang tank ikut diperbarui.',
        value: oldName,
        placeholder:'Nama kategori baru',
        okText:'Simpan Perubahan',
        icon:'fa-pen',
        onOk: function(nv){
            if(!nv){ popupError('Nama kosong','Nama kategori tidak boleh kosong.'); return; }
            if(nv === oldName) return;
            taxPost('/api/admin/category/rename', {old_name:oldName, new_name:nv}, 'Nama Kategori Diubah');
        }
    });
}

function toggleCategoryKelas(name){
    taxPost('/api/admin/category/toggle-kelas', {name:name}, 'Mode Kelas Diubah');
}

function deleteCategory(name){
    openConfirmPopup('Hapus Kategori',
        'Hapus kategori <b>'+esc(name)+'</b>? Hanya bisa jika tidak ada ikan terdaftar pada kategori ini.',
        'Ya, Hapus',
        function(){ taxPost('/api/admin/category/delete', {name:name}, 'Kategori Dihapus'); });
}

function addClass(btn){
    var el = document.getElementById('newClassName');
    var name = (el.value || '').trim();
    if(!name){ popupError('Nama kosong', 'Isi nama kelas.'); return; }
    taxPost('/api/admin/class/add', {name:name}, 'Kelas Ditambahkan', btn)
        .then(function(){ el.value = ''; });
}

function renameClassPrompt(oldName){
    popupPromptText({
        title:'Ubah Nama Kelas',
        desc:'Ubah nama kelas <b>'+esc(oldName)+'</b>. Data ikan, nilai, penugasan juri, dan rentang tank ikut diperbarui.',
        value: oldName,
        placeholder:'Nama kelas baru',
        okText:'Simpan Perubahan',
        icon:'fa-pen',
        onOk: function(nv){
            if(!nv){ popupError('Nama kosong','Nama kelas tidak boleh kosong.'); return; }
            if(nv === oldName) return;
            taxPost('/api/admin/class/rename', {old_name:oldName, new_name:nv}, 'Nama Kelas Diubah');
        }
    });
}

function editCategoryFormulaPrompt(name){
    var options = taxPresets.map(function(p){ return { value: p, label: 'Ikuti rumus: ' + p }; });
    if(!options.length){ popupError('Belum ada rumus', 'Belum ada rumus tersimpan.'); return; }
    var hasOwn = options.some(function(o){ return o.value === name; });
    popupPromptSelect({
        title: 'Edit Rumus — ' + name,
        desc: 'Pilih rumus untuk kategori <b>'+esc(name)+'</b>. Semua rumus tersedia (termasuk rumus asli tiap kategori). Pilihan ini menyalin bobot &amp; persen ke kategori ini, dan <b>semua ikan kategori ini otomatis dihitung ulang</b>.',
        options: options,
        value: hasOwn ? name : options[0].value,
        okText: 'Terapkan Rumus',
        icon: 'fa-sliders',
        onOk: function(src){
            if(!src) return;
            taxPost('/api/admin/category/apply-formula', { name: name, copy_from: src }, 'Rumus Kategori Diperbarui');
        }
    });
}

function deleteClass(name){
    openConfirmPopup('Hapus Kelas',
        'Hapus kelas <b>'+esc(name)+'</b>? Hanya bisa jika tidak ada ikan terdaftar pada kelas ini.',
        'Ya, Hapus',
        function(){ taxPost('/api/admin/class/delete', {name:name}, 'Kelas Dihapus'); });
}

/* ═══════════════════════════════════════════════
   EXPOSE FUNGSI NOMINASI KE WINDOW
   (agar bisa diakses dari onclick HTML & dari window.activatePage)
   ═══════════════════════════════════════════════ */
window.loadAdminNominasiAll   = loadAdminNominasiAll;
window.loadAdminNomTanks      = loadAdminNomTanks;
window.renderAdminNomGrid     = renderAdminNomGrid;
window.adminNomToggleTank     = adminNomToggleTank;
window.adminNomSubmit         = adminNomSubmit;
window.adminOpenNomDefect     = adminOpenNomDefect;
window.adminToggleNomDefect   = adminToggleNomDefect;
window.adminResetNomDefect    = adminResetNomDefect;
window.adminSaveNomDefect     = adminSaveNomDefect;
window.adminCloseNomDefect    = adminCloseNomDefect;
window.loadAdminPendingReview = loadAdminPendingReview;
window.adminApprovePending    = adminApprovePending;
window.adminRejectPending     = adminRejectPending;
window.adminApproveAllInGroup = adminApproveAllInGroup;
window.loadAdminLateIkan      = loadAdminLateIkan;
window.adminApproveLate       = adminApproveLate;
window.adminRejectLate        = adminRejectLate;
window.loadAdminNomHistory    = loadAdminNomHistory;
window.adminSwitchHistTab     = adminSwitchHistTab;
window.renderAdminHistory     = renderAdminHistory;
window.adminResetRejected = adminResetRejected;
window.adminNomApplyFilter  = adminNomApplyFilter;
window.adminNomResetFilter  = adminNomResetFilter;
window.adminHistApplyFilter = adminHistApplyFilter;
window.adminHistResetFilter = adminHistResetFilter;
window.adminOpenReviewNomDefect = adminOpenReviewNomDefect;
window.adminShowNomDefect = adminShowNomDefect;
window.adminDeleteNominasi = adminDeleteNominasi;
window.adminOpenNomDefect   = adminOpenNomDefect;
window.adminToggleNomDefect = adminToggleNomDefect;
window.adminResetNomDefect  = adminResetNomDefect;
window.adminSaveNomDefect   = adminSaveNomDefect;
window.adminCloseNomDefect  = adminCloseNomDefect;
window.loadScoringConfigs           = loadScoringConfigs;
window.onScoringConfigCategoryChange = onScoringConfigCategoryChange;
window.renderScoringConfigForm      = renderScoringConfigForm;
window.spcUpdateSum                 = spcUpdateSum;
window.saveScoringConfig            = saveScoringConfig;
window.toggleMvpFeature            = toggleMvpFeature;
window.updateMvpFeatureUI          = updateMvpFeatureUI;
window.toggleTeamChampionFeature   = toggleTeamChampionFeature;
window.updateTeamChampionFeatureUI = updateTeamChampionFeatureUI;
window.loadTaxonomyManage   = loadTaxonomyManage;
window.addCategory          = addCategory;
window.renameCategoryPrompt = renameCategoryPrompt;
window.toggleCategoryKelas  = toggleCategoryKelas;
window.deleteCategory       = deleteCategory;
window.addClass             = addClass;
window.renameClassPrompt    = renameClassPrompt;
window.deleteClass          = deleteClass;
window.editCategoryFormulaPrompt = editCategoryFormulaPrompt;
})();