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
- Queue worker: `php artisan queue:work` — required for WhatsApp sends (`QUEUE_CONNECTION=database`); without it `whatsapp_logs` stay `pending`.
- Tests: `php artisan test`; single test `php artisan test --filter=NombreTest` or pass a path.
- Format: `vendor/bin/pint` (no CI workflows exist).
- Docker: `docker compose up --build` → http://localhost:8080 (Apache image builds assets; source is volume-mounted).

## Critical gotchas
- **Tests can wipe the real database.** `phpunit.xml` leaves `DB_CONNECTION` commented out and Feature tests use `RefreshDatabase`, so they run against the `.env` MySQL DB. Set a test DB (e.g. sqlite `:memory:`) before running.
- **Bootstrap class conflicts.** `layouts/app.blade.php` loads Bootstrap 4 from CDN while `resources/css/app.css` imports Bootstrap 5 + Tailwind. BS4 rules like `.modal`, `.modal-header`, `.modal-body` override custom CSS — prefix custom classes (e.g. `confirm-*`).
- **Two RoleMiddleware classes.** `App\Http\Middleware\RoleMiddleware` is unused; `bootstrap/app.php` aliases `role` to Spatie's. Routes use Spatie roles (`role:Administrator`); actions are gated by permissions via `$this->authorize('crear noticias')`. `App\Models\User` has no `role` column.
- **WhatsApp credentials are in the DB**, not config: table `configuracions` (`parametro`/`valor`) keys `idnumero` and `tokenw`. The template is hardcoded in `EnviarWhatsAppJob` (`mensajes`, lang `en`, Graph API v25.0).
- **WhatsApp template text rules**: no newlines/tabs and max 4 consecutive spaces. `EnviarWhatsAppJob` sanitizes `titulo`/`cuerpo`; preserve that.
- **`WhatsAppController@resultados` is referenced in `routes/web.php` but not implemented** — that route will 500.
- **Catch-all route** `Route::get('/{slug?}', ...)` is last in `routes/web.php`; register new routes before it.
- No `.env.example` and no CI. `.env` (MySQL + `AWS_*` S3 keys) must be created manually.

## Architecture
- Public site: `NoticiaController@p` renders `principal` for the homepage and category-by-slug (catch-all). Visibility comes from model scopes: `publicadas()` (estado `publicada`, or `programada` with `publicado_en <= now`), `destacadas()` / `NoD()` (`is_featured`). `@show` increments `vistas`.
- Admin: `@index` renders `panel.dashboard`; CRUD via `Route::resource('noticias')` (except `index`).
- Images: stored on S3 under `imagenes/`; `noticias.portada_path` holds the S3 path.
- WhatsApp mass send: `whatsapp/masivo` → `mensajeMasivo` (validates titulo, cuerpo, link URL, image URL) collects phones from `numeros` and/or CSV upload → dispatches one `EnviarWhatsAppJob` per phone and logs a `whatsapp_logs` row with a shared `batch_id`.
- Homepage "load more" is Livewire `App\Livewire\NoticiasList`.

## Conventions
- UI copy, comments and route/view names are Spanish; keep user-facing strings in Spanish.
- `.editorconfig`: 4-space indent, LF.
- Views extend `<x-app-layout>` but embed their own full HTML document/head (Bootstrap CDN + Font Awesome) and often inline `<style>`; follow that existing pattern.
