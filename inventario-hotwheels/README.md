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
- **Inventario**: cuadrícula con foto y existencia; filtros Sin nombre / Con repetidos / Agotados.

Se ejecuta publicada en claude.ai (usa la base de datos, el almacén de fotos y la lectura
de imágenes con Claude de la plataforma), así que `index.html` es el código fuente de la
página publicada; abierto como archivo local no guarda datos.
