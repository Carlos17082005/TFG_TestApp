@php $disabled = $estado ? 'disabled' : ''; @endphp

@foreach (['verdadero', 'falso'] as $valor)
    @php
        $class  = '';
        $checked = '';

        if ($estado) {
            if ($estado['usuario'] === $valor) $checked = 'checked';

            if ($estado['correcta'] === $valor)     $class .= ' correct-bg';
            elseif ($estado['usuario'] === $valor)  $class .= ' incorrect-bg';
        }
    @endphp

    <label class="{{ $class }}">
        <input type="radio"
               name="respuestas[{{ $id }}]"
               value="{{ $valor }}"
               {{ $checked }}
               {{ $disabled }}>
        {{ __('pregunta.' . $valor) }}
    </label>
@endforeach
