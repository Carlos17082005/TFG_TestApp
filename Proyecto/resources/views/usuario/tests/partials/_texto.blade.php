@php
    $disabled    = $estado ? 'disabled' : '';
    $respUsuario = $estado['usuario'] ?? '';
    $esCorrecta  = $estado && $estado['puntuacion'] >= 1.0;

    $class = '';
    if ($estado) {
        $class .= $esCorrecta ? ' correct-bg' : ' incorrect-bg';
    }
@endphp

<input type="text"
       name="respuestas[{{ $id }}]"
       class="{{ $class }}"
       value="{{ $respUsuario }}"
       placeholder="Escribe tu respuesta..."
       autocomplete="off"
       {{ $disabled }}>

@if ($estado && !$esCorrecta)
    <div class="correct-text">Respuesta correcta: {{ $estado['correcta'] }}</div>
@endif