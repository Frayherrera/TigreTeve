<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappBaja extends Model
{
    protected $table = 'whatsapp_bajas';

    protected $fillable = ['telefono', 'motivo'];

    /**
     * Clave de comparación: últimos 10 dígitos. Así "573001234567" (como lo envía Meta)
     * coincide con "3001234567" (como suele venir en los CSV).
     */
    public static function clave(string $telefono): string
    {
        return substr(preg_replace('/\D/', '', $telefono), -10);
    }

    /** Quita de la lista los teléfonos dados de baja. Devuelve [enviables, omitidos]. */
    public static function filtrar(array $telefonos): array
    {
        $bajas = static::pluck('telefono')->map(fn ($t) => static::clave($t))->flip();

        $enviables = [];
        $omitidos = 0;
        foreach ($telefonos as $telefono) {
            if (isset($bajas[static::clave($telefono)])) {
                $omitidos++;
            } else {
                $enviables[] = $telefono;
            }
        }

        return [$enviables, $omitidos];
    }
}
