@extends('layouts.app')

@section('title', 'Mis Tests')

@push('styles')
<style>
    :root {
        --color-modulo: {{ $modulo->color }};
        --color-modulo-10: {{ $modulo->color }}1a;
        --color-modulo-20: {{ $modulo->color }}33;
        --color-modulo-h: {{ $modulo->color }}; 
    }

    /* Estilo para los títulos de cada sección (Disponibles, Completados, etc.) */
    .seccion-titulo {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--tx-2);
        margin-top: 2.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--color-modulo);
    }

    /* Estilo para tests que no se pueden clicar (grises) */
    .test-disabled {
        background-color: #f9fafb; 
        border-left: 4px solid #9ca3af !important; 
    }
    .test-disabled h3 {
        color: #4b5563;
    }
</style>
@endpush

@section('content')
    <x-errores />
    
    <div style="max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin: 0; text-align: left;">
                @if(isset($tipo) && $tipo === 'examen')
                    Mis Exámenes
                @elseif(isset($tipo) && $tipo === 'practica')
                    Mis Prácticas
                @else
                    Mis Tests
                @endif
            </h1>
            
            <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}" class="btn btn-secondary">
                Volver al Panel
            </a>
        </div>

        {{-- LÓGICA DE CLASIFICACIÓN DE TESTS --}}
        @php 
            $alumno = Auth::user()->alumno;
            $disponibles = [];
            $completados = [];
            $no_disponibles = [];

            foreach ($tests as $test) {
                // 1. ¿Ya lo ha hecho?
                $hizoExamen = $alumno->puntuaciones()->where('id_test', $test->id_test)->exists();
                
                if ($hizoExamen) {
                    $completados[] = $test;
                    continue; // Pasamos al siguiente test, ya no hay que comprobar fechas
                }

                // 2. Si no lo ha hecho, comprobamos las fechas (solo si es examen y tiene fechas)
                if ($test->tipo == 'examen' && $test->examen) {
                    $ahora = now();
                    $apertura = \Carbon\Carbon::parse($test->examen->fecha_apertura);
                    $cierre = \Carbon\Carbon::parse($test->examen->fecha_cierre);

                    if ($ahora < $apertura) {
                        // El test es en el futuro
                        $test->motivo_bloqueo = 'Se abre el ' . $apertura->format('d/m/Y H:i');
                        $no_disponibles[] = $test;
                    } elseif ($ahora > $cierre) {
                        // El test ya se cerró
                        $test->motivo_bloqueo = 'Cerrado el ' . $cierre->format('d/m/Y H:i');
                        $no_disponibles[] = $test;
                    } else {
                        // Está en fecha correcta
                        $disponibles[] = $test;
                    }
                } else {
                    // Si es práctica (y no está completada), siempre está disponible
                    $disponibles[] = $test;
                }
            }
        @endphp

        {{-- ========================================== --}}
        {{-- 1. SECCIÓN: TESTS DISPONIBLES              --}}
        {{-- ========================================== --}}
        <h2 class="seccion-titulo" style="margin-top: 0;">Disponibles</h2>
        <div style="display: grid; gap: 1.5rem;">
            @forelse ($disponibles as $test)
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
            @empty
                <div class="form-card" style="text-align: center; padding: 3rem 2rem;">
                    <p style="color: var(--tx-3); font-size: 1.1rem; margin: 0;">
                        No tienes {{ isset($tipo) && $tipo == 'examen' ? 'exámenes' : 'prácticas' }} disponibles para realizar.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- ========================================== --}}
        {{-- 2. SECCIÓN: TESTS NO DISPONIBLES (Fechas)  --}}
        {{-- ========================================== --}}
        @if (count($no_disponibles) > 0)
            <h2 class="seccion-titulo">No Disponibles</h2>
            <div style="display: grid; gap: 1.5rem;">
                @foreach ($no_disponibles as $test)
                    <div class="form-card test-disabled" style="padding: 1.5rem; margin-bottom: 0; display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <h3 style="margin: 0; font-size: 1.25rem;">{{ $test->nombre }}</h3>
                                <span style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 600; background: #e5e7eb; color: #4b5563;">
                                    {{ strtoupper($test->tipo) }}
                                </span>
                            </div>
                            <p style="color: var(--tx-2); margin: 0;">{{ $test->descripcion }}</p>
                        </div>
                        
                        <div style="text-align: right; margin-top: 0.5rem;">
                            {{-- Mostramos por qué no puede acceder --}}
                            <span class="btn btn-secondary" style="cursor: not-allowed; background-color: #e5e7eb; color: #6b7280; border-color: #d1d5db;">
                                <i class="ti ti-clock" style="margin-right: 5px;"></i> {{ $test->motivo_bloqueo }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- 3. SECCIÓN: TESTS COMPLETADOS              --}}
        {{-- ========================================== --}}
        @if (count($completados) > 0)
            <h2 class="seccion-titulo">Completados</h2>
            <div style="display: grid; gap: 1.5rem;">
                @foreach ($completados as $test)
                    <div class="form-card test-disabled" style="padding: 1.5rem; margin-bottom: 0; display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <h3 style="margin: 0; font-size: 1.25rem;">{{ $test->nombre }}</h3>
                                <span style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 600; background: #d1fae5; color: #065f46;">
                                    <i class="ti ti-check"></i> REALIZADO
                                </span>
                            </div>
                            <p style="color: var(--tx-2); margin: 0;">{{ $test->descripcion }}</p>
                        </div>
                        
                        <div style="text-align: right; margin-top: 0.5rem;">
                            <span class="btn btn-secondary" style="cursor: default; background-color: #d1fae5; color: #065f46; border-color: #a7f3d0; opacity: 1;">
                                Completado
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
    </div>
@endsection