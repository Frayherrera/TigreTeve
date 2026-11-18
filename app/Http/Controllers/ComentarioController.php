<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Noticia;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    /**
     * Guarda un comentario enviado desde la página pública de una noticia.
     */
    public function store(Request $request, string $slug)
    {
        $noticia = Noticia::publicadas()->where('slug', $slug)->firstOrFail();

        // Honeypot: los bots suelen rellenar todos los campos. Fingimos éxito sin guardar.
        if (filled($request->input('sitio_web'))) {
            return redirect()->to(route('noticias.show2', $noticia->slug).'#comentarios')
                ->with('comentario_ok', 'Tu comentario fue publicado.');
        }

        $user = $request->user();

        $reglas = [
            'contenido' => ['required', 'string', 'min:2', 'max:1000'],
        ];
        if (! $user) {
            $reglas['autor_nombre'] = ['required', 'string', 'max:80'];
            $reglas['autor_email'] = ['nullable', 'email', 'max:120'];
        }

        $datos = $request->validate($reglas, [
            'contenido.required' => 'Escribe tu comentario.',
            'contenido.min' => 'El comentario es demasiado corto.',
            'contenido.max' => 'El comentario no puede pasar de 1000 caracteres.',
            'autor_nombre.required' => 'Escribe tu nombre.',
            'autor_nombre.max' => 'El nombre no puede pasar de 80 caracteres.',
            'autor_email.email' => 'El correo no es válido.',
        ]);

        $noticia->comentarios()->create([
            'user_id' => $user?->id,
            'autor_nombre' => $user ? $user->name : trim($datos['autor_nombre']),
            'autor_email' => $user ? $user->email : ($datos['autor_email'] ?? null),
            'contenido' => trim($datos['contenido']),
            'aprobado' => true,
        ]);

        return redirect()->to(route('noticias.show2', $noticia->slug).'#comentarios')
            ->with('comentario_ok', 'Tu comentario fue publicado.');
    }

    /**
     * Panel: noticias que tienen comentarios, con conteos.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));

        $noticias = Noticia::query()
            ->whereHas('comentarios')
            ->withCount([
                'comentarios',
                'comentarios as ocultos_count' => fn ($q) => $q->where('aprobado', false),
            ])
            ->withMax('comentarios', 'created_at')
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'LIKE', "%{$buscar}%"))
            ->orderByDesc('comentarios_max_created_at')
            ->paginate(15)
            ->withQueryString();

        $totalComentarios = Comentario::count();

        return view('comentarios.index', compact('noticias', 'buscar', 'totalComentarios'));
    }

    /**
     * Panel: todos los comentarios de una noticia (visibles y ocultos).
     */
    public function porNoticia(Noticia $noticia)
    {
        $comentarios = $noticia->comentarios()
            ->with('usuario')
            ->latest()
            ->paginate(25);

        $ocultos = $noticia->comentarios()->where('aprobado', false)->count();

        return view('comentarios.noticia', compact('noticia', 'comentarios', 'ocultos'));
    }

    /**
     * Panel: oculta o vuelve a mostrar un comentario en el sitio.
     */
    public function alternar(Comentario $comentario)
    {
        $comentario->update(['aprobado' => ! $comentario->aprobado]);

        return back()->with('ok', $comentario->aprobado
            ? 'El comentario vuelve a ser visible en el sitio.'
            : 'El comentario quedó oculto en el sitio.');
    }

    /**
     * Panel: elimina un comentario definitivamente.
     */
    public function destroy(Comentario $comentario)
    {
        $comentario->delete();

        return back()->with('ok', 'Comentario eliminado.');
    }
}
