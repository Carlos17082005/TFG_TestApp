@extends('layouts.app')

@section('title', 'login')

@section('content')
    <h1>Iniciar sesión</h1>

    <form method="POST" action="{{ route('auth.login')}}">
        @csrf

        <!-- Email -->
        <p>
            <label>
                <input type="email"
                        name="email"
                        placeholder="ejemplo@email.com"
                        value="{{ old('email') }}"
                        class="input input-bordered @error('email') input-error @enderror"
                        required
                        autofocus>
                <span>Email</span>
            </label>
            @error('email')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Password -->
        <p>
            <label>
                <input type="password"
                        name="password"
                        placeholder="••••••••"
                        class="input input-bordered @error('password') input-error @enderror"
                        required>
                <span>Password</span>
            </label>
            @error('password')
                <span>{{ $message }}</span>
            @enderror
        </p>

        <!-- Mantener sesión -->
        <p>
            <label>
                <input type="checkbox"
                        name="remember"
                        class="checkbox">
                <span>Mantener sesión iniciada</span>
            </label>
        </p>

        <!-- Submit Button -->
        <button type="submit">
            Iniciar sesión
        </button>
    </form>
 
    <p>
        ¿No tienes una cuenta?
        <a href="{{ route('inicio.register.mostrar') }}">Registrarse</a>
    </p>
@endsection