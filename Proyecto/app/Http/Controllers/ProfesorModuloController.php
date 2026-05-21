<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfesorModuloController extends Controller {

    // Mostrar la vista de la creación del módulo

    public function create() {
        return view('usuario.profesor.modulo.gestionModulo');
    }

    // Crear el modulo

    public function store(Request $request) {

        $validated = $request->validate([
            'ciclo' => 'required|string|max:255',
            'modulo' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'idioma' => 'required|string|max:2',
            'clave_matriculacion' => 'required|string|min:4'
        ]);

        try {
            $modulo = Modulo::create([
                'ciclo' => $validated['ciclo'],
                'modulo' => $validated['modulo'],
                'color' => $validated['color'],
                'idioma' => $validated['idioma'],
                'clave_matriculacion' => $validated['clave_matriculacion'],
                'id_profesor' => Auth::user()->profesor->id_profesor,
            ]);

            return redirect()->route('inicio.dashboardProfesor.mostrar', $modulo->id_modulo);
        } catch(\Exception $e) {
           return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ================
    // ==== EDITAR ====
    // ================

    public function edit(Modulo $modulo) {
        return view('usuario.profesor.modulo.gestionModulo', compact('modulo'));
    }

    // Editar módulo
    public function update(Request $request, Modulo $modulo) {
        $validated = $request->validate([
            'ciclo' => 'required|string|max:255',
            'modulo' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'idioma' => 'required|string|max:2',
            'clave_matriculacion' => 'required|string|min:4'
        ]);

        try {
            DB::beginTransaction();

            $modulo->update([
                'ciclo' => $validated['ciclo'],
                'modulo' => $validated['modulo'],
                'color' => $validated['color'],
                'idioma' => $validated['idioma'],
                'clave_matriculacion' => $validated['clave_matriculacion'],
            ]);

            DB::commit();

            return redirect()->route('inicio.dashboardProfesor.mostrar', compact('modulo'));
        } catch(\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'No se ha podido editar el módulo, vuelve a intentarlo']);
        }
    }

    // ==================
    // ==== ELIMINAR ====
    // ==================

    // Eliminar módulo
    public function destroy(Modulo $modulo) {
        try {
            $modulo->delete();
            return redirect()->route('inicio.dashboardProfesor.mostrar');
        } catch(\Exception $e) {
            return back()->withErrors(['error' => 'No se ha podido eliminar el módulo, vuelve a intentarlo']);
        }
    }
}