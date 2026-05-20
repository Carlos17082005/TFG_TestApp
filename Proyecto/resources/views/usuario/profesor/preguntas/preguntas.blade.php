@extends('layouts.app')

@section('title', 'Preguntas')

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
    <h1>Banco de Preguntas</h1>

    <div style="text-align: right; margin-bottom: 2rem;">
        <a href="{{ route('profesor.preguntas.create', $modulo->id_modulo) }}">
            <button type="button" class="btn btn-primary">+ Crear nueva pregunta</button>
        </a>
    </div>

    <div x-data="{
            busqueda: '',
            get parsed() {
                // Separamos por espacios SIN normalizar todavía para no perder los ':'
                let tokens = this.busqueda.trim().toLowerCase().split(/\s+/).filter(Boolean);
                
                let etiquetas = tokens.filter(t => t.startsWith(':')).map(t => this.normalizar(t.slice(1)));
                let tipo      = this.normalizar((tokens.find(t => t.startsWith('tipo:')) ?? '').slice(5));
                let texto     = this.normalizar(tokens.filter(t => !t.startsWith(':') && !t.startsWith('tipo:')).join(' '));
                
                return { etiquetas, tipo, texto };
            },
            normalizar(texto) {
                if (!texto) return '';
                return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^\w\s]/g, '').replace(/\s+/g, ' ').trim();
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
        
        <div class="form-card" style="margin-bottom: 2rem;">
            <div class="form-group">
                <label class="form-label">Buscador avanzado</label>
                <input type="search" x-model="busqueda" class="form-input" placeholder="Ej: capital :geografía tipo:multiple">
                <p style="font-size: 0.8rem; color: var(--tx-3); margin-top: 0.5rem;">
                    Usa <strong>:etiqueta</strong> para filtrar por tema y <strong>tipo:nombre</strong> para el formato.
                </p>
            </div>
        </div>

        @foreach ($preguntas as $pregunta)
            <div class="form-card" 
                 style="padding: 1.25rem; margin-bottom: 1rem;"
                 x-data='{
                    abierta: false,
                    enunciado: @json(strtolower($pregunta->contenido["enunciado"] ?? "")),
                    etiquetas: @json($pregunta->listaEtiquetas->pluck("nombre")->map(fn($n) => strtolower($n))->toArray()),
                    tipo: @json(strtolower($pregunta->tipo))
                 }'
                 x-show="coincide(enunciado, etiquetas, tipo)">

                <div @click="abierta = !abierta" style="cursor: pointer; display: flex; justify-content: space-between; align-items: start;">        
                    <div>
                        <strong style="color: var(--color-modulo);">{{ ucfirst($pregunta->tipo) }}</strong>
                        <p style="margin: 0.5rem 0; font-size: 1.1rem; color: var(--tx-1);">{{ $pregunta->contenido['enunciado'] }}</p>
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                            @forelse ($pregunta->listaEtiquetas as $etiqueta)
                                <span style="font-size: 0.7rem; background: var(--color-modulo-10); color: var(--color-modulo); padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 600;">
                                    #{{ $etiqueta->nombre }}
                                </span>
                            @empty
                                <span style="font-size: 0.7rem; color: var(--tx-4);">Sin etiquetas</span>
                            @endforelse
                        </div>
                    </div>
                    <span style="color: var(--tx-4); transition: transform 0.2s;" :style="abierta ? 'transform: rotate(180deg)' : ''">▼</span>
                </div>
                
                <div class="acciones-pregunta" x-show="abierta" x-cloak style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem; display: flex; gap: 0.75rem;">
                    <a href="{{ route('profesor.preguntas.edit', [$modulo->id_modulo, $pregunta->id_pregunta]) }}" class="btn btn-secondary">
                        Editar Pregunta
                    </a>
                    <form action="{{ route('profesor.preguntas.destroy', [$modulo->id_modulo, $pregunta->id_pregunta]) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta pregunta?');">
                        @csrf
                        @method('DELETE') 
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>     
        @endforeach
    </div>
@endsection