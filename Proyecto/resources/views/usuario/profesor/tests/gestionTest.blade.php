@extends('layouts.app')

@section('title', 'Gestión de Test')

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

    /* Tarjetas banco izquierda */
    .tarjeta-banco {
        background: var(--surface);
        border: 1px solid var(--border);
        border-left: 3px solid transparent;
        border-radius: var(--r-sm);
        padding: 0.8rem;
        margin-bottom: 0.8rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: border-left-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .tarjeta-banco:hover {
        border-left-color: var(--color-modulo);
        box-shadow: 0 3px 8px rgba(0,0,0,0.13);
        background: var(--surface-2, #f7f7f9);
    }

    /* Tarjetas panel derecho seleccionadas */
    .tarjeta-seleccionada {
        background: var(--surface);
        border: 1px solid var(--color-modulo-20, color-mix(in srgb, var(--color-modulo) 20%, transparent));
        border-left: 3px solid var(--color-modulo);
        border-radius: var(--r-sm);
        padding: 0.8rem;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: box-shadow 0.15s, background 0.15s;
    }
    .tarjeta-seleccionada:hover {
        box-shadow: 0 3px 8px rgba(0,0,0,0.13);
        background: var(--surface-2, #f7f7f9);
    }

    /* Botón X */
    .btn-quitar {
        background: #fde8e8;
        border: none;
        color: #c0392b;
        cursor: pointer;
        width: 2rem;
        height: 2rem;
        border-radius: 0.4rem;
        font-size: 1rem;
        font-weight: bold;
        line-height: 1;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s, color 0.15s, transform 0.1s;
    }
    .btn-quitar:hover {
        background: #c0392b;
        color: #fff;
        transform: scale(1.1);
    }

    .tarjeta-banco--atenuada {
        background-color:#D4D4D4;
    }
</style>
@endpush

@section('content')
    <x-errores />

    @php
        $edicion = isset($test);
        $accion = $edicion ? route('profesor.tests.update', [$modulo->id_modulo, $test->id_test]) : route('profesor.tests.store',  $modulo->id_modulo);
        $accionBorrador = $edicion ? route('profesor.tests.borrador', [$modulo->id_modulo, $test->id_test]) : route('profesor.tests.borrador.nuevo', $modulo->id_modulo);
        
        $borrador    = session('test_borrador');
        $usoBorrador = $borrador && ($borrador['origen_modulo'] ?? null) === $modulo->id_modulo && ($borrador['origen_test'] ?? null) === ($test->id_test ?? null);

        $valueNombre      = old('nombre',      $usoBorrador ? $borrador['nombre']      : ($test->nombre      ?? ''));
        $valueDescripcion = old('descripcion',  $usoBorrador ? $borrador['descripcion'] : ($test->descripcion ?? ''));
        $valueTipo        = old('tipo',         $usoBorrador ? $borrador['tipo']        : ($test->tipo        ?? ''));
        $valuePreguntas   = old('preguntas',    $usoBorrador ? $borrador['preguntas']   : ($edicion ? $test->preguntas->pluck('id_pregunta')->toArray() : []));
        $valueDuracion    = old('duracion',       $usoBorrador ? ($borrador['duracion'] ?? '')       : ($test->examen->duracion ?? ''));
        $valueFecha       = old('fecha_apertura', $usoBorrador ? ($borrador['fecha_apertura'] ?? '') : ($test->examen->fecha_apertura ?? ''));
        $valueFechaCierre = old('fecha_cierre', $usoBorrador ? ($borrador['fecha_cierre'] ?? '') : ($test->examen->fecha_cierre ?? ''));
    @endphp

    <h1 style="text-align: left;">{{ $edicion ? 'Editar Test' : 'Crear Test' }}</h1>

    <form method="POST" action="{{ $accion }}" class="form-card">
        @csrf 
        @if($edicion) @method('PUT') @endif

        <div class="form-group">
            <label class="form-label">Nombre del Test</label>
            <input id="nombre-test" type="text" name="nombre" value="{{ $valueNombre }}" class="form-input" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-input" required>{{ trim($valueDescripcion) }}</textarea>
        </div>

        <div class="form-group" x-data="{ tipo: '{{ $valueTipo }}' }">
            <label class="form-label">Tipo de Test</label>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <label>
                    <input id="tipo-test-practica" type="radio" name="tipo" value="practica" x-on:change="tipo = 'practica'" {{ $valueTipo === 'practica' ? 'checked' : '' }}>
                    <span>Práctica (Sin límite de tiempo)</span>
                </label>
                <label>
                    <input id="tipo-test-examen" type="radio" name="tipo" value="examen" x-on:change="tipo = 'examen'" {{ $valueTipo === 'examen' ? 'checked' : '' }}>
                    <span>Examen Oficial</span>
                </label>
                <label>
                    <input type="radio" name="tipo" value="borrador" x-on:change="tipo = 'borrador'" {{ $valueTipo === 'borrador' ? 'checked' : '' }}>
                    <span>Borrador</span>
                </label>
            </div>

            <div x-show="tipo === 'examen'" style="display: flex; gap: 1rem; background: var(--surface-2); padding: 1rem; border-radius: var(--r-sm); border: 1px solid var(--border);">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Duración (minutos)</label>
                    <input id="duracion-test" type="number" name="duracion" value="{{ $valueDuracion }}" class="form-input">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Fecha de apertura</label>
                    <input id="fecha-test" type="datetime-local" name="fecha_apertura" value="{{ $valueFecha }}" class="form-input">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Fecha de cierre</label>
                    <input id="fecha-cierre-test" type="datetime-local" name="fecha_cierre" value="{{ $valueFechaCierre }}" class="form-input">
                </div>
            </div>
        </div>

        <hr style="margin: 1rem 0;">
        
        <h2>Asignar Preguntas</h2>

        <div x-data="{
                busqueda: '',
                seleccionadas: {{ json_encode(array_map('intval', $valuePreguntas)) }},

                normalizar(texto) {
                    if (!texto) return '';
                    return texto
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^\w\s]/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                get parsed() {
                    let tokens = this.busqueda.trim().toLowerCase().split(/\s+/).filter(Boolean);
                    let etiquetas = tokens.filter(t => t.startsWith(':')).map(t => this.normalizar(t.slice(1)));
                    let tipo      = this.normalizar((tokens.find(t => t.startsWith('tipo:')) ?? '').slice(5));
                    let texto     = this.normalizar(tokens.filter(t => !t.startsWith(':') && !t.startsWith('tipo:')).join(' '));
                    return { etiquetas, tipo, texto };
                },

                coincide(enunciado, etiquetas_pregunta, tipo_pregunta) {
                    let { etiquetas, tipo, texto } = this.parsed;
                    let enunciadoNorm = this.normalizar(enunciado);
                    let etiquetasNorm = etiquetas_pregunta.map(e => this.normalizar(e));
                    if (texto && !enunciadoNorm.includes(texto)) return false;
                    if (tipo  && !tipo_pregunta.includes(tipo)) return false;
                    if (etiquetas.length && !etiquetas.every(e => etiquetasNorm.some(ep => ep.includes(e)))) return false;
                    return true;
                },

                toggle(id) {
                    if (this.seleccionadas.includes(id)) {
                        this.seleccionadas = this.seleccionadas.filter(s => s !== id);
                    } else {
                        this.seleccionadas.push(id);
                    }
                },

                estaSeleccionada(id) {
                    return this.seleccionadas.includes(id);
                }
            }">

            {{-- Barra de búsqueda + botón --}}
            <div style="display: flex; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Buscar pregunta en el banco:</label>
                    <input type="search" x-model="busqueda" class="form-input" placeholder="Texto libre, :etiqueta, tipo:multiple...">
                </div>
                <button type="button" class="btn btn-secondary" onclick="gestorPreguntas('{{ route('profesor.preguntas.create', $modulo->id_modulo) }}')">+ Crear pregunta</button>
            </div>

            {{-- Layout dos columnas --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">

                {{-- Columna izquierda: Banco --}}
                <div>
                    <p class="form-label" style="margin-bottom: 0.5rem;">
                        Banco de preguntas
                    </p>
                    <div style="display: flex; flex-direction: column; max-height: 400px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--r-sm); padding: 0.5rem; background: var(--bg);">
                        @foreach ($preguntas as $pregunta)
                            @php $id = $pregunta->id_pregunta; @endphp
                            <div x-data='{ abierta: false, enunciado: @json(strtolower($pregunta->contenido["enunciado"] ?? "")), etiquetas: @json($pregunta->listaEtiquetas->pluck("nombre")->map(fn($n) => strtolower($n))->toArray()), tipo: @json(strtolower($pregunta->tipo)), id: {{ $id }} }'
                                x-show="coincide(enunciado, etiquetas, tipo)"
                                class="tarjeta-banco"
                                :class="estaSeleccionada(id) ? 'tarjeta-banco--atenuada' : ''">

                                <label style="display: flex; gap: 1rem; align-items: center; width: 100%; cursor: pointer; margin: 0; padding: 0; border: none; background: transparent;">
                                    <input type="checkbox"
                                        name="preguntas[]"
                                        value="{{ $id }}"
                                        id="pregunta-{{ $id }}"
                                        :checked="estaSeleccionada(id)"
                                        @change="toggle(id)">
                                    <span style="flex: 1; font-weight: 500; font-size: 0.95rem;">{{ $pregunta->contenido['enunciado'] }}</span>
                                    <button type="button" @click.prevent="abierta = !abierta" style="background: none; border: none; color: var(--tx-3); cursor: pointer; padding: 0.2rem;">Ver más</button>
                                </label>

                                <div x-show="abierta" x-cloak style="font-size: 0.85rem; color: var(--tx-2); padding-left: 2rem; border-top: 1px dashed var(--border); padding-top: 0.5rem;">
                                    <strong>Tipo:</strong> {{ ucfirst($pregunta->tipo) }} <br>
                                    <strong>Etiquetas:</strong>
                                    @forelse ($pregunta->listaEtiquetas as $etiqueta)
                                        <span style="color: var(--color-modulo);">#{{ $etiqueta->nombre }}</span>
                                    @empty
                                        Ninguna
                                    @endforelse
                                    <br>
                                    <button type="button" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; margin-top: 0.5rem;" onclick="gestorPreguntas('{{ route('profesor.preguntas.edit', [$modulo->id_modulo, $pregunta->id_pregunta]) }}')">Editar esta pregunta</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Columna derecha: Seleccionadas --}}
                <div>
                    <p class="form-label" style="margin-bottom: 0.5rem;">
                        Preguntas seleccionadas (<span x-text="seleccionadas.length"></span>)
                    </p>
                    <div style="display: flex; flex-direction: column; max-height: 400px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--r-sm); padding: 0.5rem; background: var(--bg);">

                        {{-- Mensaje vacío --}}
                        <p x-show="seleccionadas.length === 0"
                        style="color: var(--tx-3); font-size: 0.9rem; text-align: center; padding: 2rem 1rem; margin: 0;">
                            Ninguna pregunta seleccionada todavía.
                        </p>

                        @foreach ($preguntas as $pregunta)
                            @php $id = $pregunta->id_pregunta; @endphp
                            <div x-data='{ enunciado: @json(strtolower($pregunta->contenido["enunciado"] ?? "")), etiquetas: @json($pregunta->listaEtiquetas->pluck("nombre")->map(fn($n) => strtolower($n))->toArray()), tipo: @json(strtolower($pregunta->tipo)) }'
                                x-show="estaSeleccionada({{ $id }}) && coincide(enunciado, etiquetas, tipo)"
                                x-cloak
                                class="tarjeta-seleccionada">

                                <span style="flex: 1; font-weight: 500; font-size: 0.95rem;">
                                    {{ $pregunta->contenido['enunciado'] }}
                                </span>

                                <button type="button"
                                        @click="toggle({{ $id }})"
                                        title="Quitar pregunta"
                                        class="btn-quitar">
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- fin grid --}}
        </div>{{-- fin x-data --}}

        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">{{ $edicion ? 'Actualizar Test' : 'Crear Test' }}</button>
        <button type="button" class="boton_cancelar btn btn-primary" onclick="if(confirm('¿Seguro que NO quieres Guardar los Cambios?')) { history.back(); }">
            <span class="volver_negrita">Cancelar</span>
        </button>
    </form>

    <form id="borrador" method="POST" action="{{ $accionBorrador }}" style="display:none;">
        @csrf
        <input id="nombre-borrador" type="hidden" name="nombre">
        <input id="descripcion-borrador" type="hidden" name="descripcion">
        <input id="tipo-borrador" type="hidden" name="tipo">
        <input id="url-borrador" type="hidden" name="url_actual" value="{{ url()->current() }}">
        <div id="preguntas-borrador"></div>
        <input id="destino-borrador" type="hidden" name="destino_pregunta_url">
        <input id="duracion-borrador" type="hidden" name="duracion">
        <input id="fecha-borrador" type="hidden" name="fecha_apertura">
        <input id="fecha-cierre-borrador" type="hidden" name="fecha_cierre">
    </form>

    <script>
        function gestorPreguntas(destino) {
            var borrador = document.getElementById('borrador');
            document.getElementById('nombre-borrador').value = document.getElementById('nombre-test').value;
            document.getElementById('descripcion-borrador').value = document.querySelector('textarea[name="descripcion"]').value;
            var tipoMarcado = document.querySelector('input[name="tipo"]:checked');
            var tipoSeleccionado = tipoMarcado ? tipoMarcado.value : '';
            document.getElementById('tipo-borrador').value = tipoSeleccionado;
            document.getElementById('duracion-borrador').value = tipoSeleccionado === 'examen' ? document.getElementById('duracion-test').value : '';
            document.getElementById('fecha-borrador').value = tipoSeleccionado === 'examen' ? document.getElementById('fecha-test').value : '';
            document.getElementById('fecha-cierre-borrador').value = tipoSeleccionado === 'examen' ? document.getElementById('fecha-cierre-test').value : '';
            
            var contenedorPreguntas = document.getElementById('preguntas-borrador');
            contenedorPreguntas.innerHTML = '';
            document.querySelectorAll('input[name="preguntas[]"]:checked').forEach(function(cb) {
                var oculto = document.createElement('input'); oculto.type = 'hidden'; oculto.name = 'preguntas[]'; oculto.value = cb.value;
                contenedorPreguntas.appendChild(oculto);
            });
            document.getElementById('destino-borrador').value = destino;
            borrador.submit();
        }
    </script>
@endsection