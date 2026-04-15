@extends('layouts.app')

@section('title', 'Modulos')

@section('content')
    <x-header />
    
   @forelse ($modulos as $modulo)
       <p><a href="{{ route('alumno.moduloDashboard.mostrar', $modulo) }}">{{ $modulo->ciclo }} {{ $modulo->modulo }} {{ $modulo->profesor->usuario->nombre}} {{ $modulo->profesor->usuario->apellidos}}</a></p>
   @empty
       <p>No te has unido a ningún módulo :(</p>
   @endforelse

   <p><a href="{{ route('alumno.seleccionarModulo.mostrar') }}">Unirse a un nuevo módulo</a></p>
@endsection