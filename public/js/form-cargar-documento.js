/* =========================================
   FORM-CARGAR-DOCUMENTO.JS
   Modal "Cargar Documento" + Acciones docs
   PostGrado Pro
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {

    var modal = document.getElementById('cd-modal-documento');
    var form = document.getElementById('cd-form-documento');
    var dropzone = document.getElementById('cd-dropzone');
    var fileInput = document.getElementById('cd-archivo');
    var titulo = document.getElementById('cd-modal-titulo');

    // --- Abrir modal ---
    function abrirModal(alumnoId, alumnoNombre) {
        if (!modal) return;
        if (form) form.action = '/estudiantes/' + alumnoId + '/documentos';
        if (titulo) titulo.textContent = 'Cargar Documento \u2014 ' + alumnoNombre;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // --- Cerrar modal ---
    function cerrarModal() {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
        if (form) form.reset();
        if (dropzone) {
            dropzone.classList.remove('active', 'cd-has-error');
            var tp = dropzone.querySelector('p.cd-dropzone-text');
            if (tp) tp.textContent = 'Arrastra el archivo aqu\u00ed';
        }
        var errs = dropzone ? dropzone.querySelectorAll('.error') : [];
        errs.forEach(function(e) { e.remove(); });
        var selects = document.querySelectorAll('.cd-form-group select.error-input');
        selects.forEach(function(s) { s.classList.remove('error-input'); });
    }

    // --- Boton "Cargar Documento" (show) y "Subir" (index) ---
    var btnCargar = document.getElementById('btn-cargar-documento');
    if (btnCargar) {
        btnCargar.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.alumnoId;
            var nombre = this.dataset.alumnoNombre;
            if (id) abrirModal(id, nombre || 'Alumno');
        });
    }
    var btnsSubir = document.querySelectorAll('.btn-subir-doc');
    btnsSubir.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.alumnoId;
            var nombre = this.dataset.alumnoNombre;
            if (id) abrirModal(id, nombre || 'Alumno');
        });
    });

    // --- Cerrar con X y Cancelar ---
    var btnCerrar = document.getElementById('cd-cerrar-modal');
    var btnCancelar = document.getElementById('cd-cancelar');
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);

    // --- Cerrar clic fuera ---
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrarModal();
        });
    }

    // --- Cerrar con Escape ---
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            cerrarModal();
        }
    });

    // --- Drag & Drop ---
    if (dropzone && fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(evt) {
            dropzone.addEventListener(evt, function(ev) { ev.preventDefault(); ev.stopPropagation(); });
        });
        ['dragenter', 'dragover'].forEach(function(evt) {
            dropzone.addEventListener(evt, function() { dropzone.classList.add('active'); });
        });
        ['dragleave', 'drop'].forEach(function(evt) {
            dropzone.addEventListener(evt, function() { dropzone.classList.remove('active'); });
        });

        dropzone.addEventListener('drop', function(e) {
            fileInput.files = e.dataTransfer.files;
            manejarArchivo({ target: { files: e.dataTransfer.files } });
        });

        dropzone.addEventListener('click', function() { fileInput.click(); });
        fileInput.addEventListener('change', function(e) { manejarArchivo(e); });

        function manejarArchivo(e) {
            var files = e.target.files;
            if (files.length > 0) {
                var file = files[0];
                var validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten archivos JPG, PNG o PDF');
                    fileInput.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('El archivo no puede ser mayor a 5MB');
                    fileInput.value = '';
                    return;
                }
                var tp = dropzone.querySelector('p.cd-dropzone-text');
                if (tp) tp.textContent = 'Archivo: ' + file.name;
                dropzone.classList.remove('cd-has-error');
                var err = dropzone.querySelector('.error');
                if (err) err.remove();
            }
        }
    }

    // --- Validacion pre-submit ---
    if (form) {
        form.addEventListener('submit', function(e) {
            var valido = true;
            var tipoSelect = document.getElementById('cd-tipo');
            if (!tipoSelect || !tipoSelect.value) {
                valido = false;
                if (tipoSelect) tipoSelect.classList.add('error-input');
            } else {
                if (tipoSelect) tipoSelect.classList.remove('error-input');
            }
            if (!fileInput || !fileInput.files.length) {
                valido = false;
                if (dropzone) {
                    dropzone.classList.add('cd-has-error');
                    if (!dropzone.querySelector('.error')) {
                        var err = document.createElement('p');
                        err.className = 'error';
                        err.textContent = 'Debe seleccionar un archivo';
                        dropzone.appendChild(err);
                    }
                }
            } else {
                if (dropzone) {
                    dropzone.classList.remove('cd-has-error');
                    var err = dropzone.querySelector('.error');
                    if (err) err.remove();
                }
            }
            if (!valido) {
                e.preventDefault();
                return;
            }
            var btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Cargando...';
            }
        });
    }

    // === ACCIONES DE DOCUMENTOS: Ver / Descargar / Eliminar ===

    // --- Ver documento (preview en modal) ---
    document.querySelectorAll('.btn-ver-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.dataset.url;
            var nombre = this.dataset.nombre || 'Documento';
            if (!url) { alert('Documento no disponible'); return; }
            abrirPreviewModal(url, nombre);
        });
    });

    // --- Descargar documento ---
    document.querySelectorAll('.btn-descargar-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.dataset.url;
            if (!url) { alert('Documento no disponible'); return; }
            window.location.href = url;
        });
    });

    // --- Eliminar documento ---
    document.querySelectorAll('.btn-eliminar-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var docId = this.dataset.docId;
            var docNombre = this.dataset.docNombre || 'este documento';
            if (!docId) return;
            if (!confirm('\u00bfEst\u00e1 seguro de eliminar "' + docNombre + '"? Esta acci\u00f3n no se puede deshacer.')) return;

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/documentos/' + docId;
            form.style.display = 'none';

            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);

            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        });
    });

    // --- Modal de preview ---
    function abrirPreviewModal(url, nombre) {
        var existing = document.getElementById('cd-preview-modal');
        if (existing) existing.remove();

        var isImage = /\.(jpg|jpeg|png)$/i.test(url);
        var content = '';
        if (isImage) {
            content = '<img src="' + url + '" alt="' + nombre + '" style="max-width:100%;max-height:70vh;border-radius:8px;display:block;margin:0 auto;">';
        } else {
            content = '<iframe src="' + url + '" style="width:100%;height:70vh;border:none;border-radius:8px;"></iframe>';
        }

        var overlay = document.createElement('div');
        overlay.id = 'cd-preview-modal';
        overlay.className = 'cd-modal-overlay active';
        overlay.innerHTML =
            '<div class="cd-modal-box" style="max-width:800px;">' +
                '<div class="cd-modal-header">' +
                    '<h3 class="cd-modal-title">' + nombre + '</h3>' +
                    '<button type="button" class="cd-modal-close" onclick="this.closest(\'.cd-modal-overlay\').remove();document.body.style.overflow=\'\';">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<line x1="18" y1="6" x2="6" y2="18"/>' +
                            '<line x1="6" y1="6" x2="18" y2="18"/>' +
                        '</svg>' +
                    '</button>' +
                '</div>' +
                '<div class="cd-modal-body" style="padding:16px;">' +
                    content +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', function handler(e) {
            if (e.key === 'Escape') {
                var m = document.getElementById('cd-preview-modal');
                if (m) {
                    m.remove();
                    document.body.style.overflow = '';
                }
                document.removeEventListener('keydown', handler);
            }
        });
    }

});
