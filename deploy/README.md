# Despliegue en el VPS

Guía para publicar los cambios en el servidor de producción (VPS sin Docker).
La base de datos es RDS y ya está migrada; aun así, `php artisan migrate --force` es seguro de repetir.

## 1. Requisitos de PHP (una sola vez)

Las miniaturas WebP y las notificaciones push necesitan estas extensiones:

```bash
php -m | grep -Ei 'gd|gmp|bcmath'
php -r 'var_dump(function_exists("imagewebp"));'   # debe decir bool(true)
```

Si falta alguna (Ubuntu/Debian, ajusta la versión de PHP):

```bash
sudo apt install php8.2-gd php8.2-gmp php8.2-bcmath
sudo systemctl restart php8.2-fpm   # o apache2, según el servidor
```

Si `imagewebp` no existe, las miniaturas se generan en JPEG: funciona, pero pesan más.

## 2. Actualizar el código

```bash
cd /var/www/tigreteve            # ruta real del proyecto
git fetch && git checkout mejoras-difusion   # o master, cuando se fusione
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 3. Variables del `.env` de producción

```dotenv
APP_URL=https://<dominio-real>
VAPID_SUBJECT=mailto:<correo-de-contacto>
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

Las claves VAPID se generan una vez con `php artisan push:claves`. No las cambies después:
los suscriptores quedarían sin avisos. Tras editar el `.env`, ejecuta `php artisan config:cache`.

## 4. Worker de la cola con Supervisor (una sola vez)

Sin worker, los envíos de WhatsApp quedan en "pendiente", los avisos push no salen
y las noticias nuevas no tienen miniatura.

```bash
sudo apt install supervisor
sudo cp deploy/supervisor/tigreteve-worker.conf /etc/supervisor/conf.d/
sudo nano /etc/supervisor/conf.d/tigreteve-worker.conf   # ajustar la ruta y el usuario
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status tigreteve-worker:*
```

Después de **cada** despliegue, reinicia el worker para que tome el código nuevo:

```bash
php artisan queue:restart
```

Log del worker: `storage/logs/worker.log`. Trabajos fallidos: `php artisan queue:failed`
(reintentar con `php artisan queue:retry all`).

## 5. Miniaturas de noticias anteriores (opcional)

```bash
php artisan noticias:miniaturas           # las encola para el worker
php artisan noticias:miniaturas --ahora   # o las procesa en el momento
```

## 6. Meta / WhatsApp (cuando el sitio nuevo esté publicado)

Parámetros en la tabla `configuracions` (`parametro` / `valor`):

| parametro | Para qué |
|---|---|
| `webhook_token` | Token de verificación que escribes en Meta al registrar el webhook |
| `app_secret` | Clave secreta de la app (Meta → Configuración de la app → Básica) |
| `plantilla_noticia` | Nombre de la plantilla para enviar noticias del sitio |
| `plantilla_noticia_idioma` | Opcional, por defecto `es` |

- **Webhook:** URL `https://<dominio>/webhooks/whatsapp`, suscrito al campo `messages`.
- **Plantilla de noticias:** encabezado de imagen, cuerpo con variables `{{titulo}}` y `{{cuerpo}}`,
  botón de URL dinámica `https://<dominio>/noticias/{{1}}`.

## Comprobación rápida

- `https://<dominio>/robots.txt` y `/sitemap.xml` responden.
- Publicar una noticia de prueba con portada: en unos segundos se llena su miniatura.
- Envíos de WhatsApp: `/whatsapp-masivo/envios` muestra entregados y leídos (con el webhook activo).
