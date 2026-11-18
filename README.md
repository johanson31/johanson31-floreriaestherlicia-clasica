# Florería Estherlicia — web clásica

El diseño de siempre de floreriaestherlicia.com (encabezado azul, menú amarillo, fondo
verde, margaritas que caen), reconstruido sin el código viejo. El catálogo se lee de la
API del Laravel (`floreriaestherlicia-2026`), que sigue siendo el panel de administración.

## Qué se quitó de la web antigua y por qué

| Antes | Problema | Ahora |
|---|---|---|
| PHP 5 + `mysql_*` | No corre en PHP 7/8; SQL armado con `$_GET` | Sin base de datos: solo lee la API |
| `admin/` propio | Puerta de entrada (había hasta un `wp-login.php` falso) | Se administra en Laravel |
| `index.php` con código inyectado, `moist.php`, `StereosStarved.php` | Restos de una infección | No existen; cualquier `.php` que no sea `index.php` da 404 |
| `$_GET['moneda']`, `$_GET['tipo']` impresos tal cual | XSS | Todo pasa por listas blancas y `e()` |
| jQuery 1.4, cross-slide, highslide, Flash | Abandonados, con vulnerabilidades / no funcionan | JS propio, sin dependencias |
| Contador de visitas en archivo | Escritura en disco en cada visita | Eliminado |

## Arquitectura

```
public/            ← única carpeta expuesta (raíz del dominio)
  index.php        ← front controller
  assets/          ← css, js, imágenes del diseño original
src/
  Catalogo/        ← Producto, PaginaProductos, CatalogoRepository (interfaz), ApiCatalogoRepository
  Http/            ← HttpClient (interfaz) + CurlHttpClient
  Support/         ← FileCache (JSON, con copia de respaldo si la API cae)
  Web/             ← Aplicacion (rutas), Menu (estructura del menú), Vista, Respuesta
  Arranque.php     ← composition root + cabeceras de seguridad (CSP estricta)
templates/         ← HTML de cada página
config/            ← config.php (no va a git; copiar de config.example.php)
storage/cache/     ← caché de la API
```

- **Repository + inversión de dependencias:** las páginas dependen de `CatalogoRepository`;
  la API es solo una implementación. Los tests usan un repositorio en memoria.
- **Sin Composer en producción:** `public/index.php` trae su propio autoload. Composer solo
  hace falta para correr los tests.
- **Pedidos y contacto:** el navegador envía el formulario a `POST {api}/api/contacto`
  (Laravel valida, limita a 5 por minuto y manda el correo). En el pedido, además, se
  ofrece un botón para enviarlo por WhatsApp.

### Contrato de la API que usa

| Uso | Endpoint |
|---|---|
| Listado | `GET /api/catalogo/productos?categoria_slug=cruces&orden=codigo&per_page=12&page=1` (también `tipo`, `precio_min`, `precio_max`, `buscar`) |
| Datos del sitio | `GET /api/config/site` → `whatsapp_contacto` |
| Pedido / contacto | `POST /api/contacto` `{nombre, email, telefono?, asunto, mensaje}` → `200 {message}` / `422` / `429` |

## Rutas

| Ruta | Antes |
|---|---|
| `/` | `index.php` |
| `/categoria/{slug}?pagina=N` | `prod-*.php?cate=N` (redirige 301) |
| `/tipo/{rosas…}` | `busq-x-tipo.php?tipo=` (301) |
| `/precio?rango=301-500` | `busq-x-precio.php` |
| `/buscar?prefijo=ANI-&numero=1503` | `buscar-resultado.php` |
| `/pedido/{CODIGO}` | `pedido.php?idproducto=` |
| `/ofertas`, `/nuestra-empresa`, `/contactenos`, `/cuidado-de-las-flores`, `/dedicatorias` | sus `.php` (301) |

## Desarrollo

```bash
cp config/config.example.php config/config.php   # api_url = Laravel local, debug = true
composer install                                   # solo para PHPUnit
composer test
composer serve                                     # http://127.0.0.1:8090
```

## Despliegue en cPanel

Ver [DEPLOY.md](DEPLOY.md).
