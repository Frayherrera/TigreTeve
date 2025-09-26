<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\WeatherController;
use App\Models\Noticia;


Route::get('/dashboard', [NoticiaController::class, 'index'])->name('noticias.index');
// Route::get('/', [NoticiaController::class, 'p'])->name('noticias.home');
Route::get('/noticia/{id}', [NoticiaController::class, 'show'])->name('news.show');
Route::get('/', function(){
    return view('welcome');
});

Route::get('/dashboard', function () {
    $noticias = Noticia::latest()->get();
    return view('panel.dashboard', compact('noticias'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::resource('noticias', NoticiaController::class);
Route::get('/noticias/{slug}', [NoticiaController::class, 'show'])->name('noticias.show');
require __DIR__ . '/auth.php';
