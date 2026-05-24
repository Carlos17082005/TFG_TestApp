@php
    $leftSecs  = array_filter($secciones, fn($s) => $s['col'] === 'left');
    $rightSecs = array_filter($secciones, fn($s) => $s['col'] === 'right');

    // Dividimos en Top y Bottom para forzar la alineación de "Activo Corriente" y "Pasivo Corriente"
    $leftSecsTop = array_filter($leftSecs, fn($s) => $s['key'] === 'activo_nc');
    $leftSecsBottom = array_filter($leftSecs, fn($s) => $s['key'] !== 'activo_nc');
    
    $rightSecsTop = array_filter($rightSecs, fn($s) => $s['key'] !== 'pasivo_c');
    $rightSecsBottom = array_filter($rightSecs, fn($s) => $s['key'] === 'pasivo_c');

    $todosElementos = [];
    $uidCounter = 0;
    foreach ($secciones as $sec) {
        foreach ($sec['bloques'] as $bloque) {
            foreach ($bloque['filas'] as $fila) {
                if (!empty($fila['nombre'])) {
                    // uid es un índice numérico único por instancia; permite duplicados de nombre
                    $todosElementos[] = ['uid' => $uidCounter++, 'nombre' => $fila['nombre'], 'importe' => $fila['importe'] ?? ''];
                }
            }
        }
    }

    // ALEATORIZAR EL ORDEN DE LOS ELEMENTOS AQUÍ
    shuffle($todosElementos);

    $normImporte = function($v) {
        $v = trim(str_replace(' ', '', (string) $v));
        if (strpos($v, ',') !== false) {
            return (float) str_replace(',', '.', str_replace('.', '', $v));
        }
        return (float) $v;
    };
@endphp

@if(!$estado)
{{-- =====================================================================
     MODO RESPUESTA: Tabla interactiva sin overflow y con columna de importes
     ===================================================================== --}}
<div x-data="balanceApp({{ $id }}, {{ json_encode($secciones) }}, {{ json_encode($todosElementos) }}, {{ json_encode(old('respuestas.' . $id, [])) }})" class="balance-wrap">
    
    <div class="balance-main-grid">
        <div style="display: flex; width: 100%;">
            <div class="bal-outer-hdr" style="width: 50%; border-right: 1px solid #ccc;">Activo</div>
            <div class="bal-outer-hdr" style="width: 50%;">Patrimonio neto y pasivo</div>
        </div>

        <div style="display: flex; width: 100%; border-bottom: 1px solid #ccc;">
            <div style="width: 50%; border-right: 1px solid #ccc; display: flex; flex-direction: column; min-width: 0;">
                <template x-for="sec in leftSecsTop" :key="sec.key">
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                      <span x-text="sec.titulo"></span>
                                </div>
                                <div class="bal-col-importe style-hdr-total">
                                    <span x-text="fmtNum(totalSeccionCompleta(sec.key))"></span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="bal-block-hdr" x-show="bloque.label" x-text="bloque.label"></div>
                                    <template x-for="(fila, fi) in getFilas(sec.key, bi)" :key="fi">
                                        <div class="bal-row">
                                            <div class="bal-col-nombre">
                                                {{-- uid como valor interno; hidden input envía nombre al servidor --}}
                                                <select class="bal-select" x-model="fila.uid" @change="onSelectUid(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.uid">
                                                        <option :value="el.uid" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
                                                <input type="hidden" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" :value="fila.nombre">
                                            </div>
                                            <div class="bal-col-importe">
                                                <input type="text" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][importe]`" class="bal-importe-input" x-model="fila.importe" placeholder="0" readonly>
                                            </div>
                                            <div class="bal-col-accion">
                                                <button type="button" class="bal-remove-btn" @click="removeRow(sec.key, bi, fi)">&times;</button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="bal-add-btn" @click="addRow(sec.key, bi)">+ Añadir fila</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            
            <div style="width: 50%; display: flex; flex-direction: column; min-width: 0;">
                <template x-for="sec in rightSecsTop" :key="sec.key">
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span x-text="sec.titulo"></span>
                                </div>
                                <div class="bal-col-importe style-hdr-total">
                                    <span x-text="fmtNum(totalSeccionCompleta(sec.key))"></span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="bal-block-hdr" x-show="bloque.label" x-text="bloque.label"></div>
                                    <template x-for="(fila, fi) in getFilas(sec.key, bi)" :key="fi">
                                        <div class="bal-row">
                                            <div class="bal-col-nombre">
                                                {{-- uid como valor interno; hidden input envía nombre al servidor --}}
                                                <select class="bal-select" x-model="fila.uid" @change="onSelectUid(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.uid">
                                                        <option :value="el.uid" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
                                                <input type="hidden" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" :value="fila.nombre">
                                            </div>
                                            <div class="bal-col-importe">
                                                <input type="text" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][importe]`" class="bal-importe-input" x-model="fila.importe" placeholder="0" readonly>
                                            </div>
                                            <div class="bal-col-accion">
                                                <button type="button" class="bal-remove-btn" @click="removeRow(sec.key, bi, fi)">&times;</button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="bal-add-btn" @click="addRow(sec.key, bi)">+ Añadir fila</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div style="display: flex; width: 100%; border-bottom: 1px solid #ccc;">
            <div style="width: 50%; border-right: 1px solid #ccc; display: flex; flex-direction: column; min-width: 0;">
                <template x-for="sec in leftSecsBottom" :key="sec.key">
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span x-text="sec.titulo"></span>
                                </div>
                                <div class="bal-col-importe style-hdr-total">
                                    <span x-text="fmtNum(totalSeccionCompleta(sec.key))"></span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="bal-block-hdr" x-show="bloque.label" x-text="bloque.label"></div>
                                    <template x-for="(fila, fi) in getFilas(sec.key, bi)" :key="fi">
                                        <div class="bal-row">
                                            <div class="bal-col-nombre">
                                                {{-- uid como valor interno; hidden input envía nombre al servidor --}}
                                                <select class="bal-select" x-model="fila.uid" @change="onSelectUid(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.uid">
                                                        <option :value="el.uid" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
                                                <input type="hidden" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" :value="fila.nombre">
                                            </div>
                                            <div class="bal-col-importe">
                                                <input type="text" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][importe]`" class="bal-importe-input" x-model="fila.importe" placeholder="0" readonly>
                                            </div>
                                            <div class="bal-col-accion">
                                                <button type="button" class="bal-remove-btn" @click="removeRow(sec.key, bi, fi)">&times;</button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="bal-add-btn" @click="addRow(sec.key, bi)">+ Añadir fila</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            
            <div style="width: 50%; display: flex; flex-direction: column; min-width: 0;">
                <template x-for="sec in rightSecsBottom" :key="sec.key">
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span x-text="sec.titulo"></span>
                                </div>
                                <div class="bal-col-importe style-hdr-total">
                                    <span x-text="fmtNum(totalSeccionCompleta(sec.key))"></span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            <template x-for="(bloque, bi) in sec.bloques" :key="bi">
                                <div>
                                    <div class="bal-block-hdr" x-show="bloque.label" x-text="bloque.label"></div>
                                    <template x-for="(fila, fi) in getFilas(sec.key, bi)" :key="fi">
                                        <div class="bal-row">
                                            <div class="bal-col-nombre">
                                                {{-- uid como valor interno; hidden input envía nombre al servidor --}}
                                                <select class="bal-select" x-model="fila.uid" @change="onSelectUid(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.uid">
                                                        <option :value="el.uid" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
                                                <input type="hidden" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" :value="fila.nombre">
                                            </div>
                                            <div class="bal-col-importe">
                                                <input type="text" :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][importe]`" class="bal-importe-input" x-model="fila.importe" placeholder="0" readonly>
                                            </div>
                                            <div class="bal-col-accion">
                                                <button type="button" class="bal-remove-btn" @click="removeRow(sec.key, bi, fi)">&times;</button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="bal-add-btn" @click="addRow(sec.key, bi)">+ Añadir fila</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div style="display: flex; width: 100%;">
            <div class="bal-outer-footer" style="width: 50%; border-right: 1px solid #ccc;">
                <div class="bal-footer-label">Total Activo (A+B)</div>
                <div class="bal-footer-val" x-text="fmtNum(totalActivo)"></div>
            </div>
            <div class="bal-outer-footer" style="width: 50%;">
                <div class="bal-footer-label">Total Patrimonio neto y Pasivo (A+B+C)</div>
                <div class="bal-footer-val" x-text="fmtNum(totalPasivo)"></div>
            </div>
        </div>
    </div>

    {{-- Banco de elementos --}}
    <div class="bal-banco">
        <div class="bal-banco-hdr"> Elementos disponibles</div>
        <div class="bal-banco-grid">
            <template x-for="el in elementosDisponibles" :key="el.uid"> 
                <div class="bal-banco-item">
                    <span class="bal-banco-nombre" x-text="el.nombre"></span>
                    <span class="bal-banco-importe" x-text="el.importe"></span>
                </div>
            </template>
            <template x-if="elementosDisponibles.length === 0">
                <div class="bal-banco-vacio" style="grid-column: 1 / -1;">✓ Todos los elementos han sido colocados</div>
            </template>
        </div>
    </div>

</div>

@else
{{-- =====================================================================
     MODO CORRECCIÓN: Limpio de textos alternativos e importes en su columna
     ===================================================================== --}}
@php
    $userResp = $estado['usuario'] ?? [];

    $calcSecTotalCompleta = function($sec, $userResp) use ($normImporte) {
        $total = 0;
        foreach ($sec['bloques'] as $bi => $bloque) {
            foreach ($userResp[$sec['key']][$bi] ?? [] as $filaU) {
                $total += $normImporte($filaU['importe'] ?? '');
            }
        }
        return $total;
    };
    $calcSecTotalCorrecto = function($sec) use ($normImporte) {
        $total = 0;
        foreach ($sec['bloques'] as $bloque) {
            foreach ($bloque['filas'] as $fila) {
                if (!empty($fila['nombre'])) {
                    $total += $normImporte($fila['importe'] ?? '');
                }
            }
        }
        return $total;
    };
    $fmtNum = function($n) {
        if (floor($n) == $n) {
            return number_format($n, 0, ',', '.');
        }
        $formateado = number_format($n, 2, ',', '.');
        return rtrim(rtrim($formateado, '0'), ',');
    };
@endphp
<div class="balance-wrap">
    <div class="balance-main-grid">
        <div style="display: flex; width: 100%;">
            <div class="bal-outer-hdr" style="width: 50%; border-right: 1px solid #ccc;">Activo</div>
            <div class="bal-outer-hdr" style="width: 50%;">Patrimonio neto y pasivo</div>
        </div>

        <div style="display: flex; width: 100%; border-bottom: 1px solid #ccc;">
            <div style="width: 50%; border-right: 1px solid #ccc; display: flex; flex-direction: column; min-width: 0;">
                @foreach ($leftSecsTop as $sec)
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span>{{ $sec['titulo'] }}</span>
                                </div>
                                @php
                                    $secTotalUsuario  = $calcSecTotalCompleta($sec, $userResp);
                                    $secTotalCorrecto = $calcSecTotalCorrecto($sec);
                                    $secTotalOk       = abs($secTotalUsuario - $secTotalCorrecto) < 0.01;
                                @endphp
                                <div class="bal-col-importe style-hdr-total">
                                    @if($secTotalOk)
                                        <span>{{ $fmtNum($secTotalUsuario) }}</span>
                                    @else
                                        <span style="color:#2563eb;font-weight:bold;" title="Total correcto">{{ $fmtNum($secTotalCorrecto) }}</span>
                                    @endif
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                    
                                    // Control para evitar emparejar dos veces el mismo elemento si hay duplicados
                                    $matched = [];
                                    foreach ($filasCorrectas as $idx => $fc) $matched[$idx] = false;
                                @endphp
                                
                                {{-- 1. PINTAR RESPUESTAS DEL USUARIO --}}
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $correcto = false;
                                            // Buscar la primera fila correcta que coincida en nombre Y monto exacto
                                            foreach ($filasCorrectas as $idx => $filaC) {
                                                if (!$matched[$idx]) {
                                                    $mismoNombre = strtolower(trim($filaC['nombre'])) === strtolower(trim($filaU['nombre']));
                                                    $mismoImporte = abs($normImporte($filaC['importe'] ?? '') - $normImporte($filaU['importe'] ?? '')) < 0.01;
                                                    
                                                    if ($mismoNombre && $mismoImporte) {
                                                        $correcto = true;
                                                        $matched[$idx] = true; // Consumimos este elemento correcto
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="bal-row {{ $correcto ? 'bal-correct' : 'bal-incorrect' }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                {{-- Eliminada la corrección en línea para forzar que aparezca abajo si falla --}}
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                
                                {{-- 2. PINTAR RESPUESTAS CORRECTAS FALTANTES (bal-missing) --}}
                                @foreach ($filasCorrectas as $idx => $filaC)
                                    {{-- Si no fue emparejada, significa que el usuario no la puso, o falló en el monto --}}
                                    @if(!$matched[$idx])
                                        <div class="bal-row bal-missing">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre" style="color: #2563eb; font-weight: 500;">{{ $filaC['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe">
                                                <span style="color: #2563eb; font-weight: bold;">{{ $filaC['importe'] ?? '' }}</span>
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div style="width: 50%; display: flex; flex-direction: column; min-width: 0;">
                @foreach ($rightSecsTop as $sec)
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span>{{ $sec['titulo'] }}</span>
                                </div>
                                @php
                                    $secTotalUsuario  = $calcSecTotalCompleta($sec, $userResp);
                                    $secTotalCorrecto = $calcSecTotalCorrecto($sec);
                                    $secTotalOk       = abs($secTotalUsuario - $secTotalCorrecto) < 0.01;
                                @endphp
                                <div class="bal-col-importe style-hdr-total">
                                    @if($secTotalOk)
                                        <span>{{ $fmtNum($secTotalUsuario) }}</span>
                                    @else
                                        <span style="color:#2563eb;font-weight:bold;" title="Total correcto">{{ $fmtNum($secTotalCorrecto) }}</span>
                                    @endif
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                    
                                    // Control para evitar emparejar dos veces el mismo elemento si hay duplicados
                                    $matched = [];
                                    foreach ($filasCorrectas as $idx => $fc) $matched[$idx] = false;
                                @endphp
                                
                                {{-- 1. PINTAR RESPUESTAS DEL USUARIO --}}
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $correcto = false;
                                            // Buscar la primera fila correcta que coincida en nombre Y monto exacto
                                            foreach ($filasCorrectas as $idx => $filaC) {
                                                if (!$matched[$idx]) {
                                                    $mismoNombre = strtolower(trim($filaC['nombre'])) === strtolower(trim($filaU['nombre']));
                                                    $mismoImporte = abs($normImporte($filaC['importe'] ?? '') - $normImporte($filaU['importe'] ?? '')) < 0.01;
                                                    
                                                    if ($mismoNombre && $mismoImporte) {
                                                        $correcto = true;
                                                        $matched[$idx] = true; // Consumimos este elemento correcto
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="bal-row {{ $correcto ? 'bal-correct' : 'bal-incorrect' }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                {{-- Eliminada la corrección en línea para forzar que aparezca abajo si falla --}}
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                
                                {{-- 2. PINTAR RESPUESTAS CORRECTAS FALTANTES (bal-missing) --}}
                                @foreach ($filasCorrectas as $idx => $filaC)
                                    {{-- Si no fue emparejada, significa que el usuario no la puso, o falló en el monto --}}
                                    @if(!$matched[$idx])
                                        <div class="bal-row bal-missing">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre" style="color: #2563eb; font-weight: 500;">{{ $filaC['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe">
                                                <span style="color: #2563eb; font-weight: bold;">{{ $filaC['importe'] ?? '' }}</span>
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="display: flex; width: 100%; border-bottom: 1px solid #ccc;">
            <div style="width: 50%; border-right: 1px solid #ccc; display: flex; flex-direction: column; min-width: 0;">
                @foreach ($leftSecsBottom as $sec)
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span>{{ $sec['titulo'] }}</span>
                                </div>
                                @php
                                    $secTotalUsuario  = $calcSecTotalCompleta($sec, $userResp);
                                    $secTotalCorrecto = $calcSecTotalCorrecto($sec);
                                    $secTotalOk       = abs($secTotalUsuario - $secTotalCorrecto) < 0.01;
                                @endphp
                                <div class="bal-col-importe style-hdr-total">
                                    @if($secTotalOk)
                                        <span>{{ $fmtNum($secTotalUsuario) }}</span>
                                    @else
                                        <span style="color:#2563eb;font-weight:bold;" title="Total correcto">{{ $fmtNum($secTotalCorrecto) }}</span>
                                    @endif
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                    
                                    // Control para evitar emparejar dos veces el mismo elemento si hay duplicados
                                    $matched = [];
                                    foreach ($filasCorrectas as $idx => $fc) $matched[$idx] = false;
                                @endphp
                                
                                {{-- 1. PINTAR RESPUESTAS DEL USUARIO --}}
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $correcto = false;
                                            // Buscar la primera fila correcta que coincida en nombre Y monto exacto
                                            foreach ($filasCorrectas as $idx => $filaC) {
                                                if (!$matched[$idx]) {
                                                    $mismoNombre = strtolower(trim($filaC['nombre'])) === strtolower(trim($filaU['nombre']));
                                                    $mismoImporte = abs($normImporte($filaC['importe'] ?? '') - $normImporte($filaU['importe'] ?? '')) < 0.01;
                                                    
                                                    if ($mismoNombre && $mismoImporte) {
                                                        $correcto = true;
                                                        $matched[$idx] = true; // Consumimos este elemento correcto
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="bal-row {{ $correcto ? 'bal-correct' : 'bal-incorrect' }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                {{-- Eliminada la corrección en línea para forzar que aparezca abajo si falla --}}
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                
                                {{-- 2. PINTAR RESPUESTAS CORRECTAS FALTANTES (bal-missing) --}}
                                @foreach ($filasCorrectas as $idx => $filaC)
                                    {{-- Si no fue emparejada, significa que el usuario no la puso, o falló en el monto --}}
                                    @if(!$matched[$idx])
                                        <div class="bal-row bal-missing">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre" style="color: #2563eb; font-weight: 500;">{{ $filaC['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe">
                                                <span style="color: #2563eb; font-weight: bold;">{{ $filaC['importe'] ?? '' }}</span>
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div style="width: 50%; display: flex; flex-direction: column; min-width: 0;">
                @foreach ($rightSecsBottom as $sec)
                    <div class="bal-section">
                        <div class="bal-section-content">
                            <div class="bal-section-hdr">
                                <div class="bal-col-nombre">
                                    <span>{{ $sec['titulo'] }}</span>
                                </div>
                                @php
                                    $secTotalUsuario  = $calcSecTotalCompleta($sec, $userResp);
                                    $secTotalCorrecto = $calcSecTotalCorrecto($sec);
                                    $secTotalOk       = abs($secTotalUsuario - $secTotalCorrecto) < 0.01;
                                @endphp
                                <div class="bal-col-importe style-hdr-total">
                                    @if($secTotalOk)
                                        <span>{{ $fmtNum($secTotalUsuario) }}</span>
                                    @else
                                        <span style="color:#2563eb;font-weight:bold;" title="Total correcto">{{ $fmtNum($secTotalCorrecto) }}</span>
                                    @endif
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                    
                                    // Control para evitar emparejar dos veces el mismo elemento si hay duplicados
                                    $matched = [];
                                    foreach ($filasCorrectas as $idx => $fc) $matched[$idx] = false;
                                @endphp
                                
                                {{-- 1. PINTAR RESPUESTAS DEL USUARIO --}}
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $correcto = false;
                                            // Buscar la primera fila correcta que coincida en nombre Y monto exacto
                                            foreach ($filasCorrectas as $idx => $filaC) {
                                                if (!$matched[$idx]) {
                                                    $mismoNombre = strtolower(trim($filaC['nombre'])) === strtolower(trim($filaU['nombre']));
                                                    $mismoImporte = abs($normImporte($filaC['importe'] ?? '') - $normImporte($filaU['importe'] ?? '')) < 0.01;
                                                    
                                                    if ($mismoNombre && $mismoImporte) {
                                                        $correcto = true;
                                                        $matched[$idx] = true; // Consumimos este elemento correcto
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="bal-row {{ $correcto ? 'bal-correct' : 'bal-incorrect' }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                {{-- Eliminada la corrección en línea para forzar que aparezca abajo si falla --}}
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                
                                {{-- 2. PINTAR RESPUESTAS CORRECTAS FALTANTES (bal-missing) --}}
                                @foreach ($filasCorrectas as $idx => $filaC)
                                    {{-- Si no fue emparejada, significa que el usuario no la puso, o falló en el monto --}}
                                    @if(!$matched[$idx])
                                        <div class="bal-row bal-missing">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre" style="color: #2563eb; font-weight: 500;">{{ $filaC['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe">
                                                <span style="color: #2563eb; font-weight: bold;">{{ $filaC['importe'] ?? '' }}</span>
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @php
            $totalActivo = 0; $totalPasivo = 0;
            foreach ($leftSecs as $sec) {
                foreach ($sec['bloques'] as $bi => $bloque) {
                    foreach ($userResp[$sec['key']][$bi] ?? [] as $f) { $totalActivo += $normImporte($f['importe'] ?? ''); }
                }
            }
            foreach ($rightSecs as $sec) {
                foreach ($sec['bloques'] as $bi => $bloque) {
                    foreach ($userResp[$sec['key']][$bi] ?? [] as $f) { $totalPasivo += $normImporte($f['importe'] ?? ''); }
                }
            }
        @endphp
        
        @php
            $totalActivoCorrecto = 0;
            $totalPasivoCorrecto = 0;
            foreach ($leftSecs as $sec) {
                foreach ($sec['bloques'] as $bloque) {
                    foreach ($bloque['filas'] as $fila) {
                        $totalActivoCorrecto += $normImporte($fila['importe'] ?? '');
                    }
                }
            }
            foreach ($rightSecs as $sec) {
                foreach ($sec['bloques'] as $bloque) {
                    foreach ($bloque['filas'] as $fila) {
                        $totalPasivoCorrecto += $normImporte($fila['importe'] ?? '');
                    }
                }
            }
            $activoCuadra = abs($totalActivo - $totalActivoCorrecto) < 0.01;
            $pasivoCuadra = abs($totalPasivo - $totalPasivoCorrecto) < 0.01;
        @endphp

        <div style="display: flex; width: 100%;">
            <div class="bal-outer-footer" style="width: 50%; border-right: 1px solid #ccc;">
                <div class="bal-footer-label">Total Activo (A+B)</div>
                <div class="bal-footer-val" style="color: {{ $activoCuadra ? '#16a34a' : '#2563eb' }};">
                    {{ $fmtNum($activoCuadra ? $totalActivo : $totalActivoCorrecto) }}
                </div>
            </div>
            <div class="bal-outer-footer" style="width: 50%;">
                <div class="bal-footer-label">Total Patrimonio neto y Pasivo (A+B+C)</div>
                <div class="bal-footer-val" style="color: {{ $pasivoCuadra ? '#16a34a' : '#2563eb' }};">
                    {{ $fmtNum($pasivoCuadra ? $totalPasivo : $totalPasivoCorrecto) }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
function balanceApp(balId, secciones, todosElementos, oldData = {}) {
    return {
        secciones,
        todosElementos,
        filas: {},

        get leftSecsTop()  { return this.secciones.filter(s => s.col === 'left' && s.key === 'activo_nc'); },
        get leftSecsBottom() { return this.secciones.filter(s => s.col === 'left' && s.key !== 'activo_nc'); },
        get rightSecsTop() { return this.secciones.filter(s => s.col === 'right' && s.key !== 'pasivo_c'); },
        get rightSecsBottom() { return this.secciones.filter(s => s.col === 'right' && s.key === 'pasivo_c'); },

        get elementosDisponibles() {
            const usados = new Set();
            for (const secKey in this.filas) {
                for (const bi in this.filas[secKey]) {
                    for (const fila of this.filas[secKey][bi]) {
                        // Rastrear por uid para que duplicados de nombre se traten de forma independiente
                        if (fila.uid !== null && fila.uid !== undefined && fila.uid !== '') usados.add(fila.uid);
                    }
                }
            }
            return this.todosElementos.filter(el => !usados.has(el.uid));
        },

        disponiblesParaFila(secKey, bi, fi) {
            const filaActualUid = this.filas[secKey]?.[bi]?.[fi]?.uid ?? null;
            const usados = new Set();
            for (const sk in this.filas) {
                for (const b in this.filas[sk]) {
                    for (let f = 0; f < this.filas[sk][b].length; f++) {
                        const uid = this.filas[sk][b][f].uid;
                        if (uid !== null && uid !== undefined && uid !== '') {
                            // Excluir todos los uids ocupados por otras filas
                            if (!(sk === secKey && parseInt(b) === bi && f === fi)) usados.add(uid);
                        }
                    }
                }
            }
            // Mostrar los disponibles + el que ya tiene esta fila (para que siga seleccionado)
            return this.todosElementos.filter(el => !usados.has(el.uid) || el.uid === filaActualUid);
        },

        getFilas(secKey, bi) {
            if (!this.filas[secKey]) this.filas[secKey] = {};
            if (!this.filas[secKey][bi]) this.filas[secKey][bi] = [];
            return this.filas[secKey][bi];
        },

        addRow(secKey, bi) {
            if (!this.filas[secKey]) this.filas[secKey] = {};
            if (!this.filas[secKey][bi]) this.filas[secKey][bi] = [];
            this.filas[secKey][bi].push({ uid: null, nombre: '', importe: '' });
        },

        removeRow(secKey, bi, fi) {
            this.filas[secKey][bi].splice(fi, 1);
            this.$nextTick(() => this.calcTotales());
        },

        onSelectUid(secKey, bi, fi, uid) {
            // uid llega como string desde el DOM; convertir a número para comparación estricta
            const uidNum = uid !== '' && uid !== null ? parseInt(uid, 10) : null;
            const el = uidNum !== null ? this.todosElementos.find(e => e.uid === uidNum) : null;
            this.filas[secKey][bi][fi].uid     = uidNum !== null ? uidNum : null;
            this.filas[secKey][bi][fi].nombre  = el ? el.nombre : '';
            this.filas[secKey][bi][fi].importe = el ? el.importe : '';
            this.$nextTick(() => this.calcTotales());
        },

        parseImporte(v) {
            v = String(v).trim();
            if (v.includes(',')) {
                return parseFloat(v.replace(/\./g, '').replace(',', '.')) || 0;
            }
            return parseFloat(v) || 0;
        },

        totalActivo: 0,
        totalPasivo: 0,

        totalSeccionCompleta(secKey) {
            let total = 0;
            for (const bi in this.filas[secKey] ?? {}) {
                for (const fila of this.filas[secKey][bi] ?? []) {
                    if (!fila.importe) continue;
                    total += this.parseImporte(fila.importe);
                }
            }
            return total;
        },

        calcTotales() {
            let activo = 0, pasivo = 0;
            for (const sec of this.secciones) {
                for (const bi in this.filas[sec.key] ?? {}) {
                    for (const fila of this.filas[sec.key][bi]) {
                        if (!fila.importe) continue;
                        const v = this.parseImporte(fila.importe);
                        if (sec.col === 'left') activo += v;
                        else pasivo += v;
                    }
                }
            }
            this.totalActivo = activo;
            this.totalPasivo = pasivo;
        },

        fmtNum(n) {
            return n.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        init() {
            for (const sec of this.secciones) {
                this.filas[sec.key] = {};
                for (let bi = 0; bi < sec.bloques.length; bi++) {
                    const filasGuardadas = oldData?.[sec.key]?.[bi];
                    if (filasGuardadas) {
                        // usedUids persiste dentro de un bloque para evitar asignar el mismo uid dos veces
                        // cuando hay varios elementos con el mismo nombre (duplicados)
                        const usedUids = new Set();
                        const filasFiltradas = filasGuardadas
                            .filter(f => f.nombre !== null && f.nombre !== '')
                            .map(f => {
                                // Buscar el primer uid disponible con este nombre (respeta duplicados)
                                const el = this.todosElementos.find(e =>
                                    !usedUids.has(e.uid) &&
                                    (e.nombre === f.nombre ||
                                     e.nombre.toLowerCase().trim() === f.nombre.toLowerCase().trim())
                                );
                                if (el) usedUids.add(el.uid);
                                return {
                                    uid:     el ? el.uid : null,
                                    nombre:  f.nombre,
                                    importe: el ? el.importe : f.importe,
                                };
                            });
                        this.filas[sec.key][bi] = filasFiltradas.length > 0
                            ? filasFiltradas
                            : [{ nombre: '', importe: '' }];
                    } else {
                        this.filas[sec.key][bi] = [{ uid: null, nombre: '', importe: '' }];
                    }
                }
            }
            // Primero calcular totales con los datos
            this.calcTotales();
            // Luego en el siguiente tick forzar que Alpine re-evalúe los x-model de los selects
            this.$nextTick(() => {
                // Re-asignar cada nombre para forzar que Alpine sincronice el select
                for (const secKey in this.filas) {
                    for (const bi in this.filas[secKey]) {
                        this.filas[secKey][bi].forEach((fila, fi) => {
                            if (fila.uid !== null && fila.uid !== undefined) {
                                // Forzar re-sincronización del select mediante reset momentáneo de uid
                                const uid = fila.uid;
                                fila.uid = null;
                                this.$nextTick(() => {
                                    fila.uid = uid;
                                });
                            }
                        });
                    }
                }
                this.calcTotales();
            });
        }
    }
}
</script>