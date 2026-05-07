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
        $tests = $modulo->tests;
        return view('usuario.profesor.tests.tests', compact('modulo', 'tests'));
    }

    // ===============
    // ==== CREAR ====
    // ===============

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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|in:practica,examen',
            'preguntas' => 'required|array|min:1',
            'preguntas.*' => 'exists:preguntas,id_pregunta',
            'duracion' => 'required_if:tipo,examen|nullable|integer|min:1',
            'fecha_apertura' => 'required_if:tipo,examen|nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $test = Test::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'tipo' => $validated['tipo'],
                'id_modulo' => $modulo->id_modulo,
            ]);

            $test->preguntas()->sync($validated['preguntas']);

            if ($test->tipo == 'examen') {
                Examen::create([
                    'duracion' => $validated['duracion'],
                    'fecha_apertura' => $validated['fecha_apertura'],
                    'id_test' => $test->id_test,
                ]);
            }

            DB::commit();

            session()->forget(self::SESSION_KEY);

            return redirect()->route('profesor.tests.index', $modulo->id_modulo);
        } catch(\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'No se ha podido crear el test, vuelve a intentarlo']);
        }

    }

    // ================
    // ==== EDITAR ====
    // ================

    // Mostrar página de edición de tests
    public function edit(Modulo $modulo, Test $test) {
        $preguntas = $modulo->preguntas()->with('listaEtiquetas')->get();
        return view('usuario.profesor.tests.gestionTest', compact('preguntas', 'test', 'modulo'));
    }

    // Editar test
    public function update(Request $request, Modulo $modulo, Test $test) {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|in:practica,examen',
            'preguntas' => 'required|array|min:1',
            'preguntas.*' => 'exists:preguntas,id_pregunta',
            'duracion' => 'required_if:tipo,examen|nullable|integer|min:1',
            'fecha_apertura' => 'required_if:tipo,examen|nullable|date',
        ]);

        try {
            DB::beginTransaction();

            if ($test->tipo == 'examen' && $validated['tipo'] == 'practica') {
                $test->examen()->delete();
            } else if ($test->tipo == 'practica' && $validated['tipo'] == 'examen') {
                Examen::create([
                    'duracion' => $validated['duracion'],
                    'fecha_apertura' => $validated['fecha_apertura'],
                    'id_test' => $test->id_test,
                ]);
            } else if ($test->tipo == 'examen' && $validated['tipo'] == 'examen'){
                $test->examen->update([
                    'duracion' => $validated['duracion'],
                    'fecha_apertura' => $validated['fecha_apertura'],
                ]);
            }

            $test->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'tipo' => $validated['tipo'],
            ]);

            $test->preguntas()->sync($validated['preguntas']);

            DB::commit();

            session()->forget(self::SESSION_KEY);

            return redirect()->route('profesor.tests.index', $modulo->id_modulo);
        } catch(\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ==================
    // ==== ELIMINAR ====
    // ==================

    // Eliminar test
    public function destroy(Modulo $modulo, Test $test) {
        try {
            $test->delete();
            session()->forget(self::SESSION_KEY);
            return redirect()->route('profesor.tests.index', $modulo->id_modulo);
        } catch(\Exception $e) {
            return back()->withErrors(['error' => 'No se ha podido eliminar el test, vuelve a intentarlo']);
        }
    }


    ////////////////////////////////////////////////////////
    // Borrador
    public function borrador(Request $request, Modulo $modulo, Test $test = null) {
        session([self::SESSION_KEY => [
            'nombre'         => $request->input('nombre'),
            'descripcion'    => $request->input('descripcion'),
            'tipo'           => $request->input('tipo'),
            'preguntas'      => $request->input('preguntas', []),
            'duracion'       => $request->input('duracion'),       // <-- NUEVO
            'fecha_apertura' => $request->input('fecha_apertura'), // <-- NUEVO
            'origen_modulo'  => $modulo->id_modulo,
            'origen_test'    => $test?->id_test, 
        ]]);

        return redirect($request->input('destino_pregunta_url'));
    }
}
