<nav>
    <div class="modulo_con_color">
        <select onchange="if(this.value) location = this.value;">
            <option value="">-- Selecciona un módulo --</option>
            @foreach (Auth::user()->profesor->modulos as $modulo)
                <option value="{{ route('inicio.dashboardProfesor.mostrar', $modulo->id_modulo) }}"
                    {{ $moduloActual?->id_modulo === $modulo->id_modulo ? 'selected' : '' }}>
                    {{ $modulo->ciclo }} {{ $modulo->modulo }}
                </option>
            @endforeach
            <option value="{{ route('profesor.modulos.create') }}">+ Crear nuevo módulo</option>
        </select>

          @if ($moduloActual)
          <h2>{{ $moduloActual->ciclo }} {{ $moduloActual->modulo }}</h2>
          <a href="{{ route('profesor.preguntas.index', $moduloActual->id_modulo) }}"><button type="button">Preguntas</button></a>
          <a href="{{ route('profesor.tests.index', $moduloActual->id_modulo) }}"><button type="button">Tests</button></a>
          <a href="{{ route('profesor.alumnos.index', $moduloActual->id_modulo) }}"><button type="button">Alumnos</button></a>
          <a href="{{ route('profesor.historial', $moduloActual->id_modulo) }}"><button type="button">Historial</button></a>
          <a href="{{ route('profesor.modulos.edit', $moduloActual->id_modulo) }}"><button type="button">Ajustes</button></a>
      @endif
    </div>
    
</nav>