@extends('layouts.app')
<div>
    <h1>{{ $test->nombre }}</h1>
    <p>{{ $test->descripcion }}</p>
    <hr>
    @if(isset($nota))
        <div>
            <h2>
                Tu nota final es: <strong>{{ $nota }} / 10</strong>
            </h2>
        </div>
    @endif
    <form action="#" method="POST">  
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

