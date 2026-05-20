<?php

namespace App\Services;

use App\Models\Modulo;

class InicioService
{
    

    public function AccesoModuloAlumno($moduloActual, $alumno, $usuario)
    {
        // Verificar que el alumno tiene acceso al módulo
        $tieneAcceso = false;

        if ($moduloActual) {
            $tieneAcceso = $moduloActual->alumnos()
                    ->wherePivot('id_alumno', $alumno->id_alumno)
                    ->wherePivot('tiene_acceso', 1)
                    ->exists();
        }
        
        if ($tieneAcceso) {
            // Se guarda el último módulo visitado
            if ($moduloActual) {
                $usuario->id_ultimo_modulo_visitado = $moduloActual->id_modulo;
                $usuario->save();
            }
        } else {
            $moduloActual = null;
        }

        return $moduloActual;
    }
}
