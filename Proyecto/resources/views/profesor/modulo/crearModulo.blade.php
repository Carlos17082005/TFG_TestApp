@extends('layouts.app')

@section('title', 'Crearmodulo')

@section('content')
    <x-header />
    
    <h1> Crear módulo </h1>

    <form method="POST" action="{{ route('profesor.crearModulo.crear') }}">
        @csrf

        <!-- Ciclo -->

        <p>
            <label>
                <input type="text"
                        name="ciclo"
                        placeholder="1DAW"
                        value="{{ old('ciclo') }}"
                        class="input input-bordered @error('ciclo') input-error @enderror"
                        required
                        autofocus>
                <span>Ciclo</span>
            </label>
            @error('ciclo')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Modulo -->

        <p>
            <label>
                <input type="text"
                        name="modulo"
                        placeholder="Programación"
                        value="{{ old('modulo') }}"
                        class="input input-bordered @error('modulo') input-error @enderror"
                        required>
                <span>Módulo</span>
            </label>
            @error('modulo')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Clave de matriculación del alumnado -->

        <p>
            <label>
                <input type="text"
                        name="clave_matriculacion"
                        placeholder="****"
                        value="{{ old('clave_matriculacion') }}"
                        class="input input-bordered @error('clave_matriculacion') input-error @enderror"
                        required>
                <span>Clave de matriculación del alumnado</span>
            </label>
            @error('clave_matriculacion')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Botón de submit -->

        <button type="submit">
            Crear módulo
        </button>
    </form>
@endsection