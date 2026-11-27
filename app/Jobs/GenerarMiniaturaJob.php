<?php

namespace App\Jobs;

use App\Models\Noticia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Crea una versión liviana de la portada (640 px de ancho, WebP; JPEG si el servidor
 * no soporta WebP) y la guarda en S3 en imagenes/miniaturas/. La original no se toca:
 * se sigue usando para la vista previa al compartir y para WhatsApp.
 */
class GenerarMiniaturaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const ANCHO = 640;

    public $tries = 2;

    public function __construct(public int $noticiaId) {}

    public function handle(): void
    {
        $noticia = Noticia::find($this->noticiaId);
        if (! $noticia || ! $noticia->portada_path) {
            return;
        }

        $disco = Storage::disk('s3');
        $portadaOrigen = $noticia->portada_path;

        $original = @imagecreatefromstring($disco->get($portadaOrigen));
        if (! $original) {
            throw new RuntimeException("No se pudo leer la imagen {$portadaOrigen}");
        }

        $ancho = imagesx($original);
        $alto = imagesy($original);
        $nuevoAncho = min(self::ANCHO, $ancho);
        $nuevoAlto = (int) round($alto * $nuevoAncho / $ancho);

        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        imagecopyresampled($lienzo, $original, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($lienzo, null, 78);
            $extension = 'webp';
        } else {
            imagejpeg($lienzo, null, 80);
            $extension = 'jpg';
        }
        $binario = ob_get_clean();
        imagedestroy($original);
        imagedestroy($lienzo);

        $ruta = 'imagenes/miniaturas/'.pathinfo($portadaOrigen, PATHINFO_FILENAME).'.'.$extension;
        $disco->put($ruta, $binario, ['ContentType' => $extension === 'webp' ? 'image/webp' : 'image/jpeg']);

        // La portada pudo cambiar mientras se procesaba: no pisar una más nueva.
        $noticia->refresh();
        if ($noticia->portada_path !== $portadaOrigen) {
            $disco->delete($ruta);

            return;
        }

        $anterior = $noticia->miniatura_path;
        $noticia->forceFill(['miniatura_path' => $ruta])->saveQuietly();

        if ($anterior && $anterior !== $ruta) {
            $disco->delete($anterior);
        }
    }
}
