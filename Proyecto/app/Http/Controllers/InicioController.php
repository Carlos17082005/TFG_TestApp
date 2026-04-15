<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InicioController
{
    public function indexMostrar() {
        if (Auth::check()) {
            return Auth::user()->esProfesor()
                ? view('profesor.dashboard')
                : view('alumno.dashboard');
        }

        return view('index');
    }

    public function loginMostrar() {
        return view('auth.login');
    }

    public function registerMostrar() {
        return view('auth.register');
    }

    public function dashboardProfesorMostrar() {
        return view('profesor.dashboard');
    }

    public function dashboardAlumnoMostrar() {
        return view('alumno.dashboard');
    }
}
