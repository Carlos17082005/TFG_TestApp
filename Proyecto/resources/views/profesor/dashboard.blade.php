@extends('layouts.app')

@section('title', 'dashboard profesor')

@section('content')
    <x-header />
    
    Dashboard profesor {{ Auth::user()->nombre }}

    @if (session('error'))
        <div>
            {{ session('error') }}
        </div>
    @endif

    <h1>Modulos</h1>

    @forelse (Auth::user()->profesor->modulos as $modulo)

        <a href="{{ route('profesor.modulos.mostrar', $modulo->id_modulo) }}">
            {{ $modulo->id_modulo }}
            {{ $modulo->ciclo }}
            {{ $modulo->modulo }}
        </a>

    @empty
        <p>No tiene modulos</p>
    @endforelse

    <p><a href="{{ route('profesor.crearModulo.mostrar') }}">Crear nuevo modulo</a></p>
@endsection