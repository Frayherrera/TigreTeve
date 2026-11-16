<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Lee los metadatos públicos (Open Graph) de una publicación de Facebook para rellenar
 * el envío masivo de WhatsApp. Facebook puede responder con el muro de inicio de sesión;
 * en ese caso se informa para llenar los campos a mano.
 */
class FacebookPostService
{
    private const AGENTE = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36';
    private const HOSTS = ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com', 'mbasic.facebook.com', 'fb.com', 'www.fb.com', 'fb.watch'];
    private const MAX_TITULO = 150;
    private const MAX_CUERPO = 900;

    public function __construct(private ArticuloExternoService $articulos)
    {
    }

    public static function esEnlaceDeFacebook(string $url): bool
    {
        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), self::HOSTS, true);
    }

    /**
     * @return array{ok: bool, error?: string, titulo?: string, cuerpo?: string, link?: string, image?: ?string, avisos?: array<int, string>}
     */
    public function leer(string $url): array
    {
        try {
            $respuesta = Http::timeout(10)
                ->withHeaders(['User-Agent' => self::AGENTE, 'Accept-Language' => 'es-CO,es;q=0.9'])
                ->withOptions(['allow_redirects' => ['max' => 5, 'track_redirects' => true]])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('No se pudo abrir la publicación de Facebook', ['url' => $url, 'error' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'No se pudo conectar con Facebook. Inténtalo de nuevo en unos segundos.'];
        }

        $final = (string) ($respuesta->effectiveUri() ?? $url);
        $meta = $this->metadatos($respuesta->body());
        $descripcion = $this->limpiar($meta['og:description'] ?? '');

        if (! $respuesta->successful() || str_contains($final, '/login') || $descripcion === '') {
            return [
                'ok' => false,
                'error' => 'Facebook no mostró la publicación sin iniciar sesión (o no es pública). Llena los campos a mano.',
            ];
        }

        [$titulo, $cuerpo] = $this->tituloYCuerpo($this->limpiarTitulo($meta['og:title'] ?? ''), $descripcion, $final);

        $avisos = [];
        $link = $this->enlaceCanonico($meta['og:url'] ?? $meta['canonical'] ?? $final);
        if (! preg_match('~^https://www\.facebook\.com/[^/]+/[^/]+/[^/?#]+$~', $link)) {
            $avisos[] = 'El enlace no tiene la forma facebook.com/página/posts/id que usa el botón de la plantilla. Revísalo antes de enviar.';
        }

        $image = null;
        $imagenFacebook = html_entity_decode($meta['og:image'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($imagenFacebook !== '') {
            // Los enlaces de imagen de Facebook caducan; WhatsApp la descarga en cada envío, así que se copia a S3.
            $ruta = $this->articulos->subirImagen($imagenFacebook);
            if ($ruta) {
                $image = \Storage::disk('s3')->url($ruta);
            } else {
                $image = $imagenFacebook;
                $avisos[] = 'No se pudo copiar la imagen a S3; se usa el enlace de Facebook, que caduca en unos días.';
            }
        } else {
            $avisos[] = 'La publicación no tiene imagen; agrega el link de una a mano (la plantilla la exige).';
        }

        return ['ok' => true, 'titulo' => $titulo, 'cuerpo' => $cuerpo, 'link' => $link, 'image' => $image, 'avisos' => $avisos];
    }

    /** @return array<string, string> */
    private function metadatos(string $html): array
    {
        $dom = new \DOMDocument();
        $previo = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_use_internal_errors($previo);
        $xpath = new \DOMXPath($dom);

        $meta = [];
        foreach ($xpath->query('//meta[@property or @name]') as $m) {
            $clave = strtolower($m->getAttribute('property') ?: $m->getAttribute('name'));
            if (! isset($meta[$clave]) && $m->getAttribute('content') !== '') {
                $meta[$clave] = trim($m->getAttribute('content'));
            }
        }
        $canonica = trim((string) $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        if ($canonica !== '') {
            $meta['canonical'] = $canonica;
        }

        return $meta;
    }

    private function limpiar(string $texto): string
    {
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/[ \t\x{00A0}]+/u', ' ', $texto);

        return trim(preg_replace('/ *\R */u', "\n", $texto));
    }

    /** Quita los agregados de Facebook: "… | Facebook", "… | By Página", "… | Por Página". */
    private function limpiarTitulo(string $titulo): string
    {
        $titulo = $this->limpiar($titulo);
        $titulo = preg_replace('/\s*\|\s*(Facebook|By .+|Por .+)$/iu', '', $titulo);
        $titulo = preg_replace('/\s*\|\s*(Facebook|By .+|Por .+)$/iu', '', $titulo);

        $generico = ['facebook', 'log in or sign up to view', 'inicia sesión o regístrate para verlo', 'iniciar sesión en facebook'];

        return in_array(mb_strtolower($titulo), $generico, true) ? '' : $titulo;
    }

    /**
     * El título de la plantilla sale de og:title si es un titular propio; si solo es el nombre de la página,
     * se toma la primera frase del texto. El cuerpo es el texto sin repetir el título.
     *
     * @return array{0: string, 1: string}
     */
    private function tituloYCuerpo(string $titulo, string $descripcion, string $url): array
    {
        $pagina = Str::slug((string) (explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[0] ?? ''));
        $esNombreDePagina = $titulo !== '' && (($pagina !== '' && Str::slug($titulo) === $pagina) || mb_strlen($titulo) < 4);

        if ($titulo === '' || $esNombreDePagina) {
            $titulo = $this->primeraFrase($descripcion);
        }

        // Si og:title es el comienzo del texto (Facebook lo recorta con "..."), se quita esa parte del cuerpo.
        $prefijo = rtrim($titulo, '.… ');
        $cuerpo = $descripcion;
        if ($prefijo !== '' && str_starts_with($descripcion, $prefijo)) {
            $cuerpo = ltrim(mb_substr($descripcion, mb_strlen($prefijo)), " .…!?\n");
        }
        if ($cuerpo === '') {
            $cuerpo = $descripcion;
        }

        return [
            Str::limit(rtrim($titulo, '.… '), self::MAX_TITULO, '…'),
            Str::limit($cuerpo, self::MAX_CUERPO, '…'),
        ];
    }

    private function primeraFrase(string $texto): string
    {
        $linea = trim(preg_split('/\n/', $texto)[0] ?? '');
        if (preg_match('/^(.{15,140}?[.!?])(\s|$)/u', $linea, $m)) {
            return $m[1];
        }

        return Str::words($linea, 14, '');
    }

    /** Lleva el enlace a https://www.facebook.com/… sin parámetros de seguimiento. */
    private function enlaceCanonico(string $url): string
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ruta = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
        $consulta = (string) parse_url($url, PHP_URL_QUERY);

        // permalink.php / story.php dependen de sus parámetros: se conservan.
        $conservar = preg_match('~/(permalink|story)\.php$~', $ruta) && $consulta !== '';

        return 'https://www.facebook.com' . $ruta . ($conservar ? '?' . $consulta : '');
    }
}
