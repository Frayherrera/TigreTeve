<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticiaTag extends Model
{
    protected $table = 'noticia_tag';
    protected $fillable = ['noticia_id', 'tag_id'];
}
