<?= view('_cabeza', ['titulo' => 'Instalar · Inventario Hot Wheels']) ?>
<body>
<div class="auth">
  <form class="auth-card" method="post" action="<?= site_url('instalar') ?>">
    <?= csrf_field() ?>
    <div>
      <h1>Primera vez</h1>
      <p class="lead">Crea la cuenta del administrador. Esta página se desactiva sola en cuanto la creas.</p>
    </div>
    <?php if (! empty($error)): ?><div class="msg"><?= esc($error) ?></div><?php endif ?>
    <label class="field"><span>Tu nombre</span><input class="inp" name="nombre" required></label>
    <label class="field"><span>Usuario para entrar</span><input class="inp" name="usuario" autocapitalize="none" pattern="[A-Za-z0-9._\-]{3,60}" required placeholder="victor"></label>
    <label class="field"><span>Contraseña (mínimo 8)</span><input class="inp" name="password" type="password" minlength="8" autocomplete="new-password" required></label>
    <button class="btn primary block" type="submit">Crear base de datos y cuenta</button>
  </form>
</div>
</body>
</html>
