/* Inventario Hot Wheels — panel (administrador y vendedores).
   Misma lógica que la app de claude.ai, ahora contra la API de CodeIgniter. */
(function(){
"use strict";
const P = window.PETUS;
const ADMIN = P.yo.rol === "admin";
const $ = (s, r=document) => r.querySelector(s);
const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
const F = id => document.getElementById(id);
const CAR_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M3 15v-3l2-1 2-4h8l3 4 3 1v3h-2"/><circle cx="7" cy="16" r="2"/><circle cx="17" cy="16" r="2"/><path d="M9 16h6"/></svg>';

const S = { productos:[], ventas:[], loaded:false, filtro:"todos", nuevo:null, invLimit:36, carrito:[] };
const PAGE = 36;

/* ---------- API ---------- */
const CSRF_H = document.querySelector('meta[name="csrf-header"]').content;
const CSRF_T = document.querySelector('meta[name="csrf-token"]').content;
async function api(ruta, opts){
  opts = opts || {};
  const init = { method: opts.method || (opts.body || opts.json ? "POST" : "GET"), headers: { "X-Requested-With": "XMLHttpRequest" }, credentials: "same-origin" };
  if(init.method !== "GET") init.headers[CSRF_H] = CSRF_T;
  if(opts.json){ init.headers["Content-Type"] = "application/json"; init.body = JSON.stringify(opts.json); }
  else if(opts.body) init.body = opts.body;
  let r;
  try{ r = await fetch(P.base + ruta, init); }catch(_){ throw { error: "Sin conexión. Revisa tu internet." }; }
  if(r.status === 401){ location.href = P.base + "entrar"; throw { error: "Tu sesión terminó." }; }
  let data = null; try{ data = await r.json(); }catch(_){}
  if(!r.ok) throw (data && data.error) ? data : { error: "Error del servidor (" + r.status + ")." };
  return data;
}

/* ---------- utils ---------- */
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
const norm = s => String(s ?? "").toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g,"").replace(/[^a-z0-9]+/g," ").trim();
const thumb = (p, cls="") => p && p.mini ? `<img class="${cls}" src="${esc(p.mini)}" alt="" loading="lazy" decoding="async">` : `<div class="noimg ${cls}">${CAR_SVG}</div>`;
const byId = id => S.productos.find(p => p.id === Number(id));
const cant = p => Math.max(0, Number(p && p.cantidad) || 0);
const MXN = new Intl.NumberFormat("es-MX",{style:"currency",currency:"MXN",minimumFractionDigits:0,maximumFractionDigits:2});
const precioDe = p => { const v = Number(p && p.precio); return isFinite(v) && v > 0 ? v : null; };
const antesDe = p => { const a = Number(p && p.precioAntes), v = precioDe(p); return isFinite(a) && a > 0 && v != null && a > v ? a : null; };
const duenoDe = p => (p && p.dueno === "L") ? "L" : "V";
const ownTag = p => `<span class="own own-${duenoDe(p)}" title="${duenoDe(p) === "L" ? "Lupita" : "Victor"}">${duenoDe(p)}</span>`;
const fmtPrecio = v => v == null ? "" : MXN.format(v);
function parsePrecio(t){ const c = String(t ?? "").replace(/[^0-9.,]/g,"").replace(/,(?=\d{3}\b)/g,"").replace(",","."); const v = parseFloat(c); return isFinite(v) && v > 0 ? Math.round(v*100)/100 : null; }
const meta = p => [p.serie, p.numero, p.color, p.anio].filter(Boolean).map(esc).join(" · ");

function score(p, q){
  const nq = norm(q); if(!nq) return 0;
  const name = norm(p.nombre), hay = name + " " + norm(p.serie) + " " + norm(p.color);
  if(name === nq) return 3;
  if(name.startsWith(nq)) return 2.5;
  if(hay.includes(nq)) return 2;
  const toks = nq.split(" "), words = hay.split(" ");
  let hit = 0;
  for(const t of toks){ if(words.some(w => w.startsWith(t) || (t.length>3 && w.includes(t)))) hit++; }
  return hit / toks.length;
}
function search(q, list=S.productos){
  return list.map(p => [p, score(p,q)]).filter(x => x[1] >= 0.6).sort((a,b) => b[1]-a[1] || norm(a[0].nombre).localeCompare(norm(b[0].nombre))).map(x => x[0]);
}

/* ---------- toast / sheet ---------- */
let toastT;
function toast(msg, actLabel, act){
  F("toastTxt").textContent = msg;
  const b = F("toastAct");
  b.hidden = !actLabel; b.textContent = actLabel || "";
  b.onclick = () => { F("toast").hidden = true; act && act(); };
  F("toast").hidden = false;
  clearTimeout(toastT); toastT = setTimeout(() => F("toast").hidden = true, actLabel ? 8000 : 3500);
}
const errMsg = e => (e && e.error) || "Algo salió mal. Intenta de nuevo.";
function openSheet(html){ F("panel").innerHTML = '<div class="grab"></div>' + html; F("sheet").hidden = false; return F("panel"); }
function closeSheet(){ F("sheet").hidden = true; F("panel").innerHTML = ""; }
F("sheet").addEventListener("click", e => { if(e.target.id === "sheet") closeSheet(); });
function stepper(root){
  $$("[data-step]", root).forEach(b => b.addEventListener("click", () => {
    const inp = b.parentElement.querySelector("input");
    const min = Number(inp.min || 0), max = inp.max ? Number(inp.max) : Infinity;
    inp.value = Math.min(max, Math.max(min, (Number(inp.value) || 0) + Number(b.dataset.step)));
    inp.dispatchEvent(new Event("input"));
  }));
}
stepper(document);

/* ---------- routing ---------- */
const VIEWS = ADMIN ? ["inventario","agregar","vender"] : ["vender","agregar"];
function route(){
  let v = location.hash.slice(1);
  if(!VIEWS.includes(v)) v = VIEWS[0];
  ["inventario","agregar","vender"].forEach(x => F("v-"+x).hidden = x !== v);
  $$(".tab").forEach(t => { if(t.dataset.tab === v) t.setAttribute("aria-current","page"); else t.removeAttribute("aria-current"); });
  if(v === "vender") setTimeout(() => F("venQ").focus(), 50);
  if(v === "vender" && S.jcarg && !S.jornada) pedirInicio(false);
  window.scrollTo(0,0);
}
window.addEventListener("hashchange", route);

function medirCabecera(){
  const h = $(".top");
  document.documentElement.style.setProperty("--top-h", (h ? h.offsetHeight : 0) + "px");
}
window.addEventListener("resize", medirCabecera);
if("ResizeObserver" in window) new ResizeObserver(medirCabecera).observe($(".top"));

/* ---------- datos ---------- */
async function cargarProductos(){
  try{
    S.productos = await api("api/productos");
    S.loaded = true; F("globalBanner").hidden = true;
    renderAll();
  }catch(e){ const b = F("globalBanner"); b.hidden = false; b.textContent = errMsg(e); }
}
async function cargarVentas(){
  try{ const r = await api("api/jornada"); S.jornada = r.jornada; renderJornada(); }catch(_){}
}
// refresca cada 30 s para ver lo que vendieron otros
setInterval(() => { if(!document.hidden){ cargarProductos(); cargarVentas(); } }, 30000);

/* ---------- render ---------- */
function renderStats(){
  F("stModelos").textContent = S.productos.length;
  F("stPiezas").textContent = S.productos.reduce((a,p) => a + cant(p), 0);
}
function renderInventario(){
  if(!ADMIN) return;
  const q = F("invQ").value;
  let list = q.trim() ? search(q) : S.productos.slice().sort((a,b) => (b.creado||0) - (a.creado||0));
  if(S.filtro === "pendientes") list = list.filter(p => p.pendiente || !p.nombre);
  if(S.filtro === "repetidos") list = list.filter(p => cant(p) > 1);
  if(S.filtro === "agotados") list = list.filter(p => cant(p) === 0);
  if(S.filtro === "sinprecio") list = list.filter(p => precioDe(p) == null);
  if(S.filtro === "L" || S.filtro === "V") list = list.filter(p => duenoDe(p) === S.filtro);
  const pend = S.productos.filter(p => p.pendiente || !p.nombre).length;
  F("invLead").textContent = pend ? `${pend} sin nombre. Toca cada uno para escribir cómo registrarlo.` : "Toca un carrito para editar nombre, precio, cantidad o notas.";
  F("invEmpty").hidden = !(S.loaded && S.productos.length === 0);
  const total = list.length; list = list.slice(0, S.invLimit);
  F("invCards").innerHTML = list.map(p => `
    <button class="card" data-id="${p.id}">
      <div class="ph">${thumb(p)}${(p.pendiente||!p.nombre) ? '<span class="tag">Sin nombre</span>' : ""}<span class="badge ${cant(p)===0?"zero":""}">×${cant(p)}</span>${ownTag(p)}</div>
      <div class="cap"><b>${esc(p.nombre || "Sin nombre")}</b><small>${meta(p) || "&nbsp;"}</small>${precioDe(p) != null ? `<span class="price">${fmtPrecio(precioDe(p))}</span>` : '<small>Sin precio</small>'}</div>
    </button>`).join("") + (total > list.length ? `<button class="btn ghost more" data-more="inv">Ver más (${total - list.length})</button>` : "") || (S.productos.length ? '<p class="lead">Nada coincide con el filtro.</p>' : "");
  observarMas();
}
let masObs = null;
function observarMas(){
  if(!("IntersectionObserver" in window)) return;
  if(!masObs) masObs = new IntersectionObserver(ents => ents.forEach(en => {
    if(!en.isIntersecting) return; masObs.unobserve(en.target); S.invLimit += PAGE; renderInventario();
  }), { rootMargin:"400px" });
  masObs.disconnect();
  $$("[data-more]").forEach(b => { if(b.offsetParent) masObs.observe(b); });
}
if(ADMIN){
  F("invCards").addEventListener("click", e => {
    if(e.target.closest("[data-more]")){ S.invLimit += PAGE; renderInventario(); return; }
    const c = e.target.closest(".card"); if(c) openEdit(c.dataset.id);
  });
  F("invQ").addEventListener("input", () => { S.invLimit = PAGE; renderInventario(); });
  F("invFiltros").addEventListener("click", e => {
    const c = e.target.closest(".chip"); if(!c) return;
    S.filtro = c.dataset.f; S.invLimit = PAGE; $$("#invFiltros .chip").forEach(x => x.classList.toggle("on", x === c)); renderInventario();
  });
}
/* ---------- jornada de venta ---------- */
const HORA = new Intl.DateTimeFormat("es-MX",{hour:"2-digit",minute:"2-digit"});
const DIA = new Intl.DateTimeFormat("es-MX",{weekday:"long",day:"numeric",month:"long"});
const DIAC = new Intl.DateTimeFormat("es-MX",{weekday:"short",day:"numeric",month:"short"});
const money = v => fmtPrecio(Number(v) || 0) || "$0";
function renderJornada(){
  const j = S.jornada;
  F("jorSin").hidden = !!j; F("jorCon").hidden = !j;
  F("jorStrip").hidden = !j;
  if(!j){ F("jorDesde").textContent = ""; return; }
  const mismoDia = new Date(j.inicio).toDateString() === new Date().toDateString();
  F("jorDesde").textContent = (mismoDia ? "Hoy" : DIAC.format(new Date(j.inicio))) + " desde " + HORA.format(new Date(j.inicio));
  F("jorFondo").textContent = money(j.fondo);
  F("jorTotal").textContent = money(j.total);
  F("jorCaja").textContent = money(j.total + j.fondo);
  F("jorPiezas").textContent = `${j.numVentas} venta${j.numVentas === 1 ? "" : "s"} · ${j.piezas} pieza${j.piezas === 1 ? "" : "s"}` + (mismoDia ? "" : " · Esta jornada empezó otro día: finalízala.");
  F("jorStrip").textContent = `Jornada abierta · vendido ${money(j.total)}`;
  F("salesList").innerHTML = j.ventas.length ? j.ventas.map(v => `<div class="sale"><span><b>${v.cantidad}×</b> ${esc(v.nombre)}${v.total ? ` · <b>${fmtPrecio(v.total)}</b>` : ""}</span><time>${HORA.format(new Date(v.fecha))}</time></div>`).join("")
    + `<div class="sale"><span><b>Total vendido</b></span><b>${money(j.total)}</b></div>` : '<p class="lead" style="padding-bottom:12px">Aún no hay ventas en esta jornada.</p>';
}
let preguntado = false;
function pedirInicio(forzar){
  if(S.jornada || (preguntado && !forzar)) return;
  preguntado = true;
  const panel = openSheet(`<h2>Hola, ${esc(P.yo.nombre)}</h2>
    <p class="q">¿Llevas cambio para empezar la jornada?</p>
    <div class="choice" id="chIni">
      <button class="btn primary" type="button" id="chSi">Sí, llevo cambio</button>
      <button class="btn ghost" type="button" id="chNo">Sin cambio</button>
    </div>
    <div class="view" id="chMonto" hidden>
      <label class="field"><span>¿Cuánto llevas de cambio?</span><div class="money"><span>$</span><input id="chFondo" inputmode="decimal" placeholder="0"></div></label>
      <button class="btn primary block" type="button" id="chOk">Iniciar jornada</button>
      <button class="btn text" type="button" id="chBack">Volver</button>
    </div>
    ${ADMIN ? '<button class="btn text" type="button" id="chLuego">Ahora no (solo voy a administrar)</button>' : ""}`);
  const abrir = async fondo => {
    try{ const r = await api("api/jornada/abrir", { json: { fondo } }); S.jornada = r.jornada; renderJornada(); closeSheet(); toast(fondo > 0 ? `Jornada iniciada con ${money(fondo)} de cambio.` : "Jornada iniciada sin cambio."); }
    catch(e){ toast(errMsg(e)); }
  };
  F("chSi").onclick = () => { F("chIni").hidden = true; F("chMonto").hidden = false; F("chFondo").focus(); };
  F("chNo").onclick = () => abrir(0);
  F("chBack").onclick = () => { F("chIni").hidden = false; F("chMonto").hidden = true; };
  F("chOk").onclick = () => {
    const v = parsePrecio(F("chFondo").value);
    if(v == null){ toast("Escribe cuánto llevas de cambio, o elige “Sin cambio”."); return; }
    abrir(v);
  };
  if(F("chLuego")) F("chLuego").onclick = closeSheet;
}
F("jorIniciar").onclick = () => pedirInicio(true);
F("jorFin").onclick = async () => {
  await cargarVentas();
  const j = S.jornada; if(!j) return;
  const panel = openSheet(`<h2>Finalizar jornada</h2>
    <p class="lead">${esc(P.yo.nombre)} · ${DIA.format(new Date(j.inicio))}, desde ${HORA.format(new Date(j.inicio))}</p>
    <div class="jor-nums"><div><span>Cambio</span><b>${money(j.fondo)}</b></div><div><span>Vendido</span><b>${money(j.total)}</b></div><div><span>En caja</span><b>${money(j.total + j.fondo)}</b></div></div>
    <p class="lead">${j.numVentas} venta${j.numVentas === 1 ? "" : "s"} · ${j.piezas} pieza${j.piezas === 1 ? "" : "s"}. Cuenta el dinero: deben ser <b>${money(j.fondo)}</b> de cambio más <b>${money(j.total)}</b> de lo vendido.</p>
    ${S.carrito.length ? '<div class="notice warn"><p>Tienes una venta sin registrar en “Venta actual”. Regístrala o cancélala antes de finalizar.</p></div>' : ""}
    <p class="q">¿Seguro que quieres finalizar tu jornada?</p>
    <div class="row"><button class="btn ghost" type="button" id="finNo">No, seguir vendiendo</button><button class="btn dark" type="button" id="finSi">Sí, finalizar</button></div>`);
  F("finNo").onclick = closeSheet;
  F("finSi").onclick = async () => {
    F("finSi").disabled = true; F("finSi").textContent = "Finalizando…";
    try{
      const r = await api("api/jornada/cerrar", { json: {} });
      S.jornada = null; preguntado = true; renderJornada();
      const tg = r.avisos && r.avisos.telegram, em = r.avisos && r.avisos.email;
      const aviso = tg ? "Se envió el resumen por Telegram." : (tg === false ? "No se pudo enviar el aviso por Telegram (revisa Avisos de pedidos)." : (em ? "Se envió el resumen por correo." : "Quedó guardada en el historial."));
      const jj = r.jornada;
      openSheet(`<h2>Jornada finalizada</h2>
        <div class="jor-nums"><div><span>Cambio</span><b>${money(jj.fondo)}</b></div><div><span>Vendido</span><b>${money(jj.total)}</b></div><div><span>Entrega</span><b>${money(jj.total + jj.fondo)}</b></div></div>
        <p class="lead">${aviso}</p>
        <a class="btn primary block" href="${P.base}salir">Salir</a>
        <button class="btn ghost block" type="button" id="finNueva">Iniciar otra jornada</button>`);
      F("finNueva").onclick = () => pedirInicio(true);
    }catch(e){ toast(errMsg(e)); F("finSi").disabled = false; F("finSi").textContent = "Sí, finalizar"; }
  };
};

/* ---------- historial por día ---------- */
let histDias = 7;
async function abrirHistorial(){
  let lista;
  try{ lista = await api("api/jornadas?dias=" + histDias); }catch(e){ toast(errMsg(e)); return; }
  const dias = {};
  lista.forEach(j => { const k = new Date(j.inicio).toDateString(); (dias[k] = dias[k] || []).push(j); });
  const totalPer = lista.reduce((a, j) => a + j.total, 0);
  const panel = openSheet(`<h2>Historial de ventas</h2>
    <p class="lead">${ADMIN ? "Todas las jornadas de todos los vendedores." : "Tus jornadas."} Toca una para ver sus ventas.</p>
    <div class="chips">${[7,31,90].map(d => `<button class="chip${d === histDias ? " on" : ""}" data-dias="${d}" type="button">${d === 7 ? "7 días" : d === 31 ? "Mes" : "3 meses"}</button>`).join("")}</div>
    <div class="t-total"><span>Total vendido en el periodo</span><b style="font-size:26px">${money(totalPer)}</b></div>
    ${Object.keys(dias).length ? Object.keys(dias).map(k => {
      const js = dias[k], sum = js.reduce((a, j) => a + j.total, 0);
      return `<div class="hist-day"><b>${DIA.format(new Date(js[0].inicio))}</b><span>${money(sum)}</span></div>` + js.map(j => `
        <button class="hist-row" type="button" data-j="${j.id}">
          <span><b>${esc(j.vendedor)}</b><br><small>${HORA.format(new Date(j.inicio))} – ${j.fin ? HORA.format(new Date(j.fin)) : "abierta"} · ${j.piezas} pieza${j.piezas === 1 ? "" : "s"}</small></span>
          <span class="r">${money(j.total)}<small>cambio ${money(j.fondo)}</small></span>
        </button>`).join("");
    }).join("") : '<div class="empty"><b>Sin jornadas en este periodo</b></div>'}
    <button class="btn text" type="button" id="histClose">Cerrar</button>`);
  F("histClose").onclick = closeSheet;
  $$("[data-dias]", panel).forEach(b => b.onclick = () => { histDias = Number(b.dataset.dias); abrirHistorial(); });
  $$("[data-j]", panel).forEach(b => b.onclick = () => verJornada(b.dataset.j));
}
async function verJornada(id){
  let j; try{ j = await api("api/jornadas/" + id); }catch(e){ toast(errMsg(e)); return; }
  openSheet(`<h2>${esc(j.vendedor)}</h2>
    <p class="lead">${DIA.format(new Date(j.inicio))} · ${HORA.format(new Date(j.inicio))} – ${j.fin ? HORA.format(new Date(j.fin)) : "abierta"}</p>
    <div class="jor-nums"><div><span>Cambio</span><b>${money(j.fondo)}</b></div><div><span>Vendido</span><b>${money(j.total)}</b></div><div><span>En caja</span><b>${money(j.total + j.fondo)}</b></div></div>
    <p class="lead">${j.numVentas} venta${j.numVentas === 1 ? "" : "s"} · ${j.piezas} pieza${j.piezas === 1 ? "" : "s"}</p>
    <div>${j.ventas.map(v => `<div class="sale"><span><b>${v.cantidad}×</b> ${esc(v.nombre)}${v.total ? ` · <b>${fmtPrecio(v.total)}</b>` : ""}</span><time>${HORA.format(new Date(v.fecha))}</time></div>`).join("") || '<p class="lead">Sin ventas.</p>'}</div>
    <div class="row"><button class="btn ghost" type="button" id="jvBack">Volver al historial</button><button class="btn text" type="button" id="jvClose">Cerrar</button></div>`);
  F("jvBack").onclick = abrirHistorial;
  F("jvClose").onclick = closeSheet;
}
F("jorHist").onclick = abrirHistorial;
function renderAll(){ renderStats(); renderInventario(); renderVentasRes(); if(S.loaded) renderCarrito(); if(S.nuevo) renderNotices(); }

/* ---------- fotos: comprimir y recortar ---------- */
async function decode(b){
  try{ return await createImageBitmap(b, {imageOrientation:"from-image"}); }
  catch(_){ return await new Promise((ok, ko) => { const i = new Image(); i.onload = () => ok(i); i.onerror = ko; i.src = URL.createObjectURL(b); }); }
}
async function compress(file, max){
  const src = await decode(file);
  const k = Math.min(1, (max || 1600) / Math.max(src.width, src.height));
  const c = document.createElement("canvas"); c.width = Math.round(src.width*k); c.height = Math.round(src.height*k);
  c.getContext("2d").drawImage(src, 0, 0, c.width, c.height);
  return await new Promise(ok => c.toBlob(ok, "image/jpeg", 0.86));
}
async function cropBlob(src, c){
  const img = await decode(src);
  const W = img.width, H = img.height;
  const sx = Math.round(c.x*W), sy = Math.round(c.y*H), sw = Math.max(1, Math.round(c.w*W)), sh = Math.max(1, Math.round(c.h*H));
  const cv = document.createElement("canvas"); cv.width = sw; cv.height = sh;
  cv.getContext("2d").drawImage(img, sx, sy, sw, sh, 0, 0, sw, sh);
  return await new Promise(ok => cv.toBlob(ok, "image/jpeg", 0.88));
}
// Editor: rectángulo con cuatro esquinas sobre la foto. Devuelve {x,y,w,h} en fracciones, null (foto completa) o undefined (cancelar).
function abrirRecortador(url, inicial){
  return new Promise(resolve => {
    let done = false;
    const fin = v => { if(done) return; done = true; closeSheet(); resolve(v); };
    openSheet(`<h2>Recortar foto</h2><p class="lead">Arrastra las esquinas para dejar solo el producto.</p>
      <div class="cropper-wrap"><div class="cropper" id="cropper"><img src="${url}" alt=""><div class="crop-box" id="cropBox"><i data-h="nw"></i><i data-h="ne"></i><i data-h="sw"></i><i data-h="se"></i></div></div></div>
      <button class="btn primary block" id="cropOk" type="button">Listo</button>
      <div class="row"><button class="btn ghost" id="cropAll" type="button">Seleccionar todo</button><button class="btn text" id="cropNo" type="button">Cancelar</button></div>`);
    const wrap = F("cropper"), box = F("cropBox");
    let c = Object.assign({ x:0.08, y:0.08, w:0.84, h:0.84 }, inicial || {});
    const MIN = 0.06;
    const draw = () => { box.style.left = c.x*100+"%"; box.style.top = c.y*100+"%"; box.style.width = c.w*100+"%"; box.style.height = c.h*100+"%"; };
    draw();
    let drag = null;
    const start = e => { e.preventDefault(); drag = { h: e.target.dataset.h || "move", px: e.clientX, py: e.clientY, r: wrap.getBoundingClientRect(), c0: Object.assign({}, c) }; try{ e.target.setPointerCapture(e.pointerId); }catch(_){} };
    const move = e => {
      if(!drag) return;
      const dx = (e.clientX - drag.px) / drag.r.width, dy = (e.clientY - drag.py) / drag.r.height, o = drag.c0;
      let x1 = o.x, y1 = o.y, x2 = o.x + o.w, y2 = o.y + o.h;
      if(drag.h === "move"){ c = { x:Math.min(1 - o.w, Math.max(0, o.x + dx)), y:Math.min(1 - o.h, Math.max(0, o.y + dy)), w:o.w, h:o.h }; draw(); return; }
      if(drag.h.includes("w")) x1 = Math.min(x2 - MIN, Math.max(0, o.x + dx));
      if(drag.h.includes("e")) x2 = Math.max(x1 + MIN, Math.min(1, x2 + dx));
      if(drag.h.includes("n")) y1 = Math.min(y2 - MIN, Math.max(0, o.y + dy));
      if(drag.h.includes("s")) y2 = Math.max(y1 + MIN, Math.min(1, y2 + dy));
      c = { x:x1, y:y1, w:x2 - x1, h:y2 - y1 }; draw();
    };
    const end = () => { drag = null; };
    box.addEventListener("pointerdown", start);
    $$("#cropBox i").forEach(h => h.addEventListener("pointerdown", e => { e.stopPropagation(); start(e); }));
    [box, ...$$("#cropBox i")].forEach(el => { el.addEventListener("pointermove", move); el.addEventListener("pointerup", end); el.addEventListener("pointercancel", end); });
    F("cropOk").onclick = () => fin((c.w > 0.995 && c.h > 0.995) ? null : c);
    F("cropAll").onclick = () => { c = { x:0, y:0, w:1, h:1 }; draw(); };
    F("cropNo").onclick = () => fin(undefined);
  });
}

/* ---------- editar (admin) ---------- */
const pick = (pre, sel) => `<div class="field"><span>Dueño</span><div class="owner-pick" role="radiogroup">
  <label><input type="radio" name="${pre}Dueno" value="V"${sel === "V" ? " checked" : ""}><span class="own own-V">V</span>Victor</label>
  <label><input type="radio" name="${pre}Dueno" value="L"${sel === "L" ? " checked" : ""}><span class="own own-L">L</span>Lupita</label></div></div>`;
function formData(obj){ const fd = new FormData(); Object.entries(obj).forEach(([k,v]) => { if(v !== undefined && v !== null) fd.append(k, v); }); return fd; }
function reemplazar(p){ const i = S.productos.findIndex(x => x.id === p.id); if(i >= 0) S.productos[i] = p; else S.productos.unshift(p); renderAll(); }

function openEdit(id){
  const p = byId(id); if(!p) return;
  const panel = openSheet(`
    ${p.foto ? `<img class="big" src="${esc(p.foto)}" alt=""><button class="btn ghost" id="eCrop" type="button">Recortar foto</button>` : ""}
    <label class="btn ghost" for="eFoto">${p.foto ? "Cambiar foto" : "Agregar foto"}</label><input id="eFoto" type="file" accept="image/*" hidden>
    ${(p.pendiente||!p.nombre) ? `<div class="notice warn"><h3>Falta el nombre</h3><p>Escribe el nombre como aparece en el blister.</p></div>` : ""}
    <label class="field"><span>Nombre</span><input id="eNombre" class="inp big" value="${esc(p.nombre)}"></label>
    <div class="grid2">
      <label class="field"><span>Precio de venta</span><div class="money"><span>$</span><input id="ePrecio" inputmode="decimal" placeholder="0.00" value="${precioDe(p) ?? ""}"></div></label>
      <label class="field"><span>Precio antes (descuento)</span><div class="money"><span>$</span><input id="eAntes" inputmode="decimal" placeholder="—" value="${antesDe(p) ?? ""}"></div></label>
    </div>
    ${pick("e", duenoDe(p))}
    <div class="grid2">
      <label class="field"><span>Serie</span><input id="eSerie" class="inp" value="${esc(p.serie)}"></label>
      <label class="field"><span>Nº colección</span><input id="eNumero" class="inp" value="${esc(p.numero)}"></label>
      <label class="field"><span>Color</span><input id="eColor" class="inp" value="${esc(p.color)}"></label>
      <label class="field"><span>Año</span><input id="eAnio" class="inp" value="${esc(p.anio)}"></label>
    </div>
    <div class="field"><span>Existencia</span><div class="stepper"><button type="button" data-step="-1" aria-label="Menos">−</button><input id="eCant" type="number" min="0" value="${cant(p)}" inputmode="numeric" aria-label="Existencia"><button type="button" data-step="1" aria-label="Más">+</button></div></div>
    <label class="field"><span>Notas</span><textarea id="eNotas" class="inp">${esc(p.notas)}</textarea></label>
    <button class="btn primary block" id="eSave">Guardar cambios</button>
    <div class="row"><button class="btn ghost" id="eDel">Eliminar</button><button class="btn text" id="eClose">Cerrar</button></div>
    <div id="eConfirm" class="notice dup" hidden><p>¿Eliminar <b>${esc(p.nombre || "este producto")}</b> y su foto? No se puede deshacer.</p><div class="row"><button class="btn danger" id="eDelYes">Sí, eliminar</button><button class="btn ghost" id="eDelNo">No</button></div></div>
  `);
  stepper(panel);
  const datos = () => ({
    nombre: F("eNombre").value.trim(), precio: F("ePrecio").value, precioAntes: F("eAntes").value,
    dueno: ($('input[name="eDueno"]:checked') || {}).value || "V",
    serie: F("eSerie").value.trim(), numero: F("eNumero").value.trim(), color: F("eColor").value.trim(), anio: F("eAnio").value.trim(),
    cantidad: Math.max(0, parseInt(F("eCant").value,10) || 0), notas: F("eNotas").value.trim(),
  });
  const guardarFoto = async blob => {
    toast("Guardando foto…");
    try{ const fd = formData(datos()); fd.append("foto", blob, "foto.jpg"); reemplazar(await api("api/productos/" + p.id, { body: fd })); toast("Foto guardada"); setTimeout(() => openEdit(p.id), 200); }
    catch(e){ toast(errMsg(e)); }
  };
  F("eClose").onclick = closeSheet;
  F("eFoto").onchange = async e => {
    const f = e.target.files && e.target.files[0]; if(!f) return;
    try{
      const b = await compress(f); const url = URL.createObjectURL(b);
      const c = await abrirRecortador(url, null); URL.revokeObjectURL(url);
      if(c === undefined){ openEdit(p.id); return; }
      guardarFoto(c ? await cropBlob(b, c) : b);
    }catch(_){ toast("No pude abrir esa imagen."); }
  };
  if(F("eCrop")) F("eCrop").onclick = async () => {
    let orig; try{ const r = await fetch(p.foto); orig = await r.blob(); }catch(_){ toast("No pude abrir la foto guardada."); return; }
    const url = URL.createObjectURL(orig); const c = await abrirRecortador(url, null); URL.revokeObjectURL(url);
    if(!c){ openEdit(p.id); return; }
    guardarFoto(await cropBlob(orig, c));
  };
  F("eDel").onclick = () => { F("eConfirm").hidden = false; F("eConfirm").scrollIntoView({block:"nearest"}); };
  F("eDelNo").onclick = () => F("eConfirm").hidden = true;
  F("eDelYes").onclick = async () => {
    try{ await api("api/productos/" + p.id + "/eliminar", { body: new FormData() }); S.productos = S.productos.filter(x => x.id !== p.id); renderAll(); closeSheet(); toast("Eliminado"); }
    catch(e){ toast(errMsg(e)); }
  };
  F("eSave").onclick = async () => {
    const d = datos(); F("eSave").disabled = true;
    const otro = d.nombre && S.productos.find(x => x.id !== p.id && norm(x.nombre) === norm(d.nombre) && (!d.numero || !x.numero || norm(x.numero) === norm(d.numero)));
    try{ reemplazar(await api("api/productos/" + p.id, { body: formData(d) })); closeSheet(); toast(otro ? `Guardado. Ojo: ya existe otro “${otro.nombre}”.` : "Cambios guardados"); }
    catch(e){ toast(errMsg(e)); F("eSave").disabled = false; }
  };
}

/* ---------- agregar (admin y vendedores) ---------- */
const FIELDS = ["fNombre","fPrecio","fSerie","fNumero","fColor","fAnio","fNotas"];
{
  async function onPick(e){
    const file = e.target.files && e.target.files[0]; e.target.value = "";
    if(!file) return;
    let b; try{ b = await compress(file); }catch(_){ toast("No pude abrir esa imagen. Prueba con otra."); return; }
    iniciarNuevo(b);
    // propone recortar al producto en cuanto se toma la foto
    const url = URL.createObjectURL(b); const c = await abrirRecortador(url, null); URL.revokeObjectURL(url);
    if(c) aplicarRecorte(S.nuevo, c);
    F("fNombre").focus();
  }
  F("camIn").addEventListener("change", onPick);
  F("galIn").addEventListener("change", onPick);
  F("sinFoto").onclick = () => { iniciarNuevo(null); F("fNombre").focus(); };
  F("addRetake").onclick = () => F("camIn").click();
  F("addCancel").onclick = resetAdd;
  let dupT;
  ["fNombre","fNumero","fSerie","fColor"].forEach(f => F(f).addEventListener("input", () => { clearTimeout(dupT); dupT = setTimeout(checkLocalDup, 250); }));
  F("cropAdj").onclick = async () => {
    const n = S.nuevo; if(!n || !n.orig) return;
    const url = URL.createObjectURL(n.orig); const c = await abrirRecortador(url, n.crop); URL.revokeObjectURL(url);
    if(c !== undefined) aplicarRecorte(n, c);
  };
  F("cropFull").onclick = () => { if(S.nuevo) aplicarRecorte(S.nuevo, null); };
  F("addForm").addEventListener("submit", e => { e.preventDefault(); guardarNuevo(false); });
  F("addPend").onclick = () => guardarNuevo(true);
}
function iniciarNuevo(b){
  if(S.nuevo && S.nuevo.url) URL.revokeObjectURL(S.nuevo.url);
  S.nuevo = { orig:b, blob:b, crop:null, url: b ? URL.createObjectURL(b) : null, dupId:null, dupDecision:null, varianteId:null };
  FIELDS.forEach(f => F(f).value = ""); F("fCant").value = 1; F("fHW").value = "1";
  let ultimo = null; try{ ultimo = localStorage.getItem("petusDueno"); }catch(_){}
  const r = $(`input[name="fDueno"][value="${ultimo === "L" ? "L" : "V"}"]`); if(r) r.checked = true;
  F("addPrev").hidden = !b; if(b) F("addImg").src = S.nuevo.url;
  F("cropBar").hidden = !b; renderCropBar();
  F("addStart").hidden = true; F("addForm").hidden = false;
  renderNotices();
}
async function aplicarRecorte(n, c){
  try{
    const b = c ? await cropBlob(n.orig, c) : n.orig;
    if(S.nuevo !== n) return;
    n.blob = b; n.crop = c;
    if(n.url) URL.revokeObjectURL(n.url);
    n.url = URL.createObjectURL(b); F("addImg").src = n.url; renderCropBar();
  }catch(_){ toast("No se pudo recortar la foto."); }
}
function renderCropBar(){ const n = S.nuevo; if(!n) return; F("cropTxt").textContent = n.crop ? "Recortada al producto." : "Foto completa."; F("cropFull").hidden = !n.crop; }
function resetAdd(){
  if(S.nuevo && S.nuevo.url) URL.revokeObjectURL(S.nuevo.url);
  S.nuevo = null;
  F("addForm").hidden = true; F("addStart").hidden = false; F("addNotices").innerHTML = "";
}
function checkLocalDup(){
  const n = S.nuevo; if(!n || n.dupDecision) { renderNotices(); return; }
  const nm = norm(F("fNombre").value);
  const same = nm ? S.productos.filter(p => norm(p.nombre) === nm) : [];
  const num = norm(F("fNumero").value), ser = norm(F("fSerie").value);
  const serOk = p => { const ps = norm(p.serie); return !ser || !ps || ps === ser || ps.includes(ser) || ser.includes(ps); };
  const hit = same.find(p => (!num || !p.numero || norm(p.numero) === num) && serOk(p));
  n.dupId = hit ? hit.id : null;
  n.varianteId = (!hit && same.length) ? same[0].id : null;
  renderNotices();
}
function renderNotices(){
  const n = S.nuevo; if(!n) return;
  const out = [];
  const d = n.dupId && byId(n.dupId);
  if(d && n.dupDecision !== "distinto"){
    out.push(`<div class="notice dup"><h3>Parece repetido</h3><p>Ya tienes un producto con el mismo nombre${F("fNumero").value.trim() ? " y número" : ""}:</p>
      <div class="match">${thumb(d)}<div><b>${esc(d.nombre)}</b><small>${meta(d) || ""} · existencia ×${cant(d)}</small></div></div>
      <div class="row"><button type="button" class="btn primary" id="dupSame">Es el mismo: sumar</button><button type="button" class="btn ghost" id="dupDiff">Es otro: guardar aparte</button></div></div>`);
  } else if(n.varianteId && byId(n.varianteId)){
    const v = byId(n.varianteId);
    out.push(`<div class="notice warn"><h3>Mismo nombre, otra versión</h3><p>Ya tienes “${esc(v.nombre)}” (${meta(v) || "sin detalles"}, ×${cant(v)}), pero con otro número o serie. Se guardará como producto aparte.</p><div class="row"><button type="button" class="btn ghost" id="varSame">En realidad es el mismo: sumar</button></div></div>`);
  }
  F("addNotices").innerHTML = out.join("");
  if(F("dupSame")) F("dupSame").onclick = () => sumarExistente(n.dupId);
  if(F("dupDiff")) F("dupDiff").onclick = () => { n.dupDecision = "distinto"; renderNotices(); };
  if(F("varSame")) F("varSame").onclick = () => sumarExistente(n.varianteId);
  F("addSave").textContent = (d && n.dupDecision !== "distinto") ? "Guardar como producto nuevo" : "Guardar en inventario";
}
function lockAdd(on){ ["addSave","addPend","addCancel"].forEach(i => F(i).disabled = on); $$("#addNotices button").forEach(b => b.disabled = on); }
async function sumarExistente(id){
  const n = S.nuevo, p = byId(id); if(!n || !p) return;
  const fd = formData({ cantidad: Math.max(1, parseInt(F("fCant").value,10) || 1), precio: F("fPrecio").value });
  if(n.blob && !p.foto) fd.append("foto", n.blob, "foto.jpg");
  lockAdd(true);
  try{ const r = await api("api/productos/" + id + "/sumar", { body: fd }); reemplazar(r); toast(`Sumado. Ahora tienes ${r.cantidad} de “${r.nombre}”`); resetAdd(); }
  catch(e){ toast(errMsg(e)); }
  finally{ lockAdd(false); }
}
async function guardarNuevo(sinNombre){
  const n = S.nuevo; if(!n) return;
  const nombre = sinNombre ? "" : F("fNombre").value.trim();
  if(!sinNombre && !nombre){ toast("Escribe el nombre, o usa “Guardar sin nombre por ahora”."); F("fNombre").focus(); return; }
  const dueno = ($('input[name="fDueno"]:checked') || {}).value === "L" ? "L" : "V";
  try{ localStorage.setItem("petusDueno", dueno); }catch(_){}
  const fd = formData({ nombre, precio: F("fPrecio").value, dueno, serie: F("fSerie").value.trim(), numero: F("fNumero").value.trim(),
    color: F("fColor").value.trim(), anio: F("fAnio").value.trim(), cantidad: Math.max(1, parseInt(F("fCant").value,10) || 1),
    notas: F("fNotas").value.trim(), esHotWheels: F("fHW").value });
  if(n.blob) fd.append("foto", n.blob, "foto.jpg");
  lockAdd(true); F("addSave").textContent = "Guardando…";
  try{
    const p = await api("api/productos", { body: fd }); reemplazar(p);
    toast(nombre ? `Guardado: ${nombre}${precioDe(p) != null ? " · " + fmtPrecio(precioDe(p)) : " (sin precio)"}` : "Guardado sin nombre. Complétalo en Inventario › Sin nombre.");
    resetAdd();
  }catch(e){ toast(errMsg(e)); }
  finally{ lockAdd(false); if(S.nuevo) renderNotices(); }
}

/* ---------- vender ---------- */
function renderVentasRes(){
  const q = F("venQ").value.trim();
  const list = q ? search(q) : [];
  F("venNone").hidden = !(q && !list.length);
  F("venNoneTxt").textContent = `No encontré “${q}”`;
  F("venRes").innerHTML = list.slice(0, 30).map(p => `
    <button class="item" data-id="${p.id}">${thumb(p)}<div class="info"><div class="name">${ownTag(p)} ${esc(p.nombre || "Sin nombre")}</div><div class="meta">${precioDe(p) != null ? `<span class="price">${fmtPrecio(precioDe(p))}</span> · ` : ""}${meta(p) || "&nbsp;"}</div></div><span class="qty ${cant(p)===0?"zero":""}">×${cant(p)}</span></button>`).join("");
}
F("venQ").addEventListener("input", renderVentasRes);
F("venRes").addEventListener("click", e => { const it = e.target.closest(".item"); if(it) openVenta(it.dataset.id); });

function openVenta(id){
  const p = byId(id); if(!p) return;
  const stock = cant(p);
  const panel = openSheet(`
    ${p.foto ? `<img class="big" src="${esc(p.foto)}" alt="">` : `<div class="noimg big" style="height:160px">${CAR_SVG}</div>`}
    <div><h2>${esc(p.nombre || "Sin nombre")}</h2><div class="kv">${precioDe(p) != null ? `<span>Precio <b class="price">${fmtPrecio(precioDe(p))}</b></span>` : "<span>Sin precio</span>"}${meta(p) ? `<span>${meta(p)}</span>` : ""}<span>Existencia <b>×${stock}</b></span></div></div>
    <div id="vAsk">
      <p class="q">¿Es este el producto?</p>
      <div class="row" style="margin-top:10px"><button class="btn ghost" id="vNo">No, es otro</button><button class="btn primary" id="vYes" ${stock ? "" : "disabled"}>${stock ? "Sí, es este" : "Sin existencias"}</button></div>
    </div>
    <div id="vQty" class="view" hidden>
      <p class="q">¿Cuántos se lleva?</p>
      <div class="stepper"><button type="button" data-step="-1" aria-label="Menos">−</button><input id="vCant" type="number" min="1" value="1" inputmode="numeric" aria-label="Cantidad"><button type="button" data-step="1" aria-label="Más">+</button></div>
      <p class="lead" id="vLeft"></p>
      <button class="btn primary block" id="vOk">Agregar a la venta</button>
      <button class="btn text" id="vBack">Cancelar</button>
    </div>`);
  stepper(panel);
  const yaEn = enCarrito(p.id), maxAdd = Math.max(0, stock - yaEn);
  F("vCant").max = Math.max(1, maxAdd);
  const upd = () => {
    let v = parseInt(F("vCant").value,10) || 1; v = Math.min(Math.max(1, maxAdd), Math.max(1, v));
    F("vLeft").textContent = (yaEn ? `Ya hay ${yaEn} en la venta. ` : "") + `Quedarán ${stock - yaEn - v}.` + (precioDe(p) != null ? ` Subtotal: ${fmtPrecio(precioDe(p) * v)}.` : "");
    F("vOk").textContent = `Agregar ${v} a la venta`;
  };
  F("vCant").addEventListener("input", upd); upd();
  F("vNo").onclick = () => { closeSheet(); F("venQ").focus(); };
  F("vBack").onclick = closeSheet;
  F("vYes").onclick = () => { if(!maxAdd){ toast("Ya agregaste todas las piezas que hay de este producto."); return; } F("vAsk").hidden = true; F("vQty").hidden = false; F("vCant").focus(); F("vCant").select(); };
  F("vOk").onclick = () => {
    const v = Math.min(maxAdd, Math.max(1, parseInt(F("vCant").value,10) || 1));
    agregarAlCarrito(p.id, v); closeSheet();
    F("venQ").value = ""; renderVentasRes();
    toast(`Agregado: ${v} × ${p.nombre}. Busca otro producto o registra la venta.`);
  };
}

/* ---------- venta actual (carrito) ---------- */
const CKEY = "petusCarrito:" + P.yo.id;
try{ const c = JSON.parse(localStorage.getItem(CKEY) || "[]"); S.carrito = Array.isArray(c) ? c.filter(x => x && x.id && x.cant > 0) : []; }catch(_){ S.carrito = []; }
function guardarCarrito(){ try{ localStorage.setItem(CKEY, JSON.stringify(S.carrito)); }catch(_){} }
function enCarrito(id){ const it = S.carrito.find(x => x.id === Number(id)); return it ? it.cant : 0; }
function agregarAlCarrito(id, n){
  id = Number(id); const it = S.carrito.find(x => x.id === id);
  if(it) it.cant += n; else S.carrito.push({ id, cant: n });
  guardarCarrito(); renderCarrito();
}
function renderCarrito(){
  S.carrito = S.carrito.filter(x => byId(x.id)).map(x => ({ id: x.id, cant: Math.min(x.cant, Math.max(1, cant(byId(x.id)))) }));
  F("ticket").hidden = !S.carrito.length;
  if(!S.carrito.length) return;
  let total = 0, piezas = 0, sinPrecio = 0;
  F("tRows").innerHTML = S.carrito.map(x => {
    const p = byId(x.id), pr = precioDe(p), sub = pr != null ? pr * x.cant : null;
    piezas += x.cant; if(sub != null) total += sub; else sinPrecio++;
    return `<div class="t-row" data-id="${p.id}">${thumb(p)}
      <div class="t-info"><div class="t-name">${ownTag(p)} ${esc(p.nombre || "Sin nombre")}</div>
        <div class="t-line"><div class="stepper"><button type="button" data-tstep="-1" aria-label="Menos">−</button><input type="number" min="1" max="${cant(p)}" value="${x.cant}" inputmode="numeric" aria-label="Cantidad" data-tq><button type="button" data-tstep="1" aria-label="Más">+</button></div><span>× ${pr != null ? fmtPrecio(pr) : "sin precio"}</span></div></div>
      <div class="t-sub">${sub != null ? fmtPrecio(sub) : "—"}<button type="button" class="t-del" data-tdel>Quitar</button></div>
    </div>`;
  }).join("");
  F("tCount").textContent = `${S.carrito.length} producto${S.carrito.length === 1 ? "" : "s"} · ${piezas} pieza${piezas === 1 ? "" : "s"}`;
  F("tTotal").textContent = fmtPrecio(total) + (sinPrecio ? " + " + sinPrecio + " sin precio" : "");
  F("tOk").textContent = `Registrar venta · ${fmtPrecio(total)}`;
}
F("tRows").addEventListener("click", e => {
  const row = e.target.closest(".t-row"); if(!row) return;
  const it = S.carrito.find(x => x.id === Number(row.dataset.id)); if(!it) return;
  if(e.target.closest("[data-tdel]")){ S.carrito = S.carrito.filter(x => x !== it); guardarCarrito(); renderCarrito(); return; }
  const st = e.target.closest("[data-tstep]");
  if(st){ it.cant = Math.min(cant(byId(it.id)), Math.max(1, it.cant + Number(st.dataset.tstep))); guardarCarrito(); renderCarrito(); }
});
F("tRows").addEventListener("change", e => {
  if(!e.target.matches("[data-tq]")) return;
  const it = S.carrito.find(x => x.id === Number(e.target.closest(".t-row").dataset.id)); if(!it) return;
  it.cant = Math.min(cant(byId(it.id)), Math.max(1, parseInt(e.target.value,10) || 1)); guardarCarrito(); renderCarrito();
});
let confirmarCancelar = 0;
F("tClear").onclick = () => {
  if(Date.now() - confirmarCancelar > 4000){ confirmarCancelar = Date.now(); F("tClear").textContent = "Toca otra vez para cancelar la venta"; setTimeout(() => F("tClear").textContent = "Cancelar venta", 4000); return; }
  S.carrito = []; guardarCarrito(); renderCarrito(); F("tClear").textContent = "Cancelar venta"; toast("Venta cancelada");
};
F("tOk").onclick = async () => {
  if(!S.carrito.length) return;
  const btn = F("tOk"); btn.disabled = true; btn.textContent = "Registrando…";
  try{
    const r = await api("api/ventas", { json: { items: S.carrito.map(x => ({ id: x.id, cantidad: x.cant })) } });
    S.carrito = []; guardarCarrito();
    await Promise.all([cargarProductos(), cargarVentas()]);
    const msg = `Venta registrada: ${r.piezas} pieza${r.piezas === 1 ? "" : "s"} por ${fmtPrecio(r.total)}.` + (r.faltan.length ? ` Sin existencia suficiente: ${r.faltan.join(", ")}.` : "");
    toast(msg, "Deshacer", async () => {
      try{ await api("api/ventas/deshacer", { json: { grupo: r.grupo } }); await Promise.all([cargarProductos(), cargarVentas()]); toast("Venta deshecha"); }
      catch(e){ toast(errMsg(e)); }
    });
  }catch(e){ if(e && e.sinJornada){ S.jornada = null; renderJornada(); pedirInicio(true); } else toast(errMsg(e)); }
  finally{ btn.disabled = false; renderCarrito(); }
};

/* ---------- admin: liga, contacto, vendedores ---------- */
if(ADMIN){
  F("copyCat").onclick = () => {
    const inp = F("catLink");
    const ok = () => toast("Liga copiada.");
    try{ navigator.clipboard.writeText(inp.value).then(ok, () => { inp.focus(); inp.select(); }); }catch(_){ inp.focus(); inp.select(); }
  };
  F("btnContacto").onclick = () => {
    openSheet(`<h2>Contacto del catálogo</h2>
      <label class="field"><span>Contacto o mensaje</span><textarea id="cContacto" class="inp" placeholder="Pedidos por WhatsApp al 55 1234 5678 · Entregas en …">${esc(P.contacto)}</textarea></label>
      <button class="btn primary block" id="cSave">Guardar</button><button class="btn text" id="cClose">Cerrar</button>`);
    F("cClose").onclick = closeSheet;
    F("cSave").onclick = async () => {
      try{ await api("api/ajustes", { json: { contacto: F("cContacto").value.trim() } }); P.contacto = F("cContacto").value.trim(); closeSheet(); toast("Contacto guardado"); }
      catch(e){ toast(errMsg(e)); }
    };
  };
  F("btnUsuarios").onclick = abrirUsuarios;
  F("btnPedidos").onclick = abrirPedidos;
  F("btnAvisos").onclick = abrirAvisos;
}

/* ---------- pedidos del catálogo (admin) ---------- */
let pedidos = [];
async function cargarPedidos(){
  if(!ADMIN) return;
  try{ pedidos = await api("api/pedidos"); }catch(_){ return; }
  const n = pedidos.filter(p => p.estado === "nuevo").length;
  F("pedNuevos").hidden = !n; F("pedNuevos").textContent = n;
}
async function abrirPedidos(){
  await cargarPedidos();
  const fmt = new Intl.DateTimeFormat("es-MX",{day:"numeric",month:"short",hour:"2-digit",minute:"2-digit"});
  const ESTADOS = { nuevo:"Nuevo", atendido:"Atendido", cancelado:"Cancelado" };
  const pintar = () => {
    const panel = openSheet(`<h2>Pedidos del catálogo</h2>
      <p class="lead">Los pedidos que hacen tus clientes. Márcalos como atendidos cuando los entregues.</p>
      ${pedidos.length ? pedidos.map(p => `<div class="ticket" style="border-width:${p.estado === "nuevo" ? 2 : 1}px;border-color:${p.estado === "nuevo" ? "var(--accent)" : "var(--line)"}" data-id="${p.id}">
        <div class="ticket-head"><h3>${esc(p.codigo)} · ${esc(p.cliente)}</h3><small>${fmt.format(new Date(p.fecha))}</small></div>
        <div class="kv"><span>Tel. <b>${esc(p.telefono)}</b></span><span class="pill">${ESTADOS[p.estado] || p.estado}</span></div>
        ${p.nota ? `<p class="lead" style="font-size:14px">Nota: ${esc(p.nota)}</p>` : ""}
        <div>${p.items.map(it => `<div class="sale"><span><b>${it.cantidad}×</b> ${esc(it.nombre)} <span class="own own-${it.dueno === "L" ? "L" : "V"}" style="width:18px;height:18px;font-size:11px;vertical-align:middle">${it.dueno === "L" ? "L" : "V"}</span></span><span>${it.precio != null ? fmtPrecio(it.precio * it.cantidad) : "a consultar"}</span></div>`).join("")}</div>
        <div class="t-total"><span>Total</span><b style="font-size:24px">${fmtPrecio(p.total)}</b></div>
        <div class="row">${p.estado !== "atendido" ? '<button class="btn ghost" data-e="atendido" type="button">Marcar atendido</button>' : '<button class="btn ghost" data-e="nuevo" type="button">Volver a nuevo</button>'}${p.estado !== "cancelado" ? '<button class="btn text" data-e="cancelado" type="button">Cancelar</button>' : ""}</div>
      </div>`).join("") : '<div class="empty"><b>Aún no hay pedidos</b><span>Cuando un cliente envíe un pedido desde el catálogo aparecerá aquí.</span></div>'}
      <button class="btn text" id="pedClose" type="button">Cerrar</button>`);
    F("pedClose").onclick = closeSheet;
    $$("[data-e]", panel).forEach(b => b.onclick = async () => {
      try{ pedidos = await api("api/pedidos/" + b.closest("[data-id]").dataset.id + "/estado", { json: { estado: b.dataset.e } }); const n = pedidos.filter(p => p.estado === "nuevo").length; F("pedNuevos").hidden = !n; F("pedNuevos").textContent = n; pintar(); }
      catch(e){ toast(errMsg(e)); }
    });
  };
  pintar();
}
async function abrirAvisos(){
  let c; try{ c = await api("api/avisos"); }catch(e){ toast(errMsg(e)); return; }
  openSheet(`<h2>Avisos de pedidos</h2>
    <p class="lead">Elige cómo quieres enterarte de cada pedido nuevo. Todo es gratis. Los pedidos también quedan guardados en "Pedidos".</p>
    <label class="field"><span>Correo donde recibir los pedidos</span><input id="avEmail" class="inp" type="email" placeholder="tucorreo@gmail.com" value="${esc(c.email)}"></label>
    <label class="field"><span>Tu WhatsApp (10 dígitos)</span><input id="avWa" class="inp" inputmode="tel" placeholder="5512345678" value="${esc(c.whatsapp)}"></label>
    <p class="lead" style="font-size:13px">Con WhatsApp, al terminar el pedido el cliente toca "Enviar también por WhatsApp" y se abre el chat contigo con el pedido ya escrito.</p>
    <hr class="sep"><h3 class="opt-title">Telegram (aviso automático al celular)</h3>
    <p class="lead" style="font-size:14px">1. En Telegram busca <b>@BotFather</b>, mándale <b>/newbot</b> y sigue los pasos. Te da un <b>token</b>.<br>2. Pégalo aquí y guarda.<br>3. Abre tu bot nuevo, toca <b>Iniciar</b> y mándale "hola".<br>4. Toca <b>Detectar mi chat</b>.</p>
    <label class="field"><span>Token del bot</span><input id="avTok" class="inp" autocomplete="off" placeholder="123456789:ABC..." value="${esc(c.tgToken)}"></label>
    <label class="field"><span>Chat</span><input id="avChat" class="inp" inputmode="numeric" placeholder="se llena solo" value="${esc(c.tgChat)}"></label>
    <div class="row"><button class="btn ghost" id="avDet" type="button">Detectar mi chat</button><button class="btn ghost" id="avProb" type="button">Enviar prueba</button></div>
    <button class="btn primary block" id="avSave" type="button">Guardar</button>
    <button class="btn text" id="avClose" type="button">Cerrar</button>`);
  const guardar = async () => api("api/avisos", { json: { email: F("avEmail").value.trim(), whatsapp: F("avWa").value.trim(), tgToken: F("avTok").value.trim(), tgChat: F("avChat").value.trim() } });
  F("avClose").onclick = closeSheet;
  F("avSave").onclick = async () => { try{ await guardar(); toast("Avisos guardados"); closeSheet(); }catch(e){ toast(errMsg(e)); } };
  F("avDet").onclick = async () => {
    try{ await guardar(); const r = await api("api/avisos/detectar-chat", { json: {} }); F("avChat").value = r.tgChat; toast("Chat encontrado" + (r.nombre ? ": " + r.nombre : "") + ". Toca Enviar prueba."); }
    catch(e){ toast(errMsg(e)); }
  };
  F("avProb").onclick = async () => {
    try{
      await guardar(); const r = await api("api/avisos/probar", { json: {} });
      const t = x => x === null ? "no configurado" : (x ? "enviado ✓" : "falló ✗");
      toast(`Correo: ${t(r.email)} · Telegram: ${t(r.telegram)}`);
    }catch(e){ toast(errMsg(e)); }
  };
}
if(ADMIN){ cargarPedidos(); setInterval(() => { if(!document.hidden) cargarPedidos(); }, 60000); }
async function abrirUsuarios(){
  let lista = [];
  try{ lista = await api("api/usuarios"); }catch(e){ toast(errMsg(e)); return; }
  const pintar = () => {
    openSheet(`<h2>Vendedores</h2>
      <p class="lead">Cada vendedor entra con su usuario y contraseña. Solo puede registrar ventas y ver el catálogo.</p>
      <div class="users">${lista.map(u => `<div class="user-row" data-id="${u.id}"><div><b>${esc(u.nombre)}</b><small>Usuario: ${esc(u.usuario)} ${u.rol === "admin" ? '<span class="pill">Administrador</span>' : (u.activo ? "" : '<span class="pill">Desactivado</span>')}</small></div>
        <button class="btn ghost" data-u="pass" type="button">Contraseña</button>${u.rol === "admin" ? "" : `<button class="btn ghost" data-u="act" type="button">${u.activo ? "Desactivar" : "Activar"}</button>`}</div>`).join("")}</div>
      <hr class="sep"><h3 class="opt-title">Agregar vendedor</h3>
      <label class="field"><span>Nombre</span><input id="uNombre" class="inp" placeholder="Jonathan"></label>
      <label class="field"><span>Usuario para entrar</span><input id="uUsuario" class="inp" autocapitalize="none" placeholder="jonathan"></label>
      <label class="field"><span>Contraseña (mínimo 8)</span><input id="uPass" class="inp" type="text" autocomplete="off"></label>
      <button class="btn primary block" id="uAdd" type="button">Agregar vendedor</button>
      <button class="btn text" id="uClose" type="button">Cerrar</button>`);
    F("uClose").onclick = closeSheet;
    F("uAdd").onclick = async () => {
      try{ lista = await api("api/usuarios", { json: { nombre: F("uNombre").value.trim(), usuario: F("uUsuario").value.trim(), password: F("uPass").value } }); toast("Vendedor agregado"); pintar(); }
      catch(e){ toast(errMsg(e)); }
    };
    $$(".user-row [data-u]").forEach(b => b.onclick = async () => {
      const id = b.closest(".user-row").dataset.id, u = lista.find(x => String(x.id) === id);
      if(b.dataset.u === "act"){
        try{ lista = await api("api/usuarios/" + id, { json: { activo: !u.activo } }); pintar(); }catch(e){ toast(errMsg(e)); }
      } else {
        const fila = b.closest(".user-row");
        if(fila.querySelector(".inp")) return;
        fila.insertAdjacentHTML("afterend", `<div class="row" data-np="${id}"><input class="inp" type="text" placeholder="Nueva contraseña (mín. 8)" style="flex:1 1 180px"><button class="btn primary" type="button">Guardar</button></div>`);
        const caja = $(`[data-np="${id}"]`);
        caja.querySelector("button").onclick = async () => {
          try{ lista = await api("api/usuarios/" + id, { json: { password: caja.querySelector("input").value } }); toast("Contraseña cambiada"); pintar(); }catch(e){ toast(errMsg(e)); }
        };
      }
    });
  };
  pintar();
}

/* ---------- inicio ---------- */
route(); medirCabecera();
cargarProductos();
cargarVentas().then(() => { S.jcarg = true; const v = location.hash.slice(1); if(!S.jornada && (v === "vender" || (!v && !ADMIN))) pedirInicio(false); });
})();
