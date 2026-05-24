@php
    $disabled = $estado ? 'disabled' : '';

    $indexCorrecto = null;
    $indexUsuario  = null;
    if ($estado) {
        foreach ($opciones as $i => $op) {
            if ($indexCorrecto === null && $op === $estado['correcta']) $indexCorrecto = $i;
            if ($indexUsuario  === null && $op === $estado['usuario'])  $indexUsuario  = $i;
        }
    }
@endphp
<div style="display: flex; flex-direction: column; gap: 0.5rem;">
    @foreach ($opciones as $index => $opcion)
        @php
            $letra   = chr(97 + $index);
            $class   = '';
            $checked = '';

            if ($estado) {
                if ($index === $indexUsuario) $checked = 'checked';

                if ($index === $indexCorrecto) {
                    $class .= ' correct-bg';
                    if ($indexUsuario === null) $class .= ' azulado-bg';

                } elseif ($index === $indexUsuario) {
                    $class .= ' incorrect-bg';
                }
            }
        @endphp
        <label class="{{ $class }}">
            <input type="radio" name="respuestas[{{ $id }}]" value="{{ $opcion }}"
                {{ $estado
                    ? $checked
                    : (old('respuestas.' . $id) === $opcion ? 'checked' : '') }}
                {{ $disabled }}>
            <span><strong>{{ $letra }})</strong> {{ $opcion }}</span>
        </label>
    @endforeach
</div>