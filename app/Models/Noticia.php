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

    public function scopeNoD($query){
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
    public function scopePublicadas($q)
    {
        return $q->where('estado', 'publicada')->where(function ($qq) {
            $qq->whereNull('publicado_en')
                ->orWhere('publicado_en', '<=', now());
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
    public function scopeBuscar($q, $term)
    {
        $t = Str::of($term)->trim();
        if ($t->isEmpty()) return $q;
        return $q->where(function ($qq) use ($t) {
            $qq->where('titulo', 'like', "%{$t}%")->orWhere('resumen', 'like', "%{$t}%")->orWhere('cuerpo', 'like', "%{$t}%");
        });
    }
}
