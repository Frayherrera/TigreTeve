<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Noticia extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'titulo',
        'slug',
        'resumen',
        'cuerpo',
        'estado',
        'publicado_en',
        'portada_path',
        'fuente',
        'is_featured'
    ];
    protected $casts = ['publicado_en' => 'datetime'];

    public function scopeDestacadas($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNoD($query)
    {
        return $query->where('is_featured', false);
    }

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,       // Modelo relacionado
            'noticia_tag',    // Tabla pivote
            'noticia_id',     // Clave foránea de este modelo en la pivote
            'tag_id'          // Clave foránea del otro modelo en la pivote
        );
    }
    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
    public function media()
    {
        return $this->hasMany(Media::class);
    }
    // Scopes útiles
    public function scopePublicadas($query)
    {
        return $query->where(function ($q) {
            // Caso 1: Publicadas (sin importar fecha)
            $q->where('estado', 'publicada')

                // Caso 2: Programadas cuya fecha ya llegó
                ->orWhere(function ($sub) {
                    $sub->where('estado', 'programada')
                        ->where('publicado_en', '<=', now());
                });
        });
    }



    public function scopeProgramadasPara($q, $fecha)
    {
        return $q->where('estado', 'programada')->where(
            'publicado_en',
            '<=',
            $fecha
        );
    }
    public function scopeBuscar($query, $term)
    {
        $term = trim($term);

        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('titulo', 'LIKE', "%{$term}%")
                ->orWhere('resumen', 'LIKE', "%{$term}%")
                ->orWhere('cuerpo', 'LIKE', "%{$term}%")
                ->orWhereHas('tags', function ($tagQuery) use ($term) {
                    $tagQuery->where('nombre', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('categoria', function ($catQuery) use ($term) {
                    $catQuery->where('nombre', 'LIKE', "%{$term}%");
                });
        });
    }
}
