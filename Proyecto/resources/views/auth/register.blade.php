@extends('layouts.app')

@section('title', 'register')

@section('content')

    <h1>Registrarse</h1>

    <form method="POST" action="{{ route('auth.register') }}">
        @csrf

        <!-- Nombre -->
        <p>
            <label>
                <span>Nombre</span>
                <input type="text"
                        name="nombre"
                        placeholder="Jane"
                        value="{{ old('nombre') }}"
                        class="input input-bordered @error('nombre') input-error @enderror"
                        required>
            </label>

            @error('name')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Apellidos -->
        <p>
            <label>
                <span>Apellidos</span>
                <input type="text"
                        name="apellidos"
                        placeholder="Doe"
                        value="{{ old('apellidos') }}"
                        class="input input-bordered @error('nombre') input-error @enderror"
                        required>
            </label>

            @error('apellidos')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Email -->
        <p>
            <label>
                <span>Email</span>
                <input type="email"
                        name="email"
                        placeholder="alumno@email.com"
                        value="{{ old('email') }}"
                        class="input input-bordered @error('email') input-error @enderror"
                        required>
            </label>
            @error('email')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Password -->
        <p>
            <label>
                <span>Contraseña</span>
                <input type="password"
                        name="password"
                        placeholder="••••••••"
                        class="input input-bordered @error('password') input-error @enderror"
                        required>
            </label>

            @error('password')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Password confirmación -->
        <p>
            <label>
                <span>Confirma contraseña</span>
                <input type="password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        required>
            </label>
        </p>

        <!-- Submit Button -->
        <button type="submit">
            Registrarse
        </button>
    </form>

    <p>
        ¿Ya tienes una cuenta?
        <a href="{{ route('inicio.login.mostrar') }}">Iniciar sesión</a>
    </p>
@endsection