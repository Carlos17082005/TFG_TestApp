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
    /* UX Si una pregunta está marcada, se muestra en arriba en la lista de preguntas */
    div[style*="display: flex"] > div:has(input[type="checkbox"]:checked) {
        order: -1;
    }
    /* inputs con color del modulo */
    input:checked{
        accent-color: var(--color-modulo);
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

    {{-- Inicializamos los estados de Alpine con una función que evalúa estrictamente el input numérico --}}
    <form method="POST" action="{{ $accion }}" class="form-card" 
          x-data="{ 
              adicional: false, 
              preguntasAMostrar: '{{ old('preguntas_a_mostrar', $test->preguntas_a_mostrar ?? '') }}',
              aleatorio: {{ old('aleatorio', isset($test) ? $test->aleatorio : true) ? 'true' : 'false' }},
              correccion: {{ old('correccion', isset($test) ? $test->correccion : true) ? 'true' : 'false' }},
              
              get limitActivo() {
                  return this.preguntasAMostrar !== '' && this.preguntasAMostrar !== null && parseInt(this.preguntasAMostrar) > 0;
              }
          }" 
          x-init="
              if (limitActivo) { aleatorio = true; }
              $watch('preguntasAMostrar', () => { 
                  if (limitActivo) { aleatorio = true; } 
              })
          "
          style="position: relative;">
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

        <div class="form-group" style="position: absolute; top: 1.5rem; right: 1.5rem;">
            <button type="button"
                    @click="adicional = !adicional"
                    style="background: none; border: none; cursor: pointer; font-size: 0.875rem; color: var(--color-modulo); font-weight: bold; font-family: var(--font);"
                    x-text="adicional ? 'Ocultar ajustes' : 'Ajustes adicionales'">
            </button>
        </div>
        
        <div x-show="adicional" x-cloak class="form-group" style="margin-top: 1rem; padding: 1rem; border: 1px solid var(--border); border-radius: var(--r-sm); background: var(--surface-2);">
            <div style="margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                {{-- Si limitActivo es true, el checkbox se desactiva y no envía datos. Por lo tanto, el input hidden suple enviando un '1' a la BD --}}
                <input type="hidden" name="aleatorio" :value="limitActivo ? '1' : '0'">
                <input type="checkbox" name="aleatorio" id="aleatorio" value="1" 
                       x-model="aleatorio"
                       :disabled="limitActivo">
                <label for="aleatorio" class="form-label" style="margin: 0;">Orden de las preguntas aleatorio</label>
            </div>

            <div style="margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="hidden" name="correccion" value="0">
                <input type="checkbox" name="correccion" id="correccion" value="1" x-model="correccion">
                <label for="correccion" class="form-label" style="margin: 0;">Mostrar correccion</label>
            </div>

            <div style="margin-top: 1rem;">
                <label for="preguntas_a_mostrar" class="form-label">Número de preguntas a mostrar (Opcional) (Se seleccionará las preguntas de forma aleatoria)</label>
                <input type="number" name="preguntas_a_mostrar" id="preguntas_a_mostrar" class="form-input" 
                       x-model="preguntasAMostrar" 
                       placeholder="Ej: 15 (Si se deja vacío, se mostrarán todas)">
            </div>
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
                }
            }">

            <div style="display: flex; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Buscar pregunta en el banco:</label>
                    <input type="search" x-model="busqueda" class="form-input" placeholder="Texto libre, :etiqueta, tipo:multiple...">
                </div>
                <button type="button" class="btn btn-secondary" onclick="gestorPreguntas('{{ route('profesor.preguntas.create', $modulo->id_modulo) }}')">+ Crear pregunta</button>
            </div>

            <div style="display: flex; flex-direction: column; max-height: 400px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--r-sm); padding: 0.5rem; background: var(--bg);">
                @foreach ($preguntas as $pregunta)
                    <div x-data='{ abierta: false, enunciado: @json(strtolower($pregunta->contenido["enunciado"] ?? "")), etiquetas: @json($pregunta->listaEtiquetas->pluck("nombre")->map(fn($n) => strtolower($n))->toArray()), tipo: @json(strtolower($pregunta->tipo)) }'
                         x-show="coincide(enunciado, etiquetas, tipo)"
                         style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 0.8rem; margin-bottom: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        
                        <label style="display: flex; gap: 1rem; align-items: center; width: 100%; cursor: pointer; margin: 0; padding: 0; border: none; background: transparent;">
                            <input type="checkbox" name="preguntas[]" value="{{ $pregunta->id_pregunta }}" id="pregunta-{{ $pregunta->id_pregunta }}" {{ in_array($pregunta->id_pregunta, $valuePreguntas) ? 'checked' : '' }}>   
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