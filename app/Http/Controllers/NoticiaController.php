<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticiaRequest;
use App\Http\Requests\UpdateNoticiaRequest;
use App\Models\Noticia;
use App\Models\Categoria;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class NoticiaController extends Controller
{
    use AuthorizesRequests;
    public function p(Request $request, $slug = null)
    {
        $term = $request->input('q');
        $categorias = Categoria::all();

        // Si no paso slug, no busco categoría
        if ($slug) {
            $categoria = Categoria::where('slug', $slug)->first();
            
            $noticias = Noticia::with(['categoria'])
                ->where('category_id', $categoria->id)
                ->NoD()
                ->publicadas()
                ->buscar($term)
                ->latest()
                ->get();

            $noticiasDestacadas = Noticia::with(['categoria'])
                ->where('category_id', $categoria->id)
                ->publicadas()
                ->destacadas()
                ->buscar($term)
                ->latest()
                ->get();
        } else {
            // si no paso slug, traigo todo
            $noticias = Noticia::with(['categoria'])
                ->NoD()
                ->publicadas()
                ->buscar($term)
                ->latest()
                ->get();
            $noticiasDestacadas = Noticia::with(['categoria'])
                ->publicadas()
                ->destacadas()
                ->buscar($term)
                ->latest()
                ->get();

            $categoria = null; // para la vista
        }

        return view('Principal', compact('noticias', 'categorias', 'noticiasDestacadas', 'categoria'));
    }

    public function index(Request $request)
    {
        $filtro = $request->get('estado'); // puede ser 'publicadas', 'borradores', 'programadas'

        $query = Noticia::query();

        if ($filtro === 'publicadas') {
            $query->where('estado', 'publicada');
        } elseif ($filtro === 'borradores') {
            $query->where('estado', 'borrador');
        } elseif ($filtro === 'programadas') {
            $query->where('estado', 'programada');
        }

        $noticias = $query->latest()->paginate(6);

        return view('panel.dashboard', compact('noticias', 'filtro'));
    }

    public function create()
    {
        $this->authorize('crear noticias');
        return view('noticias.create', [
            'categorias' => Categoria::all(),
            'tags' => Tag::all()
        ]);
    }
    public function store(StoreNoticiaRequest $request)
    {
        $folder = "imagenes";
        $data = $request->validated();

        $data['is_featured'] = $request->has('is_featured'); // ✅ Captura si el checkbox está marcado
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['titulo']) . '-' . Str::random(6);

        if ($request->hasFile('portada')) {
            $file = $request->file('portada');
            $route = Storage::disk('s3')->put('imagenes', $file);
            $data['portada_path'] = $route;
        }

        $noticia = Noticia::create($data);
        $noticia->tags()->sync($request->input('tags', []));

        return redirect()->route('noticias.index')->with('ok', 'Noticia creada');
    }

    public function show($slug)
    {
        $categorias = Categoria::all();
        $noticia = Noticia::where('slug', $slug)->firstOrFail();

        abort_unless(
            $noticia->estado === 'publicada' || $noticia->publicado_en <= now(),
            404
        );

        $noticia->increment('vistas');

        return view('noticias.show', compact('noticia', 'categorias'));
    }
    public function edit(Noticia $noticia)
    {

        $categorias = Categoria::all();
        $tags = Tag::all();
        return view('noticias.edit', [
            'n' => $noticia,
            'categorias' => $categorias,
            'tags' => $tags,
        ]);
    }

    public function update(UpdateNoticiaRequest $request, Noticia $noticia)
    {
        $data = $request->validated();
        $data['is_featured'] = $request->has('is_featured');

        // Actualizar portada si suben otra
        if ($request->hasFile('portada')) {
            if ($noticia->portada_path) {
                Storage::disk('s3')->delete($noticia->portada_path);
            }
            $file = $request->file('portada');
            $route = Storage::disk('s3')->put('imagenes', $file);
            $data['portada_path'] = $route;
        }

        $noticia->update($data);

        // Sincronizar tags
        $noticia->tags()->sync($request->input('tags', []));

        return redirect()->route('noticias.index')->with('success', 'Noticia actualizada correctamente');
    }

    public function destroy(Noticia $noticia)
    {
        if ($noticia->portada_path) {
            Storage::disk('s3')->delete($noticia->portada_path);
        }
        $this->authorize('eliminar noticias');
        $noticia->delete();
        return redirect()->route('noticias.index')->with('ok', 'Noticia eliminada con éxito');
    }

    public function buscar(Request $request)
    {
        $term = $request->input('q'); // el texto del buscador
        $categorias = Categoria::all();

        $noticias3 = Noticia::with(['categoria'])
            ->publicadas()
            ->buscar($term)   // usamos el scope que ya tienes en tu modelo
            ->latest()
            ->paginate(10);

        return view('principal', compact('noticias3', 'term', 'categorias'));
    }
}
