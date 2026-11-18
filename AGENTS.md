# AGENTS.md

## Stack
- Laravel 11 / PHP 8.2, Blade + Livewire 3, Breeze auth, Spatie Permission.
- Frontend: Tailwind 3 **and** Bootstrap 5 via Vite, Alpine, PWA (service worker `/sw.js`, manifest `/manifest.json`).
- MySQL; uploads on S3; queue, cache and session use the database driver.

## Design work
- For **any** UI/visual design request (new views, modals, cards, redesigns, styling), read and follow the installed `frontend-design` skill first: `~/.agents/skills/frontend-design/SKILL.md`. Use its plan → critique → build process instead of defaulting to generic layouts.

## Commands
- Install: `composer install` then `npm install`.
- Dev: `php artisan serve` + `npm run dev` (Vite). Build: `npm run build`.
- Migrate: `php artisan migrate`.
- Queue worker: `php artisan queue:work` — required for WhatsApp sends, push notifications and WebP thumbnails (`QUEUE_CONNECTION=database`); without it `whatsapp_logs` stay `pending`. In Docker it is the `queue` service.
- Tests: `php artisan test`; single test `php artisan test --filter=NombreTest` or pass a path. No local PHP on the dev machine: run inside the container (`docker exec laravel_apache php artisan test`).
- Backfill thumbnails: `php artisan noticias:miniaturas [--ahora] [--todas]`. New VAPID keys: `php artisan push:claves`.
- Format: `vendor/bin/pint` (no CI workflows exist).
- Docker: `docker compose up --build` → http://localhost:8080 (Apache image builds assets; source is volume-mounted). This is local dev only.
- Production is a VPS without Docker: deploy steps, required PHP extensions (`gd` with WebP, `gmp`, `bcmath`) and the Supervisor worker config live in `deploy/` (`deploy/README.md`). Run `php artisan queue:restart` after every deploy.

## Critical gotchas
- **`.env` points at AWS RDS** (likely production). Never run `migrate`, tinker writes or a local queue worker against it without asking. Tests use sqlite `:memory:` via `phpunit.xml`; keep it that way (Feature tests use `RefreshDatabase`). The 4 Breeze auth tests (missing `dashboard` route) and `ExampleTest` (no `RefreshDatabase`) fail by design of the existing code.
- **Bootstrap class conflicts.** `layouts/app.blade.php` loads Bootstrap 4 from CDN while `resources/css/app.css` imports Bootstrap 5 + Tailwind. BS4 rules like `.modal`, `.modal-header`, `.modal-body` override custom CSS — prefix custom classes (e.g. `confirm-*`).
- **Two RoleMiddleware classes.** `App\Http\Middleware\RoleMiddleware` is unused; `bootstrap/app.php` aliases `role` to Spatie's. Routes use Spatie roles (`role:Administrator`); actions are gated by permissions via `$this->authorize('crear noticias')`. `App\Models\User` has no `role` column.
- **WhatsApp settings are in the DB**, not config: table `configuracions` (`parametro`/`valor`). Keys: `idnumero`, `tokenw` (sending); `webhook_token`, `app_secret` (webhook `/webhooks/whatsapp`, CSRF-exempt in `bootstrap/app.php`); `plantilla_noticia` (+ optional `plantilla_noticia_idioma`, default `es`) for sending site news from the noticia form.
- **The default template `mensajes` (lang `en`, Graph API v25.0) only fits Facebook post links**: its URL button takes `posts/{id}` from `https://www.facebook.com/{page}/posts/{id}`. Site news need a separate Meta template whose button base is `https://<domain>/noticias/{{1}}` (suffix = slug), configured as `plantilla_noticia`.
- **WhatsApp template text rules**: no newlines/tabs and max 4 consecutive spaces. `EnviarWhatsAppJob` sanitizes `titulo`/`cuerpo`; preserve that.
- **Opt-outs**: replies like "SALIR" land in `whatsapp_bajas`; `EnvioWhatsApp::encolar` and the job skip them (matched by last 10 digits). Always enqueue through `EnvioWhatsApp`.
- **Queued jobs must not call `route()`/`url()` for public links**: the worker uses `APP_URL` (`http://localhost` locally). Compute URLs in the request and pass them in (see `AvisosNoticia`).
- **Push notifications** need `VAPID_PUBLIC_KEY`/`VAPID_PRIVATE_KEY` in `.env` (`config/services.php` → `webpush`); without them the opt-in prompt and the form checkbox are hidden/disabled.
- **Catch-all route** `Route::get('/{slug?}', ...)` is last in `routes/web.php`; register new routes before it.
- No `.env.example` and no CI. `.env` (MySQL + `AWS_*` S3 keys) must be created manually.

## Architecture
- Public site: `NoticiaController@p` renders `principal` for the homepage and category-by-slug (catch-all). Visibility comes from model scopes: `publicadas()` (estado `publicada`, or `programada` with `publicado_en <= now`), `destacadas()` / `NoD()` (`is_featured`); use `publicadas()` (or `Noticia::esVisible()`) for anything public. `@show` counts reads through `RegistroVistas` (skips bots/prefetch, dedupes IP+UA for 30 min in cache) into `noticias.vistas` and daily `noticia_vistas` (Colombia day), shown in `/panel/estadisticas`.
- Per-page SEO: `layouts/home` yields `meta` (OG/Twitter/JSON-LD in `noticias/show`); `/sitemap.xml`, `/sitemap-noticias.xml`, `/robots.txt` (dynamic, no static file) and `/feed` come from `SeoController`, cached and cleared by `NoticiaObserver`.
- `NoticiaObserver` dispatches `GenerarMiniaturaJob` whenever `portada_path` changes (640px WebP in `imagenes/miniaturas/`, `miniatura_path`); listings use `miniaturaUrl()`, OG/WhatsApp keep the original.
- Noticia form "Difusión" (`noticias/_avisos`): `urgente` (24h "Última hora" strip on the homepage), push (`EnviarPushNoticiaJob`) and WhatsApp via `AvisosNoticia`; scheduled news get delayed jobs that re-check visibility; `push_enviado_en` / `whatsapp_enviado_en` prevent duplicates.
- Comments: public form on `noticias/show` (`ComentarioController@store`, throttle + honeypot, published immediately); admin moderation at `/panel/comentarios`.
- Admin: `@index` renders `panel.dashboard`; CRUD via `Route::resource('noticias')` (except `index`).
- Images: stored on S3 under `imagenes/`; `noticias.portada_path` holds the S3 path.
- WhatsApp mass send: `whatsapp/masivo` → `mensajeMasivo` (validates titulo, cuerpo, link URL, image URL) collects phones from `numeros` and/or CSV upload → dispatches one `EnviarWhatsAppJob` per phone and logs a `whatsapp_logs` row with a shared `batch_id`.
- Homepage "load more" is Livewire `App\Livewire\NoticiasList`.

## Conventions
- UI copy, comments and route/view names are Spanish; keep user-facing strings in Spanish.
- `.editorconfig`: 4-space indent, LF.
- Views extend `<x-app-layout>` but embed their own full HTML document/head (Bootstrap CDN + Font Awesome) and often inline `<style>`; follow that existing pattern.
