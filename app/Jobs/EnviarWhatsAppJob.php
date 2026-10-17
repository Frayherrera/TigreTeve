<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Configuracion;
use App\Models\WhatsappLog;
use Throwable;

class EnviarWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $payload; // array con titulo, cuerpo, image, link, template, lang, batch_id

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
        $phoneId = Configuracion::valor('idnumero');
        $token = Configuracion::valor('tokenw');

        if (!$token || !$phoneId) {
            throw new \Exception("Faltan parámetros 'tokenw' o 'idnumero' en la tabla configuraciones");
        }

        $aLink = explode("/", $this->payload['link'] ?? '');

        // Armar el body conforme a tu plantilla "noticia"
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->phone,
            'type' => 'template',
            'template' => [
                'name' => 'noticiamodificable',
                'language' => ['code' => 'es_CO'],
                'components' => [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'parameter_name' => 'titulo',
                                'text' => $this->payload['titulo'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'parameter_name' => 'cuerpo',
                                'text' => $this->payload['cuerpo'],
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
                                'text' => $aLink[4]."/".$aLink[5],
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

        // Guardar o actualizar log
        $logData = [
            'phone' => $this->phone,
            'response' => json_encode($json)."-----".(json_encode($response)),
            'batch_id' => $this->payload['batch_id'] ?? null,
        ];

        if ($response->successful() && isset($json['messages'])) {
            $logData['status'] = 'sent';
            WhatsappLog::updateOrCreate(['phone' => $this->phone, 'batch_id' => $logData['batch_id']], $logData);
            return;
        }

        $errorMessage = $json['error']['message'] ?? ($response->body() ?: 'Error desconocido');

        // Si el número no tiene WhatsApp
        if (stripos($errorMessage, 'Recipient phone number not found') !== false) {
            $logData['status'] = 'no_whatsapp';
            WhatsappLog::updateOrCreate(['phone' => $this->phone, 'batch_id' => $logData['batch_id']], $logData);
            return;
        }

        // Si es rate limit o error servidor -> lanzar excepción para reintentar
        if ($response->serverError() || $response->status() == 429) {
            throw new \Exception("Error temporal: {$errorMessage}");
        }

        // Errores definitivos
        $logData['status'] = 'failed';
        WhatsappLog::updateOrCreate(['phone' => $this->phone, 'batch_id' => $logData['batch_id']], $logData);
    }

    public function failed(Throwable $exception)
    {
        // Si agotó reintentos, marcar failed
        WhatsappLog::updateOrCreate(
            ['phone' => $this->phone, 'batch_id' => $this->payload['batch_id'] ?? null],
            ['status' => 'failed', 'response' => $exception->getMessage()]
        );
    }
}
