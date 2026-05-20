<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Pregunta;
use App\Models\Test;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = Modulo::with('preguntas')->get()->keyBy('modulo');

        // ── Helper: crea un test y asocia preguntas por índice (0-based) ────
        $crearTest = function (
            Modulo $modulo,
            string $nombre,
            string $descripcion,
            string $tipo,
            array  $indices
        ): Test {
            $test = Test::create([
                'nombre'      => $nombre,
                'descripcion' => $descripcion,
                'tipo'        => $tipo,
                'id_modulo'   => $modulo->id_modulo,
                'preguntas_a_mostrar' => null,
                'aleatorio'           => true,
                'correccion'          => true,
            ]);

            $preguntas = $modulo->preguntas->values();
            $ids = collect($indices)
                ->map(fn($i) => $preguntas[$i]->id_pregunta ?? null)
                ->filter()
                ->toArray();

            $test->preguntas()->sync($ids);

            return $test;
        };

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 1 — Programación
        // ════════════════════════════════════════════════════════════════════
        $mProg = $modulos['Programación'];

        $crearTest($mProg, 'Test rápido: Variables y Arrays', 'Preguntas básicas sobre variables y arrays en PHP', 'practica', [0, 1, 3, 4, 9, 10, 12, 14]);
        $crearTest($mProg, 'Test de POO', 'Conceptos de Programación Orientada a Objetos', 'practica', [5, 6, 11, 13, 15, 17]);
        $crearTest($mProg, 'Test de Bucles', 'Tipos de bucles y sus usos', 'practica', [2, 7, 10, 14, 16, 18, 19]);
        $crearTest($mProg, 'Examen Trimestral — Programación', 'Examen con todos los temas del trimestre', 'examen', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 2 — Bases de Datos
        // ════════════════════════════════════════════════════════════════════
        $mBBDD = $modulos['Bases de Datos'];

        $crearTest($mBBDD, 'Test SQL básico', 'Sentencias fundamentales de SQL', 'practica', [0, 1, 4, 5, 6, 9, 10, 13]);
        $crearTest($mBBDD, 'Test de JOINs', 'Tipos de JOIN y sus diferencias', 'practica', [2, 8, 14, 16]);
        $crearTest($mBBDD, 'Test de Normalización', 'Formas normales y dependencias', 'practica', [3, 7, 12, 18, 19]);
        $crearTest($mBBDD, 'Examen Trimestral — BBDD', 'Evaluación completa de bases de datos', 'examen', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 3 — Desarrollo Web
        // ════════════════════════════════════════════════════════════════════
        $mWeb = $modulos['Desarrollo Web'];

        $crearTest($mWeb, 'Test HTML', 'Etiquetas y estructura HTML', 'practica', [0, 1, 4, 7, 9, 13, 15, 18]);
        $crearTest($mWeb, 'Test CSS', 'Selectores, propiedades y unidades CSS', 'practica', [2, 5, 8, 10, 12, 14, 16, 19]);
        $crearTest($mWeb, 'Test JavaScript', 'Interactividad y DOM con JavaScript', 'practica', [3, 6, 11, 13, 17]);
        $crearTest($mWeb, 'Examen Trimestral — Web', 'Examen completo de desarrollo web', 'examen', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 4 — Sistemas Operativos
        // ════════════════════════════════════════════════════════════════════
        $mSO = $modulos['Sistemas Operativos'];

        $crearTest($mSO, 'Test Linux básico', 'Fundamentos del sistema operativo Linux', 'practica', [0, 2, 6, 9, 10, 14, 16, 18]);
        $crearTest($mSO, 'Test de Comandos', 'Comandos esenciales de terminal', 'practica', [1, 3, 4, 5, 7, 11, 12, 13, 15]);
        $crearTest($mSO, 'Test de Permisos', 'Gestión de permisos en Linux', 'practica', [10, 12, 17, 19]);
        $crearTest($mSO, 'Examen Trimestral — SO', 'Evaluación completa de sistemas operativos', 'examen', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 5 — Entornos de Desarrollo
        // ════════════════════════════════════════════════════════════════════
        $mEdes = $modulos['Entornos de Desarrollo'];

        $crearTest($mEdes, 'Test Git básico', 'Comandos fundamentales de Git', 'practica', [0, 1, 4, 5, 6, 11, 17, 18, 19]);
        $crearTest($mEdes, 'Test Git avanzado', 'Ramas, merge y flujos de trabajo', 'practica', [2, 8, 9, 12, 15, 18]);
        $crearTest($mEdes, 'Test de IDEs', 'Herramientas y entornos integrados', 'practica', [3, 7, 10, 13, 14, 16]);
        $crearTest($mEdes, 'Examen Trimestral — Entornos', 'Evaluación completa de entornos de desarrollo', 'examen', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);
    }
}