<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['noticia_id', 'path', 'tipo', 'caption'];

    public function noticia()
    {
        return $this->belongsTo(Noticia::class);
    }
}
