@extends('layouts.app')

@section('title', 'tests')

@section('content')
    <x-header />
    <x-errores />

    @php
        $edicion = isset($test);

        $accion = $edicion
            ? route('profesor.tests.update', [$modulo->id_modulo, $test->id_test])
            : route('profesor.tests.store',  $modulo->id_modulo);

            //////////////////////
        $accionBorrador = $edicion
            ? route('profesor.tests.borrador',       [$modulo->id_modulo, $test->id_test])
            : route('profesor.tests.borrador.nuevo', $modulo->id_modulo);
            //////////////////////
        $borrador    = session('test_borrador');
        $usoBorrador = $borrador
            && ($borrador['origen_modulo'] ?? null) === $modulo->id_modulo
            && ($borrador['origen_test']   ?? null) === ($test->id_test ?? null);

        $valueNombre      = old('nombre',      $usoBorrador ? $borrador['nombre']      : ($test->nombre      ?? ''));
        $valueDescripcion = old('descripcion',  $usoBorrador ? $borrador['descripcion'] : ($test->descripcion ?? ''));
        $valueTipo        = old('tipo',         $usoBorrador ? $borrador['tipo']        : ($test->tipo        ?? ''));
        $valuePreguntas   = old('preguntas',    $usoBorrador ? $borrador['preguntas']   : ($edicion ? $test->preguntas->pluck('id_pregunta')->toArray() : []));
        $valueDuracion    = old('duracion',       $usoBorrador ? ($borrador['duracion'] ?? '')       : ($test->examen->duracion ?? ''));
        $valueFecha       = old('fecha_apertura', $usoBorrador ? ($borrador['fecha_apertura'] ?? '') : ($test->examen->fecha_apertura ?? ''));
    @endphp

    <form method="POST" action="{{ $accion }}">
        @csrf 

        @if($edicion)
            @method('PUT')
            <h2>Editar Test</h2>
        @else
            <h2>Crear Test</h2>
        @endif

        

        <!-- Nombre -->

        <p>
            <label>
                <span>Nombre</span>
                <input id="nombre-test" type="text" name="nombre"value="{{ $valueNombre }}" required autofocus>
            </label>
        </p>

        <!-- Descripción -->

        <p>
            <label>
                <span>Descripción</span>
                <textarea name="descripcion"
                            required>
                    {{ $valueDescripcion }}
                </textarea>
            </label>
        </p>

        <!-- Tipo -->

        <div x-data="{ tipo: '{{ $valueTipo }}' }">
    
            <label>
                <input id="tipo-test-practica" type="radio" name="tipo" value="practica"
                    x-on:change="tipo = 'practica'"
                    {{ $valueTipo === 'practica' ? 'checked' : '' }}>
                <span>Práctica</span>
            </label>

            <label>
                <input id="tipo-test-examen" type="radio" name="tipo" value="examen"
                    x-on:change="tipo = 'examen'"
                    {{ $valueTipo === 'examen' ? 'checked' : '' }}>
                <span>Examen</span>
            </label>

            <div x-show="tipo === 'examen'">
                <label>
                    <span>Duración (minutos)</span>
                    <input id="duracion-test" type="number" name="duracion" value="{{ $valueDuracion }}">
                </label>

                <label>
                    <span>Fecha de apertura</span>
                    <input id="fecha-test" type="datetime-local" name="fecha_apertura" value="{{ $valueFecha }}">
                </label>
            </div>

        </div>

        <!-- Asignar Preguntas al Test -->
        
        <h4>Asignar Preguntas al Test</h4>

        <div x-data="{
                busqueda: '',

                normalizar(texto) {
                    return texto
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^\w\s]/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                get parsed() {
                    let tokens = this.normalizar(this.busqueda).split(/\s+/).filter(Boolean);
                    let etiquetas = tokens.filter(t => t.startsWith(':')).map(t => t.slice(1));
                    let tipo      = (tokens.find(t => t.startsWith('tipo:')) ?? '').slice(5);
                    let texto     = tokens.filter(t => !t.startsWith(':') && !t.startsWith('tipo:')).join(' ');
                    return { etiquetas, tipo, texto };
                },

                coincide(enunciado, etiquetas_pregunta, tipo_pregunta) {
                    let { etiquetas, tipo, texto } = this.parsed;
                    if (texto && !enunciado.includes(texto)) return false;
                    if (tipo  && !tipo_pregunta.includes(tipo)) return false;
                    if (etiquetas.length && !etiquetas.every(e => etiquetas_pregunta.some(ep => ep.includes(e)))) return false;
                    return true;
                }
            }">

            <label>Buscar pregunta:</label>
            <input type="search" x-model="busqueda" placeholder="Texto, :etiqueta, tipo:multiple ...">
            <button type="button" onclick="gestorPreguntas('{{ route('profesor.preguntas.create', $modulo->id_modulo) }}')">+ Crear pregunta</button>
            <p>
                Puedes combinar: <i>:etiqueta</i> para filtrar por etiqueta (varias a la vez),
                <i>tipo:multiple</i> / <i>tipo:booleana</i> / <i>tipo:texto</i> / <i>tipo:conecta</i> para filtrar por tipo,
                y texto libre para buscar en el enunciado.
            </p>

            @foreach ($preguntas as $pregunta)
                <div x-data='{
                        abierta: false,
                        enunciado: @json(strtolower($pregunta->contenido["enunciado"] ?? "")),
                        etiquetas: @json($pregunta->listaEtiquetas->pluck("nombre")->map(fn($n) => strtolower($n))->toArray()),
                        tipo: @json(strtolower($pregunta->tipo))
                    }'
                    x-show="coincide(enunciado, etiquetas, tipo)">

                    <div @click="abierta = !abierta">     
                        {{ $pregunta->contenido['enunciado'] }}
                        <input
                            type="checkbox"
                            name="preguntas[]"
                            @click.stop
                            value="{{ $pregunta->id_pregunta }}"
                            id="pregunta-{{ $pregunta->id_pregunta }}"
                            {{ in_array($pregunta->id_pregunta, $valuePreguntas) ? 'checked' : '' }}
                        >   
                    </div>
                    
                    <div x-show="abierta" x-cloak>
                        <br>

                        Tipo: {{ $pregunta->tipo }}
                        <br>

                        Etiquetas: 
                        @forelse ($pregunta->listaEtiquetas as $etiqueta)
                            [{{ $etiqueta->nombre }}]
                        @empty
                            Ninguna
                        @endforelse

                        <button type="button" onclick="gestorPreguntas('{{ route('profesor.preguntas.edit', [$modulo->id_modulo, $pregunta->id_pregunta]) }}')">Editar</button>
                        <br><br>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit">{{ $edicion ? 'Actualizar' : 'Crear' }}</button>
    </form>

    {{-- Formulario oculto para enviar los datos de la session al servidor y asi no perder el test --}}
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
    </form>

    <script>
        function gestorPreguntas(destino) {
            var borrador = document.getElementById('borrador');
            var contenedorPreguntas = document.getElementById('preguntas-borrador'); // Fallo corregido: el ID exacto de tu div

            document.getElementById('nombre-borrador').value = document.getElementById('nombre-test').value;

            document.getElementById('descripcion-borrador').value = document.querySelector('textarea[name="descripcion"]').value;

            var tipoMarcado = document.querySelector('input[name="tipo"]:checked');
            var tipoSeleccionado = tipoMarcado ? tipoMarcado.value : '';
            document.getElementById('tipo-borrador').value = tipoSeleccionado;
            document.getElementById('duracion-borrador').value = tipoSeleccionado === 'examen' ? document.getElementById('duracion-test').value : '';
            document.getElementById('fecha-borrador').value = tipoSeleccionado === 'examen' ? document.getElementById('fecha-test').value : '';
            
            contenedorPreguntas.innerHTML = '';
            document.querySelectorAll('input[name="preguntas[]"]:checked').forEach(function(cb) {
                var oculto   = document.createElement('input');
                oculto.type  = 'hidden';
                oculto.name  = 'preguntas[]';
                oculto.value = cb.value;
                contenedorPreguntas.appendChild(oculto);
            });

            document.getElementById('destino-borrador').value = destino;

            borrador.submit();
        }
    </script>

@endsection
