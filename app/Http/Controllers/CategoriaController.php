<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('noticias')
            ->orderBy('nombre')
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['nombre']);

        Categoria::create($data);

        return redirect()->route('categorias.index')->with('ok', 'Categoría creada.');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $this->validated($request, $categoria);

        if ($data['nombre'] !== $categoria->nombre) {
            $data['slug'] = $this->uniqueSlug($data['nombre'], $categoria->id);
        }

        $categoria->update($data);

        return redirect()->route('categorias.index')->with('ok', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->noticias()->exists()) {
            return redirect()->route('categorias.index')
                ->with('error', 'No puedes eliminar una categoría que tiene noticias asignadas.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('ok', 'Categoría eliminada.');
    }

    private function validated(Request $request, ?Categoria $categoria = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categorias', 'nombre')->ignore($categoria?->id),
            ],
        ], [], [
            'nombre' => 'nombre',
        ]);
    }

    private function uniqueSlug(string $nombre, ?int $ignoreId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $i = 1;

        while (Categoria::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
