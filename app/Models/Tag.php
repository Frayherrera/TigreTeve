<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';
    protected $fillable = ['nombre'];
    public function noticias()
    {
        return $this->belongsToMany(
            Noticia::class,   // Modelo relacionado
            'noticia_tag',    // Tabla pivote
            'tag_id',         // Clave foránea de este modelo en la pivote
            'noticia_id'      // Clave foránea del otro modelo en la pivote
        );
    }
}
