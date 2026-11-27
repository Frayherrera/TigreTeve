<?php

namespace App\Support;

/**
 * Números de celular de Colombia. Se escriben los 10 dígitos y el indicativo 57 se agrega solo,
 * porque WhatsApp exige el número con indicativo.
 */
class Telefono
{
    public const INDICATIVO = '57';

    /**
     * Para guardar en la lista: "300 123 4567" → "573001234567".
     * Acepta también el número que ya trae el 57 (o +57). Null si no es un celular válido.
     */
    public static function normalizar(?string $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);

        if (preg_match('/^\d{10}$/', $digitos)) {
            return self::INDICATIVO.$digitos;
        }

        if (preg_match('/^'.self::INDICATIVO.'\d{10}$/', $digitos)) {
            return $digitos;
        }

        return null;
    }

    /**
     * Para enviar: agrega el 57 a los números de 10 dígitos y deja los que ya traen indicativo
     * (incluye números viejos de la lista o de otros países). Null si no parece un teléfono.
     */
    public static function paraEnvio(?string $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);

        if (strlen($digitos) === 10) {
            return self::INDICATIVO.$digitos;
        }

        return strlen($digitos) >= 11 && strlen($digitos) <= 15 && ! preg_match('/^0+$/', $digitos)
            ? $digitos
            : null;
    }

    /** "573001234567" → "3001234567" (para mostrar en el campo con el +57 fijo). */
    public static function sinIndicativo(string $numero): string
    {
        return str_starts_with($numero, self::INDICATIVO) && strlen($numero) === 12
            ? substr($numero, 2)
            : $numero;
    }
}
