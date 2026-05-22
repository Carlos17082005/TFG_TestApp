@extends('layouts.app')

@section('title', 'Mis Tests')

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

    .seccion-completados {
        margin-top: 50px;
    }

    .seccion-completados-titulo {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--tx-2);
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--color-modulo);
    }

    .test-completado {
        filter: grayscale(95%);
    }
</style>
@endpush

@section('content')
    <x-errores />
    
    <div style="max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin: 0; text-align: left;">
                @if(isset($tipo) && $tipo === 'examen')
                    Exámenes Disponibles
                @elseif(isset($tipo) && $tipo === 'practica')
                    Prácticas Disponibles
                @else
                    Tests Disponibles
                @endif
            </h1>
            
            <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}" class="btn btn-secondary">
                Volver al Panel
            </a>
        </div>

        @php $hayTests = false; @endphp
        @php $hayCompletados = false; @endphp

        <div style="display: grid; gap: 1.5rem;">
            @foreach ($tests as $test)
                @php
                    $mostrar = true;

                    if ($test->tipo == 'examen') {
                        $alumno = Auth::user()->alumno;

                        $tieneAcceso = $test->examen 
                            && now() >= $test->examen->fecha_apertura 
                            && now() <= $test->examen->fecha_cierre;
                        
                        $hizoExamen = $alumno->puntuaciones()
                            ->where('id_test', $test->id_test)
                            ->exists();

                        $mostrar = $tieneAcceso && !$hizoExamen;
                    }

                    if ($mostrar) $hayTests = true;
                    else $hayCompletados = true;
                @endphp

                @if ($mostrar)
                    <div class="form-card" style="padding: 1.5rem; margin-bottom: 0; display: flex; flex-direction: column; gap: 1rem; border-left: 4px solid var(--color-modulo);">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <h3 style="margin: 0; font-size: 1.25rem;">{{ $test->nombre }}</h3>
                                <span style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 600; {{ $test->tipo == 'examen' ? 'background: #fee2e2; color: #dc2626;' : 'background: #d1fae5; color: #059669;' }}">
                                    {{ strtoupper($test->tipo) }}
                                </span>
                            </div>
                            <p style="color: var(--tx-2); margin: 0;">{{ $test->descripcion }}</p>
                        </div>
                        
                        <div style="text-align: right; margin-top: 0.5rem;">
                            <a href="{{ route('alumno.tests.iniciar', [$modulo->id_modulo, $test->id_test]) }}" class="btn btn-primary">
                                Realizar Test
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach

            @if (!$hayTests)
                <div class="form-card" style="text-align: center; padding: 3rem 2rem;">
                    <p style="color: var(--tx-3); font-size: 1.1rem; margin: 0;">No tienes tests disponibles en este momento.</p>
                </div>
            @endif
        </div>

        @if ($hayCompletados)
            <div class="seccion-completados">
                <h2 class="seccion-completados-titulo">Exámenes Completados</h2>
                <div style="display: grid; gap: 1.5rem;">
                    @foreach ($tests as $test)
                        @php
                            $mostrar = true;

                            if ($test->tipo == 'examen') {
                                $alumno = Auth::user()->alumno;

                                $tieneAcceso = $test->examen 
                                    && now() >= $test->examen->fecha_apertura 
                                    && now() <= $test->examen->fecha_cierre;
                                
                                $hizoExamen = $alumno->puntuaciones()
                                    ->where('id_test', $test->id_test)
                                    ->exists();

                                $mostrar = $tieneAcceso && !$hizoExamen;
                            }
                        @endphp

                        @if (!$mostrar)
                            <div class="form-card test-completado" style="background-color: #d4d4d4; padding: 1.5rem; margin-bottom: 0; display: flex; flex-direction: column; gap: 1rem; border-left: 4px solid var(--color-modulo);">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                        <h3 style="margin: 0; font-size: 1.25rem;">{{ $test->nombre }}</h3>
                                        <span style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 600; {{ $test->tipo == 'examen' ? 'background: #fee2e2; color: #dc2626;' : 'background: #d1fae5; color: #059669;' }}">
                                            {{ strtoupper($test->tipo) }}
                                        </span>
                                    </div>
                                    <p style="color: var(--tx-2); margin: 0;">{{ $test->descripcion }}</p>
                                </div>
                                
                                <div style="text-align: right; margin-top: 0.5rem;">
                                    <span class="btn btn-secondary" style="cursor: default; background-color: #e9e9e9;">Completado</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        </div>
    </div>
@endsection