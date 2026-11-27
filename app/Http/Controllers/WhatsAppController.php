<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Numero;
use App\Models\WhatsappLog;
use App\Services\EnvioWhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    public function index()
    {
        return view('whatsapp.masivo', [
            'dbCount' => Numero::count(),
        ]);
    }

    /** Noticias publicadas de la web para rellenar el mensaje (JSON para el formulario). */
    public function noticias(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));

        $noticias = Noticia::publicadas()
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'LIKE', '%'.$buscar.'%'))
            ->latest('publicado_en')
            ->latest('id')
            ->limit(12)
            ->get();

        return response()->json($noticias->map(fn (Noticia $n) => [
            'id' => $n->id,
            'titulo' => $n->titulo,
            'miniatura' => $n->miniaturaUrl(),
            'fecha' => $n->fechaPublicacion()->locale('es')->diffForHumans(),
            // Campos del mensaje
            'image' => $n->portadaUrl() ?? asset('img/logo.png'),
            'link' => route('noticias.show2', $n->slug),
            'cuerpo' => Str::limit(trim(preg_replace('/\s+/', ' ', $n->resumen ?: html_entity_decode(strip_tags((string) $n->cuerpo)))), 900),
        ])->values());
    }

    public function mensajeMasivo(Request $request, EnvioWhatsApp $envio)
    {
        $request->validate([
            'titulo' => 'required|string',
            'cuerpo' => 'required|string',
            'link' => ['required', 'url', 'regex:#/noticias/[^/?\#]+/?(?:[?\#].*)?$#'],
            'image' => 'required|url',
        ], [
            'link.regex' => 'El link del botón debe ser una noticia de la web (…/noticias/slug).',
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

        $phones = $this->cleanAndValidatePhones($phones);

        //dd($phones);

        if (empty($phones)) {
            return back()->with('msg', 'No hay números para enviar.');
        }

        // Un log "pending" y un job por número; los dados de baja se omiten.
        $lote = $envio->encolar($phones, [
            'titulo' => $request->titulo,
            'cuerpo' => $request->cuerpo,
            'image' => $request->image,
            'link' => $request->link,
        ]);

        if ($lote['encolados'] === 0) {
            return back()->with('msg', 'Todos los números de la lista pidieron no recibir mensajes.');
        }

        $url = e(route('whatsapp.resultados', $lote['batch_id']));
        $omitidos = $lote['omitidos']
            ? " Se omitieron {$lote['omitidos']} números dados de baja."
            : '';

        return back()->with('msg', "Envío masivo iniciado: {$lote['encolados']} mensajes en cola.{$omitidos} "
            ."<a href=\"{$url}\"><strong>Ver resultados del lote {$lote['batch_id']}</strong></a>");
    }

    /** Lista de envíos (lotes) recientes con su resumen. */
    public function lotes()
    {
        $lotes = WhatsappLog::query()
            ->whereNotNull('batch_id')
            ->selectRaw('batch_id, COUNT(*) as total, MIN(created_at) as creado')
            ->selectRaw("SUM(CASE WHEN status IN ('sent','delivered','read') THEN 1 ELSE 0 END) as enviados")
            ->selectRaw("SUM(CASE WHEN status IN ('delivered','read') THEN 1 ELSE 0 END) as entregados")
            ->selectRaw("SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as leidos")
            ->selectRaw("SUM(CASE WHEN status IN ('failed','no_whatsapp','baja','cancelado') THEN 1 ELSE 0 END) as fallidos")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendientes")
            ->groupBy('batch_id')
            ->orderByDesc('creado')
            ->paginate(20);

        return view('whatsapp.lotes', compact('lotes'));
    }

    /** Detalle de un lote: conteo por estado y cada número con su resultado. */
    public function resultados(Request $request, string $batchId)
    {
        $base = WhatsappLog::where('batch_id', $batchId);
        abort_unless((clone $base)->exists(), 404);

        $conteos = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $estados = [
            'pending' => 'Pendientes',
            'sent' => 'Enviados',
            'delivered' => 'Entregados',
            'read' => 'Leídos',
            'failed' => 'Fallidos',
            'no_whatsapp' => 'Sin WhatsApp',
            'baja' => 'Dados de baja',
            'cancelado' => 'Cancelados',
        ];
        $filtro = array_key_exists($request->get('estado'), $estados) ? $request->get('estado') : null;

        $logs = (clone $base)
            ->when($filtro, fn ($q) => $q->where('status', $filtro))
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $creado = (clone $base)->min('created_at');

        return view('whatsapp.resultados', compact('batchId', 'conteos', 'estados', 'filtro', 'logs', 'creado'));
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
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // $phones = $this->processExcel($file);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error procesando archivo: '.$e->getMessage());
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

        if (($handle = fopen($path, 'r')) !== false) {
            $firstRow = true;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
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

        // Un solo patrón: captura secuencias con separadores típicos
        preg_match_all('/[0-9][0-9\s\-\(\)\.]{6,}/', $text, $matches);

        foreach ($matches[0] as $phone) {
            $cleanPhone = preg_replace('/[^\d]/', '', $phone);

            // Validar longitud básica
            if (strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15) {
                $phones[] = $cleanPhone;
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
            if (empty($cleanPhone)) {
                continue;
            }
            if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 15) {
                continue;
            }

            // Evitar números que sean solo ceros
            if (preg_match('/^0+$/', $cleanPhone)) {
                continue;
            }

            $cleanPhones[] = $cleanPhone;
        }

        // Eliminar duplicados y reindexar
        return array_values(array_unique($cleanPhones));
    }
}
