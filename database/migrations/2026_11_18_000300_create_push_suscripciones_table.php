<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_suscripciones', function (Blueprint $table) {
            $table->id();
            $table->text('endpoint');
            // El endpoint puede ser muy largo para un índice único: se indexa su hash.
            $table->char('endpoint_hash', 64)->unique();
            $table->string('p256dh');
            $table->string('auth');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_suscripciones');
    }
};
