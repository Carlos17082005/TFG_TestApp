@extends('layouts.app')

@section('title', 'Alumnos')

@push('styles')
<style>
    :root {
        /* Usamos el color de la base de datos */
        --color-modulo: {{ $modulo->color }};
        
        /* Opcional: Generar variantes con transparencia usando el mismo color */
        /* Si tu color es Hex (ej: #4F46E5), puedes añadir opacidad al final */
        --color-modulo-10: {{ $modulo->color }}1a; /* 10% de opacidad */
        --color-modulo-20: {{ $modulo->color }}33; /* 20% de opacidad */
        
        /* Para el hover, podrías simplemente usar el mismo o uno ligeramente distinto */
        --color-modulo-h: {{ $modulo->color }}; 
    }
</style>
@endpush

@section('content')
    <x-errores />

    <h1>Gestión de Alumnos</h1>

    <form id="form-accesos" action="{{ route('profesor.alumnos.update', $modulo->id_modulo) }}" method="POST">
        @csrf
        @method('PUT')
    </form>

    @foreach ($usuarios as $usuario)
        <form id="form-eliminar-{{ $usuario->id_usuario }}" action="{{ route('profesor.alumnos.destroy', [$modulo->id_modulo, $usuario->id_usuario]) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @if (!$usuarios->isEmpty())
        <div class="table-container">
            <table class="main-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th style="text-align: center;">Acceso al Módulo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->nombre }}</td>
                            <td>{{ $usuario->apellidos }}</td>
                            <td style="text-align: center;">
                                <input
                                    type="checkbox"
                                    name="alumnos_acceso[]"
                                    value="{{ $usuario->id_usuario }}"
                                    id="usuario-{{ $usuario->id_usuario }}"
                                    form="form-accesos"
                                    {{ in_array($usuario->id_usuario, old('alumnos_acceso', $alumnosConAcceso)) ? 'checked' : '' }}
                                >
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar este Alumno? \n\n ESTA ACCIÓN NO SE PUEDE DESHACER');"
                                     form="form-eliminar-{{ $usuario->id_usuario }}" class="btn btn-danger">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" form="form-accesos" class="btn btn-primary">
                Guardar Cambios de Acceso
            </button>
            <button onclick="history.back()" class="boton_cancelar btn btn-primary">
                <span class="volver_negrita">Cancelar</span>
            </button>
        </div>

    @else
        <div class="form-card" style="text-align: center; padding: 3rem;">
            <p style="margin-bottom: 1rem;">No tienes alumnos en este módulo</p>
        </div>
    @endif
@endsection