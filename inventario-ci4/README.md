# Inventario Hot Wheels · Juguetería PETUS (CodeIgniter 4)

Versión para tu propio sitio de la app de inventario que hicimos en claude.ai.
Pensada para **https://bixosdement.com/inventario-hotwheels/**.

## Qué incluye

- **Inventario** (solo administrador): foto con recorte al producto, nombre escrito a mano, precio,
  precio antes (descuento), dueño **L** (Lupita) / **V** (Victor), serie, número, color, año, cantidad y notas.
  Aviso de repetidos al escribir el nombre ("Es el mismo: sumar" / "Es otro: guardar aparte").
  Filtros: Sin nombre, Con repetidos, Sin precio, Agotados, Lupita, Victor.
- **Vender** (administrador y vendedores): buscar, confirmar, agregar varios productos a la
  "Venta actual", total y **Registrar venta**. Deshacer la venta. Cada venta guarda quién la hizo.
- **Catálogo público** (`/catalogo`): sin cuenta. Portada Juguetería PETUS, buscador fijo,
  filtros (Lo más vendido, Novedades, Descuentos, Precio más bajo/alto, rangos de precio)
  y **PDF descargable**.
- **Usuarios**: tú eres administrador; los vendedores (Jonathan, María Guadalupe) entran con
  su usuario y contraseña y solo ven Vender y el Catálogo.
- **Importar** el inventario de la app de claude.ai (botón "Exportar para mi sitio (ZIP)").

La lectura automática del blister con IA no está incluida: el nombre se escribe a mano.

Requisitos: PHP 8.2 o más nuevo con las extensiones `intl`, `mbstring`, `gd`, `zip`, `mysqli`;
MySQL o MariaDB; HTTPS (la cámara del celular solo funciona con el candado).

Los pasos para subirlo están en **[INSTALAR.md](INSTALAR.md)**.
