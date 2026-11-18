# Despliegue en cPanel (floreriaestherlicia.com)

Resultado final:

| Dominio | Carpeta | Qué es |
|---|---|---|
| `floreriaestherlicia.com` | `~/clasica/public` | Esta web (diseño clásico) |
| `admin.floreriaestherlicia.com` | `~/web/public` | Laravel: panel admin + API + fotos |

Todos los comandos se ejecutan en WHM → Terminal, **como el usuario del sitio**, no como root:

```bash
su - floreriaestherli -s /bin/bash
alias php='/opt/cpanel/ea-php84/root/usr/bin/php'
```

## 0. Respaldos (antes de tocar nada)

```bash
mkdir -p ~/backups
tar -czf ~/backups/public_html_$(date +%F).tar.gz -C ~ public_html
cd ~/web && php artisan db:show | head -8          # nombre y usuario de la BD
mysqldump -u USUARIO_BD -p NOMBRE_BD > ~/backups/floreria_$(date +%F_%H%M).sql
```

## 1. Ver a qué carpeta apunta hoy el dominio (como root, con `exit`)

```bash
grep -i documentroot /var/cpanel/userdata/floreriaestherli/floreriaestherlicia.com
```

## 2. Laravel en `admin.floreriaestherlicia.com`

1. cPanel → **Domains → Create A New Domain**: `admin.floreriaestherlicia.com`, raíz del documento `web/public`
   (desmarcar "Share document root").
2. cPanel → **SSL/TLS Status → Run AutoSSL** (o el certificado que use el dominio).
3. En `~/web/.env`: `APP_URL=https://admin.floreriaestherlicia.com` y luego:

```bash
cd ~/web && php artisan optimize:clear && php artisan optimize
curl -s https://admin.floreriaestherlicia.com/api/catalogo/categorias | head -c 200
```

## 3. Catálogo antiguo completo (una sola vez)

Subir `floreria-laravel-catalogo.zip` a `~/web` y extraerlo (reemplaza 3 archivos y agrega 2). Luego:

```bash
cd ~/web
php artisan db:seed --class=CatalogoLegacySeeder --force
# "Catálogo antiguo importado: 180 actualizados, 247 creados, N desactivados."
php artisan optimize:clear && php artisan optimize
```

## 4. Web clásica

Subir `floreriaestherlicia-clasica.zip` a `~` y extraerlo en `~/clasica`. Luego:

```bash
cd ~/clasica
cp config/config.example.php config/config.php     # api_url ya apunta a admin.floreriaestherlicia.com
chmod 750 storage/cache
```

Apuntar el dominio principal a `~/clasica/public`. Si en el paso 1 la raíz era `public_html`:

```bash
mv ~/public_html ~/public_html_antigua
ln -s /home/floreriaestherli/clasica/public /home/floreriaestherli/public_html
```

Si la raíz era otra carpeta (por ejemplo `web/public`), cambiarla como root en WHM o pedir a soporte que la
apunte a `clasica/public`.

## 5. Verificar

```bash
curl -sI https://floreriaestherlicia.com/ | grep -iE "^HTTP|content-security"
curl -s https://floreriaestherlicia.com/categoria/cruces | grep -c "Código:"      # 24 (12 productos)
curl -sI "https://floreriaestherlicia.com/prod-de.php?cate=18" | grep -i location # /categoria/cruces
curl -sI https://floreriaestherlicia.com/admin/wp-login.php | head -1            # 404
```

Probar en el navegador un pedido real y confirmar que llega el correo.

## 6. Limpiar

Cuando todo esté bien, borrar la web antigua (tiene código vulnerable). El respaldo queda en `~/backups`:

```bash
rm -rf ~/public_html_antigua
```

## Actualizar más adelante

Subir los archivos cambiados a `~/clasica` (nunca a `public/` cosas que no sean assets) y borrar la caché:

```bash
rm -f ~/clasica/storage/cache/*.json
```
