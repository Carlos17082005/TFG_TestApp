@php
    $disabled = $estado ? 'disabled' : '';
    $respUsuario = $estado['usuario'] ?? '';
    $esCorrecta = $estado && $estado['puntuacion'] >= 1.0;
    $class = 'form-input';
    if ($estado) $class .= $esCorrecta ? ' correct-bg' : ' incorrect-bg';

    if (!$estado) $class .= '';
    elseif (($respUsuario === '')) $class .= ' azulado-bg';
@endphp

<p style="all: unset; !important" class="{{ $class }}"></p>
<input type="text" name="respuestas[{{ $id }}]" class="{{ $class }}" value="{{ $respUsuario }}" placeholder="Escribe tu respuesta..." autocomplete="off" {{ $disabled }} style="max-width: 100%;">

@if ($estado && !$esCorrecta)
    <div class="correct-text" style="margin-top: 0.5rem; color: rgb(17, 0, 117)">Respuesta correcta: <strong>{{ $estado['correcta'] }}</strong></div>
@endif