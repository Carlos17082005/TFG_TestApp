@extends('layouts.app')

@section('title', 'Dashboard Profesor')

@push('styles')
    @if(isset($moduloActual) && $moduloActual)
    <style>
        :root {
            /* Definimos el color principal desde la base de datos */
            --color-modulo: {{ $moduloActual->color }};
            
            /* Variantes con transparencia (agregando opacidad en Hex) */
            --color-modulo-10: {{ $moduloActual->color }}1a; /* 10% opacidad */
            --color-modulo-20: {{ $moduloActual->color }}33; /* 20% opacidad */
            
            /* Color para estados hover */
            --color-modulo-h: {{ $moduloActual->color }}; 
        }
    </style>
    @endif
@endpush

@section('content')
    <x-errores />
    
    <div class="dashboard-wrap">
        <div class="form-card" style="margin-bottom: 2rem; border-left: 4px solid var(--color-modulo); padding: 1.5rem 2rem;">
            <h1 style="text-align: left; margin-bottom: 0.5rem; font-size: 1.8rem;">¡Hola, {{ Auth::user()->nombre }}! 👋</h1>
            <p style="color: var(--tx-2); font-size: 1.1rem; margin: 0;">Bienvenido a tu panel de control de profesor.</p>
        </div>

        @if (Auth::user()->profesor->modulos->isEmpty())
            <div class="form-card" style="text-align: center; padding: 4rem 2rem;">
                <h2 style="margin-bottom: 1rem; color: var(--tx-1);">¿Primera vez por aquí?</h2>
                <p style="color: var(--tx-3); margin-bottom: 2rem; font-size: 1.1rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                    Para empezar a crear tests y gestionar a tus alumnos, primero necesitas crear un módulo de aprendizaje.
                </p>
                <a href="{{ route('profesor.modulos.create') }}" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.8rem 2rem;">
                    + Crear mi primer módulo
                </a>
            </div>

        @else
            <x-modulo-nav-profesor :moduloActual="$moduloActual" />

            @if (!$moduloActual)
                <div class="form-card" style="text-align: center; padding: 3rem 2rem; background: var(--surface-2); border-style: dashed;">
                    <h2 style="color: var(--tx-2); margin-bottom: 0.5rem; font-size: 1.4rem;">Panel de Módulos</h2>
                    <p style="color: var(--tx-3); margin: 0;">Selecciona un módulo en el menú superior para empezar a trabajar.</p>
                </div>
            @endif
        @endif
    </div>
@endsection