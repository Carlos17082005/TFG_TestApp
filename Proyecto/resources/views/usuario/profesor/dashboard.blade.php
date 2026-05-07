@extends('layouts.app')

@section('title', 'dashboard profesor')

@section('content')
    <x-header />
    <x-errores />
    
    <h1>Dashboard profesor {{ Auth::user()->nombre }}</h1>

    @if (Auth::user()->profesor->modulos->isEmpty())
        <!-- No tiene ningún módulo -->
        <p>¿Primera vez? Crea un módulo</p>
        <p><a href="{{ route('profesor.modulos.create') }}">Crear nuevo módulo</a></p>

    @else
        <!-- Tiene módulos pero ninguno seleccionado -->
        @if (!$moduloActual)
            <p>Selecciona un módulo</p>
        @endif

        <x-modulo-nav-profesor :moduloActual="$moduloActual" />
    @endif
@endsection