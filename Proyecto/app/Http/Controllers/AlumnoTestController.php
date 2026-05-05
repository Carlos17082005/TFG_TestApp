<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;

class AlumnoTestController extends Controller
{
    public function examenes(Modulo $modulo) {
        $tests = $modulo->tests()->where('tipo', 'examen')->get();
        return view('usuario.alumno.tests', ['modulo' => $modulo, 'tests'  => $tests]);
    }

    public function practicas(Modulo $modulo) {
        $tests = $modulo->tests()->where('tipo', 'practica')->get();
        return view('usuario.alumno.tests', ['modulo' => $modulo, 'tests'  => $tests]);
    }
}