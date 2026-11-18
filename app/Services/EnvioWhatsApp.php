<?php

namespace App\Services;

use App\Jobs\EnviarWhatsAppJob;
use App\Models\WhatsappBaja;
use App\Models\WhatsappLog;
use Illuminate\Support\Str;

class EnvioWhatsApp
{
    /**
     * Crea un lote: omite los números dados de baja, deja un log "pending" por número
     * y encola un job por cada uno. Con $cuando se programa para esa fecha.
     *
     * @return array{batch_id: string, encolados: int, omitidos: int}
     */
    public function encolar(array $telefonos, array $payload, ?\DateTimeInterface $cuando = null): array
    {
        [$enviables, $omitidos] = WhatsappBaja::filtrar($telefonos);

        $batchId = Str::random(10);
        $payload['batch_id'] = $batchId;

        foreach ($enviables as $telefono) {
            WhatsappLog::create([
                'phone' => $telefono,
                'status' => 'pending',
                'response' => null,
                'batch_id' => $batchId,
            ]);

            $job = new EnviarWhatsAppJob($telefono, $payload);
            if ($cuando && $cuando > now()) {
                $job->delay($cuando);
            }
            dispatch($job);
        }

        return ['batch_id' => $batchId, 'encolados' => count($enviables), 'omitidos' => $omitidos];
    }
}
