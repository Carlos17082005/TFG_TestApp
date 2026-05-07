@extends('layouts.app')

@section('title', 'ajustes')

@section('content')
    <x-header />
    <x-errores />
    
    <form method="POST" action="{{ route('alumno.ajustes.abandonar', $modulo->id_modulo) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Abandonar módulo</button>
    </form>
@endsection