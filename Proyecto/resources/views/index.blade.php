@extends('layouts.app')

@section('title', 'inicio')

@section('content')

    <h1>Duolingo</h1>

    <p>Landing</p>

    <a href="{{ route('inicio.login.mostrar') }}">Iniciar sesión</a>
    <a href="{{ route('inicio.register.mostrar') }}">Registrarse</a>

@endsection