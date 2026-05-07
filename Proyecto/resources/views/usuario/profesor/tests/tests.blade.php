@extends('layouts.app')

@section('title', 'tests')

@section('content')
    <x-header />
    <x-errores />

    <h1>Tests</h1>

    <div x-data="{
            busqueda: '',

            get parsed() {
                let tokens    = this.busqueda.trim().toLowerCase().split(/\s+/).filter(Boolean);
                let etiquetas = tokens.filter(t => t.startsWith(':')).map(t => this.normalizar(t.slice(1)));
                let tipo      = this.normalizar((tokens.find(t => t.startsWith('tipo:')) ?? '').slice(5));
                let texto     = this.normalizar(tokens.filter(t => !t.startsWith(':') && !t.startsWith('tipo:')).join(' '));
                return { etiquetas, tipo, texto };
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

            coincide(nombre, tipo) {
                let { tipo: bTipo, texto } = this.parsed;
                if (texto && !nombre.includes(texto)) return false;
                if (bTipo && !tipo.includes(bTipo))   return false;
                return true;
            }
        }">

        <label>Buscar test:</label>
        <input type="search"
            x-model="busqueda"
            placeholder="Nombre del test, tipo:examen, tipo:practica ...">

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tests as $test)
                    <tr x-show="coincide(
                            normalizar({{ Js::from(strtolower($test->nombre)) }}),
                            normalizar({{ Js::from(strtolower($test->tipo)) }})
                        )">
                        <td>{{ $test->nombre }}</td>
                        <td>
                            <a href="{{ route('profesor.tests.edit', [$modulo->id_modulo, $test->id_test]) }}"><button type="button">Editar</button></a>
                            <form method="POST" action="{{ route('profesor.tests.destroy', [$modulo->id_modulo, $test->id_test]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Eliminar</button>
                            </form>
                            <a href="{{ route('profesor.tests.iniciar', [$modulo->id_modulo, $test->id_test]) }}"><button>Probar</button></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>No tienes tests...</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

<p><a href="{{ route('profesor.tests.create', $modulo->id_modulo) }}"><button type="button">+ Crear test</button></a></p>

@endsection