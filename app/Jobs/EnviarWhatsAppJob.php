<?php

namespace App\Jobs;

use App\Models\Configuracion;
use App\Models\Noticia;
use App\Models\WhatsappBaja;
use App\Models\WhatsappLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class EnviarWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;

    /**
     * titulo, cuerpo, image, link, batch_id y, opcionalmente:
     * - plantilla / idioma: plantilla de Meta a usar (por defecto "mensajes" / "en").
     * - boton: sufijo variable del botón URL. Si no viene, se toma de "link"
     *   (noticia de la web: https://{sitio}/noticias/{slug} → "{slug}").
     */
    public $payload;

    // Control de reintentos y backoff
    public $tries = 3;

    public $backoff = 15; // segundos antes de reintentar

    public function __construct(string $phone, array $payload = [])
    {
        $this->phone = $phone;
        $this->payload = $payload;
    }

    public function handle()
    {
        $batchId = $this->payload['batch_id'] ?? null;

        // Envío de una noticia programada: si ya no está publicada, se cancela.
        if (! empty($this->payload['noticia_id'])) {
            $noticia = Noticia::find($this->payload['noticia_id']);
            if (! $noticia || ! $noticia->esVisible()) {
                $this->guardarLog(['status' => 'cancelado', 'error' => 'La noticia ya no está publicada.']);
                // Permite volver a marcar el envío desde el formulario.
                $noticia?->forceFill(['whatsapp_enviado_en' => null])->saveQuietly();

                return;
            }
        }

        // Pudo darse de baja después de encolar el envío.
        [$enviables] = WhatsappBaja::filtrar([$this->phone]);
        if (empty($enviables)) {
            $this->guardarLog(['status' => 'baja', 'error' => 'El número pidió no recibir más mensajes.']);

            return;
        }

        $phoneId = Configuracion::valor('idnumero');
        $token = Configuracion::valor('tokenw');

        if (! $token || ! $phoneId) {
            throw new \Exception("Faltan parámetros 'tokenw' o 'idnumero' en la tabla configuraciones");
        }

        // WhatsApp rechaza saltos de línea, tabs y más de 4 espacios seguidos en parámetros de plantilla
        $titulo = trim(preg_replace('/\s+/', ' ', $this->payload['titulo'] ?? ''));
        $cuerpo = trim(preg_replace('/\s+/', ' ', $this->payload['cuerpo'] ?? ''));

        $imageLink = $this->payload['image'] ?? null;

        $boton = $this->payload['boton'] ?? $this->sufijoDesdeLink($this->payload['link'] ?? '');
        if ($boton === null) {
            $this->guardarLog(['status' => 'failed', 'error' => 'El link del botón no tiene el formato que espera la plantilla.']);

            return;
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->phone,
            'type' => 'template',
            'template' => [
                'name' => $this->payload['plantilla'] ?? 'mensajes',
                'language' => ['code' => $this->payload['idioma'] ?? 'en'],
                'components' => [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'image',
                                'image' => [
                                    'link' => $imageLink,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'parameter_name' => 'cuerpo',
                                'text' => $cuerpo,
                            ],
                            [
                                'type' => 'text',
                                'parameter_name' => 'titulo',
                                'text' => $titulo,
                            ],
                        ],
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => '0',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $boton,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Llamada a la API
        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/v25.0/{$phoneId}/messages", $data);

        $json = $response->json();
        $respuesta = json_encode($json);

        if ($response->successful() && isset($json['messages'])) {
            $this->guardarLog([
                'status' => 'sent',
                'response' => $respuesta,
                'wamid' => $json['messages'][0]['id'] ?? null,
                'error' => null,
            ]);

            return;
        }

        $errorMessage = $json['error']['message'] ?? ($response->body() ?: 'Error desconocido');

        // Si el número no tiene WhatsApp
        if (stripos($errorMessage, 'Recipient phone number not found') !== false) {
            $this->guardarLog(['status' => 'no_whatsapp', 'response' => $respuesta, 'error' => $errorMessage]);

            return;
        }

        // Si es rate limit o error servidor -> lanzar excepción para reintentar
        if ($response->serverError() || $response->status() == 429) {
            throw new \Exception("Error temporal: {$errorMessage}");
        }

        // Errores definitivos
        $this->guardarLog(['status' => 'failed', 'response' => $respuesta, 'error' => $errorMessage]);
    }

    /**
     * "https://sitio/noticias/mi-noticia" → "mi-noticia". Null si no tiene ese formato.
     * El botón de la plantilla "mensajes" tiene la URL base https://noticias.tigreteve.online/noticias/{{1}}.
     */
    private function sufijoDesdeLink(string $link): ?string
    {
        $ruta = (string) parse_url($link, PHP_URL_PATH);

        return preg_match('#/noticias/([^/]+)/?$#', $ruta, $m) ? $m[1] : null;
    }

    private function guardarLog(array $datos): void
    {
        $batchId = $this->payload['batch_id'] ?? null;

        if (isset($datos['error'])) {
            $datos['error'] = mb_substr($datos['error'], 0, 190);
        }

        WhatsappLog::updateOrCreate(
            ['phone' => $this->phone, 'batch_id' => $batchId],
            $datos + ['phone' => $this->phone, 'batch_id' => $batchId]
        );
    }

    public function failed(Throwable $exception)
    {
        // Si agotó reintentos, marcar failed
        WhatsappLog::updateOrCreate(
            ['phone' => $this->phone, 'batch_id' => $this->payload['batch_id'] ?? null],
            ['status' => 'failed', 'response' => $exception->getMessage(), 'error' => mb_substr($exception->getMessage(), 0, 190)]
        );
    }
}
