<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnoModuloController extends Controller
{
    // Muestra todos los módulos creados por los profesores

    public function index() {
        $alumno = Auth::user()->alumno;

        $modulos = Modulo::whereDoesntHave('alumnos', function ($query) use ($alumno) {
            $query->where('alumnos.id_alumno', $alumno->id_alumno);
        })->get();

        return view('usuario.alumno.modulo.matriculacion.seleccionarModulo', compact('modulos'));
    }

    // Muestra el formulario para introducir la clave de matriculación al módulo

    public function create(Modulo $modulo) {
        return view('usuario.alumno.modulo.matriculacion.matricularseModulo', compact('modulo'));
    }

    // Introduce al alumno en el módulo usando una tabla pivote

    public function store(Request $request, Modulo $modulo) {
        $alumno = Auth::user()->alumno;

        // Si la clave de matriculiación no es correcta

        if ($modulo->clave_matriculacion !== $request->clave_matriculacion) {
            return back()->withErrors(['clave_matriculacion' => 'Clave incorrecta']);
        }

        try {
            // En caso de que el alumno intente matricularse de nuevo se ignora el insert
            if (!($modulo->alumnos()->where('alumnos.id_alumno', $alumno->id_alumno)->exists())) {
                $alumno->modulos()->attach($modulo->id_modulo);
            }

            return redirect()->route('inicio.dashboardAlumno.mostrar', compact('modulo'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al matricularse, inténtalo de nuevo.']);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////
    // Historial
    public function historial(Modulo $modulo) {
        try {
            $alumno = Auth::user()->alumno;
            $puntuaciones = $modulo->puntuaciones()
                ->where('puntuaciones.id_alumno', $alumno->id_alumno)
                ->with(['test'])
                ->orderBy('fecha', 'desc')
                ->get();

            return view('usuario.historial', compact('puntuaciones', 'modulo'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'No se ha podido acceder al historial'. $e->getMessage()]);
        }
    }  

    /////////////////////////////////////////////////////////////////////////////////////
    // Ajustes
    public function ajustes(Modulo $modulo) {
        return view('usuario.alumno.ajustes', compact('modulo'));
    }

    // Abandonar el módulo
    public function abandonar(Modulo $modulo) {
        try {
            $alumno = Auth::user()->alumno;

            $alumno->modulos()->detach($modulo->id_modulo);

            return redirect()->route('inicio.dashboardAlumno.mostrar');
        } catch(\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
