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

    // --------------------------------------------------------------------------------
    // PROBAR
    public function iniciarTest(Modulo $modulo, Test $test) {
        $this->testService->limpiarSesion($test->id_test, $test->preguntas);
        $ruta = Auth::user()->rol === 'profesor' ? 'profesor.tests.realizar' : 'alumno.tests.realizar';
        return redirect()->route($ruta, [$modulo->id_modulo, $test->id_test]);
    }

    public function probarTest(Modulo $modulo, Test $test) {
        $preguntasOriginales = $test->preguntas;
        $preguntasMezcladas = $this->testService->aleatorizarPreguntas($preguntasOriginales, $test->id_test);

        foreach ($preguntasMezcladas as $pregunta) {
            $pregunta->contenido = $this->testService->aleatorizarOpciones($pregunta);
        }

        $test->setRelation('preguntas', $preguntasMezcladas);

        return view('usuario.tests.realizarTest', compact('modulo', 'test'));
    }

    public function correccionTest(Request $request, Modulo $modulo, Test $test) {
        $respuestas = $request->input('respuestas', []);
        
        $preguntas = $test->preguntas;
        $resultado = $this->testService->corregir($respuestas, $preguntas);

        $usuario = auth()->user();

        if ($usuario->rol === 'alumno') { 

            $puntuacion = $test->tipo === 'examen' && $test->examen->fecha_apertura->addMinutes($test->examen->duracion)->addSeconds(10) < now()
                ? 0
                : $resultado['nota'];

                Puntuacion::create([
                    'id_test'    => $test->id_test,
                    'id_alumno'  => $usuario->id_usuario, 
                    'puntuacion' => $puntuacion,
                    'tipo'       => $test->tipo
                ]);
        }

        $preguntasMezcladas = $this->testService->aleatorizarPreguntas($preguntas, $test->id_test);

        foreach ($preguntasMezcladas as $pregunta) {
            $pregunta->contenido = $this->testService->aleatorizarOpciones($pregunta);
        }

        $test->setRelation('preguntas', $preguntasMezcladas);

        return view('usuario.tests.realizarTest', [
            'modulo' => $modulo,
            'test'   => $test,
            'estado' => $resultado['informe'], 
            'nota'   => $puntuacion,
        ]);
    }
}