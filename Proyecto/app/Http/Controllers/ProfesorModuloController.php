<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfesorModuloController extends Controller {

    // Mostrar la vista del módulo del profesor

    public function modulosMostrar(Modulo $modulo) {
        return view('profesor.modulo.modulo', compact('modulo'));
    }

    // Mostrar la vista de la creación del módulo

    public function crearModuloMostrar() {
        return view('profesor.modulo.crearModulo');
    }

    // Crear el modulo

    public function crearModuloCrear(Request $request) {

        $validated = $request->validate([
            'ciclo' => 'required|string|max:255',
            'modulo' => 'required|string|max:255',
            'clave_matriculacion' => 'required|string|min:4'
        ]);

        $modulo = Modulo::create([
            'ciclo' => $validated['ciclo'],
            'modulo' => $validated['modulo'],
            'clave_matriculacion' => $validated['clave_matriculacion'],
            'id_profesor' => Auth::user()->profesor->id_profesor,
        ]);

        return redirect()->route('profesor.modulos.mostrar', compact('modulo'));
    }
}
