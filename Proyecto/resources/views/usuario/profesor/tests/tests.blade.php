@extends('layouts.app')

@section('title', 'Tests')

@push('styles')
<style>
    :root {
        --color-modulo: {{ $modulo->color }};
        --color-modulo-10: {{ $modulo->color }}1a;
        --color-modulo-20: {{ $modulo->color }}33;
        --color-modulo-h: {{ $modulo->color }}; 
    }
</style>
@endpush

@section('content')
    <x-errores />

    <h1>Listado de Tests</h1>

    <div style="text-align: right; margin-bottom: 2rem;">
        <a href="{{ route('profesor.tests.create', $modulo->id_modulo) }}">
            <button type="button" class="btn btn-primary">+ Crear nuevo test</button>
        </a>
    </div>

    <div x-data="{
            busqueda: '',
            normalizar(texto) {
                if (!texto) return '';
                return String(texto).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^\w\s]/g, '').trim();
            },
            get parsed() {
                let tokens = this.busqueda.trim().toLowerCase().split(/\s+/).filter(Boolean);
                // Extraemos el tipo si existe el prefijo tipo:
                let tipoRaw = (tokens.find(t => t.startsWith('tipo:')) ?? '').slice(5);
                // El resto es búsqueda por nombre
                let textoRaw = tokens.filter(t => !t.startsWith('tipo:')).join(' ');
                
                return {
                    tipo: this.normalizar(tipoRaw),
                    texto: this.normalizar(textoRaw)
                };
            },
            coincide(nombre, tipo) {
                let p = this.parsed;
                let nNorm = this.normalizar(nombre);
                let tNorm = this.normalizar(tipo);

                if (p.texto && !nNorm.includes(p.texto)) return false;
                if (p.tipo && !tNorm.includes(p.tipo)) return false;
                
                return true;
            }
        }">

        <div class="form-group" style="margin-bottom: 2rem;">
            <input type="search" x-model="busqueda" class="form-input" placeholder="Ej: Unidad 1 tipo:examen">
        </div>

        <div class="table-container">
            <table class="main-table">
                <thead>
                    <tr>
                        <th>Nombre del Test</th>
                        <th style="padding-left: 37px;">Tipo</th>
                        <th style="text-align: right; padding-right: 136px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tests as $test)
                        <tr x-show="coincide({{ Js::from($test->nombre) }}, {{ Js::from($test->tipo) }})">
                            <td style="font-weight: 600;">{{ $test->nombre }}</td>
                            <td>
                                <span style="text-transform: capitalize; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8rem; 
                                    {{ $test->tipo == 'examen' ? 'background: #fee2e2; color: #dc2626;' : 
                                    ($test->tipo == 'borrador' ? 'background: #fef3c7; color: #d97706;' : 
                                    'background: #d1fae5; color: #059669;') }}">
                                    {{ $test->tipo }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    @if($test->tipo !== 'borrador')
                                        <a href="{{ route('profesor.tests.iniciar', [$modulo->id_modulo, $test->id_test]) }}" class="btn btn-secondary">Probar</a>
                                    @endif
                                    <a href="{{ route('profesor.tests.edit', [$modulo->id_modulo, $test->id_test]) }}" class="btn btn-secondary">Editar</a>
                                    <form method="POST" action="{{ route('profesor.tests.destroy', [$modulo->id_modulo, $test->id_test]) }}" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar test?')">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 3rem; color: var(--tx-4);">
                                No tienes tests creados para este módulo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection