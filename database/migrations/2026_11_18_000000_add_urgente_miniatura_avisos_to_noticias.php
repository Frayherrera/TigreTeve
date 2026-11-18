<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            // Franja de "Última hora" en la portada.
            $table->boolean('urgente')->default(false)->after('is_featured');
            // Versión liviana (WebP) de la portada para listados.
            $table->string('miniatura_path')->nullable()->after('portada_path');
            // Evitan avisar dos veces la misma noticia.
            $table->timestamp('push_enviado_en')->nullable();
            $table->timestamp('whatsapp_enviado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn(['urgente', 'miniatura_path', 'push_enviado_en', 'whatsapp_enviado_en']);
        });
    }
};
