# Cómo subir el inventario a bixosdement.com

Tiempo aproximado: 20 minutos. Todo se hace desde **cPanel** de tu hosting.
Al final la app queda en **https://bixosdement.com/inventario-hotwheels/** y el catálogo
público en **https://bixosdement.com/inventario-hotwheels/catalogo**.

Tu WordPress no se toca: la app vive en su propia carpeta.

---

## 1. Descarga el proyecto

1. En GitHub abre el repositorio, rama `claude/hotwheels-inventory-app-2ptcii`.
2. Botón verde **Code → Download ZIP**.
3. Descomprime el ZIP en tu computadora. Usarás la carpeta **`inventario-ci4`**.

La carpeta ya trae todo lo necesario (incluida la carpeta `vendor`), no tienes que instalar nada.

## 2. Crea la base de datos

En cPanel → **Bases de datos MySQL**:

1. Crea una base, por ejemplo `bixosdem_petus`.
2. Crea un usuario, por ejemplo `bixosdem_petus`, con una contraseña segura.
3. Agrega el usuario a la base con **Todos los privilegios**.

Anota los tres datos: base, usuario y contraseña.

## 3. Sube los archivos (dos carpetas)

Por seguridad, el código va **fuera** de `public_html` y solo la parte pública va dentro.

En cPanel → **Administrador de archivos**, en tu carpeta principal (la que contiene `public_html`):

1. Crea la carpeta **`inventario-hotwheels-app`** y sube ahí todo el contenido de
   `inventario-ci4` **excepto** la carpeta `public`
   (o sea: `app`, `vendor`, `writable`, `.env.ejemplo`, `composer.json`, `spark`, etc.).
   Tip: comprime esas carpetas en un ZIP, súbelo y usa "Extraer".
2. Dentro de `public_html` crea la carpeta **`inventario-hotwheels`** y sube ahí el
   **contenido** de `inventario-ci4/public` (`index.php`, `.htaccess`, `assets`, `fotos`, etc.).

Debe quedar así:

```
/home2/bixosdem/
├── inventario-hotwheels-app/     ← app, vendor, writable, .env
└── public_html/
    ├── (tu WordPress)
    └── inventario-hotwheels/     ← index.php, .htaccess, assets/, fotos/
```

> Los archivos que empiezan con punto (`.htaccess`, `.env.ejemplo`) a veces están ocultos:
> en el Administrador de archivos activa **Configuración → Mostrar archivos ocultos**.

## 4. Configura el archivo .env

1. En `inventario-hotwheels-app`, copia `.env.ejemplo` y nómbralo **`.env`**.
2. Edítalo y reemplaza `NOMBRE_DE_LA_BASE`, `USUARIO_DE_LA_BASE` y `CONTRASEÑA_DE_LA_BASE`
   con los datos del paso 2. Guarda.

## 5. Permisos

Haz que estas carpetas tengan permiso **755** (en algunos hostings **775**):

- `inventario-hotwheels-app/writable` y todo lo que tiene dentro
- `public_html/inventario-hotwheels/fotos`

## 6. Primera vez

1. Abre **https://bixosdement.com/inventario-hotwheels/instalar**
2. Escribe tu nombre, un usuario (por ejemplo `victor`) y una contraseña de al menos 8 caracteres.
3. Toca **Crear base de datos y cuenta**. Se crean las tablas solas y la página se desactiva.
4. Entra con tu usuario.

## 7. Pasa tu inventario de claude.ai

1. Abre la app de claude.ai → pestaña **Catálogo** → botón **Exportar para mi sitio (ZIP)**.
   Se descarga `inventario-petus.zip` con tus productos y fotos.
2. En tu sitio entra a **Inventario → Importar** y sube ese archivo.
   - Si el archivo es muy grande y marca error, súbelo con el Administrador de archivos a
     `inventario-hotwheels-app/writable/import/` con el nombre `inventario-petus.zip`
     y toca **Importar el archivo de la carpeta**.
3. Puedes importar otra vez después: los productos se actualizan, no se duplican.

## 8. Agrega a los vendedores

En **Inventario → Vendedores** crea a Jonathan y a María Guadalupe con su usuario y contraseña.
Ellos entran en **https://bixosdement.com/inventario-hotwheels/** y solo ven Vender y Catálogo.

---

## Si algo falla

- **Página en blanco o "Whoops"**: revisa los datos de MySQL en `.env` y los permisos de `writable`.
  Los detalles del error quedan en `inventario-hotwheels-app/writable/logs/`.
- **No carga la página o te manda a WordPress**: confirma que `.htaccess` sí se subió dentro de
  `public_html/inventario-hotwheels/`.
- **No se suben fotos**: revisa el permiso de `public_html/inventario-hotwheels/fotos`.
- **Wordfence bloquea algo**: en WordPress → Wordfence → Firewall puedes poner en lista
  permitida la ruta `/inventario-hotwheels/`.
- **Respaldo**: respalda la base de datos (cPanel → phpMyAdmin → Exportar) y la carpeta `fotos`.
