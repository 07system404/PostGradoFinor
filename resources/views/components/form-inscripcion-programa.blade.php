@props(['alumnoId' => null, 'programaId' => null])

@php
    // Dos modos de uso del mismo modal:
    //  - alumnoId   → el alumno está fijo, se elige el PROGRAMA   (Perfil, Caja)
    //  - programaId → el programa está fijo, se BUSCA el ALUMNO   (detalle del Curso)
    $modoPrograma = !is_null($programaId);
    $accion = $modoPrograma
        ? route('programas.inscribir', $programaId)
        : route('estudiantes.inscribir', $alumnoId);
@endphp

<div class="fi-modal-overlay" id="modal-nueva-inscripcion">
    <div class="fi-modal-box">
        <div class="fi-modal-header">
            <h3 class="fi-modal-title">
                {{ $modoPrograma ? 'Inscribir Estudiante al Programa' : 'Nueva Inscripción en Curso' }}
            </h3>
            <button type="button" class="fi-modal-close" id="fi-cerrar-modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="form-nueva-inscripcion" action="{{ $accion }}" method="POST">
            @csrf
            <div class="fi-modal-body">

                @if($modoPrograma)
                    {{-- Programa fijo (oculto) --}}
                    <input type="hidden" name="curso_id" value="{{ $programaId }}">
                    <input type="hidden" name="estudiante_id" id="fi-estudiante-id" value="">

                    {{-- Buscador de alumno con autocompletado --}}
                    <div class="fi-form-group">
                        <label for="fi-buscar-alumno">Estudiante</label>
                        <div class="fi-autocomplete" id="fi-autocomplete-wrap">
                            <input type="text" id="fi-buscar-alumno" autocomplete="off"
                                   placeholder="Buscar por nombre, cédula o registro..."
                                   data-buscar-url="{{ route('caja.pago.buscar') }}">
                            <div class="fi-autocomplete-dropdown" id="fi-autocomplete-dropdown"></div>
                        </div>
                        {{-- Alumno seleccionado --}}
                        <div class="fi-alumno-selected" id="fi-alumno-selected" style="display:none;">
                            <div class="fi-alumno-selected-info">
                                <strong id="fi-alumno-nombre"></strong>
                                <span id="fi-alumno-datos"></span>
                            </div>
                            <button type="button" class="fi-btn-cambiar-alumno" id="fi-cambiar-alumno">Cambiar</button>
                        </div>
                    </div>
                @else
                    {{-- Alumno fijo → se elige el programa --}}
                    <div class="fi-form-group">
                        <label for="modal-curso">Programa de Postgrado</label>
                        <select id="modal-curso" name="curso_id" required>
                            <option value="">Seleccione un programa...</option>
                            @foreach(\App\Models\Curso::orderBy('nombre')->get() as $curso)
                            <option value="{{ $curso->id }}">{{ $curso->nombre }} ({{ $curso->tipo }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="fi-form-group">
                    <label for="modal-fecha-inscripcion">Fecha de Inscripción</label>
                    <input type="date" id="modal-fecha-inscripcion" name="fecha_inscripcion" required>
                </div>
                <div class="fi-form-group">
                    <label for="modal-modalidad">Modalidad de Pago</label>
                    <select id="modal-modalidad" name="modalidad_pago" required>
                        <option value="">Seleccione la modalidad...</option>
                        <option value="Contado">Contado (100%)</option>
                        <option value="Cuotas">Cuotas</option>
                    </select>
                </div>
                <div class="fi-form-group">
                    <label for="modal-descuento">% de Descuento / Beca</label>
                    <input type="number" id="modal-descuento" name="porcentaje_descuento" min="0" max="100" step="0.01" value="0" placeholder="0.00">
                </div>
            </div>
            <div class="fi-modal-footer">
                <button type="button" class="fi-btn-cancelar" id="fi-cancelar-modal">Cancelar</button>
                <button type="submit" class="fi-btn-guardar">Guardar Inscripción</button>
            </div>
        </form>
    </div>
</div>
