<?php

namespace App\Services;

use App\Jobs\EnviarPushNoticiaJob;
use App\Models\Configuracion;
use App\Models\Noticia;
use App\Models\Numero;
use App\Support\Telefono;
use Illuminate\Support\Str;

/**
 * Avisos que se disparan desde el formulario de la noticia.
 * Si la noticia es programada, el aviso se encola para su fecha de publicación.
 * Las URL se calculan aquí (petición web) y no en el worker, que no conoce el dominio.
 */
class AvisosNoticia
{
    public function __construct(private EnvioWhatsApp $envioWhatsApp) {}

    /** Plantilla de Meta para noticias del sitio (botón con URL base .../noticias/{{1}}). */
    public static function plantillaWhatsApp(): ?string
    {
        return Configuracion::valor('plantilla_noticia');
    }

    /** @return string|null Mensaje para el editor, o null si no aplica. */
    public function push(Noticia $noticia): ?string
    {
        $cuando = $noticia->momentoDePublicacion();
        if (! $cuando || $noticia->push_enviado_en) {
            return null;
        }

        $job = new EnviarPushNoticiaJob($noticia->id, route('noticias.show2', $noticia->slug));
        if ($cuando->isFuture()) {
            $job->delay($cuando);
        }
        dispatch($job);

        return $cuando->isFuture()
            ? 'El aviso push saldrá cuando se publique la noticia.'
            : 'Aviso push en camino a los suscriptores.';
    }

    /** @return string|null Mensaje para el editor, o null si no aplica. */
    public function whatsapp(Noticia $noticia): ?string
    {
        $plantilla = static::plantillaWhatsApp();
        $cuando = $noticia->momentoDePublicacion();
        if (! $plantilla || ! $cuando || $noticia->whatsapp_enviado_en) {
            return null;
        }

        $telefonos = Numero::pluck('numero')
            ->map(fn ($n) => Telefono::paraEnvio($n))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($telefonos)) {
            return 'No se envió por WhatsApp: no hay números guardados.';
        }

        $lote = $this->envioWhatsApp->encolar($telefonos, [
            'noticia_id' => $noticia->id,
            'plantilla' => $plantilla,
            'idioma' => Configuracion::valor('plantilla_noticia_idioma', 'es'),
            'titulo' => $noticia->titulo,
            'cuerpo' => Str::limit(trim(preg_replace('/\s+/', ' ', $noticia->resumen ?: strip_tags($noticia->cuerpo))), 500),
            'image' => $noticia->portadaUrl() ?? asset('img/logo.png'),
            'boton' => $noticia->slug,
        ], $cuando);

        $noticia->forceFill(['whatsapp_enviado_en' => now()])->saveQuietly();

        $cuantos = $lote['encolados'];

        return $cuando->isFuture()
            ? "{$cuantos} mensajes de WhatsApp programados para cuando se publique."
            : "{$cuantos} mensajes de WhatsApp en cola.";
    }
}
