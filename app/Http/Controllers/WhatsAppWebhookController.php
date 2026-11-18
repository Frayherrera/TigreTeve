<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\WhatsappBaja;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook de WhatsApp Cloud API.
 *
 * Configuración (tabla configuracions):
 * - webhook_token: el "Token de verificación" que se escribe en Meta al registrar el webhook.
 * - app_secret (opcional, recomendado): "Clave secreta de la app" para validar la firma de cada aviso.
 *
 * Suscribirse en Meta al campo "messages" para recibir estados (entregado, leído, fallido)
 * y respuestas de los usuarios (para las bajas con SALIR).
 */
class WhatsAppWebhookController extends Controller
{
    /** Palabras con las que una persona pide no recibir más mensajes. */
    private const PALABRAS_BAJA = ['salir', 'baja', 'stop', 'cancelar', 'no mas', 'no más', 'detener'];

    /** Orden de los estados: nunca se retrocede (un "leído" no vuelve a "entregado"). */
    private const ORDEN = ['pending' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3];

    public function verificar(Request $request)
    {
        $token = Configuracion::valor('webhook_token');

        if ($token
            && $request->query('hub_mode') === 'subscribe'
            && hash_equals($token, (string) $request->query('hub_verify_token'))) {
            return response((string) $request->query('hub_challenge'), 200);
        }

        return response('Token de verificación inválido', 403);
    }

    public function recibir(Request $request)
    {
        if (! $this->firmaValida($request)) {
            return response('Firma inválida', 401);
        }

        foreach ($request->input('entry', []) as $entrada) {
            foreach ($entrada['changes'] ?? [] as $cambio) {
                $valor = $cambio['value'] ?? [];

                foreach ($valor['statuses'] ?? [] as $estado) {
                    $this->actualizarEstado($estado);
                }

                foreach ($valor['messages'] ?? [] as $mensaje) {
                    $this->procesarMensaje($mensaje);
                }
            }
        }

        // Meta reintenta si no recibe 200 rápido.
        return response('OK', 200);
    }

    private function firmaValida(Request $request): bool
    {
        $secreto = Configuracion::valor('app_secret');

        if (! $secreto) {
            Log::warning('Webhook de WhatsApp sin app_secret configurado: no se valida la firma.');

            return true;
        }

        $firma = (string) $request->header('X-Hub-Signature-256');
        $esperada = 'sha256='.hash_hmac('sha256', $request->getContent(), $secreto);

        return hash_equals($esperada, $firma);
    }

    private function actualizarEstado(array $estado): void
    {
        $wamid = $estado['id'] ?? null;
        $nuevo = $estado['status'] ?? null;
        if (! $wamid || ! $nuevo) {
            return;
        }

        $log = WhatsappLog::where('wamid', $wamid)->first();
        if (! $log) {
            return;
        }

        if ($nuevo === 'failed') {
            $error = $estado['errors'][0]['error_data']['details']
                ?? $estado['errors'][0]['title']
                ?? 'Meta no pudo entregar el mensaje.';
            $log->update(['status' => 'failed', 'error' => mb_substr($error, 0, 190)]);

            return;
        }

        $actual = self::ORDEN[$log->status] ?? 0;
        if (isset(self::ORDEN[$nuevo]) && self::ORDEN[$nuevo] > $actual) {
            $log->update(['status' => $nuevo]);
        }
    }

    private function procesarMensaje(array $mensaje): void
    {
        $telefono = $mensaje['from'] ?? null;
        $texto = $mensaje['text']['body']
            ?? $mensaje['button']['text']
            ?? $mensaje['interactive']['button_reply']['title']
            ?? '';

        if (! $telefono || ! $this->esPedidoDeBaja($texto)) {
            return;
        }

        WhatsappBaja::firstOrCreate(
            ['telefono' => preg_replace('/\D/', '', $telefono)],
            ['motivo' => mb_substr(trim($texto), 0, 190)]
        );
    }

    private function esPedidoDeBaja(string $texto): bool
    {
        $limpio = mb_strtolower(trim(preg_replace('/[^\pL\s]/u', '', $texto)));

        return in_array($limpio, self::PALABRAS_BAJA, true);
    }
}
