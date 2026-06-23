@extends('layouts.app')

@section('title', 'Registrar Nuevo Estudiante')

@push('styles')
<link rel="stylesheet" href="/css/estudiantes.css">
@endpush

@section('content')


<!-- Top bar: breadcrumb + action buttons -->
<div class="form-top-bar">
    <div class="form-top-bar-left">
        <nav class="breadcrumb-create" style="margin-bottom: 2px;">
            <a href="{{ route('estudiantes.index') }}">Gestión de Alumnos</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-active">Registrar Nuevo Estudiante</span>
        </nav>
        <p class="form-top-desc">Complete los datos del estudiante y opcionalmente realice la inscripción.</p>
    </div>
    <div class="form-top-actions">
        <a href="{{ route('estudiantes.index') }}" class="btn-cancelar">Cancelar</a>
        <button type="submit" class="btn-completar" id="btn-submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span id="btn-text">Guardar Estudiante</span>
        </button>
    </div>
</div>

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-error">
    <ul style="margin: 0; padding-left: 18px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Formulario -->
<form id="form-inscripcion" action="{{ route('estudiantes.store') }}" method="POST">
    @csrf

    <div class="form-grid">

        <!-- Columna Izquierda: 1. DATOS DEL ESTUDIANTE -->
        <div class="form-card form-card-primary">
            <div class="form-card-header">
                <div class="form-card-icon blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <h3 class="form-card-title">1. Datos del Estudiante</h3>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombres">Nombres Completos <span class="required">*</span></label>
                    <input type="text" id="nombres" name="nombres" placeholder="Ej. Carlos Alberto" value="{{ old('nombres') }}" required>
                </div>
                <div class="form-group">
                    <label for="paterno">Apellido Paterno <span class="required">*</span></label>
                    <input type="text" id="paterno" name="paterno" placeholder="Ej. Ruiz" value="{{ old('paterno') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="materno">Apellido Materno</label>
                    <input type="text" id="materno" name="materno" placeholder="Ej. Mendoza" value="{{ old('materno') }}">
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula / ID de Identidad <span class="required">*</span></label>
                    <input type="text" id="cedula" name="cedula" placeholder="00000000000" value="{{ old('cedula') }}" required maxlength="20">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="celular">Teléfono Celular</label>
                    <input type="text" id="celular" name="celular" placeholder="+595 981 123456" value="{{ old('celular') }}">
                </div>
                <div class="form-group">
                    <label for="registro">Registro <span class="required">*</span></label>
                    <input type="text" id="registro" name="registro" placeholder="Ej. PG-2026-0001" value="{{ old('registro') }}" required>
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" placeholder="Notas adicionales sobre el estudiante...">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: 2. DETALLES DE INSCRIPCIÓN -->
        <div class="sidebar-cards">
            <div class="form-card" id="card-inscripcion">
                <!-- Toggle Switch -->
                <div class="toggle-header">
                    <div class="toggle-wrapper">
                        <label class="toggle-switch">
                            <input type="checkbox" id="toggle-inscripcion" name="inscribir_ahora" value="1" {{ old('inscribir_ahora') ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <div class="toggle-text">
                            <strong>¿Inscribir a un programa académico ahora?</strong>
                            <span class="toggle-subtext">Si activas esto, podrás asignar un curso y plan de pago.</span>
                        </div>
                    </div>
                </div>

                <div id="inscripcion-fields" class="{{ old('inscribir_ahora') ? '' : 'is-disabled' }}">
                    <!-- Selección de Programa -->
                    <div class="form-card-section">
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="curso_id">Programa de Postgrado <span class="required">*</span></label>
                                <select id="curso_id" name="curso_id">
                                    <option value="">Seleccione un programa...</option>
                                    @foreach($cursos as $curso)
                                    <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                                        {{ $curso->nombre }} ({{ $curso->tipo }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-group">
                                <label for="fecha_inscripcion">Fecha de Inscripción <span class="required">*</span></label>
                                <input type="date" id="fecha_inscripcion" name="fecha_inscripcion" value="{{ old('fecha_inscripcion', date('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Configuración Financiera -->
                    <div class="form-card-section">
                        <div class="form-card-header">
                            <div class="form-card-icon inscripcion-blue" style="width: 32px; height: 32px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                            </div>
                            <h4 class="form-card-subtitle">Plan de Pago</h4>
                        </div>

                        <div class="form-row full">
                            <div class="form-group">
                                <label for="modalidad_pago">Modalidad de Pago <span class="required">*</span></label>
                                <select id="modalidad_pago" name="modalidad_pago">
                                    <option value="Contado" {{ old('modalidad_pago') == 'Contado' ? 'selected' : '' }}>Contado (100%)</option>
                                    <option value="Cuotas" {{ old('modalidad_pago') == 'Cuotas' ? 'selected' : '' }}>Cuotas</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-group">
                                <label for="descuento">% de Descuento / Beca</label>
                                <div class="descuento-input-wrapper">
                                    <input type="number" id="descuento" name="descuento_porcentaje" value="{{ old('descuento_porcentaje', 0) }}" min="0" max="100">
                                    <span class="suffix">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="/js/estudiantes.js"></script>

@endsection
