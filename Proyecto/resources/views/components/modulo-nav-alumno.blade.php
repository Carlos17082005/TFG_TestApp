<nav>
    <select onchange="if(this.value) location = this.value;">
        <option value="">-- Selecciona un módulo --</option>

        @foreach (Auth::user()->alumno->modulos as $modulo)
            <option value="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}"
                {{ $moduloActual?->id_modulo === $modulo->id_modulo ? 'selected' : '' }}
            >
                {{ $modulo->ciclo }} {{ $modulo->modulo }} {{$modulo->profesor->usuario->nombre}} {{$modulo->profesor->usuario->apellidos}}
            </option>
        @endforeach

        <option value="{{ route('alumno.matriculas.index') }}">+ Unirse a un nuevo módulo</option>
    </select> 

    @if ($moduloActual)
        <h2>{{ $moduloActual->ciclo }} {{ $moduloActual->modulo }}</h2>
        <a href="{{ route('alumno.tests.examen', $moduloActual->id_modulo) }}"><button type="button">Examenes</button></a>
        <a href="{{ route('alumno.tests.practica', $moduloActual->id_modulo) }}"><button type="button">Ejercicios</button></a>
        <a href="{{ route('alumno.historial', $moduloActual->id_modulo) }}"><button type="button">Historial</button></a>
        <a href="{{ route('alumno.ajustes', $moduloActual->id_modulo) }}"><button type="button">Ajustes</button></a>
    @endif
</nav>