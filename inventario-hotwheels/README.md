# Inventario Hot Wheels

App móvil (página web) para llevar el inventario de carritos con foto.

- **Agregar**: tomas la foto del blister; Claude lee el nombre tal cual está impreso
  (sin traducir en los Hot Wheels), la serie, el número y el color, y lo compara con el
  inventario. Si parece repetido te avisa y puedes sumar la cantidad al existente.
- Si no puede leer el nombre, o el producto no es Hot Wheels y su nombre está en otro
  idioma, te lo dice y te pide que escribas cómo registrarlo (o lo guardas "sin nombre"
  y lo completas después desde el filtro *Sin nombre*).
- **Vender**: escribes o dictas el nombre, confirmas que el carrito de la pantalla es el
  correcto, indicas cuántos se vendieron y se restan del inventario (con opción de deshacer).
- **Recorte**: la foto se recorta sola al producto (Claude marca dónde está el blister); se puede ajustar arrastrando las esquinas o recortar después desde Inventario.
- **Precio**: se captura después de la foto (y se edita desde Inventario); las ventas guardan el total.
- **Inventario**: cuadrícula con foto, precio y existencia; filtros Sin nombre / Con repetidos / Sin precio / Agotados.
- **Catálogo** (`#catalogo`): página para clientes con diseño de blister, precio, existencias,
  búsqueda, filtro por serie y orden por precio. Quien no es dueño solo ve el catálogo y no puede
  modificar datos.

Se ejecuta publicada en claude.ai (usa la base de datos, el almacén de fotos y la lectura
de imágenes con Claude de la plataforma), así que `index.html` es el código fuente de la
página publicada; abierto como archivo local no guarda datos.
