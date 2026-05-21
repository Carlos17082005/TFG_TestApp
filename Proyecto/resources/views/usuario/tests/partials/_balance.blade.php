@php
    $leftSecs  = array_filter($secciones, fn($s) => $s['col'] === 'left');
    $rightSecs = array_filter($secciones, fn($s) => $s['col'] === 'right');

    // Dividimos en Top y Bottom para forzar la alineación de "Activo Corriente" y "Pasivo Corriente"
    $leftSecsTop = array_filter($leftSecs, fn($s) => $s['key'] === 'activo_nc');
    $leftSecsBottom = array_filter($leftSecs, fn($s) => $s['key'] !== 'activo_nc');
    
    $rightSecsTop = array_filter($rightSecs, fn($s) => $s['key'] !== 'pasivo_c');
    $rightSecsBottom = array_filter($rightSecs, fn($s) => $s['key'] === 'pasivo_c');

    $todosElementos = [];
    foreach ($secciones as $sec) {
        foreach ($sec['bloques'] as $bloque) {
            foreach ($bloque['filas'] as $fila) {
                if (!empty($fila['nombre'])) {
                    $todosElementos[] = ['nombre' => $fila['nombre'], 'importe' => $fila['importe'] ?? ''];
                }
            }
        }
    }

    // ALEATORIZAR EL ORDEN DE LOS ELEMENTOS AQUÍ
    shuffle($todosElementos);

    $normImporte = fn($v) => (float) str_replace(['.', ' '], '', str_replace(',', '.', trim((string) $v)));
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
                                                <select :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" class="bal-select" x-model="fila.nombre" @change="onSelectNombre(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.nombre">
                                                        <option :value="el.nombre" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
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
                                                <select :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" class="bal-select" x-model="fila.nombre" @change="onSelectNombre(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.nombre">
                                                        <option :value="el.nombre" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
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
                                                <select :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" class="bal-select" x-model="fila.nombre" @change="onSelectNombre(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.nombre">
                                                        <option :value="el.nombre" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
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
                                                <select :name="`respuestas[{{ $id }}][${sec.key}][${bi}][${fi}][nombre]`" class="bal-select" x-model="fila.nombre" @change="onSelectNombre(sec.key, bi, fi, $event.target.value)">
                                                    <option value="">— Selecciona —</option>
                                                    <template x-for="el in disponiblesParaFila(sec.key, bi, fi)" :key="el.nombre">
                                                        <option :value="el.nombre" x-text="el.nombre"></option>
                                                    </template>
                                                </select>
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
            <template x-for="el in elementosDisponibles" :key="el.nombre">
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
    $fmtNum = fn($n) => number_format($n, 0, ',', '.');
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
                                <div class="bal-col-importe style-hdr-total">
                                    <span>{{ $fmtNum($calcSecTotalCompleta($sec, $userResp)) }}</span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                @endphp
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $perteneceAqui = collect($bloque['filas'])->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $filaCorrecta  = collect($bloque['filas'])->first(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $importeOk     = $filaCorrecta && abs($normImporte($filaU['importe'] ?? '') - $normImporte($filaCorrecta['importe'] ?? '')) < 0.01;
                                            $correcto      = $perteneceAqui && $importeOk;
                                            $rowClass      = $correcto ? 'bal-correct' : 'bal-incorrect';
                                        @endphp
                                        <div class="bal-row {{ $rowClass }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                @if(!$correcto && $perteneceAqui)
                                                    <span class="val-correct-inline">({{ $filaCorrecta['importe'] ?? '' }})</span>
                                                @endif
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach ($filasCorrectas as $filaC)
                                    @if(!collect($filasUsuario)->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaC['nombre']))))
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
                                <div class="bal-col-importe style-hdr-total">
                                    <span>{{ $fmtNum($calcSecTotalCompleta($sec, $userResp)) }}</span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                @endphp
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $perteneceAqui = collect($bloque['filas'])->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $filaCorrecta  = collect($bloque['filas'])->first(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $importeOk     = $filaCorrecta && abs($normImporte($filaU['importe'] ?? '') - $normImporte($filaCorrecta['importe'] ?? '')) < 0.01;
                                            $correcto      = $perteneceAqui && $importeOk;
                                            $rowClass      = $correcto ? 'bal-correct' : 'bal-incorrect';
                                        @endphp
                                        <div class="bal-row {{ $rowClass }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                @if(!$correcto && $perteneceAqui)
                                                    <span class="val-correct-inline">({{ $filaCorrecta['importe'] ?? '' }})</span>
                                                @endif
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach ($filasCorrectas as $filaC)
                                    @if(!collect($filasUsuario)->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaC['nombre']))))
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
                                <div class="bal-col-importe style-hdr-total">
                                    <span>{{ $fmtNum($calcSecTotalCompleta($sec, $userResp)) }}</span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                @endphp
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $perteneceAqui = collect($bloque['filas'])->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $filaCorrecta  = collect($bloque['filas'])->first(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $importeOk     = $filaCorrecta && abs($normImporte($filaU['importe'] ?? '') - $normImporte($filaCorrecta['importe'] ?? '')) < 0.01;
                                            $correcto      = $perteneceAqui && $importeOk;
                                            $rowClass      = $correcto ? 'bal-correct' : 'bal-incorrect';
                                        @endphp
                                        <div class="bal-row {{ $rowClass }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                @if(!$correcto && $perteneceAqui)
                                                    <span class="val-correct-inline">({{ $filaCorrecta['importe'] ?? '' }})</span>
                                                @endif
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach ($filasCorrectas as $filaC)
                                    @if(!collect($filasUsuario)->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaC['nombre']))))
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
                                <div class="bal-col-importe style-hdr-total">
                                    <span>{{ $fmtNum($calcSecTotalCompleta($sec, $userResp)) }}</span>
                                </div>
                                <div class="bal-col-accion"></div>
                            </div>
                            @foreach ($sec['bloques'] as $bi => $bloque)
                                @if(!empty($bloque['label']))<div class="bal-block-hdr">{{ $bloque['label'] }}</div>@endif
                                @php
                                    $filasUsuario   = $userResp[$sec['key']][$bi] ?? [];
                                    $filasCorrectas = array_filter($bloque['filas'], fn($f) => !empty($f['nombre']));
                                @endphp
                                @foreach ($filasUsuario as $fi => $filaU)
                                    @if(!empty($filaU['nombre']))
                                        @php
                                            $perteneceAqui = collect($bloque['filas'])->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $filaCorrecta  = collect($bloque['filas'])->first(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaU['nombre'])));
                                            $importeOk     = $filaCorrecta && abs($normImporte($filaU['importe'] ?? '') - $normImporte($filaCorrecta['importe'] ?? '')) < 0.01;
                                            $correcto      = $perteneceAqui && $importeOk;
                                            $rowClass      = $correcto ? 'bal-correct' : 'bal-incorrect';
                                        @endphp
                                        <div class="bal-row {{ $rowClass }}">
                                            <div class="bal-col-nombre">
                                                <span class="bal-nombre">{{ $filaU['nombre'] }}</span>
                                            </div>
                                            <div class="bal-col-importe layout-correction-importe">
                                                <span class="bal-importe-val">{{ $filaU['importe'] ?? '—' }}</span>
                                                @if(!$correcto && $perteneceAqui)
                                                    <span class="val-correct-inline">({{ $filaCorrecta['importe'] ?? '' }})</span>
                                                @endif
                                            </div>
                                            <div class="bal-col-accion"></div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach ($filasCorrectas as $filaC)
                                    @if(!collect($filasUsuario)->contains(fn($f) => strtolower(trim($f['nombre'] ?? '')) === strtolower(trim($filaC['nombre']))))
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
        
        <div style="display: flex; width: 100%;">
            <div class="bal-outer-footer" style="width: 50%; border-right: 1px solid #ccc;">
                <div class="bal-footer-label">Total Activo (A+B)</div>
                <div class="bal-footer-val">{{ $fmtNum($totalActivo) }}</div>
            </div>
            <div class="bal-outer-footer" style="width: 50%;">
                <div class="bal-footer-label">Total Patrimonio neto y Pasivo (A+B+C)</div>
                <div class="bal-footer-val">{{ $fmtNum($totalPasivo) }}</div>
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
                        if (fila.nombre) usados.add(fila.nombre);
                    }
                }
            }
            return this.todosElementos.filter(el => !usados.has(el.nombre));
        },

        disponiblesParaFila(secKey, bi, fi) {
            const filaActual = this.filas[secKey]?.[bi]?.[fi]?.nombre ?? '';
            const usados = new Set();
            for (const sk in this.filas) {
                for (const b in this.filas[sk]) {
                    for (let f = 0; f < this.filas[sk][b].length; f++) {
                        const n = this.filas[sk][b][f].nombre;
                        if (n && !(sk === secKey && parseInt(b) === bi && f === fi)) usados.add(n);
                    }
                }
            }
            return this.todosElementos.filter(el => !usados.has(el.nombre) || el.nombre === filaActual);
        },

        getFilas(secKey, bi) {
            if (!this.filas[secKey]) this.filas[secKey] = {};
            if (!this.filas[secKey][bi]) this.filas[secKey][bi] = [];
            return this.filas[secKey][bi];
        },

        addRow(secKey, bi) {
            if (!this.filas[secKey]) this.filas[secKey] = {};
            if (!this.filas[secKey][bi]) this.filas[secKey][bi] = [];
            this.filas[secKey][bi].push({ nombre: '', importe: '' });
        },

        removeRow(secKey, bi, fi) {
            this.filas[secKey][bi].splice(fi, 1);
            this.$nextTick(() => this.calcTotales());
        },

        onSelectNombre(secKey, bi, fi, nombre) {
            const el = this.todosElementos.find(e => e.nombre === nombre);
            this.filas[secKey][bi][fi].importe = el ? el.importe : '';
            this.$nextTick(() => this.calcTotales());
        },

        totalActivo: 0,
        totalPasivo: 0,

        totalSeccionCompleta(secKey) {
            let total = 0;
            for (const bi in this.filas[secKey] ?? {}) {
                for (const fila of this.filas[secKey][bi] ?? []) {
                    if (!fila.importe) continue;
                    total += parseFloat(String(fila.importe).replace(/\./g, '').replace(',', '.')) || 0;
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
                        const v = parseFloat(String(fila.importe).replace(/\./g, '').replace(',', '.')) || 0;
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
                        const filasFiltradas = filasGuardadas
                            .filter(f => f.nombre !== null && f.nombre !== '')
                            .map(f => {
                                // Buscar coincidencia exacta primero, luego case-insensitive
                                const el = this.todosElementos.find(e => e.nombre === f.nombre)
                                        ?? this.todosElementos.find(e =>
                                            e.nombre.toLowerCase().trim() === f.nombre.toLowerCase().trim()
                                        );
                                return {
                                    nombre:  f.nombre,          // ← SIEMPRE usar el nombre del old()
                                    importe: el ? el.importe : f.importe  // importe del banco si existe, si no del old()
                                };
                            });
                        this.filas[sec.key][bi] = filasFiltradas.length > 0
                            ? filasFiltradas
                            : [{ nombre: '', importe: '' }];
                    } else {
                        this.filas[sec.key][bi] = [{ nombre: '', importe: '' }];
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
                            if (fila.nombre) {
                                const nombre = fila.nombre;
                                fila.nombre = '';           // reset momentáneo
                                this.$nextTick(() => {      // en el siguiente tick restaurar
                                    fila.nombre = nombre;
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