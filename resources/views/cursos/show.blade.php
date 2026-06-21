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
                        <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion->id]) }}" class="btn-accion btn-caja" title="Ver Caja">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </a>

                        @if($inscripcion->estado_academico === 'Retirado')
                        {{-- BOTÓN REACTIVAR --}}
                        <form action="{{ route('programas.reactivar', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-reactivar-inline" style="display:inline;">
                            @csrf
                            <button type="button" class="btn-accion btn-reactivar btn-reactivar-curso" title="Reactivar en este curso"
                                    data-nombre="{{ $estudiante->nombre_completo }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23 4 23 10 17 10"/>
                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                </svg>
                            </button>
                        </form>
                        @else
                        {{-- BOTÓN DAR DE BAJA (de este curso) --}}
                        <form action="{{ route('programas.baja', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-baja-curso" style="display:inline;">
                            @csrf
                            <button type="button" class="btn-accion btn-baja btn-baja-curso" title="Retirar de este curso"
                                    data-nombre="{{ $estudiante->nombre_completo }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="8" y1="12" x2="16" y2="12"/>
                                </svg>
                            </button>
                        </form>
                        @endif

                        {{-- BOTÓN CAMBIAR TIPO / CONTINUAR --}}
                        @php
                            $puedeLimitar = \App\Models\Inscripcion::TIPO_ORDER[$inscripcion->tipo_inscripcion] > 1;
                            $puedeContinuar = \App\Models\Inscripcion::TIPO_ORDER[$inscripcion->tipo_inscripcion] < 3
                                && $inscripcion->estado_academico !== 'Retirado';
                        @endphp
                        @if($puedeLimitar || $puedeContinuar)
                        <div class="dropdown-accion" style="position:relative;display:inline-block;">
                            <button type="button" class="btn-accion btn-tipo-inscripcion" title="Cambiar tipo de inscripción"
                                    data-inscripcion-id="{{ $inscripcion->id }}"
                                    data-estudiante-nombre="{{ $estudiante->nombre_completo }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu-tipo" id="dropdown-tipo-{{ $inscripcion->id }}" style="display:none;position:fixed;z-index:1000;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);min-width:200px;padding:6px 0;">
                                @if($puedeLimitar)
                                    @if($inscripcion->tipo_inscripcion === 'Maestría')
                                    <form action="{{ route('programas.cambiar.tipo', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-cambiar-tipo">
                                        @csrf
                                        <input type="hidden" name="nuevo_tipo" value="Diplomado">
                                        <button type="button" class="dropdown-item btn-limitar" style="display:block;width:100%;text-align:left;padding:8px 14px;border:none;background:none;cursor:pointer;font-size:13px;"
                                                data-nombre="{{ $estudiante->nombre_completo }}" data-accion="limitar-diplomado">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;vertical-align:middle;">
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                            Solo Diplomado
                                        </button>
                                    </form>
                                    <form action="{{ route('programas.cambiar.tipo', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-cambiar-tipo">
                                        @csrf
                                        <input type="hidden" name="nuevo_tipo" value="Especialidad">
                                        <button type="button" class="dropdown-item btn-limitar" style="display:block;width:100%;text-align:left;padding:8px 14px;border:none;background:none;cursor:pointer;font-size:13px;"
                                                data-nombre="{{ $estudiante->nombre_completo }}" data-accion="limitar-especialidad">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;vertical-align:middle;">
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                            Solo Especialidad
                                        </button>
                                    </form>
                                    @elseif($inscripcion->tipo_inscripcion === 'Especialidad')
                                    <form action="{{ route('programas.cambiar.tipo', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-cambiar-tipo">
                                        @csrf
                                        <input type="hidden" name="nuevo_tipo" value="Diplomado">
                                        <button type="button" class="dropdown-item btn-limitar" style="display:block;width:100%;text-align:left;padding:8px 14px;border:none;background:none;cursor:pointer;font-size:13px;"
                                                data-nombre="{{ $estudiante->nombre_completo }}" data-accion="limitar-diplomado">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;vertical-align:middle;">
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                            Solo Diplomado
                                        </button>
                                    </form>
                                    @endif
                                @endif
                                @if($puedeContinuar)
                                    @if($inscripcion->tipo_inscripcion === 'Diplomado')
                                    <form action="{{ route('programas.continuar.fase', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-continuar-fase">
                                        @csrf
                                        <button type="button" class="dropdown-item btn-continuar" style="display:block;width:100%;text-align:left;padding:8px 14px;border:none;background:none;cursor:pointer;font-size:13px;"
                                                data-nombre="{{ $estudiante->nombre_completo }}" data-accion="continuar-especialidad">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;vertical-align:middle;">
                                                <polyline points="23 4 23 10 17 10"/>
                                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                            </svg>
                                            Continuar a Especialidad
                                        </button>
                                    </form>
                                    @elseif($inscripcion->tipo_inscripcion === 'Especialidad')
                                    <form action="{{ route('programas.continuar.fase', [$programa->id, $inscripcion->id]) }}" method="POST" class="form-continuar-fase">
                                        @csrf
                                        <button type="button" class="dropdown-item btn-continuar" style="display:block;width:100%;text-align:left;padding:8px 14px;border:none;background:none;cursor:pointer;font-size:13px;"
                                                data-nombre="{{ $estudiante->nombre_completo }}" data-accion="continuar-maestria">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;vertical-align:middle;">
                                                <polyline points="23 4 23 10 17 10"/>
                                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                            </svg>
                                            Continuar a Maestría
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endif
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

{{-- MODAL DE CONFIRMACIÓN GENÉRICO (baja/reactivar/limitar/continuar) --}}
<div class="fi-modal-overlay" id="modal-accion-inscripcion">
    <div class="fi-modal-box" style="max-width: 480px;">
        <div class="fi-modal-header" id="mai-header">
            <h3 class="fi-modal-title" id="mai-titulo">Confirmar Acción</h3>
            <button type="button" class="fi-modal-close" id="mai-cerrar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="fi-modal-body">
            <p id="mai-texto" style="font-size:14px;color:#374151;margin:0 0 8px;"></p>
            <p id="mai-subtexto" style="font-size:13px;color:#6b7280;margin:0;"></p>
        </div>
        <div class="fi-modal-footer">
            <button type="button" class="fi-btn-cancelar" id="mai-cancelar">Cancelar</button>
            <button type="button" class="fi-btn-guardar" id="mai-confirmar" style="background:#dc2626;">Confirmar</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/form-inscripcion-programa.js') }}?v={{ filemtime(public_path('js/form-inscripcion-programa.js')) }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Modal Confirmación Genérico ──
    var maiModal = document.getElementById('modal-accion-inscripcion');
    var maiTitulo = document.getElementById('mai-titulo');
    var maiTexto = document.getElementById('mai-texto');
    var maiSubtexto = document.getElementById('mai-subtexto');
    var maiConfirmar = document.getElementById('mai-confirmar');
    var maiCancelar = document.getElementById('mai-cancelar');
    var maiCerrar = document.getElementById('mai-cerrar');
    var maiForm = null;

    function maiAbrir(titulo, texto, subtexto, color, btnText) {
        maiTitulo.textContent = titulo;
        maiTexto.textContent = texto;
        maiSubtexto.textContent = subtexto || '';
        maiConfirmar.style.background = color || '#dc2626';
        maiConfirmar.textContent = btnText || 'Confirmar';
        maiModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function maiCerrarFn() {
        maiModal.classList.remove('active');
        document.body.style.overflow = '';
        maiForm = null;
    }

    if (maiConfirmar) {
        maiConfirmar.addEventListener('click', function() {
            if (maiForm) { maiForm.submit(); }
            maiCerrarFn();
        });
    }
    if (maiCancelar) maiCancelar.addEventListener('click', maiCerrarFn);
    if (maiCerrar) maiCerrar.addEventListener('click', maiCerrarFn);
    if (maiModal) maiModal.addEventListener('click', function(e) { if (e.target === maiModal) maiCerrarFn(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && maiModal && maiModal.classList.contains('active')) maiCerrarFn();
    });

    // ── Botón Baja (de este curso) ──
    document.querySelectorAll('.btn-baja-curso').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            maiForm = this.closest('form');
            maiAbrir(
                'Retirar del Programa',
                '¿Está seguro que desea retirar a "' + nombre + '" de este programa?',
                'Se condonarán las cuotas futuras y la inscripción quedará como "Retirado". Las cuotas vencidas y pagadas no se modifican. Esta acción es reversible (puede reactivar después).',
                '#dc2626',
                'Sí, retirar'
            );
        });
    });

    // ── Botón Reactivar (de este curso) ──
    document.querySelectorAll('.btn-reactivar-curso').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            maiForm = this.closest('form');
            maiAbrir(
                'Reactivar Inscripción',
                '¿Desea reactivar la inscripción de "' + nombre + '" en este programa?',
                'Las cuotas condonadas volverán a "Pendiente" con nuevas fechas de vencimiento desde hoy. Las cuotas pagadas se mantienen intactas.',
                '#059669',
                'Sí, reactivar'
            );
        });
    });

    // ── Dropdown tipo inscripción (position:fixed con cálculo JS) ──
    function posicionarDropdown(btn, dropdown) {
        var rect = btn.getBoundingClientRect();
        var dropdownWidth = 210;
        var left = rect.right - dropdownWidth;
        if (left < 10) left = 10;
        var top = rect.bottom + 4;
        // Si no cabe abajo, mostrar arriba
        if (top + 250 > window.innerHeight) {
            top = rect.top - 250;
            if (top < 10) top = 10;
        }
        dropdown.style.left = left + 'px';
        dropdown.style.top = top + 'px';
    }

    document.querySelectorAll('.btn-tipo-inscripcion').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var inscId = this.getAttribute('data-inscripcion-id');
            var dropdown = document.getElementById('dropdown-tipo-' + inscId);
            if (dropdown) {
                var visible = dropdown.style.display === 'block';
                // Cerrar todos los demás dropdowns
                document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) { d.style.display = 'none'; d.style.left = ''; d.style.top = ''; });
                if (!visible) {
                    posicionarDropdown(this, dropdown);
                    dropdown.style.display = 'block';
                }
            }
        });
    });

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-tipo-inscripcion') && !e.target.closest('.dropdown-menu-tipo')) {
            document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) { d.style.display = 'none'; d.style.left = ''; d.style.top = ''; });
        }
    });

    // Reposicionar al hacer scroll (opcional, para mantener posición correcta)
    var scrollTimer;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) {
                if (d.style.display === 'block') {
                    // Buscar el botón asociado por el ID
                    var id = d.id.replace('dropdown-tipo-', '');
                    var btn = document.querySelector('.btn-tipo-inscripcion[data-inscripcion-id="' + id + '"]');
                    if (btn) posicionarDropdown(btn, d);
                }
            });
        }, 50);
    });

    // Al seleccionar una opción (click en .btn-limitar o .btn-continuar), cerrar dropdown
    document.querySelectorAll('.btn-limitar, .btn-continuar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) { d.style.display = 'none'; d.style.left = ''; d.style.top = ''; });
        });
    });

    // ── Botones Limitar (Solo Diplomado / Solo Especialidad) ──
    document.querySelectorAll('.btn-limitar').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            var accion = this.getAttribute('data-accion');
            var tipoLabel = accion === 'limitar-diplomado' ? 'Solo Diplomado' : 'Solo Especialidad';
            maiForm = this.closest('form');
            maiAbrir(
                'Limitar a ' + tipoLabel,
                '¿Está seguro de limitar la inscripción de "' + nombre + '" a "' + tipoLabel + '"?',
                'Se condonarán todas las cuotas de fases posteriores. Las cuotas ya pagadas se mantienen intactas. Esta acción es reversible con "Continuar a la siguiente fase".',
                '#d97706',
                'Sí, ' + (accion === 'limitar-diplomado' ? 'limitar a Diplomado' : 'limitar a Especialidad')
            );
        });
    });

    // ── Botones Continuar ──
    document.querySelectorAll('.btn-continuar').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            var accion = this.getAttribute('data-accion');
            var tipoLabel = accion === 'continuar-especialidad' ? 'Especialidad' : 'Maestría';
            maiForm = this.closest('form');
            maiAbrir(
                'Continuar a ' + tipoLabel,
                '¿Desea que "' + nombre + '" continúe a "' + tipoLabel + '"?',
                'Las cuotas condonadas de la nueva fase volverán a "Pendiente" con fechas de vencimiento recalculadas desde hoy.',
                '#1B4FD8',
                'Sí, continuar'
            );
        });
    });

});
</script>

@endsection
