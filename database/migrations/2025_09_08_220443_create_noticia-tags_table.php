<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('noticia_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('noticia_id');
            $table->unsignedBigInteger('tag_id');

            // Llaves foráneas
            $table->foreign('noticia_id')->references('id')->on('noticias')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            // Para que no se dupliquen relaciones
            $table->primary(['noticia_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticia-tags');
    }
};
