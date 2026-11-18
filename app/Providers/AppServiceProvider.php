<?php

namespace App\Providers;

use App\Models\Noticia;
use App\Observers\NoticiaObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Miniaturas WebP y limpieza de la caché del sitemap/feed al guardar noticias.
        Noticia::observe(NoticiaObserver::class);
    }
}
