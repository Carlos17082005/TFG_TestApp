@extends('layouts.app')
<div>
    <h1>{{ $test->nombre }}</h1>
    <p>{{ $test->descripcion }}</p>

    @if (Auth::user()->alumno && $test->tipo == 'examen' && !isset($estado))
        <p>Tiempo restante:<strong id="temporizador">-</strong></p>
    @endif

    <hr>
    @if(isset($nota))
        <div>
            <h2>
                Tu nota final es: <strong>{{ $nota }} / 10</strong>
            </h2>
        </div>
    @endif
    <form action="{{ Auth::user()->rol === 'profesor' 
        ? route('profesor.tests.corregir', [$modulo->id_modulo, $test->id_test])
        : route('alumno.tests.corregir', [$modulo->id_modulo, $test->id_test]) 
    }}" method="POST">  
        @csrf

        @foreach($test->preguntas as $index => $pregunta)
            @php
                $contenido = $pregunta->contenido;
                $numPregunta = $index + 1; 
            @endphp

            <div>
                <h3>{{ $numPregunta }}. {{ $contenido['enunciado'] }}</h3>

                @switch($pregunta->tipo)

                    @case('multiple')
                        @include('usuario.tests.partials._multiple', [
                            'id'      => $pregunta->id_pregunta,
                            'opciones'=> $contenido['opciones'],
                            'estado'  => $estado[$pregunta->id_pregunta] ?? null,
                        ])
                    @break

                    @case('booleana')
                        @include('usuario.tests.partials._booleana', [
                            'id'    => $pregunta->id_pregunta,
                            'estado'=> $estado[$pregunta->id_pregunta] ?? null,
                        ])
                    @break

                    @case('conecta')
                        @include('usuario.tests.partials._conecta', [
                            'id'      => $pregunta->id_pregunta,
                            'parejas' => $contenido['parejas'],
                            'mezclada'=> $contenido['columna_b_mezclada']
                                            ?? collect($contenido['parejas'])->pluck('b')->toArray(),
                            'estado'  => $estado[$pregunta->id_pregunta] ?? null,
                        ])
                    @break

                    @case('texto')
                        @include('usuario.tests.partials._texto', [
                            'id'    => $pregunta->id_pregunta,
                            'estado'=> $estado[$pregunta->id_pregunta] ?? null,
                        ])
                    @break

                    @default
                        <p>Tipo de pregunta desconocido: {{ $pregunta->tipo }}</p>

                @endswitch
            </div>
            
        @endforeach

        @if(!isset($estado))
            <button type="submit">Enviar Respuestas</button>
        @else
            @if(auth()->user()->rol === 'profesor') 
                <a href="{{ route('profesor.tests.index', [$modulo->id_modulo]) }}"><button type="button">Volver</button></a>
            @else
                {{-- dashboard del alumno --}}
                <a href="{{ route('inicio.dashboardAlumno.mostrar', [$modulo->id_modulo]) }}"><button type="button">Volver</button></a>
            @endif
        @endif
    </form>
</div>

@if (Auth::user()->alumno && $test->tipo == 'examen' && !isset($estado))
    @php $examen = $test->examen @endphp

    <script>
        var segundosRestantes = {{ now()->diffInSeconds($examen->fecha_apertura->addMinutes($examen->duracion)) }};

        if (segundosRestantes > 0) {
            var temporizador = setInterval(() => {
                var horas = String(Math.floor(segundosRestantes / 3600)).padStart(2, '0');
                var minutos = String(Math.floor((segundosRestantes % 3600) / 60)).padStart(2, '0');
                var segundos = String(Math.floor(segundosRestantes % 60)).padStart(2, '0');

                document.getElementById('temporizador').innerHTML=horas+':'+minutos+':'+segundos;
                segundosRestantes--;
                if (segundosRestantes < 0) {
                    clearInterval(temporizador);
                    document.querySelector('form').submit();
                }
            }, 1000);
        }
    </script>
@endif
