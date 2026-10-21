<?php

namespace App\Http\Controllers;

use App\Models\Numero;
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
        $numero = $this->normalize($request->input('numero'));

        $request->merge(['numero' => $numero]);

        $request->validate([
            'numero' => ['required', 'string', 'regex:/^\d{7,15}$/', Rule::unique('numeros', 'numero')],
        ], [
            'numero.regex' => 'Ingresa un número válido de entre 7 y 15 dígitos (con indicativo, sin + ni espacios).',
            'numero.unique' => 'Ese número ya está en la lista.',
        ]);

        Numero::create(['numero' => $numero]);

        return redirect()->route('numeros.index')->with('ok', 'Número agregado.');
    }

    public function update(Request $request, Numero $numero)
    {
        $nuevo = $this->normalize($request->input('numero'));

        $request->merge(['numero' => $nuevo]);

        $request->validate([
            'numero' => ['required', 'string', 'regex:/^\d{7,15}$/', Rule::unique('numeros', 'numero')->ignore($numero->id)],
        ], [
            'numero.regex' => 'Ingresa un número válido de entre 7 y 15 dígitos (con indicativo, sin + ni espacios).',
            'numero.unique' => 'Ese número ya está en la lista.',
        ]);

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
                $numero = $this->normalize($cell);

                if (! preg_match('/^\d{7,15}$/', $numero)) {
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

    private function normalize(?string $valor): string
    {
        return preg_replace('/[^\d]/', '', (string) $valor);
    }
}
