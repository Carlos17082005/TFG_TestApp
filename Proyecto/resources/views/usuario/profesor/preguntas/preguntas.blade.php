@extends('layouts.app')

@section('title', 'preguntas')

@section('content')
    <x-header />
    <x-errores />
    <h1>Listado de preguntas</h1>

    <p><a href="{{ route('profesor.preguntas.create', $modulo->id_modulo) }}"><button type="button">+ Crear pregunta</button></a></p>


    <div x-data="{
            busqueda: '',

            get parsed() {
                let tokens = this.normalizar(this.busqueda).split(/\s+/).filter(Boolean);
                let etiquetas = tokens.filter(t => t.startsWith(':')).map(t => t.slice(1));
                let tipo      = (tokens.find(t => t.startsWith('tipo:')) ?? '').slice(5);
                let texto     = tokens.filter(t => !t.startsWith(':') && !t.startsWith('tipo:')).join(' ');
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

            coincide(enunciado, etiquetas_pregunta, tipo_pregunta) {
                let { etiquetas, tipo, texto } = this.parsed;

                let enunciadoNorm = this.normalizar(enunciado);
                let etiquetasNorm = etiquetas_pregunta.map(e => this.normalizar(e));

                if (texto && !enunciadoNorm.includes(texto)) return false;
                if (tipo  && !tipo_pregunta.includes(tipo)) return false;
                if (etiquetas.length && !etiquetas.every(e => etiquetasNorm.some(ep => ep.includes(e)))) return false;

                return true;
            }
        }">
        <div>
            <label>Buscar pregunta:</label>
            <input type="search"
                x-model="busqueda"
                placeholder="Texto, :etiqueta, tipo:multiple ...">
            <br>
            <p>
                Puedes combinar: <i>:etiqueta</i> para filtrar por etiqueta (varias a la vez),
                <i>tipo:multiple</i> / <i>tipo:booleana</i> / <i>tipo:texto</i> / <i>tipo:conecta</i> para filtrar por tipo,
                y texto libre para buscar en el enunciado.
            </p>
        </div>

        <br><hr><br>

        @foreach ($preguntas as $pregunta)
            <div x-data='{
                        abierta: false,
                        enunciado: @json(strtolower($pregunta->contenido["enunciado"] ?? "")),
                        etiquetas: @json($pregunta->listaEtiquetas->pluck("nombre")->map(fn($n) => strtolower($n))->toArray()),
                        tipo: @json(strtolower($pregunta->tipo))
                    }'
                    x-show="coincide(enunciado, etiquetas, tipo)">

                <div @click="abierta = !abierta">        
                    <b>Pregunta:</b> {{ $pregunta->contenido['enunciado'] }}
                    <br><br>

                    Tipo: {{ $pregunta->tipo }}
                    <br>

                    Etiquetas: 
                    @forelse ($pregunta->listaEtiquetas as $etiqueta)
                        [{{ $etiqueta->nombre }}]
                    @empty
                        Ninguna
                    @endforelse
                </div>
                
                <div x-show="abierta" x-cloak>
                    <a href="{{ route('profesor.preguntas.edit', [$modulo->id_modulo, $pregunta->id_pregunta]) }}">
                        <button type="button">Editar</button>
                    </a>
                    <form action="{{ route('profesor.preguntas.destroy', [$modulo->id_modulo, $pregunta->id_pregunta]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que quieres eliminar esta pregunta?');">
                        @csrf
                        @method('DELETE') 
                        <button type="submit">Eliminar</button>
                    </form>
                </div>
                
                <br><hr><br>
            </div>     

        @endforeach
    </div>

    
@endsection