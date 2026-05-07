<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Models\Puntuacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfesorAlumnoController extends Controller
{
    public function index(Modulo $modulo) {
        $usuarios = $modulo->alumnos->pluck('usuario');

        $alumnosConAcceso = $modulo->alumnos()
            ->wherePivot('tiene_acceso', true)
            ->pluck('alumnos.id_alumno')
            ->toArray();

        return view('usuario.profesor.alumnos.alumnos', compact('usuarios', 'modulo', 'alumnosConAcceso'));
    }

    // ================
    // ==== EDITAR ====
    // ================
    
    public function update(Request $request, Modulo $modulo) {
        $alumnosConAcceso = $request->input('alumnos_acceso', []);

        try {
            DB::beginTransaction();

            foreach ($modulo->alumnos as $alumno) {
                $modulo->alumnos()->updateExistingPivot($alumno->id_alumno, [
                    'tiene_acceso' => in_array($alumno->id_alumno, $alumnosConAcceso)
                ]);
            }

            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'No se ha podido modificar los accesos']);
        }
        
    }

    // ==================
    // ==== ELIMINAR ====
    // ==================

    public function destroy(Modulo $modulo, Alumno $alumno) {
        try {
            $modulo->alumnos()->detach($alumno->id_alumno);
            return redirect()->back();
        } catch(\Exception $e) {
            return back()->withErrors(['error' => 'No se ha podido eliminar al alumno']);
        }
    }




    /////////////////////////////////////////////////////////////////////////////////////
    // Historial
    public function historial(Modulo $modulo) {
        try {
            $puntuaciones = $modulo->puntuaciones()
                ->with(['alumno.usuario', 'test'])
                ->orderBy('fecha', 'desc')
                ->get();

            return view('usuario.historial', compact('puntuaciones', 'modulo'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'No se ha podido acceder al historial'. $e->getMessage()]);
        }
    }   
}
