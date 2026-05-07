<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Modulo;
use App\Models\Puntuacion;
use App\Models\Test;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PuntuacionSeeder extends Seeder
{
    public function run(): void
    {
        // Perfiles de rendimiento por alumno (nota base ± variación)
        $perfiles = [
            'alumno1@gmail.com'   => ['base' => 8.0, 'variacion' => 1.5],
            'alumno2@gmail.com'  => ['base' => 6.5, 'variacion' => 2.0],
            'alumno3@gmail.com'      => ['base' => 9.0, 'variacion' => 0.8],
            'alumno4@gmail.com'    => ['base' => 5.5, 'variacion' => 2.5],
            'alumno5@gmail.com'    => ['base' => 7.0, 'variacion' => 1.5],
        ];

        $usuarios = Usuario::whereIn('email', array_keys($perfiles))->get()->keyBy('email');

        foreach ($perfiles as $email => $perfil) {
            $usuario = $usuarios[$email] ?? null;
            if (!$usuario || !$usuario->alumno) continue;

            $alumno = $usuario->alumno;

            // Obtenemos los módulos a los que pertenece el alumno
            $modulos = $alumno->modulos;

            foreach ($modulos as $modulo) {
                $tests = $modulo->tests;

                foreach ($tests as $test) {
                    // Generamos entre 1 y 3 intentos por test (más para práctica)
                    $intentos = $test->tipo === 'practica'
                        ? rand(2, 3)
                        : rand(1, 2);

                    for ($i = 0; $i < $intentos; $i++) {
                        // La nota mejora ligeramente con cada intento
                        $mejora = $i * 0.5;
                        $nota   = $this->generarNota($perfil['base'] + $mejora, $perfil['variacion']);

                        // Fecha escalonada: cada intento ~7 días después del anterior
                        $fecha = Carbon::now()
                            ->subDays(rand(60, 90))
                            ->addDays($i * rand(5, 10))
                            ->format('Y-m-d H:i:s');

                        Puntuacion::create([
                            'id_test'    => $test->id_test,
                            'id_alumno'  => $alumno->id_alumno,
                            'fecha'      => $fecha,
                            'puntuacion' => $nota,
                            'tipo'       => $test->tipo,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Genera una nota aleatoria entre 0 y 10, centrada en $base con ±$variacion.
     */
    private function generarNota(float $base, float $variacion): float
    {
        $nota = $base + (mt_rand(-100, 100) / 100) * $variacion;
        return round(max(0.0, min(10.0, $nota)), 2);
    }
}
