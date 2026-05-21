<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use App\Models\Modulo;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    const SESSION_KEY = 'test_borrador';

    // Mostrar páginas de tests
    public function index(Modulo $modulo) {
        session()->forget(self::SESSION_KEY);
        $tests = $modulo->tests;
        return view('usuario.profesor.tests.tests', compact('modulo', 'tests'));
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Crear 

    // Mostrar página de creación de tests
    public function create(Modulo $modulo) {
        if ($modulo->preguntas->isEmpty()) {
            return redirect()->route('profesor.preguntas.index', $modulo->id_modulo)->withErrors(['error' => 'Debes crear preguntas antes de poder crear tests']);
        }

        $preguntas = $modulo->preguntas()->with('listaEtiquetas')->get();

        return view('usuario.profesor.tests.gestionTest', compact('modulo', 'preguntas'));
    }

    // Crear test
    public function store(Request $request, Modulo $modulo) {
        $preguntasSeleccionadas = count($request->input('preguntas', []));

        $validated = $request->validate([
            'nombre'              => 'required|string|max:255',
            'descripcion'         => 'required|string|max:255',
            'tipo'                => 'required|in:practica,examen,borrador',
            'preguntas'           => 'required|array|min:1',
            'preguntas.*'         => 'exists:preguntas,id_pregunta',
            'duracion'            => 'required_if:tipo,examen|nullable|integer|min:1',
            'fecha_apertura'      => 'required_if:tipo,examen|nullable|date',
            'fecha_cierre'        => 'required_if:tipo,examen|nullable|date|after:fecha_apertura',
            'preguntas_a_mostrar' => 'nullable|integer|min:1',
            'aleatorio'           => 'required|boolean',
            'correccion'          => 'required|boolean',
        ]);

        // LOGICA: Si es mayor, lo igualamos al total seleccionado
        $aMostrar = $validated['preguntas_a_mostrar'] ?? null;
        if ($aMostrar !== null && $aMostrar > $preguntasSeleccionadas) {
            $aMostrar = $preguntasSeleccionadas;
        }

        try {
            DB::beginTransaction();

            $test = Test::create([
                'nombre'              => $validated['nombre'],
                'descripcion'         => $validated['descripcion'],
                'tipo'                => $validated['tipo'],
                'id_modulo'           => $modulo->id_modulo,
                'preguntas_a_mostrar' => $aMostrar,
                'aleatorio'           => $validated['aleatorio'],
                'correccion'          => $validated['correccion'],
            ]);

            $test->preguntas()->sync($validated['preguntas']);

            if ($test->tipo == 'examen') {
                Examen::create([
                    'duracion'       => $validated['duracion'],
                    'fecha_apertura' => $validated['fecha_apertura'],
                    'fecha_cierre'   => $validated['fecha_cierre'],
                    'id_test'        => $test->id_test,
                ]);
            }

            DB::commit();

            session()->forget(self::SESSION_KEY);

            return redirect()->route('profesor.tests.index', $modulo->id_modulo);
        } catch(\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'No se ha podido crear el test, vuelve a intentarlo.']);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Editar 

    // Mostrar página de edición de tests
    public function edit(Modulo $modulo, Test $test) {
        $preguntas = $modulo->preguntas()->with('listaEtiquetas')->get();
        return view('usuario.profesor.tests.gestionTest', compact('preguntas', 'test', 'modulo'));
    }

    // Editar test
    public function update(Request $request, Modulo $modulo, Test $test) {
        $preguntasSeleccionadas = count($request->input('preguntas', []));

        $validated = $request->validate([
            'nombre'              => 'required|string|max:255',
            'descripcion'         => 'required|string|max:255',
            'tipo'                => 'required|in:practica,examen,borrador',
            'preguntas'           => 'required|array|min:1',
            'preguntas.*'         => 'exists:preguntas,id_pregunta',
            'duracion'            => 'required_if:tipo,examen|nullable|integer|min:1',
            'fecha_apertura'      => 'required_if:tipo,examen|nullable|date',
            'fecha_cierre'        => 'required_if:tipo,examen|nullable|date|after:fecha_apertura',
            'preguntas_a_mostrar' => 'nullable|integer|min:1',
            'aleatorio'           => 'required|boolean',
            'correccion'          => 'required|boolean',
        ]);

        // LOGICA: Si es mayor, lo igualamos al total seleccionado
        $aMostrar = $validated['preguntas_a_mostrar'] ?? null;
        if ($aMostrar !== null && $aMostrar > $preguntasSeleccionadas) {
            $aMostrar = $preguntasSeleccionadas;
        }

        try {
            DB::beginTransaction();

            if ($test->tipo == 'examen' && $validated['tipo'] == 'practica') {
                $test->examen()->delete();
            } else if ($test->tipo == 'practica' && $validated['tipo'] == 'examen') {
                Examen::create([
                    'duracion'       => $validated['duracion'],
                    'fecha_apertura' => $validated['fecha_apertura'],
                    'fecha_cierre'   => $validated['fecha_cierre'],
                    'id_test'        => $test->id_test,
                ]);
            } else if ($test->tipo == 'examen' && $validated['tipo'] == 'examen'){
                $test->examen->update([
                    'duracion'       => $validated['duracion'],
                    'fecha_apertura' => $validated['fecha_apertura'],
                    'fecha_cierre'   => $validated['fecha_cierre'],
                ]);
            }

            $test->update([
                'nombre'              => $validated['nombre'],
                'descripcion'         => $validated['descripcion'],
                'tipo'                => $validated['tipo'],
                'preguntas_a_mostrar' => $aMostrar,
                'aleatorio'           => $validated['aleatorio'],
                'correccion'          => $validated['correccion'],
            ]);

            $test->preguntas()->sync($validated['preguntas']);

            DB::commit();

            session()->forget(self::SESSION_KEY);

            return redirect()->route('profesor.tests.index', $modulo->id_modulo);
        } catch(\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'No se han podido guardar los cambios del test.']);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Eliminar 

    // Eliminar test
    public function destroy(Modulo $modulo, Test $test) {
        try {
            $test->delete();
            session()->forget(self::SESSION_KEY);
            return redirect()->route('profesor.tests.index', $modulo->id_modulo);
        } catch(\Exception $e) {
            return back()->withErrors(['error' => 'No se ha podido eliminar el test, vuelve a intentarlo.']);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Borrador 

    public function borrador(Request $request, Modulo $modulo, Test $test = null) {
        $preguntas = $request->input('preguntas', []);
        $aMostrar = $request->input('preguntas_a_mostrar');
        
        if ($aMostrar !== null && $aMostrar > count($preguntas)) {
            $aMostrar = count($preguntas);
        }

        session([self::SESSION_KEY => [
            'nombre'              => $request->input('nombre'),
            'descripcion'         => $request->input('descripcion'),
            'tipo'                => $request->input('tipo'),
            'preguntas'           => $preguntas,
            'duracion'            => $request->input('duracion'),       
            'fecha_apertura'      => $request->input('fecha_apertura'), 
            'fecha_cierre'        => $request->input('fecha_cierre'),
            'preguntas_a_mostrar' => $aMostrar,
            'aleatorio'           => $request->input('aleatorio'),
            'correccion'          => $request->input('correccion'),
            'origen_modulo'       => $modulo->id_modulo,
            'origen_test'         => $test?->id_test, 
        ]]);

        return redirect($request->input('destino_pregunta_url'));
    }
}