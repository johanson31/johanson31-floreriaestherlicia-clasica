# Despliegue en cPanel (floreriaestherlicia.com)

## Cómo queda

```
/home/floreriaestherli/
├── web/                 ← Laravel (floreria-moderna): NO se mueve
│   └── public/          ← raíz de admin.floreriaestherlicia.com (admin + API + fotos)
├── clasica/             ← web clásica completa (src, templates, config, storage)
└── public_html/         ← solo el contenido de clasica/public → floreriaestherlicia.com
```

Hoy `public_html` tiene una copia del `public/` de Laravel. Esa copia se reemplaza por la web
clásica, y Laravel pasa a responder en el subdominio usando su propio `~/web/public`.
**El orden importa:** primero el subdominio (paso 2), después la web clásica (paso 4).

Todos los comandos van en WHM → Terminal, **como el usuario del sitio**, no como root:

```bash
su - floreriaestherli -s /bin/bash
alias php='/opt/cpanel/ea-php84/root/usr/bin/php'
```

## 0. Respaldos

```bash
mkdir -p ~/backups
tar -czf ~/backups/public_html_$(date +%F).tar.gz -C ~ public_html
cd ~/web && php artisan db:show | head -8          # nombre y usuario de la BD
mysqldump -u USUARIO_BD -p NOMBRE_BD > ~/backups/floreria_$(date +%F_%H%M).sql
```

## 1. PHP 8.4

cPanel → **MultiPHP Manager**: `floreriaestherlicia.com` (y luego el subdominio) en **PHP 8.4**.

## 2. Laravel en `admin.floreriaestherlicia.com`

1. cPanel → **Domains → Create A New Domain**: `admin.floreriaestherlicia.com`,
   raíz del documento **`web/public`** (desmarcar "Share document root").
2. Si el DNS del dominio está en Cloudflare u otro proveedor, crear ahí el registro `A` de `admin`
   con la misma IP del dominio. Luego cPanel → **SSL/TLS Status → Run AutoSSL**.
3. En `~/web/.env` cambiar `APP_URL=https://admin.floreriaestherlicia.com` y luego:

```bash
cd ~/web
ls -la public/storage                                   # debe ser un enlace a storage/app/public
php artisan optimize:clear && php artisan optimize
curl -s https://admin.floreriaestherlicia.com/api/catalogo/categorias | head -c 200
curl -sI https://admin.floreriaestherlicia.com/storage/productos/101_cruces1C.jpg | head -1   # 200
```

Hasta aquí la web de siempre (`floreriaestherlicia.com`) sigue funcionando igual.

## 3. Catálogo antiguo completo (una sola vez)

Subir `floreria-laravel-catalogo.zip` a `~/web` y extraerlo (reemplaza 2 archivos y agrega 2). Luego:

```bash
cd ~/web
php artisan db:seed --class=CatalogoLegacySeeder --force
# "Catálogo antiguo importado: 180 actualizados, 247 creados, N desactivados."
php artisan optimize:clear && php artisan optimize
```

## 4. Web clásica

Subir `floreriaestherlicia-clasica.zip` a `~` (la carpeta personal) y extraerlo ahí: crea `~/clasica`.

```bash
cd ~/clasica
cp config/config.example.php config/config.php     # api_url ya apunta a admin.floreriaestherlicia.com
chmod 750 storage/cache

# Guardar lo que hay hoy en public_html (la copia del public de Laravel).
# No se mueve la carpeta en sí: cPanel la necesita con su dueño y permisos; .well-known (SSL) se queda.
mkdir -p ~/public_html_laravel
mv ~/public_html/* ~/public_html/.htaccess ~/public_html_laravel/

# Copiar el public de la web clásica (incluye su .htaccess)
cp -a ~/clasica/public/. ~/public_html/
```

`public_html/index.php` encuentra solo el código en `~/clasica`; no hay que editarlo.

**Volver atrás** si algo falla: `rm -rf ~/public_html/* ~/public_html/.htaccess && mv ~/public_html_laravel/* ~/public_html_laravel/.htaccess ~/public_html/`

## 5. Verificar

```bash
curl -sI https://floreriaestherlicia.com/ | grep -iE "^HTTP|content-security"
curl -s https://floreriaestherlicia.com/categoria/cruces | grep -c "Código:"      # 24 (12 productos)
curl -sI "https://floreriaestherlicia.com/prod-de.php?cate=18" | grep -i location # /categoria/cruces
curl -sI https://floreriaestherlicia.com/admin/wp-login.php | head -1            # 404
```

En el navegador: ver una categoría, abrir una foto, hacer un pedido de prueba y confirmar que llega el correo.
El panel admin queda en `https://admin.floreriaestherlicia.com`.

## 6. Limpiar

Cuando todo esté bien:

```bash
rm -rf ~/public_html_laravel            # la copia vieja del public de Laravel (Laravel usa ~/web/public)
```

Los respaldos quedan en `~/backups`.

## Actualizar más adelante

Código (`src/`, `templates/`): subirlo a `~/clasica`. Estilos, JS o imágenes: copiarlos también a `~/public_html/assets`.
Después borrar la caché:

```bash
rm -f ~/clasica/storage/cache/*.json
```
