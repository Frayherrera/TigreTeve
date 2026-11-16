<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','slug'];

    /**
     * Slugs que no pueden ser categoría: nombres genéricos o rutas del sitio
     * (las categorías se sirven en /{slug}, después de todas las demás rutas).
     */
    private const SLUGS_RESERVADOS = [
        'noticias', 'noticia', 'news', 'article', 'articulo', 'articulos', 'home', 'inicio', 'portada',
        'ultimas-noticias', 'ultima-hora', 'offline', 'ping', 'profile', 'usuarios', 'categorias',
        'numeros', 'whatsapp-masivo', 'noticias-externas', 'login', 'logout', 'register',
        'dashboard', 'forgot-password', 'reset-password', 'verify-email', 'confirm-password', 'password',
    ];

    public function noticias()
    {
        return $this->hasMany(Noticia::class, 'category_id');
    }

    /**
     * Busca una categoría por nombre (sin importar mayúsculas ni tildes) y la crea si no existe.
     * Devuelve null si el nombre no sirve como categoría.
     */
    public static function buscarOCrear(?string $nombre): ?self
    {
        $nombre = self::normalizarNombre($nombre);
        if ($nombre === null) {
            return null;
        }

        $slug = Str::slug($nombre);
        if (in_array($slug, self::SLUGS_RESERVADOS, true)) {
            return null;
        }

        return static::where('slug', $slug)->first()
            ?? static::all()->first(fn (self $c) => Str::slug($c->nombre) === $slug)
            ?? static::create(['nombre' => $nombre, 'slug' => $slug]);
    }

    private static function normalizarNombre(?string $nombre): ?string
    {
        if (! is_string($nombre)) {
            return null;
        }

        // "Colombia > Bogotá" o "Deportes | Fútbol": se usa la sección principal.
        $nombre = preg_split('/\s*[>|»\/,]\s*/u', html_entity_decode(strip_tags($nombre), ENT_QUOTES | ENT_HTML5, 'UTF-8'))[0] ?? '';
        $nombre = trim(preg_replace('/\s+/u', ' ', $nombre));

        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 40 || Str::slug($nombre) === '') {
            return null;
        }

        // Solo se pasa a minúsculas si viene todo en mayúsculas ("DEPORTES"); así se respetan siglas.
        if ($nombre === mb_strtoupper($nombre)) {
            $nombre = mb_strtolower($nombre);
        }

        return mb_strtoupper(mb_substr($nombre, 0, 1)) . mb_substr($nombre, 1);
    }
}
