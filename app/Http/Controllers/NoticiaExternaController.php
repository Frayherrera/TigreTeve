<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Services\ArticuloExternoService;
use App\Services\GoogleNewsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NoticiaExternaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, GoogleNewsService $google)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $tema = $request->input('tema');
        if (! array_key_exists((string) $tema, GoogleNewsService::TEMAS)) {
            $tema = null;
        }

        $resultado = $google->obtener($busqueda, $tema);

        // Marca las que ya se importaron (se guarda el enlace de Google en `enlace_google`).
        $importadas = Noticia::whereIn('enlace_google', array_column($resultado['items'], 'enlace'))
            ->pluck('id', 'enlace_google');

        return view('externas.index', [
            'items' => $resultado['items'],
            'error' => $resultado['error'],
            'actualizado' => $resultado['actualizado'],
            'importadas' => $importadas,
            'busqueda' => $busqueda,
            'tema' => $tema,
            'temas' => GoogleNewsService::TEMAS,
        ]);
    }

    public function importar(Request $request, ArticuloExternoService $articulos)
    {
        $this->authorize('crear noticias');

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'enlace' => ['required', 'url', 'max:2048'],
        ]);

        $existente = Noticia::where('enlace_google', $data['enlace'])->first();
        if ($existente) {
            return redirect()->route('noticias.edit', $existente)
                ->with('ok', 'Esta noticia ya estaba importada.');
        }

        // Resolver, descargar y subir la imagen puede tardar varios segundos.
        set_time_limit(60);

        $url = $articulos->resolverUrl($data['enlace']);
        $contenido = $url ? $articulos->extraer($url) : ['cuerpo' => null, 'resumen' => null, 'imagen' => null];
        $portada = $contenido['imagen'] ? $articulos->subirImagen($contenido['imagen']) : null;

        $noticia = Noticia::create([
            'user_id' => $request->user()->id,
            'titulo' => $data['titulo'],
            'slug' => Str::slug($data['titulo']) . '-' . Str::random(6),
            'resumen' => $contenido['resumen'],
            'cuerpo' => $contenido['cuerpo'] ?? '',
            'portada_path' => $portada,
            'estado' => 'borrador',
            'fuente' => $url ?? $data['enlace'],
            'enlace_google' => $data['enlace'],
            'is_featured' => false,
        ]);

        return redirect()->route('noticias.edit', $noticia)
            ->with('ok', $this->mensaje($contenido['cuerpo'] !== null, $portada !== null, $contenido['imagen'] !== null));
    }

    private function mensaje(bool $hayCuerpo, bool $haySubida, bool $habiaImagen): string
    {
        $partes = [$hayCuerpo ? 'se trajo el texto' : 'no se pudo extraer el texto, redáctalo'];

        if ($haySubida) {
            $partes[] = 'la foto se subió a S3';
        } elseif ($habiaImagen) {
            $partes[] = 'la foto no se pudo subir';
        } else {
            $partes[] = 'la nota no tenía foto';
        }

        return 'Borrador creado: ' . implode(' y ', $partes) . '. Revísalo antes de publicar.';
    }
}
