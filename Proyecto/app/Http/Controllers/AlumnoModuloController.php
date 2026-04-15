<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnoModuloController extends Controller
{
    // Muestra todos los módulos a los que se ha unido el alumno

    public function modulosMostrar() {
        $modulos = Auth::user()->alumno->modulos;

        return view('alumno.modulo.modulos', compact('modulos'));
    }

    // Muestra todos los módulos creados por los profesores

    public function seleccionarModuloMostrar() {
        $alumno = Auth::user()->alumno;

        $modulos = Modulo::whereDoesntHave('alumnos', function ($query) use ($alumno) {
            $query->where('alumnos.id_alumno', $alumno->id_alumno);
        })->get();

        return view('alumno.modulo.unirseModulo', compact('modulos'));
    }

    // Muestra el formulario para introducir la clave de matriculación al módulo

    public function matricularseModuloMostrar(Modulo $modulo) {
        return view('alumno.modulo.matricularseModulo', compact('modulo'));
    }

    // Introduce al alumno en el módulo usando una tabla pivote

    public function matricularseModuloEntrar(Request $request, Modulo $modulo) {
        $clave_intento = $request->clave_matriculacion;

        if ($modulo->clave_matriculacion !== $clave_intento) {
            return back()
            ->withErrors(['clave_matriculacion' => 'Clave incorrecta']);
        }

        $alumno = Auth::user()->alumno;

        $alumno->modulos()->attach($modulo->id_modulo);

        return redirect()->route('alumno.moduloDashboard.mostrar', compact('modulo'));
    }

    // --- DASHBOARD MÓDULOS DE ALUMNOS ---

    public function moduloDashboardMostrar(Modulo $modulo) {
        return view('alumno.modulo.dashboard.dashboard', compact('modulo'));
    }
}
