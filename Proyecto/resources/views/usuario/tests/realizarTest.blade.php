@extends('layouts.app')

@section('title', 'Realizando Test')

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
    /* inputs con color del modulo */
    input:checked{
        accent-color: var(--color-modulo);
    }
</style>
@endpush

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem;">{{ $test->nombre }}</h1>
        <p style="font-size: 1.1rem; color: var(--tx-2);">{{ $test->descripcion }}</p>

        @if (Auth::user()->alumno && $test->tipo == 'examen' && !isset($estado))
            <p style="justify-content: center;margin-top: 1rem;position: fixed;
            border: 2px solid var(--color-modulo);background: var(--color-modulo-20);
            border-radius: 8px;top: 55px;right: 10px; 
            font-size: larger;  padding: 1px 5px 0px 5px;">
                <strong id="temporizador">Cargando...</strong>
            </p>
        @endif
    </div>

    @if(isset($nota))
        <div style="margin-bottom: 2rem;">
            <h2>
                Tu nota final es: <strong>{{ $nota }} / 10</strong>
            </h2>
        </div>
    @endif

    <form id="form-test" action="{{ Auth::user()->rol === 'profesor' ? route('profesor.tests.corregir', [$modulo->id_modulo, $test->id_test]) : route('alumno.tests.corregir', [$modulo->id_modulo, $test->id_test]) }}" method="POST" style="background: transparent; border: none; box-shadow: none; padding: 0;">  
        @csrf

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($test->preguntas as $index => $pregunta)
                @php
                    $contenido = $pregunta->contenido;
                    $numPregunta = $index + 1; 
                @endphp

                <div class="form-card" style="padding: 1.5rem; margin-bottom: 0;">
                    <h3 style="font-size: 1.1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; margin-bottom: 1rem;">
                        <span style="color: var(--color-modulo);">{{ $numPregunta }}.</span> {{ $contenido['enunciado'] }}
                    </h3>

                    <div style="padding-left: 0.5rem;">
                        @switch($pregunta->tipo)
                            @case('multiple')
                                @include('usuario.tests.partials._multiple', ['id' => $pregunta->id_pregunta, 'opciones'=> $contenido['opciones'], 'estado' => $estado[$pregunta->id_pregunta] ?? null])
                            @break
                            @case('booleana')
                                @include('usuario.tests.partials._booleana', ['id' => $pregunta->id_pregunta, 'estado'=> $estado[$pregunta->id_pregunta] ?? null])
                            @break
                            @case('conecta')
                                @include('usuario.tests.partials._conecta', ['id' => $pregunta->id_pregunta, 'parejas' => $contenido['parejas'], 'mezclada'=> $contenido['columna_b_mezclada'] ?? collect($contenido['parejas'])->pluck('b')->toArray(), 'estado' => $estado[$pregunta->id_pregunta] ?? null])
                            @break
                            @case('texto')
                                @include('usuario.tests.partials._texto', ['id' => $pregunta->id_pregunta, 'estado'=> $estado[$pregunta->id_pregunta] ?? null])
                            @break
                        @endswitch
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem; text-align: right;">
            @if(!isset($estado))
                <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.8rem 2rem;">Enviar Respuestas</button>
            @else
                @if(auth()->user()->rol === 'profesor') 
                    <a href="{{ route('profesor.tests.index', [$modulo->id_modulo]) }}" class="btn btn-secondary">Volver a Tests</a>
                @else
                    <a href="{{ route('inicio.dashboardAlumno.mostrar', [$modulo->id_modulo]) }}" class="btn btn-secondary">Volver al Dashboard</a>
                @endif
            @endif
        </div>
    </form>
</div>

@if (Auth::user()->alumno && $test->tipo == 'examen' && !isset($estado))
    @php
        $segundosHastaCierre = now()->diffInSeconds($test->examen->fecha_cierre);
        $segundosPorDuracion = $test->examen->duracion * 60;
        $segundosRestantes = min($segundosHastaCierre, $segundosPorDuracion);
    @endphp
    <script>
        var segundosRestantes = {{ $segundosRestantes }};
        if (segundosRestantes > 0) {
            var temporizador = setInterval(() => {
                var horas = String(Math.floor(segundosRestantes / 3600)).padStart(2, '0');
                var minutos = String(Math.floor((segundosRestantes % 3600) / 60)).padStart(2, '0');
                var segundos = String(Math.floor(segundosRestantes % 60)).padStart(2, '0');
                document.getElementById('temporizador').innerHTML=horas+':'+minutos+':'+segundos;
                segundosRestantes--;
                if (segundosRestantes < 0) {
                    clearInterval(temporizador);
                    document.getElementById('form-test').submit();
                }
            }, 1000);
        }
    </script>
@endif
@endsection