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
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('resumen', 500)->nullable();
            $table->longText('cuerpo');
            $table->enum('estado', ['borrador', 'publicada', 'programada'])->default('borrador');
            $table->dateTime('publicado_en')->nullable();
            $table->string('portada_path')->nullable();
            $table->string('fuente')->nullable();
            $table->unsignedBigInteger('vistas')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['estado', 'publicado_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // quitar la FK primero
        });

        Schema::dropIfExists('noticias');
    }
};
