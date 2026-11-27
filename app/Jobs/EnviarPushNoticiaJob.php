<?php

namespace App\Jobs;

use App\Models\Noticia;
use App\Models\PushSuscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Avisa por notificación push a todos los suscriptores que salió una noticia.
 * Si la noticia es programada, se encola con retraso hasta su fecha; al ejecutarse
 * vuelve a comprobar que siga visible y que no se haya avisado ya.
 */
class EnviarPushNoticiaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $timeout = 600;

    /** $url se calcula en la petición web: en el worker route() usaría APP_URL. */
    public function __construct(public int $noticiaId, public string $url) {}

    public function handle(): void
    {
        $noticia = Noticia::find($this->noticiaId);
        if (! $noticia || ! $noticia->esVisible() || $noticia->push_enviado_en) {
            return;
        }

        $config = config('services.webpush');
        if (! $config['public_key'] || ! $config['private_key']) {
            Log::warning('Push sin claves VAPID: no se envió el aviso.', ['noticia' => $noticia->id]);

            return;
        }

        // Se marca antes de enviar para que un reintento no duplique avisos.
        $noticia->forceFill(['push_enviado_en' => now()])->saveQuietly();

        $webPush = new WebPush(['VAPID' => [
            'subject' => $config['subject'],
            'publicKey' => $config['public_key'],
            'privateKey' => $config['private_key'],
        ]], ['TTL' => 6 * 3600, 'urgency' => $noticia->urgente ? 'high' : 'normal']);

        $contenido = json_encode([
            'title' => ($noticia->urgente ? 'Última hora: ' : '').$noticia->titulo,
            'body' => Str::limit(trim(preg_replace('/\s+/', ' ', $noticia->resumen ?: strip_tags($noticia->cuerpo))), 140),
            'url' => $this->url,
            'image' => $noticia->miniaturaUrl(),
            'tag' => 'noticia-'.$noticia->id,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        PushSuscripcion::query()->chunkById(500, function ($suscripciones) use ($webPush, $contenido) {
            foreach ($suscripciones as $s) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $s->endpoint,
                    'publicKey' => $s->p256dh,
                    'authToken' => $s->auth,
                ]), $contenido);
            }

            $vencidas = [];
            foreach ($webPush->flush() as $reporte) {
                // 404/410: el navegador anuló la suscripción; se borra.
                if ($reporte->isSubscriptionExpired()) {
                    $vencidas[] = hash('sha256', $reporte->getEndpoint());
                }
            }
            if ($vencidas) {
                PushSuscripcion::whereIn('endpoint_hash', $vencidas)->delete();
            }
        });
    }
}
