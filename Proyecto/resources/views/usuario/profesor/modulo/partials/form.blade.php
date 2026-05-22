@csrf

@if(isset($modulo))
    @method('PUT')
    <h2 style="margin-bottom: 1.5rem;">Modificar Módulo</h2>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin: 0; text-align: left;">Ajustes del Módulo</h1>
        <a href="{{ route('inicio.dashboardAlumno.mostrar', $modulo->id_modulo) }}" class="btn btn-secondary">Volver al Panel</a>
    </div>
@else
    <h2 style="margin-bottom: 1.5rem;">Crear Módulo</h2>
@endif

<div class="form-group">
    <label class="form-label">Ciclo</label>
    <input type="text"
            name="ciclo"
            placeholder="Ej: 1DAW"
            value="{{ old('ciclo', $modulo->ciclo ?? '') }}"
            class="form-input @error('ciclo') incorrect-bg @enderror"
            required
            autofocus>
    @error('ciclo')
        <span style="color: var(--error); font-size: 0.8rem; font-weight: 500;">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">Módulo</label>
    <input type="text"
            name="modulo"
            placeholder="Ej: Programación"
            value="{{ old('modulo', $modulo->modulo ?? '') }}"
            class="form-input @error('modulo') incorrect-bg @enderror"
            required>
    @error('modulo')
        <span style="color: var(--error); font-size: 0.8rem; font-weight: 500;">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">Color del módulo</label>
    <input type="color" 
            name="color"
            value="{{ old('color', $modulo->color ?? '#4F46E5')}}"
            class="form-input @error('color') incorrect-bg @enderror"
            style="height: 45px; padding: 0.2rem 0.5rem; cursor: pointer;"
            required>
</div>

<div class="form-group">
    <label class="form-label">Idioma</label>
    <select name="idioma" class="form-input">
        <option value="es" {{ old('idioma', $modulo->idioma ?? 'es') === 'es' ? 'selected' : '' }}>Español</option>
        <option value="en" {{ old('idioma', $modulo->idioma ?? 'es') === 'en' ? 'selected' : '' }}>English</option>
    </select>
</div>

<div class="form-group">
    <label class="form-label">Clave de matriculación del alumnado</label>
    <input type="text"
            name="clave_matriculacion"
            placeholder="****"
            value="{{ old('clave_matriculacion', $modulo->clave_matriculacion ?? '') }}"
            class="form-input @error('clave_matriculacion') incorrect-bg @enderror"
            required>
    @error('clave_matriculacion')
        <span style="color: var(--error); font-size: 0.8rem; font-weight: 500;">{{ $message }}</span>
    @enderror
</div>

<div style="margin-top: 1.5rem;">
    <button type="submit" class="btn btn-primary" style="font-size: 1.05rem; padding: 0.75rem 1.5rem;">
        {{ isset($modulo) ? 'Actualizar Módulo' : 'Crear Módulo' }}
    </button>
    <button type="reset" onclick="history.back()" class="boton_cancelar btn btn-primary" style="font-size: 1.05rem; padding: 0.75rem 1.5rem;">
        <span class="volver_negrita">Cancelar</span>
    </button>
</div>