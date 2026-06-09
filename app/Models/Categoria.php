<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','slug'];

    public function noticias()
    {
        return $this->hasMany(Noticia::class);
    }
}
