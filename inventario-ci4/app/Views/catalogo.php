<?php
/** @var array $productos @var string $contacto @var bool $logueado */
$datos = array_map(static fn ($p) => $p + ['img' => $p['mini'], 'hw' => $p['esHotWheels']], $productos);
$json = json_encode(['productos' => $datos, 'contacto' => $contacto, 'fecha' => ''], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
?>
<?= view('_cabeza', ['titulo' => 'Catálogo · Juguetería PETUS']) ?>
<body style="background:#eceef1">
<div class="petus-cat" id="petusCat">
<style>
.petus-cat{--bg:#eceef1;--surface:#fff;--surface-2:#f5f6f8;--ink:#15181c;--muted:#5b636d;--line:#d6dae0;--accent:#e8550f;--blister:#1846c4;--flame:#ffb21a;--ok:#17804a;--danger:#c0352a;
  --f-display:"Barlow Condensed","Arial Narrow",system-ui,sans-serif;--f-body:"Barlow",system-ui,-apple-system,"Segoe UI",sans-serif;
  font-family:var(--f-body);color:var(--ink);background:var(--bg);font-size:16px;line-height:1.45;padding:16px max(16px,env(safe-area-inset-left,0px)) 32px;border-radius:0;max-width:1180px;margin:0 auto;display:flex;flex-direction:column;gap:16px;box-sizing:border-box}
.petus-cat *{box-sizing:border-box}
.petus-cat button,.petus-cat input{font:inherit;color:inherit}
.petus-cat [hidden]{display:none!important}
.petus-cat .hero{position:relative;overflow:hidden;border-radius:16px;background:var(--blister);color:#fff;padding:24px 20px 22px;display:flex;flex-direction:column;gap:6px}
.petus-cat .hero::after{content:"";position:absolute;right:-40px;top:0;bottom:0;width:200px;background:repeating-linear-gradient(115deg,rgba(255,255,255,.14) 0 10px,transparent 10px 22px);pointer-events:none}
.petus-cat .eyebrow{font-size:12px;font-weight:600;letter-spacing:.16em;text-transform:uppercase;opacity:.85;position:relative;z-index:1}
.petus-cat .hero h1{margin:0;font-family:var(--f-display);font-weight:700;font-size:clamp(36px,9vw,64px);line-height:.95;text-transform:uppercase;letter-spacing:.01em;color:#fff;position:relative;z-index:1}
.petus-cat .hero p{margin:4px 0 0;font-size:15px;opacity:.92;position:relative;z-index:1}
.petus-cat .count{align-self:flex-start;margin-top:6px;background:rgba(0,0,0,.25);border-radius:999px;padding:4px 12px;font-size:13px;font-weight:600;position:relative;z-index:1}
.petus-cat .bar{display:flex;gap:8px;position:sticky;top:env(safe-area-inset-top,0px);z-index:20;background:var(--bg);padding-block:8px;margin-block:-8px;box-shadow:0 8px 8px -8px rgba(20,24,28,.18)}
.petus-cat .inp{flex:1;min-width:0;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:12px;font-size:16px;min-height:48px}
.petus-cat .btn{appearance:none;border:1px solid transparent;border-radius:10px;padding:12px 16px;font-weight:600;font-size:16px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;line-height:1.1}
.petus-cat .btn.ghost{background:var(--surface);border-color:var(--line);color:var(--ink)}
.petus-cat .btn.primary{background:var(--accent);color:#fff}
.petus-cat .btn.outline{background:var(--surface);border:1.5px solid var(--muted);color:var(--ink)}
.petus-cat .btn.dark{background:var(--ink);color:#fff}
.petus-cat .btn:disabled{opacity:.6;cursor:default}
.petus-cat .btn:focus-visible,.petus-cat .inp:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
.petus-cat .fbadge{display:inline-grid;place-items:center;min-width:20px;height:20px;border-radius:999px;background:var(--accent);color:#fff;font-size:12px;padding:0 6px}
.petus-cat .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px}
.petus-cat .bl{position:relative;display:flex;flex-direction:column;background:var(--surface);border-radius:14px;overflow:hidden;border:1px solid var(--line);box-shadow:0 1px 2px rgba(20,24,28,.06),0 6px 18px rgba(20,24,28,.06)}
.petus-cat .band{display:flex;align-items:center;justify-content:space-between;gap:8px;background:var(--blister);color:#fff;padding:7px 10px;min-height:34px}
.petus-cat .band span{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0}
.petus-cat .num{flex:none;background:var(--flame);color:#1a0d05;font-family:var(--f-display);font-weight:700;font-size:13px;border-radius:999px;padding:1px 8px}
.petus-cat .bubble{margin:10px;border-radius:18px;aspect-ratio:1/1;max-width:100%;overflow:hidden;background:repeating-linear-gradient(115deg,var(--surface-2) 0 12px,var(--bg) 12px 24px);border:2px solid var(--line);position:relative;display:grid;place-items:center}
.petus-cat .bubble img{width:100%;height:100%;object-fit:contain;display:block;padding:6px;margin:0;border:0;box-shadow:none}
.petus-cat .bubble svg{width:40%;height:40%;color:var(--muted)}
.petus-cat .own{position:absolute;left:8px;bottom:8px;z-index:1;display:grid;place-items:center;width:28px;height:28px;border-radius:50%;font-family:var(--f-display);font-weight:700;font-size:17px;line-height:1;color:#fff;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.25)}
.petus-cat .own-L{background:#c2185b}.petus-cat .own-V{background:#0f766e}
.petus-cat .off{position:absolute;left:8px;top:8px;background:var(--danger);color:#fff;font-weight:700;font-size:12px;padding:2px 8px;border-radius:999px;z-index:1}
.petus-cat .body{padding:0 12px 12px;display:flex;flex-direction:column;gap:8px;flex:1}
.petus-cat .name{font-family:var(--f-display);font-weight:700;font-size:20px;line-height:1.02;text-transform:uppercase;overflow-wrap:anywhere}
.petus-cat .meta{font-size:12px;color:var(--muted)}
.petus-cat .foot{margin-top:auto;display:flex;align-items:flex-end;justify-content:space-between;gap:8px;flex-wrap:wrap}
.petus-cat .was{color:var(--muted);text-decoration:line-through;font-size:13px;display:block}
.petus-cat .tag{display:inline-block;background:var(--flame);color:#1a0d05;font-family:var(--f-display);font-weight:700;font-size:22px;padding:2px 12px 2px 10px;transform:skewX(-10deg);border-radius:4px}
.petus-cat .tag b{display:inline-block;transform:skewX(10deg)}
.petus-cat .tag.ask{background:var(--surface-2);color:var(--muted);font-size:15px}
.petus-cat .stock{font-size:12px;font-weight:600;color:var(--ok)}
.petus-cat .more{grid-column:1/-1;justify-self:center}
.petus-cat .empty{background:var(--surface);border:1px dashed var(--line);border-radius:12px;padding:22px 16px;text-align:center;color:var(--muted)}
.petus-cat .note{text-align:center;color:var(--muted);font-size:13px;margin:0}
.petus-cat .spin{width:18px;height:18px;border-radius:50%;border:3px solid rgba(255,255,255,.4);border-top-color:#fff;animation:petusspin .8s linear infinite;flex:none}
@keyframes petusspin{to{transform:rotate(360deg)}}
.petus-sheet{position:fixed;inset:0;z-index:99999;background:rgba(10,12,14,.55);display:flex;align-items:flex-end;justify-content:center;font-family:"Barlow",system-ui,sans-serif;color:#15181c}
.petus-sheet[hidden]{display:none!important}
.petus-sheet .panel{background:#fff;width:100%;max-width:560px;max-height:92%;overflow:auto;border-radius:18px 18px 0 0;padding:12px 20px calc(20px + env(safe-area-inset-bottom,0px));display:flex;flex-direction:column;gap:8px}
.petus-sheet .grab{width:120px;height:4px;border-radius:9px;background:#15181c;align-self:center;opacity:.8}
.petus-sheet .head{display:flex;justify-content:flex-end}
.petus-sheet .x{width:44px;height:44px;border-radius:50%;border:0;background:#f0f1f3;font-size:22px;cursor:pointer}
.petus-sheet h3{font-size:22px;font-weight:600;margin:4px 0;font-family:inherit;color:#15181c}
.petus-sheet .opt{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 0;font-size:18px;cursor:pointer;margin:0}
.petus-sheet .opt input{width:24px;height:24px;accent-color:#15181c;margin:0;flex:none}
.petus-sheet .opt small{color:#5b636d;font-size:13px}
.petus-sheet hr{border:0;border-top:1px solid #d6dae0;margin:6px 0}
.petus-sheet .foot{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding-top:10px}
.petus-sheet .btn{appearance:none;border-radius:10px;padding:12px 16px;font-weight:600;font-size:17px;cursor:pointer;min-height:52px;font-family:inherit}
.petus-sheet .outline{background:#fff;border:1.5px solid #5b636d;color:#15181c}
.petus-sheet .dark{background:#15181c;color:#fff;border:0}
@media (max-width:420px){.petus-cat .grid{grid-template-columns:1fr 1fr;gap:10px}.petus-cat .name{font-size:17px}.petus-cat .tag{font-size:19px}}
</style>
  <header class="hero">
    <span class="eyebrow">Catálogo</span>
    <h1>Juguetería PETUS</h1>
    <p class="contacto" hidden></p>
    <span class="count"></span>
  </header>
  <div class="bar">
    <input class="inp q" type="search" placeholder="Buscar modelo, serie o color" aria-label="Buscar en el catálogo" autocomplete="off">
    <button class="btn ghost filt" type="button" aria-label="Ordenar y filtrar"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>Filtros <span class="fbadge" hidden></span></button>
  </div>
  <button class="btn primary pdf" type="button">Descargar catálogo en PDF</button>
  <div class="grid"></div>
  <div class="empty" hidden>Por ahora no hay productos disponibles.</div>
  <p class="note"></p>
  <script type="application/json" class="petus-data"><?= $json ?></script>
</div>
<div class="petus-sheet" id="petusSheet" hidden><div class="panel" role="dialog" aria-modal="true" aria-label="Ordenar y filtrar"></div></div>
<script>
(function(){
  var root = document.getElementById('petusCat'); if(!root) return;
  var D = JSON.parse(root.querySelector('.petus-data').textContent);
  var P = D.productos || [];
  var $ = function(s){ return root.querySelector(s); };
  var MXN = new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN',minimumFractionDigits:0,maximumFractionDigits:2});
  var CAR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M3 15v-3l2-1 2-4h8l3 4 3 1v3h-2"/><circle cx="7" cy="16" r="2"/><circle cx="17" cy="16" r="2"/><path d="M9 16h6"/></svg>';
  var PAGE = 36, limite = PAGE, orden = 'novedades', rangos = [];
  function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function norm(s){ return String(s == null ? '' : s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'').replace(/[^a-z0-9]+/g,' ').trim(); }
  function precio(p){ return (p.precio > 0) ? p.precio : null; }
  function antes(p){ return (p.precioAntes > 0 && precio(p) != null && p.precioAntes > p.precio) ? p.precioAntes : null; }
  function desc(p){ var a = antes(p); return a ? Math.round((1 - p.precio / a) * 100) : 0; }
  function fmt(v){ return MXN.format(v); }
  function score(p, q){
    var nq = norm(q), name = norm(p.nombre), hay = name + ' ' + norm(p.serie) + ' ' + norm(p.color);
    if(name === nq) return 3; if(name.indexOf(nq) === 0) return 2.5; if(hay.indexOf(nq) >= 0) return 2;
    var toks = nq.split(' '), words = hay.split(' '), hit = 0;
    toks.forEach(function(t){ if(words.some(function(w){ return w.indexOf(t) === 0 || (t.length > 3 && w.indexOf(t) >= 0); })) hit++; });
    return hit / toks.length;
  }
  function lista(){
    var q = $('.q').value.trim(), l;
    if(q){ l = P.map(function(p){ return [p, score(p,q)]; }).filter(function(x){ return x[1] >= .6; }).sort(function(a,b){ return b[1]-a[1]; }).map(function(x){ return x[0]; }); }
    else l = P.slice();
    if(rangos.length){ l = l.filter(function(p){ var v = precio(p); return v != null && rangos.some(function(k){ var r = k.split('-').map(Number); return v >= r[0] && v <= r[1]; }); }); }
    var pv = function(p){ var v = precio(p); return v == null ? Infinity : v; };
    if(orden === 'vendido') l.sort(function(a,b){ return (b.vendidos||0) - (a.vendidos||0) || b.creado - a.creado; });
    else if(orden === 'descuentos') l.sort(function(a,b){ return desc(b) - desc(a) || b.creado - a.creado; });
    else if(orden === 'bajo') l.sort(function(a,b){ return pv(a) - pv(b); });
    else if(orden === 'alto') l.sort(function(a,b){ return (precio(b)||-1) - (precio(a)||-1); });
    else if(!q) l.sort(function(a,b){ return b.creado - a.creado; });
    return l;
  }
  function card(p){
    var pr = precio(p), off = desc(p), st = p.cantidad;
    return '<article class="bl"><div class="band"><span>' + esc(p.serie || (p.hw ? 'Hot Wheels' : 'Coleccionable')) + '</span>' + (p.numero ? '<span class="num">' + esc(p.numero) + '</span>' : '') + '</div>' +
      '<div class="bubble">' + (off ? '<span class="off">-' + off + '%</span>' : '') + (p.img ? '<img src="' + esc(p.img) + '" alt="' + esc(p.nombre) + '" loading="lazy" decoding="async" width="420" height="420">' : CAR) + '<span class="own own-' + (p.dueno === 'L' ? 'L' : 'V') + '">' + (p.dueno === 'L' ? 'L' : 'V') + '</span></div>' +
      '<div class="body"><div class="name">' + esc(p.nombre) + '</div>' + ([p.color, p.anio].filter(Boolean).length ? '<div class="meta">' + [p.color, p.anio].filter(Boolean).map(esc).join(' · ') + '</div>' : '') +
      '<div class="foot">' + (pr != null ? '<span>' + (antes(p) ? '<span class="was">' + fmt(antes(p)) + '</span>' : '') + '<span class="tag"><b>' + fmt(pr) + '</b></span></span>' : '<span class="tag ask"><b>Precio a consultar</b></span>') +
      '<span class="stock">' + (st === 1 ? 'Última pieza' : st + ' disponibles') + '</span></div></div></article>';
  }
  var obs = ('IntersectionObserver' in window) ? new IntersectionObserver(function(es){ es.forEach(function(e){ if(e.isIntersecting){ obs.unobserve(e.target); limite += PAGE; render(); } }); }, {rootMargin:'400px'}) : null;
  function render(){
    var l = lista(), total = l.length, vis = l.slice(0, limite);
    var nf = (orden !== 'novedades' ? 1 : 0) + rangos.length, b = $('.fbadge');
    b.hidden = !nf; b.textContent = nf;
    $('.grid').innerHTML = vis.map(card).join('') + (total > vis.length ? '<button class="btn ghost more" type="button">Ver más (' + (total - vis.length) + ')</button>' : '') || (P.length ? '<p class="note">Nada coincide con la búsqueda o los filtros.</p>' : '');
    var m = $('.more'); if(m){ m.onclick = function(){ limite += PAGE; render(); }; if(obs) obs.observe(m); }
  }
  // portada
  $('.count').textContent = P.length + ' modelo' + (P.length === 1 ? '' : 's') + ' disponible' + (P.length === 1 ? '' : 's');
  if(D.contacto){ var c = $('.contacto'); c.hidden = false; c.textContent = D.contacto; }
  $('.empty').hidden = P.length > 0;
  $('.note').textContent = 'Precios en pesos mexicanos (MXN).' + (D.fecha ? ' Actualizado el ' + D.fecha + '.' : '');
  $('.q').addEventListener('input', function(){ limite = PAGE; render(); });
  // hoja de ordenar y filtrar
  var sheet = document.getElementById('petusSheet'); document.body.appendChild(sheet);
  function rangosPrecio(){
    var ps = P.map(precio).filter(function(v){ return v != null; }).sort(function(a,b){ return a-b; });
    var uniq = ps.filter(function(v,i){ return ps.indexOf(v) === i; }); if(uniq.length < 2) return [];
    var groups = [], cur = null, acc = 0, step = ps.length / 3;
    uniq.forEach(function(v){ if(!cur) cur = {min:v,max:v}; cur.max = v; acc += ps.filter(function(x){ return x === v; }).length; if(groups.length < 2 && acc >= step * (groups.length + 1)){ groups.push(cur); cur = null; } });
    if(cur) groups.push(cur);
    return groups.map(function(g){ return { key: g.min + '-' + g.max, label: g.min === g.max ? fmt(g.min) : fmt(g.min) + ' - ' + fmt(g.max), n: ps.filter(function(v){ return v >= g.min && v <= g.max; }).length }; });
  }
  var ORD = [['vendido','Lo más vendido'],['novedades','Novedades'],['descuentos','Descuentos'],['bajo','Precio más bajo'],['alto','Precio más alto']];
  function cerrar(){ sheet.hidden = true; document.documentElement.style.overflow = ''; }
  function abrir(){
    var rs = rangosPrecio(), panel = sheet.querySelector('.panel');
    panel.innerHTML = '<div class="grab"></div><div class="head"><button class="x" type="button" aria-label="Cerrar">✕</button></div>' +
      '<h3>Ordenar productos por</h3>' + ORD.map(function(o){ return '<label class="opt"><span>' + o[1] + '</span><input type="radio" name="petusOrden" value="' + o[0] + '"' + (orden === o[0] ? ' checked' : '') + '></label>'; }).join('') +
      '<hr><h3>Filtrar por precio</h3>' + (rs.length ? rs.map(function(r){ return '<label class="opt"><span>' + r.label + ' <small>(' + r.n + ')</small></span><input type="checkbox" name="petusRango" value="' + r.key + '"' + (rangos.indexOf(r.key) >= 0 ? ' checked' : '') + '></label>'; }).join('') : '<p>Sin precios para filtrar.</p>') +
      '<div class="foot"><button class="btn outline limpiar" type="button">Limpiar filtros</button><button class="btn dark aplicar" type="button">Filtrar</button></div>';
    sheet.hidden = false; document.documentElement.style.overflow = 'hidden';
    panel.querySelector('.x').onclick = cerrar;
    panel.querySelector('.limpiar').onclick = function(){ orden = 'novedades'; rangos = []; limite = PAGE; cerrar(); render(); };
    panel.querySelector('.aplicar').onclick = function(){
      var o = panel.querySelector('input[name="petusOrden"]:checked'); orden = o ? o.value : 'novedades';
      rangos = Array.prototype.map.call(panel.querySelectorAll('input[name="petusRango"]:checked'), function(i){ return i.value; });
      limite = PAGE; cerrar(); render(); root.scrollIntoView({behavior:'smooth', block:'start'});
    };
  }
  sheet.addEventListener('click', function(e){ if(e.target === sheet) cerrar(); });
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && !sheet.hidden) cerrar(); });
  $('.filt').onclick = abrir;
  // PDF
  function cargar(src){ return new Promise(function(ok, ko){ var t = document.createElement('script'); t.src = src; t.onload = ok; t.onerror = ko; document.head.appendChild(t); }); }
  function latin(t){ return String(t == null ? '' : t).replace(/[‘’]/g,"'").replace(/[“”]/g,'"').replace(/[–—]/g,'-').replace(/…/g,'...').replace(/[^\x00-\xFF]/g,''); }
  function imagen(url){
    return new Promise(function(ok){
      if(!url) return ok(null);
      var im = new Image(); im.crossOrigin = 'anonymous';
      im.onload = function(){ var k = Math.min(1, 360 / Math.max(im.naturalWidth, im.naturalHeight)); var cv = document.createElement('canvas'); cv.width = Math.round(im.naturalWidth*k); cv.height = Math.round(im.naturalHeight*k); var g = cv.getContext('2d'); g.fillStyle = '#fff'; g.fillRect(0,0,cv.width,cv.height); g.drawImage(im,0,0,cv.width,cv.height); try{ ok({url: cv.toDataURL('image/jpeg',.78), w: cv.width, h: cv.height}); }catch(e){ ok(null); } };
      im.onerror = function(){ ok(null); }; im.src = url;
    });
  }
  var haciendo = false;
  $('.pdf').onclick = function(){
    if(haciendo) return; var btn = $('.pdf'), list = lista(); if(!list.length) return;
    haciendo = true; btn.disabled = true;
    var label = function(t){ btn.innerHTML = '<span class="spin"></span> ' + t; };
    label('Preparando PDF…');
    (window.jspdf ? Promise.resolve() : cargar('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js')).then(function(){
      var imgs = new Array(list.length), i = 0, hechos = 0;
      function worker(){ if(i >= list.length) return Promise.resolve(); var k = i++; return imagen(list[k].img).then(function(r){ imgs[k] = r; hechos++; if(hechos % 5 === 0) label('Preparando fotos ' + hechos + '/' + list.length + '…'); return worker(); }); }
      return Promise.all([worker(),worker(),worker(),worker(),worker(),worker()]).then(function(){ return imgs; });
    }).then(function(imgs){
      label('Armando PDF…');
      var doc = new window.jspdf.jsPDF({unit:'mm', format:'letter'});
      var W = 215.9, H = 279.4, M = 12, G = 5, COLS = 3, cw = (W - 2*M - (COLS-1)*G) / COLS, ch = 79, imgH = 45;
      var BLUE = [24,70,196], FLAME = [255,178,26], INK = [21,24,28], MUTED = [91,99,109], LINE = [214,218,224], OK = [23,128,74];
      var hasC = !!D.contacto, CH = hasC ? 58 : 50, L = M + 10;
      doc.setFillColor.apply(doc, BLUE); doc.roundedRect(M, M, W - 2*M, CH, 5, 5, 'F');
      doc.setDrawColor(70,110,215); doc.setLineWidth(3.2);
      for(var sx = W - M - 58; sx < W - M + 30; sx += 9){ var x1 = Math.max(sx, W - M - 58), x2 = Math.min(sx + 30, W - M - 7); if(x2 > x1) doc.line(x1, M + CH - 1 - (x1 - sx) * (CH - 2) / 30, x2, M + CH - 1 - (x2 - sx) * (CH - 2) / 30); }
      doc.setLineWidth(.2);
      doc.setTextColor(214,224,250); doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setCharSpace(1.6); doc.text('CATÁLOGO', L, M + 11); doc.setCharSpace(0);
      var ty = M + 28; doc.setTextColor(255,255,255); doc.setFontSize(34); doc.text('JUGUETERÍA PETUS', L, ty); ty += 10;
      if(hasC){ doc.setFont('helvetica','normal'); doc.setFontSize(10.5); doc.text(doc.splitTextToSize(latin(D.contacto), W - 2*M - 70).slice(0,1), L, ty); ty += 5.5; }
      var pill = list.length + ' modelo' + (list.length === 1 ? '' : 's') + ' disponible' + (list.length === 1 ? '' : 's');
      doc.setFont('helvetica','bold'); doc.setFontSize(11); var pw2 = doc.getTextWidth(pill) + 12;
      doc.setFillColor(18,52,150); doc.roundedRect(L, ty - 1, pw2, 9, 4.5, 4.5, 'F'); doc.setTextColor(255,255,255); doc.text(pill, L + 6, ty + 5.1);
      doc.setFont('helvetica','normal'); doc.setFontSize(8.5); doc.setTextColor.apply(doc, MUTED);
      doc.text(latin('Actualizado el ' + new Intl.DateTimeFormat('es-MX',{day:'numeric',month:'long',year:'numeric'}).format(new Date())), W - M, M + CH + 6, {align:'right'});
      var y = M + CH + 11, col = 0, page = 1;
      var footer = function(){ doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor.apply(doc, MUTED); doc.text('Precios en pesos mexicanos (MXN) · Página ' + page, W/2, H - 6, {align:'center'}); };
      list.forEach(function(p, idx){
        if(y + ch > H - 12){ footer(); doc.addPage(); page++; y = M; col = 0; }
        var x = M + col * (cw + G);
        doc.setDrawColor.apply(doc, LINE); doc.setFillColor(255,255,255); doc.roundedRect(x, y, cw, ch, 3, 3, 'FD');
        doc.setFillColor.apply(doc, BLUE); doc.roundedRect(x, y, cw, 8, 3, 3, 'F'); doc.rect(x, y + 4, cw, 4, 'F');
        doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(6.5);
        var num = latin(p.numero || ''), serie = latin(p.serie || (p.hw ? 'Hot Wheels' : 'Coleccionable')).toUpperCase();
        var maxS = cw - 6 - (num ? doc.getTextWidth(num) + 7 : 0); while(serie.length > 3 && doc.getTextWidth(serie) > maxS) serie = serie.slice(0, -2);
        doc.text(serie, x + 3, y + 5.3);
        if(num){ var nw = doc.getTextWidth(num) + 4; doc.setFillColor.apply(doc, FLAME); doc.roundedRect(x + cw - nw - 2.5, y + 1.8, nw, 4.4, 2.2, 2.2, 'F'); doc.setTextColor.apply(doc, INK); doc.text(num, x + cw - nw/2 - 2.5, y + 5, {align:'center'}); }
        var bx = x + 3, by = y + 10.5, bw = cw - 6;
        doc.setFillColor(245,246,248); doc.roundedRect(bx, by, bw, imgH, 3, 3, 'F');
        var im = imgs[idx]; if(im){ var k = Math.min((bw - 3) / im.w, (imgH - 3) / im.h), iw = im.w * k, ih = im.h * k; doc.addImage(im.url, 'JPEG', bx + (bw - iw)/2, by + (imgH - ih)/2, iw, ih, undefined, 'FAST'); }
        var dn = p.dueno === 'L' ? 'L' : 'V'; doc.setFillColor.apply(doc, dn === 'L' ? [194,24,91] : [15,118,110]); doc.circle(bx + bw - 4.5, by + imgH - 4.5, 3.2, 'F'); doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.text(dn, bx + bw - 4.5, by + imgH - 3.3, {align:'center'});
        var off = desc(p); if(off){ doc.setFillColor(192,53,42); doc.roundedRect(bx + 1.5, by + 1.5, 13, 5, 2.5, 2.5, 'F'); doc.setTextColor(255,255,255); doc.setFontSize(7); doc.text('-' + off + '%', bx + 8, by + 5, {align:'center'}); }
        doc.setTextColor.apply(doc, INK); doc.setFont('helvetica','bold'); doc.setFontSize(9.5);
        doc.text(doc.splitTextToSize(latin(p.nombre).toUpperCase(), cw - 6).slice(0, 2), x + 3, by + imgH + 5.5);
        var py = y + ch - 6, pr = precio(p); doc.setFontSize(12);
        var ptxt = pr != null ? latin(fmt(pr)) : 'Precio a consultar'; if(pr == null) doc.setFontSize(8);
        var pw = doc.getTextWidth(ptxt) + 6; doc.setFillColor.apply(doc, pr != null ? FLAME : [235,237,240]);
        doc.lines([[pw,0],[-1.5,6.5],[-pw,0],[1.5,-6.5]], x + 3.5, py - 4.6, [1,1], 'F', true);
        doc.setTextColor.apply(doc, INK); doc.text(ptxt, x + 6, py);
        if(antes(p)){ doc.setFont('helvetica','normal'); doc.setFontSize(7); doc.setTextColor.apply(doc, MUTED); var at = latin(fmt(antes(p))); doc.text(at, x + 3, py - 6.2); doc.setDrawColor.apply(doc, MUTED); doc.line(x + 3, py - 7.2, x + 3 + doc.getTextWidth(at), py - 7.2); }
        doc.setFont('helvetica','bold'); doc.setFontSize(7); doc.setTextColor.apply(doc, OK);
        doc.text(p.cantidad === 1 ? 'Última pieza' : p.cantidad + ' disponibles', x + cw - 3, py, {align:'right'});
        col++; if(col === COLS){ col = 0; y += ch + G; }
      });
      footer();
      doc.save('Catalogo-Jugueteria-PETUS.pdf');
    }).catch(function(){ alert('No se pudo crear el PDF. Intenta de nuevo.'); }).then(function(){ haciendo = false; btn.disabled = false; btn.textContent = 'Descargar catálogo en PDF'; });
  };
  render();
})();
</script>
</body>
</html>
