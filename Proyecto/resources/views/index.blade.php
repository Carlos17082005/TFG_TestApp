@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div style="max-width: 700px; margin: 4rem auto; text-align: center;">
        
        <div class="form-card" style="padding: 4rem 2rem; border-top: 6px solid var(--color-modulo); box-shadow: var(--shadow-lg);">
            
            <h1 style="font-size: 3rem; color: var(--color-modulo); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                TestApp
            </h1>
            
            <p style="font-size: 1.15rem; color: var(--tx-2); margin-bottom: 2.5rem;">
                La plataforma definitiva para crear, gestionar y realizar tests interactivos.
            </p>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('inicio.login.mostrar') }}" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.8rem 2.5rem;">
                    Iniciar sesión
                </a>
                <a href="{{ route('inicio.register.mostrar') }}" class="btn btn-secondary" style="font-size: 1.1rem; padding: 0.8rem 2.5rem;">
                    Crear una cuenta
                </a>
            </div>
            
        </div>
        
    </div>
@endsection