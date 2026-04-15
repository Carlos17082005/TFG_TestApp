<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ModuloPerteneceAlumno
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        $modulo = $request->route('modulo');

        if (!$modulo->alumnos->contains('id_alumno', $usuario->id_usuario)) {
            return redirect()
                ->route('inicio.dashboardAlumno.mostrar')
                ->with('error', 'No tienes acceso a este módulo.');
        }

        return $next($request);
    }
}
