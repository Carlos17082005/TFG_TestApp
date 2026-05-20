@php $disabled = $estado ? 'disabled' : ''; @endphp
<div style="display: flex; flex-direction: column; gap: 0.5rem;">
    @foreach (['verdadero', 'falso'] as $valor)
        @php
            $class = ''; $checked = '';
            $enBlanco = true; // sirve para detectar si ha ha dejado en blanco la respuesta
            if ($estado) {
                if ($estado['usuario'] === $valor) { $checked = 'checked'; $enBlanco = false;}
                if ($estado['correcta'] === $valor) $class .= ' correct-bg';
                elseif ($estado['usuario'] === $valor) $class .= ' incorrect-bg';
                
                // si la respuesta se ha dejado en blanco, le pone una clase especial que indica 
                // cual sería la correción (de forma diferente a si la hubiera puesto corretamente)
                if (($estado['correcta'] === $valor) && $enBlanco) $class .= ' azulado-bg';
            }
        @endphp
        <label class="{{ $class }}">
            <input type="radio" name="respuestas[{{ $id }}]" value="{{ $valor }}" {{ $checked }} {{ $disabled }}>
            <span>{{ __('pregunta.' . $valor) }}</span>
        </label>
    @endforeach
</div>