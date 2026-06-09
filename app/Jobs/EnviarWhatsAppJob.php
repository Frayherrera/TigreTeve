<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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
        $phoneId =  env('WHATSAPP_PHONE_ID');
        $token =  env('META_TOKEN');

        // Armar el body conforme a tu plantilla "noticia"
        $data = [
            "messaging_product" => "whatsapp",
            "to" => $this->phone,
            "type" => "template",
            "template" => [
                "name" => 'noticiamodificable',
                "language" => ["code" => 'en'],
                "components" => [

                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $this->payload['titulo'] ],
                            ["type" => "text", "text" => $this->payload['cuerpo'] ]
                        ]
                    ],
                    // Botón URL dinámico (solo si link existe)
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            ["type" => "text", "text" => $this->payload['link']]
                        ]
                    ]
                ]
            ]
        ];

        // Llamada a la API
        $response = Http::withToken("EAARlU1FaprYBPZBUMJIu32FsG9sOxcBv06ex8s8ZAw48lM06CLQx3jarZC2qmJ6mjGOUBXQAPPi51qelzmiDZAbvyQ4nhTvPYttb3ler1MfhrX3SZAbF6aoktSkJqNyg6oVvcLx7VTJmoMBrB2CKtDPMCes4Mz0vZBG3jgIwJ27Qsr6XTxFt5fTw8lG65kGCwiu1xWpteLjoytjMv5")
            ->post("https://graph.facebook.com/v22.0/904202119433611/messages", $data);

        $json = $response->json();

        // Guardar o actualizar log
        $logData = [
            'phone' => $this->phone,
            'response' => json_encode($json),
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
