@extends('layouts.app')

@section('title', 'dashboard alumno')

@section('content')
    <x-header />
    
    Alumno

    <p><a href="{{ route('alumno.modulos.mostrar') }}">Modulos</a></p>
@endsection