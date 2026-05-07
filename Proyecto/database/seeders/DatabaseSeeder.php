<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Orden de ejecución:
     *  1. UsuarioSeeder    → profesores y alumnos (usuarios + tablas derivadas)
     *  2. ModuloSeeder     → 5 módulos asignados a los profesores
     *  3. EtiquetaSeeder   → 15 etiquetas temáticas
     *  4. PreguntaSeeder   → 20 preguntas por módulo + asociación de etiquetas
     *  5. TestSeeder       → 4 tests por módulo (3 práctica + 1 examen)
     *  6. AlumnoModuloSeeder → matriculaciones con tiene_acceso = true
     *  7. PuntuacionSeeder → intentos de test por alumno con notas realistas
     */
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            ModuloSeeder::class,
            EtiquetaSeeder::class,
            PreguntaSeeder::class,
            TestSeeder::class,
            AlumnoModuloSeeder::class,
            PuntuacionSeeder::class,
            ExamenSeeder::class
        ]);
    }
}
