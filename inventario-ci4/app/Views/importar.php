<?= view('_cabeza', ['titulo' => 'Importar · Inventario Hot Wheels']) ?>
<body>
<div class="auth">
  <div class="auth-card">
    <div>
      <h1>Importar inventario</h1>
      <p class="lead">Sube el archivo <b>inventario-petus.zip</b> que descargaste desde la app de claude.ai. Si vuelves a importar, los productos se actualizan, no se duplican.</p>
    </div>
    <?php if (! empty($resultado)): ?><div class="msg ok"><?= esc($resultado) ?></div><?php endif ?>
    <?php if (! empty($error)): ?><div class="msg"><?= esc($error) ?></div><?php endif ?>
    <form method="post" action="<?= site_url('importar') ?>" enctype="multipart/form-data" class="view" id="fImp">
      <?= csrf_field() ?>
      <label class="field"><span>Archivo ZIP</span><input class="inp" type="file" name="archivo" accept=".zip,application/zip"></label>
      <button class="btn primary block" type="submit">Importar</button>
    </form>
    <form method="post" action="<?= site_url('importar') ?>" class="view">
      <?= csrf_field() ?>
      <input type="hidden" name="desde_carpeta" value="1">
      <p class="lead" style="font-size:14px">¿El archivo es muy grande para subirlo aquí? Súbelo con el administrador de archivos de tu hosting a la carpeta <b>writable/import/</b> con el nombre <b>inventario-petus.zip</b> y toca:</p>
      <button class="btn ghost block" type="submit" <?= $enCarpeta ? '' : 'disabled' ?>><?= $enCarpeta ? 'Importar el archivo de la carpeta' : 'No hay archivo en la carpeta' ?></button>
    </form>
    <a class="btn text" href="<?= site_url('panel') ?>#inventario">Volver al inventario</a>
  </div>
</div>
<script>
document.getElementById('fImp').addEventListener('submit', function(e){ var b = this.querySelector('button'); b.disabled = true; b.textContent = 'Importando… puede tardar unos minutos'; });
</script>
</body>
</html>
