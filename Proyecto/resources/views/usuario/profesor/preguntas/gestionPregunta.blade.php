@extends('layouts.app')

@section('title', 'Gestión de Pregunta')

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
    @php
        $edicion = isset($pregunta); 
        $url = $edicion 
            ? route('profesor.preguntas.update', [$modulo->id_modulo, $pregunta->id_pregunta]) 
            : route('profesor.preguntas.store', $modulo->id_modulo);

        $tipoData = $pregunta->tipo ?? "";
        $enunciadoData = $pregunta->contenido['enunciado'] ?? "";
        $respuestaData = $pregunta->contenido['respuesta'] ?? "";

        $opcionesData = [];
        if ($edicion && isset($pregunta->contenido['opciones'])) {
            foreach ($pregunta->contenido['opciones'] as $i => $valor) {
                $opcionesData[] = ['id' => $i + 1, 'valor' => $valor];
            }
        } else {
            $opcionesData = [['id' => 1, 'valor' => ''],['id' => 2, 'valor' => ''],['id' => 3, 'valor' => '']];
        }
        
        $parejasData = [];
        if ($edicion && !empty($pregunta->contenido['parejas'])) {
            foreach ($pregunta->contenido['parejas'] as $i => $pareja) {
                $parejasData[] = ['id' => $i + 1, 'a' => $pareja['a'], 'b' => $pareja['b']];
            }
        } else {
            $parejasData = [['id' => 1, 'a' => '', 'b' => ''],['id' => 2, 'a' => '', 'b' => '']];
        }

        $etiquetasData = [];
        if ($edicion && isset($pregunta->listaEtiquetas)) {
            $etiquetasData = $pregunta->listaEtiquetas->map(function($t) {
                return ['id' => $t->id_etiqueta, 'nombre' => $t->nombre, 'es_nueva' => false];
            })->toArray();
        }

        $borrador = session('test_borrador');
        $urlCancelar = $borrador 
            ? (isset($borrador['origen_test']) ? route('profesor.tests.edit', [$modulo->id_modulo, $borrador['origen_test']]) : route('profesor.tests.create', $modulo->id_modulo))
            : route('profesor.preguntas.index', $modulo->id_modulo);
    @endphp

    <h1 style="text-align: left;">{{ $edicion ? 'Editar Pregunta' : 'Crear Pregunta' }}</h1>

    <form method="POST" action="{{ $url }}" class="form-card" x-data="handler()" enctype="multipart/form-data" style="position: relative;">
        @csrf
        @if($edicion) @method('PUT') @endif
        
        <div class="form-group">
            <label class="form-label">¿Qué tipo de pregunta es?</label>
            <select x-model="tipo_pregunta" name="tipo" class="form-input" style="max-width: 300px;">
                <option value="">Selecciona el tipo...</option>
                <option value="texto">Pregunta Abierta</option>
                <option value="multiple">Opción Múltiple</option>
                <option value="booleana">Verdadero / Falso</option>
                <option value="conecta">Conectar Columnas</option>
            </select>
        </div>

        <!-- Botón Audio -->
        <div class="form-group" style="position: absolute; top: 1.5rem; right: 1.5rem;">
            <button type="button"
                @click="if (!tiene_audio) mostrar_audio = !mostrar_audio"
                style="background: none; border: none; cursor: pointer; font-size: 0.875rem; color: var(--tx-3); font-family: var(--font);"
                x-text="mostrar_audio ? (tiene_audio ? 'Audio' : 'Ocultar audio') : '+ Audio'">
            </button>
        </div>
        

        <!-- Panel Audio -->
        <div x-show="mostrar_audio" x-cloak class="form-group">
            <label class="form-label">Audio de la pregunta (Opcional)</label>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <label class="btn btn-secondary" style="cursor: pointer; width: max-content;">
                    Seleccionar archivo
                    <input type="file" name="audio" accept="audio/*" style="display: none;" x-ref="audioInput"
                        @change="
                            const file = $event.target.files[0];
                            if (file) {
                                audio_preview_url = URL.createObjectURL(file);
                                eliminar_audio = false;
                            }
                        ">
                </label>
                <span x-text="audio_preview_url ? 'Archivo seleccionado' : 'Ningún archivo seleccionado'"
                    style="font-size: 0.9rem; color: var(--tx-3);"></span>
            </div>

            <!-- Preview Audio -->
            <div x-show="audio_preview_url">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.75rem; padding: 0.75rem 1rem; background: var(--surface-2); border: 1.5px solid var(--border); border-radius: var(--r-sm);">
                    <audio controls style="flex: 1; min-width: 0;" :src="audio_preview_url"></audio>
                    <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.6rem; flex-shrink: 0;"
                        @click="audio_preview_url = null; $refs.audioInput.value = ''">&times;</button>
                </div>
            </div>

            <!-- Audio server -->
            @if(isset($pregunta) && !empty($pregunta->contenido['audio_path']))
                <div x-show="!eliminar_audio && !audio_preview_url">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.75rem; padding: 0.75rem 1rem; background: var(--surface-2); border: 1.5px solid var(--border); border-radius: var(--r-sm);">
                        <audio controls style="flex: 1; min-width: 0;">
                            <source src="{{ asset('storage/' . $pregunta->contenido['audio_path']) }}" type="{{ $pregunta->contenido['audio_mime'] ?? 'audio/mpeg' }}">
                        </audio>
                        <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.6rem; flex-shrink: 0;"
                            @click="eliminar_audio = true">&times;</button>
                    </div>
                </div>
                <input type="hidden" name="eliminar_audio" :value="eliminar_audio ? '1' : '0'">
            @endif
        </div>

        <div class="form-group" x-show="tipo_pregunta !== ''">
            <label class="form-label">Escribe tu pregunta:</label>
            <input type="text" name="enunciado" x-model="enunciado" class="form-input" placeholder="Ej: ¿Qué pregunta pongo aquí?">
        </div>

        <div class="form-group" x-show="tipo_pregunta === 'texto'" x-cloak>
            <label class="form-label">Respuesta correcta:</label>
            <input type="text" name="respuesta" x-model="respuesta" class="form-input" placeholder="Ej: Respuesta correcta" :disabled="tipo_pregunta !== 'texto'">
        </div>

        <div class="form-group" x-show="tipo_pregunta === 'multiple'" x-cloak>
            <label class="form-label">Opciones (Mínimo 3):</label>
            <template x-for="(opcion, index) in opciones" :key="opcion.id">
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                    <span x-text="getLetra(index) + ')'" style="font-weight: bold; width: 25px;"></span>
                    <input type="text" name="opciones[]" x-model="opcion.valor" class="form-input" placeholder="Escribe la opción..." :required="tipo_pregunta === 'multiple'" :disabled="tipo_pregunta !== 'multiple'">
                    <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.6rem;" x-show="opciones.length > 3" @click="opciones = opciones.filter(o => o.id !== opcion.id)">&times;</button>
                </div>
            </template>
            <button type="button" class="btn btn-secondary" style="width: max-content;" @click="opciones.push({ id: Date.now(), valor: '' })">+ Añadir Opción</button>

            <div style="margin-top: 1rem;">
                <label class="form-label">Respuesta correcta:</label>
                <select name="respuesta" x-model="respuesta" class="form-input" :required="tipo_pregunta === 'multiple'" :disabled="tipo_pregunta !== 'multiple'">
                    <option value="">Selecciona la respuesta correcta...</option>
                    <template x-for="(opcion, index) in opciones" :key="'resp-'+opcion.id">
                        <option :value="opcion.valor" :selected="opcion.valor === respuesta" x-text="'Opción ' + getLetra(index).toUpperCase()"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="form-group" x-show="tipo_pregunta === 'booleana'" x-cloak>
            <label class="form-label">La respuesta correcta es:</label>
            <label style="margin-bottom: 0.5rem;">
                <input type="radio" name="respuesta" x-model="respuesta" value="verdadero" :disabled="tipo_pregunta !== 'booleana'">
                <span>{{ __('pregunta.verdadero') }}</span>
            </label>
            <label>
                <input type="radio" name="respuesta" x-model="respuesta" value="falso" :disabled="tipo_pregunta !== 'booleana'">
                <span>{{ __('pregunta.falso') }}</span>
            </label>
        </div>

        <div class="form-group" x-show="tipo_pregunta === 'conecta'" x-cloak>
            <label class="form-label">Colócalas de forma ordenada (se mezclarán solas):</label>
            <template x-for="(pareja, index) in parejas" :key="pareja.id">
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                    <span x-text="(index + 1) + '.'" style="font-weight: bold; width: 25px;"></span>
                    <input type="text" name="columna_a[]" x-model="pareja.a" class="form-input" placeholder="Concepto A" :required="tipo_pregunta === 'conecta'" :disabled="tipo_pregunta !== 'conecta'">
                    <span style="color: var(--tx-3);">&rarr;</span>
                    <input type="text" name="columna_b[]" x-model="pareja.b" class="form-input" placeholder="Definición B" :required="tipo_pregunta === 'conecta'" :disabled="tipo_pregunta !== 'conecta'">
                    <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.6rem;" x-show="parejas.length > 2" @click="parejas = parejas.filter(p => p.id !== pareja.id)">&times;</button>
                </div>
            </template>
            <button type="button" class="btn btn-secondary" style="width: max-content;" @click="parejas.push({ id: Date.now(), a: '', b: '' })">+ Añadir Pareja</button>
        </div>

        <hr style="margin: 2rem 0;">

        <div class="form-group">
            <label class="form-label">Etiquetas (Opcional):</label>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <input type="search" x-model="busqueda_etiqueta" class="form-input" style="flex: 1; min-width: 200px;" placeholder="Buscar existente...">
                <select x-model="id_seleccionada" class="form-input" style="flex: 1; min-width: 200px;">
                    <option value="">Selecciona...</option>
                    <template x-for="etiqueta in etiquetas_filtradas" :key="etiqueta.id_etiqueta">
                        <option :value="etiqueta.id_etiqueta" x-text="etiqueta.nombre"></option>
                    </template>
                </select>
                <button type="button" class="btn btn-secondary" @click="agregarExistente()">Añadir</button>
            </div>
            
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <input type="text" x-model="nombre_nueva" class="form-input" placeholder="O escribe una nueva...">
                <button type="button" class="btn btn-secondary" @click="agregarNueva()">Crear</button>
            </div>

            <div style="margin-top: 1rem;">
                <p class="form-label">Seleccionadas:</p>
                <ul style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                    <template x-for="(etiqueta, index) in etiquetas_agregadas" :key="index">
                        <li style="background: var(--color-modulo-10); padding: 0.3rem 0.8rem; border-radius: 99px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; color: var(--color-modulo); border: 1px solid var(--color-modulo-20);">
                            <span x-text="etiqueta.nombre"></span>
                            <span x-show="etiqueta.es_nueva" style="font-size: 0.7rem; opacity: 0.7;">(Nueva)</span>
                            <button type="button" style="background:none; border:none; color:var(--error); cursor:pointer; font-weight:bold;" @click="quitarEtiqueta(index)">&times;</button>
                            <input type="hidden" :name="etiqueta.es_nueva ? 'etiquetas_nuevas[]' : 'etiquetas_existentes[]'" :value="etiqueta.es_nueva ? etiqueta.nombre : etiqueta.id">
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Guardar Pregunta</button>
            <a href="{{ $urlCancelar }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

    <script>
        function handler() {
            return {
                tipo_pregunta: @json($tipoData), 
                enunciado: @json($enunciadoData),
                respuesta: @json($respuestaData),
                opciones: @json($opcionesData),
                parejas: @json($parejasData),
                eliminar_audio: false,
                audio_preview_url: null,
                mostrar_audio: {{ isset($pregunta) && !empty($pregunta->contenido['audio_path'] ?? '') ? 'true' : 'false' }},
                get tiene_audio() {
                    return this.audio_preview_url !== null || 
                        ({{ isset($pregunta) && !empty($pregunta->contenido['audio_path'] ?? '') ? 'true' : 'false' }} && !this.eliminar_audio);
                },
                etiquetas_bd: @json($etiquetas_bd ?? []),
                etiquetas_agregadas: @json($etiquetasData),
                id_seleccionada: '',
                nombre_nueva: '',
                busqueda_etiqueta:  '',
                get etiquetas_filtradas() {
                    let busqueda = this.normalizar(this.busqueda_etiqueta);
                    return this.etiquetas_bd.filter(e => {
                        let yaAgregada = this.etiquetas_agregadas.some(a => a.id === e.id_etiqueta);
                        let coincide   = busqueda === '' || this.normalizar(e.nombre).includes(busqueda);
                        return !yaAgregada && coincide;
                    });
                },
                normalizar(texto) {
                    return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^\w\s]/g, '').replace(/\s+/g, ' ').trim();
                },
                agregarExistente() {
                    if (!this.id_seleccionada) return;
                    let tag = this.etiquetas_bd.find(e => e.id_etiqueta == this.id_seleccionada);
                    if (!this.etiquetas_agregadas.some(e => e.nombre === tag.nombre)) {
                        this.etiquetas_agregadas.push({ id: tag.id_etiqueta, nombre: tag.nombre, es_nueva: false });
                    }
                    this.id_seleccionada = ''; 
                    this.busqueda_etiqueta  = '';
                },
                agregarNueva() {
                    if (this.nombre_nueva.trim() === '') return;
                    let nombre = this.nombre_nueva.trim();
                    if (!this.etiquetas_agregadas.some(e => e.nombre.toLowerCase() === nombre.toLowerCase())) {
                        this.etiquetas_agregadas.push({ id: null, nombre: nombre, es_nueva: true });
                    }
                    this.nombre_nueva = ''; 
                },
                quitarEtiqueta(index) { this.etiquetas_agregadas.splice(index, 1); },
                getLetra(index) { return String.fromCharCode(97 + index); },
            }
        }
    </script>
@endsection