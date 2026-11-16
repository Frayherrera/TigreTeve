<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Los enlaces de Google Noticias superan con frecuencia los 255 caracteres.
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->text('fuente')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->string('fuente')->nullable()->change();
        });
    }
};
