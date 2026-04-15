<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {
    
    // Iniciar sesión. Tanto como de profesor como de alumno

    public function login(Request $request) {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
 
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
 
            return redirect()->intended('/')->with('success', 'Bienvenido de vuelta');
        }
 
        return back()
            ->withErrors(['email' => 'No existe esta cuenta.'])
            ->onlyInput('email');
    }

    // Registrar al alumno

    public function register(Request $request) {
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellidos' => $validated['apellidos'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'rol' => 'alumno', 
        ]);

        Alumno::create([
            'id_usuario' => $usuario->id_usuario,
        ]);

        Auth::login($usuario);
 
        return redirect()->route('inicio.dashboardAlumno.mostrar');
    }

    // Cerrar sesión

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect('/')->with('success', 'Has cerrado sesión.');
    }
}