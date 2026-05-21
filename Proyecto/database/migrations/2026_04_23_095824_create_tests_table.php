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
        Schema::create('tests', function (Blueprint $table) {
            $table->id('id_test');
            $table->string('nombre');
            $table->string('descripcion');
            $table->enum('tipo', ['practica', 'examen', 'borrador']);
            $table->integer('preguntas_a_mostrar')->nullable();
            $table->boolean('aleatorio');
            $table->boolean('correccion');
            $table->foreignId('id_modulo')
                  ->constrained('modulos', 'id_modulo')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
