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
        'enlace_google',
        'is_featured',
        'urgente',
        'miniatura_path',
        'push_enviado_en',
        'whatsapp_enviado_en',
    ];
    protected $casts = [
        'publicado_en' => 'datetime',
        'urgente' => 'boolean',
        'push_enviado_en' => 'datetime',
        'whatsapp_enviado_en' => 'datetime',
    ];

    /** ¿La ve el público ahora mismo? (misma regla que el scope publicadas). */
    public function esVisible(): bool
    {
        return $this->estado === 'publicada'
            || ($this->estado === 'programada' && $this->publicado_en && $this->publicado_en->lte(now()));
    }

    /** Momento en que sale al público: ahora si ya es visible, o la fecha programada. */
    public function momentoDePublicacion(): ?\Illuminate\Support\Carbon
    {
        if ($this->estado === 'publicada') {
            return now();
        }

        return $this->estado === 'programada' ? $this->publicado_en : null;
    }

    /** Fecha que se muestra como "publicada el". */
    public function fechaPublicacion(): \Illuminate\Support\Carbon
    {
        return $this->publicado_en && $this->publicado_en->lte(now()) ? $this->publicado_en : $this->created_at;
    }

    /** URL de la portada en S3 (original). */
    public function portadaUrl(): ?string
    {
        return $this->portada_path ? \Illuminate\Support\Facades\Storage::disk('s3')->url($this->portada_path) : null;
    }

    /** URL de la miniatura WebP si ya se generó; si no, la portada original. */
    public function miniaturaUrl(): ?string
    {
        if ($this->miniatura_path) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($this->miniatura_path);
        }

        return $this->portadaUrl();
    }

    public function vistasDiarias()
    {
        return $this->hasMany(NoticiaVista::class);
    }

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
