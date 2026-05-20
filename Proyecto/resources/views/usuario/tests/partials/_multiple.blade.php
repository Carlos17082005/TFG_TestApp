@php $disabled = $estado ? 'disabled' : ''; @endphp
<div style="display: flex; flex-direction: column; gap: 0.5rem;">
    @foreach ($opciones as $index => $opcion)
        @php
            $letra = chr(97 + $index); $class = ''; $checked = ''; 
            $enBlanco = true; // sirve para detectar si ha ha dejado en blanco la respuesta
            if ($estado) {
                if ($estado['usuario'] === $opcion){ $checked = 'checked'; $enBlanco = false;}
                if ($estado['correcta'] === $opcion) $class .= ' correct-bg';
                elseif ($estado['usuario'] === $opcion) $class .= ' incorrect-bg';

                // si la respuesta se ha dejado en blanco, le pone una clase especial que indica 
                // cual sería la correción (de forma diferente a si la hubiera puesto corretamente)
                if (($estado['correcta'] === $opcion) && $enBlanco) $class .= ' azulado-bg';
            }
        @endphp
        <label class="{{ $class }}">
            <input type="radio" name="respuestas[{{ $id }}]" value="{{ $opcion }}" {{ $checked }} {{ $disabled }}>
            <span><strong>{{ $letra }})</strong> {{ $opcion }}</span>
        </label>
    @endforeach
</div>