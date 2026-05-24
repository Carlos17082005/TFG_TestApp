@php
    $disabled = $estado ? 'disabled' : '';
    $letraParaTexto = [];
    foreach ($mezclada as $i => $textoB) { $letraParaTexto[chr(97 + $i)] = $textoB; }


    $letraATexto = function($valor) use ($letraParaTexto, $mezclada) {
        if ($valor === null || $valor === '') return null;
        $lower = strtolower(trim($valor));
        if (strlen($lower) === 1 && isset($letraParaTexto[$lower])) {
            return $letraParaTexto[$lower];
        }
        return $valor;
    };

    $textoALetra = function($textoB) use ($mezclada) {
        foreach ($mezclada as $i => $t) {
            if (strtolower(trim($t)) === strtolower(trim($textoB))) {
                return chr(97 + $i);
            }
        }
        return '?';
    };
@endphp

<div class="table-container" style="box-shadow: none; border: none;">
    <table class="main-table" style="background: transparent;">
        <tbody>
            @foreach ($parejas as $index => $pareja)
                @php
                    $valorEnviado  = $estado['usuario'][$index] ?? ''; 
                    $textoUsuario  = $letraATexto($valorEnviado); 
                    $correcto      = $pareja['b']; 
                    $esCorrecta    = $estado
                                     && $textoUsuario !== null
                                     && strtolower(trim($textoUsuario)) === strtolower(trim($correcto));
                    $letraCorrecta = $textoALetra($correcto);
                    $class = 'form-input conecta-select conecta-grupo-' . $id;
                    if ($estado) $class .= $esCorrecta ? ' correct-bg' : ' incorrect-bg';

                    $oldValor = old('respuestas.' . $id . '.' . $index, '');
                @endphp
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.5rem 0; width: 120px;">
                        <select name="respuestas[{{ $id }}][{{ $index }}]" class="{{ $class }}" {{ $disabled }} @if(!$estado) onchange="actualizarSelectsConecta({{ $id }})" @endif>
                            <option value="">-</option>
                            @foreach ($letraParaTexto as $letra => $textoB)
                                @php
                                    $selVal = $estado ? $valorEnviado : $oldValor;
                                    $isSelected = strtolower(trim($selVal)) === $letra;
                                @endphp
                                <option value="{{ $letra }}" {{ $isSelected ? 'selected' : '' }}>{{ $letra }}</option>
                            @endforeach
                        </select>
                        @if ($estado && !$esCorrecta)
                            <span class="correct-text" style="color: rgb(10, 10, 181)">Correcta: <strong style="font-stretch: expanded">{{ $letraCorrecta }}</strong></span>
                        @endif
                    </td>
                    <td style="padding: 0.5rem 1rem;"><strong>{{ $loop->iteration }}.</strong> {{ $pareja['a'] }}</td>
                    <td style="padding: 0.5rem 0; color: var(--tx-2);">
                        @if (isset($mezclada[$loop->index]))
                            <strong>{{ chr(97 + $loop->index) }}.</strong> {{ $mezclada[$loop->index] }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@once
<script>
    function actualizarSelectsConecta(idPregunta) {
        const selects = document.querySelectorAll(`.conecta-grupo-${idPregunta}`);

        const seleccionados = Array.from(selects).map(s => s.value).filter(v => v !== '');
        selects.forEach(select => {
            const actual = select.value;
            select.querySelectorAll('option').forEach(opt => {
                if (opt.value === '') return;
                const ocupada = seleccionados.includes(opt.value) && opt.value !== actual;
                opt.disabled = ocupada;
                opt.style.display = ocupada ? 'none' : '';
            });
        });
    }
</script>
@endonce