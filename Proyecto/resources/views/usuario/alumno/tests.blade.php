@extends('layouts.app')

@section('content')
    <x-header />
    <x-errores />
    
    <div>
        @forelse ($tests as $test)
            <div>
                <h3>{{ $test->nombre }}</h3>
                <p>{{ $test->descripcion }}</p>
                <a href="{{ route('tests.iniciar', [$modulo->id_modulo, $test->id_test]) }}"><button>Realizar Test</button></a>
            </div>
        @empty
            <div>
                <p>No hay tests disponibles</p>
            </div>
        @endforelse

        <br>
        <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}"><button type="button">Volver</button>
        </a>
    </div>
@endsection