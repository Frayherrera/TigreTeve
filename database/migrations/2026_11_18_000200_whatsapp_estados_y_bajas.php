<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            // Id del mensaje en Meta: lo usa el webhook para marcar entregado / leído.
            $table->string('wamid')->nullable()->after('batch_id');
            $table->string('error')->nullable()->after('wamid');
            $table->index('wamid');
            $table->index('batch_id');
        });

        // Personas que pidieron no recibir más mensajes (respondieron SALIR).
        Schema::create('whatsapp_bajas', function (Blueprint $table) {
            $table->id();
            $table->string('telefono', 30)->unique();
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_bajas');
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->dropIndex(['wamid']);
            $table->dropIndex(['batch_id']);
            $table->dropColumn(['wamid', 'error']);
        });
    }
};
