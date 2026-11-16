<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Carbon;

/**
 * Reglas compartidas para la fecha de publicación programada.
 * El formulario envía la hora local (Colombia); se guarda en la zona de la app (UTC).
 */
trait ProgramaPublicacion
{
    protected function prepareForValidation(): void
    {
        if ($this->input('estado') !== 'programada') {
            $this->merge(['publicado_en' => null]);
            return;
        }

        $valor = $this->input('publicado_en');
        if (filled($valor)) {
            try {
                $this->merge([
                    'publicado_en' => Carbon::parse($valor, config('app.zona_editorial'))
                        ->setTimezone(config('app.timezone'))
                        ->toDateTimeString(),
                ]);
            } catch (\Throwable) {
                // Valor inválido: lo rechaza la regla 'date'.
            }
        }
    }

    /** @return array<int, string> */
    protected function reglasPublicadoEn(?Carbon $fechaActual = null): array
    {
        $reglas = ['nullable', 'required_if:estado,programada', 'date'];

        // Exigir fecha futura solo si cambia (al editar una ya programada no se obliga a moverla).
        $nueva = $this->input('publicado_en');
        if ($this->input('estado') === 'programada' && (! $fechaActual || $fechaActual->toDateTimeString() !== $nueva)) {
            $reglas[] = 'after:now';
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'publicado_en.required_if' => 'Elige la fecha y hora en que se publicará la noticia.',
            'publicado_en.date' => 'La fecha de publicación no es válida.',
            'publicado_en.after' => 'La fecha de publicación programada debe ser en el futuro.',
        ];
    }
}
