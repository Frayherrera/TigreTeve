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

    public function p()
    {
        $noticias = Noticia::NoD()->get();
        $categorias = Categoria::all();
        $noticiasDestacadas = Noticia::destacadas()->get();
        return view('welcome', compact('noticias','categorias' ,'noticiasDestacadas'));
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

        $noticias = $query->latest()->paginate(10);

        return view('panel.dashboard', compact('noticias', 'filtro'));
    }


    // public function index(Request $request)
    // {
    //     $q = Noticia::with(['autor', 'categoria', 'tags'])->when($request->filled('q'), fn($qq) => $qq->buscar($request->q))->when($request->filled('categoria'), fn($qq) => $qq
    //         ->whereHas('categoria', fn($c) => $c->where('slug', $request->categoria)))->when($request->filled('tag'), fn($qq) => $qq->whereHas(
    //         'tags',
    //         fn($t) => $t->where('slug', $request->tag)
    //     ))->publicadas()->latest('publicado_en')->paginate(12)->withQueryString();
    //     $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre', 'slug']);
    //     $tags = Tag::orderBy('nombre')->get(['id', 'nombre', 'slug']);
    //     return view('noticias.index', compact('q', 'categorias', 'tags'));
    // }
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
        $data = $request->validated();

        $data['is_featured'] = $request->has('is_featured'); // ✅ Captura si el checkbox está marcado
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['titulo']) . '-' . Str::random(6);

        if ($request->hasFile('portada')) {
            $data['portada_path'] = $request->file('portada')->store('portadas', 'public');
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
            $noticia->estado === 'publicada' || optional($noticia->publicado_en) <= now(),
            404
        );

        $noticia->increment('vistas');

        return view('noticias.show', compact('noticia','categorias'));
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
            $data['portada_path'] = $request->file('portada')->store('portadas', 'public');
        }

        $noticia->update($data);

        // Sincronizar tags
        $noticia->tags()->sync($request->input('tags', []));

        return redirect()->route('noticias.index')->with('success', 'Noticia actualizada correctamente');
    }

    public function destroy(Noticia $noticia)
    {
        $this->authorize('eliminar noticias');
        $noticia->delete();
        return redirect()->route('noticias.index')->with('ok', 'Noticia eliminada con éxito');
    }
}
