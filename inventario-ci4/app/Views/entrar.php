<?= view('_cabeza', ['titulo' => 'Entrar · Inventario Hot Wheels']) ?>
<body>
<div class="auth">
  <form class="auth-card" method="post" action="<?= site_url('entrar') ?>">
    <?= csrf_field() ?>
    <div>
      <h1>Inventario Hot Wheels</h1>
      <p class="lead">Juguetería PETUS</p>
    </div>
    <?php if (! empty($error)): ?><div class="msg"><?= esc($error) ?></div><?php endif ?>
    <label class="field"><span>Usuario</span><input class="inp" name="usuario" autocomplete="username" autocapitalize="none" required value="<?= esc(old('usuario')) ?>"></label>
    <label class="field"><span>Contraseña</span><input class="inp" name="password" type="password" autocomplete="current-password" required></label>
    <button class="btn primary block" type="submit">Entrar</button>
    <a class="btn text" href="<?= site_url('catalogo') ?>">Ver el catálogo</a>
  </form>
</div>
</body>
</html>
