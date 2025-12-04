<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\WhatsappLog;
use App\Jobs\EnviarWhatsAppJob;
use App\Models\Numero;


class WhatsAppController extends Controller
{
    public function index()
    {
        return view("whatsapp.masivo");
    }

    public function mensajeMasivo(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'cuerpo' => 'required|string',

            'link'   => 'nullable|string',

        ]);


        $phones = [];

        // 1. Si quiere los números de la BD
        if ($request->list_from_db) {
            $phones = Numero::pluck('numero')->toArray();
        }

        // 2. Si se subió un archivo
        if ($request->hasFile('file')) {
            $filePhones = $this->processUploadedFile($request->file('file'));
            $phones = array_merge($phones, $filePhones);
        }


        // $phones = $this->cleanAndValidatePhones($phones);

        // dd($phones);

        if (empty($phones)) {
            return back()->with('msg', 'No hay números para enviar.');
        }
        // dd($phones);   
        // Crear batch_id manual para seguimiento (opcional)
        $batchId = Str::random(10);

        // Payload que se enviará al Job
        $payload = [
            'titulo'   => $request->titulo,
            'cuerpo'   => $request->cuerpo,
            'image'    => $request->image,
            'link'     => $request->link,
            'batch_id' => $batchId,
        ];

        // Recorrer números y enviar cada uno a la cola
        foreach ($phones as $phone) {

            // Crear log en estado "pending"
            WhatsappLog::create([
                'phone' => $phone,
                'status' => 'pending',
                'response' => null,
                'batch_id' => $batchId,
            ]);

            // Enviar job a la cola
            dispatch(new EnviarWhatsAppJob($phone, $payload));
        }

        return back()->with('msg', "Envío masivo iniciado correctamente. Lote: <strong>{$batchId}</strong>");
    }
    /**
     * Procesar archivo subido (CSV o Excel)
     */
    private function processUploadedFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $phones = [];

        try {
            if ($extension === 'csv') {
                $phones = $this->processCSV($file);
            } else if (in_array($extension, ['xlsx', 'xls'])) {
                // $phones = $this->processExcel($file);
            }
        } catch (\Exception $e) {
            throw new \Exception("Error procesando archivo: " . $e->getMessage());
        }

        return $phones;
    }

    /**
     * Procesar archivo CSV
     */
    private function processCSV($file)
    {
        $phones = [];
        $path = $file->getRealPath();

        if (($handle = fopen($path, 'r')) !== FALSE) {
            $firstRow = true;

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Saltar la primera fila si es encabezado
                if ($firstRow) {
                    $firstRow = false;
                    continue;
                }

                // Buscar números en todas las columnas
                foreach ($data as $cell) {
                    $foundPhones = $this->extractPhonesFromText($cell);
                    $phones = array_merge($phones, $foundPhones);
                }
            }
            fclose($handle);
        }

        return $phones;
    }

    /**
     * Procesar archivo Excel
     */

    /**
     * Extraer números de teléfono de texto
     */
    private function extractPhonesFromText($text)
    {
        $phones = [];

        // Patrones para números de teléfono
        $patterns = [
            '/\+?[\d\s\-\(\)\.]{7,15}/', // Patrón general
            '/\d{7,15}/' // Solo dígitos
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $text, $matches);
            foreach ($matches[0] as $phone) {
                $cleanPhone = preg_replace('/[^\d]/', '', $phone);

                // Validar longitud básica
                if (strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15) {
                    $phones[] = $cleanPhone;
                }
            }
        }

        return $phones;
    }

    /**
     * Limpiar y validar números de teléfono
     */
    private function cleanAndValidatePhones($phones)
    {
        $cleanPhones = [];

        foreach ($phones as $phone) {
            // Limpiar el número
            $cleanPhone = preg_replace('/[^\d]/', '', $phone);

            // Validaciones
            if (empty($cleanPhone)) continue;
            if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 15) continue;

            // Evitar números que sean solo ceros
            if (preg_match('/^0+$/', $cleanPhone)) continue;

            $cleanPhones[] = $cleanPhone;
        }

        // Eliminar duplicados y reindexar
        return array_values(array_unique($cleanPhones));
    }
}
