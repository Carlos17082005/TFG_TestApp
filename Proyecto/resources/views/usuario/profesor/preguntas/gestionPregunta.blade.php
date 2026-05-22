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

        // Opciones (múltiple)
        $opcionesData = [];
        if ($edicion && isset($pregunta->contenido['opciones'])) {
            foreach ($pregunta->contenido['opciones'] as $i => $valor) {
                $opcionesData[] = ['id' => $i + 1, 'valor' => $valor];
            }
        } else {
            $opcionesData = [['id' => 1, 'valor' => ''],['id' => 2, 'valor' => ''],['id' => 3, 'valor' => '']];
        }
        
        // Parejas (conecta)
        $parejasData = [];
        if ($edicion && !empty($pregunta->contenido['parejas'])) {
            foreach ($pregunta->contenido['parejas'] as $i => $pareja) {
                $parejasData[] = ['id' => $i + 1, 'a' => $pareja['a'], 'b' => $pareja['b']];
            }
        } else {
            $parejasData = [['id' => 1, 'a' => '', 'b' => ''],['id' => 2, 'a' => '', 'b' => '']];
        }

        // Secciones (balance)
        $balanceData = ($edicion && $tipoData === 'balance')
            ? ($pregunta->contenido['secciones'] ?? null)
            : null;

        // Etiquetas
            $etiquetasData = [];
        if ($edicion && isset($pregunta->listaEtiquetas)) {
            $etiquetasData = $pregunta->listaEtiquetas->map(function($t) {
                return ['id' => $t->id_etiqueta, 'nombre' => $t->nombre, 'es_nueva' => false];
            })->toArray();
        }

        // URL cancelar (borrador test)
        $borrador = session('test_borrador');
        $urlCancelar = $borrador 
            ? (isset($borrador['origen_test']) ? route('profesor.tests.edit', [$modulo->id_modulo, $borrador['origen_test']]) : route('profesor.tests.create', $modulo->id_modulo))
            : route('profesor.preguntas.index', $modulo->id_modulo);
    @endphp

    <h1 style="text-align: left;">{{ $edicion ? 'Editar Pregunta' : 'Crear Pregunta' }}</h1>

    <form method="POST" action="{{ $url }}" class="form-card" x-data="handler()" enctype="multipart/form-data" style="position: relative;"
        @submit="if (tipo_pregunta === 'balance' && !beBalanceado && !confirm('El balance no está cuadrado (Total Activo ≠ Total Patrimonio neto y Pasivo).\n\n¿Estás seguro de que quieres guardar la pregunta así?')) $event.preventDefault();">
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
                <option value="balance">Ejercicio de Balance</option>
            </select>
        </div>

        <div class="form-group" style="position: absolute; top: 1.5rem; right: 1.5rem;">
            <button type="button"
                @click="if (!tiene_audio) mostrar_audio = !mostrar_audio"
                style="background: none; border: none; cursor: pointer; font-size: 0.875rem; color: var(--tx-3); font-family: var(--font);"
                x-text="mostrar_audio ? (tiene_audio ? 'Audio' : 'Ocultar audio') : '+ Audio'">
            </button>
        </div>
        

        <div x-show="mostrar_audio" x-cloak class="form-group">

            {{-- Cabecera: label + botón seleccionar --}}
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                <label class="form-label" style="margin: 0;">Audio de la pregunta (Opcional)</label>
                <label class="btn btn-secondary" style="cursor: pointer; width: max-content; font-size: 0.85rem; padding: 0.3rem 0.75rem;">
                    + Seleccionar archivo
                    <input type="file" name="audio" accept="audio/*" style="display: none;" x-ref="audioInput"
                        @change="
                            const file = $event.target.files[0];
                            if (file) {
                                if ($refs.audioNuevo) { $refs.audioNuevo.pause(); $refs.audioNuevo.currentTime = 0; }
                                audio_preview_url = URL.createObjectURL(file);
                            }
                        ">
                </label>
            </div>

            {{-- Fila de players --}}
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">

                {{-- Audio nuevo (izquierda) --}}
                <div x-show="audio_preview_url" style="flex: 1; min-width: 260px;"
                    x-effect="if (!audio_preview_url && $refs.audioNuevo) { $refs.audioNuevo.pause(); $refs.audioNuevo.currentTime = 0; }">
                    <p style="font-size: 0.8rem; color: var(--tx-3); margin-bottom: 0.4rem;">
                        @if(isset($pregunta) && !empty($pregunta->contenido['audio_path']))
                            Nuevo audio (reemplaza al actual)
                        @else
                            Nuevo audio
                        @endif
                    </p>
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: var(--surface-2); border: 1.5px solid var(--border); border-radius: var(--r-sm);">
                        <audio x-ref="audioNuevo" controls style="flex: 1; min-width: 0;" :src="audio_preview_url"></audio>
                        <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.6rem; flex-shrink: 0;"
                            @click="$refs.audioNuevo.pause(); $refs.audioNuevo.currentTime = 0; audio_preview_url = null; $refs.audioInput.value = ''"
                            title="Quitar audio nuevo">&times;</button>
                    </div>
                </div>

                {{-- Audio guardado en servidor (derecha), solo en edición --}}
                @if(isset($pregunta) && !empty($pregunta->contenido['audio_path']))
                    <div style="flex: 1; min-width: 260px;"
                        x-show="!eliminar_audio"
                        x-effect="if (eliminar_audio && $refs.audioGuardado) { $refs.audioGuardado.pause(); $refs.audioGuardado.currentTime = 0; }">
                        <p style="font-size: 0.8rem; color: var(--tx-3); margin-bottom: 0.4rem;">Audio actual (guardado)</p>
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: var(--surface-2); border: 1.5px solid var(--border); border-radius: var(--r-sm);">
                            <audio x-ref="audioGuardado" controls style="flex: 1; min-width: 0;">
                                <source src="{{ asset('storage/' . $pregunta->contenido['audio_path']) }}" type="{{ $pregunta->contenido['audio_mime'] ?? 'audio/mpeg' }}">
                            </audio>
                            <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.6rem; flex-shrink: 0;"
                                @click="$refs.audioGuardado.pause(); $refs.audioGuardado.currentTime = 0; eliminar_audio = true"
                                title="Eliminar audio guardado">&times;</button>
                        </div>
                    </div>
                    <input type="hidden" name="eliminar_audio" :value="eliminar_audio ? '1' : '0'">
                @endif

            </div>
        </div>

        <div class="form-group" x-show="tipo_pregunta !== ''">
            <label class="form-label">Escribe tu pregunta:</label>
            <input required type="text" name="enunciado" x-model="enunciado" class="form-input" placeholder="Ej: ¿Qué pregunta pongo aquí?">
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

        <div class="form-group" x-show="tipo_pregunta === 'balance'" x-cloak>
            <label class="form-label">Rellena el balance con los importes correctos:</label>
            <p style="font-size:0.82rem;color:#666;margin-bottom:0.75rem;">
                Escribe el nombre y el importe de cada elemento. Las filas vacías no se guardarán.
            </p>

            {{-- Campo oculto que envía las secciones como JSON --}}
            <input type="hidden" name="secciones" :value="JSON.stringify(balanceSecciones)">

            <div style="display: flex; width: 100%;">
                <div class="bal-outer-hdr" style="width: 50%; border-right: 1px solid #ccc; border-radius: 6px 0px 0px 6px;">Activo</div>
                <div class="bal-outer-hdr" style="width: 50%; border-radius: 0px 6px 6px 0px;">Patrimonio neto y pasivo</div>
            </div>

            <div class="be-grid">
                {{-- CELDA 1: Top Left (Activo No Corriente) --}}
                <div class="be-col" style="border-bottom: 1px solid #ccc;">
                    <template x-for="sec in balanceSecciones.filter(s => s.col === 'left' && s.key === 'activo_nc')" :key="sec.key">
                        <div>
                            <div class="be-sec-hdr" x-text="sec.titulo"></div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="be-blk-hdr">
                                        <span x-text="bloque.label"></span>
                                    </div>
                                    <template x-for="(fila, fi) in bloque.filas" :key="fi">
                                        <div class="be-fila">
                                            <input type="text"
                                                   class="form-input be-nombre"
                                                   x-model="fila.nombre"
                                                   placeholder="Nombre del elemento"
                                                   :disabled="tipo_pregunta !== 'balance'">
                                            <input type="text"
                                                   class="form-input be-importe"
                                                   x-model="fila.importe"
                                                   placeholder="0"
                                                   :disabled="tipo_pregunta !== 'balance'"
                                                   @input="$dispatch('balance-changed')">
                                            <button type="button" class="be-btn-del-fila"
                                                    x-show="bloque.filas.length > 1"
                                                    @click="bloque.filas.splice(fi, 1); $dispatch('balance-changed')"
                                                    title="Eliminar fila">&times;</button>
                                        </div>
                                    </template>
                                    <button type="button" class="be-btn-add-fila"
                                            @click="bloque.filas.push({ nombre: '', importe: '' })">
                                        + Añadir fila
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- CELDA 2: Top Right (PN y Pasivo No Corriente) 
                     AÑADIDO: display: flex; flex-direction: column; para que dividan el espacio al 50% --}}
                <div class="be-col" style="border-bottom: 1px solid #ccc; display: flex; flex-direction: column;">
                    <template x-for="(sec, index) in balanceSecciones.filter(s => s.col === 'right' && s.key !== 'pasivo_c')" :key="sec.key">
                        <div style="flex: 1; display: flex; flex-direction: column;" :style="index === 0 ? 'border-bottom: 1px solid #ccc;' : ''">
                            <div class="be-sec-hdr" x-text="sec.titulo"></div>
                            <div style="flex: 1;">
                                <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                    <div>
                                        <div class="be-blk-hdr">
                                            <span x-text="bloque.label"></span>
                                        </div>
                                        <template x-for="(fila, fi) in bloque.filas" :key="fi">
                                            <div class="be-fila">
                                                <input type="text"
                                                       class="form-input be-nombre"
                                                       x-model="fila.nombre"
                                                       placeholder="Nombre del elemento"
                                                       :disabled="tipo_pregunta !== 'balance'">
                                                <input type="text"
                                                       class="form-input be-importe"
                                                       x-model="fila.importe"
                                                       placeholder="0"
                                                       :disabled="tipo_pregunta !== 'balance'"
                                                       @input="$dispatch('balance-changed')">
                                                <button type="button" class="be-btn-del-fila"
                                                        x-show="bloque.filas.length > 1"
                                                        @click="bloque.filas.splice(fi, 1); $dispatch('balance-changed')"
                                                        title="Eliminar fila">&times;</button>
                                            </div>
                                        </template>
                                        <button type="button" class="be-btn-add-fila"
                                                @click="bloque.filas.push({ nombre: '', importe: '' })">
                                            + Añadir fila
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- CELDA 3: Bottom Left (Activo Corriente) --}}
                <div class="be-col">
                    <template x-for="sec in balanceSecciones.filter(s => s.col === 'left' && s.key !== 'activo_nc')" :key="sec.key">
                        <div>
                            <div class="be-sec-hdr" x-text="sec.titulo"></div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="be-blk-hdr">
                                        <span x-text="bloque.label"></span>
                                    </div>
                                    <template x-for="(fila, fi) in bloque.filas" :key="fi">
                                        <div class="be-fila">
                                            <input type="text"
                                                   class="form-input be-nombre"
                                                   x-model="fila.nombre"
                                                   placeholder="Nombre del elemento"
                                                   :disabled="tipo_pregunta !== 'balance'">
                                            <input type="text"
                                                   class="form-input be-importe"
                                                   x-model="fila.importe"
                                                   placeholder="0"
                                                   :disabled="tipo_pregunta !== 'balance'"
                                                   @input="$dispatch('balance-changed')">
                                            <button type="button" class="be-btn-del-fila"
                                                    x-show="bloque.filas.length > 1"
                                                    @click="bloque.filas.splice(fi, 1); $dispatch('balance-changed')"
                                                    title="Eliminar fila">&times;</button>
                                        </div>
                                    </template>
                                    <button type="button" class="be-btn-add-fila"
                                            @click="bloque.filas.push({ nombre: '', importe: '' })">
                                        + Añadir fila
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- CELDA 4: Bottom Right (Pasivo Corriente) --}}
                <div class="be-col">
                    <template x-for="sec in balanceSecciones.filter(s => s.col === 'right' && s.key === 'pasivo_c')" :key="sec.key">
                        <div>
                            <div class="be-sec-hdr" x-text="sec.titulo"></div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="be-blk-hdr">
                                        <span x-text="bloque.label"></span>
                                    </div>
                                    <template x-for="(fila, fi) in bloque.filas" :key="fi">
                                        <div class="be-fila">
                                            <input type="text"
                                                   class="form-input be-nombre"
                                                   x-model="fila.nombre"
                                                   placeholder="Nombre del elemento"
                                                   :disabled="tipo_pregunta !== 'balance'">
                                            <input type="text"
                                                   class="form-input be-importe"
                                                   x-model="fila.importe"
                                                   placeholder="0"
                                                   :disabled="tipo_pregunta !== 'balance'"
                                                   @input="$dispatch('balance-changed')">
                                            <button type="button" class="be-btn-del-fila"
                                                    x-show="bloque.filas.length > 1"
                                                    @click="bloque.filas.splice(fi, 1); $dispatch('balance-changed')"
                                                    title="Eliminar fila">&times;</button>
                                        </div>
                                    </template>
                                    <button type="button" class="be-btn-add-fila"
                                            @click="bloque.filas.push({ nombre: '', importe: '' })">
                                        + Añadir fila
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

            </div>{{-- /be-grid --}}

            {{-- Totales en vivo --}}
            <div class="be-totales">
                <div class="be-total-cell">
                    Total Activo (A+B):
                    <strong :class="beBalanceado ? 'be-total-ok' : ''" x-text="beTotalFmt('left')"></strong>
                </div>
                <div class="be-total-cell">
                    Total PN y Pasivo (A+B+C):
                    <strong :class="beBalanceado ? 'be-total-ok' : (beTotalNum('right') > 0 ? 'be-total-mismatch' : '')"
                            x-text="beTotalFmt('right')"></strong>
                </div>
            </div>
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
        // ── Estructura por defecto del balance (PGC español) ────────────────
        const defaultBalance = [
            { key: 'activo_nc', titulo: 'A) Activo no corriente', col: 'left', bloques: [
                { label: 'Inmovilizado intangible',               filas: [{ nombre: '', importe: '' }] },
                { label: 'Inmovilizado material',                 filas: [{ nombre: '', importe: '' }] },
                { label: 'Inversiones inmobiliarias',             filas: [{ nombre: '', importe: '' }] },
                { label: 'Inversiones financieras a l/p',         filas: [{ nombre: '', importe: '' }] },
                { label: 'Deudores comerciales no corrientes',    filas: [{ nombre: '', importe: '' }] },
            ]},
            { key: 'activo_c', titulo: 'B) Activo corriente', col: 'left', bloques: [
                { label: 'Existencias',                                              filas: [{ nombre: '', importe: '' }] },
                { label: 'Deudores comerciales y otras cuentas a cobrar',            filas: [{ nombre: '', importe: '' }] },
                { label: 'Inversiones financieras a c/p',                           filas: [{ nombre: '', importe: '' }] },
                { label: 'Efectivo y otros activos líquidos equivalentes',           filas: [{ nombre: '', importe: '' }] },
            ]},
            { key: 'pn', titulo: 'A) Patrimonio neto', col: 'right', bloques: [
                { label: 'Capital y reservas',  filas: [{ nombre: '', importe: '' }] },
            ]},
            { key: 'pasivo_nc', titulo: 'B) Pasivo no corriente', col: 'right', bloques: [
                { label: 'Deudas a largo plazo', filas: [{ nombre: '', importe: '' }] },
            ]},
            { key: 'pasivo_c', titulo: 'C) Pasivo corriente', col: 'right', bloques: [
                { label: 'Acreedores comerciales y otras cuentas a pagar', filas: [{ nombre: '', importe: '' }] },
            ]},
        ];
        function handler() {
            return {
                tipo_pregunta: @json($tipoData), 
                enunciado: @json($enunciadoData),
                respuesta: @json($respuestaData),
                opciones: @json($opcionesData),
                parejas: @json($parejasData),
                balanceSecciones: @json($balanceData) ?? defaultBalance,
                eliminar_audio: false,
                audio_preview_url: null,
                mostrar_audio: {{ isset($pregunta) && !empty($pregunta->contenido['audio_path'] ?? '') ? 'true' : 'false' }},
                get tiene_audio() {
                    return this.audio_preview_url !== null || 
                        ({{ isset($pregunta) && !empty($pregunta->contenido['audio_path'] ?? '') ? 'true' : 'false' }} && !this.eliminar_audio);
                },

                // etiquetas
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
                // Balance: totales en vivo
                beTotalNum(col) {
                    return this.balanceSecciones
                        .filter(s => s.col === col)
                        .flatMap(s => s.bloques)
                        .flatMap(b => b.filas)
                        .reduce((sum, f) => {
                            const raw = (f.importe || '').replace(/\./g, '').replace(',', '.');
                            return sum + (parseFloat(raw) || 0);
                        }, 0);
                },
                beTotalFmt(col) {
                    return this.beTotalNum(col).toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },
                get beBalanceado() {
                    const a = this.beTotalNum('left');
                    const p = this.beTotalNum('right');
                    return a > 0 && Math.abs(a - p) < 0.01;
                },

                // utilidades
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