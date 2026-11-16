<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Trae el contenido de una noticia externa: resuelve el enlace de Google Noticias
 * al artículo real, extrae texto/resumen/imagen y sube la imagen a S3.
 */
class ArticuloExternoService
{
    private const AGENTE = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36';
    private const MAX_IMAGEN_BYTES = 5 * 1024 * 1024;
    private const MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    /**
     * Convierte un enlace news.google.com/rss/articles/... en la URL del medio.
     * Devuelve null si no se puede resolver.
     */
    public function resolverUrl(string $enlace): ?string
    {
        if (! str_contains((string) parse_url($enlace, PHP_URL_HOST), 'news.google.com')) {
            return $enlace;
        }

        $id = basename((string) parse_url($enlace, PHP_URL_PATH));
        if ($id === '') {
            return null;
        }

        // Formato antiguo: la URL viene codificada en base64 dentro del id.
        $crudo = base64_decode(strtr($id, '-_', '+/') . str_repeat('=', (4 - strlen($id) % 4) % 4), false);
        if ($crudo !== false && ! str_contains($crudo, 'AU_yqL')
            && preg_match('~https?://[\x21-\x7e]+~', $crudo, $m)) {
            return $m[0];
        }

        // Formato nuevo: hay que pedirle a Google la URL con la firma que trae la página del artículo.
        try {
            $pagina = $this->http(6)->get("https://news.google.com/rss/articles/{$id}");
            if (! preg_match('~data-n-a-sg="([^"]+)"~', $pagina->body(), $firma)
                || ! preg_match('~data-n-a-ts="([^"]+)"~', $pagina->body(), $marca)) {
                return null;
            }

            $peticion = json_encode([[[
                'Fbv4je',
                '["garturlreq",[["X","X",["X","X"],null,null,1,1,"US:en",null,1,null,null,null,null,null,0,1],"X","X",1,[1,1,1],1,1,null,0,0,null,0],"'
                    . $id . '",' . $marca[1] . ',"' . $firma[1] . '"]',
                null,
                'generic',
            ]]]);

            $respuesta = $this->http(6)
                ->asForm()
                ->post('https://news.google.com/_/DotsSplashUi/data/batchexecute', ['f.req' => $peticion]);

            if (preg_match('~garturlres\\\\",\\\\"(https?://.+?)\\\\"~', $respuesta->body(), $url)) {
                return stripcslashes(json_decode('"' . $url[1] . '"') ?? $url[1]);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo resolver enlace de Google Noticias', ['enlace' => $enlace, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @return array{cuerpo: ?string, resumen: ?string, imagen: ?string}
     */
    public function extraer(string $url): array
    {
        $vacio = ['cuerpo' => null, 'resumen' => null, 'imagen' => null];

        try {
            $respuesta = $this->http(10)->get($url);
            if (! $respuesta->successful()) {
                return $vacio;
            }
            $html = $respuesta->body();
        } catch (\Throwable $e) {
            Log::warning('No se pudo descargar el artículo externo', ['url' => $url, 'error' => $e->getMessage()]);
            return $vacio;
        }

        $dom = new \DOMDocument();
        $previo = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_use_internal_errors($previo);
        $xpath = new \DOMXPath($dom);

        $meta = fn (string $q) => trim((string) $xpath->evaluate("string(//meta[$q]/@content)"));
        $jsonLd = $this->jsonLd($xpath);

        $cuerpo = $this->limpiarTexto($this->buscarClave($jsonLd, 'articleBody') ?? '');
        if (mb_strlen($cuerpo) < 300) {
            $cuerpo = $this->parrafos($xpath);
        }

        $imagen = $meta('@property="og:image"') ?: $meta('@name="twitter:image"') ?: $meta('@property="og:image:url"');
        if ($imagen === '') {
            $imagenLd = $this->buscarClave($jsonLd, 'image');
            $imagen = is_array($imagenLd) ? ($imagenLd['url'] ?? $imagenLd[0]['url'] ?? $imagenLd[0] ?? '') : (string) $imagenLd;
        }

        $resumen = $meta('@property="og:description"') ?: $meta('@name="description"');

        return [
            'cuerpo' => mb_strlen($cuerpo) >= 200 ? $cuerpo : null,
            'resumen' => $resumen !== '' ? Str::limit(html_entity_decode($resumen, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 497) : null,
            'imagen' => is_string($imagen) && $imagen !== '' ? $this->absoluta($imagen, $url) : null,
        ];
    }

    /** Descarga la imagen y la guarda en S3 bajo imagenes/. Devuelve la ruta o null. */
    public function subirImagen(string $url): ?string
    {
        try {
            $respuesta = $this->http(8)->get($url);
            $mime = strtolower(trim(explode(';', (string) $respuesta->header('Content-Type'))[0]));
            $bytes = $respuesta->body();

            if (! $respuesta->successful() || ! isset(self::MIMES[$mime]) || strlen($bytes) > self::MAX_IMAGEN_BYTES || $bytes === '') {
                Log::info('Imagen externa descartada', ['url' => $url, 'status' => $respuesta->status(), 'mime' => $mime, 'bytes' => strlen($bytes)]);
                return null;
            }

            $ruta = 'imagenes/' . Str::random(40) . '.' . self::MIMES[$mime];

            return Storage::disk('s3')->put($ruta, $bytes, ['ContentType' => $mime]) ? $ruta : null;
        } catch (\Throwable $e) {
            Log::error('No se pudo subir la imagen externa a S3', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function http(int $timeout)
    {
        return Http::timeout($timeout)
            ->withHeaders(['User-Agent' => self::AGENTE, 'Accept-Language' => 'es-CO,es;q=0.9'])
            ->withOptions(['allow_redirects' => ['max' => 5]]);
    }

    /** @return array<int, mixed> */
    private function jsonLd(\DOMXPath $xpath): array
    {
        $bloques = [];
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $datos = json_decode(trim($script->textContent), true);
            if (is_array($datos)) {
                $bloques[] = $datos;
            }
        }

        return $bloques;
    }

    private function buscarClave(mixed $datos, string $clave): mixed
    {
        if (! is_array($datos)) {
            return null;
        }
        if (array_key_exists($clave, $datos) && filled($datos[$clave])) {
            return $datos[$clave];
        }
        foreach ($datos as $valor) {
            $encontrado = $this->buscarClave($valor, $clave);
            if ($encontrado !== null) {
                return $encontrado;
            }
        }

        return null;
    }

    /** Toma el bloque de la página con más texto en párrafos (normalmente el cuerpo de la nota). */
    private function parrafos(\DOMXPath $xpath): string
    {
        $excluir = 'ancestor::nav or ancestor::footer or ancestor::aside or ancestor::header or ancestor::figure or ancestor::form';
        $grupos = [];

        foreach ($xpath->query("//p[not($excluir)]") as $p) {
            $texto = $this->limpiarTexto($p->textContent);
            if (mb_strlen($texto) < 40) {
                continue;
            }
            $padre = spl_object_id($p->parentNode);
            $grupos[$padre]['textos'][] = $texto;
            $grupos[$padre]['largo'] = ($grupos[$padre]['largo'] ?? 0) + mb_strlen($texto);
        }

        if (! $grupos) {
            return '';
        }

        usort($grupos, fn ($a, $b) => $b['largo'] <=> $a['largo']);

        return implode("\n\n", $grupos[0]['textos']);
    }

    private function limpiarTexto(mixed $texto): string
    {
        if (! is_string($texto)) {
            return '';
        }
        $texto = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n\n", $texto)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/[ \t\x{00A0}]+/u', ' ', $texto);
        $texto = preg_replace('/\s*\n\s*(\n\s*)+/', "\n\n", $texto);

        return trim($texto);
    }

    private function absoluta(string $url, string $base): string
    {
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }
        $origen = parse_url($base, PHP_URL_SCHEME) . '://' . parse_url($base, PHP_URL_HOST);

        return $origen . '/' . ltrim($url, '/');
    }
}
