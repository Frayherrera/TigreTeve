<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticiaRequest;
use App\Http\Requests\UpdateNoticiaRequest;
use App\Models\Noticia;
use App\Models\Categoria;
use App\Models\Tag;
use App\Services\AvisosNoticia;
use App\Services\RegistroVistas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class NoticiaController extends Controller
{
    use AuthorizesRequests;
    public function p(Request $request, $slug = null)
    {
        $term = $request->input('q');
        $categorias = Categoria::all();

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

            $categoria = null;
        }

        // 🔥 Aquí agregas las 3 más vistas
        $topVistas = Noticia::publicadas()
            ->orderBy('vistas', 'DESC')
            ->take(3)
            ->get();

        $weather = $this->getWeather();

        // Franja "Última hora": urgentes publicadas en las últimas 24 horas.
        $hace24h = now()->subDay();
        $ultimaHora = Noticia::publicadas()
            ->where('urgente', true)
            ->where(function ($q) use ($hace24h) {
                $q->where('publicado_en', '>=', $hace24h)
                    ->orWhere(fn ($s) => $s->whereNull('publicado_en')->where('created_at', '>=', $hace24h));
            })
            ->latest()
            ->take(3)
            ->get(['id', 'titulo', 'slug']);

        return view('principal', compact(
            'noticias',
            'categorias',
            'noticiasDestacadas',
            'categoria',
            'topVistas',
            'weather',
            'ultimaHora'
        ));
    }


    public function index(Request $request)
    {
        $filtros = ['publicadas' => 'publicada', 'programadas' => 'programada', 'borradores' => 'borrador'];
        $filtro = array_key_exists($request->get('estado'), $filtros) ? $request->get('estado') : null;
        $buscar = trim((string) $request->get('q', ''));

        $query = Noticia::with(['user', 'categoria'])->withCount('comentarios');

        if ($filtro) {
            $query->where('estado', $filtros[$filtro]);
        }
        if ($buscar !== '') {
            $query->where('titulo', 'LIKE', "%{$buscar}%");
        }

        $noticias = $query->latest()->paginate(12)->withQueryString();

        // Conteos para las pestañas (respetan la búsqueda).
        $conteos = Noticia::query()
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'LIKE', "%{$buscar}%"))
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('panel.dashboard', compact('noticias', 'filtro', 'buscar', 'conteos'));
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
        $data['urgente'] = $request->boolean('urgente');
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['titulo']) . '-' . Str::random(6);

        if ($request->hasFile('portada')) {
            $file = $request->file('portada');
            \Log::info('Uploading to S3', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize(), 'mime' => $file->getMimeType()]);
            try {
                $route = Storage::disk('s3')->put('imagenes', $file);
                \Log::info('S3 put result', ['route' => $route]);
                $data['portada_path'] = $route;
            } catch (\Throwable $e) {
                \Log::error('S3 upload failed', ['error' => $e->getMessage()]);
                return back()->withInput()->with('error', 'Error al subir imagen a S3: '.$e->getMessage());
            }
        }

        $noticia = Noticia::create($data);
        $noticia->tags()->sync($request->input('tags', []));

        $avisos = $this->dispararAvisos($request, $noticia);

        return redirect()->route('noticias.index')->with('ok', trim('Noticia creada. '.$avisos));
    }

    public function show(Request $request, RegistroVistas $vistas, $slug)
    {
        $categorias = Categoria::all();
        // Solo noticias visibles al público: borradores y programadas futuras dan 404.
        $noticia = Noticia::publicadas()
            ->with(['user', 'categoria', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $vistas->registrar($noticia, $request);

        $comentarios = $noticia->comentarios()
            ->where('aprobado', true)
            ->oldest()
            ->get();

        $relacionadas = $this->relacionadas($noticia);

        return view('noticias.show', compact('noticia', 'categorias', 'comentarios', 'relacionadas'));
    }

    /**
     * "Te puede interesar": primero de la misma categoría, luego por etiquetas
     * compartidas y, si faltan, las más recientes.
     */
    private function relacionadas(Noticia $noticia, int $limite = 4)
    {
        $base = fn () => Noticia::publicadas()
            ->with('categoria')
            ->whereKeyNot($noticia->id)
            ->latest();

        $resultado = collect();

        if ($noticia->category_id) {
            $resultado = $base()->where('category_id', $noticia->category_id)->take($limite)->get();
        }

        $tagIds = $noticia->tags->pluck('id');
        if ($resultado->count() < $limite && $tagIds->isNotEmpty()) {
            $resultado = $resultado->merge(
                $base()->whereNotIn('id', $resultado->pluck('id'))
                    ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
                    ->take($limite - $resultado->count())
                    ->get()
            );
        }

        if ($resultado->count() < $limite) {
            $resultado = $resultado->merge(
                $base()->whereNotIn('id', $resultado->pluck('id'))
                    ->take($limite - $resultado->count())
                    ->get()
            );
        }

        return $resultado;
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
        $data['urgente'] = $request->boolean('urgente');

        // Actualizar portada si suben otra
        if ($request->hasFile('portada')) {
            if ($noticia->portada_path) {
                try {
                    Storage::disk('s3')->delete($noticia->portada_path);
                } catch (\Throwable $e) {
                    \Log::warning('No se pudo borrar imagen anterior de S3', ['path' => $noticia->portada_path, 'error' => $e->getMessage()]);
                }
            }
            $file = $request->file('portada');
            \Log::info('Uploading to S3 (update)', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);
            try {
                $route = Storage::disk('s3')->put('imagenes', $file);
                \Log::info('S3 put result (update)', ['route' => $route]);
                $data['portada_path'] = $route;
            } catch (\Throwable $e) {
                \Log::error('S3 upload failed (update)', ['error' => $e->getMessage()]);
                return back()->withInput()->with('error', 'Error al subir imagen a S3: '.$e->getMessage());
            }
        }

        $noticia->update($data);

        // Sincronizar tags
        $noticia->tags()->sync($request->input('tags', []));

        $avisos = $this->dispararAvisos($request, $noticia);

        return redirect()->route('noticias.index')->with('success', trim('Noticia actualizada correctamente. '.$avisos));
    }

    /**
     * Avisos marcados en el formulario: push (Administrator y Editor) y WhatsApp (Administrator).
     * Para programadas se encolan hasta su fecha. Devuelve el texto para el aviso de éxito.
     */
    private function dispararAvisos(Request $request, Noticia $noticia): string
    {
        $avisos = app(AvisosNoticia::class);
        $mensajes = [];

        if ($request->boolean('avisar_push') && $request->user()->hasAnyRole(['Administrator', 'Editor'])) {
            $mensajes[] = $avisos->push($noticia);
        }

        if ($request->boolean('enviar_whatsapp') && $request->user()->hasRole('Administrator')) {
            $mensajes[] = $avisos->whatsapp($noticia);
        }

        return implode(' ', array_filter($mensajes));
    }

    public function destroy(Noticia $noticia)
    {
        $this->authorize('eliminar noticias');
        if ($noticia->portada_path) {
            Storage::disk('s3')->delete($noticia->portada_path);
        }
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

        $weather = $this->getWeather();

        return view('principal', compact('noticias3', 'term', 'categorias', 'weather'));
    }

    private function getWeather(): array
    {
        return Cache::remember('weather_aguachica', 1800, function () {
            try {
                $response = Http::timeout(5)->get('https://wttr.in/Aguachica?format=j1');
                if ($response->successful()) {
                    $data = $response->json();
                    $current = $data['current_condition'][0] ?? [];
                    $forecast = $data['weather'][0] ?? [];

                    return [
                        'temp' => $current['temp_C'] ?? '--',
                        'desc' => $current['weatherDesc'][0]['value'] ?? '--',
                        'humidity' => $current['humidity'] ?? '--',
                        'wind' => $current['windspeedKmph'] ?? '--',
                        'max' => $forecast['maxtempC'] ?? '--',
                        'min' => $forecast['mintempC'] ?? '--',
                        'icon' => $this->weatherIcon($current['weatherCode'] ?? ''),
                    ];
                }
            } catch (\Exception $e) {
                // fallback silencioso
            }

            return [
                'temp' => '--',
                'desc' => '--',
                'humidity' => '--',
                'wind' => '--',
                'max' => '--',
                'min' => '--',
                'icon' => 'fa-sun',
            ];
        });
    }

    private function weatherIcon(string $code): string
    {
        return match (true) {
            $code >= 113 && $code <= 116 => 'fa-sun',           // Clear / Sunny
            $code >= 119 && $code <= 122 => 'fa-cloud',         // Partly cloudy
            $code >= 143 && $code <= 200 => 'fa-smog',          // Fog / Mist
            $code >= 176 && $code <= 200 => 'fa-cloud-rain',    // Rain
            $code >= 227 && $code <= 236 => 'fa-snowflake',     // Snow
            $code >= 248 && $code <= 260 => 'fa-smog',          // Fog
            $code >= 263 && $code <= 389 => 'fa-cloud-showers-heavy', // Heavy rain / Thunder
            $code >= 392 && $code <= 395 => 'fa-bolt',          // Thunder with hail
            default => 'fa-sun',
        };
    }
}
