<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSuscripcion extends Model
{
    protected $table = 'push_suscripciones';

    protected $fillable = ['endpoint', 'endpoint_hash', 'p256dh', 'auth'];
}
