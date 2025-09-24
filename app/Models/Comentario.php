<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = ['noticia_id', 'user_id', 'autor_nombre','autor_email','contenido','aprobado'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function noticia()
    {
        return $this->belongsTo(Noticia::class, 'noticia_id');
    }
}
