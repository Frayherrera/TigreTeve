<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\NumeroController;


Route::get('/whatsapp-masivo', [WhatsAppController::class, 'index'])->middleware('role:Administrator') ->name('whatsapp.masivovista')    ;
Route::post('/whatsapp-masivo/enviar', [WhatsAppController::class, 'mensajeMasivo'])->name('whatsapp.masivo')->middleware('role:Administrator');
Route::get('/whatsapp-masivo/resultados/{batchId}', [WhatsAppController::class, 'resultados'])->name('whatsapp.resultados')->middleware('role:Administrator');
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
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

require __DIR__ . '/auth.php';

Route::get('/{slug?}', [NoticiaController::class, 'p'])->name('noticias.home');
