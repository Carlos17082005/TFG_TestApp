@extends('layouts.app')

@section('title', 'dashboard alumno')

@section('content')
    <x-header />
    <x-errores />
    
    <p>Dashboard alumno {{ Auth::user()->nombre }}</p>

    @if (session('error'))
        <div>
            {{ session('error') }}
        </div>
    @endif

    @if (!$moduloActual)
        
        <p>¿Primera vez? Únete a un módulo</p>
        <p><a href="{{ route('alumno.matriculas.index') }}">Unirse a un nuevo modulo</a></p>

    @else
        <select onchange="if(this.value) location = this.value;">
            <option value="">-- Selecciona un módulo --</option>

            @foreach (Auth::user()->alumno->modulos as $modulo)
                <option value="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}"
                    {{ $moduloActual->id_modulo === $modulo->id_modulo ? 'selected' : '' }}
                >
                    {{ $modulo->ciclo }} {{ $modulo->modulo }} {{$modulo->profesor->usuario->nombre}} {{$modulo->profesor->usuario->apellidos}}
                </option>
            @endforeach

            <option value="{{ route('alumno.matriculas.index') }}">+ Unirse a un nuevo módulo</option>
        </select> 

        <p>{{$moduloActual->ciclo}} {{$moduloActual->modulo}} {{$moduloActual->profesor->usuario->nombre}} {{$moduloActual->profesor->usuario->apellidos}}</p>
    @endif

    <!---------------------------------------------------------------------------------------------------------------------------------------------------------------->
    <!-- test -->
    <div>
        <p><a href="{{ route('alumno.tests.examen', $moduloActual->id_modulo) }}">Examenes</a></p>
        <p><a href="{{ route('alumno.tests.practica', $moduloActual->id_modulo) }}">Ejercicios</a></p>
    </div>


@endsection