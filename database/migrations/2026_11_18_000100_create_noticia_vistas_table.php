<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lecturas por noticia y por día (zona editorial), sin bots ni recargas.
        Schema::create('noticia_vistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->date('fecha');
            $table->unsignedInteger('vistas')->default(0);
            $table->unique(['noticia_id', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticia_vistas');
    }
};
