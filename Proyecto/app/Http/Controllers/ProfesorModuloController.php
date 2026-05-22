<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfesorModuloController extends Controller {

    // Extraer las reglas de validación comunes para no repetirlas
    private function getValidationRules(): array {
        return [
            'ciclo'               => 'required|string|max:255',
            'modulo'              => 'required|string|max:255',
            'color'               => 'required|string|max:7',
            'idioma'              => 'required|string|max:2',
            'clave_matriculacion' => 'required|string|min:4'
        ];
    }

    // ================
    // ==== CREAR ====
    // ================

    // Mostrar la vista de la creación del módulo
    public function create() {
        return view('usuario.profesor.modulo.gestionModulo');
    }

    // Crear el modulo
    public function store(Request $request) {
        $validated = $request->validate($this->getValidationRules());

        // Añadimos el ID del profesor al array validado antes de insertar
        $validated['id_profesor'] = Auth::user()->profesor->id_profesor;

        try {
            // Pasamos el array completo a Eloquent directamente
            $modulo = Modulo::create($validated);

            return redirect()->route('inicio.dashboardProfesor.mostrar', $modulo->id_modulo);
        } catch(\Exception $e) {
           return back()->withErrors(['error' => 'No se ha podido crear el modulo, vuelva a intentar o contacte con el administrador']);
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
        $validated = $request->validate($this->getValidationRules());

        try {
            // Actualizamos pasando directamente el array validado
            $modulo->update($validated);

            return redirect()->route('inicio.dashboardProfesor.mostrar', $modulo->id_modulo);
        } catch(\Exception $e) {
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