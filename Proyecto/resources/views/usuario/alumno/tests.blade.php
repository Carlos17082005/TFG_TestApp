@extends('layouts.app')

@section('content')
    <x-header />
    <x-errores />
    
    <div>
        @php $hayTests = false; @endphp

        @foreach ($tests as $test)
            @php
                $mostrar = true;

                if ($test->tipo == 'examen') {
                    $alumno = Auth::user()->alumno;

                    $tieneAcceso = now() >= $test->examen->fecha_apertura 
                        && now() < $test->examen->fecha_apertura->addMinutes($test->examen->duracion);
                    
                    $hizoExamen = $alumno->puntuaciones()
                        ->where('id_test', $test->id_test)
                        ->exists();

                    $mostrar = $tieneAcceso && !$hizoExamen;
                }

                if ($mostrar) $hayTests = true;
            @endphp

            @if ($mostrar)
                <div>
                    <h3>{{ $test->nombre }}</h3>
                    <p>{{ $test->descripcion }}</p>
                    <a href="{{ route('alumno.tests.iniciar', [$modulo->id_modulo, $test->id_test]) }}">
                        <button>Realizar Test</button>
                    </a>
                </div>
            @endif
        @endforeach

        @if (!$hayTests)
            <p>No hay tests disponibles</p>
        @endif

        <br>
        <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}"><button type="button">Volver</button>
        </a>
    </div>
@endsection