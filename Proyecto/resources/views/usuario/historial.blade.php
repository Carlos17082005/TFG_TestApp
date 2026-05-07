@extends('layouts.app')

@section('title', 'Historial')

@section('content')
    <x-header />
    <x-errores />

    <h1>Historial de puntuaciones</h1>

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
                return texto
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^\w\s]/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
            },

            coincide(nombre_completo, test, tipo, puntuacion, fecha) {
                let { texto, nombre, test: bTest, tipo: bTipo, puntuacion: bPunt, fecha: bFecha } = this.parsed;

                let n  = this.normalizar(nombre_completo);
                let t  = this.normalizar(test);
                let ti = this.normalizar(tipo);

                if (texto  && !n.includes(this.normalizar(texto)) && !t.includes(this.normalizar(texto))) return false;
                if (nombre && !n.includes(this.normalizar(nombre))) return false;
                if (bTest  && !t.includes(this.normalizar(bTest)))  return false;
                if (bTipo  && !ti.includes(this.normalizar(bTipo))) return false;
                if (bPunt  && !String(puntuacion).startsWith(bPunt)) return false;
                if (bFecha && !fecha.includes(bFecha))               return false;

                return true;
            }
        }">

        <div>
            <label>Buscar:</label>
            <input type="search"
                   x-model="busqueda"
                   placeholder="Texto libre, nombre:x, test:x, tipo:x, puntuacion:x, fecha:x ...">
            <br>
            <p>
                Búsqueda libre busca por nombre del alumno y nombre del test. Puedes combinar:
                <i>nombre:carlos</i>, <i>test:anatomia</i>, <i>tipo:examen</i>, <i>puntuacion:8</i>, <i>fecha:2026-05</i>
            </p>
        </div>

        <br><hr><br>

        <table>
            <thead>
                <tr>
                    <th><b>Nombre y apellidos</b></th>
                    <th><b>Test</b></th>
                    <th><b>Tipo</b></th>
                    <th><b>Puntuación</b></th>
                    <th><b>Fecha</b></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($puntuaciones as $p)
                    @php
                        $nc    = strtolower($p->alumno->usuario->nombre . ' ' . $p->alumno->usuario->apellidos);
                        $test  = strtolower($p->test->nombre);
                        $tipo  = strtolower($p->test->tipo);
                        $punt  = number_format((float) $p->puntuacion, 2);
                        $fecha = $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('Y-m-d H:i') : '';
                    @endphp

                    <tr x-show="coincide(
                            {{ Js::from($nc) }},
                            {{ Js::from($test) }},
                            {{ Js::from($tipo) }},
                            {{ Js::from($punt) }},
                            {{ Js::from($fecha) }}
                        )">
                        <td>{{ $p->alumno->usuario->nombre }} {{ $p->alumno->usuario->apellidos }}</td>
                        <td>{{ $p->test->nombre }}</td>
                        <td>{{ $p->test->tipo }}</td>
                        <td>{{ $p->puntuacion }}</td>
                        <td>{{ $fecha }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <style>
        table, tr, td, th {
            border: 2px solid black; 
            border-collapse: collapse;
            padding-inline: 20px;
        }
    </style>
@endsection
