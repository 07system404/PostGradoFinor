@extends('layouts.app')

@section('title', 'Estudiantes del Programa - PostGrado Pro')

@section('content')

<link rel="stylesheet" href="{{ asset('css/cursos_show.css') }}?v={{ filemtime(public_path('css/cursos_show.css')) }}">
<link rel="stylesheet" href="{{ asset('css/form-inscripcion-programa.css') }}?v={{ filemtime(public_path('css/form-inscripcion-programa.css')) }}">

<style>
    .curso-show-table-header-right {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .btn-inscribir-estudiante {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border: none;
        border-radius: 8px;
        background: #1B4FD8;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        box-shadow: 0 4px 12px rgba(27, 79, 216, 0.25);
    }
    .btn-inscribir-estudiante:hover {
        background: #1239A8;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(27, 79, 216, 0.35);
    }
    .btn-inscribir-estudiante:active { transform: translateY(0); }
    .btn-inscribir-estudiante svg { width: 15px; height: 15px; }
</style>

<!-- Breadcrumb -->
<nav class="breadcrumb-perfil" style="margin-bottom: 16px;">
    <a href="{{ route('programas.index') }}">Programas Académicos</a>
    <span class="breadcrumb-sep">›</span>
    <span class="active">{{ $programa->nombre }}</span>
</nav>

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<!-- Cabecera del Curso -->
<div class="curso-show-header">
    <div class="curso-show-header-left">
        <div class="curso-show-tipo-badge tipo-{{ strtolower($programa->tipo) }}">
            {{ $programa->tipo }}
        </div>
        <h1 class="curso-show-title">{{ $programa->nombre }}</h1>
        <div class="curso-show-meta">
            <span class="curso-show-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="4 7 4 4 20 4 20 7"/>
                    <line x1="9" y1="20" x2="15" y2="20"/>
                    <line x1="12" y1="4" x2="12" y2="20"/>
                </svg>
                V{{ $programa->version }} · Edición {{ $programa->edicion }}
            </span>
            <span class="curso-show-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Periodo: {{ $programa->periodo }}
            </span>
            <span class="curso-show-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                {{ $programa->inscripciones->count() }} / {{ $programa->cupo }} Alumnos
            </span>
        </div>
    </div>
    <!-- Sin botones - cabecera limpia -->
</div>

<!-- Tabla de Estudiantes Inscritos -->
<div class="curso-show-table-card" style="margin-top: 0;">
    <div class="curso-show-table-header">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Lista de Estudiantes Inscritos
        </h2>
        <div class="curso-show-table-header-right">
            <span class="curso-show-count">{{ $programa->inscripciones->count() }} estudiantes</span>
            <button type="button" class="btn-inscribir-estudiante" id="btn-nueva-inscripcion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Inscribir Estudiante
            </button>
        </div>
    </div>

    <table class="curso-show-table">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Registro</th>
                <th>Cédula</th>
                <th>Estado Académico</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programa->inscripciones as $inscripcion)
                @php
                    $estudiante = $inscripcion->estudiante;
                    $claseEstado = match($inscripcion->estado_academico) {
                        'Activo' => 'estado-alumno-activo',
                        'Pendiente' => 'estado-alumno-pendiente',
                        'Egresado' => 'estado-alumno-egresado',
                        'Retirado' => 'estado-alumno-retirado',
                        'Congelado' => 'estado-alumno-congelado',
                        default => 'estado-alumno-pendiente',
                    };
                    $iconoEstado = match($inscripcion->estado_academico) {
                        'Activo' => 'check',
                        'Pendiente' => 'clock',
                        'Egresado' => 'check-double',
                        'Retirado' => 'x',
                        'Congelado' => 'pause',
                        default => 'clock',
                    };
                @endphp
            <tr>
                <td>
                    <div class="alumno-info">
                        <div class="alumno-avatar">
                            {{ strtoupper(substr($estudiante->nombres, 0, 1)) }}{{ strtoupper(substr($estudiante->paterno, 0, 1)) }}
                        </div>
                        <div class="alumno-nombre">
                            <span class="alumno-nombre-text">{{ $estudiante->nombre_completo }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="alumno-registro">{{ $estudiante->registro }}</span>
                </td>
                <td>
                    <span class="alumno-cedula">{{ $estudiante->cedula }}</span>
                </td>
                <td>
                    <span class="alumno-estado {{ $claseEstado }}">
                        @if($iconoEstado === 'check')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        @elseif($iconoEstado === 'clock')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        @elseif($iconoEstado === 'check-double')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="18 6 9 17 4 12"/>
                            <polyline points="22 6 13 17 11 15"/>
                        </svg>
                        @elseif($iconoEstado === 'x')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        @elseif($iconoEstado === 'pause')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="6" y="4" width="4" height="16"/>
                            <rect x="14" y="4" width="4" height="16"/>
                        </svg>
                        @endif
                        {{ strtoupper($inscripcion->estado_academico) }}
                    </span>
                </td>
                <td data-label="Acciones">
                    <div class="acciones">
                        <a href="{{ route('estudiantes.show', ['estudiante' => $estudiante->id, 'from' => 'programa', 'programa_id' => $programa->id]) }}"
                           class="btn-accion" title="Ver perfil del alumno">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                        <a href="{{ route('caja.index', ['buscar' => $estudiante->cedula]) }}" class="btn-accion btn-caja" title="Ver Caja">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </a>
                        <button type="button" class="btn-accion btn-baja btn-desinscribir"
                                title="Quitar de este curso"
                                data-url="{{ route('programas.desinscribir', [$programa->id, $inscripcion->id]) }}"
                                data-nombre="{{ $estudiante->nombre_completo }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="curso-show-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <h3>No hay estudiantes inscritos</h3>
                    <p>Este programa aún no tiene alumnos registrados.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal reutilizable en modo "programa fijo": se busca y elige al alumno --}}
<x-form-inscripcion-programa :programa-id="$programa->id" />

{{-- Formulario oculto que ejecuta la desinscripción (action se asigna por JS) --}}
<form id="form-desinscribir" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- Modal de confirmación de desinscripción (reutiliza estilos fi-modal) --}}
<div class="fi-modal-overlay" id="modal-desinscribir">
    <div class="fi-modal-box" style="max-width: 440px;">
        <div class="fi-modal-header">
            <h3 class="fi-modal-title">Desinscribir Estudiante</h3>
            <button type="button" class="fi-modal-close" id="desinsc-cerrar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="fi-modal-body">
            <p id="desinsc-texto" style="font-size:14px;color:#374151;margin:0 0 8px;"></p>
            <p style="font-size:13px;color:#6b7280;margin:0;">
                Solo se quita al alumno de <strong>este programa</strong>; sus datos personales
                y sus inscripciones en otros programas no se modifican.
            </p>
        </div>
        <div class="fi-modal-footer">
            <button type="button" class="fi-btn-cancelar" id="desinsc-cancelar">Cancelar</button>
            <button type="button" class="fi-btn-guardar" id="desinsc-confirmar"
                    style="background:#dc2626;">Sí, desinscribir</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/form-inscripcion-programa.js') }}?v={{ filemtime(public_path('js/form-inscripcion-programa.js')) }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modal-desinscribir');
    var form = document.getElementById('form-desinscribir');
    var texto = document.getElementById('desinsc-texto');
    var btnConfirmar = document.getElementById('desinsc-confirmar');
    var btnCancelar = document.getElementById('desinsc-cancelar');
    var btnCerrar = document.getElementById('desinsc-cerrar');

    function abrir() { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
    function cerrar() { modal.classList.remove('active'); document.body.style.overflow = ''; }

    document.querySelectorAll('.btn-desinscribir').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            form.setAttribute('action', url);
            texto.textContent = '¿Está seguro que desea desinscribir a "' + nombre + '" de este programa?';
            abrir();
        });
    });

    if (btnConfirmar) btnConfirmar.addEventListener('click', function() {
        btnConfirmar.disabled = true;
        btnConfirmar.textContent = 'Desinscribiendo...';
        form.submit();
    });
    if (btnCancelar) btnCancelar.addEventListener('click', cerrar);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrar);
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) cerrar(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) cerrar();
    });
});
</script>

@endsection
