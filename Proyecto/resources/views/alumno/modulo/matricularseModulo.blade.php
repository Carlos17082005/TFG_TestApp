@extends('layouts.app')

@section('title', 'Matricularse')

@section('content')
    <x-header />
    
    <h1>Matricularse</h1>
    
    <h2>{{ $modulo->ciclo }} {{ $modulo->modulo }} {{$modulo->profesor->usuario->nombre}} {{$modulo->profesor->usuario->apellidos}}</h2>

    <form method="POST" action="{{ route('alumnos.matricularseModulo.entrar', $modulo) }}">
        @csrf

        <!-- Clave matriculación -->

        <p>
            <label>
                <input type="text"
                        name="clave_matriculacion"
                        required
                        autofocus>
                <span>Clave de automatriculación</span>
            </label>
        </p>

        <!-- Submit Button -->
        
        <button type="submit">Entrar</button>
    </form>

@endsection