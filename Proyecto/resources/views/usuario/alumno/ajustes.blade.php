@extends('layouts.app')

@section('title', 'Ajustes')

@push('styles')
<style>
    :root {
        /* Usamos el color de la base de datos */
        --color-modulo: {{ $modulo->color }};
        
        /* Opcional: Generar variantes con transparencia usando el mismo color */
        /* Si tu color es Hex (ej: #4F46E5), puedes añadir opacidad al final */
        --color-modulo-10: {{ $modulo->color }}1a; /* 10% de opacidad */
        --color-modulo-20: {{ $modulo->color }}33; /* 20% de opacidad */
        
        /* Para el hover, podrías simplemente usar el mismo o uno ligeramente distinto */
        --color-modulo-h: {{ $modulo->color }}; 
    }
</style>
@endpush

@section('content')
    <x-errores />
    
    <div style="max-width: 600px; margin: 0 auto;">
        
        <h1 style="margin: 0; text-align: left;">Ajustes del Módulo</h1>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}" class="btn btn-secondary">Volver al Panel</a>
        </div>

        <div class="form-card" style="border-color: #FCA5A5; background-color: #FEF2F2;">
            <h3 style="color: var(--error); margin-bottom: 0.5rem; font-size: 1.2rem;">Zona Peligrosa</h3>
            <p style="color: var(--error); margin-bottom: 1.5rem; font-size: 0.95rem; opacity: 0.9;">
                Si abandonas este módulo, perderás el acceso inmediato a todos los ejercicios y exámenes que contiene. Esta acción te desvinculará del profesor para esta asignatura.
            </p>
            
            <form method="POST" action="{{ route('alumno.ajustes.abandonar', $modulo->id_modulo) }}" onsubmit="return confirm('¿Estás totalmente seguro de que deseas abandonar este módulo?');" style="margin: 0; padding: 0; background: transparent; border: none; box-shadow: none;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="width: 100%;">
                    Abandonar Módulo
                </button>
            </form>
        </div>
        
    </div>
@endsection