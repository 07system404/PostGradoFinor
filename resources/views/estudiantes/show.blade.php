@extends('layouts.app')

@section('title', 'Perfil del Alumno')

@section('content')

<link rel="stylesheet" href="{{ asset('css/estudiantes_show.css') }}">
<link rel="stylesheet" href="{{ asset('css/form-cargar-documento.css') }}">
<link rel="stylesheet" href="{{ asset('css/form-inscripcion-programa.css') }}">

<!-- Breadcrumb (contextual según el origen) -->
<nav class="breadcrumb-perfil">
    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
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

<!-- Gestion de Documentacion -->
<div class="perfil-grid full">
    <div class="perfil-card">
        <div class="perfil-card-header">
            <h2 class="perfil-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Gestion de Documentacion
            </h2>
            <button class="btn-cargar-doc" id="btn-cargar-documento" data-alumno-id="{{ $estudiante->id }}" data-alumno-nombre="{{ $estudiante->nombre_completo }}">
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
                    <th>ACCION</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $documentosEsperados = [
                        ['nombre' => 'Grado de Bachiller', 'tipo' => 'Academico'],
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
                            <button class="btn-doc-action btn-ver-doc" data-url="{{ asset('storage/' . $docSubido->ruta_archivo) }}" data-nombre="{{ $docSubido->nombre_archivo }}" title="Ver">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button class="btn-doc-action btn-descargar-doc" data-url="{{ route('documentos.descargar', $docSubido) }}" title="Descargar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </button>
                            <button class="btn-doc-action btn-eliminar-doc" data-doc-id="{{ $docSubido->id }}" data-doc-nombre="{{ $docSubido->nombre_archivo }}" title="Eliminar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                            @else
                            <span style="color: var(--gray-400); font-size: 12px;">-</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Cargar Documento -->
<div class="cd-modal-overlay" id="cd-modal-documento">
    <div class="cd-modal-box">
        <div class="cd-modal-header">
            <h3 class="cd-modal-title" id="cd-modal-titulo">Cargar Documento</h3>
            <button type="button" class="cd-modal-close" id="cd-cerrar-modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="cd-form-documento" action="{{ route('documentos.store', $estudiante->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="redirect_to" value="show">
            <div class="cd-modal-body">
                <div class="cd-form-group">
                    <label for="cd-tipo">Tipo de Documento <span class="cd-required">*</span></label>
                    <select id="cd-tipo" name="tipo" required>
                        <option value="">Seleccione un tipo...</option>
                        <option value="Grado de Bachiller">Grado de Bachiller</option>
                        <option value="Certificado de Idiomas">Certificado de Idiomas</option>
                        <option value="Copia de DNI / Pasaporte">Copia de DNI / Pasaporte</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="cd-form-group">
                    <label for="cd-archivo">Archivo (PNG, JPG, PDF) <span class="cd-required">*</span></label>
                    <div class="cd-dropzone" id="cd-dropzone">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p class="cd-dropzone-text">Arrastra el archivo aqui</p>
                        <p class="cd-dropzone-subtext">o haz clic para seleccionar</p>
                    </div>
                    <input type="file" id="cd-archivo" name="archivo" accept=".jpg,.jpeg,.png,.pdf" class="cd-hidden" required>
                </div>
            </div>
            <div class="cd-modal-footer">
                <button type="button" class="cd-btn-cancelar" id="cd-cancelar">Cancelar</button>
                <button type="submit" class="cd-btn-subir" id="cd-btn-subir">Cargar Documento</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Preview Documento -->
<div class="cd-modal-overlay" id="cd-preview-modal" style="display:none;">
    <div class="cd-modal-box" style="max-width:800px;">
        <div class="cd-modal-header">
            <h3 class="cd-modal-title" id="cd-preview-titulo">Documento</h3>
            <button type="button" class="cd-modal-close" id="cd-cerrar-preview">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cd-modal-body" id="cd-preview-body" style="padding:16px;text-align:center;">
        </div>
    </div>
</div>

<x-form-inscripcion-programa :alumno-id="$estudiante->id" />

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== MODAL CARGAR DOCUMENTO =====
    var modal = document.getElementById('cd-modal-documento');
    var form = document.getElementById('cd-form-documento');
    var dropzone = document.getElementById('cd-dropzone');
    var fileInput = document.getElementById('cd-archivo');
    var titulo = document.getElementById('cd-modal-titulo');

    function abrirModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
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

    var btnCargar = document.getElementById('btn-cargar-documento');
    if (btnCargar) {
        btnCargar.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-alumno-nombre') || 'Alumno';
            if (titulo) titulo.textContent = 'Cargar Documento \u2014 ' + nombre;
            abrirModal();
        });
    }

    var btnCerrar = document.getElementById('cd-cerrar-modal');
    var btnCancelar = document.getElementById('cd-cancelar');
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrarModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (modal && modal.classList.contains('active')) cerrarModal();
            var pm = document.getElementById('cd-preview-modal');
            if (pm && pm.style.display !== 'none') cerrarPreview();
        }
    });

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { fileInput.click(); });
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('active'); });
        dropzone.addEventListener('dragleave', function() { this.classList.remove('active'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('active');
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

    // ===== VER DOCUMENTO (PREVIEW) =====
    var previewModal = document.getElementById('cd-preview-modal');
    var previewBody = document.getElementById('cd-preview-body');
    var previewTitulo = document.getElementById('cd-preview-titulo');

    function cerrarPreview() {
        if (previewModal) { previewModal.style.display = 'none'; }
        if (previewBody) { previewBody.innerHTML = ''; }
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-ver-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('data-url');
            var nombre = this.getAttribute('data-nombre') || 'Documento';
            if (!url) { alert('Documento no disponible'); return; }

            if (previewTitulo) previewTitulo.textContent = nombre;
            var ext = url.split('.').pop().toLowerCase();
            var isImage = ['jpg','jpeg','png','gif','webp','svg'].indexOf(ext) !== -1;
            var isPdf = ext === 'pdf';

            if (isImage) {
                previewBody.innerHTML = '<img src="' + url + '" alt="' + nombre + '" style="max-width:100%;max-height:70vh;border-radius:8px;">';
            } else if (isPdf) {
                previewBody.innerHTML = '<embed src="' + url + '" type="application/pdf" style="width:100%;height:70vh;border:none;border-radius:8px;">';
            } else {
                previewBody.innerHTML = '<div style="padding:40px;text-align:center;"><p style="font-size:16px;color:var(--gray-600);margin-bottom:20px;">Vista previa no disponible para este tipo de archivo.</p><a href="' + url + '" download="' + nombre + '" class="btn-download-doc" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--primary);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Descargar archivo</a></div>';
            }
            previewModal.style.display = 'flex';
            previewModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    var btnCerrarPreview = document.getElementById('cd-cerrar-preview');
    if (btnCerrarPreview) btnCerrarPreview.addEventListener('click', cerrarPreview);
    if (previewModal) {
        previewModal.addEventListener('click', function(e) {
            if (e.target === previewModal) cerrarPreview();
        });
    }

    // ===== DESCARGAR DOCUMENTO =====
    document.querySelectorAll('.btn-descargar-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('data-url');
            if (!url) { alert('Documento no disponible'); return; }
            window.location.href = url;
        });
    });

    // ===== ELIMINAR DOCUMENTO =====
    document.querySelectorAll('.btn-eliminar-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var docId = this.getAttribute('data-doc-id');
            var docNombre = this.getAttribute('data-doc-nombre') || 'este documento';
            if (!docId) return;
            if (!confirm('\u00bfEliminar "' + docNombre + '"? Esta accion no se puede deshacer.')) return;

            var f = document.createElement('form');
            f.method = 'POST';
            f.action = '/documentos/' + docId;
            f.style.display = 'none';
            var t1 = document.createElement('input');
            t1.type = 'hidden'; t1.name = '_token';
            t1.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t1);
            var t2 = document.createElement('input');
            t2.type = 'hidden'; t2.name = '_method'; t2.value = 'DELETE';
            f.appendChild(t2);
            document.body.appendChild(f);
            f.submit();
        });
    });

    // ===== AUTO-HIDE ALERTS =====
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function() {
            a.style.transition = 'opacity 0.5s';
            a.style.opacity = '0';
            setTimeout(function() { a.remove(); }, 500);
        }, 4000);
    });

    // ===== MODAL NUEVA INSCRIPCION =====
    var btnNuevaInscripcion = document.getElementById('btn-nueva-inscripcion');
    var modalInscripcion = document.getElementById('modal-nueva-inscripcion');
    var btnCerrarInscripcion = document.getElementById('fi-cerrar-modal');
    var btnCancelarInscripcion = document.getElementById('fi-cancelar-modal');

    if (btnNuevaInscripcion && modalInscripcion) {
        btnNuevaInscripcion.addEventListener('click', function() {
            modalInscripcion.style.display = 'flex';
            modalInscripcion.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    function cerrarInscripcion() {
        if (modalInscripcion) {
            modalInscripcion.style.display = 'none';
            modalInscripcion.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (btnCerrarInscripcion) btnCerrarInscripcion.addEventListener('click', cerrarInscripcion);
    if (btnCancelarInscripcion) btnCancelarInscripcion.addEventListener('click', cerrarInscripcion);
    if (modalInscripcion) {
        modalInscripcion.addEventListener('click', function(e) {
            if (e.target === modalInscripcion) cerrarInscripcion();
        });
    }

    // ===== ANIMACION ENTRADA =====
    document.querySelectorAll('.perfil-card').forEach(function(card, i) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(function() {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 100);
    });
});
</script>

@endsection
