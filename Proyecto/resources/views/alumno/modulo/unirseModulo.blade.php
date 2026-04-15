@extends('layouts.app')

@section('title', 'Unirse modulo')

@section('content')
    <x-header />
    
    <h1>Unirse a un modulo</h1>

    @foreach ($modulos as $modulo)
        <p><a href="{{ route('alumnos.matricularseModulo.mostrar', $modulo) }}">{{$modulo->ciclo}} {{$modulo->modulo}} {{$modulo->profesor->usuario->nombre}} {{$modulo->profesor->usuario->apellidos}}</a></p>
    @endforeach

@endsection