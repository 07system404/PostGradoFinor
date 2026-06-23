/* ============================================
   FORM-CARGAR-DOCUMENTO.JS
   Modal "Cargar Documento" + Acciones docs
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    var modal = document.getElementById('cd-modal-documento');
    var form = document.getElementById('cd-form-documento');
    var dropzone = document.getElementById('cd-dropzone');
    var fileInput = document.getElementById('cd-archivo');
    var titulo = document.getElementById('cd-modal-titulo');

    // --- Abrir modal ---
    function abrirModal(alumnoId, alumnoNombre) {
        if (!modal) return;
        if (form && alumnoId) form.action = '/estudiantes/' + alumnoId + '/documentos';
        if (titulo) titulo.textContent = 'Cargar Documento \u2014 ' + (alumnoNombre || 'Alumno');
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // --- Cerrar modal ---
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

    // --- Boton "Cargar Documento" (show) ---
    var btnCargar = document.getElementById('btn-cargar-documento');
    if (btnCargar) {
        btnCargar.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-alumno-id');
            var nombre = this.getAttribute('data-alumno-nombre') || 'Alumno';
            abrirModal(id, nombre);
        });
    }

    // --- Botones "Subir" en tabla (index) ---
    document.querySelectorAll('.btn-subir-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-alumno-id');
            var nombre = this.getAttribute('data-alumno-nombre');
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

    // --- Validacion pre-submit ---
    if (form) {
        form.addEventListener('submit', function(e) {
            var tipo = document.getElementById('cd-tipo');
            if (!tipo || !tipo.value) { e.preventDefault(); tipo.classList.add('error-input'); return; }
            if (!fileInput || !fileInput.files.length) { e.preventDefault(); dropzone.classList.add('cd-has-error'); return; }
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Cargando...'; }
        });
    }

    // ===========================
    // VER DOCUMENTO (PREVIEW)
    // ===========================
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
                previewBody.innerHTML = '<div style="padding:40px;text-align:center;"><p style="font-size:16px;color:var(--gray-600);margin-bottom:20px;">Vista previa no disponible para este tipo de archivo.</p><a href="' + url + '" download="' + nombre + '" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--primary);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Descargar archivo</a></div>';
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

    // --- Cerrar preview con Escape ---
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && previewModal && previewModal.style.display !== 'none') {
            cerrarPreview();
        }
    });

    // ===========================
    // DESCARGAR DOCUMENTO
    // ===========================
    document.querySelectorAll('.btn-descargar-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('data-url');
            if (!url) { alert('Documento no disponible'); return; }
            window.location.href = url;
        });
    });

    // ===========================
    // ELIMINAR DOCUMENTO
    // ===========================
    document.querySelectorAll('.btn-eliminar-doc').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var docId = this.getAttribute('data-doc-id');
            var docNombre = this.getAttribute('data-doc-nombre') || 'este documento';
            if (!docId) return;
            if (!confirm('\u00bfEliminar "' + docNombre + '"? Esta accion no se puede deshacer.')) return;

            var f = document.createElement('form');
            f.method = 'POST'; f.action = '/documentos/' + docId;
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

});
