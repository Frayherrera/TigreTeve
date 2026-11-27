<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\NumeroController;
use App\Http\Controllers\NoticiaExternaController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\WhatsAppWebhookController;

// Buscadores y lectores RSS
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/sitemap-noticias.xml', [SeoController::class, 'sitemapNoticias'])->name('seo.sitemap_noticias');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/feed', [SeoController::class, 'feed'])->name('feed');

// Notificaciones push del navegador
Route::post('/push/suscribir', [PushController::class, 'suscribir'])->middleware('throttle:20,1')->name('push.suscribir');
Route::post('/push/desuscribir', [PushController::class, 'desuscribir'])->middleware('throttle:20,1')->name('push.desuscribir');

// Webhook de WhatsApp Cloud API (Meta). Sin CSRF: ver bootstrap/app.php.
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verificar'])->name('webhooks.whatsapp.verificar');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'recibir'])->name('webhooks.whatsapp');

Route::get('/whatsapp-masivo', [WhatsAppController::class, 'index'])->middleware('role:Administrator') ->name('whatsapp.masivovista')    ;
Route::post('/whatsapp-masivo/enviar', [WhatsAppController::class, 'mensajeMasivo'])->name('whatsapp.masivo')->middleware('role:Administrator');
Route::get('/whatsapp-masivo/noticias', [WhatsAppController::class, 'noticias'])->name('whatsapp.noticias')->middleware('role:Administrator');
Route::get('/whatsapp-masivo/envios', [WhatsAppController::class, 'lotes'])->name('whatsapp.lotes')->middleware('role:Administrator');
Route::get('/whatsapp-masivo/resultados/{batchId}', [WhatsAppController::class, 'resultados'])->name('whatsapp.resultados')->middleware('role:Administrator');
Route::get('/panel/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index')->middleware('role:Administrator|Editor');
Route::view('/offline', 'offline');
Route::get('/noticia/{id}', [NoticiaController::class, 'show'])->name('news.show');

Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index')->middleware('role:Administrator');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::resource('noticias', NoticiaController::class)->except(['index']);
Route::resource('usuarios', UserController::class)->except(['show'])->middleware('role:Administrator');
Route::middleware('role:Administrator|Editor|Reportero')->group(function () {
    Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index');
});
Route::middleware('role:Administrator|Editor|Reportero')->group(function () {
    Route::get('noticias-externas', [NoticiaExternaController::class, 'index'])->name('externas.index');
    Route::post('noticias-externas/importar', [NoticiaExternaController::class, 'importar'])->name('externas.importar');
});
Route::middleware('role:Administrator|Editor')->group(function () {
    Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
});
Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])
    ->middleware('role:Administrator')
    ->name('categorias.destroy');
Route::middleware('role:Administrator')->group(function () {
    Route::get('numeros', [NumeroController::class, 'index'])->name('numeros.index');
    Route::post('numeros', [NumeroController::class, 'store'])->name('numeros.store');
    Route::post('numeros/importar', [NumeroController::class, 'import'])->name('numeros.import');
    Route::put('numeros/{numero}', [NumeroController::class, 'update'])->name('numeros.update');
    Route::delete('numeros/{numero}', [NumeroController::class, 'destroy'])->name('numeros.destroy');
});
Route::get('/noticias/{slug}', [NoticiaController::class, 'show'])->name('noticias.show2');

// Comentarios: envío público (máx. 5 por minuto por IP) y moderación en el panel.
Route::post('/noticias/{slug}/comentarios', [ComentarioController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('comentarios.store');
Route::middleware('role:Administrator')->prefix('panel/comentarios')->name('comentarios.')->group(function () {
    Route::get('/', [ComentarioController::class, 'index'])->name('index');
    Route::get('noticia/{noticia}', [ComentarioController::class, 'porNoticia'])->name('noticia');
    Route::patch('{comentario}/visibilidad', [ComentarioController::class, 'alternar'])->name('alternar');
    Route::delete('{comentario}', [ComentarioController::class, 'destroy'])->name('destroy');
});
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

require __DIR__ . '/auth.php';

Route::get('/{slug?}', [NoticiaController::class, 'p'])->name('noticias.home');
