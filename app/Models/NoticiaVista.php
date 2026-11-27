<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticiaVista extends Model
{
    protected $table = 'noticia_vistas';

    public $timestamps = false;

    protected $fillable = ['noticia_id', 'fecha', 'vistas'];

    protected $casts = ['fecha' => 'date'];

    public function noticia()
    {
        return $this->belongsTo(Noticia::class);
    }
}
