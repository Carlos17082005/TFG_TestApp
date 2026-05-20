@php
    $disabled = $estado ? 'disabled' : '';
    $letraParaTexto = [];
    foreach ($mezclada as $i => $textoB) { $letraParaTexto[chr(97 + $i)] = $textoB; }
    $textoParaLetra = array_flip($letraParaTexto);
@endphp

<div class="table-container" style="box-shadow: none; border: none;">
    <table class="main-table" style="background: transparent;">
        <tbody>
            @foreach ($parejas as $index => $pareja)
                @php
                    $seleccionado = $estado['usuario'][$index] ?? '';
                    $correcto = $pareja['b'];
                    $esCorrecta = $estado && $seleccionado === $correcto;
                    $letraCorrecta = $textoParaLetra[$correcto] ?? '?';
                    $class = 'form-input conecta-select conecta-grupo-' . $id;
                    if ($estado) $class .= $esCorrecta ? ' correct-bg' : ' incorrect-bg';
                @endphp
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.5rem 0; width: 120px;">
                        <select name="respuestas[{{ $id }}][{{ $index }}]" class="{{ $class }}" {{ $disabled }} @if(!$estado) onchange="actualizarSelectsConecta({{ $id }})" @endif>
                            <option value="">-</option>
                            @foreach ($letraParaTexto as $letra => $textoB)
                                <option value="{{ $textoB }}" {{ $seleccionado === $textoB ? 'selected' : '' }}>{{ $letra }}</option>
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