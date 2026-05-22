@extends('layouts.app')

@section('title', isset($modulo) ? 'Editar Módulo' : 'Crear Módulo')

@section('content')
    <x-errores />
    
    <div style="max-width: 600px; margin: 0 auto;">
        <form method="POST" action="{{ isset($modulo) ? route('profesor.modulos.update', $modulo->id_modulo) : route('profesor.modulos.store') }}" class="form-card">
            @csrf
            @if(isset($modulo))
                @method('PUT')
                <h1 style="margin-bottom: 1.5rem; text-align: left;">Modificar Módulo</h1>
            @else
                <h1 style="margin-bottom: 1.5rem; text-align: left;">Crear Módulo</h1>
            @endif

            <div class="form-group">
                <label class="form-label">Ciclo</label>
                <input type="text"
                        name="ciclo"
                        placeholder="Ej: 1DAW"
                        value="{{ old('ciclo', $modulo->ciclo ?? '') }}"
                        class="form-input @error('ciclo') incorrect-bg @enderror"
                        required
                        autofocus>
                @error('ciclo')
                    <span style="color: var(--error); font-size: 0.8rem; font-weight: 500;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Módulo</label>
                <input type="text"
                        name="modulo"
                        placeholder="Ej: Programación"
                        value="{{ old('modulo', $modulo->modulo ?? '') }}"
                        class="form-input @error('modulo') incorrect-bg @enderror"
                        required>
                @error('modulo')
                    <span style="color: var(--error); font-size: 0.8rem; font-weight: 500;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Color del módulo</label>
                <input type="color" 
                        name="color"
                        value="{{ old('color', $modulo->color ?? '#4F46E5')}}"
                        class="form-input @error('color') incorrect-bg @enderror"
                        style="height: 45px; padding: 0.2rem 0.5rem; cursor: pointer;"
                        required>
            </div>

            <div class="form-group">
                <label class="form-label">Idioma</label>
                <select name="idioma" class="form-input">
                    <option value="es" {{ old('idioma', $modulo->idioma ?? 'es') === 'es' ? 'selected' : '' }}>Español</option>
                    <option value="en" {{ old('idioma', $modulo->idioma ?? 'es') === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Clave de matriculación del alumnado</label>
                <input type="text"
                        name="clave_matriculacion"
                        placeholder="****"
                        value="{{ old('clave_matriculacion', $modulo->clave_matriculacion ?? '') }}"
                        class="form-input @error('clave_matriculacion') incorrect-bg @enderror"
                        required>
                @error('clave_matriculacion')
                    <span style="color: var(--error); font-size: 0.8rem; font-weight: 500;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="font-size: 1.05rem; padding: 0.75rem 1.5rem;">
                    {{ isset($modulo) ? 'Actualizar Módulo' : 'Crear Módulo' }}
                </button>
                <a href="{{ route('inicio.dashboardProfesor.mostrar', $modulo->id_modulo ?? null) }}" class="boton_cancelar btn btn-secondary"><span class="volver_negrita">Cancelar</span></a>
            </div>
        </form>

        @if(isset($modulo))
            <div class="form-card" style="border-color: #FCA5A5; background-color: #FEF2F2; margin-top: 2rem;">
                <h3 style="color: var(--error); margin-bottom: 0.5rem; font-size: 1.2rem;">Zona Peligrosa</h3>
                <p style="color: var(--error); margin-bottom: 1.5rem; font-size: 0.95rem; opacity: 0.9;">
                    Una vez que elimines este módulo, se perderán todos los tests, preguntas y datos de los alumnos asociados a él. Esta acción no se puede deshacer.
                </p>
                
                <form method="POST" action="{{ route('profesor.modulos.destroy', $modulo->id_modulo) }}" onsubmit="return confirm('¿Estás absolutamente seguro de que quieres eliminar este módulo por completo?');" style="margin: 0; padding: 0; background: transparent; border: none; box-shadow: none;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        Eliminar Módulo Definitivamente
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection