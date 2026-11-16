<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Enlace de Google Noticias del que se importó la nota; `fuente` guarda la URL real del medio.
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->text('enlace_google')->nullable()->after('fuente');
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn('enlace_google');
        });
    }
};
