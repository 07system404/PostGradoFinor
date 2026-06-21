@extends('layouts.app')

@section('title', 'Gestion de Alumnos')

@section('content')

<link rel="stylesheet" href="{{ asset('css/estudiantes_index.css') }}">
<link rel="stylesheet" href="{{ asset('css/form-cargar-documento.css') }}">

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<!-- Barra de búsqueda -->
<div class="programas-search-bar">
    <form id="form-buscar-alumnos" action="{{ route('estudiantes.index') }}" method="GET" class="search-bar-form">
        <div class="search-bar-input-group">
            <svg class="search-bar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" id="buscar-alumno" placeholder="Buscar alumnos por nombre o registro..." 
                   value="{{ request('search') }}">
        </div>
        <div class="search-bar-select-group">
            <svg class="search-bar-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="8" y1="12" x2="20" y2="12"/>
                <line x1="12" y1="18" x2="20" y2="18"/>
            </svg>
            <select id="filtro-curso" name="curso_id" class="filter-estado">
                <option value="">Filtrar por Programa/Curso</option>
                @foreach($cursos as $curso)
                <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                    {{ $curso->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('estudiantes.create') }}" class="btn-nuevo-programa">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            + Inscripción Rápida
        </a>
    </form>
</div>

<!-- Card Table -->
<div class="card-table">
    <div class="card-table-header">
        <div class="cohorte-info">
            <h3>Gestión de Alumnos</h3>
            <span class="badge-total">TOTAL: {{ $totalAlumnos }}</span>
            <span class="badge-solventes">SOLVENTES: {{ $solventes }}</span>
        </div>
        <button class="btn-filter" title="Filtros avanzados">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="21" x2="4" y2="14"/>
                <line x1="4" y1="10" x2="4" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12" y2="3"/>
                <line x1="20" y1="21" x2="20" y2="16"/>
                <line x1="20" y1="12" x2="20" y2="3"/>
                <line x1="1" y1="14" x2="7" y2="14"/>
                <line x1="9" y1="8" x2="15" y2="8"/>
                <line x1="17" y1="16" x2="23" y2="16"/>
            </svg>
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>CÓDIGO REG.</th>
                    <th>ALUMNO</th>
                    <th>PROGRAMA ACADÉMICO</th>
                    <th>ESTADO</th>
                    <th>DOCUMENTACIÓN</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estudiantes as $estudiante)
                    @php
                        $inscripcion = $estudiante->inscripciones->first();
                        $programaPrincipal = $inscripcion ? $inscripcion->curso->nombre : 'Sin inscripción';

                        // CORRECCIÓN 1: estado calculado sobre TODAS las inscripciones
                        $todasRetiradas = $estudiante->inscripciones->every(fn($i) => $i->estado_academico === 'Retirado');
                        $tieneActiva = $estudiante->inscripciones->contains(fn($i) => $i->estado_academico !== 'Retirado');
                        if ($estudiante->inscripciones->isEmpty()) {
                            $estadoAcademico = 'Sin Inscripción';
                        } elseif ($todasRetiradas) {
                            $estadoAcademico = 'Retirado';
                        } elseif ($tieneActiva) {
                            $estadoAcademico = 'Activo';
                        } else {
                            $estadoAcademico = 'Pendiente';
                        }

                        $claseEstado = match($estadoAcademico) {
                            'Activo' => 'estado-activo',
                            'Pendiente' => 'estado-pendiente',
                            'Retirado' => 'estado-retirado',
                            'Congelado' => 'estado-congelado',
                            'Sin Inscripción' => 'estado-pendiente',
                            default => 'estado-pendiente',
                        };

                        $totalDocs = $estudiante->documentos->count();
                        $docCompletado = $totalDocs >= 3;
                    @endphp
                <tr class="{{ $estudiante->activo ? '' : 'fila-inactiva' }}">
                    <td data-label="Código Reg.">
                        <span class="codigo-reg">{{ $estudiante->registro }}</span>
                    </td>
                    <td data-label="Alumno">
                        <span class="nombre-alumno">{{ $estudiante->nombre_completo }}</span>
                        @if(!$estudiante->activo)
                        <span class="badge-inactivo">INACTIVO</span>
                        @endif
                    </td>
                    <td data-label="Programa">
                        <div class="programa-principal">{{ $programaPrincipal }}</div>                            @if($estudiante->inscripciones->count() > 1)
                            @foreach($estudiante->inscripciones->skip(1) as $insc)
                            <span class="programa-secundario">{{ strtoupper($insc->curso?->nombre ?? 'N/A') }}</span>
                            @endforeach
                            @endif
                    </td>
                    <td data-label="Estado">
                        <span class="estado-badge {{ $claseEstado }}">
                            @if($estadoAcademico == 'Activo')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            @elseif($estadoAcademico == 'Pendiente')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            @elseif($estadoAcademico == 'Retirado')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                            @endif
                            {{ strtoupper($estadoAcademico) }}
                        </span>
                    </td>
                    <td data-label="Documentación">
                        <div class="doc-status {{ $docCompletado ? 'completado' : 'pendiente' }}">
                            @if($docCompletado)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            @endif
                            <span>{{ $docCompletado ? '3/3 COMPLETADO' : $totalDocs . '/3 PENDIENTE' }}</span>
                        </div>
                        <button class="btn-subir-doc" data-alumno-nombre="{{ $estudiante->nombre_completo }}" data-alumno-id="{{ $estudiante->id }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <span>SUBIR</span>
                        </button>
                    </td>
                    <td data-label="Acciones">
                        <div class="acciones">
                            <a href="{{ route('estudiantes.show', $estudiante->id) }}" class="btn-accion" title="Ver detalle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion?->id]) }}" class="btn-accion btn-caja" title="Ver Caja">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </a>
                            @if($estudiante->inscripciones->isNotEmpty())
                                @if($todasRetiradas)
                                {{-- BOTÓN REACTIVAR (todas las inscripciones están Retiradas) --}}
                                <button type="button" class="btn-accion btn-reactivar btn-reactivar-multiple" title="Reactivar"
                                        data-nombre="{{ $estudiante->nombre_completo }}"
                                        data-reactivar-url="{{ route('inscripciones.reactivar', $estudiante->inscripciones->first()->id) }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="23 4 23 10 17 10"/>
                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                    </svg>
                                </button>
                                @else
                                {{-- BOTÓN DAR DE BAJA (al menos una inscripción activa) --}}
                                <button type="button" class="btn-accion btn-baja" title="Dar de baja de todos los cursos"
                                        data-nombre="{{ $estudiante->nombre_completo }}"
                                        data-baja-url="{{ route('estudiantes.baja.completa', $estudiante->id) }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="8" y1="12" x2="16" y2="12"/>
                                    </svg>
                                </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        No se encontraron alumnos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-footer">
        <span class="pagination-info">
            Mostrando {{ $estudiantes->firstItem() ?? 0 }} a {{ $estudiantes->lastItem() ?? 0 }} de {{ $estudiantes->total() }} alumnos
        </span>
        <div class="pagination-controls">
            {{ $estudiantes->links() }}
        </div>
    </div>
</div>

<!-- Modal Confirmación Baja/Reactivar -->
<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-container modal-container-sm">
        <div class="modal-header" id="modal-confirm-header">
            <h3 id="modal-confirm-titulo">Confirmar</h3>
            <button type="button" class="modal-close" id="cerrar-modal-confirm">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon" id="modal-confirm-icon"></div>
            <p class="confirm-text" id="modal-confirm-text"></p>
            <p class="confirm-subtext" id="modal-confirm-subtext"></p>
        </div>
        <div class="modal-footer" id="modal-confirm-footer">
            <button type="button" class="btn-cancelar" id="btn-cancelar-confirm">Cancelar</button>
            <button type="button" class="btn-confirmar" id="btn-confirmar-accion">Confirmar</button>
        </div>
    </div>
</div>

<x-form-cargar-documento />

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Busqueda ---
    var searchInput = document.getElementById('buscar-alumno');
    var searchForm = document.getElementById('form-buscar-alumnos');
    var searchTimeout;
    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var val = this.value;
            searchTimeout = setTimeout(function() {
                if (val.length >= 2 || val.length === 0) searchForm.submit();
            }, 500);
        });
    }

    // --- Filtro curso ---
    var filterSelect = document.getElementById('filtro-curso');
    if (filterSelect && searchForm) {
        filterSelect.addEventListener('change', function() { searchForm.submit(); });
    }

    // --- Modal Confirmacion ---
    var modalConfirm = document.getElementById('modal-confirmacion');
    var confirmHeader = document.getElementById('modal-confirm-header');
    var confirmIcon = document.getElementById('modal-confirm-icon');
    var confirmTitulo = document.getElementById('modal-confirm-titulo');
    var confirmText = document.getElementById('modal-confirm-text');
    var confirmSubtext = document.getElementById('modal-confirm-subtext');
    var btnConfirmar = document.getElementById('btn-confirmar-accion');
    var btnCancelarConfirm = document.getElementById('btn-cancelar-confirm');
    var btnCerrarConfirm = document.getElementById('cerrar-modal-confirm');
    var confirmForm = null;
    var confirmMethod = null; // 'baja' | 'reactivar'

    function abrirModalConfirm(tipo, subtexto) {
        if (!modalConfirm) return;
        modalConfirm.classList.remove('modal-confirm-danger', 'modal-confirm-success');
        modalConfirm.classList.add('active');
        document.body.style.overflow = 'hidden';
        if (tipo === 'baja') {
            modalConfirm.classList.add('modal-confirm-danger');
            confirmIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
            confirmTitulo.textContent = 'Dar de Baja';
            btnConfirmar.textContent = 'Si, dar de baja';
            confirmSubtext.textContent = subtexto || 'Se condonaran las cuotas futuras en todos sus cursos. Las cuotas vencidas y pagadas no se modifican.';
        } else if (tipo === 'reactivar') {
            modalConfirm.classList.add('modal-confirm-success');
            confirmIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';
            confirmTitulo.textContent = 'Reactivar Inscripción';
            btnConfirmar.textContent = 'Si, reactivar';
            confirmSubtext.textContent = subtexto || 'Las cuotas condonadas volveran a Pendiente con nuevas fechas de vencimiento.';
        }
        confirmMethod = tipo;
    }

    function cerrarModalConfirm() {
        if (!modalConfirm) return;
        modalConfirm.classList.remove('active');
        document.body.style.overflow = '';
        confirmForm = null;
        confirmMethod = null;
    }

    if (btnCerrarConfirm) btnCerrarConfirm.addEventListener('click', cerrarModalConfirm);
    if (btnCancelarConfirm) btnCancelarConfirm.addEventListener('click', cerrarModalConfirm);
    if (modalConfirm) modalConfirm.addEventListener('click', function(e) { if (e.target === modalConfirm) cerrarModalConfirm(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalConfirm && modalConfirm.classList.contains('active')) cerrarModalConfirm();
    });
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            if (confirmForm) confirmForm.submit();
            cerrarModalConfirm();
        });
    }

    // Botones de baja (usando data-baja-url, sin form)
    document.querySelectorAll('[data-baja-url]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este alumno';
            var url = this.getAttribute('data-baja-url');
            // Crear form temporal
            var f = document.createElement('form');
            f.method = 'POST'; f.action = url; f.style.display = 'none';
            var t = document.createElement('input');
            t.type = 'hidden'; t.name = '_token'; t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t);
            document.body.appendChild(f);
            confirmForm = f;
            confirmText.textContent = 'Esta seguro que desea dar de baja a "' + nombre + '" de TODOS sus cursos?';
            abrirModalConfirm('baja', 'Se condonaran las cuotas futuras y el alumno quedara como Retirado en todas sus inscripciones activas.');
        });
    });

    // Botones de reactivar (inscripciones individuales, sin form)
    document.querySelectorAll('[data-reactivar-url]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este alumno';
            var url = this.getAttribute('data-reactivar-url');
            var f = document.createElement('form');
            f.method = 'POST'; f.action = url; f.style.display = 'none';
            var t = document.createElement('input');
            t.type = 'hidden'; t.name = '_token'; t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t);
            document.body.appendChild(f);
            confirmForm = f;
            confirmText.textContent = 'Desea reactivar la inscripcion de "' + nombre + '"?';
            abrirModalConfirm('reactivar', 'Las cuotas condonadas volveran a Pendiente con fechas de vencimiento recalculadas desde hoy.');
        });
    });

    // Botones de baja antigua (form-baja-estudiante) - legacy
    document.querySelectorAll('.form-baja-estudiante .btn-baja').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = btn.getAttribute('data-nombre') || 'este alumno';
            confirmForm = btn.closest('form');
            confirmText.textContent = 'Esta seguro que desea dar de baja a "' + nombre + '"?';
            abrirModalConfirm('baja', 'El alumno quedara como inactivo en la lista.');
        });
    });

    // Botones de reactivar legacy (form-baja-inline)
    document.querySelectorAll('.form-baja-inline .btn-reactivar').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = btn.getAttribute('data-nombre') || 'este alumno';
            confirmForm = btn.closest('form');
            confirmText.textContent = 'Desea reactivar al alumno "' + nombre + '"?';
            abrirModalConfirm('reactivar');
        });
    });

    // --- Animacion filas ---
    document.querySelectorAll('table.data-table tbody tr').forEach(function(fila, i) {
        fila.style.opacity = '0';
        fila.style.transform = 'translateY(10px)';
        setTimeout(function() {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = fila.classList.contains('fila-inactiva') ? '0.5' : '1';
            fila.style.transform = 'translateY(0)';
        }, i * 80);
    });

    // --- MODAL CARGAR DOCUMENTO ---
    var modal = document.getElementById('cd-modal-documento');
    var form = document.getElementById('cd-form-documento');
    var dropzone = document.getElementById('cd-dropzone');
    var fileInput = document.getElementById('cd-archivo');
    var titulo = document.getElementById('cd-modal-titulo');

    function abrirDocModal(alumnoId, alumnoNombre) {
        if (!modal) return;
        if (form) form.action = '/estudiantes/' + alumnoId + '/documentos';
        if (titulo) titulo.textContent = 'Cargar Documento \u2014 ' + alumnoNombre;
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function cerrarDocModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.classList.remove('active');
        document.body.style.overflow = '';
        if (form) form.reset();
        if (dropzone) {
            dropzone.classList.remove('active', 'cd-has-error');
            var tp = dropzone.querySelector('p.cd-dropzone-text');
            if (tp) tp.textContent = 'Arrastra el archivo aqui';
        }
    }

    document.querySelectorAll('.btn-subir-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-alumno-id');
            var nombre = this.getAttribute('data-alumno-nombre');
            if (id) abrirDocModal(id, nombre || 'Alumno');
        });
    });

    var btnCerrar = document.getElementById('cd-cerrar-modal');
    var btnCancelar = document.getElementById('cd-cancelar');
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarDocModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarDocModal);
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) cerrarDocModal(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) cerrarDocModal();
    });

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { fileInput.click(); });
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('active'); });
        dropzone.addEventListener('dragleave', function() { this.classList.remove('active'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault(); this.classList.remove('active');
            fileInput.files = e.dataTransfer.files;
            var tp = dropzone.querySelector('p.cd-dropzone-text');
            if (tp && e.dataTransfer.files.length) tp.textContent = 'Archivo: ' + e.dataTransfer.files[0].name;
        });
        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                var tp = dropzone.querySelector('p.cd-dropzone-text');
                if (tp) tp.textContent = 'Archivo: ' + this.files[0].name;
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            var tipo = document.getElementById('cd-tipo');
            if (!tipo || !tipo.value) { e.preventDefault(); tipo.classList.add('error-input'); return; }
            if (!fileInput || !fileInput.files.length) { e.preventDefault(); dropzone.classList.add('cd-has-error'); return; }
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }
        });
    }

    // --- Auto-hide alerts ---
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function() { a.style.transition = 'opacity 0.5s'; a.style.opacity = '0'; setTimeout(function() { a.remove(); }, 500); }, 4000);
    });
});
</script>

@endsection
