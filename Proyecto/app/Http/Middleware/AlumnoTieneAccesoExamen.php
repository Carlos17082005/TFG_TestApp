<?php

namespace App\Http\Middleware;

use App\Models\Test;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlumnoTieneAccesoExamen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $test = $request->route('test');

        if(!$test instanceof Test) {
            $test = Test::findOrFail($test);
        }

        if ($test->tipo == 'examen') {
            $alumno = Auth::user()->alumno;

            $tieneAcceso = now() < $test->examen->fecha_apertura->addMinutes($test->examen->duracion);
            $hizoExamen = $alumno->puntuaciones()
                ->where('puntuaciones.id_test', $test->id_test)
                ->where('puntuaciones.id_alumno', $alumno->id_alumno)
                ->exists();

            if (!$tieneAcceso || $hizoExamen) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'No tienes acceso al examen']);
            }
        }

        return $next($request);
    }
}
