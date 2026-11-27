<?php

namespace App\Http\Controllers;

use App\Models\Numero;
use App\Support\Telefono;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NumeroController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q'));

        $numeros = Numero::query()
            ->when($buscar !== '', fn ($query) => $query->where('numero', 'like', "%{$buscar}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('numeros.index', [
            'numeros' => $numeros,
            'buscar' => $buscar,
        ]);
    }

    public function store(Request $request)
    {
        $numero = $this->validarNumero($request);

        Numero::create(['numero' => $numero]);

        return redirect()->route('numeros.index')->with('ok', 'Número agregado.');
    }

    public function update(Request $request, Numero $numero)
    {
        $nuevo = $this->validarNumero($request, $numero);

        $numero->update(['numero' => $nuevo]);

        return redirect()->route('numeros.index')->with('ok', 'Número actualizado.');
    }

    public function destroy(Numero $numero)
    {
        $numero->delete();

        return redirect()->route('numeros.index')->with('ok', 'Número eliminado.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.mimes' => 'Sube un archivo CSV o TXT.',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if ($handle === false) {
            return back()->with('error', 'No se pudo leer el archivo.');
        }

        $existentes = Numero::pluck('numero')->all();
        $vistos = [];
        $creados = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            foreach ($row as $cell) {
                // Se agrega el 57 a los celulares de 10 dígitos.
                $numero = Telefono::normalizar($cell);

                if ($numero === null) {
                    continue;
                }

                if (in_array($numero, $existentes, true) || in_array($numero, $vistos, true)) {
                    continue;
                }

                Numero::create(['numero' => $numero]);
                $vistos[] = $numero;
                $creados++;
            }
        }

        fclose($handle);

        $mensaje = $creados > 0
            ? "{$creados} número(s) importado(s)."
            : 'No se encontraron números nuevos en el archivo.';

        return redirect()->route('numeros.index')->with('ok', $mensaje);
    }

    /**
     * Celular de 10 dígitos; el indicativo 57 se agrega solo. Devuelve el número listo para guardar.
     */
    private function validarNumero(Request $request, ?Numero $actual = null): string
    {
        $original = $request->input('numero');
        $request->merge(['numero' => Telefono::normalizar($original) ?? '']);

        $unico = Rule::unique('numeros', 'numero');
        if ($actual) {
            $unico->ignore($actual->id);
        }

        try {
            $request->validate([
                'numero' => ['required', $unico],
            ], [
                'numero.required' => 'Escribe los 10 dígitos del celular, sin el 57. Ej: 3001234567.',
                'numero.unique' => 'Ese número ya está en la lista.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Devuelve al formulario lo que la persona escribió, no el número normalizado.
            $request->merge(['numero' => $original]);
            throw $e;
        }

        return $request->input('numero');
    }
}
