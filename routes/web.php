<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\WhatsAppController;


Route::get('/whatsapp-masivo', [WhatsAppController::class, 'index'])->middleware('role:Administrator') ->name('whatsapp.masivovista')    ;
Route::post('/whatsapp-masivo/enviar', [WhatsAppController::class, 'mensajeMasivo'])->name('whatsapp.masivo')->middleware('role:Administrator');
Route::get('/whatsapp-masivo/resultados/{batchId}', [WhatsAppController::class, 'resultados'])->name('whatsapp.resultados')->middleware('role:Administrator');

Route::get('/noticia/{id}', [NoticiaController::class, 'show'])->name('news.show');

Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index')->middleware('role:Administrator');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::resource('noticias', NoticiaController::class)->except(['index']);
Route::get('/noticias/{slug}', [NoticiaController::class, 'show'])->name('noticias.show2');

require __DIR__ . '/auth.php';

Route::get('/{slug?}', [NoticiaController::class, 'p'])->name('noticias.home');
