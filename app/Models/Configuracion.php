<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuracions';

    protected $fillable = [
        'parametro',
        'valor',
        'descripcion',
    ];

    /**
     * Obtiene el valor de una configuración por su parámetro.
     */
    public static function valor(string $parametro, ?string $default = null): ?string
    {
        $config = static::where('parametro', $parametro)->first();

        return $config?->valor ?? $default;
    }
}
