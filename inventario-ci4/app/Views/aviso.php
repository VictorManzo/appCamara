<?php
/** @var string $responsable @var string $domicilio @var string $correo @var string $contacto */
$pend = '<mark>[pendiente: complétalo en el panel → Contacto y aviso de privacidad]</mark>';
$dom  = $domicilio !== '' ? esc($domicilio) : $pend;
$mail = $correo !== '' ? '<b>' . esc($correo) . '</b>' : $pend;
?>
<?= view('_cabeza', ['titulo' => 'Aviso de privacidad · ' . $responsable]) ?>
<body>
<main class="wrap aviso">
  <style>
    .aviso{padding-block:24px 48px;max-width:760px;line-height:1.6}
    .aviso h1{font-family:var(--f-display);font-size:34px;text-transform:uppercase;margin:0 0 4px;line-height:1}
    .aviso h2{font-size:19px;margin:24px 0 6px;font-family:var(--f-body);text-transform:none;letter-spacing:0}
    .aviso p,.aviso li{color:var(--ink);font-size:16px}
    .aviso ul{padding-left:20px;margin:6px 0}
    .aviso .fecha{color:var(--muted);font-size:14px}
    .aviso mark{background:var(--warn-bg);color:var(--warn-ink);padding:0 4px;border-radius:4px}
    .aviso .volver{display:inline-block;margin-top:24px}
  </style>

  <h1>Aviso de privacidad</h1>
  <p class="fecha">Última actualización: 26 de noviembre de 2026</p>

  <h2>1. Quién es responsable de tus datos</h2>
  <p><b><?= esc($responsable) ?></b>, con domicilio en <?= $dom ?>, es responsable del uso y protección de tus datos personales, conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.</p>

  <h2>2. Qué datos personales recabamos</h2>
  <p>Cuando haces un pedido desde nuestro catálogo en línea te pedimos únicamente:</p>
  <ul>
    <li>Tu nombre.</li>
    <li>Tu número de teléfono o WhatsApp.</li>
    <li>La nota que quieras agregar a tu pedido (por ejemplo, lugar o forma de entrega).</li>
  </ul>
  <p>Junto con tu pedido también registramos los productos que elegiste, la fecha y hora, y la dirección IP desde la que lo enviaste (esta última solo para evitar pedidos automáticos o abusivos).</p>
  <p><b>No recabamos datos personales sensibles</b>, ni datos financieros o bancarios: el pago no se realiza en este sitio.</p>

  <h2>3. Para qué usamos tus datos</h2>
  <p>Usamos tus datos únicamente para estas finalidades, que son necesarias para atender tu solicitud:</p>
  <ul>
    <li>Recibir y registrar tu pedido.</li>
    <li>Contactarte para confirmar disponibilidad, precio, forma de pago y entrega.</li>
    <li>Darle seguimiento a tu pedido hasta su entrega o cancelación.</li>
  </ul>
  <p><b>No usamos tus datos para publicidad ni para otras finalidades</b> distintas a las anteriores.</p>

  <h2>4. Con quién compartimos tus datos</h2>
  <p>No vendemos, rentamos ni transferimos tus datos personales a terceros. Solo podrán ser comunicados a autoridades competentes cuando la ley lo exija.</p>
  <p>Para funcionar, el sitio se apoya en proveedores que tratan los datos por nuestra cuenta y solo para las finalidades de este aviso: el servicio de alojamiento web del sitio y los servicios de mensajería con los que recibimos el aviso de tu pedido (correo electrónico, Telegram y, si tú decides enviarlo, WhatsApp).</p>

  <h2>5. Cuánto tiempo conservamos tus datos</h2>
  <p>Conservamos tus datos el tiempo necesario para atender tu pedido y cumplir las obligaciones legales aplicables. Después los eliminamos o los dejamos de usar.</p>

  <h2>6. Tus derechos ARCO y cómo revocar tu consentimiento</h2>
  <p>Tienes derecho a <b>Acceder</b> a tus datos, <b>Rectificarlos</b> si son incorrectos, <b>Cancelarlos</b> cuando consideres que no se requieren para las finalidades señaladas, y <b>Oponerte</b> a su uso (derechos ARCO). También puedes revocar el consentimiento que nos diste.</p>
  <p>Para hacerlo, envía tu solicitud al correo <?= $mail ?><?= $contacto !== '' ? ' o comunícate por: ' . esc($contacto) : '' ?>, indicando tu nombre, el teléfono que usaste en tu pedido, el derecho que quieres ejercer y, de ser posible, el número de pedido. Te responderemos en un plazo máximo de 20 días hábiles y, si procede, lo haremos efectivo dentro de los 15 días hábiles siguientes.</p>

  <h2>7. Datos guardados en tu dispositivo</h2>
  <p>Para que no pierdas tu pedido mientras lo armas, el sitio guarda en tu propio navegador (almacenamiento local) los productos que elegiste y, si los escribiste, tu nombre y teléfono. Esta información no sale de tu dispositivo hasta que tú envías el pedido, y puedes borrarla limpiando los datos del sitio en tu navegador. El sitio usa también una cookie técnica de sesión necesaria para su funcionamiento; no usamos cookies de publicidad ni de rastreo.</p>

  <h2>8. Cambios a este aviso</h2>
  <p>Podemos actualizar este aviso de privacidad. Cualquier cambio se publicará en esta misma página con su fecha de actualización.</p>

  <h2>9. Consentimiento</h2>
  <p>Al marcar la casilla "He leído y acepto el Aviso de privacidad" y enviar tu pedido, aceptas que tratemos tus datos conforme a este aviso.</p>

  <a class="btn ghost volver" href="<?= site_url('catalogo') ?>">← Volver al catálogo</a>
</main>
</body>
</html>
