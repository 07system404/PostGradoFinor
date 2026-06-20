@extends('layouts.app')

@section('title', 'Perfil del Alumno')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')

<!-- Breadcrumb -->
<nav class="breadcrumb-perfil">
    <a href="{{ route('estudiantes.index') }}">Gestión de Alumnos</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-active">Detalle del Alumno</span>
</nav>

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<!-- Grid Principal -->
<div class="perfil-grid">
    <!-- Datos Personales -->
    <div class="perfil-card">
        <div class="perfil-card-header">
            <h2 class="perfil-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Datos Personales
            </h2>
            <button type="submit" form="form-datos-personales" class="btn-actualizar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                <span>Actualizar</span>
            </button>
        </div>

        <form id="form-datos-personales" action="{{ route('estudiantes.update', $estudiante->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="perfil-form-row">
                <div class="perfil-form-group">
                    <label for="nombres">Nombre</label>
                    <input type="text" id="nombres" name="nombres" value="{{ $estudiante->nombres }}" required>
                </div>
                <div class="perfil-form-group">
                    <label for="paterno">Apellido Paterno</label>
                    <input type="text" id="paterno" name="paterno" value="{{ $estudiante->paterno }}" required>
                </div>
                <div class="perfil-form-group">
                    <label for="materno">Apellido Materno</label>
                    <input type="text" id="materno" name="materno" value="{{ $estudiante->materno ?? '-' }}">
                </div>
            </div>

            <div class="perfil-form-row dos">
                <div class="perfil-form-group">
                    <label for="registro">Código de Registro</label>
                    <input type="text" id="registro" value="{{ $estudiante->registro }}" readonly style="background: var(--gray-100); color: var(--gray-500);">
                </div>
                <div class="perfil-form-group">
                    <label for="cedula">Cédula</label>
                    <input type="text" id="cedula" name="cedula" value="{{ $estudiante->cedula }}" required maxlength="20">
                </div>
            </div>

            <div class="perfil-form-row dos">
                <div class="perfil-form-group">
                    <label for="celular">Teléfono</label>
                    <input type="text" id="celular" name="celular" value="{{ $estudiante->celular ?? '+595 981 123456' }}">
                </div>
                <div class="perfil-form-group">
                    <label for="estado">Estado del Estudiante</label>
                    <select id="estado" name="activo">
                        <option value="1" {{ $estudiante->activo ? 'selected' : '' }}>ACTIVO</option>
                        <option value="0" {{ !$estudiante->activo ? 'selected' : '' }}>INACTIVO</option>
                    </select>
                </div>
            </div>

            <div class="perfil-form-row full">
                <div class="perfil-form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" placeholder="Sin observaciones...">{{ $estudiante->observaciones }}</textarea>
                </div>
            </div>
        </form>
    </div>

    <!-- Programa Académico -->
    <div class="perfil-card">
        <div class="perfil-card-header">
            <h2 class="perfil-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/>
                </svg>
                Programa Académico
            </h2>
            <button type="button" class="btn-nueva-inscripcion-perfil" id="btn-nueva-inscripcion">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nueva Inscripción</span>
            </button>
        </div>

        <div class="programas-list">
            @forelse($estudiante->inscripciones as $inscripcion)
            <div class="programa-item">
                <div class="programa-item-header">
                    <span class="programa-item-nombre">{{ $inscripcion->curso->nombre }}</span>
                    @php
                        $claseEstado = match($inscripcion->estado_academico) {
                            'Activo' => 'estado-activo',
                            'Pendiente' => 'estado-pendiente',
                            'Mora' => 'estado-mora',
                            default => 'estado-pendiente',
                        };
                    @endphp
                    <span class="estado-badge {{ $claseEstado }}">{{ strtoupper($inscripcion->estado_academico) }}</span>
                </div>
                <div class="programa-item-fecha">
                    Inscrito: {{ $inscripcion->fecha_inscripcion->format('d \d\e F, Y') }}
                </div>
            </div>
            @empty
            <div class="programa-item" style="text-align: center; color: var(--gray-500);">
                No hay inscripciones registradas.
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Gestión de Documentación -->
<div class="perfil-grid full">
    <div class="perfil-card">
        <div class="perfil-card-header">
            <h2 class="perfil-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Gestión de Documentación
            </h2>
            <button class="btn-cargar-doc" id="btn-cargar-documento">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span>Cargar Documento</span>
            </button>
        </div>

        <table class="doc-table">
            <thead>
                <tr>
                    <th>NOMBRE DEL DOCUMENTO</th>
                    <th>TIPO</th>
                    <th>ESTADO</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $documentosEsperados = [
                        ['nombre' => 'Grado de Bachiller', 'tipo' => 'Académico'],
                        ['nombre' => 'Certificado de Idiomas', 'tipo' => 'Requisito'],
                        ['nombre' => 'Copia de DNI / Pasaporte', 'tipo' => 'Identidad'],
                    ];
                    $docsSubidos = $estudiante->documentos->keyBy('tipo');
                @endphp

                @foreach($documentosEsperados as $docEsperado)
                    @php
                        $docSubido = $docsSubidos[$docEsperado['nombre']] ?? null;
                        $estadoDoc = $docSubido ? 'Subido' : 'Pendiente';
                        $claseDoc = $docSubido ? 'subido' : 'pendiente';
                    @endphp
                <tr>
                    <td>
                        <div class="doc-nombre">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            {{ $docEsperado['nombre'] }}
                        </div>
                    </td>
                    <td>
                        <span class="doc-tipo">{{ $docEsperado['tipo'] }}</span>
                    </td>
                    <td>
                        <span class="doc-estado {{ $claseDoc }}">
                            @if($docSubido)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            @endif
                            {{ $estadoDoc }}
                        </span>
                    </td>
                    <td>
                        <div class="doc-acciones">
                            @if($docSubido)
                            <button class="btn-doc-action btn-ver-doc" data-url="{{ asset('storage/' . $docSubido->ruta_archivo) }}" title="Ver">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button class="btn-doc-action btn-descargar-doc" data-url="{{ asset('storage/' . $docSubido->ruta_archivo) }}" title="Descargar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </button>
                            @else
                            <span style="color: var(--gray-400); font-size: 12px;">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Cargar Documento -->
<div class="modal-overlay" id="modal-cargar-documento">
    <div class="modal-container" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Cargar Nuevo Documento</h3>
            <button type="button" class="modal-close" id="cerrar-modal-doc">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="form-cargar-documento" action="{{ route('documentos.store', $estudiante->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-row full" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="doc-tipo">Tipo de Documento <span style="color: var(--danger);">*</span></label>
                        <select id="doc-tipo" name="tipo" required>
                            <option value="">Seleccione un tipo...</option>
                            <option value="Grado de Bachiller">Grado de Bachiller</option>
                            <option value="Certificado de Idiomas">Certificado de Idiomas</option>
                            <option value="Copia de DNI / Pasaporte">Copia de DNI / Pasaporte</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="form-row full" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="doc-archivo">Archivo (PNG, JPG, PDF) <span style="color: var(--danger);">*</span></label>
                        <div class="doc-dropzone" id="doc-dropzone">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--gray-400);">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p style="margin: 8px 0; font-size: 14px; color: var(--gray-700); font-weight: 500;">Arrastra el archivo aquí</p>
                            <p style="margin: 0; font-size: 12px; color: var(--gray-400);">o haz clic para seleccionar</p>
                        </div>
                        <input type="file" id="doc-archivo" name="archivo" accept=".jpg,.jpeg,.png,.pdf" style="display: none;" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="cancelar-modal-doc">Cancelar</button>
                <button type="submit" class="btn-completar">Cargar Documento</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nueva Inscripción -->
<div class="modal-overlay" id="modal-nueva-inscripcion">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Nueva Inscripción en Curso</h3>
            <button type="button" class="modal-close" id="cerrar-modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="form-nueva-inscripcion" action="{{ route('estudiantes.inscribir', $estudiante->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-row full" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="modal-curso">Programa de Postgrado</label>
                        <select id="modal-curso" name="curso_id" required>
                            <option value="">Seleccione un programa...</option>
                            @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}">{{ $curso->nombre }} ({{ $curso->tipo }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="modal-tipo">Tipo de Inscripción</label>
                        <select id="modal-tipo" name="tipo_inscripcion" required>
                            <option value="Diplomado">Diplomado</option>
                            <option value="Especialidad">Especialidad</option>
                            <option value="Maestría" selected>Maestría</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal-fecha-inscripcion">Fecha de Inscripción</label>
                        <input type="date" id="modal-fecha-inscripcion" name="fecha_inscripcion" required>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="modal-modalidad">Modalidad de Pago</label>
                        <select id="modal-modalidad" name="modalidad_pago" required>
                            <option value="Contado">Contado (100%)</option>
                            <option value="Cuotas">Cuotas</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="cancelar-modal">Cancelar</button>
                <button type="submit" class="btn-completar">Guardar Inscripción</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/perfil.js') }}"></script>
@endpush
