<?php

namespace App\Observers;

use App\Jobs\GenerarMiniaturaJob;
use App\Models\Noticia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NoticiaObserver
{
    public function saved(Noticia $noticia): void
    {
        if ($noticia->wasChanged('portada_path') || ($noticia->wasRecentlyCreated && $noticia->portada_path)) {
            if ($noticia->portada_path) {
                // La miniatura es opcional: si falla (p. ej. cola en modo sync), no debe impedir guardar.
                try {
                    GenerarMiniaturaJob::dispatch($noticia->id);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo generar la miniatura', ['noticia' => $noticia->id, 'error' => $e->getMessage()]);
                }
            } elseif ($noticia->miniatura_path) {
                $this->borrar($noticia->miniatura_path);
                $noticia->forceFill(['miniatura_path' => null])->saveQuietly();
            }
        }

        $this->limpiarCacheSeo();
    }

    public function deleted(Noticia $noticia): void
    {
        if ($noticia->miniatura_path) {
            $this->borrar($noticia->miniatura_path);
        }

        $this->limpiarCacheSeo();
    }

    private function limpiarCacheSeo(): void
    {
        foreach (['seo.sitemap', 'seo.sitemap_noticias', 'seo.feed'] as $clave) {
            Cache::forget($clave);
        }
    }

    private function borrar(string $ruta): void
    {
        try {
            Storage::disk('s3')->delete($ruta);
        } catch (\Throwable $e) {
            Log::warning('No se pudo borrar la miniatura de S3', ['ruta' => $ruta, 'error' => $e->getMessage()]);
        }
    }
}
