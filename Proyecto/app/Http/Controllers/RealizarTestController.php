<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Test;
use App\Models\Puntuacion;
use App\Services\TestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealizarTestController extends Controller
{
    public function __construct(protected TestService $testService) {}

    public function iniciarTest(Modulo $modulo, Test $test) {
        // Al iniciar se limpia la sesión por completo, forzando un nuevo sorteo/mezcla aleatoria
        $this->testService->limpiarSesion($test->id_test, $test->preguntas);
        session(['test_inicio_' . $test->id_test => now()]);
        $ruta = Auth::user()->rol === 'profesor' ? 'profesor.tests.realizar' : 'alumno.tests.realizar';
        return redirect()->route($ruta, [$modulo->id_modulo, $test->id_test]);
    }

    public function probarTest(Modulo $modulo, Test $test) {
        // Obtenemos el listado respetando preguntas_a_mostrar y aleatorio
        $preguntasProcesadas = $this->testService->prepararPreguntasTest($test);

        // Mezclamos las opciones internas de cada pregunta
        foreach ($preguntasProcesadas as $pregunta) {
            $pregunta->contenido = $this->testService->aleatorizarOpciones($pregunta);
        }

        $test->setRelation('preguntas', $preguntasProcesadas);

        return view('usuario.tests.realizarTest', compact('modulo', 'test'));
    }

    public function correccionTest(Request $request, Modulo $modulo, Test $test) {
        $respuestas = $request->input('respuestas', []);
        
        // Recuperamos exactamente las mismas preguntas que el alumno resolvió
        $preguntas = $this->testService->prepararPreguntasTest($test);

        // Aplicamos el orden aleatorizado guardado en sesión ANTES de corregir,
        // para que columna_b_mezclada esté disponible al convertir letra → texto en conecta
        foreach ($preguntas as $pregunta) {
            $pregunta->contenido = $this->testService->aleatorizarOpciones($pregunta);
        }

        $resultado = $this->testService->corregir($respuestas, $preguntas);

        $usuario = auth()->user();
        $puntuacion = $resultado['nota'];

        if ($usuario->rol === 'alumno') { 
            if ($test->tipo === 'examen' && now() > $test->examen->fecha_cierre->addSeconds(60)) {
                $puntuacion = 0;
            }

            $fechaInicio = session('test_inicio_' . $test->id_test, now());
            $duracionSegundos = $fechaInicio->diffInSeconds(now());

            Puntuacion::create([
                'id_test'           => $test->id_test,
                'id_alumno'         => $usuario->id_usuario,
                'puntuacion'        => $puntuacion,
                'tipo'              => $test->tipo,
                'duracion_segundos' => $duracionSegundos,
            ]);
        }
        $test->setRelation('preguntas', $preguntas);

        // REGLA DE CORRECCIÓN: Si es false, ocultamos las correcciones visuales
        // Pasamos null si está desactivado para que el parcial de Blade no dibuje el feedback analítico
        $estadoVista = $test->correccion ? $resultado['informe'] : null;

        return view('usuario.tests.realizarTest', [
            'modulo' => $modulo,
            'test'   => $test,
            'estado' => $estadoVista, 
            'nota'   => $puntuacion,
        ]);
    }
}