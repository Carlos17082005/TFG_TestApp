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
        Schema::create('etiquetas', function (Blueprint $table) {
            $table->id('id_etiqueta');
            $table->foreignId('id_modulo')
                  ->constrained('modulos', 'id_modulo')
                  ->cascadeOnDelete();
            $table->string('nombre');

            // Único por módulo: la misma palabra puede existir en módulos distintos
            $table->unique(['id_modulo', 'nombre']);
        });

        Schema::create('etiqueta_pregunta', function (Blueprint $table) {
            $table->foreignId('id_pregunta')->constrained('preguntas', 'id_pregunta')->cascadeOnDelete();
            $table->foreignId('id_etiqueta')->constrained('etiquetas', 'id_etiqueta')->cascadeOnDelete();
            $table->primary(['id_pregunta', 'id_etiqueta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etiqueta_pregunta');
        Schema::dropIfExists('etiquetas');
    }
};