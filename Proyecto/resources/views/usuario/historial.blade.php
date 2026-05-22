@extends('layouts.app')

@section('title', 'Historial')

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

    <h1>Historial de Puntuaciones</h1>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin: 0; text-align: left;">Ajustes del Módulo</h1>
        <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}" class="btn btn-secondary">Volver al Panel</a>
    </div>
    <div x-data="{
            busqueda: '',
            get parsed() {
                let tokens = this.busqueda.trim().toLowerCase().split(/\s+/).filter(Boolean);
                let extraer = (prefijo) => (tokens.find(t => t.startsWith(prefijo + ':')) ?? '').slice(prefijo.length + 1);
                return {
                    texto:      tokens.filter(t => !t.includes(':')).join(' '),
                    nombre:     extraer('nombre'),
                    test:       extraer('test'),
                    tipo:       extraer('tipo'),
                    puntuacion: extraer('puntuacion'),
                    fecha:      extraer('fecha'),
                };
            },
            normalizar(texto) {
                if (!texto) return '';
                // Convertimos forzosamente a String para evitar que explote con los números de las notas
                return String(texto).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^\w\s]/g, '').trim();
            },
            coincide(nombre_completo, test, tipo, puntuacion, fecha) {
                let p = this.parsed;
                let n = this.normalizar(nombre_completo);
                let t = this.normalizar(test);
                
                let bTexto = this.normalizar(p.texto);

                if (bTexto && !n.includes(bTexto) && !t.includes(bTexto)) return false;
                if (p.nombre && !n.includes(this.normalizar(p.nombre))) return false;
                if (p.test && !t.includes(this.normalizar(p.test))) return false;
                if (p.tipo && !this.normalizar(tipo).includes(this.normalizar(p.tipo))) return false;
                if (p.puntuacion && !String(puntuacion).startsWith(p.puntuacion)) return false;
                if (p.fecha && !fecha.includes(p.fecha)) return false;
                
                return true;
            }
        }">

        <div class="form-card" style="margin-bottom: 2rem;">
            <div class="form-group">
                <label class="form-label">Filtrar historial</label>
                <input type="search" x-model="busqueda" class="form-input" placeholder="Ej: nombre:carlos test:unidad1 puntuacion:8">
            </div>
        </div>

        <div class="table-container">
            <table class="main-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Test</th>
                        <th>Tipo</th>
                        <th>Nota</th>
                        <th>Fecha</th>
                        <th>Duración</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($puntuaciones as $p)
                        @php
                            $nc    = $p->alumno->usuario->nombre . ' ' . $p->alumno->usuario->apellidos;
                            $punt  = number_format((float) $p->puntuacion, 2);
                            $fecha = $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('Y-m-d H:i') : 'N/A';
                        @endphp
                        <tr x-show="coincide(
                                {{ Js::from($nc) }},
                                {{ Js::from($p->test->nombre) }},
                                {{ Js::from($p->test->tipo) }},
                                {{ Js::from($punt) }},
                                {{ Js::from($fecha) }}
                            )">
                            <td style="font-weight: 500;">{{ $nc }}</td>
                            <td>{{ $p->test->nombre }}</td>
                            <td><span style="font-size: 0.8rem; opacity: 0.7;">{{ strtoupper($p->test->tipo) }}</span></td>
                            <td style="font-family: var(--mono); font-weight: 700; color: var(--color-modulo);">{{ $punt }}</td>
                            <td style="font-size: 0.8rem; color: var(--tx-3);">{{ $fecha }}</td>
                            <td>{{ gmdate('H:i:s', $p->duracion_segundos) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection