<?php

namespace App\Http\Controllers;

use App\Mail\RegistroAlumno;
use App\Models\Alumno;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller {
    
    // Iniciar sesión. Tanto como de profesor como de alumno

    public function login(Request $request) {

        // En el caso de que ya haya iniciado sesión

        if (Auth::check()) {
            return redirect()->intended('/')->with('success', 'Bienvenido de vuelta');
        }

        // Se comprueba las credenciales
        
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

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'rol' => 'alumno', 
            ]);

            Alumno::create([
                'id_alumno' => $usuario->id_usuario,
            ]);

            Auth::login($usuario);

            DB::commit();
            
            Mail::to($usuario->email)->send(new RegistroAlumno($usuario));

            return redirect()->route('inicio.dashboardAlumno.mostrar');

        } catch (\Exception $e){
            DB::rollBack();
            
            return back()->withErrors(['error' => 'Error al crear la cuenta, inténtalo de nuevo.']);
        }
 
    }

    // Cerrar sesión

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect('/')->with('success', 'Has cerrado sesión.');
    }
}