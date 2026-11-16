<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lee titulares del RSS público de Google Noticias (edición Colombia).
 * No requiere API key. Los resultados se cachean para no golpear el feed en cada visita.
 */
class GoogleNewsService
{
    private const BASE = 'https://news.google.com/rss';
    private const PARAMS = ['hl' => 'es-419', 'gl' => 'CO', 'ceid' => 'CO:es-419'];
    private const CACHE_SEGUNDOS = 900;

    /** Secciones de Google Noticias: clave del feed => etiqueta en español. */
    public const TEMAS = [
        'NATION' => 'Colombia',
        'WORLD' => 'Mundo',
        'BUSINESS' => 'Economía',
        'SPORTS' => 'Deportes',
        'TECHNOLOGY' => 'Tecnología',
        'ENTERTAINMENT' => 'Entretenimiento',
        'HEALTH' => 'Salud',
        'SCIENCE' => 'Ciencia',
    ];

    /**
     * @return array{items: array<int, array>, error: bool, actualizado: Carbon}
     */
    public function obtener(?string $busqueda = null, ?string $tema = null): array
    {
        $url = $this->construirUrl($busqueda, $tema);

        // Solo se cachean respuestas válidas, así un fallo puntual no queda guardado 15 minutos.
        $cacheado = Cache::get($this->claveCache($url));
        if ($cacheado) {
            return $cacheado + ['error' => false];
        }

        try {
            $respuesta = Http::timeout(8)->get($url);
            if (! $respuesta->successful()) {
                Log::warning('Google Noticias respondió con error', ['url' => $url, 'status' => $respuesta->status()]);
                return ['items' => [], 'error' => true, 'actualizado' => now()];
            }

            $resultado = ['items' => $this->parsear($respuesta->body()), 'actualizado' => now()];
            Cache::put($this->claveCache($url), $resultado, self::CACHE_SEGUNDOS);

            return $resultado + ['error' => false];
        } catch (\Throwable $e) {
            Log::warning('No se pudo leer Google Noticias', ['url' => $url, 'error' => $e->getMessage()]);
            return ['items' => [], 'error' => true, 'actualizado' => now()];
        }
    }

    private function construirUrl(?string $busqueda, ?string $tema): string
    {
        if (filled($busqueda)) {
            return self::BASE . '/search?' . http_build_query(['q' => $busqueda] + self::PARAMS);
        }

        if ($tema && array_key_exists($tema, self::TEMAS)) {
            return self::BASE . '/headlines/section/topic/' . $tema . '?' . http_build_query(self::PARAMS);
        }

        return self::BASE . '?' . http_build_query(self::PARAMS);
    }

    private function claveCache(string $url): string
    {
        return 'google_news_' . md5($url);
    }

    /** @return array<int, array> */
    private function parsear(string $xml): array
    {
        $previo = libxml_use_internal_errors(true);
        $rss = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA);
        libxml_use_internal_errors($previo);

        if ($rss === false || ! isset($rss->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($rss->channel->item as $item) {
            $fuente = trim((string) $item->source);
            $titulo = trim((string) $item->title);

            // Google agrega " - Medio" al final del título; se quita para quedarnos con el titular limpio.
            if ($fuente !== '' && str_ends_with($titulo, ' - ' . $fuente)) {
                $titulo = substr($titulo, 0, -strlen(' - ' . $fuente));
            }

            $enlace = trim((string) $item->link);
            if ($titulo === '' || $enlace === '') {
                continue;
            }

            try {
                $fecha = Carbon::parse((string) $item->pubDate);
            } catch (\Throwable) {
                $fecha = null;
            }

            $sitio = (string) ($item->source['url'] ?? '');

            $items[] = [
                'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'enlace' => $enlace,
                'fuente' => $fuente !== '' ? $fuente : 'Fuente desconocida',
                'dominio' => $sitio ? parse_url($sitio, PHP_URL_HOST) : null,
                'publicado' => $fecha,
            ];
        }

        return $items;
    }
}
