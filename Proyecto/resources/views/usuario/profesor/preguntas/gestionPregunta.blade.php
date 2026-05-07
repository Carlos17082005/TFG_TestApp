@extends('layouts.app')

@section('title', 'preguntas')

@section('content')
    <x-header />
    <x-errores />
    @php
        $edicion = isset($pregunta);  // Detecta si es creacion o edicion 
        $url = $edicion 
            ? route('profesor.preguntas.update', [$modulo->id_modulo, $pregunta->id_pregunta]) 
            : route('profesor.preguntas.store', $modulo->id_modulo);

        // Cargamos los datos de la pregunta a editar o lo dejamos vacio
        $tipoData = $pregunta->tipo ?? "";
        $enunciadoData = $pregunta->contenido['enunciado'] ?? "";
        $respuestaData = $pregunta->contenido['respuesta'] ?? "";

        $opcionesData = [];
        if ($edicion && isset($pregunta->contenido['opciones'])) {
            foreach ($pregunta->contenido['opciones'] as $i => $valor) {
                $opcionesData[] = ['id' => $i + 1, 'valor' => $valor];
            }
            
        } else {
            $opcionesData = [
                ['id' => 1, 'valor' => ''],
                ['id' => 2, 'valor' => ''],
                ['id' => 3, 'valor' => ''],
            ];
        }
        
        $parejasData = [];
        if ($edicion && !empty($pregunta->contenido['parejas'])) {
            foreach ($pregunta->contenido['parejas'] as $i => $pareja) {
                $parejasData[] = ['id' => $i + 1, 'a' => $pareja['a'], 'b' => $pareja['b']];
            }
        } else {
            $parejasData = [
                ['id' => 1, 'a' => '', 'b' => ''],
                ['id' => 2, 'a' => '', 'b' => ''],
            ];
        }

        $etiquetasData = [];
        if ($edicion && isset($pregunta->listaEtiquetas)) {
            $etiquetasData = $pregunta->listaEtiquetas->map(function($t) {
                return ['id' => $t->id_etiqueta, 'nombre' => $t->nombre, 'es_nueva' => false];
            })->toArray();
        }
    @endphp

    <h1>{{ $edicion ? 'Editar Pregunta' : 'Crear Pregunta' }}</h1>

    <form method="POST" action="{{ $url }}">
        @csrf
        @if($edicion)
            @method('PUT')
        @endif
        <div x-data="handler()">

            <div>
                <label>¿Qué tipo de pregunta es?</label>
                <select x-model="tipo_pregunta" name="tipo">
                    <option value="">Selecciona el tipo de pregunta...</option>
                    <option value="texto">Pregunta Abierta</option>
                    <option value="multiple">Opción Múltiple</option>
                    <option value="booleana">Verdadero / Falso</option>
                    <option value="conecta">Conectar Columnas</option>
                </select>
            </div>

            <div x-show="tipo_pregunta !== ''">
                <label>Escribe tu pregunta:</label>
                <input type="text" name="enunciado" x-model="enunciado" placeholder="Ej: ¿Qué pregunta pongo aquí?">
            </div>

            <div x-show="tipo_pregunta === 'texto'" x-cloak>
                <label>Respuesta correcta:</label>
                <input type="text" name="respuesta" x-model="respuesta" placeholder="Ej: Respuesta correcta" :disabled="tipo_pregunta !== 'texto'">
            </div>

            <div x-show="tipo_pregunta === 'multiple'" x-cloak>
                <label>Opciones (Mínimo 3):</label>
                
                <template x-for="(opcion, index) in opciones" :key="opcion.id">
                    <div>
                        <span x-text="getLetra(index) + ')'"></span>
                        
                        <input type="text" name="opciones[]" x-model="opcion.valor" placeholder="Escribe la opción..." :required="tipo_pregunta === 'multiple'" :disabled="tipo_pregunta !== 'multiple'">
                        
                        <button type="button" x-show="opciones.length > 3" @click="opciones = opciones.filter(o => o.id !== opcion.id)">&times;</button>
                    </div>
                </template>
                
                <button type="button" @click="opciones.push({ id: Date.now(), valor: '' })">Añadir Opción</button>

                <div>
                    <label>Respuesta correcta:</label>
                    <select name="respuesta" x-model="respuesta" :required="tipo_pregunta === 'multiple'" :disabled="tipo_pregunta !== 'multiple'">
                        <option value="">Selecciona la respuesta correcta...</option>
                        <template x-for="(opcion, index) in opciones" :key="'resp-'+opcion.id">
                            <option :value="opcion.valor" :selected="opcion.valor === respuesta" x-text="'Opción ' + getLetra(index).toUpperCase()"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div x-show="tipo_pregunta === 'booleana'" x-cloak>
                <label>La respuesta correcta es:</label>
                <br>
                <label>
                    <input type="radio" name="respuesta" x-model="respuesta" value="verdadero" :disabled="tipo_pregunta !== 'booleana'">
                    <span>{{ __('pregunta.verdadero') }}</span>
                </label>
                <br>
                <label>
                    <input type="radio" name="respuesta" x-model="respuesta" value="falso" :disabled="tipo_pregunta !== 'booleana'">
                    <span>{{ __('pregunta.falso') }}</span>
                </label>
            </div>

            <div x-show="tipo_pregunta === 'conecta'" x-cloak>
                <label>Colocalas de forma ordena, se mezclaran de forma automatica</label>
                
                <template x-for="(pareja, index) in parejas" :key="pareja.id">
                    <div>
                        <span x-text="(index + 1) + '.'"></span>  <!-- index = 0 -->
                        
                        <input type="text" name="columna_a[]" x-model="pareja.a" placeholder="Concepto A" :required="tipo_pregunta === 'conecta'" :disabled="tipo_pregunta !== 'conecta'">
                        
                        <span> &rarr; </span>  <!-- flecha puramente visual -->
                        
                        <input type="text" name="columna_b[]" x-model="pareja.b" placeholder="Definición B" :required="tipo_pregunta === 'conecta'" :disabled="tipo_pregunta !== 'conecta'">
                        
                        <button type="button" x-show="parejas.length > 2" @click="parejas = parejas.filter(p => p.id !== pareja.id)">&times;</button>
                    </div>
                </template>
                
                <button type="button" @click="parejas.push({ id: Date.now(), a: '', b: '' })">
                    Añadir Pareja
                </button>
            </div>

            <button type="submit">Guardar Pregunta</button>
            <a href="{{ route('profesor.preguntas.index', $modulo->id_modulo) }}">
                <button type="button">Cancelar</button>
            </a>

            <br><hr><br>
            <div>
                <label>Etiquetas de la pregunta (Opcional):</label>
                <br><br>

                <div>
                    <input type="search" x-model="busqueda_etiqueta" placeholder="Buscar etiqueta existente...">
                    <select x-model="id_seleccionada">
                        <option value="">Selecciona una existente...</option>
                        <template x-for="etiqueta in etiquetas_filtradas" :key="etiqueta.id_etiqueta">
                            <option :value="etiqueta.id_etiqueta" x-text="etiqueta.nombre"></option>
                        </template>
                    </select>
                    <button type="button" @click="agregarExistente()">Añadir</button>
                </div>
                
                <br>

                <div>
                    <input type="text" x-model="nombre_nueva" placeholder="O escribe una nueva...">
                    <button type="button" @click="agregarNueva()">Crear y Añadir</button>
                </div>

                <br>

                <div>
                    <p><strong>Etiquetas seleccionadas:</strong></p>
                    <ul>
                        <template x-for="(etiqueta, index) in etiquetas_agregadas" :key="index">
                            <li>
                                <span x-text="etiqueta.nombre"></span>
                                <span x-show="etiqueta.es_nueva"> (Nueva)</span>
                                <button type="button" @click="quitarEtiqueta(index)">&times;</button>

                                <input type="hidden" 
                                    :name="etiqueta.es_nueva ? 'etiquetas_nuevas[]' : 'etiquetas_existentes[]'" 
                                    :value="etiqueta.es_nueva ? etiqueta.nombre : etiqueta.id">
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </form>

    <script>
        function handler() {
            return {
                // Datos de la pregunta que queremos editar
                tipo_pregunta: @json($tipoData), 
                enunciado: @json($enunciadoData),
                respuesta: @json($respuestaData),
                
                opciones: @json($opcionesData),
                parejas: @json($parejasData),

                // Etiquetas ------------------------------------------------------
                etiquetas_bd: @json($etiquetas_bd),
                etiquetas_agregadas: @json($etiquetasData),

                id_seleccionada: '',
                nombre_nueva: '',
                busqueda_etiqueta:  '',

                // Etiquetas filtradas según la búsqueda, excluyendo las ya añadidas
                get etiquetas_filtradas() {
                    let busqueda = this.normalizar(this.busqueda_etiqueta);
                    return this.etiquetas_bd.filter(e => {
                        let yaAgregada = this.etiquetas_agregadas.some(a => a.id === e.id_etiqueta);
                        let coincide   = busqueda === '' || this.normalizar(e.nombre).includes(busqueda);
                        return !yaAgregada && coincide;
                    });
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

                quitarEtiqueta(index) {
                    this.etiquetas_agregadas.splice(index, 1);
                },

                // Función auxiliar para convertir el índice (0,1,2) en letras (a, b, c)
                getLetra(index) {
                    return String.fromCharCode(97 + index);
                },
            }
        }
    </script>
@endsection