<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Noticia;
use App\Services\ArticuloExternoService;
use App\Services\GoogleNewsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'tema' => ['nullable', Rule::in(array_keys(GoogleNewsService::TEMAS))],
        ]);

        $existente = Noticia::where('enlace_google', $data['enlace'])->first();
        if ($existente) {
            return redirect()->route('noticias.edit', $existente)
                ->with('ok', 'Esta noticia ya estaba importada.');
        }

        // Resolver, descargar y subir la imagen puede tardar varios segundos.
        set_time_limit(60);

        $url = $articulos->resolverUrl($data['enlace']);
        $contenido = $url ? $articulos->extraer($url) : ['cuerpo' => null, 'resumen' => null, 'imagen' => null, 'seccion' => null];
        $portada = $contenido['imagen'] ? $articulos->subirImagen($contenido['imagen']) : null;

        // Categoría: sección de Google que se estaba viendo > sección del medio > "General". Se crea si no existe.
        $categoria = Categoria::buscarOCrear(GoogleNewsService::TEMAS[$data['tema'] ?? ''] ?? null)
            ?? Categoria::buscarOCrear($contenido['seccion'])
            ?? Categoria::buscarOCrear('General');

        $noticia = Noticia::create([
            'user_id' => $request->user()->id,
            'category_id' => $categoria?->id,
            'titulo' => $data['titulo'],
            'slug' => Str::slug($data['titulo']) . '-' . Str::random(6),
            'resumen' => $contenido['resumen'],
            'cuerpo' => $contenido['cuerpo'] ?? '',
            'portada_path' => $portada,
            // Se publica de una vez; si no se pudo traer el texto queda en borrador para no publicar una nota vacía.
            'estado' => $contenido['cuerpo'] !== null ? 'publicada' : 'borrador',
            'fuente' => $url ?? $data['enlace'],
            'enlace_google' => $data['enlace'],
            'is_featured' => false,
        ]);

        $mensaje = $this->mensaje($contenido['cuerpo'] !== null, $portada !== null, $contenido['imagen'] !== null);
        if ($categoria) {
            $mensaje .= ' Categoría: ' . $categoria->nombre . ($categoria->wasRecentlyCreated ? ' (nueva).' : '.');
        }

        return redirect()->route('noticias.edit', $noticia)->with('ok', $mensaje);
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

        return $hayCuerpo
            ? 'Noticia publicada: ' . implode(' y ', $partes) . '.'
            : 'Quedó como borrador: ' . implode(' y ', $partes) . ' antes de publicar.';
    }
}
