<?= view('_cabeza', ['titulo' => 'Inventario Hot Wheels']) ?>
<body class="<?= $yo['rol'] === 'admin' ? 'admin' : 'seller' ?>">
<header class="top">
  <div class="wrap">
    <div class="brand">Inventario Hot Wheels<small id="sync"><?= $yo['rol'] === 'admin' ? 'Administrador' : 'Vendedor' ?>: <?= esc($yo['nombre']) ?></small></div>
    <div class="stats">
      <div class="stat"><b id="stModelos">–</b><span>Modelos</span></div>
      <div class="stat"><b id="stPiezas">–</b><span>Piezas</span></div>
    </div>
    <a class="salir" href="<?= site_url('salir') ?>">Salir</a>
  </div>
  <div class="track" aria-hidden="true"></div>
</header>

<main class="wrap">
  <div id="globalBanner" class="banner" hidden></div>

  <!-- INVENTARIO (solo admin) -->
  <section id="v-inventario" class="view" hidden>
    <div>
      <h2>Mi inventario</h2>
      <p class="lead" id="invLead">Toca un carrito para editar nombre, precio, cantidad o notas.</p>
    </div>
    <div class="linkbox">
      <b>Catálogo para tus clientes</b>
      <span class="lead" style="font-size:14px">Público, sin cuenta: muestra los productos con existencia y su precio.</span>
      <input id="catLink" class="inp" readonly value="<?= esc(site_url('catalogo')) ?>" aria-label="Liga del catálogo">
      <div class="row"><a class="btn primary" href="<?= site_url('catalogo') ?>" target="_blank" rel="noopener">Ver catálogo</a><button class="btn ghost" id="copyCat" type="button">Copiar liga</button></div>
      <div class="row"><button class="btn primary" id="btnPedidos" type="button">Pedidos <span class="fbadge" id="pedNuevos" hidden></span></button><button class="btn ghost" id="btnAvisos" type="button">Avisos de pedidos</button></div>
      <div class="row"><button class="btn ghost" id="btnContacto" type="button">Contacto del catálogo</button><button class="btn ghost" id="btnUsuarios" type="button">Vendedores</button><a class="btn ghost" href="<?= site_url('importar') ?>">Importar</a></div>
    </div>
    <div class="search stick"><input id="invQ" class="inp" type="search" placeholder="Filtrar por nombre, serie o color" autocomplete="off"></div>
    <div class="chips" id="invFiltros">
      <button class="chip on" data-f="todos">Todos</button>
      <button class="chip" data-f="pendientes">Sin nombre</button>
      <button class="chip" data-f="repetidos">Con repetidos</button>
      <button class="chip" data-f="sinprecio">Sin precio</button>
      <button class="chip" data-f="agotados">Agotados</button>
      <button class="chip" data-f="L"><span class="own own-L" style="width:20px;height:20px;font-size:13px">L</span> Lupita</button>
      <button class="chip" data-f="V"><span class="own own-V" style="width:20px;height:20px;font-size:13px">V</span> Victor</button>
    </div>
    <div id="invCards" class="cards"></div>
    <div id="invEmpty" class="empty" hidden>
      <b>Tu inventario está vacío</b>
      <span>Toma la foto del primer blister, o importa tu inventario desde la app de claude.ai.</span>
      <div class="row"><a class="btn primary" href="#agregar">Agregar el primero</a><a class="btn ghost" href="<?= site_url('importar') ?>">Importar</a></div>
    </div>
  </section>

  <!-- AGREGAR (solo admin) -->
  <section id="v-agregar" class="view" hidden>
    <div id="addStart" class="view">
      <div>
        <h2>Agregar producto</h2>
        <p class="lead">Foto de frente al blister. Después escribes el nombre tal como aparece.</p>
      </div>
      <label class="shot" for="camIn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13" r="4"/></svg>
        <strong>Tomar foto</strong>
        <span>Recorta la foto al producto y te avisa si ya lo tienes.</span>
      </label>
      <input id="camIn" type="file" accept="image/*" capture="environment" hidden>
      <label class="btn ghost block" for="galIn">Elegir foto de la galería</label>
      <input id="galIn" type="file" accept="image/*" hidden>
      <button class="btn text" id="sinFoto" type="button">Agregar sin foto</button>
    </div>

    <form id="addForm" class="view" hidden autocomplete="off">
      <div class="preview" id="addPrev"><img id="addImg" alt="Foto del producto"><button type="button" class="btn ghost" id="addRetake">Cambiar foto</button></div>
      <div class="cropbar" id="cropBar" hidden><span id="cropTxt"></span><button type="button" class="btn ghost" id="cropAdj">Recortar al producto</button><button type="button" class="btn text" id="cropFull">Usar foto completa</button></div>
      <div id="addNotices" class="view"></div>
      <label class="field"><span>Nombre</span><input id="fNombre" class="inp big" placeholder="Nombre como aparece en el blister"></label>
      <label class="field"><span>Precio de venta por pieza</span><div class="money"><span>$</span><input id="fPrecio" inputmode="decimal" placeholder="0.00" aria-label="Precio en pesos"></div></label>
      <div class="field"><span>Dueño</span><div class="owner-pick" role="radiogroup"><label><input type="radio" name="fDueno" value="V" checked><span class="own own-V">V</span>Victor</label><label><input type="radio" name="fDueno" value="L"><span class="own own-L">L</span>Lupita</label></div></div>
      <div class="grid2">
        <label class="field"><span>Serie</span><input id="fSerie" class="inp" placeholder="HW Exotics"></label>
        <label class="field"><span>Nº colección</span><input id="fNumero" class="inp" placeholder="45/250"></label>
        <label class="field"><span>Color</span><input id="fColor" class="inp" placeholder="Rojo"></label>
        <label class="field"><span>Año</span><input id="fAnio" class="inp" inputmode="numeric" placeholder="2026"></label>
      </div>
      <label class="field"><span>¿Es Hot Wheels?</span>
        <select id="fHW" class="inp"><option value="1">Sí, Hot Wheels</option><option value="0">No, otro coleccionable</option></select></label>
      <div class="field"><span>Cantidad</span>
        <div class="stepper"><button type="button" data-step="-1" aria-label="Menos">−</button><input id="fCant" type="number" min="1" value="1" inputmode="numeric" aria-label="Cantidad"><button type="button" data-step="1" aria-label="Más">+</button></div>
      </div>
      <label class="field"><span>Notas</span><textarea id="fNotas" class="inp" placeholder="Estado del blister, ubicación, costo… (no se muestran en el catálogo)"></textarea></label>
      <button type="submit" class="btn primary block" id="addSave">Guardar en inventario</button>
      <div class="row">
        <button type="button" class="btn ghost" id="addPend">Guardar sin nombre por ahora</button>
        <button type="button" class="btn text" id="addCancel">Cancelar</button>
      </div>
    </form>
  </section>

  <!-- VENDER -->
  <section id="v-vender" class="view" hidden>
    <div>
      <h2>Registrar venta</h2>
      <p class="lead">Busca cada producto, confírmalo y agrégalo a la venta. Cuando el cliente termine, registra la venta.</p>
      <p class="jor-strip" id="jorStrip" hidden></p>
    </div>
    <div class="search stick">
      <input id="venQ" class="inp big" type="search" placeholder="Nombre del carrito" autocomplete="off">
    </div>
    <div id="venRes" class="list"></div>
    <div id="venNone" class="empty" hidden>
      <b id="venNoneTxt">No encontré ese nombre</b>
      <span>Puede estar escrito distinto en el inventario. Prueba con una sola palabra.</span>
    </div>
    <section class="ticket" id="ticket" hidden aria-label="Venta actual">
      <div class="ticket-head"><h3>Venta actual</h3><small id="tCount"></small></div>
      <div id="tRows"></div>
      <div class="t-total"><span>Total de la venta</span><b id="tTotal">$0</b></div>
      <button class="btn primary block" id="tOk" type="button">Registrar venta</button>
      <button class="btn text" id="tClear" type="button">Cancelar venta</button>
    </section>
    <section class="jornada" id="jorBox" aria-label="Mi jornada">
      <div class="ticket-head"><h3>Mi jornada</h3><small id="jorDesde"></small></div>
      <div id="jorSin" class="lead" hidden>No has iniciado tu jornada. <button class="btn primary" id="jorIniciar" type="button">Iniciar jornada</button></div>
      <div id="jorCon" hidden>
        <div class="jor-nums">
          <div><span>Cambio</span><b id="jorFondo">$0</b></div>
          <div><span>Vendido</span><b id="jorTotal">$0</b></div>
          <div><span>En caja</span><b id="jorCaja">$0</b></div>
        </div>
        <p class="lead" id="jorPiezas" style="font-size:14px"></p>
        <details class="sales" id="salesBox">
          <summary>Ventas de esta jornada</summary>
          <div id="salesList"></div>
        </details>
        <button class="btn dark block" id="jorFin" type="button">Finalizar jornada</button>
      </div>
      <button class="btn ghost block owner-only" id="jorHist" type="button">Historial de ventas por día</button>
    </section>
  </section>
</main>

<nav class="tabs" aria-label="Secciones">
  <div class="wrap">
    <a class="tab owner-only" href="#inventario" data-tab="inventario"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>Inventario</a>
    <a class="tab add" href="#agregar" data-tab="agregar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Agregar</a>
    <a class="tab seller-only" href="<?= site_url('catalogo') ?>" data-tab="catalogo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M4 9h16"/></svg>Catálogo</a>
    <a class="tab" href="#vender" data-tab="vender"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>Vender</a>
  </div>
</nav>

<div class="sheet" id="sheet" hidden><div class="panel" id="panel" role="dialog" aria-modal="true"></div></div>
<div class="toast" id="toast" hidden role="status"><span id="toastTxt"></span><button id="toastAct" hidden></button></div>

<script>
window.PETUS = <?= json_encode([
    'base'     => rtrim(site_url(), '/') . '/',
    'yo'       => $yo,
    'contacto' => $contacto,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= base_url('assets/panel.js') ?>?v=4"></script>
</body>
</html>
