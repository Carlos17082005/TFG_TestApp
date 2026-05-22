@extends('layouts.app')

@section('title', 'Matricularse')

@section('content')
    <x-errores />
    
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="text-align: center; margin-bottom: 1.5rem;">Matriculación</h1>
        
        <div class="form-card" style="text-align: center; background: var(--surface-2); border-style: dashed; padding: 2rem; margin-bottom: 2rem;">
            <p style="color: var(--tx-3); margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Estás a punto de unirte a:</p>
            <h2 style="color: var(--color-modulo); margin-bottom: 0.5rem; font-size: 1.5rem;">{{ $modulo->ciclo }} - {{ $modulo->modulo }}</h2>
            <p style="color: var(--tx-2); margin: 0;">Profesor: {{ $modulo->profesor->usuario->nombre }} {{ $modulo->profesor->usuario->apellidos }}</p>
        </div>

        <form method="POST" action="{{ route('alumno.matriculas.store', $modulo) }}" class="form-card">
            @csrf

            <div class="form-group">
                <label class="form-label">Clave de automatriculación</label>
                <input type="text"
                        name="clave_matriculacion"
                        class="form-input"
                        value="{{ old('clave_matriculacion') }}"
                        placeholder="Introduce la clave dada por tu profesor"
                        required
                        autofocus>
            </div>

            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.8rem; font-size: 1.05rem;">
                    Entrar al módulo
                </button>
                <a href="{{ route('alumno.matriculas.index') }}" class="btn btn-secondary" style="width: 100%;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection